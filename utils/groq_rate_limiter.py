"""
Groq API Rate Limiter and Retry Handler

Handles:
- Rate limit errors with exponential backoff
- Token usage tracking
- Caching to reduce API calls
- Automatic fallback to cheaper models
- Request queuing and throttling
"""

import time
import logging
import json
import hashlib
from typing import Optional, Dict, Any, Callable
from functools import wraps
from datetime import datetime, timedelta
from collections import deque
import asyncio

logger = logging.getLogger(__name__)


class RateLimitError(Exception):
    """Custom exception for rate limit errors"""
    def __init__(self, message: str, retry_after: float = 0):
        self.message = message
        self.retry_after = retry_after  # Seconds until can retry
        super().__init__(self.message)


class TokenUsageTracker:
    """Track token usage across API calls"""
    
    def __init__(self, daily_limit: int = 100000):
        self.daily_limit = daily_limit
        self.usage_history = deque(maxlen=1000)
        self.current_day = datetime.now().date()
        self.tokens_used_today = 0
        
    def reset_if_new_day(self):
        """Reset counter if it's a new day"""
        today = datetime.now().date()
        if today != self.current_day:
            self.current_day = today
            self.tokens_used_today = 0
            logger.info("Token usage counter reset for new day")
    
    def record_usage(self, tokens: int):
        """Record token usage"""
        self.reset_if_new_day()
        self.tokens_used_today += tokens
        self.usage_history.append({
            'timestamp': datetime.now(),
            'tokens': tokens,
            'total_today': self.tokens_used_today
        })
        
    def get_remaining_tokens(self) -> int:
        """Get remaining tokens for today"""
        self.reset_if_new_day()
        return max(0, self.daily_limit - self.tokens_used_today)
    
    def can_make_request(self, estimated_tokens: int) -> bool:
        """Check if we have enough tokens for a request"""
        return self.get_remaining_tokens() >= estimated_tokens
    
    def get_usage_stats(self) -> Dict[str, Any]:
        """Get usage statistics"""
        self.reset_if_new_day()
        return {
            'tokens_used_today': self.tokens_used_today,
            'remaining_tokens': self.get_remaining_tokens(),
            'daily_limit': self.daily_limit,
            'percentage_used': (self.tokens_used_today / self.daily_limit * 100) if self.daily_limit > 0 else 0
        }


class ResponseCache:
    """Simple in-memory cache for API responses"""
    
    def __init__(self, max_size: int = 100, ttl_seconds: int = 3600):
        self.cache = {}
        self.max_size = max_size
        self.ttl_seconds = ttl_seconds
        
    def _generate_key(self, *args, **kwargs) -> str:
        """Generate cache key from arguments, excluding non-serializable objects"""
        def make_serializable(obj):
            """Convert object to serializable format"""
            if isinstance(obj, (str, int, float, bool, type(None))):
                return obj
            elif isinstance(obj, (list, tuple)):
                return [make_serializable(item) for item in obj]
            elif isinstance(obj, dict):
                return {k: make_serializable(v) for k, v in obj.items()}
            else:
                # For non-serializable objects, use their type name and id
                return f"<{type(obj).__name__}:{id(obj)}>"
        
        try:
            serializable_args = make_serializable(args)
            serializable_kwargs = make_serializable(kwargs)
            key_data = json.dumps({
                'args': serializable_args, 
                'kwargs': serializable_kwargs
            }, sort_keys=True)
            return hashlib.md5(key_data.encode()).hexdigest()
        except Exception as e:
            # Fallback: create key from string representation
            key_str = f"{args}_{kwargs}"
            return hashlib.md5(key_str.encode()).hexdigest()
    
    def get(self, *args, **kwargs) -> Optional[Any]:
        """Get cached response"""
        key = self._generate_key(*args, **kwargs)
        if key in self.cache:
            value, timestamp = self.cache[key]
            if time.time() - timestamp < self.ttl_seconds:
                logger.info(f"Cache hit for key: {key[:8]}...")
                return value
            else:
                del self.cache[key]
        return None
    
    def set(self, value: Any, *args, **kwargs):
        """Set cached response"""
        key = self._generate_key(*args, **kwargs)
        
        # Evict oldest if at max size
        if len(self.cache) >= self.max_size:
            oldest_key = min(self.cache.keys(), key=lambda k: self.cache[k][1])
            del self.cache[oldest_key]
        
        self.cache[key] = (value, time.time())
        logger.info(f"Cached response for key: {key[:8]}...")
    
    def clear(self):
        """Clear all cache"""
        self.cache.clear()
        logger.info("Cache cleared")


