import sys
import os
import logging
from typing import Dict, List
from pathlib import Path

# Add project root to path
sys.path.append(str(Path(__file__).parent.parent))

# Add backend to path so imports work
sys.path.append(str(Path(__file__).parent.parent / "backend"))

# Import ResultFusion
# Note: ResultFusion is defined in backend/query_router.py
from backend.query_router import ResultFusion

def verify_rrf():
    logging.basicConfig(level=logging.INFO, format='%(message)s')
    logger = logging.getLogger(__name__)
    
    logger.info("Verifying RRF Functionality...")
    
    # Initialize RRF with k=60 (default) and NO cross-encoder (to isolate RRF logic)
    fusion = ResultFusion(k=60, use_cross_encoder=False)
    
    # Simulate results from 3 sources
    # Each source returns 3 results.
    # Scores (distances) are irrelevant for RRF unless filtered, let's assume valid distances.
    
    results_by_source = {
        "PHP": [
            {"id": "php_1", "content": "PHP Item 1", "distance": 0.1},
            {"id": "php_2", "content": "PHP Item 2", "distance": 0.2},
            {"id": "php_3", "content": "PHP Item 3", "distance": 0.3},
        ],
        "JS": [
            {"id": "js_1", "content": "JS Item 1", "distance": 0.1},
            {"id": "js_2", "content": "JS Item 2", "distance": 0.2},
            {"id": "js_3", "content": "JS Item 3", "distance": 0.3},
        ],
        "Docs": [
            {"id": "doc_1", "content": "Doc Item 1", "distance": 0.1},
            {"id": "doc_2", "content": "Doc Item 2", "distance": 0.2},
            {"id": "doc_3", "content": "Doc Item 3", "distance": 0.3},
        ]
    }
    
    # Run RRF
    # We ask for top_k=6.
    # Logic effectively creates a pool of 9 items.
    # RRF should interleave them: Rank 1s, then Rank 2s.
    
    logger.info("\n--- Input: 3 Sources, 3 Items each ---")
    merged = fusion.reciprocal_rank_fusion(results_by_source, top_k=9, source_quality_threshold=1.0)
    
    logger.info(f"\n--- Output: {len(merged)} Merged Items ---")
    
    # Print results with scores
    for i, result in enumerate(merged, 1):
        rrf_score = result.get('rrf_score', 0)
        source = result.get('source')
        original_rank = result.get('original_rank')
        logger.info(f"Rank {i}: {source} (Orig Rank {original_rank}) - Score: {rrf_score:.6f}")
        
    # Verify duplicates
    scores = [r['rrf_score'] for r in merged]
    unique_scores = set(scores)
    logger.info(f"\nUnique Scores: {len(unique_scores)} out of {len(merged)} items")
    
    if len(unique_scores) < len(merged):
        logger.info("✅ CONFIRMED: Multiple entries have the same RRF score.")
        logger.info("This is expected because RRF scores are based on rank (1/(k+rank)).")
        logger.info("Items with the same rank from different sources get the same score.")
    else:
        logger.error("❌ Unexpected: All scores are unique?")

    # Verify order preservation (Stable Sort)
    # Check if items with same score appear in order of sources (PHP, JS, Docs)
    # This depends on implementation detail of 'sorted' stable sort.
    
    logger.info("\n--- Analysis of Score Relevance ---")
    logger.info("The RRF score matters for CANDIDATE SELECTION.")
    logger.info("If we only select top 5 items for re-ranking:")
    
    top_5 = merged[:5]
    sources_in_top_5 = [r['source'] for r in top_5]
    logger.info(f"Top 5 selected: {sources_in_top_5}")
    
    # Expected: Rank 1 from PHP, JS, Docs (3 items) + Rank 2 from PHP, JS (2 items) = 5 items.
    # Note: Docs Rank 2 is excluded if limit is 5.
    
    php_count = sources_in_top_5.count("PHP")
    js_count = sources_in_top_5.count("JS")
    docs_count = sources_in_top_5.count("Docs")
    
    logger.info(f"Counts: PHP={php_count}, JS={js_count}, Docs={docs_count}")
    
    if abs(php_count - js_count) <= 1 and abs(php_count - docs_count) <= 1:
        logger.info("✅ RRF successfully balanced selection across sources.")
    else:
        logger.warning("⚠️ RRF selection might be unbalanced.")

if __name__ == "__main__":
    verify_rrf()
