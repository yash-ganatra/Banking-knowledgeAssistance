# Google Colab Setup Guide for BGE-M3 Embeddings

## ✅ Why BGE-M3 is Excellent for Your Use Case

### Performance Comparison

| Model | MTEB Score | Dim | Context | Speed | Quality |
|-------|-----------|-----|---------|-------|---------|
| **BGE-M3** | **66.0** | 1024 | 8192 | Fast | ⭐⭐⭐⭐⭐ |
| MPNet-base-v2 | 63.3 | 768 | 512 | Medium | ⭐⭐⭐⭐ |
| MiniLM-L6-v2 | 58.8 | 384 | 512 | Very Fast | ⭐⭐⭐ |

### Key Advantages for Banking Documentation

1. **8192 Token Context** - Can handle your longest chunks (max 598 tokens) easily
2. **Hybrid Scoring** - Dense + Sparse + ColBERT retrieval (best accuracy)
3. **Multilingual** - Future-proof if you add Hindi/regional language docs
4. **Semantic Understanding** - Excels at domain-specific terminology (FATCA, NPC, FEMA)
5. **T4 GPU Optimized** - 2-3x faster than CPU, perfect for Colab free tier

## 🚀 Google Colab Setup (Step-by-Step)

### Step 1: Enable T4 GPU

```python
# In Colab: Runtime > Change runtime type > T4 GPU
# Verify GPU is available
!nvidia-smi
```

### Step 2: Install Dependencies

```python
# Install required packages
!pip install -q sentence-transformers chromadb tqdm torch

# Verify PyTorch sees GPU
import torch
print(f"GPU Available: {torch.cuda.is_available()}")
print(f"GPU Name: {torch.cuda.get_device_name(0) if torch.cuda.is_available() else 'None'}")
```

### Step 3: Mount Google Drive

```python
from google.colab import drive
drive.mount('/content/drive')

# Create directories
!mkdir -p "/content/drive/MyDrive/CUBE_RAG/chunks"
!mkdir -p "/content/drive/MyDrive/CUBE_RAG/vector_db"
```

### Step 4: Upload Chunks File

Upload `cube_optimized_chunks.json` to:
```
/content/drive/MyDrive/CUBE_RAG/chunks/cube_optimized_chunks.json
```

### Step 5: Copy Embedding Script to Colab

