"""
Hybrid Search Manager

Combines dense (vector) search with sparse (BM25) search for improved retrieval accuracy.
Uses Reciprocal Rank Fusion (RRF) to merge results from both search methods.
Supports intent-aware query expansion for better recall on conceptual queries.
"""

import logging
from typing import List, Dict, Any, Optional, Tuple
from dataclasses import dataclass, field
from enum import Enum

logger = logging.getLogger(__name__)


class SearchMethod(str, Enum):
    """Available search methods"""
    DENSE = "dense"
    SPARSE = "sparse"  # BM25
    HYBRID = "hybrid"


@dataclass
class HybridSearchConfig:
    """Configuration for hybrid search"""
    dense_weight: float = 0.6  # Weight for dense (semantic) results
    sparse_weight: float = 0.4  # Weight for sparse (BM25) results
    rrf_k: int = 60  # RRF constant
    dense_top_k_multiplier: float = 2.0  # Retrieve more candidates from dense
    sparse_top_k_multiplier: float = 2.0  # Retrieve more candidates from sparse
    min_bm25_score: float = 0.5  # Minimum BM25 score to include
    enable_query_expansion: bool = True  # Enable intent-aware query expansion


@dataclass
class QueryExpansionInfo:
    """
    Information about query expansion that occurred during search.
    Used for logging and debugging.
    """
    original_query: str
    expanded_query: str
    was_expanded: bool
    query_type: str
    requires_code: bool
    source_name: str = ""


@dataclass
class QueryIntent:
    """
    Lightweight intent info passed to hybrid search.
    Mirrors relevant fields from IntentClassificationResult.
    """
    query_type: str = "mixed"  # documentation, implementation, debugging, architecture, mixed
    requires_code: bool = False
    
    @classmethod
    def from_classification(cls, intent_result) -> 'QueryIntent':
        """Create from IntentClassificationResult"""
        return cls(
            query_type=intent_result.query_type.value if hasattr(intent_result.query_type, 'value') else str(intent_result.query_type),
            requires_code=intent_result.requires_code
        )
    
    @classmethod
    def for_code_query(cls) -> 'QueryIntent':
        """Create intent for code-specific queries (no expansion)"""
        return cls(query_type="implementation", requires_code=True)
    
    @classmethod
    def for_documentation_query(cls) -> 'QueryIntent':
        """Create intent for documentation queries (with expansion)"""
        return cls(query_type="documentation", requires_code=False)


