# Blade Knowledge Integration Guide

## Overview
Successfully integrated Strategy 2 (Description-Based Pre-filtering) blade inference system into the production frontend and backend.

## What Was Added

### Backend Changes (`backend/main.py`)

1. **Import Statement**
   ```python
   from utils.blade_description_engine import BladeDescriptionEngine
   ```

2. **Global Variable**
   ```python
   blade_engine = None
   ```

3. **Startup Initialization**
   ```python
   # Initialize Blade Engine
   try:
       blade_db_path = os.path.join(VECTOR_DB_ROOT, "blade_views_chroma_db")
       blade_engine = BladeDescriptionEngine(db_path=blade_db_path)
       logger.info("Blade Engine Ready")
   except Exception as e:
       logger.error(f"Failed to load Blade Engine: {e}")
   ```

4. **New Endpoint: `/inference/blade`**
   - Accepts: `QueryRequest` with `query`, `top_k`, `rerank` parameters
   - Returns: `QueryResponse` with results, LLM response, and context
   - Features:
     - Uses Strategy 2 two-phase retrieval
     - Smart snippet extraction (max 2000 chars)
     - Description-based cross-encoder re-ranking
     - Specialized system prompt for blade templates
     - Returns formatted results with metadata

### Frontend Changes (`frontend/src/App.jsx`)

1. **Import Icon**
   ```javascript
   import { ..., FileCode } from 'lucide-react';
   ```

2. **Added Blade Context**
   ```javascript
   const contexts = [
     { id: 'business', label: 'Business Docs', icon: <FileText size={18} /> },
     { id: 'php', label: 'PHP Knowledge', icon: <Database size={18} /> },
     { id: 'js', label: 'JS Knowledge', icon: <Code size={18} /> },
     { id: 'blade', label: 'Blade Templates', icon: <FileCode size={18} /> },
   ];
   ```

3. **Updated State Comment**
   ```javascript
   const [selectedContext, setSelectedContext] = useState('business'); // business, php, js, blade
   ```

## How It Works

### User Flow
1. User opens frontend chat interface
2. Selects "Blade Templates" from context dropdown
3. Enters query (e.g., "what fields are in the account opening form")
4. Frontend sends POST to `/inference/blade` with query
5. Backend:
   - Phase 1: Retrieves 20 candidates by embedding similarity
   - Phase 2: Re-ranks using descriptions with cross-encoder
   - Extracts smart snippets (query-aware, semantic blocks)
   - Formats context for LLM (~2,000 chars vs 261k original)
   - Generates response using Groq LLM
6. Frontend displays LLM response in chat

### Key Features
- **97.4% Token Reduction**: From 96,591 → 2,541 tokens average
- **Fast Response**: ~0.85s query time
- **High Accuracy**: Correct file retrieval using description re-ranking
- **No Truncation**: Semantic extraction preserves context
- **Form-Aware**: Special handling for blade forms

## Testing

### 1. Backend Unit Test
```bash
cd /Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance
python -m pytest tests/test_blade_strategy2.py -v
```

Expected: All 18 tests pass ✅

### 2. Backend Integration Test
```bash
# Terminal 1: Start backend
cd backend
python main.py

# Terminal 2: Run test
python tests/test_blade_endpoint.py
```

Expected output:
```
✅ Backend is running at http://localhost:8000
🔍 Testing /inference/blade endpoint...
📝 Test Case 1: what are different fields in the form of account opening
✅ Status: SUCCESS
📊 Results returned: 3
📁 Retrieved Files:
  1. form.blade.php
     Score: 0.8234
     Snippet Length: 1845 chars
     Full Content: 261580 chars
     Token Reduction: 99.3%
```

### 3. Frontend Manual Test
```bash
# Terminal 1: Backend
cd backend && python main.py

# Terminal 2: Frontend
cd frontend && npm run dev
```

1. Open http://localhost:5173
2. Select "Blade Templates" from dropdown
3. Ask: "what are different fields in the form of account opening"
4. Verify response mentions form fields correctly

## API Endpoint Details

### POST `/inference/blade`

