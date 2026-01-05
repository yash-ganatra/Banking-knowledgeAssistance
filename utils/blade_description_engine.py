#!/usr/bin/env python3
"""
Blade Description Engine

Two-phase retrieval system for blade templates:
1. Retrieve candidates from existing ChromaDB collection
2. Extract descriptions from metadata
3. Re-rank using cross-encoder on descriptions
4. Extract smart snippets from top results
5. Format context for LLM
"""

import os
import sys
from pathlib import Path
from typing import List, Dict, Any, Optional
import logging

# Add parent directory to path
sys.path.append(str(Path(__file__).parent.parent))

from sentence_transformers import CrossEncoder
from FlagEmbedding import BGEM3FlagModel
import chromadb
from chromadb.config import Settings

from utils.smart_snippet_extractor import SmartSnippetExtractor

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class BladeDescriptionEngine:
    """
    Description-first retrieval engine for blade templates
    Uses existing collection with descriptions in metadata
    """
    
    def __init__(
        self,
        db_path: str = None,
        collection_name: str = "blade_views_knowledge",
        embedding_model_name: str = "BAAI/bge-m3",
        cross_encoder_model: str = "cross-encoder/ms-marco-MiniLM-L-6-v2"
    ):
        """
        Initialize engine with existing collection
        
        Args:
            db_path: Path to ChromaDB (defaults to ../vector_db/blade_views_chroma_db)
            collection_name: Collection name
            embedding_model_name: BGE-M3 model
            cross_encoder_model: Cross-encoder for re-ranking
        """
        # Set default path
        if db_path is None:
            base_dir = Path(__file__).parent.parent
            db_path = str(base_dir / "vector_db" / "blade_views_chroma_db")
        
        self.db_path = db_path
        self.collection_name = collection_name
        
        logger.info(f"Initializing BladeDescriptionEngine")
        logger.info(f"Database: {self.db_path}")
        
        # Load embedding model (BGE-M3)
        logger.info(f"Loading embedding model: {embedding_model_name}")
        self.embedding_model = BGEM3FlagModel(embedding_model_name, use_fp16=False)
        
        # Load cross-encoder for re-ranking
        logger.info(f"Loading cross-encoder: {cross_encoder_model}")
        self.cross_encoder = CrossEncoder(cross_encoder_model)
        
        # Initialize snippet extractor
        self.snippet_extractor = SmartSnippetExtractor()
        
        # Connect to ChromaDB
        logger.info("Connecting to ChromaDB...")
        self.client = chromadb.PersistentClient(
            path=self.db_path,
            settings=Settings(anonymized_telemetry=False)
        )
        
        # Get collection
        self.collection = self.client.get_collection(name=self.collection_name)
        logger.info(f"✅ Collection loaded: {self.collection.count()} documents")
    
    def query(
        self,
        query_text: str,
        top_k: int = 5,
        initial_candidates: int = 20,
        max_snippet_chars: int = 2000,
        use_rerank: bool = True,
        return_full_content: bool = False
    ) -> List[Dict[str, Any]]:
        """
        Main query method with description-first approach
        
        Args:
            query_text: User query
            top_k: Number of final results
            initial_candidates: Number of candidates to retrieve before re-ranking
            max_snippet_chars: Maximum characters per snippet
            use_rerank: Whether to use cross-encoder re-ranking
            return_full_content: Whether to include full content (for debugging)
            
        Returns:
            List of results with snippets and metadata
        """
        logger.info(f"Query: {query_text}")
        logger.info(f"Retrieving {initial_candidates} candidates, re-ranking to top {top_k}")
        
        # Phase 1: Retrieve candidates
        candidates = self.retrieve_candidates(query_text, n=initial_candidates)
        logger.info(f"✅ Retrieved {len(candidates)} candidates")
        
        # Phase 2: Extract descriptions from metadata
        descriptions = self.extract_descriptions(candidates)
        logger.info(f"✅ Extracted {len(descriptions)} descriptions")
        
        # Phase 3: Re-rank using cross-encoder on descriptions
        if use_rerank and len(candidates) > top_k:
            reranked_results = self.rerank_with_cross_encoder(
                query_text, 
                candidates, 
                descriptions
            )
            final_results = reranked_results[:top_k]
            logger.info(f"✅ Re-ranked to top {len(final_results)}")
        else:
            final_results = candidates[:top_k]
            logger.info(f"✅ Using top {len(final_results)} without re-ranking")
        
        # Phase 4: Extract smart snippets
        results_with_snippets = self.extract_snippets_for_results(
            final_results,
            query_text,
            max_snippet_chars
        )
        logger.info(f"✅ Extracted snippets")
        
        # Phase 5: Format results
        formatted_results = self.format_results(
            results_with_snippets,
            include_full_content=return_full_content
        )
        
        return formatted_results
    
    def retrieve_candidates(self, query_text: str, n: int = 20) -> List[Dict]:
        """
        Retrieve initial candidates from ChromaDB
        
        Args:
            query_text: User query
            n: Number of candidates
            
        Returns:
            List of candidate results
        """
        # Encode query with BGE-M3
        query_embedding = self.embedding_model.encode(
            query_text,
            max_length=8192
        )['dense_vecs'].tolist()
        
        # Query collection
        results = self.collection.query(
            query_embeddings=[query_embedding],
            n_results=n,
            include=['documents', 'metadatas', 'distances']
        )
        
        # Format results
        candidates = []
        if results['documents']:
            for i in range(len(results['documents'][0])):
                candidates.append({
                    'id': results['ids'][0][i],
                    'content': results['documents'][0][i],
                    'metadata': results['metadatas'][0][i],
                    'distance': results['distances'][0][i] if 'distances' in results else None
                })
        
        return candidates
    
    def extract_descriptions(self, candidates: List[Dict]) -> List[str]:
        """
        Extract descriptions from candidate metadata
        
        Args:
            candidates: List of candidate results
            
        Returns:
            List of descriptions
        """
        descriptions = []
        for candidate in candidates:
            metadata = candidate.get('metadata', {})
            description = metadata.get('description', 'No description available')
            descriptions.append(description)
        
        return descriptions
    
    def rerank_with_cross_encoder(
        self,
        query_text: str,
        candidates: List[Dict],
        descriptions: List[str]
    ) -> List[Dict]:
        """
        Re-rank candidates using cross-encoder on descriptions
        
        Args:
            query_text: User query
            candidates: List of candidates
            descriptions: List of descriptions
            
        Returns:
            Re-ranked candidates
        """
        # Create query-description pairs
        pairs = [[query_text, desc] for desc in descriptions]
        
        # Score with cross-encoder
        scores = self.cross_encoder.predict(pairs)
        
        # Attach scores to candidates
        for candidate, score in zip(candidates, scores):
            candidate['rerank_score'] = float(score)
        
        # Sort by score descending
        reranked = sorted(candidates, key=lambda x: x['rerank_score'], reverse=True)
        
        return reranked
    
    def extract_snippets_for_results(
        self,
        results: List[Dict],
        query_text: str,
        max_snippet_chars: int = 2000
    ) -> List[Dict]:
        """
        Extract smart snippets from results
        
        Args:
            results: List of results
            query_text: User query
            max_snippet_chars: Max characters per snippet
            
        Returns:
            Results with snippets added
        """
        for result in results:
            content = result.get('content', '')
            
            # Extract snippet
            snippet = self.snippet_extractor.extract_relevant_snippet(
                content=content,
                query=query_text,
                max_chars=max_snippet_chars
            )
            
            result['snippet'] = snippet
            result['snippet_length'] = len(snippet)
            result['content_length'] = len(content)
            result['compression_ratio'] = len(snippet) / len(content) if len(content) > 0 else 0
        
        return results
    
    def format_results(
        self,
        results: List[Dict],
        include_full_content: bool = False
    ) -> List[Dict[str, Any]]:
        """
        Format results for output
        
        Args:
            results: Results with snippets
            include_full_content: Whether to include full content
            
        Returns:
            Formatted results
        """
        formatted = []
        
        for result in results:
            metadata = result.get('metadata', {})
            
            formatted_result = {
                'id': result.get('id'),
                'file_name': metadata.get('file_name', 'Unknown'),
                'file_path': metadata.get('source', ''),
                'section': metadata.get('section_name', 'N/A'),
                'description': metadata.get('description', 'No description'),
                'has_form': metadata.get('has_form', False),
                'extends': metadata.get('extends'),
                'snippet': result.get('snippet', ''),
                'snippet_length': result.get('snippet_length', 0),
                'content_length': result.get('content_length', 0),
                'distance': result.get('distance'),
                'rerank_score': result.get('rerank_score'),
            }
            
            if include_full_content:
                formatted_result['full_content'] = result.get('content', '')
            
            formatted.append(formatted_result)
        
        return formatted
    
    def format_context_for_llm(
        self,
        results: List[Dict],
        include_code: bool = True,
        include_descriptions: bool = True
    ) -> str:
        """
        Format results as context string for LLM
        
        Args:
            results: Formatted results
            include_code: Whether to include code snippets
            include_descriptions: Whether to include descriptions
            
        Returns:
            Formatted context string
        """
        context_parts = []
        
        for i, result in enumerate(results, 1):
            part = f"--- Result {i}"
            
            if result.get('rerank_score') is not None:
                part += f" (Relevance: {result['rerank_score']:.3f})"
            
            part += " ---\n"
            part += f"File: {result['file_name']}\n"
            
            if result.get('section') and result['section'] != 'full_template':
                part += f"Section: {result['section']}\n"
            
            if include_descriptions:
                part += f"Description: {result['description']}\n"
            
            if result.get('has_form'):
                part += "Contains Form: Yes\n"
            
            if include_code:
                part += f"\nCode Preview:\n{result['snippet']}\n"
            
            context_parts.append(part)
        
        # Add token estimate
        total_chars = sum(len(part) for part in context_parts)
        token_estimate = total_chars // 4
        
        context = "\n\n".join(context_parts)
        context += f"\n\n---\nTotal context: {total_chars} chars (~{token_estimate} tokens)"
        
        return context
    
    def get_stats(self) -> Dict[str, Any]:
        """
        Get engine statistics
        
        Returns:
            Dictionary of stats
        """
        return {
            'database_path': self.db_path,
            'collection_name': self.collection_name,
            'total_documents': self.collection.count(),
            'embedding_model': 'BAAI/bge-m3',
            'cross_encoder': self.cross_encoder.model.name_or_path,
        }


