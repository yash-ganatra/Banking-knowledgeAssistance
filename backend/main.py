import os
import sys
import json
import logging
from typing import List, Optional, Dict, Any
from datetime import datetime
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

# Import Query Router components
from query_router import (
    IntentClassifier,
    QueryRouter,
    UnifiedQueryEngine,
    KnowledgeSource
)

# Import Rate Limiter
from utils.groq_rate_limiter import GroqRateLimiter

# Import database modules
import database
import crud
from models import MessageRole

# Import Security modules
from security.security_config import (
    SECURITY_PREAMBLE,
    get_hardened_system_prompt,
    get_banking_security_addendum,
    REFUSAL_MESSAGES
)
from security.query_guardrails import (
    QueryGuardrails,
    check_query_safety,
    SecurityAnalysisResult
)
from security.output_filter import (
    OutputFilter,
    filter_llm_response,
    redact_sensitive
)

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

class SmartQueryRequest(BaseModel):
    """Request for smart router endpoint"""
    query: str
    top_k: int = 5
    confidence_threshold: float = 0.5
    min_relevance_score: float = 2.0  # Minimum cross-encoder score to include results
    conversation_id: Optional[int] = None

class QueryResponse(BaseModel):
    results: List[Dict[str, Any]]
    llm_response: Optional[str] = None
    context_used: Optional[str] = None

class SmartQueryResponse(BaseModel):
    """Response from smart router with routing metadata"""
    results: List[Dict[str, Any]]
    llm_response: Optional[str] = None
    context_used: Optional[str] = None
    routing_decision: Dict[str, Any]  # Intent classification details
    sources_queried: List[str]  # Which DBs were actually queried
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
    def __init__(self, api_key: str, rate_limiter: Optional[GroqRateLimiter] = None):
        self.client = Groq(api_key=api_key)
        self.rate_limiter = rate_limiter or GroqRateLimiter(
            max_retries=3,
            base_delay=2.0,
            daily_token_limit=100000,
            enable_cache=True,
            cache_ttl=1800  # 30 minutes
        )
        # Initialize output filter for response sanitization
        self.output_filter = OutputFilter(strict_mode=True)

    def generate_response(self, system_prompt: str, user_query: str, context: str, model: str = "llama-3.3-70b-versatile") -> str:
        """Generate LLM response with retry logic, caching, and security filtering"""
        try:
            @self.rate_limiter.with_retry
            def _make_completion(client, messages, model, temperature, max_tokens):
                return client.chat.completions.create(
                    messages=messages,
                    model=model,
                    temperature=temperature,
                    max_tokens=max_tokens
                )
            
            # Redact sensitive data from context before sending to LLM
            filtered_context = redact_sensitive(context)
            
            messages = [
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": f"Context:\n{filtered_context}\n\nQuery: {user_query}"}
            ]
            
            chat_completion = _make_completion(
                client=self.client,
                messages=messages,
                model=model,
                temperature=0.3,
                max_tokens=2048
            )
            
            raw_response = chat_completion.choices[0].message.content
            
            # Apply output filtering to redact any sensitive data in the response
            filtered_response = self.output_filter.filter_response(raw_response)
            
            if filtered_response.redactions_made > 0:
                logger.info(f"Output filter redacted {filtered_response.redactions_made} sensitive patterns")
            
            return filtered_response.response
            
        except Exception as e:
            logger.error(f"LLM Error after retries: {e}")
            # Check if rate limit error
            if "rate_limit" in str(e).lower():
                return f"⚠️ Rate limit reached. Please try again in a few minutes or upgrade your Groq plan. The system will automatically retry with a smaller model."
            return f"Error generating response: {str(e)}"
    
    def get_usage_stats(self) -> Dict[str, Any]:
        """Get token usage statistics"""
        return self.rate_limiter.get_usage_stats()

# --- Global Instances (Lazy Loading strategy or Global Init) ---
# We initialize on startup
business_engine = None
php_engine = None
js_engine = None
blade_engine = None
llm_service = None
unified_query_engine = None  # Smart router

