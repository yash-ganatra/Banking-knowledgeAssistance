#!/usr/bin/env python3
"""
Test script for verifying filtering improvements
Tests various query types to ensure irrelevant business_docs chunks are filtered
"""

import requests
import json
from typing import Dict, List

BASE_URL = "http://localhost:8000"
TOKEN = "YOUR_TOKEN_HERE"  # Replace with actual token

HEADERS = {
    "Authorization": f"Bearer {TOKEN}",
    "Content-Type": "application/json"
}

# Test queries categorized by expected behavior
TEST_QUERIES = {
    "code_only_no_business": [
        {
            "query": "How does UserController validate input data?",
            "expected_sources": ["php_code"],
            "should_not_include": ["business_docs"],
            "description": "Code implementation query"
        },
        {
            "query": "Show me the AuthService authentication logic",
            "expected_sources": ["php_code"],
            "should_not_include": ["business_docs"],
            "description": "Specific service query"
        },
        {
            "query": "Debug DSA create account function",
            "expected_sources": ["php_code"],
            "should_not_include": ["business_docs"],
            "description": "Debugging query"
        },
        {
            "query": "What does the checkKYC command do?",
            "expected_sources": ["php_code"],
            "should_not_include": ["business_docs"],
            "description": "Command implementation query"
        }
    ],
    "business_only": [
        {
            "query": "What are the KYC requirements for account opening?",
            "expected_sources": ["business_docs"],
            "should_not_include": [],
            "description": "Pure business concept"
        },
        {
            "query": "Explain the loan approval process",
            "expected_sources": ["business_docs"],
            "should_not_include": [],
            "description": "Business workflow query"
        }
    ],
    "mixed_needed": [
        {
            "query": "How is the loan approval process implemented in the backend?",
            "expected_sources": ["business_docs", "php_code"],
            "should_not_include": [],
            "description": "Business + implementation"
        },
        {
            "query": "Complete account opening flow from UI to database with business rules",
            "expected_sources": ["blade_templates", "js_code", "php_code", "business_docs"],
            "should_not_include": [],
            "description": "Full end-to-end flow"
        }
    ]
}

def test_query(query_data: Dict, min_relevance_score: float = 2.0) -> Dict:
    """Test a single query and analyze results"""
    
    payload = {
        "query": query_data["query"],
        "top_k": 5,
        "min_relevance_score": min_relevance_score
    }
    
    print(f"\n{'='*80}")
    print(f"TESTING: {query_data['description']}")
    print(f"Query: {query_data['query']}")
    print(f"Expected sources: {query_data['expected_sources']}")
    print(f"Should NOT include: {query_data['should_not_include']}")
    print(f"Min relevance score: {min_relevance_score}")
    print(f"{'='*80}")
    
    try:
        response = requests.post(
            f"{BASE_URL}/inference/smart",
            headers=HEADERS,
            json=payload,
            timeout=30
        )
        
        if response.status_code != 200:
            print(f"❌ ERROR: Status {response.status_code}")
            print(response.text)
            return {"success": False, "error": response.text}
        
        result = response.json()
        
        # Analyze results
        sources_found = set()
        results_by_source = {}
        
        for chunk in result.get("results", []):
            source = chunk.get("source", "unknown")
            sources_found.add(source)
            
            if source not in results_by_source:
                results_by_source[source] = []
            
            results_by_source[source].append({
                "distance": chunk.get("original_distance", chunk.get("distance")),
                "rrf_score": chunk.get("rrf_score"),
                "cross_encoder_score": chunk.get("cross_encoder_score"),
                "content_preview": chunk.get("content", "")[:100]
            })
        
        # Check if unwanted sources appeared
        unwanted_found = [s for s in query_data["should_not_include"] if s in sources_found]
        
        # Print results
        print(f"\n📊 RESULTS ANALYSIS:")
        print(f"Sources found: {list(sources_found)}")
        print(f"Total chunks: {len(result.get('results', []))}")
        
        for source, chunks in results_by_source.items():
            print(f"\n  {source}: {len(chunks)} chunks")
            for i, chunk in enumerate(chunks, 1):
                print(f"    [{i}] distance={chunk['distance']:.3f}, "
                      f"rrf={chunk['rrf_score']:.4f}, "
                      f"ce_score={chunk['cross_encoder_score']:.2f}")
                print(f"        Preview: {chunk['content_preview']}...")
        
        # Verdict
        if unwanted_found:
            print(f"\n❌ FAIL: Unwanted sources appeared: {unwanted_found}")
            return {
                "success": False,
                "unwanted_sources": unwanted_found,
                "all_sources": list(sources_found)
            }
        else:
            print(f"\n✅ PASS: No unwanted sources found")
            return {
                "success": True,
                "sources": list(sources_found)
            }
            
    except Exception as e:
        print(f"❌ EXCEPTION: {str(e)}")
        return {"success": False, "error": str(e)}

