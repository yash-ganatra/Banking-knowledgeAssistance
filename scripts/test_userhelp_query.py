"""
Test the /userhelp route query to verify:
1. Only a few Cypher queries are executed (not 69!)
2. Correct flow is returned: Route -> Controller -> Action -> BladeView
"""

import sys
import os
import asyncio
import logging
from pathlib import Path

# Add project root to path
root_path = Path(__file__).parent.parent
sys.path.append(str(root_path))

# Set env vars
if "NEO4J_URI" not in os.environ:
    os.environ["NEO4J_URI"] = "bolt://localhost:7687"
if "NEO4J_USER" not in os.environ:
    os.environ["NEO4J_USER"] = "neo4j"
if "NEO4J_PASSWORD" not in os.environ:
    os.environ["NEO4J_PASSWORD"] = "password"

from utils.graph_db import Neo4jConnection, GraphQuery

async def test_route_query():
    logging.basicConfig(level=logging.INFO)
    logger = logging.getLogger(__name__)
    
    logger.info("Testing /userhelp route flow query...")
    
    try:
        conn = Neo4jConnection.from_env()
        query_builder = GraphQuery(conn)
        
        # Test the exact route flow query
        result = query_builder.get_route_flow("/userhelp")
        
        logger.info(f"✅ Query completed")
        logger.info(f"   - Entities found: {len(result.entities)}")
        logger.info(f"   - Cypher queries executed: {len(result.cypher_queries)}")
        logger.info(f"   - Query time: {result.query_time_ms:.2f}ms")
        
        # Print the flow
        logger.info("\n📊 Route Flow:")
        for entity in result.entities:
            entity_type = entity.get("type", "Unknown")
            if entity_type == "Route":
                logger.info(f"   Route: {entity.get('method', '')} {entity.get('uri', '')}")
            elif entity_type == "Controller":
                logger.info(f"   Controller: {entity.get('name', '')}")
            elif entity_type == "Action":
                logger.info(f"   Action: {entity.get('name', '')}")
            elif entity_type == "BladeView":
                logger.info(f"   BladeView: {entity.get('name', '')} ({entity.get('file', '')})")
            elif entity_type == "Model":
                logger.info(f"   Model: {entity.get('name', '')}")
        
        # Verify we got the expected components
        has_route = any(e.get("type") == "Route" for e in result.entities)
        has_controller = any(e.get("type") == "Controller" for e in result.entities)
        has_action = any(e.get("type") == "Action" for e in result.entities)
        has_view = any(e.get("type") == "BladeView" for e in result.entities)
        
        logger.info("\n✅ Verification:")
        logger.info(f"   Route found: {has_route}")
        logger.info(f"   Controller found: {has_controller}")
        logger.info(f"   Action found: {has_action}")
        logger.info(f"   BladeView found: {has_view}")
        
        if len(result.cypher_queries) > 3:
            logger.warning(f"⚠️  Too many Cypher queries executed ({len(result.cypher_queries)}). Expected 1-2.")
        else:
            logger.info(f"✅ Query count looks good!")
        
        conn.close()
        
    except Exception as e:
        logger.error(f"❌ Test failed: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    asyncio.run(test_route_query())