@app.on_event("startup")
def startup_event():
    global business_engine, php_engine, js_engine, blade_engine, llm_service, unified_query_engine
    
    # Initialize Database
    try:
        database.init_db()
        logger.info("Database initialized")
    except Exception as e:
        logger.error(f"Failed to initialize database: {e}")
    
    # Include authentication routes
    from routers.auth_routes import router as auth_router
    app.include_router(auth_router)
    
    # Include chat history routes
    from routers.chat_routes import router as chat_router
    app.include_router(chat_router)
    
    # Include code review routes
    from routers.code_review_routes import router as code_review_router
    app.include_router(code_review_router)
    
    # Include inference logs routes
    from routers.inference_logs import router as inference_logs_router
    app.include_router(inference_logs_router)
    
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
    
    # Initialize Unified Query Engine (Smart Router)
    try:
        if all([business_engine, php_engine, js_engine, blade_engine, llm_service, GROQ_API_KEY]):
            intent_classifier = IntentClassifier(groq_api_key=GROQ_API_KEY, model="llama-3.1-8b-instant")
            
            # Initialize QueryRouter with Hybrid Search enabled
            # BM25 indices should be in PROJECT_ROOT/bm25_indices
            bm25_index_dir = os.path.join(PROJECT_ROOT, "bm25_indices")
            query_router = QueryRouter(
                business_engine=business_engine,
                php_engine=php_engine,
                js_engine=js_engine,
                blade_engine=blade_engine,
                use_hybrid_search=True,  # Enable hybrid (dense + BM25) search
                bm25_index_dir=bm25_index_dir
            )
            unified_query_engine = UnifiedQueryEngine(
                intent_classifier=intent_classifier,
                query_router=query_router,
                llm_service=llm_service
            )
            logger.info("✅ Unified Query Engine (Smart Router) Ready")
            
            # Log hybrid search status
            if query_router.use_hybrid_search:
                logger.info("✅ Hybrid Search (Dense + BM25) enabled")
            else:
                logger.warning("⚠️ Hybrid Search disabled - run scripts/build_bm25_indices.py to enable")
        else:
            logger.warning("⚠️ Unified Query Engine not initialized - some engines missing")
    except Exception as e:
        logger.error(f"Failed to initialize Unified Query Engine: {e}")

# --- Helper to Format Context ---
def format_context(results: List[Dict]) -> str:
    return "\n\n".join([f"[Source: {r['metadata'].get('file_path') or r['metadata'].get('page_name') or 'N/A'}]\n{r['content']}" for r in results])

# --- Endpoints ---

@app.get("/health")
async def health_check():
    """Health check endpoint with engine status"""
    # Check hybrid search status
    hybrid_search_status = False
    hybrid_search_indices = {}
    if unified_query_engine and unified_query_engine.query_router:
        router = unified_query_engine.query_router
        hybrid_search_status = router.use_hybrid_search
        if hybrid_search_status and router.hybrid_manager:
            hybrid_search_indices = router.hybrid_manager.get_stats().get('bm25_indices', {})
    
    return {
        "status": "healthy",
        "engines": {
            "business_docs": business_engine is not None,
            "php_code": php_engine is not None,
            "js_code": js_engine is not None,
            "blade_templates": blade_engine is not None,
            "llm_service": llm_service is not None,
            "smart_router": unified_query_engine is not None
        },
        "hybrid_search": {
            "enabled": hybrid_search_status,
            "bm25_indices": hybrid_search_indices
        }
    }