```python
# Create the embedding script
%%writefile embed_cube_bge_m3.py

"""
Embed optimized CUBE chunks with BGE-M3 on Google Colab T4 GPU
"""

import json
import chromadb
from chromadb.config import Settings
from sentence_transformers import SentenceTransformer
from typing import List, Dict
from tqdm import tqdm
import os
import torch


class CUBEChunkEmbedder:
    def __init__(
        self,
        chunks_file: str,
        collection_name: str = "cube_docs_optimized",
        db_path: str = "./vector_db/cube_optimized_db",
        model_name: str = "BAAI/bge-m3",
        use_gpu: bool = True
    ):
        self.chunks_file = chunks_file
        self.collection_name = collection_name
        self.db_path = db_path
        self.model_name = model_name
        
        # GPU configuration
        self.device = "cuda" if use_gpu and torch.cuda.is_available() else "cpu"
        
        # Initialize
        self.chunks_data = None
        self.model = None
        self.client = None
        self.collection = None
    
    def load_chunks(self):
        """Load chunks from JSON file"""
        print(f"📂 Loading chunks from {self.chunks_file}...")
        with open(self.chunks_file, 'r', encoding='utf-8') as f:
            self.chunks_data = json.load(f)
        
        total_chunks = len(self.chunks_data['chunks'])
        print(f"✓ Loaded {total_chunks} chunks")
        
        # Print statistics
        stats = self.chunks_data.get('statistics', {})
        print(f"  - Page chunks: {stats.get('page_chunks', 0)}")
        print(f"  - Synthetic chunks: {stats.get('synthetic_chunks', 0)}")
        print(f"  - Avg tokens: {stats.get('avg_tokens', 0):.0f}")
    
    def initialize_model(self):
        """Initialize BGE-M3 model with GPU support"""
        print(f"\n🤖 Loading embedding model: {self.model_name}...")
        print(f"   Device: {self.device.upper()}")
        
        if self.device == "cuda":
            print(f"   GPU: {torch.cuda.get_device_name(0)}")
            print(f"   GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.2f} GB")
        
        self.model = SentenceTransformer(self.model_name, device=self.device)
        print("✓ Model loaded successfully")
    
    def initialize_chromadb(self):
        """Initialize ChromaDB client and collection"""
        print(f"\n🗄️  Initializing ChromaDB at {self.db_path}...")
        
        # Create directory if it doesn't exist
        os.makedirs(self.db_path, exist_ok=True)
        
        # Initialize ChromaDB client
        self.client = chromadb.PersistentClient(
            path=self.db_path,
            settings=Settings(
                anonymized_telemetry=False,
                allow_reset=True
            )
        )
        
        # Delete existing collection if it exists
        try:
            self.client.delete_collection(name=self.collection_name)
            print(f"  ⚠️  Deleted existing collection: {self.collection_name}")
        except:
            pass
        
        # Create new collection
        self.collection = self.client.create_collection(
            name=self.collection_name,
            metadata={"description": "CUBE Banking Documentation - BGE-M3 Embeddings"}
        )
        print(f"✓ Created collection: {self.collection_name}")
    
    def prepare_metadata_for_chroma(self, metadata: Dict) -> Dict:
        """Prepare metadata for ChromaDB (convert lists to strings)"""
        chroma_metadata = {}
        
        for key, value in metadata.items():
            if value is None:
                continue
            elif isinstance(value, (list, set)):
                # Convert lists/sets to comma-separated strings
                chroma_metadata[key] = ",".join(str(v) for v in value)
            elif isinstance(value, dict):
                # Skip complex dicts or stringify them
                chroma_metadata[key] = json.dumps(value)
            elif isinstance(value, bool):
                chroma_metadata[key] = str(value)
            elif isinstance(value, (int, float)):
                chroma_metadata[key] = value
            else:
                chroma_metadata[key] = str(value)
        
        return chroma_metadata
    
    def embed_chunks(self, batch_size: int = 16):
        """Embed all chunks with BGE-M3"""
        chunks = self.chunks_data['chunks']
        print(f"\n🔮 Embedding {len(chunks)} chunks with BGE-M3...")
        
        # Adjust batch size for GPU
        if self.device == "cuda":
            batch_size = 16  # Optimal for T4 GPU with BGE-M3
            print(f"   Using GPU-optimized batch size: {batch_size}")
        else:
            batch_size = 8  # Conservative for CPU
            print(f"   Using CPU batch size: {batch_size}")
        
        # Prepare data
        documents = []
        metadatas = []
        ids = []
        
        for idx, chunk in enumerate(tqdm(chunks, desc="Preparing chunks")):
            chunk_id = chunk['metadata'].get('chunk_id', f'chunk_{idx}')
            content = chunk['content']
            metadata = self.prepare_metadata_for_chroma(chunk['metadata'])
            
            # Add token count to metadata
            metadata['token_count'] = chunk['tokens']
            
            documents.append(content)
            metadatas.append(metadata)
            ids.append(chunk_id)
        
        # Generate embeddings in batches
        print(f"Generating BGE-M3 embeddings (1024-dim vectors)...")
        all_embeddings = []
        
        for i in tqdm(range(0, len(documents), batch_size), desc="Embedding batches"):
            batch = documents[i:i + batch_size]
            
            # BGE-M3 encoding with normalization (best for retrieval)
            embeddings = self.model.encode(
                batch,
                batch_size=batch_size,
                show_progress_bar=False,
                normalize_embeddings=True,  # Important for cosine similarity
                convert_to_tensor=False
            )
            all_embeddings.extend(embeddings.tolist())
        
        # Add to ChromaDB
        print("Adding to ChromaDB...")
        self.collection.add(
            embeddings=all_embeddings,
            documents=documents,
            metadatas=metadatas,
            ids=ids
        )
        
        print(f"✓ Successfully embedded and stored {len(chunks)} chunks")
    
    def verify_collection(self):
        """Verify the collection was created correctly"""
        print("\n🔍 Verifying collection...")
        
        count = self.collection.count()
        print(f"  Total documents in collection: {count}")
        
        # Get sample
        sample = self.collection.peek(limit=1)
        if sample['documents']:
            print(f"  Sample document length: {len(sample['documents'][0])} chars")
            print(f"  Sample metadata keys: {list(sample['metadatas'][0].keys())}")
            print(f"  Embedding dimension: {len(sample['embeddings'][0]) if sample.get('embeddings') else 'N/A'}")
    
    def test_queries(self):
        """Test some sample queries"""
        print("\n🧪 Testing sample queries with BGE-M3...")
        
        test_queries = [
            "How to open an NRI account?",
            "What is the NPC clearance process?",
            "Risk classification requirements",
            "Branch module functionality"
        ]
        
        for query in test_queries:
            print(f"\n  Query: '{query}'")
            
            # Generate query embedding with BGE-M3
            query_embedding = self.model.encode(
                [query],
                normalize_embeddings=True,
                convert_to_tensor=False
            )[0].tolist()
            
            # Search
            results = self.collection.query(
                query_embeddings=[query_embedding],
                n_results=3
            )
            
            print(f"  Top results:")
            for i, (doc, metadata) in enumerate(zip(results['documents'][0], results['metadatas'][0]), 1):
                page_name = metadata.get('page_name', metadata.get('title', 'N/A'))
                book_name = metadata.get('book_name', 'Synthetic')
                print(f"    {i}. {page_name} (from: {book_name})")
                print(f"       Preview: {doc[:100]}...")
    
    def run(self):
        """Run the complete embedding pipeline"""
        print("\n" + "="*70)
        print("CUBE DOCUMENTATION EMBEDDING - BGE-M3 on GPU")
        print("="*70)
        
        self.load_chunks()
        self.initialize_model()
        self.initialize_chromadb()
        self.embed_chunks()
        self.verify_collection()
        self.test_queries()
        
        print("\n" + "="*70)
        print("✅ EMBEDDING COMPLETE!")
        print(f"📁 Database location: {self.db_path}")
        print(f"📊 Collection name: {self.collection_name}")
        print("="*70 + "\n")


def main():
    """Main function for Google Colab"""
    
    # Google Drive paths
    chunks_file = "/content/drive/MyDrive/CUBE_RAG/chunks/cube_optimized_chunks.json"
    db_path = "/content/drive/MyDrive/CUBE_RAG/vector_db/cube_optimized_db"
    
    collection_name = "cube_docs_optimized"
    model_name = "BAAI/bge-m3"
    
    # Run embedder
    embedder = CUBEChunkEmbedder(
        chunks_file=chunks_file,
        collection_name=collection_name,
        db_path=db_path,
        model_name=model_name,
        use_gpu=True
    )
    embedder.run()


if __name__ == "__main__":
    main()
```

