
"""
Route Parser

Parses Laravel route files (web.php, api.php) to extract route definitions.
Uses regex to identify Route::get/post/etc. calls and extracts URI, Method, and Controller@Action.
"""

import re
import json
import logging
from pathlib import Path
from typing import List, Dict, Any

logger = logging.getLogger(__name__)

class RouteParser:
    def __init__(self, project_path: str):
        self.project_path = Path(project_path)
        self.routes_path = self.project_path / "routes"

    def parse(self) -> List[Dict[str, Any]]:
        """
        Parse all route files and return a list of route definitions.
        """
        routes = []
        
        # Files to parse
        route_files = ["web.php", "api.php"]
        
        for filename in route_files:
            file_path = self.routes_path / filename
            if not file_path.exists():
                logger.warning(f"Route file not found: {file_path}")
                continue
                
            logger.info(f"Parsing route file: {filename}")
            file_routes = self._parse_file(file_path)
            routes.extend(file_routes)
            
        return routes

    def _parse_file(self, file_path: Path) -> List[Dict[str, Any]]:
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()

        routes = []
        
        # Regex for standard Route::method('uri', [Controller::class, 'action'])
        # Also supports Route::method('uri', 'Controller@action')
        # And string based 'uri', 'Action' (less common in modern Laravel but possible)
        
        # Pattern 1: Route::get('uri', [Controller::class, 'method'])
        pattern_array = re.compile(
            r"Route::(get|post|put|patch|delete|any|match)\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*\[\s*([a-zA-Z0-9_]+)::class\s*,\s*['\"]([^'\"]+)['\"]\s*\]",
            re.IGNORECASE
        )
        
        # Pattern 2: Route::get('uri', 'Controller@method')
        pattern_string = re.compile(
            r"Route::(get|post|put|patch|delete|any|match)\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([a-zA-Z0-9_]+)@([a-zA-Z0-9_]+)['\"]",
            re.IGNORECASE
        )

        for match in pattern_array.finditer(content):
            method, uri, controller, action = match.groups()
            routes.append({
                "method": method.upper(),
                "uri": uri,
                "controller": controller,
                "action": action,
                "file": str(file_path.name),
                "line": content[:match.start()].count('\n') + 1
            })

        for match in pattern_string.finditer(content):
            method, uri, controller, action = match.groups()
            routes.append({
                "method": method.upper(),
                "uri": uri,
                "controller": controller,
                "action": action,
                "file": str(file_path.name),
                "line": content[:match.start()].count('\n') + 1
            })
            
        logger.info(f"Found {len(routes)} routes in {file_path.name}")
        return routes

if __name__ == "__main__":
    # Test run
    import sys
    logging.basicConfig(level=logging.INFO)
    
    if len(sys.argv) > 1:
        path = sys.argv[1]
    else:
        path = "."
        
    parser = RouteParser(path)
    print(json.dumps(parser.parse(), indent=2))
