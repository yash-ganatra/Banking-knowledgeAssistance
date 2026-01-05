# Alternative Strategies for Blade Retrieval (Without Truncation)

## Why Truncation is Problematic

You're correct that token-based truncation has issues:
- ❌ Loses semantic context from middle/end of documents
- ❌ Arbitrary cutoff points (400 tokens) may split important concepts
- ❌ Cross-encoder sees incomplete information
- ❌ Different files have different structures (important content might be at end)

## Better Alternative Strategies

---

## Strategy 1: **Semantic Chunking at Source** ⭐ (RECOMMENDED)

### Concept
Instead of truncating after embedding, intelligently split large blade files into semantic chunks **before** embedding.

### Key Principles
1. **Respect HTML/Blade structure** - split at logical boundaries
2. **Maintain context** - include parent information in each chunk
3. **Size-aware** - target 2000-4000 chars per chunk
4. **Overlap** - add 200-300 char overlap between chunks

### Implementation Procedure

#### Phase 1: Analysis (30 mins)
```bash
# 1. Identify problematic files
- Scan chunks/blade_views_enhanced.json
- Flag any file > 10,000 characters
- Analyze their structure (forms, sections, scripts)
```

#### Phase 2: Design Chunking Rules (1 hour)
```
For each large file, determine split strategy:

a) Multi-form files → Split by <form> tags
   - Each form = separate chunk
   - Include surrounding context (header/footer)
   
b) Multi-section files → Split by major <div> or <section>
   - Each major section = chunk
   - Preserve section headers
   
c) CSS/JS heavy files → Split by <style> and <script>
   - Separate CSS blocks
   - Separate JS blocks
   - Keep HTML separate
   
d) Iterative content (@foreach) → Extract loop body as template
   - Template chunk (the @foreach structure)
   - Context chunk (what data it renders)
```

#### Phase 3: Create Chunking Script (2-3 hours)
```
Script: utils/semantic_chunk_blade_views.py

Algorithm:
1. Load blade_views_enhanced.json
2. For each chunk where len(content) > 10000:
   a. Parse HTML structure (BeautifulSoup)
   b. Identify natural boundaries:
      - Forms
      - Major divs with IDs/classes
      - Style blocks
      - Script blocks
   c. Split at boundaries
   d. Create sub-chunks with metadata:
      {
        "parent_chunk_id": "original_id",
        "sub_chunk_index": 0,
        "sub_chunk_type": "form|section|style|script",
        "content": "...",
        "parent_context": "file overview from description",
        "siblings": ["other sub-chunk IDs"]
      }
   e. Maintain description for each sub-chunk
```

#### Phase 4: Re-embed (1 hour)
```
1. Run semantic chunking script
2. Generate new chunks file: blade_views_semantic_chunks.json
3. Embed with BGE-M3
4. Store in new collection: blade_views_semantic
```

#### Phase 5: Update Retrieval Logic (1 hour)
```
When retrieving:
1. Get top-K semantic chunks
2. If multiple chunks from same parent file:
   - Group them
   - Present as unified result
   - Show which sub-sections matched
```

### Advantages
- ✅ No information loss
- ✅ Each chunk is semantically complete
- ✅ Cross-encoder sees meaningful units
- ✅ Better granularity for retrieval
- ✅ Can still retrieve full file if needed

### Disadvantages
- ⚠️ Need to re-chunk and re-embed (one-time cost)
- ⚠️ More chunks = slightly slower retrieval

### Expected Results
- **form.blade.php** (261k chars) → 15-20 semantic chunks of ~15k chars each
- **Still large but manageable** - each chunk is a complete form or section
- **Better retrieval** - match specific forms, not whole file

---

## Strategy 2: **Description-Based Pre-filtering** ⭐⭐ (HIGHLY RECOMMENDED)

### Concept
Use enhanced descriptions (which are already concise) for initial retrieval and re-ranking. Only fetch full code for final top results.

### Key Innovation
Your descriptions are already GPT-enhanced and semantic (~400 chars). These are perfect for cross-encoder!

### Implementation Procedure

#### Phase 1: Create Description-Only Collection (2 hours)

```python
# Script: utils/create_description_collection.py

# Load existing chunks
chunks = json.load('chunks/blade_views_enhanced.json')

# Create description-only versions
desc_chunks = []
for chunk in chunks:
    desc_chunks.append({
        'chunk_id': chunk['chunk_id'],
        'file_name': chunk['file_name'],
        'description': chunk['description'],  # 400 chars, semantic
        'metadata': chunk['metadata']
        # NO 'content' field - saves massive space
    })

# Embed descriptions with BGE-M3
# Store in: vector_db/blade_descriptions_db
```