@app.post("/inference/smart", response_model=SmartQueryResponse)
async def inference_smart(request: SmartQueryRequest, db: Session = Depends(database.get_db)):
    """
    🚀 Smart Query Router - Automatically routes queries to appropriate vector DB(s)
    
    This endpoint uses LLM-based intent classification to determine which knowledge sources
    to query (business docs, PHP code, JS code, or Blade templates). It can query multiple
    sources in parallel and merge results using Reciprocal Rank Fusion (RRF).
    
    Features:
    - Automatic intent classification using function calling
    - Parallel multi-source querying
    - RRF-based result fusion
    - Context-aware response generation
    - Comprehensive inference logging
    """
    if not unified_query_engine:
        raise HTTPException(
            status_code=503, 
            detail="Smart router not initialized. Please ensure all engines are loaded."
        )
    
    # ===== SECURITY: Input Query Guardrails =====
    security_result = check_query_safety(request.query)
    
    if security_result.should_block:
        logger.warning(f"SECURITY: Blocked query with risk score {security_result.risk_score}. "
                      f"Patterns: {security_result.detected_patterns}")
        return SmartQueryResponse(
            results=[],
            llm_response=security_result.refusal_message,
            context_used="",
            routing_decision={
                "blocked": True,
                "reason": "security",
                "risk_score": security_result.risk_score,
                "risk_categories": [rc.value for rc in security_result.risk_categories]
            },
            sources_queried=[]
        )
    
    # Log if query has elevated risk but wasn't blocked
    if security_result.risk_score > 0.3:
        logger.info(f"SECURITY: Query passed with elevated risk score {security_result.risk_score}. "
                   f"Patterns: {security_result.detected_patterns}")
    # ===== END SECURITY =====
    
    # Initialize inference logger
    from inference_logger import InferenceLogger
    inference_logger = InferenceLogger(db)
    inference_logger.start_inference(request.query)
    
    try:
        # Execute smart query with routing and logging
        result = await unified_query_engine.smart_query(
            query=request.query,
            top_k=request.top_k,
            confidence_threshold=request.confidence_threshold,
            min_relevance_score=request.min_relevance_score,
            inference_logger=inference_logger
        )
        
        # Save to database if conversation_id provided
        if request.conversation_id:
            try:
                # Save user message
                crud.create_message(db, request.conversation_id, MessageRole.USER, request.query)
                # Save bot response with routing metadata
                if result['llm_response']:
                    # Include routing decision in context for future reference
                    context_with_metadata = f"[Routing: {', '.join(result['sources_queried'])}]\n\n{result['context']}"
                    crud.create_message(
                        db, 
                        request.conversation_id, 
                        MessageRole.BOT, 
                        result['llm_response'], 
                        context_with_metadata
                    )
            except Exception as e:
                logger.error(f"Error saving to database: {e}")
        
        # Save inference log
        try:
            inference_logger.finalize(success=True)
            inference_logger.save_to_db()
        except Exception as e:
            logger.error(f"Error saving inference log: {e}")
        
        return SmartQueryResponse(
            results=result['results'],
            llm_response=result['llm_response'],
            context_used=result['context'],
            routing_decision=result['routing_decision'],
            sources_queried=result['sources_queried']
        )
        
    except Exception as e:
        logger.error(f"Smart query failed: {e}", exc_info=True)
        # Log the failure
        try:
            inference_logger.finalize(success=False, error_message=str(e))
            inference_logger.save_to_db()
        except:
            pass
        raise HTTPException(status_code=500, detail=f"Smart query failed: {str(e)}")

