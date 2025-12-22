#!/usr/bin/env python3
"""
Embed enhanced JS chunks (with code and enhanced descriptions) into ChromaDB
Compatible with both local and Google Colab environments
Uses BGEM3FlagModel for better control over embeddings
"""

import json
import chromadb
from chromadb.config import Settings
from pathlib import Path
import hashlib
import os

# Check if running in Colab
try:
    import google.colab
    IN_COLAB = True
    print("🌐 Running in Google Colab")
except:
    IN_COLAB = False
    print("💻 Running locally")

# Configuration
if IN_COLAB:
    ENHANCED_CHUNKS_FILE = "js_file_chunks_enhanced_descriptions.json"
    CHROMA_DB_PATH = "./chroma_db"
else:
    BASE_DIR = Path(__file__).parent.parent
    ENHANCED_CHUNKS_FILE = BASE_DIR / "description" / "js_file_chunks_enhanced_descriptions.json"
    CHROMA_DB_PATH = BASE_DIR / "chroma_db"

COLLECTION_NAME = "js_code_knowledge"

# Initialize ChromaDB
print("🔧 Initializing ChromaDB...")
client = chromadb.PersistentClient(
    path=str(CHROMA_DB_PATH),
    settings=Settings(anonymized_telemetry=False)
)

# Delete existing collection if it exists
try:
    client.delete_collection(COLLECTION_NAME)
    print(f"🗑️  Deleted existing collection: {COLLECTION_NAME}")
except:
    pass

# Create collection (without embedding function - we'll add embeddings manually)
collection = client.create_collection(
    name=COLLECTION_NAME,
    metadata={
        "description": "JavaScript code with enhanced descriptions",
        "embedding_model": "BAAI/bge-m3"
    }
)
print(f"✅ Created collection: {COLLECTION_NAME}")

# Load BGE-M3 model
print("\n🤖 Loading BGE-M3 embedding model...")
try:
    from FlagEmbedding import BGEM3FlagModel
    
    model = BGEM3FlagModel(
        "BAAI/bge-m3",
        use_fp16=False  # Mac/CPU safe, set to True for GPU
    )
    print("✅ BGE-M3 model loaded successfully")
except ImportError:
    print("❌ FlagEmbedding not found. Installing...")
    os.system("pip install -U FlagEmbedding")
    from FlagEmbedding import BGEM3FlagModel
    model = BGEM3FlagModel("BAAI/bge-m3", use_fp16=False)
    print("✅ BGE-M3 model loaded successfully")


def create_chunk_text(chunk):
    """
    Create searchable text from chunk data
    """
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


def create_chunk_id(chunk, index):
    """
    Create unique ID for chunk using index to ensure uniqueness
    """
    base = f"{index}_{chunk.get('file_name', '')}_{chunk.get('chunk_type', '')}_{chunk.get('function_name', '')}_{chunk.get('endpoint_url', '')}_{chunk.get('line_start', 0)}"
    return hashlib.md5(base.encode()).hexdigest()


def embed_chunks():
    """
    Load enhanced chunks and embed into ChromaDB
    """
    # Handle file upload in Colab
    if IN_COLAB:
        if not os.path.exists(ENHANCED_CHUNKS_FILE):
            print("📤 Please upload your js_file_chunks_enhanced_descriptions.json file")
            from google.colab import files
            uploaded = files.upload()
            
            if not uploaded:
                print("❌ No file uploaded!")
                return
            
            uploaded_filename = list(uploaded.keys())[0]
            print(f"✅ Uploaded: {uploaded_filename}")
            
            if uploaded_filename != ENHANCED_CHUNKS_FILE:
                os.rename(uploaded_filename, ENHANCED_CHUNKS_FILE)
    
    print(f"\n📂 Loading enhanced chunks from: {ENHANCED_CHUNKS_FILE}")
    with open(ENHANCED_CHUNKS_FILE, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    print(f"✅ Found {len(chunks)} chunks")
    
    # Prepare data
    documents = []
    metadatas = []
    ids = []
    embeddings = []
    
    print("\n🔄 Processing chunks and generating embeddings...")
    import time
    start_time = time.time()
    
    for i, chunk in enumerate(chunks):
        # Create searchable text
        doc_text = create_chunk_text(chunk)
        
        # Create metadata
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
        
        # Create unique ID with index to ensure uniqueness
        chunk_id = create_chunk_id(chunk, i)
        
        documents.append(doc_text)
        metadatas.append(metadata)
        ids.append(chunk_id)
        
        if (i + 1) % 100 == 0:
            print(f"  Processed {i + 1}/{len(chunks)} chunks...")
    
    # Generate embeddings in batches
    print("\n🧮 Generating embeddings with BGE-M3...")
    batch_size = 50
    
    for i in range(0, len(documents), batch_size):
        batch_docs = documents[i:i+batch_size]
        
        print(f"  Embedding batch {i//batch_size + 1}/{(len(documents)-1)//batch_size + 1} ({len(batch_docs)} chunks)...", end=" ", flush=True)
        
        # Generate embeddings using BGE-M3
        batch_embeddings = model.encode(batch_docs)["dense_vecs"]
        embeddings.extend(batch_embeddings.tolist())
        
        elapsed = time.time() - start_time
        print(f"✅ ({elapsed:.1f}s elapsed)")
    
    # Insert into ChromaDB with embeddings
    print("\n💾 Inserting into ChromaDB...")
    batch_size = 100
    
    for i in range(0, len(documents), batch_size):
        batch_docs = documents[i:i+batch_size]
        batch_meta = metadatas[i:i+batch_size]
        batch_ids = ids[i:i+batch_size]
        batch_emb = embeddings[i:i+batch_size]
        
        collection.add(
            documents=batch_docs,
            metadatas=batch_meta,
            ids=batch_ids,
            embeddings=batch_emb
        )
        print(f"  Inserted batch {i//batch_size + 1}/{(len(documents)-1)//batch_size + 1}")
    
    total_time = time.time() - start_time
    print(f"\n⏱️  Total time: {total_time:.1f} seconds")
    
    print(f"\n{'='*60}")
    print(f"✨ EMBEDDING COMPLETE!")
    print(f"{'='*60}")
    print(f"Collection: {collection.name}")
    print(f"Total documents: {collection.count()}")
    print(f"Database location: {CHROMA_DB_PATH}")
    print(f"{'='*60}")


if __name__ == "__main__":
    print("="*60)
    print("🚀 Embedding JS Chunks to ChromaDB with BGE-M3")
    print("="*60)
    
    # Install dependencies in Colab
    if IN_COLAB:
        print("\n📦 Installing dependencies...")
        os.system("pip install -q chromadb FlagEmbedding")
        print("✅ Dependencies installed\n")
    
    embed_chunks()
    
    print("\n✨ Setup complete! Your knowledge base is ready for queries.")