#### Phase 2: Create Full-Content Index (1 hour)
```python
# Simple key-value store (or keep existing collection)
# chunk_id → full_content mapping
# For quick lookup after description retrieval
```

#### Phase 3: Two-Phase Retrieval (2 hours)
```python
# New inference logic:

def retrieve_blade_views(query, top_k=5):
    # PHASE 1: Retrieve based on descriptions (fast)
    desc_results = description_collection.query(
        query_embedding,
        n_results=20  # Get more candidates
    )
    
    # PHASE 2: Re-rank descriptions with cross-encoder
    # Descriptions are ~400 chars = perfect for cross-encoder!
    pairs = [[query, r['description']] for r in desc_results]
    scores = cross_encoder.predict(pairs)
    
    # Sort by score
    ranked_results = sort_by_scores(desc_results, scores)[:top_k]
    
    # PHASE 3: Fetch full content only for top-k
    for result in ranked_results:
        result['full_content'] = fetch_content(result['chunk_id'])
    
    # PHASE 4: Smart context extraction
    for result in ranked_results:
        # Extract most relevant parts using query
        result['relevant_snippet'] = extract_relevant_snippet(
            result['full_content'], 
            query,
            max_chars=2000
        )
    
    return ranked_results
```

#### Phase 4: Smart Snippet Extraction (2 hours)
```python
def extract_relevant_snippet(content, query, max_chars=2000):
    """
    Extract most relevant parts of content for the query
    Without truncation - using semantic matching
    """
    # 1. Split content into sentences/blocks
    blocks = split_into_semantic_blocks(content)
    
    # 2. Score each block against query
    block_scores = []
    for block in blocks:
        # Use sentence transformer for quick scoring
        score = cosine_similarity(
            embed(query),
            embed(block)
        )
        block_scores.append((block, score))
    
    # 3. Select top scoring blocks until max_chars
    sorted_blocks = sorted(block_scores, key=lambda x: x[1], reverse=True)
    
    selected = []
    total_chars = 0
    for block, score in sorted_blocks:
        if total_chars + len(block) <= max_chars:
            selected.append(block)
            total_chars += len(block)
    
    # 4. Reorder selected blocks by original position
    # So context flows naturally
    return reorder_by_position(selected, blocks)
```

### Advantages
- ✅ **Perfect for cross-encoder** - descriptions fit in 512 tokens
- ✅ **No information loss** - full content available when needed
- ✅ **Fast** - descriptions are small, quick to retrieve
- ✅ **Accurate** - GPT-enhanced descriptions are semantic
- ✅ **Smart extraction** - only relevant parts sent to LLM
- ✅ **No re-chunking needed** - works with existing data

### Disadvantages
- ⚠️ Need to create description collection (1-time setup)
- ⚠️ Two-phase retrieval adds complexity

### Expected Results
- Cross-encoder accuracy: **High** (descriptions fit perfectly)
- Token usage: **3-5k tokens** (only relevant snippets)
- Retrieval speed: **Fast** (descriptions are lightweight)
- No data loss: **Full content available** when needed

---

## Strategy 3: **Query-Guided Context Compression** ⭐⭐⭐

### Concept
Instead of blind truncation, use the query to intelligently select relevant parts of large documents.

### Implementation Procedure

#### Phase 1: Sentence-Level Indexing (3 hours)
```python
# For each large blade file, create sentence embeddings

# Script: utils/create_sentence_index.py

def index_sentences(blade_content, chunk_id):
    # 1. Split into sentences (or semantic blocks)
    sentences = split_by_sentences_and_structure(blade_content)
    
    # 2. Embed each sentence
    sentence_embeddings = embed_model.encode(sentences)
    
    # 3. Store in separate collection
    sentence_collection.add(
        embeddings=sentence_embeddings,
        documents=sentences,
        metadatas=[{
            'parent_chunk_id': chunk_id,
            'sentence_index': i,
            'sentence_type': detect_type(sent)  # form|style|script|html
        } for i, sent in enumerate(sentences)],
        ids=[f"{chunk_id}_sent_{i}" for i in range(len(sentences))]
    )
```