class HybridSearchFusion:
    """
    Fuses results from dense and sparse search using weighted RRF.
    """
    
    def __init__(self, config: Optional[HybridSearchConfig] = None):
        """
        Initialize hybrid search fusion.
        
        Args:
            config: Configuration for hybrid search. Uses defaults if None.
        """
        self.config = config or HybridSearchConfig()
    
    def reciprocal_rank_fusion(
        self,
        dense_results: List[Dict[str, Any]],
        sparse_results: List[Dict[str, Any]],
        top_k: int = 10
    ) -> List[Dict[str, Any]]:
        """
        Merge dense and sparse results using weighted Reciprocal Rank Fusion.
        
        Formula: score = w_dense * (1/(k+rank_dense)) + w_sparse * (1/(k+rank_sparse))
        
        Args:
            dense_results: Results from dense (vector) search
            sparse_results: Results from sparse (BM25) search
            top_k: Number of final results to return
            
        Returns:
            Merged and re-ranked results
        """
        k = self.config.rrf_k
        scores: Dict[str, float] = {}
        result_data: Dict[str, Dict[str, Any]] = {}
        
        # Process dense results
        for rank, result in enumerate(dense_results, start=1):
            doc_id = result.get('id', '')
            if not doc_id:
                continue
            
            rrf_score = self.config.dense_weight * (1.0 / (k + rank))
            scores[doc_id] = scores.get(doc_id, 0.0) + rrf_score
            
            if doc_id not in result_data:
                result_data[doc_id] = {
                    **result,
                    'dense_rank': rank,
                    'dense_distance': result.get('distance'),
                    'search_methods': ['dense']
                }
            else:
                result_data[doc_id]['dense_rank'] = rank
                result_data[doc_id]['dense_distance'] = result.get('distance')
                result_data[doc_id]['search_methods'].append('dense')
        
        # Process sparse (BM25) results
        for rank, result in enumerate(sparse_results, start=1):
            doc_id = result.get('id', '')
            if not doc_id:
                continue
            
            # Skip very low BM25 scores
            bm25_score = result.get('bm25_score', 0)
            if bm25_score < self.config.min_bm25_score:
                continue
            
            rrf_score = self.config.sparse_weight * (1.0 / (k + rank))
            scores[doc_id] = scores.get(doc_id, 0.0) + rrf_score
            
            if doc_id not in result_data:
                result_data[doc_id] = {
                    **result,
                    'sparse_rank': rank,
                    'bm25_score': bm25_score,
                    'search_methods': ['sparse']
                }
            else:
                result_data[doc_id]['sparse_rank'] = rank
                result_data[doc_id]['bm25_score'] = bm25_score
                if 'sparse' not in result_data[doc_id].get('search_methods', []):
                    result_data[doc_id]['search_methods'].append('sparse')
        
        # Sort by combined RRF score
        sorted_ids = sorted(scores.keys(), key=lambda x: scores[x], reverse=True)
        
        # Build final results
        merged_results = []
        for doc_id in sorted_ids[:top_k]:
            result = result_data[doc_id]
            result['hybrid_rrf_score'] = scores[doc_id]
            
            # Documents found by BOTH methods get a boost indicator
            result['found_by_both'] = len(result.get('search_methods', [])) > 1
            
            merged_results.append(result)
        
        # Log fusion statistics
        both_count = sum(1 for r in merged_results if r.get('found_by_both', False))
        logger.info(
            f"Hybrid RRF: {len(dense_results)} dense + {len(sparse_results)} sparse "
            f"→ {len(merged_results)} merged ({both_count} found by both methods)"
        )
        
        return merged_results
    
    def normalize_scores(
        self, 
        results: List[Dict[str, Any]], 
        score_field: str
    ) -> List[Dict[str, Any]]:
        """
        Normalize scores to [0, 1] range.
        
        Args:
            results: Results to normalize
            score_field: Field containing the score
            
        Returns:
            Results with normalized scores
        """
        if not results:
            return results
        
        scores = [r.get(score_field, 0) for r in results]
        min_score = min(scores)
        max_score = max(scores)
        
        if max_score == min_score:
            for r in results:
                r[f'{score_field}_normalized'] = 1.0
        else:
            for r in results:
                original = r.get(score_field, 0)
                r[f'{score_field}_normalized'] = (original - min_score) / (max_score - min_score)
        
        return results


