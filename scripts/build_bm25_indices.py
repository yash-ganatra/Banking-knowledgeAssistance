#!/usr/bin/env python3
"""
Build BM25 Indices for Hybrid Search

This script builds BM25 (sparse) indices from existing ChromaDB collections.
Run this once after setting up your vector databases, and again whenever
you re-embed your chunks.

Usage:
    python build_bm25_indices.py [--source SOURCE] [--all]
    
Examples:
    python build_bm25_indices.py --all           # Build all indices
    python build_bm25_indices.py --source php_code  # Build only PHP index
"""

import os
import sys
import json
import argparse
import logging
from pathlib import Path

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent))

import chromadb
from chromadb.config import Settings

from utils.bm25_index import BM25Index, BM25IndexManager

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Configuration
PROJECT_ROOT = Path(__file__).parent.parent
VECTOR_DB_ROOT = PROJECT_ROOT / "vector_db"
BM25_INDEX_DIR = PROJECT_ROOT / "bm25_indices"

# ChromaDB collection configurations
COLLECTIONS = {
    'php_code': {
        'db_path': VECTOR_DB_ROOT / 'php_chroma_db',
        'collection_name': 'php_code_chunks'
    },
    'js_code': {
        'db_path': VECTOR_DB_ROOT / 'js_chroma_db', 
        'collection_name': 'js_code_knowledge'
    },
    'blade_templates': {
        'db_path': VECTOR_DB_ROOT / 'blade_views_chroma_db',
        'collection_name': 'blade_views_knowledge'
    },
    'business_docs': {
        'db_path': VECTOR_DB_ROOT / 'cube_optimized_db',
        'collection_name': 'cube_docs_optimized'
    }
}


def load_documents_from_chromadb(db_path: Path, collection_name: str) -> list:
    """
    Load all documents from a ChromaDB collection.
    
    Args:
        db_path: Path to ChromaDB directory
        collection_name: Name of the collection
        
    Returns:
        List of documents with id, content, and metadata
    """
    if not db_path.exists():
        logger.warning(f"ChromaDB path does not exist: {db_path}")
        return []
    
    try:
        client = chromadb.PersistentClient(
            path=str(db_path),
            settings=Settings(anonymized_telemetry=False)
        )
        
        collection = client.get_collection(name=collection_name)
        
        # Get all documents
        # ChromaDB has a limit on how many we can get at once
        count = collection.count()
        logger.info(f"Collection '{collection_name}' has {count} documents")
        
        if count == 0:
            return []
        
        # Fetch in batches if large
        batch_size = 5000
        all_documents = []
        
        for offset in range(0, count, batch_size):
            result = collection.get(
                limit=batch_size,
                offset=offset,
                include=['documents', 'metadatas']
            )
            
            for i, doc_id in enumerate(result['ids']):
                all_documents.append({
                    'id': doc_id,
                    'content': result['documents'][i] if result['documents'] else '',
                    'metadata': result['metadatas'][i] if result['metadatas'] else {}
                })
        
        logger.info(f"Loaded {len(all_documents)} documents from {collection_name}")
        return all_documents
        
    except Exception as e:
        logger.error(f"Failed to load from ChromaDB: {e}")
        return []