#### Phase 2: Hierarchical Retrieval (2 hours)
```python
def hierarchical_retrieve(query, top_k=5):
    # STAGE 1: Chunk-level retrieval
    chunk_results = chunk_collection.query(query_embedding, n_results=15)
    
    # STAGE 2: For each chunk, find relevant sentences
    enhanced_results = []
    for chunk in chunk_results:
        # Get sentences from this chunk
        chunk_sentences = sentence_collection.query(
            query_embedding,
            where={'parent_chunk_id': chunk['id']},
            n_results=10
        )
        
        # Assemble relevant context from sentences
        relevant_context = assemble_context(
            chunk_sentences,
            max_chars=2000,
            preserve_structure=True
        )
        
        enhanced_results.append({
            'chunk_id': chunk['id'],
            'description': chunk['description'],
            'relevant_context': relevant_context,
            'matched_sentence_count': len(chunk_sentences)
        })
    
    # STAGE 3: Re-rank enhanced results
    return cross_encoder_rerank(query, enhanced_results)
```

#### Phase 3: Context Assembly (2 hours)
```python
def assemble_context(sentences, max_chars=2000, preserve_structure=True):
    """
    Intelligently assemble context from matched sentences
    """
    if preserve_structure:
        # Group sentences by type
        forms = [s for s in sentences if s['type'] == 'form']
        scripts = [s for s in sentences if s['type'] == 'script']
        html = [s for s in sentences if s['type'] == 'html']
        
        # Build structured context
        context = ""
        if forms:
            context += "Forms:\n" + "\n".join(forms[:3])
        if html:
            context += "\n\nHTML:\n" + "\n".join(html[:5])
        # etc...
    else:
        # Simple: top-ranked sentences in original order
        sorted_sentences = sort_by_score(sentences)
        context = "\n".join(s['text'] for s in sorted_sentences)
    
    return context[:max_chars]
```

### Advantages
- ✅ **Query-aware** - extracts what's relevant to query
- ✅ **Preserves structure** - can maintain HTML/form structure
- ✅ **Precise** - sentence-level matching
- ✅ **No truncation** - intelligently selects content

### Disadvantages
- ⚠️ Complex implementation
- ⚠️ Requires sentence-level indexing (storage increase)
- ⚠️ Multiple retrieval stages (slower)

---

## Strategy 4: **Hybrid: Multi-Vector Retrieval**

### Concept
Index each blade file with multiple embeddings:
1. Description embedding (semantic, high-level)
2. Form structure embedding (what forms exist)
3. Key directives embedding (@csrf, @auth, @include)
4. Variable embedding (what variables used)

### Implementation Procedure

#### Phase 1: Multi-Vector Extraction (3 hours)
```python
def extract_multi_vectors(blade_chunk):
    vectors = {}
    
    # Vector 1: Description (already exists)
    vectors['description'] = blade_chunk['description']
    
    # Vector 2: Form summary
    forms = extract_forms(blade_chunk['content'])
    vectors['forms'] = summarize_forms(forms)
    # "2 forms: login form with email/password, search form"
    
    # Vector 3: Blade directives
    directives = extract_directives(blade_chunk['content'])
    vectors['directives'] = " ".join(directives)
    # "@csrf @auth @include('header') @foreach($items as $item)"
    
    # Vector 4: Key variables
    variables = extract_variables(blade_chunk['content'])
    vectors['variables'] = " ".join(variables)
    # "$user $items $request"
    
    return vectors
```

#### Phase 2: Multi-Collection Storage (2 hours)
```python
# Create 4 separate collections:
# 1. blade_descriptions
# 2. blade_forms
# 3. blade_directives  
# 4. blade_variables

# Each with same chunk_ids for joining
```

#### Phase 3: Query Routing (2 hours)
```python
def smart_retrieve(query, top_k=5):
    # Analyze query to determine which vector(s) to use
    
    if "form" in query.lower() or "submit" in query.lower():
        # Prioritize form vector
        results = retrieve_from_forms_collection(query, weight=0.6)
        results += retrieve_from_descriptions(query, weight=0.4)
    
    elif "@" in query or "directive" in query.lower():
        # Prioritize directives vector
        results = retrieve_from_directives(query, weight=0.7)
        results += retrieve_from_descriptions(query, weight=0.3)
    
    else:
        # Default: description-first
        results = retrieve_from_descriptions(query, weight=0.8)
        results += retrieve_from_forms(query, weight=0.2)
    
    # Merge and deduplicate
    return merge_results(results, top_k)
```

