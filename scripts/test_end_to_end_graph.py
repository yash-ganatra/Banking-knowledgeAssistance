import sys
import os
import json
from pathlib import Path
from fastapi.testclient import TestClient

# Add project root to path
sys.path.append(str(Path(__file__).parent.parent))

# Add backend to path so imports work
sys.path.append(str(Path(__file__).parent.parent / "backend"))

# Import app from backend
from backend.main import app

def test_end_to_end_graph():
    print("🚀 Starting End-to-End Test for Graph Integration...")
    
    # Initialize TestClient (triggers startup event)
    try:
        client = TestClient(app)
        print("✅ Backend App Initialized (Startup Event Triggered)")
    except Exception as e:
        print(f"❌ Failed to initialize app: {e}")
        return

    # Define a query that should trigger graph traversal
    # "Explain the user registration flow" implies Controller -> View/Model interactions
    query = "Explain the user registration flow and what tables are involved."
    
    payload = {
        "query": query,
        "top_k": 3,
        "confidence_threshold": 0.5,
        "min_relevance_score": 0.5
    }
    
    print(f"\n📡 Sending Query: '{query}'")
    
    try:
        response = client.post("/inference/smart", json=payload)
        
        if response.status_code == 200:
            data = response.json()
            print("✅ Query Successful!")
            
            # Check Routing Decision
            routing = data.get("routing_decision", {})
            print(f"\n--- Routing Decision ---")
            print(f"Primary Source: {routing.get('primary_source')}")
            print(f"Secondary Sources: {routing.get('secondary_sources')}")
            print(f"Confidence: {routing.get('confidence')}")
            
            # Check Sources Queried
            sources = data.get("sources_queried", [])
            print(f"\n--- Sources Queried ---")
            print(sources)
            
            # Check Context Used for Graph Information
            context = data.get("context_used", "")
            print(f"\n--- Context Analysis ---")
            if "Code Relationships (from Knowledge Graph)" in context:
                print("✅ specific Graph Context Section FOUND in context!")
                # Extract graph section
                start_marker = "## Code Relationships (from Knowledge Graph)"
                graph_section = context.split(start_marker)[1].split("\n\n")[0]
                print(f"Graph Data Snippet:\n{graph_section[:500]}...")
            else:
                print("⚠️ Graph Context Section NOT found. Graph enhancement might have failed or returned empty.")
                # Print full context locally to debug if needed
                # print(context)
            
            # Check LLM Response
            llm_response = data.get("llm_response", "")
            print(f"\n--- LLM Response Snippet ---")
            print(llm_response[:500] + "..." if len(llm_response) > 500 else llm_response)
            
        else:
            print(f"❌ Query Failed: {response.status_code}")
            print(response.text)
            
    except Exception as e:
        print(f"❌ unexpected error during request: {e}")

if __name__ == "__main__":
    test_end_to_end_graph()
