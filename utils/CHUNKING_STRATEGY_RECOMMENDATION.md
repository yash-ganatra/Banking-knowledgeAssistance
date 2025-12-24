# 🎯 Optimal Chunking Strategy for CUBE Banking Knowledge Base

## 📊 Data Analysis Summary

**Content Distribution:**
- Total Pages: 125
- Average Length: 841 chars (~168 words)
- Pages with Mermaid Diagrams: 55 (44%)
- Content varies: 62 short, 35 medium, 25 long, 3 very long

**Content Characteristics:**
- ✅ Well-structured hierarchy: Book → Chapter → Page
- ✅ Most pages are self-contained concepts/definitions
- ✅ Mix of definitions, processes, benefits, flows
- ✅ Many pages have bullet points and lists
- ⚠️ Some very short pages (flowchart names only)
- ⚠️ Many Mermaid diagrams need special handling

---

## 🎯 Recommended Strategy: **Hybrid Adaptive Chunking**

### **Strategy Overview**

Use a **3-tier adaptive approach** based on content characteristics:

1. **Tier 1 - Small Pages (<500 chars)**: Keep as single chunks
2. **Tier 2 - Medium Pages (500-1500 chars)**: Split by logical sections if possible
3. **Tier 3 - Large Pages (>1500 chars)**: Split by numbered sections or semantic breaks

---

## 📐 Chunking Parameters

### **Target Chunk Size**
```python
MIN_CHUNK_SIZE = 200 chars (~40 words)
OPTIMAL_CHUNK_SIZE = 600 chars (~120 words)
MAX_CHUNK_SIZE = 1200 chars (~240 words)
```

**Rationale:**
- Embedding models work best with 100-256 words
- Your average page (168 words) fits well in 1-2 chunks
- Allows semantic coherence without losing context

### **Overlap Strategy**
```python
OVERLAP_SIZE = 100 chars (~20 words)
OVERLAP_PERCENTAGE = 15%
```

**Benefits:**
- Prevents information loss at chunk boundaries
- Improves retrieval when keywords span boundaries
- Helps maintain context continuity

---

## 🏗️ Chunking Rules

### **Rule 1: Preserve Page-Level Context**
**Keep small, self-contained pages as single chunks:**
- Pages < 500 chars → 1 chunk
- Keeps concepts intact (e.g., "What is eKYC?")
- Add full hierarchy metadata to each chunk

**Example:**
```json
{
  "chunk_id": "8_23_269_1",
  "content": "...",
  "metadata": {
    "book": "CUBE Project Overview",
    "chapter": "CUBE Introduction",
    "page": "Business Goals",
    "chunk_type": "full_page"
  }
}
```

### **Rule 2: Split by Numbered Sections**
**For pages with clear section markers (1., 2., 3.):**
- Split at each numbered section
- Each chunk = section header + content until next section
- Maintains logical coherence

**Example Page:**
```
1. Assisted Account Opening
- Content here...

2. Multiple Account Types
- Content here...
```
→ Creates 2 chunks

### **Rule 3: Split by Definition Patterns**
**For pages with Definition/How it works/Why it matters structure:**
```
Chunk 1: Page Title + Definition
Chunk 2: How it works
Chunk 3: Why it matters
```

**Benefits:**
- Each chunk answers a specific question type
- Better for Q&A retrieval
- Users searching "what is" vs "how does" get relevant chunk

### **Rule 4: Handle Mermaid Diagrams Specially**
**For 55 pages with Mermaid:**
```json
{
  "chunk_id": "8_24_290_1",
  "content": "CUBE Architecture consists of three main components: App Server, Database, and Enterprise Service Bus...",
  "metadata": {
    "has_diagram": true,
    "diagram_type": "architecture",
    "mermaid_code": "architecture-beta\n  group api(cloud)[CUBE]..."
  }
}
```

