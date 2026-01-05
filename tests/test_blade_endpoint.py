"""
Test script to verify /inference/blade endpoint is working correctly
"""
import requests
import json
import sys
from pathlib import Path

# Add parent directory to path
sys.path.append(str(Path(__file__).parent.parent))


def test_blade_endpoint():
    """Test the blade inference endpoint"""
    
    BASE_URL = "http://localhost:8000"
    
    # Test queries
    test_cases = [
        {
            "query": "what are different fields in the form of account opening",
            "top_k": 3,
            "rerank": True
        },
        {
            "query": "how is CSRF protection implemented in forms",
            "top_k": 2,
            "rerank": True
        },
        {
            "query": "what blade templates are used for user chat",
            "top_k": 2,
            "rerank": True
        }
    ]
    
    print("🔍 Testing /inference/blade endpoint...")
    print("=" * 60)
    
    for i, test_case in enumerate(test_cases, 1):
        print(f"\n📝 Test Case {i}: {test_case['query']}")
        print("-" * 60)
        
        try:
            response = requests.post(
                f"{BASE_URL}/inference/blade",
                json=test_case,
                timeout=30
            )
            
            if response.status_code == 200:
                data = response.json()
                
                print(f"✅ Status: SUCCESS")
                print(f"📊 Results returned: {len(data.get('results', []))}")
                
                # Show retrieved files
                print("\n📁 Retrieved Files:")
                for j, result in enumerate(data.get('results', []), 1):
                    metadata = result.get('metadata', {})
                    print(f"  {j}. {metadata.get('file_name', 'Unknown')}")
                    if 'rerank_score' in metadata and metadata['rerank_score'] is not None:
                        print(f"     Score: {metadata['rerank_score']:.4f}")
                    print(f"     Snippet Length: {metadata.get('snippet_length', 0)} chars")
                    print(f"     Full Content: {metadata.get('content_length', 0)} chars")
                    reduction = (1 - metadata.get('snippet_length', 0) / max(metadata.get('content_length', 1), 1)) * 100
                    print(f"     Token Reduction: {reduction:.1f}%")
                
                # Show LLM response (first 200 chars)
                if data.get('llm_response'):
                    print(f"\n🤖 LLM Response (preview):")
                    print(f"  {data['llm_response'][:200]}...")
                else:
                    print(f"\n⚠️  No LLM response (LLM service may not be initialized)")
                
                # Show context stats
                context = data.get('context_used', '')
                print(f"\n📈 Context Statistics:")
                print(f"  Total context length: {len(context)} chars")
                print(f"  Estimated tokens: ~{len(context) // 4}")
                
            else:
                print(f"❌ Status: FAILED")
                print(f"   HTTP {response.status_code}")
                print(f"   Response: {response.text[:200]}")
                
        except requests.exceptions.ConnectionError:
            print(f"❌ Connection Error: Backend not running at {BASE_URL}")
            print(f"   Start backend with: cd backend && python main.py")
            return False
        except Exception as e:
            print(f"❌ Error: {str(e)}")
            return False
    
    print("\n" + "=" * 60)
    print("✅ All blade endpoint tests completed!")
    return True


def test_health_check():
    """Test if backend is running"""
    BASE_URL = "http://localhost:8000"
    
    try:
        response = requests.get(f"{BASE_URL}/")
        if response.status_code == 200:
            print(f"✅ Backend is running at {BASE_URL}")
            return True
        else:
            print(f"⚠️  Backend responded with status {response.status_code}")
            return False
    except requests.exceptions.ConnectionError:
        print(f"❌ Backend not running at {BASE_URL}")
        print(f"   Start with: cd backend && python main.py")
        return False


if __name__ == "__main__":
    print("🚀 Blade Endpoint Integration Test")
    print("=" * 60)
    
    # Check if backend is running
    if not test_health_check():
        print("\n💡 Tip: Start backend first, then run this test")
        sys.exit(1)
    
    print()
    
    # Run blade endpoint tests
    success = test_blade_endpoint()
    
    if success:
        print("\n🎉 Integration test passed!")
        sys.exit(0)
    else:
        print("\n❌ Integration test failed!")
        sys.exit(1)
