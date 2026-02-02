"""
API endpoints for inference logging and monitoring
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.orm import Session
from sqlalchemy import desc
from typing import List, Optional
from datetime import datetime, timedelta
from pydantic import BaseModel

from database import get_db
from models import InferenceLog, RetrievalDetail

router = APIRouter(prefix="/inference-logs", tags=["Inference Logs"])

# ============================================================================
# Pydantic Response Models
# ============================================================================

class RetrievalDetailResponse(BaseModel):
    id: int
    inference_log_id: int
    chunk_id: str
    source: str
    content_preview: Optional[str]
    
    # Metadata
    file_path: Optional[str]
    file_name: Optional[str]
    class_name: Optional[str]
    method_name: Optional[str]
    
    # Scores
    initial_rank: Optional[int]
    initial_distance: Optional[float]
    bm25_score: Optional[float]
    bm25_rank: Optional[int]
    hybrid_rrf_score: Optional[float]
    found_by_both: Optional[bool]
    search_methods: Optional[List[str]]
    rrf_score: Optional[float]
    rrf_rank: Optional[int]
    cross_encoder_score: Optional[float]
    final_rank: Optional[int]
    
    # Stage
    stage: str
    included_in_context: bool
    
    created_at: datetime
    
    class Config:
        from_attributes = True

class InferenceLogResponse(BaseModel):
    id: int
    session_id: Optional[str]
    
    # Query info
    query: str
    processed_query: Optional[str]
    endpoint: str
    top_k: Optional[int]
    confidence_threshold: Optional[float]
    min_relevance_score: Optional[float]
    
    # Query expansion (BM25)
    query_expansion_applied: Optional[bool] = None
    expanded_query: Optional[str] = None
    expansion_reason: Optional[str] = None
    
    # Routing
    primary_source: Optional[str]
    secondary_sources: Optional[List[str]]
    routing_confidence: Optional[float]
    routing_reasoning: Optional[str]
    query_type: Optional[str]
    sources_queried: Optional[List[str]]
    
    # Stats
    total_chunks_retrieved: Optional[int]
    chunks_after_filtering: Optional[int]
    chunks_after_reranking: Optional[int]
    
    # Hybrid search
    hybrid_search_used: bool
    dense_results_count: Optional[int]
    sparse_results_count: Optional[int]
    found_by_both_count: Optional[int]
    
    # Timing
    total_time_ms: Optional[float]
    routing_time_ms: Optional[float]
    retrieval_time_ms: Optional[float]
    reranking_time_ms: Optional[float]
    llm_time_ms: Optional[float]
    
    # Status
    success: bool
    error_message: Optional[str]
    
    # Timestamps
    created_at: datetime
    
    class Config:
        from_attributes = True

class InferenceLogDetailResponse(InferenceLogResponse):
    retrieval_details: List[RetrievalDetailResponse]
    
    class Config:
        from_attributes = True

class InferenceLogSummary(BaseModel):
    """Summary stats for a time period"""
    total_queries: int
    successful_queries: int
    failed_queries: int
    avg_response_time_ms: float
    avg_chunks_retrieved: float
    sources_breakdown: dict
    query_types_breakdown: dict

# ============================================================================
# API Endpoints
# ============================================================================

@router.get("/", response_model=List[InferenceLogResponse])
async def list_inference_logs(
    db: Session = Depends(get_db),
    skip: int = Query(0, ge=0),
    limit: int = Query(50, ge=1, le=200),
    session_id: Optional[str] = None,
    success_only: Optional[bool] = None,
    source: Optional[str] = None,
    query_type: Optional[str] = None,
    hours_ago: Optional[int] = Query(None, description="Filter logs from last N hours")
):
    """
    List inference logs with filtering options
    """
    query = db.query(InferenceLog)
    
    # Apply filters
    if session_id:
        query = query.filter(InferenceLog.session_id == session_id)
    
    if success_only is not None:
        query = query.filter(InferenceLog.success == success_only)
    
    if source:
        # Filter by source in sources_queried JSON array
        query = query.filter(InferenceLog.sources_queried.contains([source]))
    
    if query_type:
        query = query.filter(InferenceLog.query_type == query_type)
    
    if hours_ago:
        cutoff = datetime.utcnow() - timedelta(hours=hours_ago)
        query = query.filter(InferenceLog.created_at >= cutoff)
    
    # Order by most recent first
    query = query.order_by(desc(InferenceLog.created_at))
    
    # Paginate
    logs = query.offset(skip).limit(limit).all()
    
    return logs

@router.get("/summary", response_model=InferenceLogSummary)
async def get_logs_summary(
    db: Session = Depends(get_db),
    hours_ago: int = Query(24, description="Summary for last N hours")
):
    """
    Get summary statistics for inference logs
    """
    cutoff = datetime.utcnow() - timedelta(hours=hours_ago)
    
    logs = db.query(InferenceLog).filter(InferenceLog.created_at >= cutoff).all()
    
    if not logs:
        return InferenceLogSummary(
            total_queries=0,
            successful_queries=0,
            failed_queries=0,
            avg_response_time_ms=0,
            avg_chunks_retrieved=0,
            sources_breakdown={},
            query_types_breakdown={}
        )
    
    # Calculate stats
    successful = sum(1 for log in logs if log.success)
    failed = len(logs) - successful
    
    # Average response time (only for successful queries with timing)
    times = [log.total_time_ms for log in logs if log.total_time_ms and log.success]
    avg_time = sum(times) / len(times) if times else 0
    
    # Average chunks
    chunks = [log.chunks_after_reranking for log in logs if log.chunks_after_reranking]
    avg_chunks = sum(chunks) / len(chunks) if chunks else 0
    
    # Sources breakdown
    sources_count = {}
    for log in logs:
        if log.sources_queried:
            for source in log.sources_queried:
                sources_count[source] = sources_count.get(source, 0) + 1
    
    # Query types breakdown
    query_types_count = {}
    for log in logs:
        qt = log.query_type or "unknown"
        query_types_count[qt] = query_types_count.get(qt, 0) + 1
    
    return InferenceLogSummary(
        total_queries=len(logs),
        successful_queries=successful,
        failed_queries=failed,
        avg_response_time_ms=round(avg_time, 2),
        avg_chunks_retrieved=round(avg_chunks, 2),
        sources_breakdown=sources_count,
        query_types_breakdown=query_types_count
    )

@router.get("/{log_id}", response_model=InferenceLogDetailResponse)
async def get_inference_log_detail(
    log_id: int,
    db: Session = Depends(get_db)
):
    """
    Get detailed inference log with all retrieval stages
    """
    log = db.query(InferenceLog).filter(InferenceLog.id == log_id).first()
    
    if not log:
        raise HTTPException(status_code=404, detail="Inference log not found")
    
    # Get retrieval details
    details = db.query(RetrievalDetail).filter(
        RetrievalDetail.inference_log_id == log_id
    ).order_by(RetrievalDetail.created_at).all()
    
    return InferenceLogDetailResponse(
        **{c.name: getattr(log, c.name) for c in log.__table__.columns},
        retrieval_details=[RetrievalDetailResponse(
            **{c.name: getattr(d, c.name) for c in d.__table__.columns}
        ) for d in details]
    )

@router.get("/{log_id}/pipeline", response_model=dict)
async def get_pipeline_visualization(
    log_id: int,
    db: Session = Depends(get_db)
):
    """
    Get structured pipeline data for visualization
    """
    log = db.query(InferenceLog).filter(InferenceLog.id == log_id).first()
    
    if not log:
        raise HTTPException(status_code=404, detail="Inference log not found")
    
    details = db.query(RetrievalDetail).filter(
        RetrievalDetail.inference_log_id == log_id
    ).order_by(RetrievalDetail.created_at).all()
    
    # Build pipeline stages
    stages = []
    
    # Stage 1: Query preprocessing
    stages.append({
        "stage": "preprocessing",
        "name": "Query Preprocessing",
        "input": log.query,
        "output": log.processed_query or log.query,
        "time_ms": None
    })
    
    # Stage 2: Routing
    stages.append({
        "stage": "routing",
        "name": "Smart Routing",
        "input": log.processed_query or log.query,
        "output": {
            "primary_source": log.primary_source,
            "secondary_sources": log.secondary_sources,
            "confidence": log.routing_confidence,
            "reasoning": log.routing_reasoning,
            "sources_queried": log.sources_queried
        },
        "time_ms": log.routing_time_ms
    })
    
    # Stage 3: Retrieval stats
    stages.append({
        "stage": "retrieval",
        "name": "Vector DB Retrieval",
        "sources": log.sources_queried,
        "total_chunks": log.total_chunks_retrieved,
        "hybrid_search_used": log.hybrid_search_used,
        "dense_results": log.dense_results_count,
        "sparse_results": log.sparse_results_count,
        "found_by_both": log.found_by_both_count,
        "time_ms": log.retrieval_time_ms
    })
    
    # Stage 4: Filtering and reranking
    stages.append({
        "stage": "reranking",
        "name": "Cross-Encoder Reranking",
        "chunks_before": log.chunks_after_filtering,
        "chunks_after": log.chunks_after_reranking,
        "time_ms": log.reranking_time_ms
    })
    
    # Stage 5: LLM generation
    stages.append({
        "stage": "llm_generation",
        "name": "LLM Response Generation",
        "time_ms": log.llm_time_ms
    })
    
    # Get chunk details grouped by stage (before_rerank vs final)
    chunks_before_rerank = []
    chunks_after_rerank = []
    
    for detail in details:
        chunk_data = {
            "chunk_id": detail.chunk_id,
            "source": detail.source,
            "file_path": detail.file_path,
            "file_name": detail.file_name,
            "class_name": detail.class_name,
            "method_name": detail.method_name,
            "content_preview": detail.content_preview[:300] if detail.content_preview else None,
            "initial_rank": detail.initial_rank,
            "initial_distance": detail.initial_distance,
            "bm25_score": detail.bm25_score,
            "found_by_both": detail.found_by_both,
            "rrf_score": detail.rrf_score,
            "rrf_rank": detail.rrf_rank,
            "cross_encoder_score": detail.cross_encoder_score,
            "final_rank": detail.final_rank,
            "stage": detail.stage,
            "included_in_context": detail.included_in_context
        }
        
        if detail.stage == "before_rerank":
            chunks_before_rerank.append(chunk_data)
        elif detail.stage == "final":
            chunks_after_rerank.append(chunk_data)
    
    # Sort by rank
    chunks_before_rerank.sort(key=lambda x: x.get('rrf_rank') or 999)
    chunks_after_rerank.sort(key=lambda x: x.get('final_rank') or 999)
    
    return {
        "log_id": log.id,
        "created_at": log.created_at.isoformat(),
        "query": log.query,
        "success": log.success,
        "total_time_ms": log.total_time_ms,
        "stages": stages,
        "chunks_before_rerank": chunks_before_rerank,
        "chunks_after_rerank": chunks_after_rerank,
        "rerank_summary": {
            "before_count": len(chunks_before_rerank),
            "after_count": len(chunks_after_rerank),
            "filtered_out": len(chunks_before_rerank) - len(chunks_after_rerank)
        }
    }

@router.delete("/{log_id}")
async def delete_inference_log(
    log_id: int,
    db: Session = Depends(get_db)
):
    """
    Delete an inference log and its details
    """
    log = db.query(InferenceLog).filter(InferenceLog.id == log_id).first()
    
    if not log:
        raise HTTPException(status_code=404, detail="Inference log not found")
    
    # Delete details first (cascade should handle this, but being explicit)
    db.query(RetrievalDetail).filter(RetrievalDetail.inference_log_id == log_id).delete()
    db.delete(log)
    db.commit()
    
    return {"message": "Log deleted successfully"}

@router.delete("/")
async def cleanup_old_logs(
    db: Session = Depends(get_db),
    days_old: int = Query(7, description="Delete logs older than N days")
):
    """
    Clean up old inference logs
    """
    cutoff = datetime.utcnow() - timedelta(days=days_old)
    
    # Get IDs of logs to delete
    old_logs = db.query(InferenceLog.id).filter(InferenceLog.created_at < cutoff).all()
    log_ids = [log.id for log in old_logs]
    
    if log_ids:
        # Delete details first
        db.query(RetrievalDetail).filter(RetrievalDetail.inference_log_id.in_(log_ids)).delete(synchronize_session=False)
        # Delete logs
        deleted = db.query(InferenceLog).filter(InferenceLog.id.in_(log_ids)).delete(synchronize_session=False)
        db.commit()
        return {"message": f"Deleted {deleted} logs older than {days_old} days"}
    
    return {"message": "No old logs to delete"}

# ============================================================================
# Helper functions
# ============================================================================

def _stage_name(stage: str) -> str:
    """Convert stage code to readable name"""
    names = {
        "initial_retrieval": "Initial Retrieval",
        "hybrid_search": "Hybrid Search (BM25 + Dense)",
        "rrf_fusion": "RRF Fusion",
        "reranking": "Cross-Encoder Reranking",
        "filtering": "Quality Filtering"
    }
    return names.get(stage, stage.replace("_", " ").title())
