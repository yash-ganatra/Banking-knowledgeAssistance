#!/usr/bin/env python3
"""
Chunkers for the Ingestion Pipeline

Produces chunks in the EXACT same JSON schema as the existing bulk pipelines:
- PHP: matches utils/chunk_php_metadata.py + utils/extract_php_code.py
- JS:  matches the schema in description/js_file_chunks_enhanced_descriptions.json
- Blade: matches utils/chunk_views_blade.py

These chunkers take raw parser output + the source file and produce
chunk dicts ready for description generation and embedding.
"""

import re
import os
import hashlib
import logging
from pathlib import Path
from typing import List, Dict, Any, Optional

logger = logging.getLogger(__name__)


# =============================================================================
# PHP CHUNKER — matches chunk_php_metadata.py + extract_php_code.py schemas
# =============================================================================

# Token limits for code snippets (from utils/extract_php_code.py)
PHP_MAX_CODE_LINES = 150
PHP_CONTEXT_LINES = 3


def _extract_php_imports(content: str) -> List[str]:
    """Extract use statements from PHP file content."""
    return re.findall(r"use\s+([\w\\]+);", content)


def _extract_related_classes(imports: List[str]) -> List[str]:
    """Extract class names from imports (last segment after backslash)."""
    related = []
    for imp in imports:
        parts = imp.split("\\")
        if parts:
            related.append(parts[-1])
    return related


def _extract_class_code(lines: List[str], class_name: str) -> Optional[Dict]:
    """
    Extract class definition code from file lines.
    Matches logic in utils/extract_php_code.py -> extract_class_code()
    """
    class_pattern = re.compile(
        rf"^\s*class\s+{re.escape(class_name)}\s+", re.IGNORECASE
    )
    class_start = None

    for i, line in enumerate(lines):
        if class_pattern.search(line):
            class_start = i
            break

    if class_start is None:
        return None

    brace_count = 0
    class_end = class_start
    in_class = False

    for i in range(class_start, len(lines)):
        brace_count += lines[i].count("{") - lines[i].count("}")
        if "{" in lines[i]:
            in_class = True
        if in_class and brace_count == 0:
            class_end = i
            break

    truncated = (class_end - class_start) > PHP_MAX_CODE_LINES
    if truncated:
        class_end = class_start + PHP_MAX_CODE_LINES

    snippet_start = max(0, class_start - PHP_CONTEXT_LINES)
    snippet_end = min(len(lines), class_end + PHP_CONTEXT_LINES + 1)

    return {
        "code_snippet": "".join(lines[snippet_start:snippet_end]).strip(),
        "line_start": snippet_start + 1,
        "line_end": snippet_end,
        "num_lines": snippet_end - snippet_start,
        "truncated": truncated,
    }


def _extract_method_code(
    lines: List[str], method_name: str
) -> Optional[Dict]:
    """
    Extract method code from file lines.
    Matches logic in utils/extract_php_code.py -> extract_method_code()
    """
    method_patterns = [
        re.compile(
            rf"^\s*(public|private|protected)\s+static\s+function\s+{re.escape(method_name)}\s*\(",
            re.IGNORECASE,
        ),
        re.compile(
            rf"^\s*static\s+(public|private|protected)\s+function\s+{re.escape(method_name)}\s*\(",
            re.IGNORECASE,
        ),
        re.compile(
            rf"^\s*(public|private|protected)\s+function\s+{re.escape(method_name)}\s*\(",
            re.IGNORECASE,
        ),
        re.compile(
            rf"^\s*static\s+function\s+{re.escape(method_name)}\s*\(",
            re.IGNORECASE,
        ),
        re.compile(
            rf"^\s*function\s+{re.escape(method_name)}\s*\(", re.IGNORECASE
        ),
    ]

    method_start = None
    for i, line in enumerate(lines):
        for pattern in method_patterns:
            if pattern.search(line):
                method_start = i
                break
        if method_start is not None:
            break

    if method_start is None:
        return None

    brace_count = 0
    method_end = method_start
    in_method = False

    for i in range(method_start, len(lines)):
        brace_count += lines[i].count("{") - lines[i].count("}")
        if "{" in lines[i]:
            in_method = True
        if in_method and brace_count == 0:
            method_end = i
            break

    if not in_method or brace_count != 0:
        return None

    truncated = (method_end - method_start) > PHP_MAX_CODE_LINES
    if truncated:
        method_end = method_start + PHP_MAX_CODE_LINES

    snippet_start = max(0, method_start - PHP_CONTEXT_LINES)
    snippet_end = min(len(lines), method_end + PHP_CONTEXT_LINES + 1)

    return {
        "code_snippet": "".join(lines[snippet_start:snippet_end]).strip(),
        "line_start": snippet_start + 1,
        "line_end": snippet_end,
        "num_lines": snippet_end - snippet_start,
        "truncated": truncated,
    }


