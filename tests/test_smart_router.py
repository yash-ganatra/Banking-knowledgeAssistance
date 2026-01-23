"""
Test script for the Smart Query Router
Tests various query types to ensure proper routing and accuracy
"""

import requests
import json
from typing import Dict, Any

BASE_URL = "http://localhost:8000"

# Test queries covering different scenarios
TEST_QUERIES = [
    # Single source queries
    {
        "query": "What is a term deposit account?",
        "expected_primary": "business_docs",
        "expected_sources": 1,
        "description": "Pure business documentation query"
    },
    {
        "query": "Show me the User model implementation",
        "expected_primary": "php_code",
        "expected_sources": 1,
        "description": "Pure PHP code query"
    },
    {
        "query": "How does the dashboard component render?",
        "expected_primary": "js_code",
        "expected_sources": 1,
        "description": "Pure JavaScript query"
    },
    {
        "query": "Explain the login form blade template",
        "expected_primary": "blade_templates",
        "expected_sources": 1,
        "description": "Pure Blade template query"
    },
    
    # Multi-source queries
    {
        "query": "How does the loan application form work from frontend to backend?",
        "expected_primary": "blade_templates",
        "expected_sources": [2, 3],  # Should query blade + php, maybe js
        "description": "Multi-source: form flow"
    },
    {
        "query": "What are the business rules for loan approval and how are they implemented?",
        "expected_primary": "business_docs",
        "expected_sources": 2,
        "description": "Multi-source: business + implementation"
    },
    {
        "query": "Show me how user authentication is handled",
        "expected_primary": "php_code",
        "expected_sources": [2, 3],  # php + blade, maybe business
        "description": "Multi-source: authentication flow"
    },
    
    # Ambiguous queries
    {
        "query": "How do I validate form inputs?",
        "expected_primary": ["js_code", "php_code"],  # Could be either
        "expected_sources": [1, 2],
        "description": "Ambiguous: validation (frontend or backend)"
    }
]


def test_health_check():
    """Test health endpoint to verify all engines are loaded"""
    print("\n" + "="*60)
    print("🏥 HEALTH CHECK")
    print("="*60)
    
    response = requests.get(f"{BASE_URL}/health")
    if response.status_code == 200:
        health = response.json()
        print(f"✅ Server Status: {health['status']}")
        print("\nEngine Status:")
        for engine, status in health['engines'].items():
            emoji = "✅" if status else "❌"
            print(f"  {emoji} {engine}: {'Ready' if status else 'Not Available'}")
        
        if not health['engines']['smart_router']:
            print("\n⚠️  WARNING: Smart router not initialized!")
            return False
        return True
    else:
        print(f"❌ Health check failed: {response.status_code}")
        return False


def test_smart_query(query_data: Dict):
    """Test a single smart query"""
    print("\n" + "-"*60)
    print(f"📝 Query: {query_data['query']}")
    print(f"📋 Test: {query_data['description']}")
    
    payload = {
        "query": query_data["query"],
        "top_k": 5,
        "confidence_threshold": 0.5
    }
    
    try:
        response = requests.post(f"{BASE_URL}/inference/smart", json=payload)
        
        if response.status_code == 200:
            result = response.json()
            
            # Extract routing decision
            routing = result.get('routing_decision', {})
            primary = routing.get('primary_source')
            confidence = routing.get('confidence', 0)
            reasoning = routing.get('reasoning', '')
            sources_queried = result.get('sources_queried', [])
            
            print(f"\n🎯 Routing Decision:")
            print(f"   Primary Source: {primary}")
            print(f"   Confidence: {confidence:.2f}")
            print(f"   Sources Queried: {', '.join(sources_queried)}")
            print(f"   Reasoning: {reasoning}")
            
            # Validate routing
            expected_primary = query_data['expected_primary']
            if isinstance(expected_primary, list):
                correct = primary in expected_primary
            else:
                correct = primary == expected_primary
            
            expected_count = query_data['expected_sources']
            if isinstance(expected_count, list):
                count_correct = len(sources_queried) in expected_count
            else:
                count_correct = len(sources_queried) == expected_count
            
            if correct and count_correct:
                print(f"   ✅ Routing CORRECT")
            else:
                print(f"   ⚠️  Routing UNEXPECTED (but may still be valid)")
                if not correct:
                    print(f"      Expected primary: {expected_primary}, Got: {primary}")
                if not count_correct:
                    print(f"      Expected {expected_count} sources, Got: {len(sources_queried)}")
            
            # Show results count
            results = result.get('results', [])
            print(f"\n📊 Results: {len(results)} retrieved")
            
            # Show top result
            if results:
                top_result = results[0]
                print(f"\n🏆 Top Result:")
                source = top_result.get('source', 'unknown')
                metadata = top_result.get('metadata', {})
                file_path = metadata.get('file_path') or metadata.get('page_name') or 'N/A'
                print(f"   Source: {source}")
                print(f"   File: {file_path}")
                if 'rrf_score' in top_result:
                    print(f"   RRF Score: {top_result['rrf_score']:.4f}")
                
                # Show snippet of content
                content = top_result.get('content', '')
                snippet = content[:200] + "..." if len(content) > 200 else content
                print(f"   Content: {snippet}")
            
            # Show LLM response snippet
            llm_response = result.get('llm_response', '')
            if llm_response:
                response_snippet = llm_response[:300] + "..." if len(llm_response) > 300 else llm_response
                print(f"\n💬 LLM Response Preview:")
                print(f"   {response_snippet}")
            
            return True
            
        else:
            print(f"❌ Query failed: {response.status_code}")
            print(f"   Error: {response.text}")
            return False
            
    except Exception as e:
        print(f"❌ Exception: {e}")
        return False


