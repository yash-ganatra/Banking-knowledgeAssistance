"""
Inference Logging Service

Provides comprehensive logging for the entire RAG inference pipeline.
Tracks queries, routing decisions, retrieval stages, and performance metrics.
"""

import logging
import time
from typing import List, Dict, Any, Optional
from datetime import datetime
from dataclasses import dataclass, field
from contextlib import contextmanager
from sqlalchemy.orm import Session

from models import InferenceLog, RetrievalDetail

logger = logging.getLogger(__name__)


@dataclass
class ChunkLog:
    """Log entry for a single chunk at a specific stage"""
    chunk_id: str
    source: str
    content_preview: str = ""
    file_path: Optional[str] = None
    file_name: Optional[str] = None
    class_name: Optional[str] = None
    method_name: Optional[str] = None
    
    # Scores
    initial_rank: Optional[int] = None
    initial_distance: Optional[float] = None
    bm25_score: Optional[float] = None
    bm25_rank: Optional[int] = None
    hybrid_rrf_score: Optional[float] = None
    found_by_both: bool = False
    search_methods: List[str] = field(default_factory=list)
    rrf_score: Optional[float] = None
    rrf_rank: Optional[int] = None
    cross_encoder_score: Optional[float] = None
    final_rank: Optional[int] = None
    
    stage: str = "initial"
    included_in_context: bool = False


@dataclass 
class InferenceLogEntry:
    """Complete log entry for an inference request"""
    # Request
    query: str
    endpoint: str
    processed_query: Optional[str] = None
    top_k: int = 5
    confidence_threshold: Optional[float] = None
    min_relevance_score: Optional[float] = None
    
    # Query Expansion (BM25)
    query_expansion_applied: bool = False
    expanded_query: Optional[str] = None
    expansion_reason: Optional[str] = None  # e.g., "documentation query, requires_code=False"
    
    # Routing
    primary_source: Optional[str] = None
    secondary_sources: List[str] = field(default_factory=list)
    routing_confidence: Optional[float] = None
    routing_reasoning: Optional[str] = None
    query_type: Optional[str] = None
    sources_queried: List[str] = field(default_factory=list)
    
    # Retrieval Stats
    total_chunks_retrieved: int = 0
    chunks_after_filtering: int = 0
    chunks_after_reranking: int = 0
    
    # Hybrid Search
    hybrid_search_used: bool = False
    dense_results_count: int = 0
    sparse_results_count: int = 0
    found_by_both_count: int = 0
    
    # Timing (in ms)
    total_time_ms: float = 0
    routing_time_ms: float = 0
    retrieval_time_ms: float = 0
    reranking_time_ms: float = 0
    llm_time_ms: float = 0
    
    # Graph Enhancement
    graph_used: bool = False
    graph_context: Optional[Dict[str, Any]] = None
    
    # User/Session
    user_id: Optional[int] = None
    conversation_id: Optional[int] = None
    session_id: Optional[str] = None
    
    # Status
    success: bool = True
    error_message: Optional[str] = None
    
    # Chunk details at each stage
    chunks: List[ChunkLog] = field(default_factory=list)
    
    # Internal timing helpers
    _start_time: float = field(default_factory=time.time)
    _stage_start: float = 0


