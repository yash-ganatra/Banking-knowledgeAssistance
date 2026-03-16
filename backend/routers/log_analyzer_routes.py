"""
Log Analyzer API Routes
Provides endpoints for uploading a log file, parsing/previewing errors,
and running full LLM-powered root cause analysis.
"""

import logging
import asyncio
import hashlib
from typing import Optional, List
from datetime import datetime
from fastapi import APIRouter, UploadFile, File, HTTPException, Form, Depends
from pydantic import BaseModel, Field

from log_analyzer import LogAnalyzer
from database import get_db, SessionLocal
from sqlalchemy.orm import Session
from models import LogAnalysisJob

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/api/log-analyzer", tags=["Log Analyzer"])

# Module-level reference — set by main.py during startup
_log_analyzer: Optional[LogAnalyzer] = None


def init_log_analyzer(log_analyzer: LogAnalyzer):
    """Called from main.py startup to inject the LogAnalyzer instance."""
    global _log_analyzer
    _log_analyzer = log_analyzer
    logger.info("✅ Log Analyzer routes initialized")


def _get_analyzer() -> LogAnalyzer:
    if _log_analyzer is None:
        raise HTTPException(
            status_code=503,
            detail="Log Analyzer not initialized. Ensure PHP engine and LLM service are available."
        )
    return _log_analyzer


# ---- Response Models ----

class ParsedErrorSummary(BaseModel):
    fingerprint: str
    error_message: str
    exception_class: Optional[str]
    origin_file: Optional[str]
    origin_line: Optional[int]
    triggering_function: Optional[str]
    view_file: Optional[str]
    occurrence_count: int
    first_seen: str
    last_seen: str
    app_stack_frames: List[str]
    severity_hint: str


class ParseLogResponse(BaseModel):
    total_entries: int
    unique_errors: int
    errors: List[dict]
    analysis_job_id: Optional[int] = None
    analysis_status: Optional[str] = None


class AnalyzeLogResponse(BaseModel):
    total_entries: int
    unique_errors: int
    analyzed_count: int
    analyses: List[dict]


class AnalysisJobResponse(BaseModel):
    analysis_job_id: int
    status: str
    total_entries: Optional[int] = None
    unique_errors: Optional[int] = None
    analyzed_count: int = 0
    analyses: List[dict] = Field(default_factory=list)
    error_message: Optional[str] = None
    created_at: datetime
    updated_at: datetime


# ---- Endpoints ----

MAX_FILE_SIZE = 10 * 1024 * 1024  # 10 MB


def _get_cached_analyses_by_fingerprint(
    db: Session,
    fingerprints: List[str],
    exclude_job_id: Optional[int] = None,
    limit_jobs: int = 200
) -> dict:
    """
    Reuse previously completed analyses for identical fingerprints.
    This reduces latency without reducing retrieval/context quality.
    """
    if not fingerprints:
        return {}

    fp_set = set(fingerprints)
    cached = {}

    query = db.query(LogAnalysisJob).filter(
        LogAnalysisJob.status == "completed",
        LogAnalysisJob.analysis_result.isnot(None)
    )
    if exclude_job_id is not None:
        query = query.filter(LogAnalysisJob.id != exclude_job_id)

    recent_jobs = query.order_by(LogAnalysisJob.created_at.desc()).limit(limit_jobs).all()

    for job in recent_jobs:
        result = job.analysis_result if isinstance(job.analysis_result, dict) else {}
        analyses = result.get("analyses", []) if isinstance(result, dict) else []
        if not isinstance(analyses, list):
            continue

        for analysis in analyses:
            if not isinstance(analysis, dict):
                continue
            fp = analysis.get("fingerprint")
            if fp in fp_set and fp not in cached:
                cached[fp] = analysis
                if len(cached) == len(fp_set):
                    return cached

    return cached


