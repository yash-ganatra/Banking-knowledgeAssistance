"""
Query Guardrails Module

Provides input validation and prompt injection detection for user queries.
This module analyzes incoming queries for potential security threats before
they are processed by the LLM.
"""

import re
import logging
from enum import Enum
from dataclasses import dataclass, field
from typing import List, Optional, Tuple, Dict
from .security_config import BLOCKED_TOPICS, log_security_event

logger = logging.getLogger(__name__)


class RiskCategory(str, Enum):
    """Categories of security risks detected in queries"""
    SAFE = "safe"
    PROMPT_INJECTION = "prompt_injection"
    ROLE_MANIPULATION = "role_manipulation"
    SYSTEM_EXTRACTION = "system_extraction"
    CREDENTIAL_REQUEST = "credential_request"
    EXPLOIT_REQUEST = "exploit_request"
    DELIMITER_ATTACK = "delimiter_attack"
    JAILBREAK_ATTEMPT = "jailbreak_attempt"
    SENSITIVE_QUERY = "sensitive_query"
    SOCIAL_ENGINEERING = "social_engineering"


@dataclass
class SecurityAnalysisResult:
    """Result of security analysis on a query"""
    is_safe: bool
    risk_score: float  # 0.0 (safe) to 1.0 (high risk)
    detected_patterns: List[str] = field(default_factory=list)
    risk_categories: List[RiskCategory] = field(default_factory=list)
    should_block: bool = False
    sanitized_query: Optional[str] = None
    refusal_message: str = "I cannot process this request."
    
    def to_dict(self) -> Dict:
        return {
            "is_safe": self.is_safe,
            "risk_score": self.risk_score,
            "detected_patterns": self.detected_patterns,
            "risk_categories": [rc.value for rc in self.risk_categories],
            "should_block": self.should_block,
        }


