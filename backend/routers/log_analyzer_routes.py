"""
Log Analyzer API Routes
Provides endpoints for uploading a log file, parsing/previewing errors,
and running full LLM-powered root cause analysis.
"""

import logging
from typing import Optional, List
from fastapi import APIRouter, UploadFile, File, HTTPException, Form
from pydantic import BaseModel

from log_analyzer import LogAnalyzer

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


class AnalyzeLogResponse(BaseModel):
    total_entries: int
    unique_errors: int
    analyzed_count: int
    analyses: List[dict]


# ---- Endpoints ----

MAX_FILE_SIZE = 10 * 1024 * 1024  # 10 MB


@router.post("/parse", response_model=ParseLogResponse)
async def parse_log(file: UploadFile = File(...)):
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
        return ParseLogResponse(**result)
    except Exception as e:
        logger.error(f"Log parsing failed: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=f"Failed to parse log file: {str(e)}")


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