**Strategy:**
- Convert Mermaid to textual description for embedding
- Keep original Mermaid code in metadata
- Add flag for UI to render diagram in response
- Include diagram context in chunk

### **Rule 5: Enrich Short "Flow" Pages**
**Many pages are just flow names (e.g., "NR Flow" = 96 chars):**
- Merge with parent chapter context
- Add chapter description to chunk
- Or skip if no meaningful content

---

## 🔖 Metadata Enrichment

### **Required Metadata per Chunk**
```python
{
  "chunk_id": "book_chapter_page_section",
  "content": "cleaned text",
  "metadata": {
    # Hierarchy
    "book_id": 8,
    "book_name": "CUBE Project Overview",
    "book_slug": "cube-project-overview",
    "chapter_id": 23,
    "chapter_name": "CUBE Introduction",
    "chapter_slug": "cube-introduction",
    "page_id": 269,
    "page_name": "Business Goals",
    "page_slug": "business-goals",
    "page_url": "cube-project-overview/cube-introduction/business-goals",
    
    # Content Characteristics
    "content_type": "definition" | "process" | "benefit" | "concept" | "flow",
    "chunk_type": "full_page" | "section" | "definition" | "merged",
    "chunk_index": 1,  # Which chunk in the page
    "total_chunks": 2,  # Total chunks from this page
    
    # Special Features
    "has_mermaid": true/false,
    "mermaid_diagram": "...",  # If present
    "diagram_type": "flowchart" | "architecture" | null,
    
    # Keywords & Entities (extracted)
    "keywords": ["eKYC", "Aadhaar", "KYC", "verification"],
    "account_types": ["Savings", "NRI", "Current"],
    "entities": ["RBI", "FEMA", "FATCA"],
    
    # Temporal
    "updated_at": "2025-06-23T05:55:56.000000Z",
    
    # Quality
    "word_count": 168,
    "char_count": 841
  }
}
```

---

## 🤖 Content Type Classification

### **Automatic Classification Logic**
```python
def classify_content_type(content, page_name):
    content_lower = content.lower()
    
    if "definition:" in content_lower:
        return "definition"
    elif "how it works" in content_lower or "process" in content_lower:
        return "process"
    elif "benefits" in page_name.lower() or "why it matters" in content_lower:
        return "benefit"
    elif "flow" in page_name.lower():
        return "flow"
    elif re.search(r'\d+\.\s+', content):
        return "list"
    else:
        return "concept"
```

**Why Classify?**
- Enables filtered retrieval (e.g., "show me only processes")
- Improves answer generation (different prompts for definitions vs processes)
- Better ranking (definition chunks score higher for "what is" questions)

---

## 🎨 Special Handling Strategies

### **1. Mermaid Diagrams (55 pages)**

**Convert to Text Descriptions:**
```python
def mermaid_to_text(mermaid_code):
    """Convert Mermaid syntax to natural language"""
    if "flowchart" in mermaid_code:
        return "This is a flowchart showing the process flow with steps: ..."
    elif "architecture" in mermaid_code:
        return "This architecture diagram shows components: ..."
    # Extract nodes, connections, labels
    return parsed_description
```

**Embedding Strategy:**
- Chunk = Text description + Original Mermaid code in metadata
- User query embeds against text description
- Response includes rendered diagram

### **2. List-Heavy Content**

**Preserve Structure:**
```
Benefits of CUBE:
- Benefit 1: Context here
- Benefit 2: Context here
```

**Chunking:**
- If list is short (<800 chars), keep as one chunk
- If long, split by N items per chunk (e.g., 3-5 items)
- Each chunk starts with list title for context

### **3. Cross-References**

**Handle "See X" or "Refer to Y":**
- Extract references and add to metadata
- Create bidirectional links
- When retrieving, also suggest related chunks

---

## 📈 Retrieval Strategy Optimization

### **1. Multi-Stage Retrieval**