### Step 6: Run Embedding

```python
# Run the embedding script
!python embed_cube_bge_m3.py
```

## 📊 Expected Performance

### With T4 GPU:
- **Model Load Time**: ~30 seconds (downloading BGE-M3)
- **Embedding Time**: ~2-3 minutes for 134 chunks
- **GPU Memory Usage**: ~2.5 GB (well within T4's 16GB)
- **Vector Database Size**: ~150 MB

### Resource Usage:
```
134 chunks × 1024 dimensions × 4 bytes = ~550 KB (embeddings only)
+ metadata + documents = ~150 MB total
```

## 🧪 Testing Queries

After embedding, test with:

```python
from sentence_transformers import SentenceTransformer
import chromadb

# Load model
model = SentenceTransformer("BAAI/bge-m3", device="cuda")

# Connect to DB
client = chromadb.PersistentClient(
    path="/content/drive/MyDrive/CUBE_RAG/vector_db/cube_optimized_db"
)
collection = client.get_collection("cube_docs_optimized")

# Query
query = "How does NPC clearance work?"
query_embedding = model.encode([query], normalize_embeddings=True)[0].tolist()

results = collection.query(
    query_embeddings=[query_embedding],
    n_results=5
)

# Display results
for i, (doc, meta) in enumerate(zip(results['documents'][0], results['metadatas'][0]), 1):
    print(f"\n{i}. {meta['page_name']}")
    print(f"   Book: {meta['book_name']}")
    print(f"   Preview: {doc[:200]}...")
```

## ⚡ Optimization Tips

### 1. Batch Size Tuning
```python
# For T4 GPU (16GB)
batch_size = 16  # BGE-M3 optimal

# For larger GPUs (V100/A100)
batch_size = 32

# For CPU
batch_size = 8
```

### 2. Memory Management
```python
import gc
import torch

# Clear GPU memory between runs
torch.cuda.empty_cache()
gc.collect()
```

### 3. Persistent Storage
```python
# Always save to Google Drive, not /content (temporary)
db_path = "/content/drive/MyDrive/CUBE_RAG/vector_db/cube_optimized_db"

# Download vector DB to local machine later
from google.colab import files
!zip -r vector_db.zip "/content/drive/MyDrive/CUBE_RAG/vector_db"
files.download("vector_db.zip")
```

## 🎯 BGE-M3 vs Other Models for Your Use Case

| Aspect | BGE-M3 | MPNet | MiniLM |
|--------|--------|-------|--------|
| Banking terminology | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| Long context (598 tokens) | ✅ Handles easily | ⚠️ Truncates | ⚠️ Truncates |
| Multilingual future | ✅ 100+ langs | ❌ English only | ❌ English only |
| T4 GPU speed | ✅ 2-3 min | ✅ 1-2 min | ✅ <1 min |
| Accuracy (MTEB) | **66.0** | 63.3 | 58.8 |
| **Recommended** | ✅ **YES** | If speed critical | Not recommended |

## 🔍 Troubleshooting

### GPU Out of Memory
```python
# Reduce batch size
batch_size = 8  # Instead of 16
```

### Model Download Fails
```python
# Use mirror
import os
os.environ['HF_ENDPOINT'] = 'https://hf-mirror.com'
```

### ChromaDB Permission Error
```python
# Ensure Drive is mounted with write permissions
!chmod -R 777 "/content/drive/MyDrive/CUBE_RAG"
```

## ✅ Conclusion

**BGE-M3 is HIGHLY RECOMMENDED** for your CUBE banking documentation:
- Superior accuracy for domain-specific content
- Perfect context length (8192 >> 598 max chunk)
- T4 GPU compatible (free Colab tier)
- Future-proof for multilingual expansion
- Industry-standard for RAG systems

**Estimated total time on Colab T4**: 3-5 minutes for 134 chunks
