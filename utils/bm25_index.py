"""
BM25 Index Manager for Hybrid Search

Provides sparse keyword-based search to complement dense vector search.
BM25 excels at exact term matching (function names, file names, identifiers)
while dense search handles semantic understanding.
"""

import json
import pickle
import logging
import os
from pathlib import Path
from typing import List, Dict, Any, Optional, Tuple
from dataclasses import dataclass
import re

try:
    from rank_bm25 import BM25Okapi
except ImportError:
    raise ImportError("Please install rank-bm25: pip install rank-bm25")

logger = logging.getLogger(__name__)


@dataclass
class BM25SearchResult:
    """Result from BM25 search"""
    chunk_id: str
    content: str
    metadata: Dict[str, Any]
    bm25_score: float
    rank: int


# Banking domain abbreviation expansions
BANKING_ABBREVIATIONS = {
    'kyc': 'KYC know your customer',
    'oao': 'OAO online account opening',
    'dsa': 'DSA direct selling agent',
    'td': 'TD term deposit',
    'fd': 'FD fixed deposit',
    'npa': 'NPA non performing asset',
    'uam': 'UAM user access management',
    'vkyc': 'VKYC video KYC verification',
    'npc': 'NPC non personal customer',
    'cif': 'CIF customer information file',
    'aml': 'AML anti money laundering',
    'pep': 'PEP politically exposed person',
    'crf': 'CRF customer request form',
}


def expand_query_abbreviations(query: str) -> str:
    """
    Expand banking abbreviations in a query.
    Uses simple word-based replacement (no regex).
    
    Only expands standalone lowercase abbreviations to avoid
    breaking code identifiers like 'validateKYC' or 'kyc_status'.
    
    Args:
        query: Original query string
        
    Returns:
        Query with abbreviations expanded
    """
    words = query.split()
    result = []
    
    for word in words:
        # Strip punctuation for matching but preserve it
        stripped = word.lower().strip('.,?!:;"\'')
        leading = word[:len(word) - len(word.lstrip('.,?!:;"\'' ))]
        trailing = word[len(word.rstrip('.,?!:;"\'' )):]
        
        # Only expand if:
        # 1. Word is lowercase (not an identifier like KYC or KYCController)
        # 2. Word matches exactly (not part of compound like kyc_validator)
        if stripped in BANKING_ABBREVIATIONS and word == word.lower() and '_' not in word:
            expansion = BANKING_ABBREVIATIONS[stripped]
            result.append(leading + expansion + trailing)
        else:
            result.append(word)
    
    return ' '.join(result)


def should_expand_query(query_type: str, requires_code: bool) -> bool:
    """
    Determine if query expansion is appropriate based on intent classification.
    
    Args:
        query_type: Type from intent classification ('documentation', 'implementation', etc.)
        requires_code: Whether the query requires code examples
        
    Returns:
        True if query should be expanded, False otherwise
    """
    # DON'T expand for code-specific queries
    if requires_code:
        return False
    
    if query_type in ['implementation', 'debugging']:
        return False
    
    # DO expand for conceptual/documentation queries
    if query_type in ['documentation', 'architecture']:
        return True
    
    # For 'mixed' type, expand (semantic recall is usually more important)
    return True


