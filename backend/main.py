
import os
import sys
import json
import logging
from typing import List, Optional, Dict, Any
from fastapi import FastAPI, HTTPException, Body, Depends
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import chromadb
from chromadb.config import Settings
from sentence_transformers import SentenceTransformer, CrossEncoder
from FlagEmbedding import BGEM3FlagModel
from groq import Groq
from dotenv import load_dotenv
import uvicorn
from sqlalchemy.orm import Session

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load environment variables
load_dotenv()

# Add parent directory to path to allow imports if needed
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

# Import BladeDescriptionEngine
from utils.blade_description_engine import BladeDescriptionEngine

# Import database modules
import database
import crud
from models import MessageRole

app = FastAPI(title="Banking Knowledge Assistant API")

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- Configuration ---
GROQ_API_KEY = os.getenv("GROQ_API_KEY")
if not GROQ_API_KEY:
    # Fallback to key found in notebook if not in env (For development only)
    GROQ_API_KEY = "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"

PROJECT_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
VECTOR_DB_ROOT = os.path.join(PROJECT_ROOT, "vector_db")
EMBEDDING_DB_ROOT = os.path.join(PROJECT_ROOT, "embedding_vectordb")

# --- Models ---

class QueryRequest(BaseModel):
    query: str
    top_k: int = 5
    rerank: bool = True
    conversation_id: Optional[int] = None  # Optional conversation ID to save to

class QueryResponse(BaseModel):
    results: List[Dict[str, Any]]
    llm_response: Optional[str] = None
    context_used: Optional[str] = None

# --- Engines ---

class BusinessQueryEngine:
    def __init__(self):
        # Correct path to business docs
        self.db_path = os.path.join(VECTOR_DB_ROOT, "business_docs_chroma_db")
        self.collection_name = "cube_docs_optimized"
        self.embedding_model_name = "BAAI/bge-m3"
        
        logger.info(f"Initializing Business Engine with DB: {self.db_path} and Model: {self.embedding_model_name}")
        self.embedding_model = SentenceTransformer(self.embedding_model_name)
        # Note: Notebook does not use reranker, relying on BGE-M3 quality
        
        self.client = chromadb.PersistentClient(
            path=self.db_path,
            settings=Settings(anonymized_telemetry=False)
        )
        self.collection = self.client.get_collection(name=self.collection_name)

    def query(self, query_text: str, top_k: int = 5, rerank: bool = True) -> List[Dict]:
        # BGE-M3 encoding via SentenceTransformer
        query_embedding = self.embedding_model.encode(
            [query_text],
            normalize_embeddings=True
        )[0].tolist()
        
        results = self.collection.query(
            query_embeddings=[query_embedding],
            n_results=top_k
        )
        
        formatted_results = []
        if results['documents']:
            for i in range(len(results['documents'][0])):
                formatted_results.append({
                    'id': results['ids'][0][i],
                    'content': results['documents'][0][i],
                    'metadata': results['metadatas'][0][i],
                    'distance': results['distances'][0][i] if 'distances' in results else None
                })
        
        return formatted_results

class CodeQueryEngine:
    def __init__(self, db_path: str, collection_name: str, language: str):
        self.db_path = db_path
        self.collection_name = collection_name
        self.language = language # 'php' or 'js'
        self.model_name = "BAAI/bge-m3"
        
        logger.info(f"Initializing Code Engine ({language}) with DB: {self.db_path}")
        # Use SentenceTransformer to share the model cache/memory with Business Engine
        # and avoid downloading the model twice (FlagEmbedding vs SentenceTransformer caches)
        self.model = SentenceTransformer(self.model_name)
        
        self.client = chromadb.PersistentClient(
            path=self.db_path,
            settings=Settings(anonymized_telemetry=False)
        )
        self.collection = self.client.get_collection(name=self.collection_name)

    def query(self, query_text: str, top_k: int = 5) -> List[Dict]:
        # SentenceTransformer returns the dense embedding directly
        query_embedding = self.model.encode(
            [query_text],
            normalize_embeddings=True
        )[0].tolist()
        
        results = self.collection.query(
            query_embeddings=[query_embedding],
            n_results=top_k
        )
        
        formatted_results = []
        if results['documents']:
            for i in range(len(results['documents'][0])):
                formatted_results.append({
                    'id': results['ids'][0][i],
                    'content': results['documents'][0][i],
                    'metadata': results['metadatas'][0][i],
                    'distance': results['distances'][0][i] if 'distances' in results else None
                })
        
        return formatted_results

