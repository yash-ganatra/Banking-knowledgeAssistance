
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
    from parsers.ui_element_parser import UIElementParser
    from parsers.js_parser import JSParser
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
    ui_parser = UIElementParser(laravel_root)
    js_parser = JSParser(laravel_root)
    
    routes = route_parser.parse()
    controllers = controller_parser.parse()
    views = blade_parser.parse()
    helpers = helper_parser.parse()
    ui_data = ui_parser.parse()
    js_data = js_parser.parse()
    
    # Get View->JS includes
    views_path = laravel_root / "resources/views"
    view_js_includes = js_parser.parse_blade_js_includes(views_path)
    
    logger.info(f"Parsed {len(routes)} routes, {len(controllers)} controllers, {len(views)} views, {len(helpers)} helpers")
    logger.info(f"Parsed {len(ui_data['elements'])} UI elements, {len(js_data['functions'])} JS functions")
    
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
                "visibility": m.get("visibility", "public"),
                "is_static": m.get("is_static", False),
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
    
    # Process UI Elements
    ui_element_nodes = []
    for el in ui_data["elements"]:
        # Element already has 'id' in format view_name:type:element_id
        ui_element_nodes.append({
            "id": el["id"],
            "type": el["type"],
            "name": el.get("name", ""),
            "view_name": el.get("view_name", ""),
            "html_id": el.get("html_id", ""),
            "action": el.get("action", ""),
            "method": el.get("method", ""),
            "text": el.get("text", "")[:100] if el.get("text") else "",  # Truncate long text
            "target_route": el.get("target_route", "")
        })
    logger.info(f"Prepared {len(ui_element_nodes)} UI element nodes")
    
    # Process JS Functions
    js_function_nodes = []
    for fn in js_data["functions"]:
        # Use the ID from the parser (format: file_basename:function_name)
        js_function_nodes.append({
            "id": fn["id"],  # e.g., "financial_details:checkFinancialFields"
            "name": fn["name"],
            "file": fn["file"],
            "type": fn["type"],
            "params": fn.get("params", ""),
            "line": fn.get("line", 0)
        })
    logger.info(f"Prepared {len(js_function_nodes)} JS function nodes")
        
    # 4. Load Nodes
    logger.info("Loading Nodes into Neo4j...")
    loader.load_nodes_batch(route_nodes, "Route")
    loader.load_nodes_batch(controller_nodes, "Controller")
    loader.load_nodes_batch(helper_class_nodes, "HelperClass")
    loader.load_nodes_batch(action_nodes, "Action")
    loader.load_nodes_batch(view_nodes, "BladeView")
    loader.load_nodes_batch(model_nodes, "Model")
    loader.load_nodes_batch(table_nodes, "DBTable")
    loader.load_nodes_batch(ui_element_nodes, "UIElement")
    loader.load_nodes_batch(js_function_nodes, "JSFunction")
    
    # 5. Create MODEL_MAPS_TO_TABLE relationships
    # Strategy A: Case-insensitive name matching (e.g., Model "User" -> table "USERS")
    # Strategy B: Co-occurrence: If an Action uses exactly 1 Model AND 1-2 tables, link them
    logger.info("Creating MODEL_MAPS_TO_TABLE relationships...")
    
    known_table_ids = {t["id"] for t in table_nodes}
    rels_model_table = set()
    
    # Strategy A: Direct name matching (case-insensitive)
    for model in model_nodes:
        model_name = model["name"]
        model_lower = model_name.lower()
        for table_id in known_table_ids:
            table_lower = table_id.lower()
            if table_lower == model_lower or table_lower == model_lower + 's' or table_lower == model_lower + 'es':
                rels_model_table.add((model_name, table_id))
    
    # Strategy B: Co-occurrence inference from controllers
    for c in controllers:
        for m_data in c["methods"]:
            models_used = set(m_data.get("models", []))
            tables_used = {t["name"] for t in m_data.get("tables", []) if t["name"] in known_table_ids}
            if len(models_used) == 1 and 0 < len(tables_used) <= 2:
                for table_name in tables_used:
                    rels_model_table.add((list(models_used)[0], table_name))
    
    # Strategy B: Co-occurrence inference from helpers
    for h in helpers:
        for m_data in h["methods"]:
            models_used = set(m_data.get("models", []))
            tables_used = {t["name"] for t in m_data.get("tables", []) if t["name"] in known_table_ids}
            if len(models_used) == 1 and 0 < len(tables_used) <= 2:
                for table_name in tables_used:
                    rels_model_table.add((list(models_used)[0], table_name))
    
    rels_model_table_list = list(rels_model_table)
    loader.load_relationships_batch(rels_model_table_list, "MODEL_MAPS_TO_TABLE", "Model", "DBTable")
    logger.info(f"Created {len(rels_model_table_list)} MODEL_MAPS_TO_TABLE relationships")
    
    # 6. Prepare and Load Relationships
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
    
    # ACTION_CALLS_ACTION (inter-function calls)
    # Handles both static calls (ClassName::method) and instance calls ($this->method)
    rels_action_calls_action = []
    
    # From controllers
    for c in controllers:
        c_id = c["name"]
        for m in c["methods"]:
            caller_id = f"{c_id}@{m['name']}"
            for call in m.get("function_calls", []):
                if call["class"] == "self":
                    # $this->method() call - resolve to same controller
                    target_id = f"{c_id}@{call['method']}"
                else:
                    target_id = f"{call['class']}@{call['method']}"
                rels_action_calls_action.append((caller_id, target_id))
    
    # From helpers
    for h in helpers:
        h_id = h["name"]
        for m in h["methods"]:
            caller_id = f"{h_id}@{m['name']}"
            for call in m.get("function_calls", []):
                if call["class"] == "self":
                    # $this->method() call - resolve to same helper class
                    target_id = f"{h_id}@{call['method']}"
                else:
                    target_id = f"{call['class']}@{call['method']}"
                rels_action_calls_action.append((caller_id, target_id))
    
    loader.load_relationships_batch(rels_action_calls_action, "ACTION_CALLS_ACTION", "Action", "Action")
    logger.info(f"Created {len(rels_action_calls_action)} ACTION_CALLS_ACTION relationships")
    
    # VIEW_CONTAINS_ELEMENT (BladeView -> UIElement)
    # ui_data["view_contains_element"] is a list of tuples (view_name, element_id)
    rels_view_element = ui_data["view_contains_element"]  # Already in (view_id, element_id) format
    
    loader.load_relationships_batch(rels_view_element, "VIEW_CONTAINS_ELEMENT", "BladeView", "UIElement")
    logger.info(f"Created {len(rels_view_element)} VIEW_CONTAINS_ELEMENT relationships")
    
    # UI_POSTS_TO_ACTION (UIElement -> Action)
    # ui_data["ui_posts_to_action"] is a list of tuples (element_id, target_route)
    rels_ui_action = []
    for (el_id, target) in ui_data["ui_posts_to_action"]:
        # The target is typically a route name (e.g., 'password.reset')
        # Try to find matching action from routes
        action_found = False
        for r in routes:
            # Check if route name matches
            route_name = r.get("name", "")
            if target == route_name or target == f"/{r['uri']}" or target == r["uri"]:
                action_id = f"{r['controller']}@{r['action']}"
                rels_ui_action.append((el_id, action_id))
                action_found = True
                break
        
        # If not found in routes, check if it's already in Controller@action format
        if not action_found and "@" in target:
            rels_ui_action.append((el_id, target))
    
    loader.load_relationships_batch(rels_ui_action, "UI_POSTS_TO_ACTION", "UIElement", "Action")
    logger.info(f"Created {len(rels_ui_action)} UI_POSTS_TO_ACTION relationships")
    
    # VIEW_INCLUDES_JS (BladeView -> JSFunction)
    # view_js_includes is a list of (view_name, js_base_name) tuples
    # We need to match by js_base_name (e.g., 'bank' matches functions from 'bank.js')
    rels_view_js = []
    
    # Create a lookup: js_base_name -> list of function ids
    js_file_to_functions = {}
    for fn in js_data["functions"]:
        # fn["file"] is like "public/js/bank.js" or just the relative path
        # Extract base name from it
        file_str = str(fn["file"])
        if "/" in file_str:
            js_base = file_str.split("/")[-1].replace(".js", "")
        else:
            js_base = file_str.replace(".js", "")
        
        if js_base not in js_file_to_functions:
            js_file_to_functions[js_base] = []
        js_file_to_functions[js_base].append(fn["id"])
    
    # Build relationships: view -> all functions from included JS file
    for (view_name, js_base_name) in view_js_includes:
        if js_base_name in js_file_to_functions:
            for fn_id in js_file_to_functions[js_base_name]:
                rels_view_js.append((view_name, fn_id))
    
    loader.load_relationships_batch(rels_view_js, "VIEW_INCLUDES_JS", "BladeView", "JSFunction")
    logger.info(f"Created {len(rels_view_js)} VIEW_INCLUDES_JS relationships")
    
    logger.info(f"Build complete in {time.time() - start_time:.2f} seconds")
    conn.close()

if __name__ == "__main__":
    main()
