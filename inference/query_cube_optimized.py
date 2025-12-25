"""
Advanced Query Interface for CUBE Documentation with Hybrid Retrieval
Supports semantic search, metadata filtering, and re-ranking
"""

import chromadb
from chromadb.config import Settings
from sentence_transformers import SentenceTransformer, CrossEncoder
from typing import List, Dict, Optional
import json


class CUBEQueryEngine:
    def __init__(
        self,
        db_path: str = "./vector_db/cube_optimized_db",
        collection_name: str = "cube_docs_optimized",
        embedding_model: str = "sentence-transformers/all-MiniLM-L6-v2",
        reranker_model: Optional[str] = "cross-encoder/ms-marco-MiniLM-L-6-v2"
    ):
        self.db_path = db_path
        self.collection_name = collection_name
        
        print("🔧 Initializing CUBE Query Engine...")
        
        # Load models
        print(f"  Loading embedding model: {embedding_model}")
        self.embedding_model = SentenceTransformer(embedding_model)
        
        if reranker_model:
            print(f"  Loading reranker model: {reranker_model}")
            self.reranker = CrossEncoder(reranker_model)
        else:
            self.reranker = None
        
        # Connect to ChromaDB
        print(f"  Connecting to database: {db_path}")
        self.client = chromadb.PersistentClient(
            path=db_path,
            settings=Settings(anonymized_telemetry=False)
        )
        self.collection = self.client.get_collection(name=collection_name)
        
        print(f"✓ Connected to collection: {collection_name}")
        print(f"  Total documents: {self.collection.count()}\n")
    
    def query(
        self,
        query_text: str,
        top_k: int = 5,
        filters: Optional[Dict] = None,
        rerank: bool = True,
        include_synthetic: bool = True
    ) -> List[Dict]:
        """
        Query the CUBE documentation
        
        Args:
            query_text: The search query
            top_k: Number of results to return
            filters: Metadata filters (e.g., {"book_name": "CUBE Project Overview"})
            rerank: Whether to rerank results using cross-encoder
            include_synthetic: Whether to include synthetic chunks in results
        
        Returns:
            List of result dictionaries with content and metadata
        """
        
        # Generate query embedding
        query_embedding = self.embedding_model.encode([query_text])[0].tolist()
        
        # Prepare where clause for filtering
        where = {}
        if filters:
            where.update(filters)
        
        if not include_synthetic:
            where["is_synthetic"] = {"$ne": "True"}
        
        # Retrieve initial results (2x top_k for reranking)
        initial_k = top_k * 2 if rerank and self.reranker else top_k
        
        results = self.collection.query(
            query_embeddings=[query_embedding],
            n_results=initial_k,
            where=where if where else None
        )
        
        # Format results
        formatted_results = []
        for i in range(len(results['documents'][0])):
            formatted_results.append({
                'id': results['ids'][0][i],
                'content': results['documents'][0][i],
                'metadata': results['metadatas'][0][i],
                'distance': results['distances'][0][i] if 'distances' in results else None
            })
        
        # Rerank if enabled
        if rerank and self.reranker and len(formatted_results) > 0:
            pairs = [[query_text, result['content']] for result in formatted_results]
            scores = self.reranker.predict(pairs)
            
            # Add rerank scores and sort
            for result, score in zip(formatted_results, scores):
                result['rerank_score'] = float(score)
            
            formatted_results.sort(key=lambda x: x['rerank_score'], reverse=True)
            formatted_results = formatted_results[:top_k]
        
        return formatted_results
    
    def query_by_concept(
        self,
        concept: str,
        top_k: int = 5
    ) -> List[Dict]:
        """Query by specific concept tag"""
        return self.query(
            query_text=concept,
            top_k=top_k,
            filters=None  # Will match concept_tags automatically
        )
    
    def query_by_account_type(
        self,
        account_type: str,
        query_text: str,
        top_k: int = 5
    ) -> List[Dict]:
        """Query within specific account type"""
        # Note: ChromaDB filtering on comma-separated values needs special handling
        results = self.query(
            query_text=f"{account_type} {query_text}",
            top_k=top_k * 2,  # Get more results
            rerank=True
        )
        
        # Filter results that contain the account type
        filtered = [
            r for r in results 
            if account_type.lower() in r['metadata'].get('concept_tags', '').lower()
            or account_type.lower() in r['metadata'].get('account_types', '').lower()
        ]
        
        return filtered[:top_k]
    
    def query_by_module(
        self,
        module: str,
        query_text: str,
        top_k: int = 5
    ) -> List[Dict]:
        """Query within specific module (Branch, NPC, Admin, QC, etc.)"""
        results = self.query(
            query_text=f"{module} {query_text}",
            top_k=top_k * 2,
            rerank=True
        )
        
        # Filter results that contain the module
        filtered = [
            r for r in results 
            if module.lower() in r['metadata'].get('modules', '').lower()
            or module.lower() in r['content'].lower()[:200]
        ]
        
        return filtered[:top_k]
    
    def get_related_pages(
        self,
        page_id: str,
        top_k: int = 5
    ) -> List[Dict]:
        """Find pages related to a specific page"""
        # Get the page content
        page_result = self.collection.get(ids=[page_id])
        
        if not page_result['documents']:
            return []
        
        page_content = page_result['documents'][0]
        
        # Find similar pages
        return self.query(
            query_text=page_content[:500],  # Use first part as query
            top_k=top_k + 1  # +1 to exclude self
        )[1:]  # Skip first result (the page itself)
    
    def print_results(self, results: List[Dict], show_content: bool = True):
        """Pretty print query results"""
        print(f"\n{'='*80}")
        print(f"FOUND {len(results)} RESULTS")
        print('='*80)
        
        for i, result in enumerate(results, 1):
            metadata = result['metadata']
            
            # Header
            print(f"\n[{i}] ", end="")
            
            if metadata.get('is_synthetic') == 'True':
                print(f"📚 {metadata.get('title', 'Synthetic Summary')}")
                print(f"    Type: Cross-Reference Summary")
                print(f"    Keywords: {metadata.get('keywords', 'N/A')}")
            else:
                page_name = metadata.get('page_name', 'Unknown')
                hierarchy = metadata.get('hierarchy_path', 'N/A')
                print(f"📄 {page_name}")
                print(f"    Path: {hierarchy}")
            
            # Metadata
            print(f"    Chunk ID: {result['id']}")
            
            if 'concept_tags' in metadata:
                concepts = metadata['concept_tags'].split(',')[:5]
                print(f"    Concepts: {', '.join(concepts)}")
            
            # Scores
            if 'rerank_score' in result:
                print(f"    Relevance: {result['rerank_score']:.4f}")
            elif result.get('distance') is not None:
                print(f"    Distance: {result['distance']:.4f}")
            
            # Content preview
            if show_content:
                content = result['content']
                preview = content[:400] + "..." if len(content) > 400 else content
                print(f"\n    Content Preview:")
                print(f"    {preview}")
            
            # Show Mermaid diagram if present
            if metadata.get('is_mermaid') == 'True' or metadata.get('mermaid_code'):
                print(f"\n    📊 Contains Mermaid Diagram:")
                mermaid_code = metadata.get('mermaid_code', '')
                if mermaid_code:
                    # Show first 200 chars of diagram code
                    preview_diagram = mermaid_code[:200] + "..." if len(mermaid_code) > 200 else mermaid_code
                    print(f"    ```mermaid")
                    print(f"    {preview_diagram}")
                    print(f"    ```")
                    print(f"    💡 Full diagram available in metadata['mermaid_code']")
            
            print(f"\n{'-'*80}")
    
    def interactive_query(self):
        """Interactive query interface"""
        print("\n" + "="*80)
        print("CUBE DOCUMENTATION QUERY INTERFACE")
        print("="*80)
        print("\nCommands:")
        print("  - Just type your question naturally")
        print("  - 'account:<type> <query>' - Search within specific account type")
        print("  - 'module:<name> <query>' - Search within specific module")
        print("  - 'concept:<name>' - Find by concept")
        print("  - 'quit' or 'exit' - Exit")
        print("="*80)
        
        while True:
            try:
                query = input("\n💬 Your question: ").strip()
                
                if query.lower() in ['quit', 'exit', 'q']:
                    print("👋 Goodbye!")
                    break
                
                if not query:
                    continue
                
                # Parse special commands
                if query.startswith('account:'):
                    parts = query.split(' ', 1)
                    account_type = parts[0].split(':')[1]
                    query_text = parts[1] if len(parts) > 1 else account_type
                    results = self.query_by_account_type(account_type, query_text)
                
                elif query.startswith('module:'):
                    parts = query.split(' ', 1)
                    module = parts[0].split(':')[1]
                    query_text = parts[1] if len(parts) > 1 else module
                    results = self.query_by_module(module, query_text)
                
                elif query.startswith('concept:'):
                    concept = query.split(':')[1].strip()
                    results = self.query_by_concept(concept)
                
                else:
                    # Regular semantic search
                    results = self.query(query, top_k=5, rerank=True)
                
                self.print_results(results, show_content=True)
            
            except KeyboardInterrupt:
                print("\n\n👋 Goodbye!")
                break
            except Exception as e:
                print(f"\n❌ Error: {e}")


def main():
    # Initialize query engine
    engine = CUBEQueryEngine(
        db_path="/Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance/vector_db/cube_optimized_db",
        collection_name="cube_docs_optimized"
    )
    
    # Example queries
    print("\n" + "="*80)
    print("EXAMPLE QUERIES")
    print("="*80)
    
    examples = [
        {
            'query': "What are the requirements for opening an NRI account?",
            'description': "General NRI account query"
        },
        {
            'query': "How does the NPC clearance process work?",
            'description': "Process flow query"
        },
        {
            'query': "What is risk classification and how is it determined?",
            'description': "Compliance query"
        }
    ]
    
    for example in examples:
        print(f"\n\n🔍 Example: {example['description']}")
        print(f"   Query: '{example['query']}'")
        
        results = engine.query(
            example['query'],
            top_k=3,
            rerank=True
        )
        
        engine.print_results(results, show_content=True)
    
    # Start interactive mode
    print("\n\n" + "="*80)
    response = input("Start interactive query mode? (y/n): ").strip().lower()
    if response == 'y':
        engine.interactive_query()


if __name__ == "__main__":
    main()
