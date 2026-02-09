"""
Neo4j Setup Script

Initializes the Neo4j database schema, creates constraints and indexes,
and verifies the connection.

Usage:
    python scripts/setup_neo4j.py
"""

import os
import sys
import logging
from pathlib import Path

# Add project root to path
project_root = Path(__file__).resolve().parent.parent
sys.path.append(str(project_root))

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)
logger = logging.getLogger("setup_neo4j")

try:
    from utils.graph_db import init_graph_schema, get_graph_connection
except ImportError:
    print("Error: Could not import graph_db utils.")
    print("Make sure you are running this from the project root or scripts directory.")
    sys.exit(1)


def main():
    print("="*60)
    print("🕸️  Neo4j Graph Database Setup")
    print("="*60)
    
    # 1. Load environment variables from .env
    env_path = project_root / ".env"
    if env_path.exists():
        print(f"Loading configuration from {env_path}")
        with open(env_path, "r") as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    key, value = line.split("=", 1)
                    if not os.environ.get(key):  # Don't overwrite existing
                        os.environ[key] = value.strip('"\'')
    
    # 2. Check environment variables
    uri = os.getenv("NEO4J_URI", "bolt://localhost:7687")
    user = os.getenv("NEO4J_USER", "neo4j")
    password = os.getenv("NEO4J_PASSWORD")
    
    print(f"Configuration:")
    print(f"  URI: {uri}")
    print(f"  User: {user}")
    
    if not password:
        print("\n❌ Error: NEO4J_PASSWORD environment variable is missing.")
        print("Please set it in your .env file or environment.")
        print("Example: export NEO4J_PASSWORD=secret_password")
        sys.exit(1)
    
    # 2. Test Connection
    print("\nTesting connection...")
    try:
        conn = get_graph_connection()
        if conn.is_connected():
            print("✅ Connection successful!")
        else:
            print("❌ Connection failed.")
            sys.exit(1)
    except Exception as e:
        print(f"❌ Connection error: {e}")
        print("\nMake sure Neo4j is running. You can start it with Docker:")
        print("docker run -d --name neo4j -p 7474:7474 -p 7687:7687 -e NEO4J_AUTH=neo4j/your_password neo4j:5")
        sys.exit(1)
        
    # 3. Initialize Schema
    print("\nInitializing schema (constraints and indexes)...")
    try:
        results = init_graph_schema(conn)
        
        success_count = sum(results.values())
        print(f"\nSchema Execution Results:")
        for query_name, success in results.items():
            status = "✅" if success else "❌"
            print(f"  {status} {query_name}")
            
        if success_count == len(results):
            print(f"\n✅ Schema initialization complete! ({success_count}/{len(results)} successful)")
        else:
            print(f"\n⚠️ Schema initialization finished with warnings ({success_count}/{len(results)} successful)")
            
    except Exception as e:
        print(f"❌ Schema initialization failed: {e}")
        sys.exit(1)
    finally:
        conn.close()

    print("\n" + "="*60)
    print("🎉 Setup Complete")
    print("="*60)


if __name__ == "__main__":
    main()