class BM25Index:
    """
    BM25 index for a single knowledge source (PHP, JS, Blade, Business Docs)
    Supports building, saving, loading, and querying the index.
    """
    
    def __init__(self, source_name: str, index_dir: str = "./bm25_indices"):
        """
        Initialize BM25 index for a knowledge source.
        
        Args:
            source_name: Name of the source (e.g., 'php_code', 'js_code')
            index_dir: Directory to store/load index files
        """
        self.source_name = source_name
        self.index_dir = Path(index_dir)
        self.index_dir.mkdir(parents=True, exist_ok=True)
        
        self.bm25: Optional[BM25Okapi] = None
        self.documents: List[Dict[str, Any]] = []  # Original documents with metadata
        self.tokenized_corpus: List[List[str]] = []
        
    def _tokenize(self, text: str) -> List[str]:
        """
        Tokenize text for BM25 indexing.
        Handles code-specific tokenization (camelCase, snake_case, etc.)
        
        Args:
            text: Text to tokenize
            
        Returns:
            List of tokens
        """
        if not text:
            return []
        
        # Convert to lowercase
        text = text.lower()
        
        # Split camelCase and PascalCase: validateKYCDocument -> validate kyc document
        text = re.sub(r'([a-z])([A-Z])', r'\1 \2', text)
        
        # Split snake_case: validate_kyc_document -> validate kyc document
        text = text.replace('_', ' ')
        
        # Split on common code delimiters
        text = re.sub(r'[.\->/\\:;,(){}\[\]"\'`@#$%^&*+=|<>!?]', ' ', text)
        
        # Split on whitespace and filter
        tokens = text.split()
        
        # Remove very short tokens (likely noise) but keep meaningful ones
        tokens = [t.strip() for t in tokens if len(t.strip()) >= 2]
        
        # Remove common stop words that don't help code search
        stop_words = {
            'the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'must', 'shall', 'can', 'need', 'dare',
            'ought', 'used', 'to', 'of', 'in', 'for', 'on', 'with', 'at', 'by',
            'from', 'as', 'into', 'through', 'during', 'before', 'after',
            'above', 'below', 'between', 'under', 'again', 'further', 'then',
            'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all',
            'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
            'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just',
            'and', 'but', 'if', 'or', 'because', 'until', 'while', 'this', 'that'
        }
        
        tokens = [t for t in tokens if t not in stop_words]
        
        return tokens
    
    def _extract_searchable_text(self, document: Dict[str, Any]) -> str:
        """
        Extract all searchable text from a document.
        Combines content and relevant metadata fields.
        
        Args:
            document: Document dictionary with content and metadata
            
        Returns:
            Combined searchable text
        """
        parts = []
        
        # Main content
        if document.get('content'):
            parts.append(document['content'])
        
        # Metadata fields that are useful for search
        metadata = document.get('metadata', {})
        
        # File/class/method names (high value for code search)
        for field in ['file_name', 'file_path', 'class_name', 'method_name', 
                      'function_name', 'page_name', 'section_name']:
            if metadata.get(field):
                # Add multiple times to boost importance
                parts.append(str(metadata[field]))
                parts.append(str(metadata[field]))
        
        # Description fields
        for field in ['description', 'class_description', 'method_description']:
            if metadata.get(field):
                parts.append(str(metadata[field]))
        
        # Code snippet if separate from content
        if metadata.get('code_snippet') and metadata['code_snippet'] != document.get('content'):
            parts.append(str(metadata['code_snippet']))
        
        return ' '.join(parts)
    
    def build_index(self, documents: List[Dict[str, Any]]) -> None:
        """
        Build BM25 index from documents.
        
        Args:
            documents: List of documents, each with 'id', 'content', and 'metadata'
        """
        logger.info(f"Building BM25 index for {self.source_name} with {len(documents)} documents")
        
        self.documents = documents
        self.tokenized_corpus = []
        
        for doc in documents:
            searchable_text = self._extract_searchable_text(doc)
            tokens = self._tokenize(searchable_text)
            self.tokenized_corpus.append(tokens)
        
        # Build BM25 index
        self.bm25 = BM25Okapi(self.tokenized_corpus)
        
        logger.info(f"BM25 index built for {self.source_name}: {len(self.documents)} documents indexed")
    
    def save(self) -> None:
        """Save index to disk"""
        if self.bm25 is None:
            raise ValueError("No index to save. Build index first.")
        
        index_path = self.index_dir / f"{self.source_name}_bm25.pkl"
        
        data = {
            'source_name': self.source_name,
            'documents': self.documents,
            'tokenized_corpus': self.tokenized_corpus,
            'bm25': self.bm25
        }
        
        with open(index_path, 'wb') as f:
            pickle.dump(data, f)
        
        logger.info(f"BM25 index saved to {index_path}")
    
    def load(self) -> bool:
        """
        Load index from disk.
        
        Returns:
            True if loaded successfully, False otherwise
        """
        index_path = self.index_dir / f"{self.source_name}_bm25.pkl"
        
        if not index_path.exists():
            logger.warning(f"No BM25 index found at {index_path}")
            return False
        
        try:
            with open(index_path, 'rb') as f:
                data = pickle.load(f)
            
            self.source_name = data['source_name']
            self.documents = data['documents']
            self.tokenized_corpus = data['tokenized_corpus']
            self.bm25 = data['bm25']
            
            logger.info(f"BM25 index loaded from {index_path}: {len(self.documents)} documents")
            return True
            
        except Exception as e:
            logger.error(f"Failed to load BM25 index: {e}")
            return False
    
    def search(self, query: str, top_k: int = 10, expand_abbreviations: bool = False) -> List[BM25SearchResult]:
        """
        Search the BM25 index.
        
        Args:
            query: Search query
            top_k: Number of results to return
            expand_abbreviations: Whether to expand banking abbreviations in query
            
        Returns:
            List of BM25SearchResult objects
        """
        if self.bm25 is None:
            logger.warning(f"BM25 index not loaded for {self.source_name}")
            return []
        
        # Optionally expand abbreviations for better recall on conceptual queries
        search_query = query
        if expand_abbreviations:
            search_query = expand_query_abbreviations(query)
            if search_query != query:
                logger.info(f"BM25 query expanded: '{query}' → '{search_query}'")
        
        # Tokenize query
        query_tokens = self._tokenize(search_query)
        
        if not query_tokens:
            return []
        
        # Get BM25 scores for all documents
        scores = self.bm25.get_scores(query_tokens)
        
        # Get top-k indices
        top_indices = sorted(range(len(scores)), key=lambda i: scores[i], reverse=True)[:top_k]
        
        results = []
        for rank, idx in enumerate(top_indices, start=1):
            if scores[idx] > 0:  # Only include documents with positive scores
                doc = self.documents[idx]
                results.append(BM25SearchResult(
                    chunk_id=doc.get('id', f"{self.source_name}_{idx}"),
                    content=doc.get('content', ''),
                    metadata=doc.get('metadata', {}),
                    bm25_score=float(scores[idx]),
                    rank=rank
                ))
        
        return results
    
    def get_document_count(self) -> int:
        """Get number of indexed documents"""
        return len(self.documents)


