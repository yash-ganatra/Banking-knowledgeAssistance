
"""
Helper/Utility Parser

Parses PHP Helper and Utility files (e.g., app/Helpers/) to extract:
- Class definitions (with or without `extends`)
- All function methods (public, protected, private, static)
- Model usage (Model::method)
- Table usage (DB::table)

This parser is broader than ControllerParser, which only handles
classes that `extends` a base class in the Controllers directory.
"""

import re
import logging
from pathlib import Path
from typing import List, Dict, Any

logger = logging.getLogger(__name__)


class HelperParser:
    def __init__(self, project_path: str, scan_dirs: List[str] = None):
        """
        Initialize the HelperParser.

        Args:
            project_path: Root path of the Laravel project
            scan_dirs: List of relative directories to scan (default: ["app/Helpers"])
        """
        self.project_path = Path(project_path)
        self.scan_dirs = scan_dirs or ["app/Helpers"]

    def parse(self) -> List[Dict[str, Any]]:
        """
        Parse all helper/utility PHP files and return class definitions.
        """
        helpers = []

        for scan_dir in self.scan_dirs:
            target_path = self.project_path / scan_dir
            if not target_path.exists():
                logger.warning(f"Helper directory not found: {target_path}")
                continue

            files = list(target_path.rglob("*.php"))
            logger.info(f"Found {len(files)} PHP files in {scan_dir}")

            for file_path in files:
                logger.info(f"Parsing helper: {file_path.name}")
                helper_data = self._parse_file(file_path)
                if helper_data:
                    helpers.append(helper_data)

        return helpers

    def parse_single_file(self, file_path: str) -> Dict[str, Any]:
        """
        Parse a single PHP helper/utility file.
        Used by the ingestion pipeline for incremental updates.
        
        Args:
            file_path: Absolute path to a .php helper file
        
        Returns:
            Dict with helper data, or empty dict if parsing fails
        """
        file_path = Path(file_path)
        if not file_path.exists():
            logger.warning(f"File not found: {file_path}")
            return {}
        logger.info(f"Parsing helper: {file_path.name}")
        result = self._parse_file(file_path)
        return result if result else {}

    def _parse_file(self, file_path: Path) -> Dict[str, Any]:
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()

        # Extract Class Name — matches `class X`, `class X extends Y`, `class X implements Y`
        class_match = re.search(r"class\s+(\w+)", content)
        if not class_match:
            logger.debug(f"No class found in {file_path.name}, skipping")
            return None

        class_name = class_match.group(1)

        # Check if it extends something
        extends_match = re.search(r"class\s+\w+\s+extends\s+(\w+)", content)
        parent_class = extends_match.group(1) if extends_match else None

        # Extract ALL methods (public, protected, private, static)
        methods = []
        method_pattern = re.compile(
            r"(public|protected|private)\s+(static\s+)?function\s+(\w+)\s*\(([^)]*)\)"
        )

        for match in method_pattern.finditer(content):
            visibility = match.group(1)
            is_static = bool(match.group(2))
            method_name = match.group(3)
            params = match.group(4)
            start_pos = match.end()

            # Find method body (brace matching)
            body = self._extract_method_body(content, start_pos)

            # Extract relationships from body
            models = self._extract_models(body)
            tables = self._extract_tables(body)
            views = self._extract_views(body)
            function_calls = self._extract_function_calls(body)

            methods.append({
                "name": method_name,
                "params": params,
                "visibility": visibility,
                "is_static": is_static,
                "models": models,
                "tables": tables,
                "views": views,
                "function_calls": function_calls,
                "line": content[:match.start()].count('\n') + 1,
            })

        logger.info(f"  → Found {len(methods)} methods in {class_name}")

        return {
            "name": class_name,
            "file": str(file_path.relative_to(self.project_path)),
            "parent_class": parent_class,
            "type": "helper",
            "methods": methods,
        }

    def _extract_method_body(self, content: str, start_pos: int) -> str:
        """Extract method body by matching braces."""
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
        """Extract view() and View::make() calls."""
        views = set()
        matches = re.findall(r"view\s*\(\s*['\"]([^'\"]+)['\"]", body)
        views.update(matches)
        matches = re.findall(r"View::make\s*\(\s*['\"]([^'\"]+)['\"]", body)
        views.update(matches)
        return list(views)

    def _extract_models(self, body: str) -> List[str]:
        """Extract Model::method() static calls."""
        matches = re.findall(r"([A-Z][a-zA-Z0-9_]+)::[a-z]", body)
        ignored = {
            "DB", "Log", "Route", "View", "Schema", "Auth", "Session",
            "Redirect", "Request", "Response", "Config", "Validator",
            "Carbon", "File", "Crypt", "Cache", "Str", "DateTime",
        }
        return list(set(m for m in matches if m not in ignored))

    def _extract_tables(self, body: str) -> List[Dict[str, str]]:
        """Extract DB::table('name') calls with access type."""
        tables = []
        matches = re.finditer(r"DB::table\s*\(\s*['\"]([^'\"]+)['\"]", body)

        for match in matches:
            table_name = match.group(1)
            access = "read"
            if re.search(
                r"->(insert|update|delete|truncate)\(",
                body[match.end():match.end() + 200]
            ):
                access = "write"
            tables.append({"name": table_name, "access": access})

        return tables

    def _extract_function_calls(self, body: str) -> List[Dict[str, str]]:
        """
        Extract method calls from body:
        1. Static calls: ClassName::methodName()
        2. Instance calls: $this->methodName()
        
        Returns list of dicts: {'class': 'ClassName'|'self', 'method': 'methodName'}
        """
        pattern = re.compile(r"([A-Z][a-zA-Z0-9_]+)::([a-zA-Z_]\w*)\s*\(")

        ignored_classes = {
            "DB", "Log", "Route", "View", "Schema", "Auth", "Session",
            "Redirect", "Request", "Response", "Config", "Validator",
            "Carbon", "File", "Crypt", "Cache", "Str", "DateTime",
            "PDF", "Image", "Hash", "Mail", "Queue", "Event",
            "Arr", "Collection", "Artisan", "URL", "Gate",
            "JWE", "ExceptionController",
        }

        calls = []
        seen = set()
        
        # 1. Static method calls: ClassName::methodName()
        for match in pattern.finditer(body):
            class_name = match.group(1)
            method_name = match.group(2)

            if class_name in ignored_classes:
                continue

            key = f"{class_name}::{method_name}"
            if key not in seen:
                seen.add(key)
                calls.append({"class": class_name, "method": method_name})

        # 2. Instance method calls: $this->methodName()
        # These represent intra-class calls (ACTION_CALLS_ACTION within same class)
        instance_pattern = re.compile(r"\$this->([a-zA-Z_]\w*)\s*\(")
        for match in instance_pattern.finditer(body):
            method_name = match.group(1)
            key = f"self::{method_name}"
            if key not in seen:
                seen.add(key)
                calls.append({"class": "self", "method": method_name})

        return calls


if __name__ == "__main__":
    import sys
    import json
    logging.basicConfig(level=logging.INFO)

    path = "."
    if len(sys.argv) > 1:
        path = sys.argv[1]

    parser = HelperParser(path)
    result = parser.parse()
    print(json.dumps(result, indent=2))
