#!/usr/bin/env python3
"""
Re-embed PHP chunks with code snippets into ChromaDB.
Optimized for Google Colab with T4 GPU.
Simple workflow: Upload JSON → Embed → Download ZIP
"""

import json
import chromadb
from chromadb.config import Settings
from FlagEmbedding import BGEM3FlagModel
from tqdm import tqdm
import os
from pathlib import Path
from typing import List, Dict
import numpy as np
import sys
import gc

# ============================================================================
# COLAB DETECTION & SETUP
# ============================================================================
def is_colab():
    """Check if running in Google Colab."""
    try:
        import google.colab
        return True
    except:
        return False

def setup_colab_environment():
    """Setup Google Colab environment."""
    print("🔧 Setting up Google Colab environment...")
    
    # Install packages
    print("\n📦 Installing required packages...")
    os.system("pip install -q chromadb FlagEmbedding sentence-transformers")
    
    # Check GPU
    import torch
    if torch.cuda.is_available():
        gpu_name = torch.cuda.get_device_name(0)
        print(f"✓ GPU detected: {gpu_name}")
        print(f"✓ CUDA version: {torch.version.cuda}")
    else:
        print("⚠️  No GPU detected! Go to Runtime > Change runtime type > GPU (T4)")

# Run setup if in Colab
IN_COLAB = is_colab()
if IN_COLAB:
    setup_colab_environment()

# ============================================================================
# CONFIGURATION
# ============================================================================
if IN_COLAB:
    BASE_DIR = Path("/content")
    ENHANCED_CHUNKS_FILE = BASE_DIR / "php_metadata_chunks_with_code.json"
    CHROMA_DIR = BASE_DIR / "php_code_with_snippets_db"
else:
    BASE_DIR = Path(__file__).parent.parent
    ENHANCED_CHUNKS_FILE = BASE_DIR / "chunks" / "php_metadata_chunks_with_code.json"
    CHROMA_DIR = BASE_DIR / "vector_db" / "php_code_with_snippets_db"

COLLECTION_NAME = "php_code_chunks"
BATCH_SIZE = 8 if IN_COLAB else 16  # Reduced for stability
RESET_COLLECTION = True
CHECKPOINT_EVERY = 50  # Save progress every N chunks