def chunk_php_file(
    file_path: str,
    parser_data: Dict[str, Any],
    project_root: str,
) -> List[Dict[str, Any]]:
    """
    Chunk a single PHP file into class-level and method-level chunks.
    
    Output schema matches EXACTLY:
    - utils/chunk_php_metadata.py -> create_class_chunk / create_method_chunk
    - utils/extract_php_code.py -> code_snippet fields
    
    Args:
        file_path: Absolute path to the PHP file
        parser_data: Output from ControllerParser._parse_file() or HelperParser._parse_file()
                     Contains: name, file, methods[], (optional: parent_class, type)
        project_root: Root of the Laravel project (code/code/)
    
    Returns:
        List of chunk dicts ready for description generation + embedding
    """
    file_path = Path(file_path)
    chunks = []

    # Read file lines for code extraction
    try:
        with open(file_path, "r", encoding="utf-8") as f:
            lines = f.readlines()
        content = "".join(lines)
    except Exception as e:
        logger.error(f"Error reading {file_path}: {e}")
        return []

    # Build the relative path matching the existing format (code/code/app/...)
    try:
        rel_path = str(file_path.relative_to(Path(project_root).parent.parent))
    except ValueError:
        rel_path = str(file_path)
    # Normalize to forward slashes
    rel_path = rel_path.replace("\\", "/")

    class_name = parser_data.get("name", "")
    method_names = [m["name"] for m in parser_data.get("methods", [])]
    imports = _extract_php_imports(content)
    related_classes = _extract_related_classes(imports)

    # --- Class-level chunk ---
    class_chunk_id = hashlib.md5(
        f"{rel_path}_{class_name}_class".encode()
    ).hexdigest()

    class_code_data = _extract_class_code(lines, class_name)

    class_chunk = {
        "chunk_id": class_chunk_id,
        "chunk_type": "php_class",
        "language": "php",
        "file_path": rel_path,
        "class_name": class_name,
        "class_description": "",  # Filled by description generator
        "methods": method_names,
        "num_methods": len(method_names),
        "dependencies": imports,
        "related_classes": related_classes,
    }

    if class_code_data:
        class_chunk["code_snippet"] = class_code_data["code_snippet"]
        class_chunk["code_line_start"] = class_code_data["line_start"]
        class_chunk["code_line_end"] = class_code_data["line_end"]
        class_chunk["code_num_lines"] = class_code_data["num_lines"]
        class_chunk["code_truncated"] = class_code_data["truncated"]
    else:
        class_chunk["code_snippet"] = None
        class_chunk["code_line_start"] = None
        class_chunk["code_line_end"] = None
        class_chunk["code_num_lines"] = 0
        class_chunk["code_truncated"] = False

    chunks.append(class_chunk)

    # --- Method-level chunks ---
    for method_data in parser_data.get("methods", []):
        method_name = method_data["name"]
        method_chunk_id = hashlib.md5(
            f"{rel_path}_{class_name}_{method_name}_method".encode()
        ).hexdigest()

        params = method_data.get("params", "")
        # Infer return type from the signature if possible (not always available from parser)
        return_type = "void"

        method_code_data = _extract_method_code(lines, method_name)

        method_chunk = {
            "chunk_id": method_chunk_id,
            "chunk_type": "php_method",
            "language": "php",
            "file_path": rel_path,
            "class_name": class_name,
            "method_name": method_name,
            "parameters": params,
            "return_type": return_type,
            "method_description": "",  # Filled by description generator
            "dependencies": imports,
            "related_classes": related_classes,
        }

        if method_code_data:
            method_chunk["code_snippet"] = method_code_data["code_snippet"]
            method_chunk["code_line_start"] = method_code_data["line_start"]
            method_chunk["code_line_end"] = method_code_data["line_end"]
            method_chunk["code_num_lines"] = method_code_data["num_lines"]
            method_chunk["code_truncated"] = method_code_data["truncated"]
        else:
            method_chunk["code_snippet"] = None
            method_chunk["code_line_start"] = None
            method_chunk["code_line_end"] = None
            method_chunk["code_num_lines"] = 0
            method_chunk["code_truncated"] = False

        chunks.append(method_chunk)

    logger.info(
        f"  PHP chunked: {class_name} → 1 class + {len(method_names)} method chunks"
    )
    return chunks