async def _run_analysis_job(job_id: int, log_text: str, top_k_context: int = 5):
    """Background coroutine: analyze all parsed errors and persist final result."""
    db = SessionLocal()
    try:
        analyzer = _get_analyzer()

        # Parse once to get ordered fingerprints for this uploaded file
        parsed = analyzer.parse_log(log_text)
        parsed_errors = parsed.get("errors", [])
        ordered_fingerprints = [e.get("fingerprint") for e in parsed_errors if e.get("fingerprint")]

        # Reuse prior completed analyses for identical fingerprints
        cached_by_fp = _get_cached_analyses_by_fingerprint(
            db=db,
            fingerprints=ordered_fingerprints,
            exclude_job_id=job_id,
            limit_jobs=200
        )

        missing_fps = [fp for fp in ordered_fingerprints if fp not in cached_by_fp]

        fresh_analyses_by_fp = {}
        if missing_fps:
            # Full-quality analysis for misses only (no fast-mode compromise)
            fresh_result = await analyzer.analyze_log(
                log_content=log_text,
                selected_errors=missing_fps,
                top_k_context=top_k_context,
                fast_mode=False,
                concurrency=2
            )
            for analysis in fresh_result.get("analyses", []):
                fp = analysis.get("fingerprint")
                if fp:
                    fresh_analyses_by_fp[fp] = analysis

        merged_analyses = []
        for fp in ordered_fingerprints:
            if fp in cached_by_fp:
                merged_analyses.append(cached_by_fp[fp])
            elif fp in fresh_analyses_by_fp:
                merged_analyses.append(fresh_analyses_by_fp[fp])

        result = {
            "total_entries": parsed.get("total_entries", 0),
            "unique_errors": parsed.get("unique_errors", 0),
            "analyzed_count": len(merged_analyses),
            "analyses": merged_analyses,
        }

        job = db.query(LogAnalysisJob).filter(LogAnalysisJob.id == job_id).first()
        if not job:
            logger.warning(f"Log analysis job {job_id} not found when trying to save results")
            return

        job.status = "completed"
        job.total_entries = result.get("total_entries")
        job.unique_errors = result.get("unique_errors")
        job.analysis_result = result
        job.error_message = None
        db.commit()
        logger.info(f"✅ Log analysis job {job_id} completed")
    except Exception as e:
        logger.error(f"Log analysis job {job_id} failed: {e}", exc_info=True)
        job = db.query(LogAnalysisJob).filter(LogAnalysisJob.id == job_id).first()
        if job:
            job.status = "failed"
            job.error_message = str(e)
            db.commit()
    finally:
        db.close()


