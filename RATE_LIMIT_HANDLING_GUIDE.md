# Rate Limit Handling Guide

## Overview

This system now includes comprehensive rate limit handling for Groq API calls with automatic retry, caching, and fallback mechanisms.

## Features Implemented

### 1. **Automatic Retry with Exponential Backoff**
- Automatically retries failed requests up to 3 times
- Exponential backoff: 2s, 4s, 8s delays
- Parses rate limit error messages to extract exact wait times
- Maximum wait time capped at 60 seconds per retry

### 2. **Response Caching**
- Caches API responses for 30 minutes (LLM) and 1 hour (intent classification)
- Reduces redundant API calls for similar queries
- In-memory cache with automatic eviction (max 100 entries)
- Cache keys handle non-serializable objects (e.g., Groq client)

### 3. **Token Usage Tracking**
- Tracks daily token usage across all services
- Daily limit: 100,000 tokens (Groq free tier)
- Automatic reset at midnight
- Accessible via API endpoint

### 4. **Model Fallback Chain**
When rate limits are hit and wait time exceeds 5 minutes:
1. `llama-3.3-70b-versatile` (Primary - most capable)
2. `llama-3.1-70b-versatile` (Fallback 1)
3. `llama-3.1-8b-instant` (Fallback 2 - fastest, cheapest)
4. `mixtral-8x7b-32768` (Fallback 3)

### 5. **Graceful Error Handling**
- User-friendly error messages
- Specific handling for rate limit errors
- Logging of all retry attempts

## API Endpoints

### Check Token Usage
```bash
GET /api/token-usage
```

**Response:**
```json
{
  "llm_service": {
    "tokens_used_today": 45230,
    "remaining_tokens": 54770,
    "daily_limit": 100000,
    "percentage_used": 45.23
  },
  "intent_classifier": {
    "tokens_used_today": 12450,
    "remaining_tokens": 87550,
    "daily_limit": 100000,
    "percentage_used": 12.45
  },
  "timestamp": "2026-01-23T10:30:00"
}
```

### Clear Cache
```bash
POST /api/clear-cache
```

**Response:**
```json
{
  "message": "Cache cleared successfully",
  "services": ["llm_service", "intent_classifier"]
}
```

## Configuration

### Environment Variables

```bash
# .env file
GROQ_API_KEY=your_groq_api_key_here

# Optional: Configure rate limiter settings
GROQ_MAX_RETRIES=3
GROQ_BASE_DELAY=2.0
GROQ_DAILY_TOKEN_LIMIT=100000
GROQ_ENABLE_CACHE=true
GROQ_CACHE_TTL=1800
```

### Custom Configuration

Modify rate limiter settings in your code:

```python
from utils.groq_rate_limiter import GroqRateLimiter

# Custom rate limiter
custom_limiter = GroqRateLimiter(
    max_retries=5,              # More retries
    base_delay=1.0,             # Faster initial retry
    max_delay=120.0,            # Higher max wait
    daily_token_limit=150000,   # Higher limit (paid tier)
    enable_cache=True,
    cache_ttl=3600,             # 1 hour cache
    fallback_models=[           # Custom fallback chain
        "llama-3.3-70b-versatile",
        "llama-3.1-8b-instant"
    ]
)

# Use with LLMService
llm_service = LLMService(GROQ_API_KEY, rate_limiter=custom_limiter)
```

## Error Messages

### Rate Limit Reached
```
⚠️ Rate limit reached. Please try again in a few minutes or upgrade your Groq plan. 
The system will automatically retry with a smaller model.
```

### After All Retries Failed
```
Error generating response: Error code: 429 - Rate limit exceeded
```

## Best Practices

### 1. Monitor Token Usage
Check token usage regularly to avoid hitting limits:

```bash
curl http://localhost:8000/api/token-usage
```

### 2. Clear Cache When Needed
If you update your knowledge base or want fresh responses:

```bash
curl -X POST http://localhost:8000/api/clear-cache
```

### 3. Optimize Queries
- Use more specific queries to reduce context size
- Adjust `top_k` parameter to retrieve fewer results
- Enable reranking only when necessary

### 4. Upgrade Groq Tier
For production use with high volume:
- **Free Tier**: 100K tokens/day
- **Dev Tier**: Higher limits, faster models
- Visit: https://console.groq.com/settings/billing

### 5. Cache Strategy
The system automatically caches:
- **LLM responses**: 30 minutes (queries change frequently)
- **Intent classification**: 1 hour (routing decisions more stable)

## Troubleshooting