def load_documents_from_json(json_path: Path, source_name: str) -> list:
    """
    Alternative: Load documents directly from JSON chunks file.
    Use this if ChromaDB loading has issues.
    
    Args:
        json_path: Path to JSON chunks file
        source_name: Name of the source for ID prefix
        
    Returns:
        List of documents
    """
    if not json_path.exists():
        logger.warning(f"JSON file does not exist: {json_path}")
        return []
    
    try:
        with open(json_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        # Handle different JSON structures
        if isinstance(data, dict) and 'chunks' in data:
            chunks = data['chunks']
        elif isinstance(data, list):
            chunks = data
        else:
            logger.error(f"Unexpected JSON structure in {json_path}")
            return []
        
        documents = []
        for i, chunk in enumerate(chunks):
            # Adapt to different chunk formats
            doc_id = chunk.get('chunk_id') or chunk.get('id') or f"{source_name}_{i}"
            
            # Get content - might be in different fields
            content = chunk.get('content') or chunk.get('text') or ''
            
            # If content is empty, try to build from description and code
            if not content:
                parts = []
                if chunk.get('description'):
                    parts.append(chunk['description'])
                if chunk.get('code_snippet'):
                    parts.append(chunk['code_snippet'])
                if chunk.get('method_description'):
                    parts.append(chunk['method_description'])
                if chunk.get('class_description'):
                    parts.append(chunk['class_description'])
                content = '\n'.join(parts)
            
            # Build metadata
            metadata = {k: v for k, v in chunk.items() 
                       if k not in ['content', 'text', 'chunk_id', 'id']}
            
            documents.append({
                'id': doc_id,
                'content': content,
                'metadata': metadata
            })
        
        logger.info(f"Loaded {len(documents)} documents from {json_path}")
        return documents
        
    except Exception as e:
        logger.error(f"Failed to load JSON: {e}")
        return []


def build_index_for_source(source_name: str, documents: list) -> bool:
    """
    Build and save BM25 index for a source.
    
    Args:
        source_name: Name of the knowledge source
        documents: List of documents to index
        
    Returns:
        True if successful
    """
    if not documents:
        logger.warning(f"No documents to index for {source_name}")
        return False
    
    try:
        # Create index
        index = BM25Index(source_name, str(BM25_INDEX_DIR))
        
        # Build index
        index.build_index(documents)
        
        # Save to disk
        index.save()
        
        logger.info(f"✅ BM25 index built for {source_name}: {len(documents)} documents")
        return True
        
    except Exception as e:
        logger.error(f"❌ Failed to build index for {source_name}: {e}")
        return False


def build_from_chromadb(source_name: str) -> bool:
    """Build BM25 index from ChromaDB collection."""
    config = COLLECTIONS.get(source_name)
    if not config:
        logger.error(f"Unknown source: {source_name}")
        return False
    
    documents = load_documents_from_chromadb(
        config['db_path'], 
        config['collection_name']
    )
    
    return build_index_for_source(source_name, documents)


def build_from_json_fallback(source_name: str) -> bool:
    """Build BM25 index from JSON chunks as fallback."""
    chunks_dir = PROJECT_ROOT / "chunks"
    
    json_files = {
        'php_code': chunks_dir / 'php_metadata_chunks_with_code.json',
        'js_code': chunks_dir / 'js_file_chunks_enhanced_descriptions.json',
        'blade_templates': chunks_dir / 'blade_views_enhanced.json',
        'business_docs': chunks_dir / 'cube_optimized_chunks.json'
    }
    
    json_path = json_files.get(source_name)
    if not json_path:
        logger.error(f"No JSON file configured for {source_name}")
        return False
    
    documents = load_documents_from_json(json_path, source_name)
    return build_index_for_source(source_name, documents)


def build_all_indices(use_chromadb: bool = True) -> dict:
    """
    Build BM25 indices for all sources.
    
    Args:
        use_chromadb: Try to load from ChromaDB first, fall back to JSON
        
    Returns:
        Dict mapping source name to success status
    """
    results = {}
    
    for source_name in COLLECTIONS.keys():
        logger.info(f"\n{'='*60}")
        logger.info(f"Building index for: {source_name}")
        logger.info('='*60)
        
        success = False
        
        if use_chromadb:
            success = build_from_chromadb(source_name)
        
        # Fall back to JSON if ChromaDB failed
        if not success:
            logger.info(f"Trying JSON fallback for {source_name}...")
            success = build_from_json_fallback(source_name)
        
        results[source_name] = success
    
    return results


def test_indices():
    """Test all built indices with sample queries."""
    logger.info("\n" + "="*60)
    logger.info("Testing BM25 Indices")
    logger.info("="*60)
    
    manager = BM25IndexManager(str(BM25_INDEX_DIR))
    load_results = manager.load_all_indices()
    
    test_queries = {
        'php_code': 'validateKYCDocument KYCController',
        'js_code': 'userDetailsCallBackFunction admin',
        'blade_templates': 'account opening form CSRF',
        'business_docs': 'term deposit account opening process'
    }
    
    for source_name, loaded in load_results.items():
        if not loaded:
            logger.warning(f"⚠️  {source_name}: Index not loaded")
            continue
        
        query = test_queries.get(source_name, 'test query')
        results = manager.search(source_name, query, top_k=3)
        
        logger.info(f"\n📊 {source_name}: {len(results)} results for '{query}'")
        for i, r in enumerate(results[:3], 1):
            score = r.get('bm25_score', 0)
            doc_id = r.get('id', 'unknown')[:50]
            logger.info(f"   {i}. [{score:.2f}] {doc_id}")


def main():
    parser = argparse.ArgumentParser(
        description='Build BM25 indices for hybrid search'
    )
    parser.add_argument(
        '--source', 
        choices=list(COLLECTIONS.keys()),
        help='Build index for specific source only'
    )
    parser.add_argument(
        '--all', 
        action='store_true',
        help='Build indices for all sources'
    )
    parser.add_argument(
        '--json-only',
        action='store_true',
        help='Use JSON files instead of ChromaDB'
    )
    parser.add_argument(
        '--test',
        action='store_true',
        help='Test indices after building'
    )
    
    args = parser.parse_args()
    
    # Create output directory
    BM25_INDEX_DIR.mkdir(parents=True, exist_ok=True)
    
    print("\n" + "🔧 BM25 Index Builder for Hybrid Search".center(60))
    print("="*60)
    
    if args.source:
        # Build single source
        if args.json_only:
            success = build_from_json_fallback(args.source)
        else:
            success = build_from_chromadb(args.source)
            if not success:
                success = build_from_json_fallback(args.source)
        
        results = {args.source: success}
    else:
        # Build all by default
        results = build_all_indices(use_chromadb=not args.json_only)
    
    # Print summary
    print("\n" + "="*60)
    print("BUILD SUMMARY".center(60))
    print("="*60)
    
    for source, success in results.items():
        status = "✅ Success" if success else "❌ Failed"
        print(f"  {source}: {status}")
    
    successful = sum(1 for s in results.values() if s)
    print(f"\n  Total: {successful}/{len(results)} indices built")
    print(f"  Output directory: {BM25_INDEX_DIR}")
    
    # Test if requested
    if args.test:
        test_indices()
    
    return 0 if all(results.values()) else 1


if __name__ == "__main__":
    sys.exit(main())
