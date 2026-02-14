
"""
Build Graph Script

Orchestrates the parsing of code artifacts and loading them into Neo4j.
1. Parses Routes, Controllers, and Views.
2. Creates Nodes for each entity.
3. Creates Relationships between entities.
4. Loads everything into Neo4j.

Usage:
    python scripts/build_graph.py
"""

import sys
import logging
import time
from pathlib import Path
from typing import Dict, List, Any, Set, Tuple

# Add project root to path
project_root = Path(__file__).resolve().parent.parent
sys.path.append(str(project_root))

from utils.graph_db import Neo4jConnection, GraphLoader, init_graph_schema
try:
    from parsers.route_parser import RouteParser
    from parsers.controller_parser import ControllerParser
    from parsers.blade_parser import BladeParser
    from parsers.helper_parser import HelperParser
except ImportError as e:
    print(f"Error importing parsers: {e}")
    sys.exit(1)

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)
logger = logging.getLogger("build_graph")

def main():
    logger.info("Starting Graph Build Process...")
    start_time = time.time()
    
    # Load .env manually
    env_path = project_root / ".env"
    if env_path.exists():
        import os
        logger.info(f"Loading configuration from {env_path}")
        with open(env_path, "r") as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    key, value = line.split("=", 1)
                    if not os.environ.get(key):
                        os.environ[key] = value.strip('"\'')
    
    # 1. Initialize Connection and Loader
    try:
        conn = Neo4jConnection.from_env()
        loader = GraphLoader(conn)
        
        # Initialize schema
        init_graph_schema(conn)
        
        # Clear existing graph (Optional: make this configurable)
        # loader.clear_graph() 
        # For now, we use MERGE, so it updates existing.
        
    except Exception as e:
        logger.error(f"Failed to connect to Neo4j: {e}")
        return

    # 2. Parse Codebase
    logger.info("Parsing codebase...")
    
    # Laravel app is in code/code directory
    laravel_root = project_root / "code/code"
    
    route_parser = RouteParser(laravel_root)
    controller_parser = ControllerParser(laravel_root)
    blade_parser = BladeParser(laravel_root)
    helper_parser = HelperParser(laravel_root)
    
    routes = route_parser.parse()
    controllers = controller_parser.parse()
    views = blade_parser.parse()
    helpers = helper_parser.parse()
    
    logger.info(f"Parsed {len(routes)} routes, {len(controllers)} controllers, {len(views)} views, {len(helpers)} helpers")
    
    # 3. Prepare Nodes
    # We need to assign IDs to everything to create relationships later.
    # Route ID: method:uri
    # Controller ID: class_name
    # Action ID: class_name@method_name
    # View ID: view.name
    
    route_nodes = []
    controller_nodes = []
    helper_class_nodes = []
    action_nodes = []
    view_nodes = []
    model_nodes = [] # Extracted from controllers and helpers
    table_nodes = []
    
    # Track unique IDs to avoid duplicates in list
    seen_models = set()
    
    # Process Routes
    for r in routes:
        r_id = f"{r['method']}:{r['uri']}"
        route_nodes.append({
            "id": r_id,
            "method": r["method"],
            "uri": r["uri"],
            "file": r["file"]
        })
        
    # Process Controllers and Actions
    for c in controllers:
        c_id = c["name"]
        controller_nodes.append({
            "id": c_id,
            "name": c["name"],
            "file": c["file"]
        })
        
        for m in c["methods"]:
            a_id = f"{c_id}@{m['name']}"
            action_nodes.append({
                "id": a_id,
                "name": m["name"],
                "controller_id": c_id,
                "params": m["params"],
                "start_line": m["line"]
            })
            
            # Extract Models
            for model_name in m["models"]:
                if model_name not in seen_models:
                    model_nodes.append({
                        "id": model_name,
                        "name": model_name
                    })
                    seen_models.add(model_name)

            # Extract Tables
            if "tables" in m:
                for table in m["tables"]:
                    table_nodes.append({
                        "id": table["name"],
                        "name": table["name"]
                    })

    # Process Helpers
    for h in helpers:
        h_id = h["name"]
        helper_class_nodes.append({
            "id": h_id,
            "name": h["name"],
            "file": h["file"],
            "parent_class": h.get("parent_class", ""),
            "type": "helper"
        })
        
        for m in h["methods"]:
            a_id = f"{h_id}@{m['name']}"
            action_nodes.append({
                "id": a_id,
                "name": m["name"],
                "controller_id": h_id,
                "params": m["params"],
                "visibility": m.get("visibility", "public"),
                "is_static": m.get("is_static", False),
                "start_line": m["line"]
            })
            
            # Extract Models from helpers
            for model_name in m.get("models", []):
                if model_name not in seen_models:
                    model_nodes.append({
                        "id": model_name,
                        "name": model_name
                    })
                    seen_models.add(model_name)
            
            # Extract Tables from helpers
            if "tables" in m:
                for table in m["tables"]:
                    table_nodes.append({
                        "id": table["name"],
                        "name": table["name"]
                    })

    # Process Views
    for v in views:
        view_nodes.append({
            "id": v["name"],
            "name": v["name"],
            "file": v["file"]
        })
        
    # 4. Load Nodes
    logger.info("Loading Nodes into Neo4j...")
    loader.load_nodes_batch(route_nodes, "Route")
    loader.load_nodes_batch(controller_nodes, "Controller")
    loader.load_nodes_batch(helper_class_nodes, "HelperClass")
    loader.load_nodes_batch(action_nodes, "Action")
    loader.load_nodes_batch(view_nodes, "BladeView")
    loader.load_nodes_batch(model_nodes, "Model")
    loader.load_nodes_batch(table_nodes, "DBTable")
    
    # 5. Prepare and Load Relationships
    logger.info("Loading Relationships...")
    
    # ROUTE_CALLS_ACTION
    rels_route_action = []
    for r in routes:
        r_id = f"{r['method']}:{r['uri']}"
        # Controller@Action or [Controller, Action]
        if "@" in r["action"]:
            # Format: Name@action
            target_action = r["action"] # This might be just 'action' if controller is separate
            # Wait, parser output: controller=Name, action=Method
            a_id = f"{r['controller']}@{r['action']}"
            rels_route_action.append((r_id, a_id))
        else:
             a_id = f"{r['controller']}@{r['action']}"
             rels_route_action.append((r_id, a_id))
             
    loader.load_relationships_batch(rels_route_action, "ROUTE_CALLS_ACTION", "Route", "Action")
    
    # HAS_ACTION (Controller -> Action)
    rels_has_action = []
    for c in controllers:
        c_id = c["name"]
        for m in c["methods"]:
            a_id = f"{c_id}@{m['name']}"
            rels_has_action.append((c_id, a_id))
            
    loader.load_relationships_batch(rels_has_action, "HAS_ACTION", "Controller", "Action")
    
    # HAS_ACTION (HelperClass -> Action)
    rels_helper_action = []
    rels_helper_uses_model = []
    rels_helper_reads_table = []
    rels_helper_writes_table = []
    rels_helper_loads_view = []
    
    for h in helpers:
        h_id = h["name"]
        for m in h["methods"]:
            a_id = f"{h_id}@{m['name']}"
            rels_helper_action.append((h_id, a_id))
            
            for model in m.get("models", []):
                rels_helper_uses_model.append((a_id, model))
            
            for view in m.get("views", []):
                rels_helper_loads_view.append((a_id, view))
            
            if "tables" in m:
                for table in m["tables"]:
                    if table["access"] == "write":
                        rels_helper_writes_table.append((a_id, table["name"]))
                    else:
                        rels_helper_reads_table.append((a_id, table["name"]))
    
    loader.load_relationships_batch(rels_helper_action, "HAS_ACTION", "HelperClass", "Action")
    loader.load_relationships_batch(rels_helper_uses_model, "ACTION_USES_MODEL", "Action", "Model")
    loader.load_relationships_batch(rels_helper_loads_view, "ACTION_LOADS_VIEW", "Action", "BladeView")
    loader.load_relationships_batch(rels_helper_reads_table, "ACTION_READS_TABLE", "Action", "DBTable")
    loader.load_relationships_batch(rels_helper_writes_table, "ACTION_WRITES_TABLE", "Action", "DBTable")
    
    # ACTION_USES_MODEL & ACTION_LOADS_VIEW & TABLE Rels
    rels_uses_model = []
    rels_loads_view = []
    rels_reads_table = []
    rels_writes_table = []
    
    for c in controllers:
        c_id = c["name"]
        for m in c["methods"]:
            a_id = f"{c_id}@{m['name']}"
            
            for model in m["models"]:
                rels_uses_model.append((a_id, model))
            
            for view in m["views"]:
                rels_loads_view.append((a_id, view))

            if "tables" in m:
                for table in m["tables"]:
                    if table["access"] == "write":
                        rels_writes_table.append((a_id, table["name"]))
                    else:
                        rels_reads_table.append((a_id, table["name"]))
                
    loader.load_relationships_batch(rels_uses_model, "ACTION_USES_MODEL", "Action", "Model")
    loader.load_relationships_batch(rels_loads_view, "ACTION_LOADS_VIEW", "Action", "BladeView")
    loader.load_relationships_batch(rels_reads_table, "ACTION_READS_TABLE", "Action", "DBTable")
    loader.load_relationships_batch(rels_writes_table, "ACTION_WRITES_TABLE", "Action", "DBTable")
    
    # VIEW_INCLUDES_VIEW (from @include)
    rels_view_include = []
    for v in views:
        v_id = v["name"]
        for inc in v["includes"]:
            rels_view_include.append((v_id, inc))
            
    # Schema defines VIEW_INCLUDES_JS but not VIEW_INCLUDES_VIEW? 
    # Let's check schema. Doc mentions "BladeView" relationships?
    # utils/graph_db.py schema doesn't list VIEW_INCLUDES_VIEW. 
    # But it's useful. We can skip if not in schema or add it.
    # I'll check schema again. RELATIONSHIP_TYPES keys.
    # It has VIEW_CONTAINS_ELEMENT, VIEW_INCLUDES_JS.
    # We can skip Includes for now or add to schema. 
    # Let's skip to strictly follow schema/doc.
    
    # ACTION_CALLS_ACTION (inter-function calls like CommonFunctions::decrypt256())
    rels_action_calls_action = []
    
    # From controllers
    for c in controllers:
        c_id = c["name"]
        for m in c["methods"]:
            caller_id = f"{c_id}@{m['name']}"
            for call in m.get("function_calls", []):
                target_id = f"{call['class']}@{call['method']}"
                rels_action_calls_action.append((caller_id, target_id))
    
    # From helpers
    for h in helpers:
        h_id = h["name"]
        for m in h["methods"]:
            caller_id = f"{h_id}@{m['name']}"
            for call in m.get("function_calls", []):
                target_id = f"{call['class']}@{call['method']}"
                rels_action_calls_action.append((caller_id, target_id))
    
    loader.load_relationships_batch(rels_action_calls_action, "ACTION_CALLS_ACTION", "Action", "Action")
    logger.info(f"Created {len(rels_action_calls_action)} ACTION_CALLS_ACTION relationships")
    
    logger.info(f"Build complete in {time.time() - start_time:.2f} seconds")
    conn.close()

if __name__ == "__main__":
    main()
