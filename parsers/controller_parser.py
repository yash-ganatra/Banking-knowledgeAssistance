
"""
Controller Parser

Parses PHP Controller files to extract:
- Actions (public function methods)
- View calls (view('name'))
- Model usage (Model::find, etc.)
- Function calls (this->method())

Uses regex for extraction due to missing PHP parser environment.
"""

import re
import logging
from pathlib import Path
from typing import List, Dict, Any

logger = logging.getLogger(__name__)

class ControllerParser:
    def __init__(self, project_path: str):
        self.project_path = Path(project_path)
        self.controllers_path = self.project_path / "app/Http/Controllers"

    def parse(self) -> List[Dict[str, Any]]:
        """
        Parse all controller files and return list of controller definitions.
        """
        controllers = []
        
        # Find all PHP files in Controllers directory (recursive)
        if not self.controllers_path.exists():
            logger.warning(f"Controllers directory not found: {self.controllers_path}")
            return []
            
        files = list(self.controllers_path.rglob("*.php"))
        
        for file_path in files:
            logger.info(f"Parsing controller: {file_path.name}")
            controller_data = self._parse_file(file_path)
            if controller_data:
                controllers.append(controller_data)
                
        return controllers

    def _parse_file(self, file_path: Path) -> Dict[str, Any]:
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()

        # Extract Class Name
        class_match = re.search(r"class\s+(\w+)\s+extends", content)
        if not class_match:
            return None
            
        class_name = class_match.group(1)
        
        # Extract Methods
        methods = []
        # Pattern for public function method()
        method_pattern = re.compile(r"public\s+function\s+(\w+)\s*\(([^)]*)\)")
        
        for match in method_pattern.finditer(content):
            method_name = match.group(1)
            params = match.group(2)
            start_pos = match.end()
            
            # Find method body (naive approach: match braces)
            body = self._extract_method_body(content, start_pos)
            
            # extract relationships from body
            views = self._extract_views(body)
            models = self._extract_models(body)
            tables = self._extract_tables(body)
            
            methods.append({
                "name": method_name,
                "params": params,
                "views": views,
                "models": models,
                "tables": tables,
                "line": content[:match.start()].count('\n') + 1
            })
            
        return {
            "name": class_name,
            "file": str(file_path.relative_to(self.project_path)),
            "methods": methods
        }

    def _extract_method_body(self, content: str, start_pos: int) -> str:
        """
        Extract the body of a method starting from the opening brace.
        Handles nested braces reasonably well.
        """
        # Find first opening brace
        brace_start = content.find("{", start_pos)
        if brace_start == -1:
            return ""
            
        balance = 1
        pos = brace_start + 1
        length = len(content)
        
        while pos < length and balance > 0:
            char = content[pos]
            if char == "{":
                balance += 1
            elif char == "}":
                balance -= 1
            pos += 1
            
        return content[brace_start:pos]

    def _extract_views(self, body: str) -> List[str]:
        # view('view.name') or View::make('view.name')
        views = set()
        
        # view('name')
        matches = re.findall(r"view\s*\(\s*['\"]([^'\"]+)['\"]", body)
        views.update(matches)
        
        # View::make('name')
        matches = re.findall(r"View::make\s*\(\s*['\"]([^'\"]+)['\"]", body)
        views.update(matches)
        
        return list(views)

    def _extract_models(self, body: str) -> List[str]:
        # Model::method()
        # Assumes models start with uppercase and are called statically
        matches = re.findall(r"([A-Z][a-zA-Z0-9_]+)::[a-z]", body)
        
        # Filter out common non-models
        ignored = {"DB", "Log", "Route", "View", "Schema", "Auth", "Session", "Redirect", "Request", "Response", "Config", "Validator"}
        
        return list(set([m for m in matches if m not in ignored]))

    def _extract_tables(self, body: str) -> List[Dict[str, str]]:
        # Detect DB::table('name')
        # Returns list of dicts: {'name': 'users', 'access': 'read'|'write'}
        
        tables = []
        # Find all DB::table occurrences
        matches = re.finditer(r"DB::table\s*\(\s*['\"]([^'\"]+)['\"]", body)
        
        for match in matches:
            table_name = match.group(1)
            access = "read" # Default
            
            # Simple heuristic for access type based on nearby keywords in the body
            # (Note: This is rough; usually we'd check the method chain on this specific match)
            if re.search(r"->(insert|update|delete|truncate)\(", body[match.end():match.end()+200]): 
                 access = "write"
            
            tables.append({"name": table_name, "access": access})
            
        return tables

if __name__ == "__main__":
    import sys
    import json
    logging.basicConfig(level=logging.INFO)
    
    path = "."
    if len(sys.argv) > 1:
        path = sys.argv[1]
        
    parser = ControllerParser(path)
    print(json.dumps(parser.parse(), indent=2))
