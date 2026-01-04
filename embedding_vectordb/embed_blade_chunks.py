#!/usr/bin/env python3
"""
Embed Blade View Chunks to ChromaDB
Embeds the *functional descriptions* of Blade templates for semantic retrieval.
"""

import json
import os
import chromadb
from chromadb.config import Settings
from pathlib import Path

# Configuration
BASE_DIR = Path(__file__).parent.parent
INPUT_FILE = BASE_DIR / "chunks/blade_views_enhanced.json"
CHROMA_DB_PATH = BASE_DIR / "vector_db/blade_views_chroma_db"
COLLECTION_NAME = "blade_views_knowledge"

def main():
    print("🚀 Starting Blade View Embedding...")
    
    # 1. Load Chunks
    if not INPUT_FILE.exists():
        print(f"❌ Error: Input file not found at {INPUT_FILE}")
        return
        
    with open(INPUT_FILE, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
        
    print(f"📂 Loaded {len(chunks)} chunks")
    
    # 2. Filter for Enhanced Chunks
    # We only want to embed chunks that have a description to search against
    valid_chunks = [c for c in chunks if c.get('description_enhanced')]
    print(f"🔍 Found {len(valid_chunks)} enhanced chunks to embed")
    
    if not valid_chunks:
        print("⚠️  No enhanced chunks found. Run utils/enhance_blade_chunks.py first.")
        return

    # 3. Initialize ChromaDB
    print(f"🔧 Initializing ChromaDB at {CHROMA_DB_PATH}")
    client = chromadb.PersistentClient(
        path=str(CHROMA_DB_PATH),
        settings=Settings(anonymized_telemetry=False)
    )
    
    # Reset collection
    try:
        client.delete_collection(COLLECTION_NAME)
        print(f"🗑️  Deleted existing collection: {COLLECTION_NAME}")
    except:
        pass
        
    collection = client.create_collection(
        name=COLLECTION_NAME,
        metadata={"embedding_model": "BAAI/bge-m3"}
    )
    
    # 4. Load Embedding Model
    print("🤖 Loading Embedding Model (BAAI/bge-m3)...")
    try:
        from FlagEmbedding import BGEM3FlagModel
        model = BGEM3FlagModel("BAAI/bge-m3", use_fp16=False)
    except ImportError:
        print("❌ FlagEmbedding not found. Please install it.")
        return

    # 5. Generate Embeddings (of DESCRIPTIONS)
    documents = [] # The raw code (for result display)
    embeddings = [] # The vector (of the description)
    metadatas = []
    ids = []
    
    # We need to store Code in 'documents' so it's returned by query, 
    # but we embed 'description' for the search vector.
    # Chroma's default 'add' calculates embedding from 'documents' if 'embeddings' not provided.
    # Here we PROVIDE 'embeddings' explicitly derived from descriptions.
    
    batch_size = 50
    total_batches = (len(valid_chunks) + batch_size - 1) // batch_size
    
    print(f"🔄 Processing {len(valid_chunks)} chunks in {total_batches} batches...")
    
    for i in range(0, len(valid_chunks), batch_size):
        batch = valid_chunks[i:i+batch_size]
        
        # Prepare batch data
        batch_descriptions = [c['description'] for c in batch]
        batch_codes = [c['content'] for c in batch]
        batch_ids = [c['chunk_id'] for c in batch]
        
        # Prepare metadata
        batch_metadatas = []
        for c in batch:
            meta = c.get('metadata', {}).copy()
            meta['file_name'] = c.get('file_name', '')
            meta['section'] = c.get('section_name', '')
            meta['description'] = c.get('description', '') # Store desc in metadata too used for context
            # Chroma metadata must be flat primitives
            clean_meta = {}
            for k, v in meta.items():
                if isinstance(v, (str, int, float, bool)):
                    clean_meta[k] = v
                else:
                    clean_meta[k] = str(v)
            batch_metadatas.append(clean_meta)
            
        # Generate Embeddings from DESCRIPTIONS
        print(f"  ⚡ Embedding batch {i//batch_size + 1}/{total_batches}...", end="", flush=True)
        batch_embeddings = model.encode(batch_descriptions)['dense_vecs'].tolist()
        
        # Add to collection
        # documents=batch_codes -> This ensures the CODE is returned when searched
        collection.add(
            ids=batch_ids,
            embeddings=batch_embeddings,
            documents=batch_codes, 
            metadatas=batch_metadatas
        )
        print(" Done ✅")

    print(f"\n🎉 Successfully embedded {len(valid_chunks)} Blade view chunks!")
    print(f"Database: {CHROMA_DB_PATH}")

if __name__ == "__main__":
    main()
