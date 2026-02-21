"""
JavaScript Parser

Parses JavaScript files to extract:
- Function definitions
- AJAX calls ($.ajax, fetch, axios)
- Event handlers (onclick, addEventListener)
- Route/URL endpoint calls

Creates JSFunction nodes and identifies:
- VIEW_INCLUDES_JS relationships (via script src in Blade)
- JS function → backend endpoint mappings
"""

import re
import logging
from pathlib import Path
from typing import List, Dict, Any, Optional, Set

logger = logging.getLogger(__name__)


class JSParser:
    """
    Parses JavaScript files to extract functions and endpoint calls.
    """
    
    def __init__(self, project_path: str):
        self.project_path = Path(project_path)
        self.js_paths = [
            self.project_path / "public/js",
            self.project_path / "resources/js",
        ]
    
    def parse(self) -> Dict[str, Any]:
        """
        Parse all JS files and return functions and relationships.
        
        Returns:
            Dict with:
            - functions: List of JSFunction node data
            - endpoints: List of (function_id, endpoint_url) tuples
            - view_includes_js: List of (view_name, js_file) tuples (populated separately)
        """
        functions = []
        endpoints = []
        
        for js_path in self.js_paths:
            if not js_path.exists():
                continue
                
            files = list(js_path.rglob("*.js"))
            logger.info(f"Parsing {len(files)} JS files from {js_path}")
            
            for file_path in files:
                try:
                    file_data = self._parse_file(file_path)
                    if file_data:
                        functions.extend(file_data["functions"])
                        endpoints.extend(file_data["endpoints"])
                except Exception as e:
                    logger.warning(f"Error parsing {file_path}: {e}")
        
        logger.info(f"Extracted {len(functions)} JS functions, {len(endpoints)} endpoint calls")
        
        return {
            "functions": functions,
            "endpoints": endpoints,
            "view_includes_js": []  # Populated by scanning blade files
        }
    
    def parse_single_file(self, file_path: str) -> Optional[Dict[str, Any]]:
        """
        Parse a single JS file by absolute path.
        Used by the ingestion pipeline for incremental updates.
        
        Args:
            file_path: Absolute path to a .js file
        
        Returns:
            Dict with functions and endpoints, or None if parsing fails
        """
        file_path = Path(file_path)
        if not file_path.exists():
            logger.warning(f"File not found: {file_path}")
            return None
        try:
            return self._parse_file(file_path)
        except Exception as e:
            logger.warning(f"Error parsing {file_path}: {e}")
            return None

    def _parse_file(self, file_path: Path) -> Optional[Dict[str, Any]]:
        """Parse a single JS file"""
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        
        # Get relative path for file identification
        try:
            relative_path = file_path.relative_to(self.project_path)
        except ValueError:
            relative_path = file_path.name
        
        file_name = file_path.stem  # e.g., "bank" from "bank.js"
        
        functions = []
        endpoints = []
        
        # Extract function definitions
        func_defs = self._extract_functions(content, str(relative_path), file_name)
        functions.extend(func_defs)
        
        # Extract AJAX/fetch endpoint calls
        ajax_endpoints = self._extract_ajax_calls(content, str(relative_path), file_name)
        endpoints.extend(ajax_endpoints)
        
        return {
            "functions": functions,
            "endpoints": endpoints
        }
    
    def _extract_functions(self, content: str, file_path: str, file_name: str) -> List[Dict[str, Any]]:
        """Extract function definitions from JS content"""
        functions = []
        seen_names = set()
        
        # Pattern 1: function name() { }
        pattern_func = re.compile(r"function\s+(\w+)\s*\(([^)]*)\)")
        
        # Pattern 2: const/let/var name = function() { }
        pattern_var_func = re.compile(r"(?:const|let|var)\s+(\w+)\s*=\s*function\s*\(([^)]*)\)")
        
        # Pattern 3: const/let/var name = () => { } or (params) => { }
        pattern_arrow = re.compile(r"(?:const|let|var)\s+(\w+)\s*=\s*(?:\(([^)]*)\)|(\w+))\s*=>")
        
        # Pattern 4: object method name() { } or name: function() { }
        pattern_method = re.compile(r"(\w+)\s*:\s*function\s*\(([^)]*)\)")
        
        # Pattern 5: jQuery event handlers $("selector").on("event", function() {})
        # We'll capture the event type and create a pseudo-function
        pattern_jquery_event = re.compile(
            r'\$\([\'"]([^"\']+)[\'"]\)\s*\.\s*on\s*\(\s*[\'"](\w+)[\'"]'
        )
        
        # Pattern 6: $("body").on("click", ".selector", function)
        pattern_delegated = re.compile(
            r'\$\([\'"]([^"\']+)[\'"]\)\s*\.\s*on\s*\(\s*[\'"](\w+)[\'"]\s*,\s*[\'"]([^"\']+)[\'"]'
        )
        
        for match in pattern_func.finditer(content):
            name = match.group(1)
            params = match.group(2)
            if name not in seen_names:
                seen_names.add(name)
                line = content[:match.start()].count('\n') + 1
                functions.append({
                    "id": f"{file_name}:{name}",
                    "name": name,
                    "file": file_path,
                    "params": params,
                    "type": "function",
                    "line": line
                })
        
        for match in pattern_var_func.finditer(content):
            name = match.group(1)
            params = match.group(2)
            if name not in seen_names:
                seen_names.add(name)
                line = content[:match.start()].count('\n') + 1
                functions.append({
                    "id": f"{file_name}:{name}",
                    "name": name,
                    "file": file_path,
                    "params": params,
                    "type": "function",
                    "line": line
                })
        
        for match in pattern_arrow.finditer(content):
            name = match.group(1)
            params = match.group(2) or match.group(3) or ""
            if name not in seen_names:
                seen_names.add(name)
                line = content[:match.start()].count('\n') + 1
                functions.append({
                    "id": f"{file_name}:{name}",
                    "name": name,
                    "file": file_path,
                    "params": params,
                    "type": "arrow_function",
                    "line": line
                })
        
        # Extract event handlers
        for match in pattern_delegated.finditer(content):
            selector = match.group(1)
            event = match.group(2)
            target = match.group(3)
            handler_name = f"on_{event}_{self._sanitize_selector(target)}"
            if handler_name not in seen_names:
                seen_names.add(handler_name)
                line = content[:match.start()].count('\n') + 1
                functions.append({
                    "id": f"{file_name}:{handler_name}",
                    "name": handler_name,
                    "file": file_path,
                    "params": "",
                    "type": "event_handler",
                    "event": event,
                    "selector": target,
                    "line": line
                })
        
        return functions
    
    def _extract_ajax_calls(self, content: str, file_path: str, file_name: str) -> List[tuple]:
        """Extract AJAX/fetch calls and their endpoint URLs"""
        endpoints = []
        
        # Pattern 1: $.ajax({ url: '/path', ... })
        ajax_pattern = re.compile(
            r"\$\.ajax\s*\(\s*\{[^}]*url\s*:\s*['\"]([^'\"]+)['\"]",
            re.DOTALL
        )
        
        # Pattern 2: $.get/$.post('/url', ...)
        jquery_get_post = re.compile(
            r"\$\.(get|post|getJSON)\s*\(\s*['\"]([^'\"]+)['\"]"
        )
        
        # Pattern 3: fetch('/url', ...)
        fetch_pattern = re.compile(
            r"fetch\s*\(\s*['\"]([^'\"]+)['\"]"
        )
        
        # Pattern 4: axios.get/post('/url', ...)
        axios_pattern = re.compile(
            r"axios\.(get|post|put|patch|delete)\s*\(\s*['\"]([^'\"]+)['\"]"
        )
        
        # Pattern 5: crudAjaxCall({url: '/path', ...}) - custom in this codebase
        crud_ajax_pattern = re.compile(
            r"(\w+)\.url\s*=\s*['\"]([^'\"]+)['\"]"
        )
        
        # Pattern 6: Direct URL in object literal like { url: '/bank/fileupload', ... }
        url_in_object = re.compile(
            r"['\"]?url['\"]?\s*:\s*['\"]([^'\"]+)['\"]"
        )
        
        # Pattern 7: window.location or location.href redirects
        redirect_pattern = re.compile(
            r"(?:window\.)?location(?:\.href)?\s*=\s*(?:baseUrl\s*\+\s*)?['\"]([^'\"]+)['\"]"
        )
        
        # Pattern 8: redirectUrl function calls (custom in this codebase)
        redirect_url_pattern = re.compile(
            r"redirectUrl\s*\([^,]+,\s*['\"]([^'\"]+)['\"]"
        )
        
        seen_urls = set()
        
        for match in ajax_pattern.finditer(content):
            url = match.group(1)
            if url and url not in seen_urls and url.startswith('/'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:ajax", url, "ajax"))
        
        for match in jquery_get_post.finditer(content):
            method = match.group(1)
            url = match.group(2)
            if url and url not in seen_urls and url.startswith('/'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:${method}", url, method))
        
        for match in fetch_pattern.finditer(content):
            url = match.group(1)
            if url and url not in seen_urls and url.startswith('/'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:fetch", url, "fetch"))
        
        for match in axios_pattern.finditer(content):
            method = match.group(1)
            url = match.group(2)
            if url and url not in seen_urls and url.startswith('/'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:axios_{method}", url, method))
        
        for match in crud_ajax_pattern.finditer(content):
            url = match.group(2)
            if url and url not in seen_urls and url.startswith('/'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:crudAjaxCall", url, "ajax"))
        
        for match in url_in_object.finditer(content):
            url = match.group(1)
            if url and url not in seen_urls and url.startswith('/') and not url.startswith('//'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:ajax", url, "ajax"))
        
        for match in redirect_pattern.finditer(content):
            url = match.group(1)
            if url and url not in seen_urls and url.startswith('/'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:redirect", url, "redirect"))
        
        for match in redirect_url_pattern.finditer(content):
            url = match.group(1)
            if url and url not in seen_urls and url.startswith('/'):
                seen_urls.add(url)
                endpoints.append((f"{file_name}:redirectUrl", url, "redirect"))
        
        return endpoints
    
    def _sanitize_selector(self, selector: str) -> str:
        """Convert CSS selector to valid identifier"""
        # Remove leading . or #
        selector = selector.lstrip('.#')
        # Replace non-alphanumeric with underscore
        return re.sub(r'[^a-zA-Z0-9]', '_', selector)
    
    def parse_blade_js_includes(self, views_path: Path) -> List[tuple]:
        """
        Scan blade files for JS includes to build VIEW_INCLUDES_JS relationships.
        
        Returns:
            List of (view_name, js_file_name) tuples
        """
        includes = []
        
        if not views_path.exists():
            return includes
        
        files = list(views_path.rglob("*.blade.php"))
        
        # Patterns for JS inclusion
        # <script src="{{ asset('custom/js/bank.js') }}"> or direct paths
        # Captures the filename before .js regardless of path depth
        script_src_pattern = re.compile(
            r'<script[^>]+src=["\'][^"\']*[/\\](\w+)\.js["\']',
            re.IGNORECASE
        )
        
        # asset('custom/js/bank.js') or asset('js/bank.js') or mix(...)
        # Matches any path ending in /filename.js
        asset_pattern = re.compile(
            r"(?:asset|mix)\s*\(\s*['\"][^'\"]*[/\\](\w+)\.js['\"]"
        )
        
        for file_path in files:
            try:
                with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
                    content = f.read()
                
                relative_path = file_path.relative_to(views_path)
                view_name = str(relative_path).replace(".blade.php", "").replace("/", ".")
                
                for match in script_src_pattern.finditer(content):
                    js_name = match.group(1)
                    includes.append((view_name, js_name))
                
                for match in asset_pattern.finditer(content):
                    js_name = match.group(1)
                    if (view_name, js_name) not in includes:
                        includes.append((view_name, js_name))
                        
            except Exception as e:
                logger.debug(f"Error scanning {file_path} for JS: {e}")
        
        return includes


if __name__ == "__main__":
    import sys
    import json
    logging.basicConfig(level=logging.INFO)
    
    path = "."
    if len(sys.argv) > 1:
        path = sys.argv[1]
    
    parser = JSParser(path)
    result = parser.parse()
    
    print(f"Functions: {len(result['functions'])}")
    print(f"Endpoints: {len(result['endpoints'])}")
    
    # Print sample
    if result['functions']:
        print("\nSample functions:")
        for func in result['functions'][:10]:
            print(f"  - {func['name']} ({func['file']}:{func.get('line', '?')})")
    
    if result['endpoints']:
        print("\nSample endpoint calls:")
        for src, url, method in result['endpoints'][:10]:
            print(f"  - {src} → {url} ({method})")
    
    # Also test blade JS scanning
    project_path = Path(path)
    views_path = project_path / "resources/views"
    if views_path.exists():
        includes = parser.parse_blade_js_includes(views_path)
        print(f"\nView→JS includes: {len(includes)}")
        for view, js in includes[:5]:
            print(f"  - {view} includes {js}.js")