def run_all_tests():
    """Run all test queries"""
    
    print(f"\n{'#'*80}")
    print(f"# FILTERING IMPROVEMENTS TEST SUITE")
    print(f"# Testing queries that should NOT include business_docs")
    print(f"{'#'*80}")
    
    results = {
        "passed": 0,
        "failed": 0,
        "errors": 0
    }
    
    # Test code-only queries (most important for filtering)
    print("\n\n### CATEGORY 1: Code-Only Queries (should NOT include business_docs)")
    for query_data in TEST_QUERIES["code_only_no_business"]:
        result = test_query(query_data, min_relevance_score=2.0)
        
        if result.get("success"):
            results["passed"] += 1
        elif "error" in result:
            results["errors"] += 1
        else:
            results["failed"] += 1
    
    # Test business-only queries
    print("\n\n### CATEGORY 2: Business-Only Queries (should include business_docs)")
    for query_data in TEST_QUERIES["business_only"]:
        result = test_query(query_data, min_relevance_score=2.0)
        
        if result.get("success"):
            results["passed"] += 1
        elif "error" in result:
            results["errors"] += 1
        else:
            results["failed"] += 1
    
    # Test mixed queries
    print("\n\n### CATEGORY 3: Mixed Queries (may include business_docs)")
    for query_data in TEST_QUERIES["mixed_needed"]:
        result = test_query(query_data, min_relevance_score=2.0)
        
        if result.get("success"):
            results["passed"] += 1
        elif "error" in result:
            results["errors"] += 1
        else:
            results["failed"] += 1
    
    # Summary
    print(f"\n\n{'#'*80}")
    print(f"# TEST SUMMARY")
    print(f"{'#'*80}")
    print(f"Passed: {results['passed']}")
    print(f"Failed: {results['failed']}")
    print(f"Errors: {results['errors']}")
    print(f"Total: {results['passed'] + results['failed'] + results['errors']}")
    
    if results['failed'] == 0 and results['errors'] == 0:
        print("\n✅ ALL TESTS PASSED!")
    else:
        print("\n❌ SOME TESTS FAILED")
        print("\nTroubleshooting tips:")
        print("1. Check logs for routing decisions and filtering stats")
        print("2. Increase min_relevance_score to 3.0 for stricter filtering")
        print("3. Review FILTERING_IMPROVEMENTS.md for tuning parameters")

def test_with_different_thresholds():
    """Test same query with different relevance thresholds"""
    
    query_data = TEST_QUERIES["code_only_no_business"][0]
    
    print(f"\n{'#'*80}")
    print(f"# THRESHOLD TUNING TEST")
    print(f"# Testing same query with different min_relevance_score values")
    print(f"{'#'*80}")
    
    thresholds = [1.0, 1.5, 2.0, 2.5, 3.0]
    
    for threshold in thresholds:
        print(f"\n\n### Testing with min_relevance_score = {threshold}")
        test_query(query_data, min_relevance_score=threshold)

if __name__ == "__main__":
    import sys
    
    if len(sys.argv) > 1 and sys.argv[1] == "--tune":
        test_with_different_thresholds()
    else:
        run_all_tests()
    
    print(f"\n\nTo test threshold tuning, run: python {sys.argv[0]} --tune")