### Issue: Still getting rate limit errors

**Solution 1**: Wait for the specified time
```
Rate limit errors include wait time: "Please try again in 32m39s"
```

**Solution 2**: Clear cache and retry
```bash
curl -X POST http://localhost:8000/api/clear-cache
```

**Solution 3**: Use smaller model explicitly
Update `model` parameter in your requests to use faster models:
- `llama-3.1-8b-instant` (fastest, uses fewer tokens)

### Issue: Cache not working

**Verify cache is enabled:**
```python
# Check in main.py
llm_service.rate_limiter.cache  # Should not be None
```

**Clear and rebuild cache:**
```bash
curl -X POST http://localhost:8000/api/clear-cache
```

### Issue: Token usage not accurate

**Reset token counter:**
Counters automatically reset at midnight. To manually reset, restart the backend:
```bash
# Terminal 1
cd backend
uvicorn main:app --reload
```

## Rate Limit Details by Model

| Model | Tokens/Day (Free) | Speed | Use Case |
|-------|------------------|-------|----------|
| llama-3.3-70b-versatile | 100K | Medium | Complex queries, best quality |
| llama-3.1-70b-versatile | 100K | Medium | Fallback, good quality |
| llama-3.1-8b-instant | 100K | Fast | Simple queries, classifications |
| mixtral-8x7b-32768 | 100K | Fast | Emergency fallback |

**Note**: All models share the same 100K daily limit on free tier.

## Code Changes Summary

### Modified Files:

1. **`utils/groq_rate_limiter.py`** (NEW)
   - Rate limiter implementation
   - Token tracking
   - Response caching
   - Retry logic with exponential backoff

2. **`backend/main.py`**
   - Updated `LLMService` to use rate limiter
   - Added `/api/token-usage` endpoint
   - Added `/api/clear-cache` endpoint

3. **`backend/query_router.py`**
   - Updated `IntentClassifier` to use rate limiter
   - Added retry logic for classification calls

4. **`inference/blade_inference_strategy2.py`**
   - Updated `BladeInferenceSystem` to use rate limiter
   - Added retry logic for LLM calls

## Testing

### Test Rate Limiting
```python
# Make rapid requests to trigger rate limit
for i in range(50):
    response = requests.post(
        "http://localhost:8000/inference/smart",
        json={"query": f"Test query {i}", "top_k": 5}
    )
    print(f"Request {i}: {response.status_code}")
```

### Test Caching
```python
import time

# First request (should hit API)
start = time.time()
response1 = requests.post(
    "http://localhost:8000/inference/smart",
    json={"query": "What is a term deposit?", "top_k": 5}
)
time1 = time.time() - start

# Second identical request (should use cache)
start = time.time()
response2 = requests.post(
    "http://localhost:8000/inference/smart",
    json={"query": "What is a term deposit?", "top_k": 5}
)
time2 = time.time() - start

print(f"First request: {time1:.2f}s")
print(f"Cached request: {time2:.2f}s")
print(f"Speedup: {time1/time2:.1f}x")
```

### Test Token Tracking
```bash
# Check usage before
curl http://localhost:8000/api/token-usage

# Make some requests
curl -X POST http://localhost:8000/inference/smart \
  -H "Content-Type: application/json" \
  -d '{"query": "Explain KYC process", "top_k": 5}'

# Check usage after
curl http://localhost:8000/api/token-usage
```

## Monitoring Dashboard (Optional)

Create a simple monitoring script:

```python
import requests
import time

def monitor_tokens():
    while True:
        response = requests.get("http://localhost:8000/api/token-usage")
        stats = response.json()
        
        llm_stats = stats.get("llm_service", {})
        used = llm_stats.get("tokens_used_today", 0)
        remaining = llm_stats.get("remaining_tokens", 0)
        percentage = llm_stats.get("percentage_used", 0)
        
        print(f"\r📊 Tokens: {used:,} used | {remaining:,} remaining | {percentage:.1f}% used", end="")
        time.sleep(30)  # Update every 30 seconds

if __name__ == "__main__":
    monitor_tokens()
```

## Support

For issues or questions:
1. Check logs: `tail -f backend/logs/app.log`
2. Review error messages in terminal
3. Check Groq console: https://console.groq.com/
4. Clear cache and restart services

## Future Enhancements

- [ ] Persistent token usage tracking (database)
- [ ] Rate limit prediction and warnings
- [ ] Automatic tier upgrade suggestions
- [ ] Request queuing for rate-limited scenarios
- [ ] Multiple API key rotation
- [ ] Per-user token quotas