# Convenience function
def create_engine(db_path: str = None) -> BladeDescriptionEngine:
    """
    Create a BladeDescriptionEngine with default settings
    
    Args:
        db_path: Optional custom database path
        
    Returns:
        Initialized engine
    """
    return BladeDescriptionEngine(db_path=db_path)


# Example usage
if __name__ == "__main__":
    print("Blade Description Engine - Test")
    print("=" * 60)
    
    try:
        # Initialize engine
        engine = BladeDescriptionEngine()
        
        # Get stats
        stats = engine.get_stats()
        print("\n📊 Engine Stats:")
        for key, value in stats.items():
            print(f"  {key}: {value}")
        
        # Test query
        test_query = "How does the login form protect against CSRF?"
        print(f"\n🔍 Test Query: {test_query}")
        print("=" * 60)
        
        results = engine.query(
            query_text=test_query,
            top_k=3,
            initial_candidates=10,
            max_snippet_chars=500
        )
        
        print(f"\n✅ Retrieved {len(results)} results\n")
        
        for i, result in enumerate(results, 1):
            print(f"{i}. {result['file_name']}")
            print(f"   Score: {result.get('rerank_score', 'N/A')}")
            print(f"   Snippet: {result['snippet_length']} chars (from {result['content_length']} chars)")
            print(f"   Description: {result['description'][:100]}...")
            print()
        
        # Test context formatting
        context = engine.format_context_for_llm(results[:2])
        print("=" * 60)
        print("📝 Sample LLM Context:")
        print("=" * 60)
        print(context[:500] + "...")
        
        print("\n✅ Blade Description Engine ready for use!")
        
    except Exception as e:
        print(f"\n❌ Error: {e}")
        print("\nMake sure:")
        print("  1. ChromaDB exists at: vector_db/blade_views_chroma_db/")
        print("  2. Collection 'blade_views_knowledge' exists")
        print("  3. Dependencies installed: pip install chromadb FlagEmbedding sentence-transformers")
