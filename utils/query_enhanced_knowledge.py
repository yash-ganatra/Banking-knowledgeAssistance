#!/usr/bin/env python3
"""
Query enhanced JS knowledge base with code
"""

import chromadb
from pathlib import Path
import json

BASE_DIR = Path(__file__).parent.parent
CHROMA_DB_PATH = BASE_DIR / "chroma_db"

# Initialize ChromaDB
client = chromadb.PersistentClient(path=str(CHROMA_DB_PATH))
collection = client.get_collection("js_code_knowledge")

def query_knowledge_base(query, n_results=5, filter_dict=None):
    """
    Query the knowledge base
    
    Args:
        query: Search query
        n_results: Number of results
        filter_dict: Optional filters (e.g., {"chunk_type": "js_function"})
    """
    print(f"\n{'='*80}")
    print(f"QUERY: {query}")
    print(f"{'='*80}\n")
    
    results = collection.query(
        query_texts=[query],
        n_results=n_results,
        where=filter_dict if filter_dict else None
    )
    
    for i, (doc, metadata, distance) in enumerate(zip(
        results['documents'][0],
        results['metadatas'][0],
        results['distances'][0]
    )):
        print(f"\n{'─'*80}")
        print(f"RESULT {i+1} (Relevance: {1 - distance:.3f})")
        print(f"{'─'*80}")
        print(f"Type: {metadata.get('chunk_type')}")
        print(f"File: {metadata.get('file_name')}")
        
        if metadata.get('function_name'):
            print(f"Function: {metadata.get('function_name')}")
        
        if metadata.get('endpoint_url'):
            print(f"Endpoint: {metadata.get('endpoint_url')}")
        
        if metadata.get('has_code') == 'yes':
            print(f"📝 Code: Lines {metadata.get('line_start')}-{metadata.get('line_end')} ({metadata.get('code_lines')} lines)")
        
        print(f"\n{doc[:500]}...")  # Show first 500 chars
    
    print(f"\n{'='*80}\n")


def interactive_query():
    """
    Interactive query interface
    """
    print("\n🔍 JavaScript Knowledge Base Query Interface")
    print("=" * 80)
    print(f"Collection: {collection.name}")
    print(f"Total chunks: {collection.count()}")
    print("=" * 80)
    
    while True:
        print("\nQuery examples:")
        print("  1. How to fetch branches?")
        print("  2. Show me encryption code")
        print("  3. NPC review workflow")
        print("  4. AJAX calls in admin module")
        print("  5. Exit")
        
        choice = input("\nEnter your query (or number): ").strip()
        
        if choice == '5' or choice.lower() == 'exit':
            break
        
        # Predefined queries
        queries = {
            '1': "How to fetch branches in admin module",
            '2': "Show me AES encryption implementation",
            '3': "NPC review workflow click events",
            '4': "AJAX calls to save user details"
        }
        
        query = queries.get(choice, choice)
        
        # Ask for filters
        print("\nFilter by:")
        print("  1. All chunks")
        print("  2. Functions only")
        print("  3. Endpoints only")
        print("  4. With code only")
        
        filter_choice = input("Choose filter (1-4, default=1): ").strip() or '1'
        
        filter_dict = None
        if filter_choice == '2':
            filter_dict = {"chunk_type": "js_function"}
        elif filter_choice == '3':
            filter_dict = {"chunk_type": "js_ajax_endpoint"}
        elif filter_choice == '4':
            filter_dict = {"has_code": "yes"}
        
        query_knowledge_base(query, n_results=5, filter_dict=filter_dict)


# Example queries
if __name__ == "__main__":
    import sys
    
    if len(sys.argv) > 1:
        # Command line query
        query = ' '.join(sys.argv[1:])
        query_knowledge_base(query)
    else:
        # Interactive mode
        interactive_query()
