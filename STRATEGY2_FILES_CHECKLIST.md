# Strategy 2 Implementation - Files Checklist

## Required Files (Must Create)

### 1. Smart Snippet Extractor
**File**: `utils/smart_snippet_extractor.py`
**Purpose**: Extract query-relevant parts from large blade files
**Size**: ~200-300 lines
**Dependencies**: transformers, scikit-learn, bs4 (BeautifulSoup)
**Key Functions**:
- `extract_relevant_snippet(content, query, max_chars=2000)` - Main extraction logic
- `split_into_semantic_blocks(content)` - Split by HTML structure
- `score_blocks_against_query(blocks, query)` - Relevance scoring
- `assemble_snippet(blocks, max_chars)` - Reconstruct coherent snippet

---

### 2. Blade Description Engine
**File**: `utils/blade_description_engine.py`
**Purpose**: Two-phase retrieval with description re-ranking
**Size**: ~250-350 lines
**Dependencies**: chromadb, sentence-transformers, FlagEmbedding
**Key Class**: `BladeDescriptionEngine`
**Key Methods**:
- `__init__()` - Load collection, models
- `query(query_text, top_k=5)` - Main query method
- `retrieve_candidates(query_embedding, n=20)` - Get initial results
- `extract_descriptions(results)` - Pull from metadata
- `rerank_with_cross_encoder(query, descriptions)` - Re-rank top results
- `extract_snippets_for_results(results, query)` - Smart extraction
- `format_context(results)` - Prepare for LLM

---

### 3. Optimized Inference Script
**File**: `inference/blade_inference_strategy2.py`
**Purpose**: End-to-end inference with Strategy 2
**Size**: ~300-400 lines
**Dependencies**: All of the above + groq
**Structure**:
- Setup & imports
- Model loading (BGE-M3, Cross-Encoder)
- Engine initialization
- Query interface
- Results display
- LLM integration
- Comparison metrics
- Batch testing

---

## Optional Files (Recommended)

### 4. Test Suite
**File**: `tests/test_blade_strategy2.py`
**Purpose**: Validate implementation
**Size**: ~150-200 lines
**Test Cases**:
- Description extraction test
- Cross-encoder ranking test
- Snippet quality test
- Token reduction test
- Performance test
- End-to-end test

---

### 5. Backend Integration
**File**: `backend/main.py` (modify existing)
**Purpose**: Add REST API endpoint for blade queries
**Changes**: Add ~50 lines
**New Endpoint**: `POST /inference/blade`
**Adds**: BladeDescriptionEngine initialization and route handler

---

## Existing Files (No Changes Needed)

### Files We Use (Don't Modify)
- `vector_db/blade_views_chroma_db/` - Existing database (has descriptions in metadata)
- `chunks/blade_views_enhanced.json` - Source data (reference only)
- `backend/main.py` - Existing backend (optional integration)

---

## File Creation Order

### Phase 1: Core Utilities (3-4 hours)
1. ✅ `utils/smart_snippet_extractor.py` - Start here
2. ✅ `utils/blade_description_engine.py` - Depends on #1

### Phase 2: Application (2-3 hours)
3. ✅ `inference/blade_inference_strategy2.py` - Depends on #1, #2

### Phase 3: Validation (2-3 hours)
4. ✅ `tests/test_blade_strategy2.py` - Test everything

### Phase 4: Production (1-2 hours, optional)
5. ✅ Modify `backend/main.py` - Add endpoint

---

## Quick Reference

### Minimum Viable Implementation (4-6 hours)
```
✅ utils/smart_snippet_extractor.py
✅ utils/blade_description_engine.py
✅ inference/blade_inference_strategy2.py
```
**Result**: Working end-to-end retrieval with 80-90% token reduction

### Complete Implementation (8-10 hours)
```
✅ All 3 files above
✅ tests/test_blade_strategy2.py
✅ backend/main.py (modified)
```
**Result**: Production-ready with tests and API

---

## Dependencies Summary

### Python Packages Needed
```bash
pip install chromadb FlagEmbedding sentence-transformers transformers scikit-learn beautifulsoup4 groq python-dotenv
```

### Models Downloaded (Auto)
- `BAAI/bge-m3` - Embedding model (~2GB)
- `cross-encoder/ms-marco-MiniLM-L-6-v2` - Re-ranking model (~100MB)

---

## File Size Estimates

| File | Lines | Size | Time |
|------|-------|------|------|
| smart_snippet_extractor.py | ~250 | ~12KB | 2-3h |
| blade_description_engine.py | ~300 | ~15KB | 2-3h |
| blade_inference_strategy2.py | ~350 | ~18KB | 2h |
| test_blade_strategy2.py | ~180 | ~9KB | 2-3h |
| backend/main.py (changes) | ~50 | ~2KB | 1h |
| **Total** | **~1,130** | **~56KB** | **9-12h** |

---

## What Each File Does (Visual)

```
┌─────────────────────────────────────────────────┐
│  blade_inference_strategy2.py                   │
│  (User Interface)                               │
│  - Takes user query                             │
│  - Displays results                             │
│  - Shows metrics                                │
└───────────────┬─────────────────────────────────┘
                │ uses
                ↓
┌─────────────────────────────────────────────────┐
│  blade_description_engine.py                    │
│  (Orchestration Layer)                          │
│  - Retrieves from ChromaDB                      │
│  - Extracts descriptions from metadata          │
│  - Re-ranks with cross-encoder                  │
│  - Formats final context                        │
└───────────────┬─────────────────────────────────┘
                │ uses
                ↓
┌─────────────────────────────────────────────────┐
│  smart_snippet_extractor.py                     │
│  (Low-level Utility)                            │
│  - Parses blade HTML                            │
│  - Scores blocks vs query                       │
│  - Extracts relevant snippets                   │
└─────────────────────────────────────────────────┘
```

---

## Start Here

**Create files in this exact order**:

1. **First**: `utils/smart_snippet_extractor.py`
   - Most independent, no dependencies on other files
   - Can test immediately with sample blade content

2. **Second**: `utils/blade_description_engine.py`
   - Uses snippet extractor from #1
   - Core retrieval logic

3. **Third**: `inference/blade_inference_strategy2.py`
   - Uses engine from #2
   - User-facing script

4. **Optional**: `tests/test_blade_strategy2.py`
   - Validates everything works

5. **Optional**: Modify `backend/main.py`
   - Production API

---

## Ready to Start?

**Option A**: Create all files automatically (I'll generate all 3-5 files now)
**Option B**: Create step-by-step (I'll create one file, you test, then next)
**Option C**: Just file #1 first (start with snippet extractor only)

Which approach do you prefer?
