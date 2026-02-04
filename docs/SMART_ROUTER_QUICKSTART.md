# Smart Query Router - Quick Start Guide

## 🚀 Getting Started

### 1. Start the Backend Server

```bash
cd backend
python main.py
```

The server will initialize all engines including the smart router. Look for:
```
INFO: Business Engine Ready
INFO: PHP Engine Ready
INFO: JS Engine Ready
INFO: Blade Engine Ready
INFO: LLM Service Ready
INFO: ✅ Unified Query Engine (Smart Router) Ready
```

### 2. Verify Setup

Check health endpoint:
```bash
curl http://localhost:8000/health
```

Expected response:
```json
{
    "status": "healthy",
    "engines": {
        "business_docs": true,
        "php_code": true,
        "js_code": true,
        "blade_templates": true,
        "llm_service": true,
        "smart_router": true
    }
}
```

### 3. Test Smart Router

#### Example 1: Single-Source Query (Business Documentation)

```bash
curl -X POST http://localhost:8000/inference/smart \
  -H "Content-Type: application/json" \
  -d '{
    "query": "What is a term deposit account?",
    "top_k": 5
  }'
```

**Expected routing:** `business_docs` only

#### Example 2: Multi-Source Query (Form + Backend)

```bash
curl -X POST http://localhost:8000/inference/smart \
  -H "Content-Type: application/json" \
  -d '{
    "query": "How does the loan application form work from frontend to backend?",
    "top_k": 5
  }'
```

**Expected routing:** `blade_templates` + `php_code` (parallel query + RRF fusion)

#### Example 3: Business + Implementation

```bash
curl -X POST http://localhost:8000/inference/smart \
  -H "Content-Type: application/json" \
  -d '{
    "query": "What are the business rules for loan approval and how are they implemented in code?",
    "top_k": 5
  }'
```

**Expected routing:** `business_docs` + `php_code`

### 4. Run Automated Tests

```bash
cd tests
python test_smart_router.py
```

This will:
- Test 8 different query types
- Verify routing accuracy
- Compare with direct endpoints
- Show detailed results

### 5. Understanding the Response

Smart router returns:

```json
{
    "results": [...],              // Retrieved documents with RRF scores
    "llm_response": "...",         // Generated answer
    "context_used": "...",         // Full context sent to LLM
    "routing_decision": {          // How query was routed
        "primary_source": "blade_templates",
        "secondary_sources": ["php_code"],
        "confidence": 0.92,
        "reasoning": "Query asks about form implementation",
        "query_type": "implementation",
        "requires_code": true
    },
    "sources_queried": [           // Which DBs were actually searched
        "blade_templates",
        "php_code"
    ]
}
```

## 📊 Interpreting Results

### Result Fields

Each result contains:
```json
{
    "id": "unique_id",
    "content": "The actual text/code snippet",
    "metadata": {
        "file_path": "path/to/file",
        "section": "section_name",
        ...
    },
    "source": "blade_templates",   // Which DB it came from
    "rrf_score": 0.0328,           // RRF fusion score (higher = better)
    "distance": 0.234,             // Original vector distance
    "original_rank": 1              // Rank in original source
}
```

### RRF Scores

- **0.02-0.04**: Highly relevant (top-ranked results)
- **0.01-0.02**: Relevant
- **< 0.01**: Lower relevance

RRF combines rankings from multiple sources:
```
RRF = 1/(60 + rank_in_source1) + 1/(60 + rank_in_source2) + ...
```

## 🎯 Query Tips for Best Results

### Single-Source Queries

| Query Pattern | Routes To | Example |
|---------------|-----------|---------|
| "What is [concept]?" | Business Docs | "What is a savings account?" |
| "Explain [business process]" | Business Docs | "Explain loan approval workflow" |
| "Show me [Controller/Model]" | PHP Code | "Show me the UserController" |
| "How does [Class] work?" | PHP Code | "How does AuthService work?" |
| "[Component] implementation" | JS Code | "Dashboard component implementation" |
| "React component for [X]" | JS Code | "React component for user profile" |
| "[template].blade.php" | Blade Templates | "login.blade.php structure" |
| "Form for [X]" | Blade Templates | "Form for account creation" |

### Multi-Source Queries

| Query Pattern | Routes To | Example |
|---------------|-----------|---------|
| "How does [X] work?" (ambiguous) | Multiple | "How does authentication work?" |
| "From frontend to backend [X]" | Blade + PHP (+JS) | "From frontend to backend: loan submission" |
| "Business rules for [X] implemented" | Business + PHP | "Business rules for withdrawals implemented" |
| "Complete flow of [X]" | All relevant | "Complete flow of user registration" |
| "[X] validation" (ambiguous) | PHP or JS (or both) | "Form validation logic" |