class InferenceLogger:
    """
    Service for logging inference pipeline execution.
    Tracks all stages from query to response.
    """
    
    def __init__(self, db_session: Optional[Session] = None):
        """
        Initialize the inference logger.
        
        Args:
            db_session: SQLAlchemy session for persisting logs
        """
        self.db = db_session
        self.current_log: Optional[InferenceLogEntry] = None
    
    def start_inference(
        self,
        query: str,
        endpoint: str = "smart",
        top_k: int = 5,
        confidence_threshold: Optional[float] = None,
        min_relevance_score: Optional[float] = None,
        user_id: Optional[int] = None,
        conversation_id: Optional[int] = None,
        session_id: Optional[str] = None
    ) -> InferenceLogEntry:
        """
        Start logging a new inference request.
        
        Args:
            query: User's query
            endpoint: API endpoint (smart, php, js, blade, business)
            top_k: Number of results requested
            confidence_threshold: Confidence threshold for routing
            min_relevance_score: Minimum relevance score for filtering
            user_id: Optional user ID
            conversation_id: Optional conversation ID
            session_id: Optional session ID for anonymous tracking
            
        Returns:
            InferenceLogEntry to track this inference
        """
        self.current_log = InferenceLogEntry(
            query=query,
            endpoint=endpoint,
            top_k=top_k,
            confidence_threshold=confidence_threshold,
            min_relevance_score=min_relevance_score,
            user_id=user_id,
            conversation_id=conversation_id,
            session_id=session_id,
            _start_time=time.time()
        )
        
        logger.debug(f"Started inference log for query: {query[:100]}...")
        return self.current_log
    
    def log_preprocessing(self, processed_query: str) -> None:
        """Log query after preprocessing (abbreviation expansion, etc.)"""
        if self.current_log:
            self.current_log.processed_query = processed_query
    
    def log_query_expansion(
        self,
        original_query: str,
        expanded_query: str,
        was_expanded: bool,
        query_type: str,
        requires_code: bool
    ) -> None:
        """
        Log query expansion decision and result.
        
        Args:
            original_query: The original user query
            expanded_query: The query after expansion (may be same as original)
            was_expanded: Whether expansion was actually applied
            query_type: Intent classification query type
            requires_code: Whether the query requires code
        """
        if not self.current_log:
            return
        
        self.current_log.query_expansion_applied = was_expanded
        
        if was_expanded:
            self.current_log.expanded_query = expanded_query
            self.current_log.expansion_reason = f"query_type={query_type}, requires_code={requires_code}"
            logger.info(
                f"Query expansion applied: '{original_query}' → '{expanded_query}' "
                f"(reason: {self.current_log.expansion_reason})"
            )
        else:
            self.current_log.expanded_query = None
            self.current_log.expansion_reason = f"Skipped: query_type={query_type}, requires_code={requires_code}"
            logger.info(
                f"Query expansion skipped for: '{original_query}' "
                f"(reason: code-specific query, type={query_type}, requires_code={requires_code})"
            )
    
    def log_routing_decision(
        self,
        primary_source: str,
        secondary_sources: List[str],
        confidence: float,
        reasoning: str,
        query_type: str,
        routing_time_ms: float
    ) -> None:
        """Log the intent classification / routing decision"""
        if self.current_log:
            self.current_log.primary_source = primary_source
            self.current_log.secondary_sources = secondary_sources
            self.current_log.routing_confidence = confidence
            self.current_log.routing_reasoning = reasoning
            self.current_log.query_type = query_type
            self.current_log.routing_time_ms = routing_time_ms
    
    def log_sources_queried(self, sources: List[str]) -> None:
        """Log which sources were actually queried"""
        if self.current_log:
            self.current_log.sources_queried = sources

    def log_graph_context(self, context: Any) -> None:
        """Log graph enhancement context"""
        if self.current_log and context:
            self.current_log.graph_used = True
            # Convert context object to dict if needed
            if hasattr(context, 'to_dict'):
                self.current_log.graph_context = context.to_dict()
            elif isinstance(context, dict):
                self.current_log.graph_context = context
            else:
                # Fallback for other types
                try:
                    self.current_log.graph_context = {
                        "related_entities": getattr(context, "related_entities", []),
                        "traversal_depth": getattr(context, "traversal_depth", 0),
                    }
                except:
                    self.current_log.graph_context = {"raw": str(context)}
    
    def log_initial_retrieval(
        self,
        source: str,
        results: List[Dict[str, Any]],
        retrieval_time_ms: float
    ) -> None:
        """Log initial retrieval results from a source (before any fusion/filtering)"""
        if not self.current_log:
            return
        
        self.current_log.retrieval_time_ms += retrieval_time_ms
        
        for rank, result in enumerate(results, start=1):
            chunk_log = self._create_chunk_log(result, source, rank, "initial")
            chunk_log.initial_rank = rank
            chunk_log.initial_distance = result.get('distance')
            self.current_log.chunks.append(chunk_log)
            self.current_log.total_chunks_retrieved += 1
    
    def log_hybrid_search(
        self,
        source: str,
        dense_count: int,
        sparse_count: int,
        merged_results: List[Dict[str, Any]]
    ) -> None:
        """Log hybrid search fusion results"""
        if not self.current_log:
            return
        
        self.current_log.hybrid_search_used = True
        self.current_log.dense_results_count += dense_count
        self.current_log.sparse_results_count += sparse_count
        
        found_by_both = 0
        for rank, result in enumerate(merged_results, start=1):
            if result.get('found_by_both', False):
                found_by_both += 1
            
            # Update existing chunk or create new
            chunk_log = self._find_or_create_chunk_log(result, source)
            chunk_log.bm25_score = result.get('bm25_score')
            chunk_log.bm25_rank = result.get('bm25_rank')
            chunk_log.hybrid_rrf_score = result.get('hybrid_rrf_score')
            chunk_log.found_by_both = result.get('found_by_both', False)
            chunk_log.search_methods = result.get('search_methods', [])
            chunk_log.stage = "after_hybrid"
        
        self.current_log.found_by_both_count += found_by_both
    
    def log_rrf_fusion(
        self,
        merged_results: List[Dict[str, Any]]
    ) -> None:
        """Log RRF fusion results (multi-source) - these are the chunks BEFORE reranking"""
        if not self.current_log:
            return
        
        self.current_log.chunks_after_filtering = len(merged_results)
        
        for rank, result in enumerate(merged_results, start=1):
            chunk_log = self._find_or_create_chunk_log(result, result.get('source', 'unknown'))
            chunk_log.rrf_score = result.get('rrf_score')
            chunk_log.rrf_rank = rank
            chunk_log.stage = "before_rerank"  # Mark as before reranking stage
    
    def log_before_reranking(
        self,
        chunks: List[Dict[str, Any]]
    ) -> None:
        """
        Explicitly log chunks before cross-encoder reranking.
        Call this before log_reranking to capture the pre-rerank state.
        """
        if not self.current_log:
            return
        
        # Create copies of chunks at the before_rerank stage
        for rank, result in enumerate(chunks, start=1):
            source = result.get('source', 'unknown')
            chunk_id = result.get('id', f"{source}_{rank}")
            
            # Create a new chunk log entry for before_rerank stage
            chunk_log = ChunkLog(
                chunk_id=f"{chunk_id}_before",
                source=source,
                content_preview=result.get('content', '')[:500] if result.get('content') else "",
                file_path=result.get('metadata', {}).get('file_path'),
                file_name=result.get('metadata', {}).get('file_name'),
                class_name=result.get('metadata', {}).get('class_name'),
                method_name=result.get('metadata', {}).get('method_name'),
                initial_distance=result.get('distance'),
                rrf_score=result.get('rrf_score'),
                rrf_rank=rank,
                stage="before_rerank"
            )
            self.current_log.chunks.append(chunk_log)
    
    def log_reranking(
        self,
        reranked_results: List[Dict[str, Any]],
        reranking_time_ms: float
    ) -> None:
        """Log cross-encoder reranking results"""
        if not self.current_log:
            return
        
        self.current_log.reranking_time_ms = reranking_time_ms
        self.current_log.chunks_after_reranking = len(reranked_results)
        
        for rank, result in enumerate(reranked_results, start=1):
            chunk_log = self._find_or_create_chunk_log(result, result.get('source', 'unknown'))
            chunk_log.cross_encoder_score = result.get('cross_encoder_score')
            chunk_log.final_rank = rank
            chunk_log.stage = "final"
            chunk_log.included_in_context = True
    
    def log_llm_generation(self, llm_time_ms: float) -> None:
        """Log LLM response generation time"""
        if self.current_log:
            self.current_log.llm_time_ms = llm_time_ms
    
    def log_error(self, error_message: str) -> None:
        """Log an error during inference"""
        if self.current_log:
            self.current_log.success = False
            self.current_log.error_message = error_message
    
    def finalize(self, success: bool = True, error_message: Optional[str] = None) -> None:
        """
        Mark the inference as complete with success/failure status.
        
        Args:
            success: Whether the inference was successful
            error_message: Error message if failed
        """
        if self.current_log:
            self.current_log.success = success
            if error_message:
                self.current_log.error_message = error_message
            self.current_log.total_time_ms = (time.time() - self.current_log._start_time) * 1000

    def finish_inference(self) -> Optional[InferenceLogEntry]:
        """
        Finish logging and calculate total time.
        Returns the completed log entry.
        """
        if not self.current_log:
            return None
        
        self.current_log.total_time_ms = (time.time() - self.current_log._start_time) * 1000
        
        log_entry = self.current_log
        self.current_log = None
        
        logger.info(
            f"Inference completed: {log_entry.endpoint} | "
            f"Sources: {log_entry.sources_queried} | "
            f"Chunks: {log_entry.total_chunks_retrieved} → {log_entry.chunks_after_reranking} | "
            f"Time: {log_entry.total_time_ms:.0f}ms | "
            f"Hybrid: {log_entry.hybrid_search_used}"
        )
        
        return log_entry
    
    def save_to_db(self, log_entry: Optional[InferenceLogEntry] = None) -> Optional[int]:
        """
        Save the log entry to database.
        
        Args:
            log_entry: Completed inference log entry (uses current_log if not provided)
            
        Returns:
            ID of the saved log, or None if save failed
        """
        if not self.db:
            logger.warning("No database session available for saving inference log")
            return None
        
        # Use current_log if log_entry not provided
        if log_entry is None:
            log_entry = self.current_log
        
        if log_entry is None:
            logger.warning("No log entry to save")
            return None
        
        try:
            # Create main log record
            db_log = InferenceLog(
                query=log_entry.query,
                processed_query=log_entry.processed_query,
                endpoint=log_entry.endpoint,
                top_k=log_entry.top_k,
                confidence_threshold=log_entry.confidence_threshold,
                min_relevance_score=log_entry.min_relevance_score,
                # Query expansion fields
                query_expansion_applied=log_entry.query_expansion_applied,
                expanded_query=log_entry.expanded_query,
                expansion_reason=log_entry.expansion_reason,
                # Routing fields
                primary_source=log_entry.primary_source,
                secondary_sources=log_entry.secondary_sources,
                routing_confidence=log_entry.routing_confidence,
                routing_reasoning=log_entry.routing_reasoning,
                query_type=log_entry.query_type,
                sources_queried=log_entry.sources_queried,
                total_chunks_retrieved=log_entry.total_chunks_retrieved,
                chunks_after_filtering=log_entry.chunks_after_filtering,
                chunks_after_reranking=log_entry.chunks_after_reranking,
                hybrid_search_used=log_entry.hybrid_search_used,
                dense_results_count=log_entry.dense_results_count,
                sparse_results_count=log_entry.sparse_results_count,
                found_by_both_count=log_entry.found_by_both_count,
                # Graph Enhancement
                graph_used=log_entry.graph_used,
                graph_context=log_entry.graph_context,
                
                total_time_ms=log_entry.total_time_ms,
                routing_time_ms=log_entry.routing_time_ms,
                retrieval_time_ms=log_entry.retrieval_time_ms,
                reranking_time_ms=log_entry.reranking_time_ms,
                llm_time_ms=log_entry.llm_time_ms,
                user_id=log_entry.user_id,
                conversation_id=log_entry.conversation_id,
                session_id=log_entry.session_id,
                success=log_entry.success,
                error_message=log_entry.error_message
            )
            
            self.db.add(db_log)
            self.db.flush()  # Get the ID
            
            # Save chunk details at key stages: before_rerank and final
            # This allows comparing chunks before and after cross-encoder reranking
            stages_to_save = ["before_rerank", "final"]
            chunks_to_save = [c for c in log_entry.chunks if c.stage in stages_to_save]
            for chunk in chunks_to_save:
                db_detail = RetrievalDetail(
                    inference_log_id=db_log.id,
                    chunk_id=chunk.chunk_id,
                    source=chunk.source,
                    content_preview=chunk.content_preview[:500] if chunk.content_preview else None,
                    file_path=chunk.file_path,
                    file_name=chunk.file_name,
                    class_name=chunk.class_name,
                    method_name=chunk.method_name,
                    initial_rank=chunk.initial_rank,
                    initial_distance=chunk.initial_distance,
                    bm25_score=chunk.bm25_score,
                    bm25_rank=chunk.bm25_rank,
                    hybrid_rrf_score=chunk.hybrid_rrf_score,
                    found_by_both=chunk.found_by_both,
                    search_methods=chunk.search_methods,
                    rrf_score=chunk.rrf_score,
                    rrf_rank=chunk.rrf_rank,
                    cross_encoder_score=chunk.cross_encoder_score,
                    final_rank=chunk.final_rank,
                    stage=chunk.stage,
                    included_in_context=chunk.included_in_context
                )
                self.db.add(db_detail)
            
            self.db.commit()
            
            logger.debug(f"Saved inference log {db_log.id} with {len(chunks_to_save)} chunk details")
            return db_log.id
            
        except Exception as e:
            logger.error(f"Failed to save inference log: {e}")
            self.db.rollback()
            return None
    
    def _create_chunk_log(
        self, 
        result: Dict[str, Any], 
        source: str, 
        rank: int,
        stage: str
    ) -> ChunkLog:
        """Create a ChunkLog from a result dict"""
        metadata = result.get('metadata', {})
        content = result.get('content', '')
        
        return ChunkLog(
            chunk_id=result.get('id', f"{source}_{rank}"),
            source=source,
            content_preview=content[:500] if content else "",
            file_path=metadata.get('file_path'),
            file_name=metadata.get('file_name'),
            class_name=metadata.get('class_name'),
            method_name=metadata.get('method_name'),
            stage=stage
        )
    
    def _find_or_create_chunk_log(
        self, 
        result: Dict[str, Any], 
        source: str
    ) -> ChunkLog:
        """Find existing chunk log or create new one"""
        if not self.current_log:
            return self._create_chunk_log(result, source, 0, "unknown")
        
        chunk_id = result.get('id', '')
        
        # Find existing
        for chunk in self.current_log.chunks:
            if chunk.chunk_id == chunk_id:
                return chunk
        
        # Create new
        chunk_log = self._create_chunk_log(result, source, len(self.current_log.chunks) + 1, "initial")
        self.current_log.chunks.append(chunk_log)
        return chunk_log


@contextmanager
def inference_logging(
    db_session: Optional[Session],
    query: str,
    endpoint: str,
    **kwargs
):
    """
    Context manager for inference logging.
    
    Usage:
        with inference_logging(db, query, "smart", top_k=5) as log:
            log.log_routing_decision(...)
            log.log_initial_retrieval(...)
            # ... inference pipeline ...
        # Log is automatically finished and saved
    """
    logger_instance = InferenceLogger(db_session)
    log_entry = logger_instance.start_inference(query, endpoint, **kwargs)
    
    try:
        yield logger_instance
    except Exception as e:
        logger_instance.log_error(str(e))
        raise
    finally:
        finished_log = logger_instance.finish_inference()
        if finished_log and db_session:
            logger_instance.save_to_db(finished_log)