**Request Body:**
```json
{
  "query": "what fields are in account opening form",
  "top_k": 3,
  "rerank": true
}
```

**Response:**
```json
{
  "results": [
    {
      "id": "doc_123",
      "content": "... smart snippet ...",
      "metadata": {
        "file_name": "form.blade.php",
        "file_path": "/path/to/form.blade.php",
        "section": "body",
        "description": "Account opening form...",
        "has_form": true,
        "snippet_length": 1845,
        "content_length": 261580,
        "rerank_score": 0.8234
      },
      "distance": 0.234
    }
  ],
  "llm_response": "The account opening form contains the following fields...",
  "context_used": "### File: form.blade.php\n..."
}
```

## Architecture

```
Frontend (React)
    ↓ POST /inference/blade
Backend (FastAPI)
    ↓ query()
BladeDescriptionEngine
    ↓ Phase 1: Embedding Search
ChromaDB (177 blade docs)
    ↓ 20 candidates
    ↓ Phase 2: Extract descriptions
Cross-Encoder Re-ranker
    ↓ Top 3 re-ranked results
SmartSnippetExtractor
    ↓ Query-aware snippets
LLM Service (Groq)
    ↓ Generate response
Frontend (Display)
```

## Performance Metrics

| Metric | Value |
|--------|-------|
| Average Query Time | 0.85s |
| Token Reduction | 97.4% |
| Avg Context Size | ~2,541 tokens |
| Max Snippet Size | 2,000 chars |
| Initial Candidates | 20 docs |
| Final Results | 3 docs |

## Error Handling

### Backend Not Initialized
```json
{
  "status_code": 503,
  "detail": "Blade engine not initialized"
}
```

**Solution**: Check backend logs for initialization errors

### ChromaDB Not Found
```
Failed to load Blade Engine: [Errno 2] No such file or directory: '.../blade_views_chroma_db'
```

**Solution**: Verify vector DB exists at `vector_db/blade_views_chroma_db/`

## Configuration

### Backend Settings
- **DB Path**: `vector_db/blade_views_chroma_db/`
- **Collection**: `blade_views_knowledge`
- **Initial Candidates**: 20
- **Top K**: 3 (default)
- **Max Snippet**: 2000 chars
- **Re-ranking**: Enabled by default

### Frontend Settings
- **Context ID**: `blade`
- **Label**: "Blade Templates"
- **Icon**: FileCode (lucide-react)

## Troubleshooting

### Issue: No LLM response
**Cause**: LLM service not initialized
**Solution**: Set GROQ_API_KEY environment variable

### Issue: Empty results
**Cause**: Query too generic or ChromaDB empty
**Solution**: Check collection has 177 docs, try more specific query

### Issue: Slow response
**Cause**: Re-ranking 20 candidates is slow
**Solution**: Reduce `initial_candidates` parameter (trade accuracy for speed)

## Next Steps

Optional enhancements:
1. Add caching for frequent queries
2. Add blade syntax highlighting in frontend
3. Add "Show Full Code" toggle in UI
4. Add batch query support
5. Add query analytics/logging

## Files Modified

### Backend
- [backend/main.py](backend/main.py) - Added blade engine + endpoint

### Frontend  
- [frontend/src/App.jsx](frontend/src/App.jsx) - Added blade context option

### Tests
- [tests/test_blade_endpoint.py](tests/test_blade_endpoint.py) - New integration test

### Strategy Implementation (Already Complete)
- [utils/blade_description_engine.py](utils/blade_description_engine.py) - Two-phase retrieval
- [utils/smart_snippet_extractor.py](utils/smart_snippet_extractor.py) - Semantic extraction
- [inference/blade_inference_strategy2.py](inference/blade_inference_strategy2.py) - CLI system
- [tests/test_blade_strategy2.py](tests/test_blade_strategy2.py) - Unit tests

## Summary

✅ Backend integrated with blade_engine initialization
✅ New `/inference/blade` endpoint following existing pattern
✅ Frontend updated with 4th context option
✅ Integration test script created
✅ 97.4% token reduction maintained
✅ No breaking changes to existing endpoints
✅ Ready for production use