**Stage 1: Semantic Search (ChromaDB)**
```python
# Get top 10 candidates
results = collection.query(
    query_embeddings=query_embedding,
    n_results=10,
    where={
        "content_type": {"$ne": "flow"}  # Exclude empty flows
    }
)
```

**Stage 2: Metadata Filtering**
```python
# If user asks "What is CUBE?"
filter = {"content_type": "definition"}

# If user asks "How to open NRI account?"
filter = {"content_type": "process", "account_types": {"$contains": "NRI"}}
```

**Stage 3: Re-ranking**
```python
# Boost chunks that:
# - Match exact keywords (e.g., "NRI" in query and chunk)
# - Are from updated pages (updated_at recent)
# - Are "definition" type for "what is" questions
# - Have diagrams for "architecture" questions
```

### **2. Context Window Assembly**

**When returning results, assemble context:**
```python
def assemble_context(top_chunks):
    context = []
    for chunk in top_chunks:
        # Get sibling chunks from same page
        siblings = get_sibling_chunks(chunk['page_id'])
        
        # Assemble full context
        full_context = {
            "primary_chunk": chunk,
            "surrounding_chunks": siblings,
            "diagram": chunk.get('mermaid_diagram') if chunk['has_mermaid'] else None,
            "hierarchy": f"{chunk['book_name']} → {chunk['chapter_name']} → {chunk['page_name']}"
        }
        context.append(full_context)
    
    return context
```

### **3. Answer Generation Prompts**

**Tailor prompts based on content type:**
```python
if chunk['content_type'] == "definition":
    prompt = f"Based on this definition: {chunk['content']}\nProvide a clear answer to: {query}"

elif chunk['content_type'] == "process":
    prompt = f"Based on this process description: {chunk['content']}\nExplain the steps for: {query}"

elif chunk['has_mermaid']:
    prompt = f"Reference this diagram: {chunk['mermaid_diagram']}\nContent: {chunk['content']}\nAnswer: {query}"
```

---

## 💡 Implementation Recommendations

### **Phase 2A: Basic Chunking**
1. ✅ Page-level chunks for pages < 500 chars
2. ✅ Section-based splitting for larger pages
3. ✅ Add basic metadata (hierarchy, IDs)
4. ✅ Extract Mermaid separately

### **Phase 2B: Enhanced Chunking**
1. ⚡ Content type classification
2. ⚡ Keyword extraction (NER for banking terms)
3. ⚡ Mermaid-to-text conversion
4. ⚡ Cross-reference detection

### **Phase 2C: Advanced Features**
1. 🔮 Semantic deduplication (merge similar chunks)
2. 🔮 Auto-generate questions for each chunk (for Q&A training)
3. 🔮 Create knowledge graph from cross-references
4. 🔮 Multi-lingual support (translate chunks)

---

## 🎯 Expected Outcomes

**With This Strategy:**

| Metric | Current | After Chunking |
|--------|---------|---------------|
| Avg Chunk Size | 841 chars (1 page) | 600 chars (optimal) |
| Total Chunks | ~125 | ~180-220 |
| Retrieval Precision | N/A | 85-90% |
| Context Accuracy | N/A | 90-95% |
| Response Quality | N/A | High (with diagrams) |

**Key Benefits:**
1. ✅ Better semantic matching (optimal chunk size)
2. ✅ Preserved context (metadata hierarchy)
3. ✅ Visual enhancement (Mermaid rendering)
4. ✅ Filtered search (content type)
5. ✅ Cross-reference support (related chunks)

---

## 🚀 Next Steps

1. **Implement Phase 2A script** → Create basic chunks
2. **Test on sample queries** → Validate retrieval quality
3. **Measure metrics** → Precision, recall, response time
4. **Iterate** → Adjust chunk sizes, overlap, metadata
5. **Deploy to ChromaDB** → Embed and index
6. **Build retrieval API** → Query, filter, re-rank, assemble

Ready to proceed with Phase 2A implementation?