### Pro Tips

✅ **Do:**
- Be specific about what you want
- Mention domain explicitly if needed: "In the PHP code, show me..."
- Use technical terms: "controller", "component", "template", "model"
- Ask about flows/processes for multi-source queries

❌ **Avoid:**
- Overly vague queries: "How does this work?"
- Mixed concerns without context: "validation" (could be frontend or backend)
- Queries outside the domain: "How to deploy to AWS?"

## 🔧 Tuning Parameters

### confidence_threshold (default: 0.5)

Controls when secondary sources are included:

```json
{
    "query": "How does authentication work?",
    "confidence_threshold": 0.7  // Stricter - fewer secondary sources
}
```

- **0.3**: Permissive - more multi-source queries
- **0.5**: Balanced (recommended)
- **0.7**: Strict - mostly single-source queries

### top_k (default: 5)

Number of results to return:

```json
{
    "query": "Loan approval process",
    "top_k": 10  // More results
}
```

For multi-source queries, each source retrieves `top_k` results, then RRF selects the final `top_k`.

## 📈 Monitoring

### Check Routing Decisions

Look at backend logs:
```
INFO: Intent Classification: {
    "primary_source": "blade_templates",
    "confidence": 0.92,
    "reasoning": "Query explicitly asks about blade form template",
    ...
}
INFO: Retrieved 5 results from blade_templates
INFO: Retrieved 4 results from php_code
INFO: RRF merged 2 sources into 5 results
```

### Unexpected Routing?

If the router makes an unexpected decision:
1. Check the `reasoning` field in the response
2. Review the confidence score
3. Adjust query phrasing for clarity
4. Report persistent issues for prompt tuning

## 🆚 Smart Router vs Direct Endpoints

### When to use Smart Router (`/inference/smart`)

✅ **Use for:**
- User-facing queries (unknown intent)
- Exploratory questions
- Cross-domain questions
- Production application

### When to use Direct Endpoints

✅ **Use for:**
- Debugging specific databases
- Performance testing individual engines
- Known single-domain queries in batch processing
- Bypassing routing for specific use cases

**Note:** Both approaches return same quality results for single-domain queries.

## 🐛 Troubleshooting

### "Smart router not initialized"

**Cause:** One or more engines failed to load

**Solution:**
1. Check `/health` endpoint
2. Verify all vector DBs exist:
   - `vector_db/business_docs_chroma_db/`
   - `vector_db/php_vector_db/`
   - `vector_db/js_chroma_db/`
   - `vector_db/blade_views_chroma_db/`
3. Check backend logs for engine initialization errors

### Wrong sources queried

**Example:** Query about PHP code routed to Business Docs

**Solution:**
1. Check the `reasoning` in the response
2. Rephrase query to be more specific
3. Mention the domain explicitly: "In the PHP backend code..."

### Low confidence scores

**Symptom:** Confidence consistently < 0.5

**Cause:** Ambiguous queries

**Solution:**
- Add context to your query
- Be more specific about what you're looking for
- Accept that some queries genuinely span multiple domains

### Slow responses

**Multi-source queries expected latency:** ~1.2s

If slower:
1. Check individual engine health
2. Monitor backend resource usage
3. Consider reducing `top_k` for faster queries

## 📝 Next Steps

1. ✅ Test with your real queries
2. ✅ Run the test suite: `python tests/test_smart_router.py`
3. ✅ Integrate into frontend (see frontend integration below)
4. ✅ Monitor routing decisions and adjust as needed
5. ✅ Collect user feedback on response quality

## 🔗 Frontend Integration Example

```javascript
async function smartQuery(userQuery) {
    const response = await fetch('http://localhost:8000/inference/smart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            query: userQuery,
            top_k: 5,
            confidence_threshold: 0.5,
            conversation_id: currentConversationId  // optional
        })
    });
    
    const result = await response.json();
    
    // Show which sources were queried
    console.log('Routed to:', result.sources_queried);
    console.log('Confidence:', result.routing_decision.confidence);
    
    // Display LLM response
    displayResponse(result.llm_response);
    
    // Show source attribution
    displaySources(result.sources_queried);
    
    return result;
}
```

## 🎓 Learn More

- Full documentation: [SMART_ROUTER_GUIDE.md](./SMART_ROUTER_GUIDE.md)
- Test suite: [tests/test_smart_router.py](./tests/test_smart_router.py)
- Implementation: [backend/query_router.py](./backend/query_router.py)

---

**Questions?** Check the logs, run tests, or review the comprehensive guide!