class QueryGuardrails:
    """
    Security guardrails for analyzing and validating user queries.
    
    Detects various types of prompt injection attacks and sensitive queries
    before they reach the LLM.
    """
    
    def __init__(self, strict_mode: bool = True):
        """
        Initialize the query guardrails.
        
        Args:
            strict_mode: If True, blocks queries at lower risk thresholds
        """
        self.strict_mode = strict_mode
        self.block_threshold = 0.5 if strict_mode else 0.7
        
        # Compile all regex patterns for efficiency
        self._compile_patterns()
    
    def _compile_patterns(self):
        """Compile all detection regex patterns"""
        
        # =================================================================
        # PROMPT INJECTION PATTERNS
        # =================================================================
        self.injection_patterns: List[Tuple[re.Pattern, str, float]] = [
            # Instruction override attempts (HIGH RISK)
            (re.compile(r"ignore\s+(all\s+)?(previous|prior|above|earlier|preceding)\s+(instructions?|rules?|prompts?|guidelines?|directives?)", re.IGNORECASE),
             "instruction_override", 0.95),
            (re.compile(r"(disregard|forget|override|bypass|skip)\s+(everything|all|your|the|any)\s+(instructions?|rules?|prompts?|guidelines?|above)", re.IGNORECASE),
             "instruction_override", 0.95),
            (re.compile(r"(new|different|updated|real|actual)\s+(instructions?|rules?|prompts?)\s*[:\-]", re.IGNORECASE),
             "instruction_injection", 0.9),
            (re.compile(r"(stop|end|terminate)\s+(being|acting\s+as)\s+(an?\s+)?(assistant|ai|bot|helper)", re.IGNORECASE),
             "role_termination", 0.85),
            (re.compile(r"from\s+now\s+on[,\s]+(you\s+)?(will|must|should|are)", re.IGNORECASE),
             "instruction_injection", 0.85),
            
            # Role manipulation (HIGH RISK)
            (re.compile(r"you\s+are\s+now\s+(a|an|the|my)?\s*\w+", re.IGNORECASE),
             "role_manipulation", 0.9),
            (re.compile(r"act\s+(as|like)\s+(a|an|if\s+you\s+were)", re.IGNORECASE),
             "role_manipulation", 0.85),
            (re.compile(r"pretend\s+(to\s+be|you'?re|that\s+you)", re.IGNORECASE),
             "role_manipulation", 0.85),
            (re.compile(r"roleplay\s+(as|that)", re.IGNORECASE),
             "role_manipulation", 0.85),
            (re.compile(r"(switch|change)\s+(to|into)\s+.{1,30}\s+mode", re.IGNORECASE),
             "role_manipulation", 0.8),
            (re.compile(r"(enable|activate|enter)\s+.{1,20}\s+mode", re.IGNORECASE),
             "role_manipulation", 0.75),
            
            # System prompt extraction (HIGH RISK)
            (re.compile(r"(reveal|show|display|output|print|tell\s+me|give\s+me|what\s+is)\s+(your\s+)?(system\s+)?(prompt|instructions?|rules?|guidelines?|directives?)", re.IGNORECASE),
             "system_extraction", 0.95),
            (re.compile(r"(repeat|echo|print|output)\s+(everything|all|what'?s?)\s+(above|before|prior)", re.IGNORECASE),
             "system_extraction", 0.9),
            (re.compile(r"what\s+(are|were)\s+(your|the)\s+(initial|original|first|system)\s+(instructions?|prompts?|rules?)", re.IGNORECASE),
             "system_extraction", 0.9),
            (re.compile(r"(show|display|reveal)\s+(me\s+)?(the\s+)?(hidden|secret|internal)", re.IGNORECASE),
             "system_extraction", 0.85),
            (re.compile(r"(copy|paste|dump)\s+(your\s+)?(system|initial)\s*(prompt|message|instructions?)", re.IGNORECASE),
             "system_extraction", 0.9),
            
            # Delimiter/format exploitation (MEDIUM-HIGH RISK)
            (re.compile(r"\[INST\]|\[/INST\]|\[SYS\]|\[/SYS\]", re.IGNORECASE),
             "delimiter_attack", 0.85),
            (re.compile(r"<<SYS>>|<</SYS>>|<<INST>>|<</INST>>", re.IGNORECASE),
             "delimiter_attack", 0.85),
            (re.compile(r"<\|im_start\|>|<\|im_end\|>|<\|system\|>|<\|user\|>|<\|assistant\|>", re.IGNORECASE),
             "delimiter_attack", 0.85),
            (re.compile(r"```\s*(system|instruction|prompt|rules?)", re.IGNORECASE),
             "delimiter_attack", 0.8),
            (re.compile(r"<system>|</system>|<instruction>|</instruction>", re.IGNORECASE),
             "delimiter_attack", 0.8),
            (re.compile(r"#{3,}\s*(system|instruction|new\s+rules?)", re.IGNORECASE),
             "delimiter_attack", 0.75),
            
            # Jailbreak attempts (HIGH RISK)
            (re.compile(r"\bDAN\b|\bDANN?\b|do\s+anything\s+now", re.IGNORECASE),
             "jailbreak_attempt", 0.95),
            (re.compile(r"(jailbreak|jail\s*break|jail-break)", re.IGNORECASE),
             "jailbreak_attempt", 0.95),
            (re.compile(r"(developer|dev|debug|admin|god|sudo|root)\s+mode", re.IGNORECASE),
             "jailbreak_attempt", 0.9),
            (re.compile(r"(bypass|disable|remove|turn\s+off)\s+(safety|security|filter|restriction|guard|content\s+filter)", re.IGNORECASE),
             "jailbreak_attempt", 0.95),
            (re.compile(r"(unlock|enable|activate)\s+(hidden|secret|admin|full|unrestricted)", re.IGNORECASE),
             "jailbreak_attempt", 0.85),
            (re.compile(r"without\s+(any\s+)?(restrictions?|limitations?|filters?|safety)", re.IGNORECASE),
             "jailbreak_attempt", 0.8),
            (re.compile(r"(no\s+)?ethical\s+(restrictions?|guidelines?|boundaries)", re.IGNORECASE),
             "jailbreak_attempt", 0.85),
        ]
        
        # =================================================================
        # CREDENTIAL/SENSITIVE REQUEST PATTERNS
        # =================================================================
        self.sensitive_patterns: List[Tuple[re.Pattern, str, float]] = [
            # Direct credential requests
            (re.compile(r"(show|reveal|output|what\s+is|give\s+me|tell\s+me)\s+(the\s+)?(api\s*key|password|secret|credential|token)", re.IGNORECASE),
             "credential_request", 0.9),
            (re.compile(r"(database|db|mysql|postgres|mongo)\s+(password|credential|connection\s+string|uri)", re.IGNORECASE),
             "credential_request", 0.85),
            (re.compile(r"(groq|openai|anthropic|azure)\s*(api)?\s*(key|secret|token)", re.IGNORECASE),
             "credential_request", 0.9),
            (re.compile(r"\.env\s+(file|variable|content|value)", re.IGNORECASE),
             "credential_request", 0.8),
            (re.compile(r"(config|configuration|settings?)\s+(file\s+)?(with\s+)?(password|credential|secret)", re.IGNORECASE),
             "credential_request", 0.8),
            
            # Infrastructure probing
            (re.compile(r"(server|database|backend)\s+(ip|address|location|host(name)?)", re.IGNORECASE),
             "infrastructure_probe", 0.7),
            (re.compile(r"(internal|private)\s+(network|infrastructure|server|ip)", re.IGNORECASE),
             "infrastructure_probe", 0.75),
            (re.compile(r"(production|staging|dev)\s+(server|environment|url|endpoint)", re.IGNORECASE),
             "infrastructure_probe", 0.6),
        ]
        
        # =================================================================
        # EXPLOIT/ATTACK REQUEST PATTERNS
        # =================================================================
        self.exploit_patterns: List[Tuple[re.Pattern, str, float]] = [
            # SQL Injection
            (re.compile(r"(how\s+to|explain|show\s+me|give\s+me)\s+(do\s+)?(sql\s+injection|sqli)", re.IGNORECASE),
             "exploit_request", 0.9),
            (re.compile(r"sql\s+injection\s+(payload|example|attack|query|code)", re.IGNORECASE),
             "exploit_request", 0.9),
            
            # XSS
            (re.compile(r"(how\s+to|explain|show\s+me)\s+(do\s+)?(xss|cross[- ]site\s+scripting)", re.IGNORECASE),
             "exploit_request", 0.9),
            (re.compile(r"xss\s+(payload|example|attack|vector|code)", re.IGNORECASE),
             "exploit_request", 0.9),
            
            # Authentication bypass
            (re.compile(r"(bypass|circumvent|skip|avoid)\s+(the\s+)?(authentication|authorization|login|auth)", re.IGNORECASE),
             "exploit_request", 0.9),
            (re.compile(r"(authentication|login|auth)\s+(bypass|exploit|vulnerability|hack)", re.IGNORECASE),
             "exploit_request", 0.85),
            
            # General exploit requests
            (re.compile(r"(how\s+to|explain)\s+(hack|exploit|attack|breach|compromise)", re.IGNORECASE),
             "exploit_request", 0.85),
            (re.compile(r"(vulnerability|exploit|attack)\s+(code|payload|script|example)", re.IGNORECASE),
             "exploit_request", 0.8),
            (re.compile(r"(reverse\s+shell|backdoor|rootkit|keylogger|malware)", re.IGNORECASE),
             "exploit_request", 0.95),
            (re.compile(r"(brute\s*force|credential\s+stuffing|password\s+crack)", re.IGNORECASE),
             "exploit_request", 0.85),
        ]
        
        # =================================================================
        # SOCIAL ENGINEERING PATTERNS
        # =================================================================
        self.social_engineering_patterns: List[Tuple[re.Pattern, str, float]] = [
            (re.compile(r"i('?m|\s+am)\s+(an?\s+)?(admin|administrator|developer|owner|manager|security\s+researcher)", re.IGNORECASE),
             "authority_claim", 0.5),
            (re.compile(r"(this\s+is\s+)?(an?\s+)?(emergency|urgent|critical|important)", re.IGNORECASE),
             "urgency_claim", 0.4),
            (re.compile(r"(for|doing)\s+(security\s+)?(testing|audit|assessment|research|penetration\s+test)", re.IGNORECASE),
             "testing_claim", 0.5),
            (re.compile(r"i('?m|\s+am)\s+(authorized|allowed|permitted)\s+to", re.IGNORECASE),
             "authorization_claim", 0.5),
            (re.compile(r"(my\s+)?boss|manager|supervisor\s+(asked|told|wants|needs)", re.IGNORECASE),
             "authority_chain", 0.4),
        ]
    
    def analyze_query(self, query: str) -> SecurityAnalysisResult:
        """
        Analyze a query for security threats.
        
        Args:
            query: The user query to analyze
            
        Returns:
            SecurityAnalysisResult with threat assessment
        """
        detected_patterns = []
        risk_categories = set()
        max_risk_score = 0.0
        
        # Check injection patterns
        for pattern, pattern_name, risk_score in self.injection_patterns:
            if pattern.search(query):
                detected_patterns.append(f"injection:{pattern_name}")
                max_risk_score = max(max_risk_score, risk_score)
                
                # Map to risk category
                if "override" in pattern_name or "injection" in pattern_name:
                    risk_categories.add(RiskCategory.PROMPT_INJECTION)
                elif "role" in pattern_name:
                    risk_categories.add(RiskCategory.ROLE_MANIPULATION)
                elif "extraction" in pattern_name:
                    risk_categories.add(RiskCategory.SYSTEM_EXTRACTION)
                elif "delimiter" in pattern_name:
                    risk_categories.add(RiskCategory.DELIMITER_ATTACK)
                elif "jailbreak" in pattern_name:
                    risk_categories.add(RiskCategory.JAILBREAK_ATTEMPT)
        
        # Check sensitive patterns
        for pattern, pattern_name, risk_score in self.sensitive_patterns:
            if pattern.search(query):
                detected_patterns.append(f"sensitive:{pattern_name}")
                max_risk_score = max(max_risk_score, risk_score)
                risk_categories.add(RiskCategory.CREDENTIAL_REQUEST)
        
        # Check exploit patterns
        for pattern, pattern_name, risk_score in self.exploit_patterns:
            if pattern.search(query):
                detected_patterns.append(f"exploit:{pattern_name}")
                max_risk_score = max(max_risk_score, risk_score)
                risk_categories.add(RiskCategory.EXPLOIT_REQUEST)
        
        # Check social engineering (additive to risk score, not standalone blocking)
        social_engineering_score = 0.0
        for pattern, pattern_name, risk_score in self.social_engineering_patterns:
            if pattern.search(query):
                detected_patterns.append(f"social:{pattern_name}")
                social_engineering_score += risk_score
                risk_categories.add(RiskCategory.SOCIAL_ENGINEERING)
        
        # Social engineering combined with other risks increases overall score
        if social_engineering_score > 0 and max_risk_score > 0:
            max_risk_score = min(1.0, max_risk_score + (social_engineering_score * 0.2))
        
        # Check blocked topics
        query_lower = query.lower()
        for topic in BLOCKED_TOPICS:
            if topic in query_lower:
                detected_patterns.append(f"blocked_topic:{topic}")
                max_risk_score = max(max_risk_score, 0.85)
                risk_categories.add(RiskCategory.EXPLOIT_REQUEST)
        
        # Determine if query should be blocked
        should_block = max_risk_score >= self.block_threshold
        is_safe = max_risk_score < 0.3
        
        # Determine refusal message
        refusal_message = self._get_refusal_message(list(risk_categories))
        
        # Log security event if threats detected
        if detected_patterns:
            log_security_event(
                event_type="query_analysis",
                query=query,
                details={
                    "risk_score": max_risk_score,
                    "patterns": detected_patterns,
                    "blocked": should_block
                }
            )
        
        return SecurityAnalysisResult(
            is_safe=is_safe,
            risk_score=max_risk_score,
            detected_patterns=detected_patterns,
            risk_categories=list(risk_categories),
            should_block=should_block,
            sanitized_query=self._sanitize_query(query) if not should_block else None,
            refusal_message=refusal_message
        )
    
    def is_safe(self, query: str) -> bool:
        """Quick check if a query is safe to process."""
        return not self.analyze_query(query).should_block
    
    def get_risk_score(self, query: str) -> float:
        """Get the risk score for a query."""
        return self.analyze_query(query).risk_score
    
    def _sanitize_query(self, query: str) -> str:
        """
        Sanitize a query by removing potentially dangerous content.
        Only called for queries that aren't blocked but have some risk.
        """
        sanitized = query
        
        # Remove common delimiter attacks
        delimiter_patterns = [
            r"\[/?INST\]",
            r"\[/?SYS\]",
            r"<</?SYS>>",
            r"<\|im_(start|end)\|>",
            r"<\|system\|>",
            r"<\|user\|>",
            r"<\|assistant\|>",
        ]
        
        for pattern in delimiter_patterns:
            sanitized = re.sub(pattern, "", sanitized, flags=re.IGNORECASE)
        
        # Remove multiple consecutive special characters that might be delimiter attempts
        sanitized = re.sub(r"[#]{4,}", "###", sanitized)
        sanitized = re.sub(r"[=]{4,}", "===", sanitized)
        sanitized = re.sub(r"[-]{4,}", "---", sanitized)
        
        return sanitized.strip()
    
    def _get_refusal_message(self, risk_categories: List[RiskCategory]) -> str:
        """Get appropriate refusal message based on detected risks."""
        if not risk_categories:
            return "I cannot process this request."
        
        # Priority order for refusal messages
        if RiskCategory.JAILBREAK_ATTEMPT in risk_categories:
            return "I cannot process this request."
        if RiskCategory.PROMPT_INJECTION in risk_categories:
            return "I cannot process this request."
        if RiskCategory.SYSTEM_EXTRACTION in risk_categories:
            return "I cannot process this request."
        if RiskCategory.CREDENTIAL_REQUEST in risk_categories:
            return "I cannot provide information about credentials or authentication secrets."
        if RiskCategory.EXPLOIT_REQUEST in risk_categories:
            return "I cannot provide information about security exploits or attack techniques."
        if RiskCategory.ROLE_MANIPULATION in risk_categories:
            return "I cannot process this request."
        
        return "I cannot process this request."


# =============================================================================
# CONVENIENCE FUNCTIONS
# =============================================================================

# Global instance for easy access
_guardrails_instance: Optional[QueryGuardrails] = None

def get_guardrails(strict_mode: bool = True) -> QueryGuardrails:
    """Get or create the global guardrails instance."""
    global _guardrails_instance
    if _guardrails_instance is None:
        _guardrails_instance = QueryGuardrails(strict_mode=strict_mode)
    return _guardrails_instance

def check_query_safety(query: str) -> SecurityAnalysisResult:
    """Convenience function to check query safety."""
    return get_guardrails().analyze_query(query)

def is_query_safe(query: str) -> bool:
    """Convenience function for quick safety check."""
    return get_guardrails().is_safe(query)