class GroqRateLimiter:
    """
    Comprehensive rate limiter for Groq API calls
    
    Features:
    - Exponential backoff retry
    - Token usage tracking
    - Response caching
    - Automatic model fallback
    - Request throttling
    """
    
    def __init__(
        self,
        max_retries: int = 3,
        base_delay: float = 1.0,
        max_delay: float = 60.0,
        daily_token_limit: int = 100000,
        enable_cache: bool = True,
        cache_ttl: int = 3600,
        fallback_models: Optional[list] = None
    ):
        """
        Initialize rate limiter
        
        Args:
            max_retries: Maximum number of retry attempts
            base_delay: Base delay in seconds for exponential backoff
            max_delay: Maximum delay between retries
            daily_token_limit: Daily token limit for tracking
            enable_cache: Whether to enable response caching
            cache_ttl: Cache time-to-live in seconds
            fallback_models: List of fallback models to try
        """
        self.max_retries = max_retries
        self.base_delay = base_delay
        self.max_delay = max_delay
        
        self.token_tracker = TokenUsageTracker(daily_limit=daily_token_limit)
        self.cache = ResponseCache(ttl_seconds=cache_ttl) if enable_cache else None
        
        # Default fallback models (from most capable to least, all free tier)
        self.fallback_models = fallback_models or [
            "llama-3.3-70b-versatile",  # Primary model
            "llama-3.1-70b-versatile",  # Fallback 1
            "llama-3.1-8b-instant",      # Fallback 2 (faster, cheaper)
            "mixtral-8x7b-32768"         # Fallback 3
        ]
        
        self.current_model_index = 0
        self.rate_limit_until = None  # Timestamp until which we should wait
        
    def _parse_rate_limit_error(self, error_message: str) -> float:
        """
        Parse rate limit error message to extract wait time
        
        Returns:
            Wait time in seconds
        """
        try:
            # Extract time from error message like "Please try again in 32m39.552s"
            if "try again in" in error_message.lower():
                time_str = error_message.split("try again in")[-1].strip()
                time_str = time_str.split(".")[0]  # Remove milliseconds
                
                # Parse various formats
                wait_seconds = 0
                if 'h' in time_str:
                    hours = int(time_str.split('h')[0])
                    wait_seconds += hours * 3600
                    time_str = time_str.split('h')[1]
                if 'm' in time_str:
                    minutes = int(time_str.split('m')[0])
                    wait_seconds += minutes * 60
                    time_str = time_str.split('m')[1]
                if 's' in time_str:
                    seconds = int(time_str.split('s')[0])
                    wait_seconds += seconds
                
                return float(wait_seconds)
        except Exception as e:
            logger.warning(f"Failed to parse rate limit wait time: {e}")
        
        # Default to 1 hour if parsing fails
        return 3600.0
    
    def _should_retry(self, error: Exception, attempt: int) -> tuple[bool, float]:
        """
        Determine if should retry and calculate wait time
        
        Returns:
            (should_retry, wait_seconds)
        """
        if attempt >= self.max_retries:
            return False, 0
        
        error_str = str(error)
        
        # Check for rate limit error
        if "rate_limit_exceeded" in error_str.lower() or "429" in error_str:
            wait_time = self._parse_rate_limit_error(error_str)
            logger.warning(f"Rate limit hit. Need to wait {wait_time:.0f}s")
            self.rate_limit_until = time.time() + wait_time
            
            # If wait time is too long, try fallback model instead
            if wait_time > 300:  # More than 5 minutes
                logger.info("Wait time too long, will try fallback model")
                return True, min(self.base_delay * (2 ** attempt), self.max_delay)
            
            return True, min(wait_time, self.max_delay)
        
        # Check for other retryable errors
        if any(err in error_str.lower() for err in ['timeout', 'connection', 'service unavailable']):
            wait_time = min(self.base_delay * (2 ** attempt), self.max_delay)
            return True, wait_time
        
        return False, 0
    
    def _get_next_model(self, current_model: str) -> Optional[str]:
        """Get next fallback model"""
        try:
            current_idx = self.fallback_models.index(current_model)
            if current_idx < len(self.fallback_models) - 1:
                next_model = self.fallback_models[current_idx + 1]
                logger.info(f"Falling back from {current_model} to {next_model}")
                return next_model
        except ValueError:
            pass
        
        # If current model not in list or no more fallbacks, use fastest model
        return "llama-3.1-8b-instant"
    
    def with_retry(self, func: Callable) -> Callable:
        """
        Decorator to add retry logic to Groq API calls
        
        Usage:
            @rate_limiter.with_retry
            def my_groq_call(client, messages, model):
                return client.chat.completions.create(...)
        """
        @wraps(func)
        def wrapper(*args, **kwargs):
            last_exception = None
            current_model = kwargs.get('model', self.fallback_models[0])
            
            # Check cache first
            if self.cache:
                cached = self.cache.get(*args, **kwargs)
                if cached is not None:
                    return cached
            
            for attempt in range(self.max_retries + 1):
                try:
                    # Check if we're in rate limit timeout
                    if self.rate_limit_until and time.time() < self.rate_limit_until:
                        wait_time = self.rate_limit_until - time.time()
                        if wait_time > 0:
                            logger.info(f"Waiting {wait_time:.1f}s due to rate limit...")
                            time.sleep(min(wait_time, 5))  # Wait max 5s at a time
                            continue
                    
                    # Make API call
                    result = func(*args, **kwargs)
                    
                    # Track token usage if available
                    if hasattr(result, 'usage') and result.usage:
                        tokens = result.usage.total_tokens
                        self.token_tracker.record_usage(tokens)
                        logger.info(f"Used {tokens} tokens. {self.token_tracker.get_remaining_tokens()} remaining today")
                    
                    # Cache successful result
                    if self.cache:
                        self.cache.set(result, *args, **kwargs)
                    
                    return result
                    
                except Exception as e:
                    last_exception = e
                    should_retry, wait_time = self._should_retry(e, attempt)
                    
                    if not should_retry:
                        logger.error(f"Non-retryable error or max retries reached: {e}")
                        break
                    
                    # Try fallback model if rate limited
                    if "rate_limit" in str(e).lower() and wait_time > 60:
                        next_model = self._get_next_model(current_model)
                        if next_model != current_model:
                            kwargs['model'] = next_model
                            current_model = next_model
                            logger.info(f"Retrying with fallback model: {next_model}")
                            continue
                    
                    logger.warning(f"Attempt {attempt + 1}/{self.max_retries + 1} failed. Waiting {wait_time:.1f}s before retry...")
                    time.sleep(wait_time)
            
            # All retries failed
            raise last_exception
        
        return wrapper
    
    def get_usage_stats(self) -> Dict[str, Any]:
        """Get token usage statistics"""
        return self.token_tracker.get_usage_stats()
    
    def clear_cache(self):
        """Clear response cache"""
        if self.cache:
            self.cache.clear()


# Global instance for easy import
default_rate_limiter = GroqRateLimiter(
    max_retries=3,
    base_delay=2.0,
    daily_token_limit=100000,
    enable_cache=True,
    cache_ttl=1800  # 30 minutes cache
)