class HybridSearchManager:
    """
    Manages hybrid search across knowledge sources.
    Coordinates between dense vector search and BM25 sparse search.
    """
    
    def __init__(
        self,
        bm25_manager,  # BM25IndexManager instance
        config: Optional[HybridSearchConfig] = None
    ):
        """
        Initialize hybrid search manager.
        
        Args:
            bm25_manager: BM25IndexManager instance for sparse search
            config: Hybrid search configuration
        """
        self.bm25_manager = bm25_manager
        self.config = config or HybridSearchConfig()
        self.fusion = HybridSearchFusion(self.config)
    
    def search(
        self,
        source_name: str,
        query: str,
        dense_results: List[Dict[str, Any]],
        top_k: int = 10,
        method: SearchMethod = SearchMethod.HYBRID,
        query_intent: Optional[QueryIntent] = None
    ) -> Tuple[List[Dict[str, Any]], Optional[QueryExpansionInfo]]:
        """
        Perform hybrid search combining dense and sparse results.
        
        Args:
            source_name: Name of the knowledge source
            query: Search query
            dense_results: Results from dense (vector) search
            top_k: Number of final results
            method: Search method to use
            query_intent: Optional intent info to control query expansion
            
        Returns:
            Tuple of (merged search results, expansion info if applicable)
        """
        if method == SearchMethod.DENSE:
            return dense_results[:top_k], None
        
        # Determine if we should expand abbreviations based on intent
        expand_abbreviations = False
        expansion_info = None
        
        if self.config.enable_query_expansion and query_intent is not None:
            # Import here to avoid circular imports
            from utils.bm25_index import should_expand_query, expand_query_abbreviations
            expand_abbreviations = should_expand_query(
                query_type=query_intent.query_type,
                requires_code=query_intent.requires_code
            )
            
            # Create expansion info for logging
            if expand_abbreviations:
                expanded_query = expand_query_abbreviations(query)
                was_actually_expanded = expanded_query != query
                expansion_info = QueryExpansionInfo(
                    original_query=query,
                    expanded_query=expanded_query,
                    was_expanded=was_actually_expanded,
                    query_type=query_intent.query_type,
                    requires_code=query_intent.requires_code,
                    source_name=source_name
                )
                if was_actually_expanded:
                    logger.info(f"Query expanded for {source_name}: '{query}' → '{expanded_query}'")
                else:
                    logger.info(f"Query expansion enabled for {source_name} but no abbreviations found")
            else:
                expansion_info = QueryExpansionInfo(
                    original_query=query,
                    expanded_query=query,
                    was_expanded=False,
                    query_type=query_intent.query_type,
                    requires_code=query_intent.requires_code,
                    source_name=source_name
                )
                logger.info(f"Query expansion skipped for {source_name} (code-specific: type={query_intent.query_type}, requires_code={query_intent.requires_code})")
        
        # Get sparse (BM25) results with optional expansion
        sparse_top_k = int(top_k * self.config.sparse_top_k_multiplier)
        sparse_results = self.bm25_manager.search(
            source_name, query, sparse_top_k, 
            expand_abbreviations=expand_abbreviations
        )
        
        if method == SearchMethod.SPARSE:
            return sparse_results[:top_k], expansion_info
        
        # Hybrid: Fuse dense and sparse results
        if not sparse_results:
            logger.warning(f"No BM25 results for {source_name}, falling back to dense only")
            return dense_results[:top_k], expansion_info
        
        merged = self.fusion.reciprocal_rank_fusion(
            dense_results=dense_results,
            sparse_results=sparse_results,
            top_k=top_k
        )
        
        return merged, expansion_info
    
    def search_multi_source(
        self,
        results_by_source: Dict[str, List[Dict[str, Any]]],
        query: str,
        top_k: int = 10,
        query_intent: Optional[QueryIntent] = None
    ) -> Tuple[Dict[str, List[Dict[str, Any]]], Optional[QueryExpansionInfo]]:
        """
        Apply hybrid search to multiple sources.
        
        Args:
            results_by_source: Dense results organized by source
            query: Search query
            top_k: Results per source
            query_intent: Optional intent info to control query expansion
            
        Returns:
            Tuple of (hybrid results organized by source, first expansion info)
        """
        hybrid_results = {}
        first_expansion_info = None
        
        for source_name, dense_results in results_by_source.items():
            results, expansion_info = self.search(
                source_name=source_name,
                query=query,
                dense_results=dense_results,
                top_k=top_k,
                query_intent=query_intent
            )
            hybrid_results[source_name] = results
            
            # Keep the first expansion info (they should all be the same)
            if first_expansion_info is None and expansion_info is not None:
                first_expansion_info = expansion_info
        
        return hybrid_results, first_expansion_info
    
    def is_available(self, source_name: str) -> bool:
        """Check if hybrid search is available for a source"""
        return self.bm25_manager.is_index_available(source_name)
    
    def get_stats(self) -> Dict[str, Any]:
        """Get hybrid search statistics"""
        return {
            'config': {
                'dense_weight': self.config.dense_weight,
                'sparse_weight': self.config.sparse_weight,
                'rrf_k': self.config.rrf_k
            },
            'bm25_indices': self.bm25_manager.get_stats()
        }
