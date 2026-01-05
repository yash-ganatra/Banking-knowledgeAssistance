"""
Realistic blade inference tests using actual banking application queries
Tests the blade knowledge base with domain-specific queries
"""
import sys
import json
from pathlib import Path

# Add parent directory to path
sys.path.append(str(Path(__file__).parent.parent))

from utils.blade_description_engine import BladeDescriptionEngine
from groq import Groq
import os
from dotenv import load_dotenv
import time

# Load environment variables
load_dotenv()

# Initialize engine
db_path = "vector_db/blade_views_chroma_db"
blade_engine = BladeDescriptionEngine(db_path=db_path)

# Initialize Groq for LLM responses
groq_client = None
try:
    groq_client = Groq(api_key=os.getenv("GROQ_API_KEY"))
except Exception as e:
    print(f"⚠️  Groq not initialized: {e}")


def test_query(query, expected_files=None, category="general"):
    """Test a single query and validate results"""
    print(f"\n{'='*80}")
    print(f"📝 Query: {query}")
    print(f"🏷️  Category: {category}")
    if expected_files:
        print(f"🎯 Expected files: {', '.join(expected_files)}")
    print(f"{'-'*80}")
    
    start_time = time.time()
    
    try:
        # Query the engine
        results = blade_engine.query(
            query_text=query,
            top_k=3,
            initial_candidates=20,
            max_snippet_chars=2000,
            use_rerank=True
        )
        
        query_time = time.time() - start_time
        
        if not results:
            print("❌ No results returned")
            return False
        
        # Extract retrieved files
        retrieved_files = [r['file_name'] for r in results]
        print(f"\n📁 Retrieved Files:")
        for i, result in enumerate(results, 1):
            print(f"  {i}. {result['file_name']}")
            print(f"     Section: {result['section']}")
            print(f"     Rerank Score: {result.get('rerank_score', 0):.4f}")
            print(f"     Snippet: {result['snippet_length']} / {result['content_length']} chars")
            print(f"     Reduction: {(1 - result['snippet_length'] / result['content_length']) * 100:.1f}%")
        
        # Validate expected files if provided
        success = True
        if expected_files:
            matches = sum(1 for ef in expected_files if ef in retrieved_files)
            accuracy = (matches / len(expected_files)) * 100
            print(f"\n🎯 Accuracy: {matches}/{len(expected_files)} ({accuracy:.0f}%)")
            
            if matches == 0:
                print(f"❌ FAILED: None of expected files retrieved")
                success = False
            elif matches < len(expected_files):
                missing = [f for f in expected_files if f not in retrieved_files]
                print(f"⚠️  Partial match. Missing: {', '.join(missing)}")
            else:
                print(f"✅ All expected files retrieved!")
        
        # Generate LLM response if available
        if groq_client:
            try:
                context = blade_engine.format_context_for_llm(results, include_code=True)
                system_prompt = """You are an expert Laravel Blade developer analyzing a banking application.
Answer based strictly on the provided blade template context. Be concise but thorough."""
                
                response = groq_client.chat.completions.create(
                    messages=[
                        {"role": "system", "content": system_prompt},
                        {"role": "user", "content": f"Context:\n{context}\n\nQuestion: {query}"}
                    ],
                    model="llama-3.3-70b-versatile",
                    temperature=0.1,
                    max_tokens=500
                )
                
                llm_answer = response.choices[0].message.content
                print(f"\n🤖 LLM Answer (preview):")
                print(f"   {llm_answer[:250]}...")
            except Exception as e:
                print(f"\n⚠️  LLM generation failed: {e}")
        
        print(f"\n⏱️  Query Time: {query_time:.2f}s")
        print(f"✅ Test {'PASSED' if success else 'FAILED'}")
        
        return success
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        import traceback
        traceback.print_exc()
        return False


def run_category_tests(category_name, queries):
    """Run all tests in a category"""
    print(f"\n\n{'#'*80}")
    print(f"# CATEGORY: {category_name.upper().replace('_', ' ')}")
    print(f"{'#'*80}")
    
    passed = 0
    total = len(queries)
    
    for query_data in queries:
        if isinstance(query_data, dict):
            success = test_query(
                query_data['query'],
                query_data.get('expected_files'),
                query_data.get('category', category_name)
            )
        else:
            success = test_query(query_data, category=category_name)
        
        if success:
            passed += 1
    
    print(f"\n{'='*80}")
    print(f"Category Summary: {passed}/{total} tests passed ({(passed/total)*100:.0f}%)")
    print(f"{'='*80}")
    
    return passed, total


def main():
    """Run comprehensive blade inference tests"""
    print("🚀 REALISTIC BLADE KNOWLEDGE BASE TESTING")
    print("="*80)
    
    # Load test queries
    queries_file = Path(__file__).parent / "blade_realistic_queries.json"
    with open(queries_file, 'r') as f:
        test_data = json.load(f)
    
    total_passed = 0
    total_tests = 0
    
    # Test each category
    categories_to_test = [
        ('account_opening_forms', test_data['categories']['account_opening_forms']),
        ('authentication_security', test_data['categories']['authentication_security']),
        ('kyc_documents', test_data['categories']['kyc_documents']),
        ('delight_kit_management', test_data['categories']['delight_kit_management']),
        ('chat_communication', test_data['categories']['chat_communication']),
        ('tracking_monitoring', test_data['categories']['tracking_monitoring']),
    ]
    
    for category_name, queries in categories_to_test:
        passed, total = run_category_tests(category_name, queries)
        total_passed += passed
        total_tests += total
    
    # Final summary
    print(f"\n\n{'#'*80}")
    print(f"# FINAL RESULTS")
    print(f"{'#'*80}")
    print(f"\n✅ Passed: {total_passed}/{total_tests}")
    print(f"❌ Failed: {total_tests - total_passed}/{total_tests}")
    print(f"📊 Success Rate: {(total_passed/total_tests)*100:.1f}%")
    
    if total_passed == total_tests:
        print(f"\n🎉 ALL TESTS PASSED!")
        return 0
    elif total_passed / total_tests >= 0.8:
        print(f"\n✅ GOOD: 80%+ success rate")
        return 0
    else:
        print(f"\n⚠️  NEEDS IMPROVEMENT: <80% success rate")
        return 1


if __name__ == "__main__":
    sys.exit(main())
