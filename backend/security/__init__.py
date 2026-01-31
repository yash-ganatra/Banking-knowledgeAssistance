"""
Security Module for Banking Knowledge Assistant

This module provides comprehensive security guardrails including:
- Input query validation and injection detection
- Output filtering and sensitive data redaction
- Security configuration and prompt hardening
"""

from .security_config import (
    SECURITY_PREAMBLE,
    BLOCKED_TOPICS,
    SENSITIVE_PATTERNS,
    get_hardened_system_prompt
)

from .query_guardrails import (
    QueryGuardrails,
    SecurityAnalysisResult,
    RiskCategory
)

from .output_filter import (
    OutputFilter,
    FilteredResponse
)

__all__ = [
    # Security Config
    'SECURITY_PREAMBLE',
    'BLOCKED_TOPICS', 
    'SENSITIVE_PATTERNS',
    'get_hardened_system_prompt',
    
    # Query Guardrails
    'QueryGuardrails',
    'SecurityAnalysisResult',
    'RiskCategory',
    
    # Output Filter
    'OutputFilter',
    'FilteredResponse',
]
