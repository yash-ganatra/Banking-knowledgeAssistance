import sys
import os
import asyncio
import logging
from pathlib import Path
from unittest.mock import MagicMock

# Add project root to path
root_path = Path(__file__).parent.parent
sys.path.append(str(root_path))
# Add backend to path so 'from security' imports work
sys.path.append(str(root_path / "backend"))

# Set auth env vars if missing (for DB connection)
if "NEO4J_URI" not in os.environ:
    os.environ["NEO4J_URI"] = "bolt://localhost:7687"
if "NEO4J_USER" not in os.environ:
    os.environ["NEO4J_USER"] = "neo4j"
if "NEO4J_PASSWORD" not in os.environ:
    os.environ["NEO4J_PASSWORD"] = "password"

from backend.query_router import QueryRouter, KnowledgeSource, QueryType, QueryIntent

# Mock query engines to avoid needing ChromaDB
class MockEngine:
    def query(self, *args, **kwargs):
        # Return a result that mentions a known entity "HomeController" to trigger graph
        return [{
            "id": "1", 
            "content": "class HomeController extends Controller { ... }", 
            "metadata": {"file_path": "app/Http/Controllers/HomeController.php", "class_name": "HomeController"}, 
            "distance": 0.1
        }]

async def test_router():
    logging.basicConfig(level=logging.INFO)
    logger = logging.getLogger(__name__)
    
    logger.info("Initializing QueryRouter...")
    
    # Mock dependencies
    business = MockEngine()
    php = MockEngine()
    js = MockEngine()
    blade = MagicMock() # Blade engine has different interface, easiest to mock queries
    blade.query.return_value = []
    
    try:
        router = QueryRouter(
            business_engine=business,
            php_engine=php,
            js_engine=js,
            blade_engine=blade,
            use_graph_enhancement=True,
            use_hybrid_search=False # Disable hybrid to simplify test dependencies
        )
    except Exception as e:
        logger.error(f"Failed to init router: {e}")
        import traceback
        traceback.print_exc()
        return
    
    if router.graph_retriever:
        logger.info("✅ GraphEnhancedRetriever initialized successfully")
    else:
        logger.error("❌ GraphEnhancedRetriever failed to initialize (check Neo4j credentials)")
        return

    # Test query_single_source with graph - Using HomeController which we know exists
    logger.info("Testing query_single_source with graph enhancement...")
    query = "How does HomeController work?"
    intent = QueryIntent(query_type=QueryType.IMPLEMENTATION, requires_code=True)
    
    try:
        results, context = await router.query_single_source(
            KnowledgeSource.PHP_CODE,
            query,
            top_k=1,
            query_intent=intent
        )
        logger.info(f"✅ query_single_source returned {len(results)} results")
        
        if context:
            logger.info("✅ GraphContext returned")
            logger.info("--- Context Start ---")
            print(context.to_context_string())
            logger.info("--- Context End ---")
            
            # Check if boost applied
            if results and "graph_boost" in results[0]:
                 logger.info(f"✅ Graph Boost applied: {results[0]['graph_boost']}")
            else:
                 logger.warning("⚠️ No graph boost found in results")
                 
        else:
            logger.warning("⚠️ No GraphContext returned. (Is Neo4j running? Does HomeController exist in graph?)")
            
    except Exception as e:
        logger.error(f"❌ query_single_source failed: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    asyncio.run(test_router())
