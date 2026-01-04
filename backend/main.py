
import os
import sys
import json
import logging
from typing import List, Optional, Dict, Any
from fastapi import FastAPI, HTTPException, Body
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import chromadb
from chromadb.config import Settings
from sentence_transformers import SentenceTransformer, CrossEncoder
from FlagEmbedding import BGEM3FlagModel
from groq import Groq
from dotenv import load_dotenv
import uvicorn

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load environment variables
load_dotenv()

# Add parent directory to path to allow imports if needed
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

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
llm_service = None

@app.on_event("startup")
def startup_event():
    global business_engine, php_engine, js_engine, llm_service
    
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

        
    # Initialize LLM
    if GROQ_API_KEY:
        llm_service = LLMService(GROQ_API_KEY)
        logger.info("LLM Service Ready")

# --- Helper to Format Context ---
def format_context(results: List[Dict]) -> str:
    return "\n\n".join([f"[Source: {r['metadata'].get('file_path') or r['metadata'].get('page_name') or 'N/A'}]\n{r['content']}" for r in results])

# --- Endpoints ---

@app.post("/inference/business", response_model=QueryResponse)
async def inference_business(request: QueryRequest):
    if not business_engine:
        raise HTTPException(status_code=503, detail="Business engine not initialized")
    
    results = business_engine.query(request.query, request.top_k, request.rerank)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        system_prompt = "You are an expert banking assistant. Answer the user query based strictly on the provided business documentation context. If the provided context contains Mermaid JS diagram code, you MUST include it in your response wrapped in a mermaid code block."
        llm_response = llm_service.generate_response(system_prompt, request.query, context)
        
    return QueryResponse(results=results, llm_response=llm_response, context_used=context)

@app.post("/inference/php", response_model=QueryResponse)
async def inference_php(request: QueryRequest):
    if not php_engine:
        raise HTTPException(status_code=503, detail="PHP engine not initialized")
    
    results = php_engine.query(request.query, request.top_k)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        system_prompt = "You are an expert PHP Laravel developer. Answer the user query based strictly on the provided PHP code context. Do not hallucinate."
        llm_response = llm_service.generate_response(system_prompt, request.query, context)
        
    return QueryResponse(results=results, llm_response=llm_response, context_used=context)

@app.post("/inference/js", response_model=QueryResponse)
async def inference_js(request: QueryRequest):
    if not js_engine:
        raise HTTPException(status_code=503, detail="JS engine not initialized")
    
    results = js_engine.query(request.query, request.top_k)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        system_prompt = "You are an expert JavaScript/React developer. Answer the user query based strictly on the provided JS code context. Do not hallucinate."
        llm_response = llm_service.generate_response(system_prompt, request.query, context)
        
    return QueryResponse(results=results, llm_response=llm_response, context_used=context)

if __name__ == "__main__":
    uvicorn.run("backend.main:app", host="0.0.0.0", port=8000, reload=True)
