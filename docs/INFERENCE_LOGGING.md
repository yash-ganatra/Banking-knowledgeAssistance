# Inference Logging System

This document describes the comprehensive inference logging system for the RAG Banking Knowledge Assistant.

## Overview

The inference logging system tracks every inference request through the entire RAG pipeline, capturing:
- Query preprocessing and routing decisions
- Which vector databases were queried
- All chunks retrieved at each stage (initial retrieval, hybrid search, RRF fusion, reranking)
- Performance timing for each stage
- Final results sent to the LLM

## Components

### 1. Database Models (`backend/models.py`)

Two new tables are added:

#### `inference_logs` table
Stores high-level information about each inference request:
- Query and preprocessing info
- Routing decision details (primary source, secondary sources, confidence, reasoning)
- Retrieval statistics (total chunks, filtered, reranked)
- Hybrid search stats (dense count, sparse count, overlap)
- Timing metrics for each stage
- Success/failure status

#### `retrieval_details` table  
Stores detailed information about each chunk at the final stage:
- Chunk ID and source
- File path, class name, method name
- Scores at each stage (initial distance, BM25 score, RRF score, cross-encoder score)
- Final rank and whether it was included in context

### 2. Inference Logger Service (`backend/inference_logger.py`)

The `InferenceLogger` class provides methods to log each stage:

```python
logger = InferenceLogger(db_session)
logger.start_inference(query, endpoint="smart", top_k=5)

# Log routing decision
logger.log_routing_decision(
    primary_source="php_code",
    secondary_sources=["js_code"],
    confidence=0.85,
    reasoning="Query mentions PHP controller",
    query_type="implementation",
    routing_time_ms=150
)

# Log retrieval for each source
logger.log_initial_retrieval("php_code", results, retrieval_time_ms=200)

# Log hybrid search fusion
logger.log_hybrid_search("php_code", dense_count=10, sparse_count=10, merged_results)

# Log RRF fusion
logger.log_rrf_fusion(merged_results)

# Log reranking
logger.log_reranking(reranked_results, reranking_time_ms=300)

# Finalize and save
logger.finalize(success=True)
logger.save_to_db()
```

### 3. API Endpoints (`backend/routers/inference_logs.py`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/inference-logs/` | GET | List logs with filtering (hours_ago, success_only, source, query_type) |
| `/inference-logs/summary` | GET | Get summary statistics for a time period |
| `/inference-logs/{id}` | GET | Get detailed log with all retrieval details |
| `/inference-logs/{id}/pipeline` | GET | Get structured pipeline data for visualization |
| `/inference-logs/{id}` | DELETE | Delete a specific log |
| `/inference-logs/` | DELETE | Clean up old logs (by days_old parameter) |

### 4. Frontend Component (`frontend/src/components/InferenceLogs.jsx`)

A React component that provides:
- Summary statistics cards (total queries, success rate, avg response time, avg chunks)
- Filterable and expandable log list
- Detailed pipeline visualization modal showing:
  - All pipeline stages with timing
  - Chunks retrieved by source with scores
  - Hybrid search overlap analysis

## Setup

### 1. Run Database Migration

```bash
cd backend
python migrate_inference_logs.py
```

This creates the `inference_logs` and `retrieval_details` tables.

### 2. Restart Backend

The logging is automatically integrated into the `/inference/smart` endpoint.

### 3. Access the Logs UI

In the frontend, click on "Inference Logs" in the sidebar under Capabilities.

## Example Query Flow

1. **User Query**: "How does the AccountController handle deposits?"

2. **Preprocessing**: Query is cleaned and normalized

3. **Routing** (150ms):
   - Primary: `php_code` (confidence: 0.85)
   - Secondary: `business_docs`
   - Reasoning: "Query mentions specific PHP controller"

4. **Retrieval** (300ms):
   - php_code: 10 dense + 8 sparse = 15 unique (3 overlap)
   - business_docs: 10 dense + 6 sparse = 12 unique (4 overlap)

5. **RRF Fusion**: 27 chunks merged to 15 candidates

6. **Reranking** (200ms):
   - Cross-encoder scores applied
   - 15 → 5 chunks (min_score threshold: 2.0)

7. **LLM Generation** (800ms):
   - Model: llama-3.3-70b-versatile
   - Context: 5 chunks (~3000 tokens)

**Total Time**: 1450ms

## Monitoring Best Practices

1. **Check Success Rate**: A low success rate may indicate routing issues or insufficient chunks

2. **Monitor Hybrid Search Overlap**: Higher overlap means both dense and sparse found the same results (good for confidence)

3. **Review Reranking Dropoff**: If many chunks are filtered by cross-encoder, consider adjusting the min_relevance_score

4. **Track Timing by Stage**: Identify bottlenecks (routing vs retrieval vs LLM)

5. **Analyze Query Types**: Different query types may benefit from different routing strategies

## Cleanup

To prevent database bloat, periodically clean up old logs:

```bash
# Via API
curl -X DELETE "http://localhost:8000/inference-logs/?days_old=7"
```

Or schedule a cron job to call the cleanup endpoint.