# --- LLM Integration ---

class LLMService:
    def __init__(self, api_key: str):
        self.client = Groq(api_key=api_key)

    def generate_response(self, system_prompt: str, user_query: str, context: str) -> str:
        try:
            chat_completion = self.client.chat.completions.create(
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": f"Context:\n{context}\n\nQuery: {user_query}"}
                ],
                model="llama-3.3-70b-versatile",
                temperature=0.3,
                max_tokens=2048
            )
            return chat_completion.choices[0].message.content
        except Exception as e:
            logger.error(f"LLM Error: {e}")
            return f"Error generating response: {str(e)}"

# --- Global Instances (Lazy Loading strategy or Global Init) ---
# We initialize on startup
business_engine = None
php_engine = None
js_engine = None
blade_engine = None
llm_service = None

@app.on_event("startup")
def startup_event():
    global business_engine, php_engine, js_engine, blade_engine, llm_service
    
    # Initialize Database
    try:
        database.init_db()
        logger.info("Database initialized")
    except Exception as e:
        logger.error(f"Failed to initialize database: {e}")
    
    # Include chat history routes
    from routers.chat_routes import router as chat_router
    app.include_router(chat_router)
    
    # Initialize Business Engine
    try:
        business_engine = BusinessQueryEngine()
        logger.info("Business Engine Ready")
    except Exception as e:
        logger.error(f"Failed to load Business Engine: {e}")

    # Initialize PHP Engine
    try:
        # PHP DB with code snippets
        php_db_path = os.path.join(VECTOR_DB_ROOT, "php_vector_db")
        php_engine = CodeQueryEngine(php_db_path, "php_code_chunks", "php")
        logger.info("PHP Engine Ready")
    except Exception as e:
        logger.error(f"Failed to load PHP Engine: {e}")

    # Initialize JS Engine
    try:
        # JS DB is in vector_db
        js_db_path = os.path.join(VECTOR_DB_ROOT, "js_chroma_db")
        js_engine = CodeQueryEngine(js_db_path, "js_code_knowledge", "js")
        logger.info("JS Engine Ready")
    except Exception as e:
        logger.error(f"Failed to load JS Engine: {e}")

    # Initialize Blade Engine
    try:
        blade_db_path = os.path.join(VECTOR_DB_ROOT, "blade_views_chroma_db")
        blade_engine = BladeDescriptionEngine(db_path=blade_db_path)
        logger.info("Blade Engine Ready")
    except Exception as e:
        logger.error(f"Failed to load Blade Engine: {e}")
        
    # Initialize LLM
    if GROQ_API_KEY:
        llm_service = LLMService(GROQ_API_KEY)
        logger.info("LLM Service Ready")

# --- Helper to Format Context ---
def format_context(results: List[Dict]) -> str:
    return "\n\n".join([f"[Source: {r['metadata'].get('file_path') or r['metadata'].get('page_name') or 'N/A'}]\n{r['content']}" for r in results])

# --- Endpoints ---

@app.post("/inference/business", response_model=QueryResponse)
async def inference_business(request: QueryRequest, db: Session = Depends(database.get_db)):
    if not business_engine:
        raise HTTPException(status_code=503, detail="Business engine not initialized")
    
    results = business_engine.query(request.query, request.top_k, request.rerank)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        system_prompt = "You are an expert banking assistant. Answer the user query based strictly on the provided business documentation context. If the provided context contains Mermaid JS diagram code, you MUST include it in your response wrapped in a mermaid code block."
        llm_response = llm_service.generate_response(system_prompt, request.query, context)
    
    # Save to database if conversation_id provided
    if request.conversation_id:
        try:
            # Save user message
            crud.create_message(db, request.conversation_id, MessageRole.USER, request.query)
            # Save bot response
            if llm_response:
                crud.create_message(db, request.conversation_id, MessageRole.BOT, llm_response, context)
        except Exception as e:
            logger.error(f"Error saving to database: {e}")
        
    return QueryResponse(results=results, llm_response=llm_response, context_used=context)