class BM25IndexManager:
    """
    Manages BM25 indices for all knowledge sources.
    Provides unified interface for building, loading, and searching.
    """
    
    def __init__(self, index_dir: str = "./bm25_indices"):
        """
        Initialize the BM25 index manager.
        
        Args:
            index_dir: Directory to store all BM25 indices
        """
        self.index_dir = index_dir
        self.indices: Dict[str, BM25Index] = {}
        
        # Source names matching KnowledgeSource enum
        self.source_names = ['business_docs', 'php_code', 'js_code', 'blade_templates']
    
    def get_or_create_index(self, source_name: str) -> BM25Index:
        """
        Get existing index or create new one for a source.
        
        Args:
            source_name: Name of the knowledge source
            
        Returns:
            BM25Index instance
        """
        if source_name not in self.indices:
            self.indices[source_name] = BM25Index(source_name, self.index_dir)
        return self.indices[source_name]
    
    def load_all_indices(self) -> Dict[str, bool]:
        """
        Load all available indices from disk.
        
        Returns:
            Dict mapping source name to load success status
        """
        results = {}
        for source_name in self.source_names:
            index = self.get_or_create_index(source_name)
            results[source_name] = index.load()
        return results
    
    def search(self, source_name: str, query: str, top_k: int = 10, 
                expand_abbreviations: bool = False) -> List[Dict[str, Any]]:
        """
        Search a specific source's BM25 index.
        
        Args:
            source_name: Name of the knowledge source
            query: Search query
            top_k: Number of results
            expand_abbreviations: Whether to expand banking abbreviations
            
        Returns:
            List of result dictionaries compatible with dense search results
        """
        index = self.indices.get(source_name)
        
        if index is None or index.bm25 is None:
            logger.warning(f"BM25 index not available for {source_name}")
            return []
        
        bm25_results = index.search(query, top_k, expand_abbreviations=expand_abbreviations)
        
        # Convert to format compatible with dense search results
        results = []
        for r in bm25_results:
            results.append({
                'id': r.chunk_id,
                'content': r.content,
                'metadata': r.metadata,
                'bm25_score': r.bm25_score,
                'bm25_rank': r.rank,
                'source': source_name
            })
        
        return results
    
    def is_index_available(self, source_name: str) -> bool:
        """Check if an index is loaded and ready"""
        index = self.indices.get(source_name)
        return index is not None and index.bm25 is not None
    
    def get_stats(self) -> Dict[str, Any]:
        """Get statistics about all indices"""
        stats = {}
        for source_name, index in self.indices.items():
            stats[source_name] = {
                'loaded': index.bm25 is not None,
                'document_count': index.get_document_count() if index.bm25 else 0
            }
        return stats