class PHPChunksReEmbedder:
    """Re-embeds PHP chunks with code into ChromaDB using BGE-M3."""
    
    def __init__(self, chroma_persist_directory: str, collection_name: str = "php_code_chunks", 
                 model_name: str = "BAAI/bge-m3"):
        print("🔧 Initializing ChromaDB client...")
        os.makedirs(chroma_persist_directory, exist_ok=True)
        
        self.client = chromadb.PersistentClient(
            path=chroma_persist_directory,
            settings=Settings(anonymized_telemetry=False, allow_reset=True)
        )
        
        print(f"🤖 Loading BGE-M3 model: {model_name}...")
        if IN_COLAB:
            print("   📥 Downloading model (~2GB, cached after first run)")
            print("   ⏱️  This may take 2-5 minutes...")
        
        import torch
        use_gpu = torch.cuda.is_available()
        device = "cuda" if use_gpu else "cpu"
        
        self.model = BGEM3FlagModel(model_name, use_fp16=use_gpu, device=device)
        
        print(f"✓ Model loaded on {device.upper()}!")
        if use_gpu:
            print(f"✓ Using GPU acceleration with fp16 precision")
        
        self.collection_name = collection_name
        self.collection = None
        
    def create_or_get_collection(self, reset: bool = False):
        if reset:
            try:
                self.client.delete_collection(name=self.collection_name)
                print(f"🗑️  Deleted existing collection")
            except:
                pass
        
        self.collection = self.client.get_or_create_collection(
            name=self.collection_name,
            metadata={
                "description": "PHP code chunks with descriptions and code snippets",
                "embedding_model": "BAAI/bge-m3",
                "includes_code": "true"
            }
        )
        print(f"✓ Collection ready. Current count: {self.collection.count()}")
        
    def prepare_chunk_text(self, chunk: Dict) -> str:
        chunk_type = chunk.get("chunk_type", "")
        text_parts = []
        
        if chunk_type == "php_class":
            text_parts.append(f"Class: {chunk.get('class_name', '')}")
            text_parts.append(f"Description: {chunk.get('class_description', '')}")
            text_parts.append(f"File: {chunk.get('file_path', '')}")
            
            methods = chunk.get('methods', [])
            if methods:
                text_parts.append(f"Methods: {', '.join(methods)}")
            
            dependencies = chunk.get('dependencies', [])
            if dependencies:
                text_parts.append(f"Dependencies: {', '.join(dependencies)}")
            
            code_snippet = chunk.get('code_snippet')
            if code_snippet:
                text_parts.append(f"\nCode:\n{code_snippet}")
                
        elif chunk_type == "php_method":
            text_parts.append(f"Method: {chunk.get('class_name', '')}.{chunk.get('method_name', '')}")
            text_parts.append(f"Description: {chunk.get('method_description', '')}")
            text_parts.append(f"Parameters: {chunk.get('parameters', '')}")
            text_parts.append(f"Returns: {chunk.get('return_type', '')}")
            text_parts.append(f"File: {chunk.get('file_path', '')}")
            
            code_snippet = chunk.get('code_snippet')
            if code_snippet:
                text_parts.append(f"\nCode:\n{code_snippet}")
        
        return "\n".join(text_parts)
    
    def prepare_metadata(self, chunk: Dict) -> Dict:
        metadata = {}
        
        # Handle base fields - ensure no None values
        for key in ["chunk_id", "chunk_type", "language", "file_path"]:
            if key in chunk and chunk[key] is not None:
                metadata[key] = str(chunk[key])
            elif key in chunk:
                metadata[key] = ""
        
        chunk_type = chunk.get("chunk_type", "")
        
        if chunk_type == "php_class":
            metadata["class_name"] = chunk.get("class_name") or ""
            metadata["num_methods"] = int(chunk.get("num_methods") or 0)
            metadata["methods"] = ", ".join(chunk.get("methods") or [])
            metadata["dependencies"] = ", ".join(chunk.get("dependencies") or [])
            
        elif chunk_type == "php_method":
            metadata["class_name"] = chunk.get("class_name") or ""
            metadata["method_name"] = chunk.get("method_name") or ""
            metadata["return_type"] = chunk.get("return_type") or ""
            params = chunk.get("parameters")
            metadata["parameters"] = str(params) if params is not None else ""
        
        # Code-related metadata
        if chunk.get("code_snippet"):
            metadata["has_code"] = True
            metadata["code_num_lines"] = int(chunk.get("code_num_lines") or 0)
            metadata["code_line_start"] = int(chunk.get("code_line_start") or 0)
            metadata["code_line_end"] = int(chunk.get("code_line_end") or 0)
        else:
            metadata["has_code"] = False
        
        return metadata
    
    def embed_chunks(self, chunks: List[Dict], batch_size: int = 8):
        print(f"\n📊 Processing {len(chunks)} chunks...")
        
        stats = {
            'total': len(chunks),
            'with_code': sum(1 for c in chunks if c.get('code_snippet')),
            'without_code': sum(1 for c in chunks if not c.get('code_snippet'))
        }
        
        print(f"   ✓ {stats['with_code']} chunks with code")
        print(f"   ⚠ {stats['without_code']} chunks without code")
        print(f"\n🔄 Embedding in batches of {batch_size}...")
        print(f"💾 Checkpoints every {CHECKPOINT_EVERY} chunks")
        
        # Check for existing progress
        start_idx = self.collection.count()
        if start_idx > 0:
            print(f"📍 Resuming from chunk {start_idx}")
            chunks = chunks[start_idx:]
        
        for i in tqdm(range(0, len(chunks), batch_size), desc="Embedding batches"):
            batch = chunks[i:i + batch_size]
            texts = [self.prepare_chunk_text(chunk) for chunk in batch]
            
            # Encode with smaller batch for stability
            embeddings = self.model.encode(
                texts,
                batch_size=min(batch_size, 4),  # Even smaller internal batch
                max_length=8192
            )['dense_vecs']
            
            ids = [chunk["chunk_id"] for chunk in batch]
            metadatas = [self.prepare_metadata(chunk) for chunk in batch]
            
            self.collection.add(
                ids=ids,
                embeddings=embeddings.tolist(),
                metadatas=metadatas,
                documents=texts
            )
            
            # Clear memory every batch
            if i % CHECKPOINT_EVERY == 0 and i > 0:
                gc.collect()
                if IN_COLAB:
                    import torch
                    if torch.cuda.is_available():
                        torch.cuda.empty_cache()
                print(f"\n💾 Checkpoint: {self.collection.count()} chunks embedded")
        
        # Final cleanup
        gc.collect()
        if IN_COLAB:
            import torch
            if torch.cuda.is_available():
                torch.cuda.empty_cache()
        
        print(f"\n✅ Successfully embedded {len(chunks)} chunks")
        print(f"✓ Collection now contains {self.collection.count()} items")
    
    def test_search(self, query: str, n_results: int = 5):
        print(f"\n🔍 Testing: '{query}'")
        
        query_embedding = self.model.encode(query)['dense_vecs']
        results = self.collection.query(
            query_embeddings=query_embedding.tolist(),
            n_results=n_results
        )
        
        print(f"\n📋 Top {n_results} results:")
        for i, (doc, metadata, distance) in enumerate(zip(
            results['documents'][0],
            results['metadatas'][0],
            results['distances'][0]
        ), 1):
            print(f"\n{i}. Distance: {distance:.4f}")
            print(f"   Type: {metadata.get('chunk_type', 'N/A')}")
            print(f"   Class: {metadata.get('class_name', 'N/A')}")
            if metadata.get('method_name'):
                print(f"   Method: {metadata.get('method_name', 'N/A')}")
            print(f"   Has Code: {metadata.get('has_code', False)}")
            print(f"   Preview: {doc[:200]}...")
        
        return results


