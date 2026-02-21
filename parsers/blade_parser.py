
"""
Blade Parser

Parses Blade template files to extract:
- Route usage (route('name'))
- View inclusion (@include('name'))
- Layout extension (@extends('name'))
- JS integration (function calls in scripts)
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
        
        return {
            "name": view_name,
            "file": str(file_path.relative_to(self.project_path)),
            "routes": list(set(routes)),
            "includes": list(set(includes)),
            "extends": list(set(extends))
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