@router.post("/parse", response_model=ParseLogResponse)
async def parse_log(
    file: UploadFile = File(...),
    db: Session = Depends(get_db)
):
    """
    Parse and deduplicate a log file (no LLM calls).
    Returns a summary with unique errors, occurrence counts, and severity hints.
    Use this for a preview before running full analysis.
    
    Accepted file types: .log, .txt
    Max file size: 10 MB
    """
    analyzer = _get_analyzer()
    
    # Validate file type
    if file.filename and not file.filename.endswith(('.log', '.txt')):
        raise HTTPException(
            status_code=400,
            detail="Unsupported file type. Please upload a .log or .txt file."
        )
    
    # Read content with size check
    content = await file.read()
    if len(content) > MAX_FILE_SIZE:
        raise HTTPException(
            status_code=413,
            detail=f"File too large. Maximum size is {MAX_FILE_SIZE // (1024*1024)} MB."
        )
    
    try:
        log_text = content.decode('utf-8', errors='replace')
    except Exception:
        raise HTTPException(status_code=400, detail="Could not decode file content as text.")
    
    try:
        result = analyzer.parse_log(log_text)

        # If this exact file was already analyzed, reuse stored result
        file_hash = hashlib.sha256(log_text.encode("utf-8")).hexdigest()
        existing = (
            db.query(LogAnalysisJob)
            .filter(LogAnalysisJob.file_hash == file_hash)
            .order_by(LogAnalysisJob.created_at.desc())
            .first()
        )

        if existing and existing.status == "completed" and existing.analysis_result:
            return ParseLogResponse(
                **result,
                analysis_job_id=existing.id,
                analysis_status=existing.status
            )

        # Create a new job and auto-run full analysis in background
        job = LogAnalysisJob(
            file_name=file.filename,
            file_hash=file_hash,
            status="processing",
            total_entries=result.get("total_entries"),
            unique_errors=result.get("unique_errors"),
            parse_result=result,
            analysis_result=None,
            error_message=None,
        )
        db.add(job)
        db.commit()
        db.refresh(job)

        asyncio.create_task(_run_analysis_job(job.id, log_text, top_k_context=5))

        return ParseLogResponse(
            **result,
            analysis_job_id=job.id,
            analysis_status=job.status
        )
    except Exception as e:
        logger.error(f"Log parsing failed: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=f"Failed to parse log file: {str(e)}")


@router.get("/analysis/{job_id}", response_model=AnalysisJobResponse)
async def get_analysis_job(job_id: int, db: Session = Depends(get_db)):
    """
    Get persisted analysis by job id.
    UI can call this on "View Analysis" without triggering fresh LLM analysis.
    """
    job = db.query(LogAnalysisJob).filter(LogAnalysisJob.id == job_id).first()
    if not job:
        raise HTTPException(status_code=404, detail="Analysis job not found")

    analyses = []
    analyzed_count = 0
    if job.analysis_result and isinstance(job.analysis_result, dict):
        analyses = job.analysis_result.get("analyses", []) or []
        analyzed_count = job.analysis_result.get("analyzed_count", len(analyses))

    return AnalysisJobResponse(
        analysis_job_id=job.id,
        status=job.status,
        total_entries=job.total_entries,
        unique_errors=job.unique_errors,
        analyzed_count=analyzed_count,
        analyses=analyses,
        error_message=job.error_message,
        created_at=job.created_at,
        updated_at=job.updated_at,
    )


@router.post("/analyze", response_model=AnalyzeLogResponse)
async def analyze_log(
    file: UploadFile = File(...),
    selected_errors: Optional[str] = Form(None),
    top_k_context: int = Form(3)
):
    """
    Full analysis pipeline: parse → deduplicate → retrieve code context → LLM root cause.
    
    Args:
        file: The log file to analyze (.log or .txt)
        selected_errors: Optional comma-separated list of error fingerprints to analyze.
                        If not provided, all unique errors are analyzed.
        top_k_context: Number of code chunks to retrieve per error (default: 3)
    
    Returns:
        Detailed root cause analysis for each unique (or selected) error.
    """
    analyzer = _get_analyzer()
    
    # Validate file type
    if file.filename and not file.filename.endswith(('.log', '.txt')):
        raise HTTPException(
            status_code=400,
            detail="Unsupported file type. Please upload a .log or .txt file."
        )
    
    # Read content with size check
    content = await file.read()
    if len(content) > MAX_FILE_SIZE:
        raise HTTPException(
            status_code=413,
            detail=f"File too large. Maximum size is {MAX_FILE_SIZE // (1024*1024)} MB."
        )
    
    try:
        log_text = content.decode('utf-8', errors='replace')
    except Exception:
        raise HTTPException(status_code=400, detail="Could not decode file content as text.")
    
    # Parse selected_errors from comma-separated string
    selected = None
    if selected_errors:
        selected = [s.strip() for s in selected_errors.split(',') if s.strip()]
    
    try:
        result = await analyzer.analyze_log(
            log_content=log_text,
            selected_errors=selected,
            top_k_context=top_k_context
        )
        return AnalyzeLogResponse(**result)
    except Exception as e:
        logger.error(f"Log analysis failed: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=f"Failed to analyze log file: {str(e)}")
