# Running in Google Colab - Instructions

## 🚀 Quick Start

### 1. Open Google Colab
Go to: https://colab.research.google.com/

### 2. Create New Notebook
Click "New Notebook"

### 3. Upload the Embedding Script

**Option A: Direct Copy-Paste**
```python
# Copy the entire content of embed_enhanced_chunks_to_chromadb.py
# Paste it into a Colab cell and run
```

**Option B: Upload File**
```python
from google.colab import files
uploaded = files.upload()  # Select embed_enhanced_chunks_to_chromadb.py
!python embed_enhanced_chunks_to_chromadb.py
```

### 4. Run the Script
The script will:
1. ✅ Automatically detect Colab environment
2. 📦 Install ChromaDB
3. 📤 Prompt you to upload `js_file_chunks_enhanced_descriptions.json`
4. 🔄 Process and embed all chunks
5. 💾 Save ChromaDB to `./chroma_db/` folder

---

## 📝 Complete Colab Code (Copy & Paste)

```python
# ============================================================
# STEP 1: Install Dependencies
# ============================================================
!pip install -q chromadb sentence-transformers

# ============================================================
# STEP 2: Upload Your JSON File
# ============================================================
from google.colab import files
print("📤 Upload js_file_chunks_enhanced_descriptions.json")
uploaded = files.upload()

# ============================================================
# STEP 3: Embedding Code (Run This Cell)
# ============================================================
import json
import chromadb
from chromadb.utils import embedding_functions
import hashlib
import os

# Configuration
ENHANCED_CHUNKS_FILE = "js_file_chunks_enhanced_descriptions.json"
CHROMA_DB_PATH = "./chroma_db"

# Initialize ChromaDB
client = chromadb.PersistentClient(path=CHROMA_DB_PATH)

# Delete existing collection if needed
try:
    client.delete_collection("js_code_knowledge")
    print("🗑️  Deleted existing collection")
except:
    pass

# Create BGE-M3 embedding function
print("🤖 Loading BGE-M3 embedding model...")
bge_m3_ef = embedding_functions.SentenceTransformerEmbeddingFunction(
    model_name="BAAI/bge-m3"
)
print("✅ BGE-M3 model loaded")

# Create new collection with BGE-M3
collection = client.create_collection(
    name="js_code_knowledge",
    embedding_function=bge_m3_ef,
    metadata={"description": "JavaScript code with enhanced descriptions", "embedding_model": "BAAI/bge-m3"}
)

def create_chunk_text(chunk):
    """Create searchable text from chunk"""
    parts = []
    
    if chunk.get('file_name'):
        parts.append(f"File: {chunk['file_name']}")
    
    if chunk.get('function_name'):
        parts.append(f"Function: {chunk['function_name']}")
    
    if chunk.get('endpoint_url'):
        parts.append(f"Endpoint: {chunk['endpoint_url']}")
    
    if chunk.get('description'):
        parts.append(f"Description: {chunk['description']}")
    
    if chunk.get('code_snippet'):
        parts.append(f"Code:\n{chunk['code_snippet']}")
    
    if chunk.get('parameters'):
        params = ', '.join(chunk['parameters'])
        parts.append(f"Parameters: {params}")
    
    if chunk.get('parent_file_dom_selectors'):
        selectors = ', '.join(chunk['parent_file_dom_selectors'][:5])
        parts.append(f"DOM Selectors: {selectors}")
    
    return "\n\n".join(parts)

def create_chunk_id(chunk):
    """Create unique ID for chunk"""
    base = f"{chunk.get('file_name', '')}_{chunk.get('chunk_type', '')}_{chunk.get('function_name', '')}_{chunk.get('endpoint_url', '')}"
    return hashlib.md5(base.encode()).hexdigest()

# Load chunks
print("📂 Loading enhanced chunks...")
with open(ENHANCED_CHUNKS_FILE, 'r', encoding='utf-8') as f:
    chunks = json.load(f)

print(f"✅ Found {len(chunks)} chunks")

# Prepare data
documents = []
metadatas = []
ids = []

for i, chunk in enumerate(chunks):
    doc_text = create_chunk_text(chunk)
    
    metadata = {
        'chunk_type': chunk.get('chunk_type', ''),
        'file_name': chunk.get('file_name', ''),
        'file_path': chunk.get('file_path', ''),
        'function_name': chunk.get('function_name', ''),
        'feature_domain': chunk.get('feature_domain', ''),
        'endpoint_url': chunk.get('endpoint_url', ''),
        'has_code': 'yes' if chunk.get('code_snippet') else 'no',
        'line_start': chunk.get('line_start', 0),
        'line_end': chunk.get('line_end', 0),
        'code_lines': chunk.get('code_lines', 0),
        'description_enhanced': 'yes' if chunk.get('description_enhanced') else 'no',
    }
    
    if chunk.get('parameters'):
        metadata['parameters'] = json.dumps(chunk['parameters'])
    
    chunk_id = create_chunk_id(chunk)
    
    documents.append(doc_text)
    metadatas.append(metadata)
    ids.append(chunk_id)
    
    if (i + 1) % 100 == 0:
        print(f"Processed {i + 1}/{len(chunks)} chunks...")

# Batch insert
print("\n🔄 Inserting into ChromaDB...")
print("⏳ This may take 2-5 minutes for BGE-M3 to generate embeddings...")
batch_size = 50  # Reduced for better progress visibility

import time
start_time = time.time()

for i in range(0, len(documents), batch_size):
    batch_docs = documents[i:i+batch_size]
    batch_meta = metadatas[i:i+batch_size]
    batch_ids = ids[i:i+batch_size]
    
    print(f"📝 Processing batch {i//batch_size + 1}/{(len(documents)-1)//batch_size + 1} ({len(batch_docs)} chunks)...", end=" ", flush=True)
    
    collection.add(
        documents=batch_docs,
        metadatas=batch_meta,
        ids=batch_ids
    )
    
    elapsed = time.time() - start_time
    print(f"✅ Done ({elapsed:.1f}s elapsed)")

total_time = time.time() - start_time
print(f"\n⏱️  Total embedding time: {total_time:.1f} seconds")

print(f"\n{'='*60}")
print(f"✨ EMBEDDING COMPLETE!")
print(f"{'='*60}")
print(f"Collection: {collection.name}")
print(f"Total documents: {collection.count()}")
print(f"Database location: {CHROMA_DB_PATH}")
print(f"{'='*60}")

# ============================================================
# STEP 4: Test Query (Optional)
# ============================================================
print("\n🔍 Testing with sample query...")

# Generate query embedding using BGE-M3
query_text = "How to fetch user details?"
query_embedding = model.encode([query_text])["dense_vecs"]

results = collection.query(
    query_embeddings=query_embedding.tolist(),
    n_results=3
)

print("\nTop 3 Results:")
for i, (doc, meta) in enumerate(zip(results['documents'][0], results['metadatas'][0]), 1):
    print(f"\n{i}. {meta['chunk_type']} - {meta['file_name']}")
    print(f"   Function: {meta.get('function_name', 'N/A')}")
    print(f"   Preview: {doc[:150]}...")

# ============================================================
# STEP 5: Download ChromaDB (Optional)
# ============================================================
print("\n📥 Download your ChromaDB folder:")
!zip -r chroma_db.zip chroma_db/
files.download('chroma_db.zip')
```