def main():
    print("=" * 80)
    print("🚀 Re-embedding PHP Chunks with Code Snippets")
    if IN_COLAB:
        print("   🌐 Running on Google Colab")
    print("=" * 80)
    
    # Check/upload file
    if not ENHANCED_CHUNKS_FILE.exists():
        if IN_COLAB:
            print(f"\n📤 Please upload your chunks file...")
            from google.colab import files
            uploaded = files.upload()
            
            for filename in uploaded.keys():
                if filename.endswith('.json'):
                    os.rename(filename, str(ENHANCED_CHUNKS_FILE))
                    print(f"✓ Uploaded: {filename}")
                    break
            else:
                print("❌ No JSON file uploaded!")
                return
        else:
            print(f"\n❌ File not found: {ENHANCED_CHUNKS_FILE}")
            print("   Run: python utils/extract_php_code.py")
            return
    
    # Load chunks
    print(f"\n📂 Loading: {ENHANCED_CHUNKS_FILE.name}")
    with open(ENHANCED_CHUNKS_FILE, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    with_code = sum(1 for c in chunks if c.get('code_snippet'))
    print(f"✓ Loaded {len(chunks)} chunks ({with_code} with code, {with_code/len(chunks)*100:.1f}%)")
    
    # Initialize embedder
    print(f"\n🔧 Initializing...")
    print(f"   DB: {CHROMA_DIR}")
    print(f"   Collection: {COLLECTION_NAME}")
    print(f"   Batch: {BATCH_SIZE} ({'T4 GPU' if IN_COLAB else 'local'})")
    
    # Memory info
    if IN_COLAB:
        import torch
        if torch.cuda.is_available():
            total_mem = torch.cuda.get_device_properties(0).total_memory / 1e9
            print(f"   GPU Memory: {total_mem:.1f}GB")
    
    embedder = PHPChunksReEmbedder(
        chroma_persist_directory=str(CHROMA_DIR),
        collection_name=COLLECTION_NAME
    )
    
    embedder.create_or_get_collection(reset=RESET_COLLECTION)
    embedder.embed_chunks(chunks, batch_size=BATCH_SIZE)
    
    # Test queries
    print("\n" + "=" * 80)
    print("🧪 Testing with Sample Queries")
    print("=" * 80)
    
    for query in ["API queue processing", "database query execution", "user authentication"]:
        embedder.test_search(query, n_results=3)
        print("\n" + "-" * 80)
    
    print("\n" + "=" * 80)
    print("✅ Done! Vector DB created successfully")
    print(f"📁 Location: {CHROMA_DIR}")
    
    # Auto-download in Colab
    if IN_COLAB:
        print(f"\n📥 Creating ZIP for download...")
        import shutil
        from google.colab import files
        
        zip_path = "/content/php_vector_db"
        shutil.make_archive(zip_path, 'zip', str(CHROMA_DIR))
        
        print(f"✓ Zipped successfully")
        print(f"📦 Downloading...")
        
        files.download(f"{zip_path}.zip")
        print(f"✓ Download initiated!")
    
    print("=" * 80)


if __name__ == "__main__":
    main()
