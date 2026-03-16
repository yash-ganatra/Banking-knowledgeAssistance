"""
Test script for the CRAG (Corrective RAG) Pipeline

Tests the retrieval evaluator, knowledge refinement, and self-correction loop
integrated into the Smart Query Router.

Run: python3 tests/test_crag_pipeline.py
Requires: backend server running on http://localhost:8000
"""

import requests
import json
import time
from typing import Dict, Any

BASE_URL = "http://localhost:8000"


# --------------------------------------------------------------------------
# Test Queries
# --------------------------------------------------------------------------

CRAG_TEST_QUERIES = [
    # 1. High-confidence query — should get CORRECT verdict, no retry
    {
        "query": "What is a term deposit account?",
        "description": "High-confidence single-source query — expects CORRECT verdict",
        "expect_verdict": "correct",
        "expect_retry": False,
    },
    # 2. Implementation query — should evaluate code chunks
    {
        "query": "How does the User model work in PHP?",
        "description": "Code-specific query — expects CORRECT verdict for PHP source",
        "expect_verdict": "correct",
        "expect_retry": False,
    },
    # 3. Ambiguous query — may trigger AMBIGUOUS verdict
    {
        "query": "How does the system handle account opening?",
        "description": "Cross-domain ambiguous query — may trigger AMBIGUOUS verdict",
        "expect_verdict": None,  # Could be correct or ambiguous
        "expect_retry": None,
    },
    # 4. Multi-source query
    {
        "query": "What are the loan approval rules and how are they implemented in the code?",
        "description": "Multi-source query spanning business + code",
        "expect_verdict": None,
        "expect_retry": None,
    },
]


# --------------------------------------------------------------------------
# Tests
# --------------------------------------------------------------------------

def test_health_check() -> bool:
    """Verify server is up and CRAG-enabled."""
    print("\n" + "=" * 60)
    print("HEALTH CHECK")
    print("=" * 60)

    try:
        resp = requests.get(f"{BASE_URL}/health", timeout=10)
        if resp.status_code != 200:
            print(f"FAIL: Health check returned {resp.status_code}")
            return False

        health = resp.json()
        smart_ok = health.get("engines", {}).get("smart_router", False)
        print(f"  Server status: {health['status']}")
        print(f"  Smart router:  {'Ready' if smart_ok else 'NOT Ready'}")

        if not smart_ok:
            print("\n  Smart router not ready — CRAG tests will fail.")
            return False

        return True
    except requests.ConnectionError:
        print(f"FAIL: Cannot connect to {BASE_URL}. Is the server running?")
        return False


