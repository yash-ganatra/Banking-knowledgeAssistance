"""
Output Filter Module

Provides output validation and sensitive data redaction for LLM responses.
This module scans LLM outputs for sensitive patterns and redacts them before
returning to the user.
"""

import re
import logging
from dataclasses import dataclass, field
from typing import List, Dict, Tuple, Optional

logger = logging.getLogger(__name__)


@dataclass
class FilteredResponse:
    """Result of filtering an LLM response"""
    original_length: int
    filtered_length: int
    response: str
    redactions_made: int
    patterns_found: List[str] = field(default_factory=list)
    dangerous_code_detected: bool = False
    is_safe: bool = True
    
    def to_dict(self) -> Dict:
        return {
            "original_length": self.original_length,
            "filtered_length": self.filtered_length,
            "redactions_made": self.redactions_made,
            "patterns_found": self.patterns_found,
            "dangerous_code_detected": self.dangerous_code_detected,
            "is_safe": self.is_safe
        }


class OutputFilter:
    """
    Filters LLM outputs to redact sensitive information and detect dangerous content.
    """
    
    def __init__(self, strict_mode: bool = True):
        """
        Initialize the output filter.
        
        Args:
            strict_mode: If True, applies more aggressive filtering
        """
        self.strict_mode = strict_mode
        self._compile_patterns()
    
    def _compile_patterns(self):
        """Compile all redaction regex patterns"""
        
        # =================================================================
        # REDACTION PATTERNS - (pattern, replacement, description)
        # =================================================================
        self.redaction_patterns: List[Tuple[re.Pattern, str, str]] = [
            # API Keys - Various formats
            (re.compile(r"gsk_[a-zA-Z0-9]{20,}", re.IGNORECASE),
             "[REDACTED_GROQ_KEY]", "groq_api_key"),
            (re.compile(r"sk-[a-zA-Z0-9]{20,}", re.IGNORECASE),
             "[REDACTED_OPENAI_KEY]", "openai_api_key"),
            (re.compile(r"sk-ant-[a-zA-Z0-9\-]{20,}", re.IGNORECASE),
             "[REDACTED_ANTHROPIC_KEY]", "anthropic_api_key"),
            (re.compile(r"xai-[a-zA-Z0-9]{20,}", re.IGNORECASE),
             "[REDACTED_XAI_KEY]", "xai_api_key"),
            (re.compile(r"AIza[a-zA-Z0-9_-]{35}", re.IGNORECASE),
             "[REDACTED_GOOGLE_KEY]", "google_api_key"),
            
            # Generic API key patterns
            (re.compile(r"api[_-]?key\s*[=:]\s*['\"]?([a-zA-Z0-9_\-]{16,})['\"]?", re.IGNORECASE),
             "api_key=[REDACTED]", "generic_api_key"),
            (re.compile(r"apikey\s*[=:]\s*['\"]?([a-zA-Z0-9_\-]{16,})['\"]?", re.IGNORECASE),
             "apikey=[REDACTED]", "generic_api_key"),
            (re.compile(r"x-api-key\s*[=:]\s*['\"]?([a-zA-Z0-9_\-]{16,})['\"]?", re.IGNORECASE),
             "x-api-key=[REDACTED]", "header_api_key"),
            
            # Bearer tokens
            (re.compile(r"bearer\s+[a-zA-Z0-9_\-\.]{20,}", re.IGNORECASE),
             "Bearer [REDACTED_TOKEN]", "bearer_token"),
            (re.compile(r"authorization\s*[=:]\s*['\"]?bearer\s+[a-zA-Z0-9_\-\.]+['\"]?", re.IGNORECASE),
             "Authorization: Bearer [REDACTED]", "auth_header"),
            
            # JWT Tokens (basic pattern)
            (re.compile(r"eyJ[a-zA-Z0-9_-]*\.eyJ[a-zA-Z0-9_-]*\.[a-zA-Z0-9_-]*"),
             "[REDACTED_JWT]", "jwt_token"),
            
            # Passwords and secrets
            (re.compile(r"password\s*[=:]\s*['\"]?([^\s'\"]{4,})['\"]?", re.IGNORECASE),
             "password=[REDACTED]", "password"),
            (re.compile(r"passwd\s*[=:]\s*['\"]?([^\s'\"]{4,})['\"]?", re.IGNORECASE),
             "passwd=[REDACTED]", "password"),
            (re.compile(r"pwd\s*[=:]\s*['\"]?([^\s'\"]{4,})['\"]?", re.IGNORECASE),
             "pwd=[REDACTED]", "password"),
            (re.compile(r"secret\s*[=:]\s*['\"]?([^\s'\"]{8,})['\"]?", re.IGNORECASE),
             "secret=[REDACTED]", "secret"),
            (re.compile(r"secret[_-]?key\s*[=:]\s*['\"]?([^\s'\"]+)['\"]?", re.IGNORECASE),
             "secret_key=[REDACTED]", "secret_key"),
            
            # Database credentials
            (re.compile(r"DB_PASS(WORD)?\s*[=:]\s*[^\s\n]+", re.IGNORECASE),
             "DB_PASSWORD=[REDACTED]", "db_password"),
            (re.compile(r"DATABASE_PASSWORD\s*[=:]\s*[^\s\n]+", re.IGNORECASE),
             "DATABASE_PASSWORD=[REDACTED]", "db_password"),
            (re.compile(r"MYSQL_PASSWORD\s*[=:]\s*[^\s\n]+", re.IGNORECASE),
             "MYSQL_PASSWORD=[REDACTED]", "mysql_password"),
            (re.compile(r"POSTGRES_PASSWORD\s*[=:]\s*[^\s\n]+", re.IGNORECASE),
             "POSTGRES_PASSWORD=[REDACTED]", "postgres_password"),
            (re.compile(r"MONGO_PASSWORD\s*[=:]\s*[^\s\n]+", re.IGNORECASE),
             "MONGO_PASSWORD=[REDACTED]", "mongo_password"),
            (re.compile(r"REDIS_PASSWORD\s*[=:]\s*[^\s\n]+", re.IGNORECASE),
             "REDIS_PASSWORD=[REDACTED]", "redis_password"),
            
            # Connection strings
            (re.compile(r"mysql://[a-zA-Z0-9_]+:[^@\s]+@[^\s]+", re.IGNORECASE),
             "mysql://[REDACTED_CONNECTION]", "mysql_connection"),
            (re.compile(r"postgres(ql)?://[a-zA-Z0-9_]+:[^@\s]+@[^\s]+", re.IGNORECASE),
             "postgresql://[REDACTED_CONNECTION]", "postgres_connection"),
            (re.compile(r"mongodb(\+srv)?://[a-zA-Z0-9_]+:[^@\s]+@[^\s]+", re.IGNORECASE),
             "mongodb://[REDACTED_CONNECTION]", "mongo_connection"),
            (re.compile(r"redis://:[^@\s]+@[^\s]+", re.IGNORECASE),
             "redis://[REDACTED_CONNECTION]", "redis_connection"),
            (re.compile(r"amqp://[a-zA-Z0-9_]+:[^@\s]+@[^\s]+", re.IGNORECASE),
             "amqp://[REDACTED_CONNECTION]", "amqp_connection"),
            
            # JDBC connection strings
            (re.compile(r"jdbc:[a-zA-Z]+://[^\s]+password=[^\s&]+", re.IGNORECASE),
             "jdbc:[REDACTED_CONNECTION]", "jdbc_connection"),
            
            # AWS credentials
            (re.compile(r"AKIA[0-9A-Z]{16}", re.IGNORECASE),
             "[REDACTED_AWS_KEY]", "aws_access_key"),
            (re.compile(r"aws[_-]?secret[_-]?access[_-]?key\s*[=:]\s*[^\s]+", re.IGNORECASE),
             "AWS_SECRET_ACCESS_KEY=[REDACTED]", "aws_secret"),
            
            # Private keys
            (re.compile(r"-----BEGIN\s+(RSA\s+)?PRIVATE\s+KEY-----[\s\S]*?-----END\s+(RSA\s+)?PRIVATE\s+KEY-----"),
             "[REDACTED_PRIVATE_KEY]", "private_key"),
            (re.compile(r"-----BEGIN\s+EC\s+PRIVATE\s+KEY-----[\s\S]*?-----END\s+EC\s+PRIVATE\s+KEY-----"),
             "[REDACTED_EC_PRIVATE_KEY]", "ec_private_key"),
            
            # Internal file paths
            (re.compile(r"/var/www/[^\s\n'\"]+", re.IGNORECASE),
             "[REDACTED_SERVER_PATH]", "server_path"),
            (re.compile(r"/home/[a-zA-Z0-9_]+/[^\s\n'\"]+"),
             "[REDACTED_HOME_PATH]", "home_path"),
            (re.compile(r"/etc/(passwd|shadow|hosts|sudoers|nginx/[^\s]+|apache[^\s]*)", re.IGNORECASE),
             "[REDACTED_SYSTEM_PATH]", "system_path"),
            (re.compile(r"/opt/[^\s\n'\"]+/(config|secret|credential|password|\.env)", re.IGNORECASE),
             "[REDACTED_CONFIG_PATH]", "config_path"),
            (re.compile(r"C:\\\\Users\\\\[a-zA-Z0-9_]+\\\\[^\s\n'\"]+", re.IGNORECASE),
             "[REDACTED_WINDOWS_PATH]", "windows_path"),
            
            # Internal IP addresses
            (re.compile(r"\b10\.\d{1,3}\.\d{1,3}\.\d{1,3}\b"),
             "[REDACTED_INTERNAL_IP]", "internal_ip_10"),
            (re.compile(r"\b192\.168\.\d{1,3}\.\d{1,3}\b"),
             "[REDACTED_INTERNAL_IP]", "internal_ip_192"),
            (re.compile(r"\b172\.(1[6-9]|2[0-9]|3[0-1])\.\d{1,3}\.\d{1,3}\b"),
             "[REDACTED_INTERNAL_IP]", "internal_ip_172"),
            
            # Email addresses (optional, configurable)
            # Uncomment if you want to redact emails
            # (re.compile(r"[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"),
            #  "[REDACTED_EMAIL]", "email"),
        ]
        
        # =================================================================
        # DANGEROUS CODE PATTERNS - Content that should trigger warnings
        # =================================================================
        self.dangerous_code_patterns: List[Tuple[re.Pattern, str]] = [
            # SQL Injection payloads
            (re.compile(r"'\s*(OR|AND)\s+['\"0-9]+\s*=\s*['\"0-9]+", re.IGNORECASE),
             "sql_injection_or"),
            (re.compile(r"UNION\s+(ALL\s+)?SELECT\s+.+FROM\s+(information_schema|mysql|sys)\.", re.IGNORECASE),
             "sql_injection_union"),
            (re.compile(r";\s*(DROP|DELETE|TRUNCATE|ALTER)\s+(TABLE|DATABASE)", re.IGNORECASE),
             "sql_injection_destructive"),
            (re.compile(r"(SLEEP|BENCHMARK|WAITFOR\s+DELAY)\s*\(", re.IGNORECASE),
             "sql_injection_timing"),
            (re.compile(r"(LOAD_FILE|INTO\s+OUTFILE|INTO\s+DUMPFILE)\s*\(", re.IGNORECASE),
             "sql_injection_file"),
            
            # XSS payloads
            (re.compile(r"<script[^>]*>.*?(alert|document\.|window\.|eval\(|Function\().*?</script>", re.IGNORECASE | re.DOTALL),
             "xss_script_tag"),
            (re.compile(r"javascript\s*:\s*(alert|document\.|window\.|eval|Function)", re.IGNORECASE),
             "xss_javascript_uri"),
            (re.compile(r"on(load|error|click|mouseover|focus|blur)\s*=\s*['\"]?[^'\"]*?(alert|document\.|eval)", re.IGNORECASE),
             "xss_event_handler"),
            (re.compile(r"<img[^>]+onerror\s*=", re.IGNORECASE),
             "xss_img_onerror"),
            (re.compile(r"<svg[^>]+onload\s*=", re.IGNORECASE),
             "xss_svg_onload"),
            
            # Command injection
            (re.compile(r";\s*(cat|ls|rm|wget|curl|nc|bash|sh|python|perl|ruby)\s+", re.IGNORECASE),
             "command_injection"),
            (re.compile(r"\|\s*(cat|ls|rm|wget|curl|nc|bash|sh)\s+", re.IGNORECASE),
             "command_injection_pipe"),
            (re.compile(r"`[^`]*(cat|ls|rm|wget|curl|nc|bash|sh)[^`]*`"),
             "command_injection_backtick"),
            (re.compile(r"\$\([^)]*?(cat|ls|rm|wget|curl|nc|bash|sh)[^)]*\)"),
             "command_injection_subshell"),
            
            # Path traversal
            (re.compile(r"\.\./\.\./\.\./", re.IGNORECASE),
             "path_traversal"),
            (re.compile(r"\.\.\\\\\.\.\\\\\.\.\\\\", re.IGNORECASE),
             "path_traversal_windows"),
            
            # LDAP injection
            (re.compile(r"\)\s*\(\|?\s*\(", re.IGNORECASE),
             "ldap_injection"),
            
            # XML External Entity (XXE)
            (re.compile(r"<!ENTITY\s+\w+\s+SYSTEM", re.IGNORECASE),
             "xxe_attack"),
            (re.compile(r"<!DOCTYPE[^>]+\[.*<!ENTITY", re.IGNORECASE | re.DOTALL),
             "xxe_doctype"),
        ]
    
    def filter_response(self, response: str) -> FilteredResponse:
        """
        Filter an LLM response for sensitive information and dangerous code.
        
        Args:
            response: The raw LLM response
            
        Returns:
            FilteredResponse with redacted content
        """
        original_length = len(response)
        filtered = response
        redactions_made = 0
        patterns_found = []
        dangerous_code_detected = False
        
        # Apply redaction patterns
        for pattern, replacement, pattern_name in self.redaction_patterns:
            matches = pattern.findall(filtered)
            if matches:
                filtered = pattern.sub(replacement, filtered)
                redactions_made += len(matches) if isinstance(matches[0], str) else len(matches)
                patterns_found.append(pattern_name)
                logger.info(f"Redacted {len(matches)} instance(s) of {pattern_name}")
        
        # Check for dangerous code patterns
        dangerous_patterns_found = []
        for pattern, pattern_name in self.dangerous_code_patterns:
            if pattern.search(filtered):
                dangerous_code_detected = True
                dangerous_patterns_found.append(pattern_name)
                patterns_found.append(f"dangerous:{pattern_name}")
        
        if dangerous_code_detected:
            logger.warning(f"Dangerous code patterns detected: {dangerous_patterns_found}")
            # Pattern detection is logged but no warning is appended to user responses
        
        # Log if significant redactions were made
        if redactions_made > 0:
            from .security_config import log_security_event
            log_security_event(
                event_type="output_redaction",
                query="[output filtering]",
                details={
                    "redactions": redactions_made,
                    "patterns": patterns_found
                }
            )
        
        return FilteredResponse(
            original_length=original_length,
            filtered_length=len(filtered),
            response=filtered,
            redactions_made=redactions_made,
            patterns_found=patterns_found,
            dangerous_code_detected=dangerous_code_detected,
            is_safe=not dangerous_code_detected or not self.strict_mode
        )
    
    def redact_sensitive_data(self, text: str) -> str:
        """
        Redact sensitive data from text (simpler version for context filtering).
        
        Args:
            text: Text to redact
            
        Returns:
            Redacted text
        """
        result = text
        for pattern, replacement, _ in self.redaction_patterns:
            result = pattern.sub(replacement, result)
        return result
    
    def contains_dangerous_code(self, text: str) -> bool:
        """
        Check if text contains dangerous code patterns.
        
        Args:
            text: Text to check
            
        Returns:
            True if dangerous patterns found
        """
        for pattern, _ in self.dangerous_code_patterns:
            if pattern.search(text):
                return True
        return False
    
    def get_dangerous_patterns(self, text: str) -> List[str]:
        """
        Get list of dangerous patterns found in text.
        
        Args:
            text: Text to analyze
            
        Returns:
            List of pattern names found
        """
        found = []
        for pattern, pattern_name in self.dangerous_code_patterns:
            if pattern.search(text):
                found.append(pattern_name)
        return found


# =============================================================================
# CONVENIENCE FUNCTIONS  
# =============================================================================

# Global instance for easy access
_filter_instance: Optional[OutputFilter] = None

def get_output_filter(strict_mode: bool = True) -> OutputFilter:
    """Get or create the global output filter instance."""
    global _filter_instance
    if _filter_instance is None:
        _filter_instance = OutputFilter(strict_mode=strict_mode)
    return _filter_instance

def filter_llm_response(response: str) -> FilteredResponse:
    """Convenience function to filter an LLM response."""
    return get_output_filter().filter_response(response)

def redact_sensitive(text: str) -> str:
    """Convenience function to redact sensitive data from text."""
    return get_output_filter().redact_sensitive_data(text)