# =============================================================================
# JS CHUNKER — matches the schema in js_file_chunks_enhanced_descriptions.json
# =============================================================================

def chunk_js_file(
    file_path: str,
    parser_data: Dict[str, Any],
    project_root: str,
) -> List[Dict[str, Any]]:
    """
    Chunk a single JS file into per-function chunks.
    
    Output schema matches the existing js_file_chunks_enhanced_descriptions.json structure
    used by embedding_vectordb/embed_js_chunk_to_chromadb.py.
    
    Args:
        file_path: Absolute path to the JS file
        parser_data: Output from JSParser._parse_file()
                     Contains: functions[], endpoints[]
        project_root: Root of the Laravel project (code/code/)
    
    Returns:
        List of chunk dicts ready for description generation + embedding
    """
    file_path = Path(file_path)
    chunks = []

    # Read file content for code extraction
    try:
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        content_lines = content.split("\n")
    except Exception as e:
        logger.error(f"Error reading {file_path}: {e}")
        return []

    # Build relative path
    try:
        rel_path = str(file_path.relative_to(Path(project_root).parent.parent))
    except ValueError:
        rel_path = str(file_path)
    rel_path = rel_path.replace("\\", "/")

    file_name = file_path.name  # e.g., "bank.js"
    file_stem = file_path.stem   # e.g., "bank"

    # Derive feature domain from path
    feature_domain = file_stem

    # Build endpoint URL lookup from parser data
    endpoint_map = {}  # function_id -> first endpoint URL
    for func_id, url, method_type in parser_data.get("endpoints", []):
        if func_id not in endpoint_map:
            endpoint_map[func_id] = url

    # Extract DOM selectors from the whole file (jQuery selectors)
    dom_selectors = list(set(re.findall(r'["\']([#.][a-zA-Z][\w-]*)["\']', content)))

    functions = parser_data.get("functions", [])

    for func in functions:
        func_name = func.get("name", "")
        func_line = func.get("line", 0)
        func_params = func.get("params", "")

        # Extract code snippet: from the function start line, find the matching brace
        code_snippet = _extract_js_function_code(content_lines, func_line)
        code_lines_count = code_snippet.count("\n") + 1 if code_snippet else 0

        # Parse parameters into a list
        param_list = [p.strip() for p in func_params.split(",") if p.strip()]

        # Find matching endpoint
        func_id = f"{file_stem}:{func_name}"
        endpoint_url = endpoint_map.get(func_id, "")

        chunk = {
            "chunk_type": "js_function",
            "language": "javascript",
            "file_path": rel_path,
            "file_name": file_name,
            "feature_domain": feature_domain,
            "function_name": func_name,
            "parameters": param_list,
            "endpoint_url": endpoint_url,
            "code_snippet": code_snippet,
            "line_start": func_line,
            "line_end": func_line + code_lines_count,
            "code_lines": code_lines_count,
            "description": "",  # Filled by description generator
            "description_enhanced": False,
            "parent_file_dom_selectors": dom_selectors[:20],
        }
        chunks.append(chunk)

    # Also create chunks for endpoints that aren't tied to a named function
    seen_endpoints = set()
    for func_id, url, method_type in parser_data.get("endpoints", []):
        if url not in seen_endpoints:
            seen_endpoints.add(url)
            # Only create a standalone endpoint chunk if not already covered by a function chunk
            if not any(c.get("endpoint_url") == url for c in chunks):
                chunk = {
                    "chunk_type": "js_ajax_endpoint",
                    "language": "javascript",
                    "file_path": rel_path,
                    "file_name": file_name,
                    "feature_domain": feature_domain,
                    "function_name": "",
                    "parameters": [],
                    "endpoint_url": url,
                    "http_method": method_type.upper() if method_type else "",
                    "code_snippet": "",
                    "line_start": 0,
                    "line_end": 0,
                    "code_lines": 0,
                    "description": "",
                    "description_enhanced": False,
                    "parent_file_dom_selectors": dom_selectors[:20],
                }
                chunks.append(chunk)

    logger.info(
        f"  JS chunked: {file_name} → {len(chunks)} chunks "
        f"({len(functions)} functions)"
    )
    return chunks