---

## 🎯 What This Does

1. **Installs ChromaDB** - Vector database for embeddings
2. **Uploads JSON** - Your enhanced chunks file
3. **Creates Collection** - Named "js_code_knowledge"
4. **Embeds Chunks** - Combines description + code for better search
5. **Stores Metadata** - File names, functions, DOM selectors, etc.
6. **Tests Search** - Sample query to verify it works
7. **Downloads DB** - Zip file you can use elsewhere

---

## 📊 Expected Output

```
📤 Upload js_file_chunks_enhanced_descriptions.json
✅ Found 498 chunks
Processed 100/498 chunks...
Processed 200/498 chunks...
...
🔄 Inserting into ChromaDB...
Inserted batch 1/5
Inserted batch 2/5
...
✨ EMBEDDING COMPLETE!
Collection: js_code_knowledge
Total documents: 498
```

---

## 🔍 Query Examples After Embedding

```python
# Search for encryption functions
results = collection.query(
    query_texts=["encryption decrypt functions"],
    n_results=5
)

# Search by feature
results = collection.query(
    query_texts=["user authentication login"],
    n_results=5,
    where={"feature_domain": "admin"}
)

# Search with filters
results = collection.query(
    query_texts=["AJAX endpoint POST request"],
    n_results=10,
    where={"chunk_type": "js_ajax_endpoint"}
)

# Print results
for doc, meta in zip(results['documents'][0], results['metadatas'][0]):
    print(f"\n{meta['file_name']} - {meta['function_name']}")
    print(doc[:200])
```

---

## 💾 Download and Use Locally

After running in Colab:

1. **Download the ZIP** - `chroma_db.zip` will be downloaded
2. **Extract locally** - Unzip to your project folder
3. **Use in your app** - Load with ChromaDB client

```python
# Local usage after download
import chromadb

client = chromadb.PersistentClient(path="./chroma_db")
collection = client.get_collection("js_code_knowledge")

# Now you can query!
results = collection.query(
    query_texts=["your search query"],
    n_results=5
)
```

---

## 🚨 Troubleshooting

### "No file uploaded"
- Make sure you click "Choose Files" and select the JSON

### "Collection already exists"
- The script automatically deletes old collections
- Or manually: `client.delete_collection("js_code_knowledge")`

### "Out of memory"
- Process in smaller batches (reduce batch_size from 100 to 50)

### "ChromaDB not found"
- Run: `!pip install chromadb`

---

## 📈 Next Steps

1. ✅ Embed chunks (you're doing this now)
2. 🤖 Build RAG chatbot using this knowledge base
3. 🔗 Integrate with your chat interface
4. 📊 Monitor query performance and accuracy

---

**Questions?** The vector DB is now ready for your RAG application!