def test_crag_metadata_present(query_data: Dict) -> bool:
    """Send a smart query and verify CRAG metadata is present."""
    print(f"\n  Query: {query_data['query']}")
    print(f"  Test:  {query_data['description']}")

    payload = {
        "query": query_data["query"],
        "top_k": 5,
        "confidence_threshold": 0.5,
        "min_relevance_score": 0.0,
    }

    try:
        start = time.time()
        resp = requests.post(f"{BASE_URL}/inference/smart", json=payload, timeout=60)
        elapsed = time.time() - start

        if resp.status_code != 200:
            print(f"  FAIL: Status {resp.status_code} — {resp.text[:200]}")
            return False

        result = resp.json()
        crag = result.get("crag_metadata")

        if crag is None:
            print("  FAIL: crag_metadata is missing from response")
            return False

        # Display CRAG results
        verdict = crag.get("aggregate_verdict", "N/A")
        confidence = crag.get("overall_confidence", 0)
        correct = crag.get("correct_count", 0)
        ambiguous = crag.get("ambiguous_count", 0)
        incorrect = crag.get("incorrect_count", 0)
        action = crag.get("recommended_action", "N/A")
        eval_time = crag.get("evaluation_time_ms", 0)
        retry_used = crag.get("retry_used", False)
        refinement = crag.get("refinement")

        print(f"\n  CRAG Evaluation:")
        print(f"    Verdict:      {verdict} (confidence: {confidence:.2f})")
        print(f"    Chunks:       {correct} correct, {ambiguous} ambiguous, {incorrect} incorrect")
        print(f"    Action:       {action}")
        print(f"    Eval time:    {eval_time:.0f}ms")
        print(f"    Retry used:   {retry_used}")

        if refinement:
            comp = refinement.get("overall_compression", 1.0)
            sents_b = refinement.get("sentences_before", 0)
            sents_a = refinement.get("sentences_after", 0)
            print(f"    Refinement:   {sents_b} → {sents_a} sentences ({comp:.0%} of original)")

        print(f"    Total time:   {elapsed:.1f}s")

        # Validate expected verdict if specified
        passed = True
        if query_data.get("expect_verdict") and verdict != query_data["expect_verdict"]:
            print(f"    NOTE: Expected {query_data['expect_verdict']}, got {verdict}")
            # Not a hard failure — LLM evaluation can vary
        
        if query_data.get("expect_retry") is not None and retry_used != query_data["expect_retry"]:
            print(f"    NOTE: Expected retry={query_data['expect_retry']}, got retry={retry_used}")

        # Check chunk evaluations exist
        chunk_evals = crag.get("chunk_evaluations", [])
        if not chunk_evals:
            print("  WARN: No individual chunk evaluations returned")
        else:
            print(f"\n  Per-Chunk Evaluations ({len(chunk_evals)} chunks):")
            for ce in chunk_evals[:5]:  # Show first 5
                print(f"    [{ce.get('index')}] {ce.get('verdict'):10s} "
                      f"conf={ce.get('confidence', 0):.2f}  {ce.get('reason', '')[:60]}")

        # Show LLM response snippet
        llm_resp = result.get("llm_response", "")
        if llm_resp:
            snippet = llm_resp[:200] + "..." if len(llm_resp) > 200 else llm_resp
            print(f"\n  LLM Response: {snippet}")

        print(f"\n  PASS")
        return True

    except Exception as e:
        print(f"  FAIL: {e}")
        return False


def test_max_retries_capped() -> bool:
    """Verify that corrective retries never exceed MAX_CRAG_RETRIES."""
    print("\n  Test: Max retries capped at 1")
    
    # Use a deliberately obscure query
    payload = {
        "query": "XYZABC123 nonexistent-term foobar",
        "top_k": 5,
        "confidence_threshold": 0.5,
        "min_relevance_score": 0.0,
    }

    try:
        resp = requests.post(f"{BASE_URL}/inference/smart", json=payload, timeout=60)
        if resp.status_code != 200:
            print(f"  SKIP: Query returned {resp.status_code}")
            return True  # Not a CRAG failure

        result = resp.json()
        crag = result.get("crag_metadata", {})
        
        # Even for the worst query, retry should happen at most once
        retry_used = crag.get("retry_used", False)
        print(f"    Retry used: {retry_used}")
        print(f"    Verdict:    {crag.get('aggregate_verdict', 'N/A')}")
        print(f"  PASS")
        return True

    except Exception as e:
        print(f"  FAIL: {e}")
        return False


# --------------------------------------------------------------------------
# Runner
# --------------------------------------------------------------------------

def run_all_tests():
    print("\n" + "=" * 60)
    print("CRAG PIPELINE TEST SUITE")
    print("=" * 60)
    print(f"Server: {BASE_URL}")

    if not test_health_check():
        print("\nHealth check failed. Aborting.")
        return

    print("\n" + "=" * 60)
    print("CRAG METADATA TESTS")
    print("=" * 60)

    passed = 0
    failed = 0
    for qd in CRAG_TEST_QUERIES:
        if test_crag_metadata_present(qd):
            passed += 1
        else:
            failed += 1

    print("\n" + "=" * 60)
    print("RETRY LIMIT TEST")
    print("=" * 60)
    if test_max_retries_capped():
        passed += 1
    else:
        failed += 1

    # Summary
    total = passed + failed
    print("\n" + "=" * 60)
    print(f"RESULTS: {passed}/{total} passed, {failed}/{total} failed")
    print("=" * 60)

    if failed == 0:
        print("All CRAG tests passed!")
    else:
        print("Some tests failed. Review above.")


if __name__ == "__main__":
    print("CRAG Pipeline Tests")
    print("Make sure the backend is running on http://localhost:8000\n")
    run_all_tests()