### Advantages
- ✅ **Specialized retrieval** - different aspects indexed separately
- ✅ **Query-adaptive** - uses right vector for query type
- ✅ **Scalable** - can add more vectors as needed
- ✅ **No truncation** - each vector is purpose-built

### Disadvantages
- ⚠️ Complex setup (4x collections)
- ⚠️ Storage overhead
- ⚠️ Need query routing logic

---

## Strategy 5: **LLM-Based Context Summarization** (Most Accurate, Slower)

### Concept
Use an LLM to create query-specific summaries instead of truncation.

### Implementation Procedure

#### Phase 1: Cache Setup (1 hour)
```python
# Create cache for common query types
cache = {}  # (chunk_id, query_type) → summary
```

#### Phase 2: On-Demand Summarization (2 hours)
```python
def get_context_for_query(chunk, query, max_tokens=2000):
    # Check cache
    cache_key = f"{chunk['id']}_{query_category(query)}"
    if cache_key in cache:
        return cache[cache_key]
    
    # Generate query-specific summary
    prompt = f"""
    Given this Laravel Blade template and the user query,
    extract and return ONLY the parts relevant to answering the query.
    Preserve code structure. Maximum {max_tokens} tokens.
    
    Query: {query}
    
    Template:
    {chunk['content']}
    
    Relevant excerpt:
    """
    
    summary = call_llm(prompt, max_tokens=max_tokens)
    
    # Cache it
    cache[cache_key] = summary
    
    return summary
```

#### Phase 3: Parallel Processing (1 hour)
```python
# For top-5 retrieved chunks, summarize in parallel
import concurrent.futures

def parallel_summarize(chunks, query):
    with concurrent.futures.ThreadPoolExecutor() as executor:
        futures = [
            executor.submit(get_context_for_query, chunk, query)
            for chunk in chunks
        ]
        summaries = [f.result() for f in futures]
    return summaries
```

### Advantages
- ✅ **Most accurate** - LLM understands context
- ✅ **Query-specific** - tailored to each question
- ✅ **Preserves meaning** - semantic compression, not truncation
- ✅ **No re-embedding needed** - works with existing data

### Disadvantages
- ⚠️ Slower (LLM calls)
- ⚠️ Cost (API usage)
- ⚠️ Need caching for speed

---

## Recommended Implementation Path

### Phase 1: Quick Win (1-2 days)
**Strategy 2: Description-Based Pre-filtering**
- Easiest to implement
- Works with existing data
- High accuracy (descriptions fit cross-encoder)
- Immediate 80% token reduction

### Phase 2: Medium-term (3-5 days)
**Strategy 1: Semantic Chunking**
- Better granularity
- One-time re-chunking effort
- Long-term solution
- Best for production

### Phase 3: Advanced (Optional)
**Strategy 3 or 4**: If you need even better precision
**Strategy 5**: If cost/latency acceptable and need maximum accuracy

---

## Comparison Matrix

| Strategy | Setup Time | Accuracy | Speed | Token Reduction | Complexity |
|----------|-----------|----------|-------|-----------------|------------|
| 1. Semantic Chunking | 1-2 days | High | Fast | 60-70% | Medium |
| 2. Description-First | 1 day | Very High | Very Fast | 80-90% | Low |
| 3. Query-Guided | 3-4 days | Very High | Medium | 70-80% | High |
| 4. Multi-Vector | 3-5 days | High | Fast | 70-80% | High |
| 5. LLM Summarization | 1 day | Highest | Slow | 80-90% | Medium |

---

## My Recommendation

**Start with Strategy 2 (Description-Based Pre-filtering)**

### Why:
1. ✅ Your descriptions are already perfect (GPT-enhanced, ~400 chars)
2. ✅ Fits cross-encoder perfectly (no truncation needed)
3. ✅ Quick to implement (1-2 days)
4. ✅ No re-chunking required
5. ✅ Can add Strategy 1 later if needed
6. ✅ Solves both problems: token usage AND cross-encoder accuracy

### Implementation Steps:
```
Day 1:
- Morning: Create description-only collection
- Afternoon: Implement two-phase retrieval
- Test with sample queries

Day 2:
- Morning: Implement smart snippet extraction
- Afternoon: Integrate with inference notebook
- Evening: Performance testing & tuning
```

---

## Next Steps

1. **Review this document**
2. **Choose strategy** (I recommend #2)
3. **I'll provide detailed implementation plan** for chosen strategy
4. **We implement step-by-step with testing**

Which strategy interests you most? Or would you like me to explain any of them in more detail?