@app.post("/inference/php", response_model=QueryResponse)
async def inference_php(request: QueryRequest, db: Session = Depends(database.get_db)):
    if not php_engine:
        raise HTTPException(status_code=503, detail="PHP engine not initialized")
    
    results = php_engine.query(request.query, request.top_k)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        system_prompt = "You are an expert PHP Laravel developer. Answer the user query based strictly on the provided PHP code context. Do not hallucinate."
        llm_response = llm_service.generate_response(system_prompt, request.query, context)
    
    # Save to database if conversation_id provided
    if request.conversation_id:
        try:
            crud.create_message(db, request.conversation_id, MessageRole.USER, request.query)
            if llm_response:
                crud.create_message(db, request.conversation_id, MessageRole.BOT, llm_response, context)
        except Exception as e:
            logger.error(f"Error saving to database: {e}")
        
    return QueryResponse(results=results, llm_response=llm_response, context_used=context)

@app.post("/inference/js", response_model=QueryResponse)
async def inference_js(request: QueryRequest, db: Session = Depends(database.get_db)):
    if not js_engine:
        raise HTTPException(status_code=503, detail="JS engine not initialized")
    
    results = js_engine.query(request.query, request.top_k)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        system_prompt = "You are an expert JavaScript/React developer. Answer the user query based strictly on the provided JS code context. Do not hallucinate."
        llm_response = llm_service.generate_response(system_prompt, request.query, context)
    
    # Save to database if conversation_id provided
    if request.conversation_id:
        try:
            crud.create_message(db, request.conversation_id, MessageRole.USER, request.query)
            if llm_response:
                crud.create_message(db, request.conversation_id, MessageRole.BOT, llm_response, context)
        except Exception as e:
            logger.error(f"Error saving to database: {e}")
        
    return QueryResponse(results=results, llm_response=llm_response, context_used=context)

@app.post("/inference/blade", response_model=QueryResponse)
async def inference_blade(request: QueryRequest, db: Session = Depends(database.get_db)):
    if not blade_engine:
        raise HTTPException(status_code=503, detail="Blade engine not initialized")
    
    # Use blade engine's query method with Strategy 2
    blade_results = blade_engine.query(
        query_text=request.query,
        top_k=request.top_k,
        initial_candidates=20,
        max_snippet_chars=2000,
        use_rerank=request.rerank
    )
    
    # Format context for LLM using blade engine's method
    context = blade_engine.format_context_for_llm(
        blade_results,
        include_code=True,
        include_descriptions=True
    )
    
    llm_response = None
    if llm_service:
        system_prompt = """You are an expert Laravel Blade developer and template analyst.
Answer the user query based strictly on the provided blade template context.
Guidelines:
1. Reference specific files and code sections when relevant
2. Explain blade directives (@csrf, @auth, @include, etc.) clearly
3. Highlight form handling and security features
4. Be concise but thorough
5. If context is insufficient, say so"""
        llm_response = llm_service.generate_response(system_prompt, request.query, context)
    
    # Save to database if conversation_id provided
    if request.conversation_id:
        try:
            crud.create_message(db, request.conversation_id, MessageRole.USER, request.query)
            if llm_response:
                crud.create_message(db, request.conversation_id, MessageRole.BOT, llm_response, context)
        except Exception as e:
            logger.error(f"Error saving to database: {e}")
    
    # Convert blade results to standard format
    formatted_results = [{
        'id': r['id'],
        'content': r['snippet'],  # Use snippet instead of full content
        'metadata': {
            'file_name': r['file_name'],
            'file_path': r['file_path'],
            'section': r['section'],
            'description': r['description'],
            'has_form': r['has_form'],
            'snippet_length': r['snippet_length'],
            'content_length': r['content_length'],
            'rerank_score': r.get('rerank_score')
        },
        'distance': r.get('distance')
    } for r in blade_results]
        
    return QueryResponse(results=formatted_results, llm_response=llm_response, context_used=context)

if __name__ == "__main__":
    uvicorn.run("backend.main:app", host="0.0.0.0", port=8000, reload=True)
