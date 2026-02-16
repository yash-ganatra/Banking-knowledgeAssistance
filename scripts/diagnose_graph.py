#!/usr/bin/env python3
"""
Graph Database Diagnostic Tool

Checks the current state of the Neo4j graph database and provides
actionable recommendations.

Usage:
    python3 scripts/diagnose_graph.py
"""

import sys
import os
from pathlib import Path

# Add project root to path
project_root = Path(__file__).resolve().parent.parent
sys.path.append(str(project_root))

# Load .env manually (same pattern as build_graph.py)
env_path = project_root / ".env"
if env_path.exists():
    with open(env_path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith("#") and "=" in line:
                key, value = line.split("=", 1)
                if not os.environ.get(key):
                    os.environ[key] = value.strip('"\'')

# Set default credentials if not set
if "NEO4J_URI" not in os.environ:
    os.environ["NEO4J_URI"] = "bolt://localhost:7687"
if "NEO4J_USER" not in os.environ:
    os.environ["NEO4J_USER"] = "neo4j"
if "NEO4J_PASSWORD" not in os.environ:
    print("⚠️  NEO4J_PASSWORD not set. Please set it via environment or .env file")
    sys.exit(1)

from utils.graph_db import Neo4jConnection

def check_connection():
    """Test Neo4j connection"""
    print("\n🔌 Testing Neo4j Connection...")
    try:
        conn = Neo4jConnection.from_env()
        print("✅ Connected to Neo4j successfully")
        return conn
    except Exception as e:
        print(f"❌ Failed to connect to Neo4j: {e}")
        print("\n📝 Troubleshooting:")
        print("   1. Check if Neo4j is running: neo4j status")
        print("   2. Verify credentials in .env file")
        print("   3. Check NEO4J_URI, NEO4J_USER, NEO4J_PASSWORD environment variables")
        return None

def check_node_counts(conn):
    """Check how many nodes of each type exist"""
    print("\n📊 Node Counts:")
    
    node_types = ["Route", "Controller", "HelperClass", "Action", "Model", "BladeView", "DBTable", "UIElement", "JSFunction"]
    
    total_nodes = 0
    results = {}
    
    with conn.session() as session:
        for node_type in node_types:
            query = f"MATCH (n:{node_type}) RETURN count(n) as count"
            result = session.run(query)
            count = result.single()["count"]
            results[node_type] = count
            total_nodes += count
            
            status = "✅" if count > 0 else "❌"
            print(f"   {status} {node_type}: {count}")
    
    print(f"\n   📈 Total Nodes: {total_nodes}")
    return results, total_nodes

def check_relationships(conn):
    """Check relationship counts"""
    print("\n🔗 Relationship Counts:")
    
    rel_types = [
        "ROUTE_CALLS_ACTION",
        "HAS_ACTION", 
        "ACTION_LOADS_VIEW",
        "ACTION_USES_MODEL",
        "ACTION_READS_TABLE",
        "ACTION_WRITES_TABLE",
        "ACTION_CALLS_ACTION",
        "VIEW_CONTAINS_ELEMENT",
        "UI_POSTS_TO_ACTION",
        "VIEW_INCLUDES_JS"
    ]
    
    total_rels = 0
    results = {}
    
    with conn.session() as session:
        for rel_type in rel_types:
            query = f"MATCH ()-[r:{rel_type}]->() RETURN count(r) as count"
            result = session.run(query)
            count = result.single()["count"]
            results[rel_type] = count
            total_rels += count
            
            status = "✅" if count > 0 else "❌"
            print(f"   {status} {rel_type}: {count}")
    
    print(f"\n   📈 Total Relationships: {total_rels}")
    return results, total_rels

def check_specific_route(conn, route_uri="/userhelp"):
    """Check if a specific route exists and its connections"""
    print(f"\n🔍 Checking Route: {route_uri}")
    
    with conn.session() as session:
        # Check route existence
        query = f"MATCH (r:Route {{uri: $uri}}) RETURN r"
        result = session.run(query, uri=route_uri)
        route = result.single()
        
        if not route:
            print(f"   ❌ Route '{route_uri}' NOT FOUND in graph")
            return False
        
        print(f"   ✅ Route '{route_uri}' EXISTS")
        route_node = dict(route["r"])
        print(f"      Method: {route_node.get('method', 'N/A')}")
        print(f"      File: {route_node.get('file', 'N/A')}")
        
        # Check connected action
        query = """
        MATCH (r:Route {uri: $uri})-[:ROUTE_CALLS_ACTION]->(a:Action)
        OPTIONAL MATCH (a)<-[:HAS_ACTION]-(c:Controller)
        OPTIONAL MATCH (a)-[:ACTION_LOADS_VIEW]->(v:BladeView)
        RETURN a, c, v
        """
        result = session.run(query, uri=route_uri)
        record = result.single()
        
        if record:
            if record["a"]:
                action = dict(record["a"])
                print(f"   ✅ Connected to Action: {action.get('name', 'N/A')}")
            if record["c"]:
                controller = dict(record["c"])
                print(f"   ✅ Connected to Controller: {controller.get('name', 'N/A')}")
            if record["v"]:
                view = dict(record["v"])
                print(f"   ✅ Connected to View: {view.get('name', 'N/A')}")
        else:
            print(f"   ⚠️  Route exists but has NO CONNECTIONS")
        
        return True

def provide_recommendations(node_counts, rel_counts, total_nodes, total_rels):
    """Provide actionable recommendations"""
    print("\n\n═══════════════════════════════════════")
    print("📋 RECOMMENDATIONS")
    print("═══════════════════════════════════════\n")
    
    if total_nodes == 0:
        print("🚨 CRITICAL: Graph database is EMPTY")
        print("\n✅ ACTION REQUIRED:")
        print("   Run the graph build script to populate the database:")
        print("   ")
        print("   python3 scripts/build_graph.py")
        print("   ")
        print("   This will:")
        print("   • Parse all routes, controllers, views, and helpers")
        print("   • Create ~500-1000 nodes")
        print("   • Create ~1000-2000 relationships")
        print("   • Take approximately 10-30 seconds")
        return
    
    if total_nodes < 100:
        print("⚠️  WARNING: Graph has very few nodes")
        print(f"   Current: {total_nodes} nodes, Expected: 500-1000+")
        print("\n✅ RECOMMENDED ACTION:")
        print("   Re-run the build script to ensure complete ingestion:")
        print("   ")
        print("   python3 scripts/build_graph.py")
    
    # Check for missing relationships
    if rel_counts.get("ROUTE_CALLS_ACTION", 0) == 0:
        print("\n⚠️  WARNING: No ROUTE_CALLS_ACTION relationships")
        print("   Routes are not connected to actions")
        print("   Queries like 'trace flow for route X' will fail")
    
    if rel_counts.get("ACTION_LOADS_VIEW", 0) == 0:
        print("\n⚠️  WARNING: No ACTION_LOADS_VIEW relationships")
        print("   Actions are not connected to Blade views")
        print("   Cannot trace route → controller → view flow")
    
    # Check for missing features
    if node_counts.get("UIElement", 0) == 0:
        print("\n💡 ENHANCEMENT OPPORTUNITY:")
        print("   No UI elements ingested")
        print("   Consider implementing UI element parser to enable:")
        print("   • Form → action tracing")
        print("   • Button → endpoint mapping")
        print("   • Input field validation rules")
    
    if node_counts.get("JSFunction", 0) == 0:
        print("\n💡 ENHANCEMENT OPPORTUNITY:")
        print("   No JavaScript functions ingested")
        print("   Consider implementing JS parser to enable:")
        print("   • AJAX call tracing")
        print("   • Frontend → backend flow analysis")
        print("   • Event handler mapping")
    
    if total_nodes > 0 and total_rels > 0:
        print("\n✅ Graph database looks healthy!")
        print(f"   • {total_nodes} nodes ingested")
        print(f"   • {total_rels} relationships created")
        print("\n   You can now query the graph for:")
        print("   • Route → Controller → Action flows")
        print("   • Action → Model → Table mappings")
        print("   • Controller → View relationships")
        print("   • Function call graphs")

def main():
    print("╔═══════════════════════════════════════╗")
    print("║  Graph Database Diagnostic Tool      ║")
    print("╚═══════════════════════════════════════╝")
    
    # Test connection
    conn = check_connection()
    if not conn:
        return 1
    
    # Check node counts
    node_counts, total_nodes = check_node_counts(conn)
    
    # Check relationships
    rel_counts, total_rels = check_relationships(conn)
    
    # Check specific route
    check_specific_route(conn, "/userhelp")
    
    # Provide recommendations
    provide_recommendations(node_counts, rel_counts, total_nodes, total_rels)
    
    conn.close()
    
    print("\n✅ Diagnostic complete\n")
    return 0

if __name__ == "__main__":
    sys.exit(main())
