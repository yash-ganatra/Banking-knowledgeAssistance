# Quick Fix: Rate Limit Errors ⚡

## Immediate Solutions

### 1️⃣ Check Token Usage
```bash
curl http://localhost:8000/api/token-usage
```

### 2️⃣ Clear Cache (forces fresh responses)
```bash
curl -X POST http://localhost:8000/api/clear-cache
```

### 3️⃣ Wait and Retry
The error message tells you exactly how long to wait:
```
Please try again in 32m39s
```

### 4️⃣ System Will Auto-Retry
The new system automatically:
- ✅ Retries up to 3 times
- ✅ Uses exponential backoff
- ✅ Falls back to faster models
- ✅ Caches responses to reduce API calls

## What Changed

### Before ❌
```python
# Would fail immediately on rate limit
chat_completion = self.client.chat.completions.create(...)
# Error: Rate limit exceeded ❌
```

### After ✅
```python
# Now automatically retries with fallback
@rate_limiter.with_retry
def _make_completion(...):
    return client.chat.completions.create(...)
# ✅ Retries → Falls back to smaller model → Succeeds
```

## Files Modified
1. ✅ `utils/groq_rate_limiter.py` - NEW rate limiter
2. ✅ `backend/main.py` - LLMService uses retry logic
3. ✅ `backend/query_router.py` - IntentClassifier uses retry logic
4. ✅ `inference/blade_inference_strategy2.py` - BladeInference uses retry logic

## Restart Backend to Apply
```bash
cd backend
uvicorn main:app --reload
```

## Features
- 🔄 **Auto-retry** - 3 attempts with exponential backoff
- 💾 **Caching** - 30-60 min cache reduces duplicate calls
- 📊 **Token tracking** - Monitor daily usage (100K limit)
- 🔀 **Model fallback** - Switches to faster models automatically
- ⚡ **Smart caching** - Handles non-serializable objects

## Monitor Usage
```bash
# Terminal 1 - Monitor tokens
watch -n 10 'curl -s http://localhost:8000/api/token-usage | jq'

# Terminal 2 - Run backend
cd backend && uvicorn main:app --reload

# Terminal 3 - Run frontend
cd frontend && npm run dev
```

## If Rate Limit Persists

### Option 1: Reduce Token Usage
- Lower `top_k` parameter (default 5 → try 3)
- Use more specific queries
- Disable reranking for simple queries

### Option 2: Upgrade Groq Tier
Visit: https://console.groq.com/settings/billing
- Free: 100K tokens/day
- Dev Tier: Much higher limits

### Option 3: Use Faster Model
The system will automatically fallback to:
1. llama-3.3-70b (primary)
2. llama-3.1-70b (fallback)  
3. llama-3.1-8b-instant (fast)
4. mixtral-8x7b (fastest)

## Test It
```bash
# Test multiple requests
for i in {1..5}; do
  echo "Request $i"
  curl -X POST http://localhost:8000/inference/smart \
    -H "Content-Type: application/json" \
    -d '{"query": "Test query", "top_k": 3}'
  sleep 1
done
```

## Need Help?
Check the full guide: `RATE_LIMIT_HANDLING_GUIDE.md`