def _extract_js_function_code(lines: List[str], start_line: int, max_lines: int = 150) -> str:
    """Extract JS function body from the given start line by brace matching."""
    if start_line < 1 or start_line > len(lines):
        return ""

    # Convert to 0-indexed
    idx = start_line - 1
    brace_count = 0
    in_func = False
    end_idx = idx

    for i in range(idx, min(len(lines), idx + max_lines)):
        line = lines[i]
        brace_count += line.count("{") - line.count("}")
        if "{" in line:
            in_func = True
        if in_func and brace_count <= 0:
            end_idx = i
            break
    else:
        end_idx = min(len(lines) - 1, idx + max_lines - 1)

    return "\n".join(lines[idx : end_idx + 1])


# =============================================================================
# BLADE CHUNKER — matches utils/chunk_views_blade.py schema
# =============================================================================

def chunk_blade_file(
    file_path: str,
    project_root: str,
) -> List[Dict[str, Any]]:
    """
    Chunk a single Blade template file.
    
    Output schema matches EXACTLY: utils/chunk_views_blade.py -> chunk_blade_template()
    
    The blade chunker doesn't need parser output because the chunking is done
    directly from the file content (sections / full template).
    
    Args:
        file_path: Absolute path to the .blade.php file
        project_root: Root project (the Banking-knowledgeAssistance/ dir)
    
    Returns:
        List of chunk dicts matching the blade_views_raw.json schema
    """
    file_path = Path(file_path)

    try:
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()
    except Exception as e:
        logger.error(f"Error reading {file_path}: {e}")
        return []

    # Build relative path from project root (matching existing: "code/code/resources/views/...")
    try:
        rel_path = str(file_path.relative_to(Path(project_root)))
    except ValueError:
        rel_path = str(file_path)
    rel_path = rel_path.replace("\\", "/")

    filename = file_path.name  # e.g., "login.blade.php"

    # Extract base metadata (matches utils/chunk_views_blade.py -> extract_metadata)
    extends_match = re.search(r"@extends\s*\(['\"](.+?)['\"]\)", content)
    extends_val = extends_match.group(1) if extends_match else None
    includes_list = re.findall(r"@include\s*\(['\"](.+?)['\"]\)", content)
    has_form = "<form" in content

    chunks = []

    # 1. Handle block sections: @section('key') ... @endsection
    block_pattern = r"@section\s*\(['\"]([^,]*?)['\"]\)(.*?)@endsection"
    block_sections = re.findall(block_pattern, content, re.DOTALL)

    if block_sections:
        for section_name, section_content in block_sections:
            cleaned_content = section_content.strip()
            if not cleaned_content:
                continue

            section_has_form = "<form" in cleaned_content

            chunk_id_base = f"{rel_path}#section-{section_name}"
            chunk_id = hashlib.md5(chunk_id_base.encode()).hexdigest()

            chunk = {
                "chunk_id": chunk_id,
                "file_name": filename,
                "file_path": rel_path,
                "chunk_type": "blade_section",
                "section_name": section_name,
                "content": f"<!-- Section: {section_name} -->\n{cleaned_content}",
                "metadata": {
                    "source": rel_path,
                    "extends": extends_val,
                    "includes": includes_list,
                    "has_form": section_has_form,
                },
                "description": "",  # Filled by description generator
                "description_enhanced": False,
            }
            chunks.append(chunk)

    # 2. If no block sections, chunk the whole file
    if not block_sections:
        chunk_id = hashlib.md5(f"{rel_path}#full".encode()).hexdigest()
        chunks.append(
            {
                "chunk_id": chunk_id,
                "file_name": filename,
                "file_path": rel_path,
                "chunk_type": "blade_full",
                "section_name": "full_template",
                "content": content.strip(),
                "metadata": {
                    "source": rel_path,
                    "extends": extends_val,
                    "includes": includes_list,
                    "has_form": has_form,
                },
                "description": "",
                "description_enhanced": False,
            }
        )

    logger.info(f"  Blade chunked: {filename} → {len(chunks)} chunks")
    return chunks
