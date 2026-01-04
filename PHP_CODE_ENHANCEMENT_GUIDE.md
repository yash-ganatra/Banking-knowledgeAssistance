# PHP Code Enhancement - Adding Code Snippets to RAG System

## 🎯 Overview

This guide explains how to enhance your PHP RAG system by adding **actual PHP code snippets** to your existing chunks. This improves retrieval accuracy by allowing semantic matching on both descriptions AND actual implementation code.

## 📊 Current vs Enhanced Approach

### Current (Description Only)
```json
{
  "chunk_id": "php_method_2",
  "method_name": "__construct",
  "method_description": "The __construct method initializes the object...",
  "dependencies": ["DB", "Cache", "Log"]
}
```

### Enhanced (Description + Code)
```json
{
  "chunk_id": "php_method_2",
  "method_name": "__construct",
  "method_description": "The __construct method initializes the object...",
  "dependencies": ["DB", "Cache", "Log"],
  "code_snippet": "public function __construct()\n{\n    parent::__construct();\n}",
  "code_num_lines": 4
}
```

## 🚀 Implementation Steps

### Step 1: Extract PHP Code (5-10 minutes)

Run the code extraction script to add code snippets to your existing chunks:

```bash
cd /Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance

python utils/extract_php_code.py
```

**What it does:**
- ✓ Reads `chunks/php_metadata_chunks_for_chromadb.json`
- ✓ Extracts actual PHP code from `code/code/` directory
- ✓ Matches classes and methods to chunks
- ✓ Adds `code_snippet`, `code_line_start`, `code_line_end` fields
- ✓ Saves to `chunks/php_metadata_chunks_with_code.json`

**Expected Output:**
```
📦 Enhancing PHP Chunks with Code Snippets
✓ Loaded 1200 chunks
✓ Successfully extracted: 1150 (95.8%)
✗ File not found: 30
✗ Code not found: 20
```

### Step 2: Re-embed with Code (15-30 minutes)

Re-embed all chunks using the enhanced version with code:

```bash
python embedding_vectordb/reembed_php_with_code.py
```

**What it does:**
- ✓ Loads enhanced chunks with code
- ✓ Combines description + code for embedding
- ✓ Uses BGE-M3 model (same as before, supports 8K tokens)
- ✓ Creates NEW vector DB: `vector_db/php_code_with_snippets_db/`
- ✓ Runs test queries to verify

**Expected Output:**
```
🚀 Re-embedding PHP Chunks with Code Snippets
✓ Loaded 1200 chunks
   1150 chunks have code snippets (95.8%)
🤖 Loading BGE-M3 model...
✓ Model loaded successfully!
📊 Processing 1200 chunks...
✅ Successfully embedded 1200 chunks into ChromaDB
```

### Step 3: Update Your Inference Script

Update your PHP inference script to use the new vector DB:

```python
# OLD
CHROMA_DIR = "./vector_db/php_code_chunks_db"

# NEW
CHROMA_DIR = "./vector_db/php_code_with_snippets_db"
```

## 🔍 Why This Approach Works

### ✅ Benefits

1. **Better Semantic Matching**: Queries like "how to query database" will match actual SQL queries in code
2. **Exact Pattern Finding**: Search for specific patterns (e.g., "Cache::put", "DB::select")
3. **Implementation Details**: See how methods actually work, not just descriptions
4. **Proven Pattern**: Your JS chunks already use this successfully with `code_snippet` field

### 🎯 BGE-M3 is Perfect for This

- **8K Token Context**: Handles large code snippets
- **Multilingual**: Understands PHP syntax + English descriptions
- **Dense Vectors**: Already configured in your system
- **No Changes Needed**: Same model, just richer input text

## 📝 File Structure

```
Banking-knowledgeAssistance/
├── utils/
│   └── extract_php_code.py              # NEW - Extracts code from PHP files
├── embedding_vectordb/
│   ├── embed_php_chunks_to_chromadb.py  # UPDATED - Includes code in embedding
│   └── reembed_php_with_code.py         # NEW - Re-embeds with code
├── chunks/
│   ├── php_metadata_chunks_for_chromadb.json      # OLD - No code
│   └── php_metadata_chunks_with_code.json         # NEW - With code
└── vector_db/
    ├── php_code_chunks_db/              # OLD - Description only
    └── php_code_with_snippets_db/       # NEW - Description + code
```

## 🧪 Testing

After re-embedding, test with queries that target code patterns:

```python
from embedding_vectordb.reembed_php_with_code import PHPChunksReEmbedder

embedder = PHPChunksReEmbedder(
    chroma_persist_directory="./vector_db/php_code_with_snippets_db",
    collection_name="php_code_chunks"
)

# Test queries
queries = [
    "API queue processing with retry logic",
    "database select query with date filtering",
    "cache checking before execution",
    "DB::select with schema and date"
]

for query in queries:
    print(f"\nQuery: {query}")
    results = embedder.test_search(query, n_results=3)
```

## ⚙️ Configuration Options

### Code Snippet Limits (in `extract_php_code.py`)

```python
MAX_CODE_LINES = 150  # Maximum lines per snippet
CONTEXT_LINES = 3     # Lines before/after for context
```

Adjust these if:
- **Token limit errors**: Reduce `MAX_CODE_LINES` to 100
- **Need more context**: Increase `CONTEXT_LINES` to 5
- **Large classes**: Split into multiple chunks

### Batch Size (in `reembed_php_with_code.py`)

```python
BATCH_SIZE = 16  # Adjust based on GPU memory
```

- **GPU with 8GB+**: Use 32
- **GPU with 4GB**: Use 16
- **CPU only**: Use 4-8

## 🔄 Comparison with JS Chunks

Your JS chunks already follow this pattern successfully:

| Aspect | JS Chunks | PHP Chunks (Now) |
|--------|-----------|------------------|
| Has Code | ✅ Yes (`code_snippet`) | ✅ Yes (`code_snippet`) |
| Has Description | ✅ Yes | ✅ Yes |
| Embedding Model | BGE-M3 | BGE-M3 |
| Vector DB | ChromaDB | ChromaDB |
| Pattern | Description + Code | Description + Code |

## 🎓 Next Steps

1. ✅ Run `extract_php_code.py` to create enhanced chunks
2. ✅ Run `reembed_php_with_code.py` to create new vector DB
3. ✅ Update inference scripts to use new DB path
4. ✅ Test with code-specific queries
5. 🔄 Optional: Update Blade chunks similarly (if needed)

## 💡 Tips

- **Keep both vector DBs** initially to compare results
- **Test thoroughly** before removing old DB
- **Monitor token usage** - code increases embedding size
- **Version your chunks** - keep dated backups

## 🐛 Troubleshooting

### "File not found" errors
- Check `file_path` in chunks matches actual file structure
- Verify `code/code/` directory exists

### Memory errors during embedding
- Reduce `BATCH_SIZE` in `reembed_php_with_code.py`
- Process in smaller batches

### Poor retrieval quality
- Check `code_snippet` is actually included in results
- Verify embeddings contain code (check `prepare_chunk_text()`)
- Try adjusting `MAX_CODE_LINES` for better snippets

## 📞 Support

If you encounter issues:
1. Check error messages in terminal
2. Verify file paths and directories exist
3. Ensure BGE-M3 model is downloaded
4. Check ChromaDB version compatibility

---

**Ready to enhance your PHP RAG with code? Start with Step 1!** 🚀
