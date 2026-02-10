
import os
import sys
from pathlib import Path

# Add project root to path (parent of scripts/)
project_root = Path(__file__).resolve().parent.parent
sys.path.append(str(project_root))

from dotenv import load_dotenv
from utils.graph_db import Neo4jConnection

# Load .env
load_dotenv(project_root / ".env")

def check():
    conn = Neo4jConnection.from_env()
    with conn.session() as session:
        # Check HomeController connections
        result = session.run("""
        MATCH (c:Controller {name: 'HomeController'})-[:HAS_ACTION]->(a)
        OPTIONAL MATCH (a)-[:ACTION_READS_TABLE]->(t)
        RETURN a.name, t.name LIMIT 5
        """)
        print("HomeController Data:")
        for r in result:
            print(f"Action: {r['a.name']} -> Table: {r['t.name']}")
            
        # Check Route connections
        result = session.run("MATCH (r:Route)-[:ROUTE_CALLS_ACTION]->(a) RETURN r.uri, a.name LIMIT 5")
        print("\nRoute Data:")
        for r in result:
            print(f"Route: {r['r.uri']} -> Action: {r['a.name']}")

if __name__ == "__main__":
    check()