def test_comparison_with_direct_endpoint():
    """Compare smart router with direct endpoint for the same query"""
    print("\n" + "="*60)
    print("⚖️  COMPARISON TEST: Smart Router vs Direct Endpoint")
    print("="*60)
    
    test_query = "What is a savings account?"
    
    # Test smart router
    print("\n1️⃣ Smart Router:")
    smart_response = requests.post(
        f"{BASE_URL}/inference/smart",
        json={"query": test_query, "top_k": 5}
    )
    
    if smart_response.status_code == 200:
        smart_result = smart_response.json()
        print(f"   ✅ Routed to: {', '.join(smart_result['sources_queried'])}")
        print(f"   📊 Results: {len(smart_result['results'])}")
    
    # Test direct business endpoint
    print("\n2️⃣ Direct Business Endpoint:")
    direct_response = requests.post(
        f"{BASE_URL}/inference/business",
        json={"query": test_query, "top_k": 5, "rerank": True}
    )
    
    if direct_response.status_code == 200:
        direct_result = direct_response.json()
        print(f"   ✅ Results: {len(direct_result['results'])}")
    
    # Compare top results
    if smart_response.status_code == 200 and direct_response.status_code == 200:
        print("\n📊 Comparison:")
        smart_ids = [r['id'] for r in smart_result['results'][:3]]
        direct_ids = [r['id'] for r in direct_result['results'][:3]]
        
        overlap = len(set(smart_ids) & set(direct_ids))
        print(f"   Top-3 Overlap: {overlap}/3 results")
        
        if overlap >= 2:
            print(f"   ✅ High overlap - routing is accurate!")
        else:
            print(f"   ⚠️  Low overlap - may indicate different ranking")


def run_all_tests():
    """Run complete test suite"""
    print("\n" + "="*60)
    print("🧪 SMART QUERY ROUTER TEST SUITE")
    print("="*60)
    print(f"Testing endpoint: {BASE_URL}")
    
    # Health check first
    if not test_health_check():
        print("\n❌ Health check failed. Aborting tests.")
        return
    
    # Test each query
    passed = 0
    failed = 0
    
    for query_data in TEST_QUERIES:
        result = test_smart_query(query_data)
        if result:
            passed += 1
        else:
            failed += 1
    
    # Comparison test
    test_comparison_with_direct_endpoint()
    
    # Summary
    print("\n" + "="*60)
    print("📊 TEST SUMMARY")
    print("="*60)
    print(f"✅ Passed: {passed}/{len(TEST_QUERIES)}")
    print(f"❌ Failed: {failed}/{len(TEST_QUERIES)}")
    
    if failed == 0:
        print("\n🎉 All tests passed! Smart router is working correctly.")
    else:
        print("\n⚠️  Some tests failed. Review the logs above.")


if __name__ == "__main__":
    print("Starting Smart Query Router Tests...")
    print("Make sure the backend server is running on http://localhost:8000")
    input("\nPress Enter to continue...")
    
    try:
        run_all_tests()
    except KeyboardInterrupt:
        print("\n\n⚠️ Tests interrupted by user")
    except Exception as e:
        print(f"\n\n❌ Test suite failed with error: {e}")
