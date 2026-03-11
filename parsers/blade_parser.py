
"""
Blade Parser

Parses Blade template files to extract:
- Route usage (route('name'))
- View inclusion (@include('name'))
- Layout extension (@extends('name'))
- Inline JavaScript functions defined inside <script> blocks
"""

import re
import logging
from pathlib import Path
from typing import List, Dict, Any

logger = logging.getLogger(__name__)

class BladeParser:
    def __init__(self, project_path: str):
        self.project_path = Path(project_path)
        self.views_path = self.project_path / "resources/views"

    def parse(self) -> List[Dict[str, Any]]:
        """
        Parse all blade files and return list of view definitions.
        """
        views = []
        
        if not self.views_path.exists():
            logger.warning(f"Views directory not found: {self.views_path}")
            return []
            
        files = list(self.views_path.rglob("*.blade.php"))
        
        for file_path in files:
            view_data = self._parse_file(file_path)
            if view_data:
                views.append(view_data)
                
        return views

    def parse_single_file(self, file_path: str) -> Dict[str, Any]:
        """
        Parse a single blade file and return its view definition.
        Used by the ingestion pipeline for incremental updates.
        
        Args:
            file_path: Absolute path to a .blade.php file
        
        Returns:
            Dict with view data, or empty dict if parsing fails
        """
        file_path = Path(file_path)
        if not file_path.exists():
            logger.warning(f"File not found: {file_path}")
            return {}
        result = self._parse_file(file_path)
        return result if result else {}

    def _extract_inline_js_functions(self, content: str) -> List[Dict[str, Any]]:
        """
        Extract named JavaScript functions from <script> blocks in a blade file.

        Returns a list of dicts:
            [{"name": "functionName", "params": ["arg1", "arg2"], "line": <int>}, ...]
        """
        found = []
        func_pattern = re.compile(r"function\s+([a-zA-Z_]\w*)\s*\(([^)]*)\)")
        script_pattern = re.compile(
            r"<script[^>]*>(.*?)</script>", re.DOTALL | re.IGNORECASE
        )

        seen: set = set()
        for script_match in script_pattern.finditer(content):
            script_body = script_match.group(1)
            script_body_start = script_match.start(1)

            for func_match in func_pattern.finditer(script_body):
                func_name = func_match.group(1)
                if func_name in seen:
                    continue
                seen.add(func_name)

                params_raw = func_match.group(2).strip()
                param_list = [p.strip() for p in params_raw.split(",") if p.strip()]

                abs_pos = script_body_start + func_match.start()
                line_num = content[:abs_pos].count("\n") + 1

                found.append({"name": func_name, "params": param_list, "line": line_num})

        return found

    def _parse_file(self, file_path: Path) -> Dict[str, Any]:
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()

        # Derive view name from file path (e.g. resources/views/auth/login.blade.php -> auth.login)
        relative_path = file_path.relative_to(self.views_path)
        view_name = str(relative_path).replace(".blade.php", "").replace("/", ".")
        
        # Extract Route usages
        # {{ route('name') }}
        routes = re.findall(r"route\s*\(\s*['\"]([^'\"]+)['\"]", content)
        
        # Extract Includes
        # @include('name')
        includes = re.findall(r"@include\s*\(\s*['\"]([^'\"]+)['\"]", content)
        
        # Extract Extends
        # @extends('name')
        extends = re.findall(r"@extends\s*\(\s*['\"]([^'\"]+)['\"]", content)

        # Extract inline JS functions from <script> blocks
        inline_js_functions = self._extract_inline_js_functions(content)

        return {
            "name": view_name,
            "file": str(file_path.relative_to(self.project_path)),
            "routes": list(set(routes)),
            "includes": list(set(includes)),
            "extends": list(set(extends)),
            "inline_js_functions": inline_js_functions,
        }

if __name__ == "__main__":
    import sys
    import json
    logging.basicConfig(level=logging.INFO)
    
    path = "."
    if len(sys.argv) > 1:
        path = sys.argv[1]
        
    parser = BladeParser(path)
    print(json.dumps(parser.parse(), indent=2))