@app.post("/inference/business", response_model=QueryResponse)
async def inference_business(request: QueryRequest, db: Session = Depends(database.get_db)):
    if not business_engine:
        raise HTTPException(status_code=503, detail="Business engine not initialized")
    
    # ===== SECURITY: Input Query Guardrails =====
    security_result = check_query_safety(request.query)
    if security_result.should_block:
        logger.warning(f"SECURITY: Blocked business query - risk: {security_result.risk_score}")
        return QueryResponse(
            results=[],
            llm_response=security_result.refusal_message,
            context_used=""
        )
    # ===== END SECURITY =====
    
    results = business_engine.query(request.query, request.top_k, request.rerank)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        # Use hardened system prompt with security preamble
        base_prompt = "You are an expert banking assistant. Answer the user query based strictly on the provided business documentation context. If the provided context contains Mermaid JS diagram code, you MUST include it in your response wrapped in a mermaid code block."
        system_prompt = get_hardened_system_prompt(base_prompt, get_banking_security_addendum())
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
    
    # ===== SECURITY: Input Query Guardrails =====
    security_result = check_query_safety(request.query)
    if security_result.should_block:
        logger.warning(f"SECURITY: Blocked PHP query - risk: {security_result.risk_score}")
        return QueryResponse(
            results=[],
            llm_response=security_result.refusal_message,
            context_used=""
        )
    # ===== END SECURITY =====
    
    results = php_engine.query(request.query, request.top_k)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        # Use hardened system prompt with security preamble
        base_prompt = "You are an expert PHP Laravel developer. Answer the user query based strictly on the provided PHP code context. Do not hallucinate."
        system_prompt = get_hardened_system_prompt(base_prompt, get_banking_security_addendum())
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
    
    # ===== SECURITY: Input Query Guardrails =====
    security_result = check_query_safety(request.query)
    if security_result.should_block:
        logger.warning(f"SECURITY: Blocked JS query - risk: {security_result.risk_score}")
        return QueryResponse(
            results=[],
            llm_response=security_result.refusal_message,
            context_used=""
        )
    # ===== END SECURITY =====
    
    results = js_engine.query(request.query, request.top_k)
    context = format_context(results)
    
    llm_response = None
    if llm_service:
        # Use hardened system prompt with security preamble
        base_prompt = "You are an expert JavaScript/React developer. Answer the user query based strictly on the provided JS code context. Do not hallucinate."
        system_prompt = get_hardened_system_prompt(base_prompt, get_banking_security_addendum())
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
    
    # ===== SECURITY: Input Query Guardrails =====
    security_result = check_query_safety(request.query)
    if security_result.should_block:
        logger.warning(f"SECURITY: Blocked Blade query - risk: {security_result.risk_score}")
        return QueryResponse(
            results=[],
            llm_response=security_result.refusal_message,
            context_used=""
        )
    # ===== END SECURITY =====
    
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
        # Use hardened system prompt with security preamble
        base_prompt = """You are an expert Laravel Blade developer and template analyst.
Answer the user query based strictly on the provided blade template context.
Guidelines:
1. Reference specific files and code sections when relevant
2. Explain blade directives (@csrf, @auth, @include, etc.) clearly
3. Highlight form handling and security features
4. Be concise but thorough
5. If context is insufficient, say so"""
        system_prompt = get_hardened_system_prompt(base_prompt, get_banking_security_addendum())
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

@app.get("/api/token-usage")
async def get_token_usage():
    """Get token usage statistics across all services"""
    stats = {
        "llm_service": None,
        "unified_query_engine": None,
        "timestamp": datetime.now().isoformat()
    }
    
    try:
        if llm_service:
            stats["llm_service"] = llm_service.get_usage_stats()
        
        if unified_query_engine and hasattr(unified_query_engine, 'intent_classifier'):
            intent_classifier = unified_query_engine.intent_classifier
            if hasattr(intent_classifier, 'rate_limiter'):
                stats["intent_classifier"] = intent_classifier.rate_limiter.get_usage_stats()
        
        return stats
    except Exception as e:
        logger.error(f"Error getting token usage: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/clear-cache")
async def clear_cache():
    """Clear all API response caches"""
    try:
        cleared = []
        
        if llm_service and hasattr(llm_service, 'rate_limiter'):
            llm_service.rate_limiter.clear_cache()
            cleared.append("llm_service")
        
        if unified_query_engine and hasattr(unified_query_engine, 'intent_classifier'):
            intent_classifier = unified_query_engine.intent_classifier
            if hasattr(intent_classifier, 'rate_limiter'):
                intent_classifier.rate_limiter.clear_cache()
                cleared.append("intent_classifier")
        
        return {"message": "Cache cleared successfully", "services": cleared}
    except Exception as e:
        logger.error(f"Error clearing cache: {e}")
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run("backend.main:app", host="0.0.0.0", port=8000, reload=True)
