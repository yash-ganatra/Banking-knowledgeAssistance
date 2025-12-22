#!/usr/bin/env python3
"""
Enhance existing JS chunks with actual code snippets
Reads js_file_chunks_enriched.json and adds code snippets to each chunk
"""

import json
import os
import re
from pathlib import Path

# Configuration
BASE_DIR = Path(__file__).parent.parent
ENRICHED_CHUNKS_FILE = BASE_DIR / "description" / "js_file_chunks_enriched.json"
JS_CODE_DIR = BASE_DIR / "code" / "code" / "public" / "js"
OUTPUT_FILE = BASE_DIR / "description" / "js_file_chunks_with_code.json"

# Token limits for code snippets
MAX_CODE_TOKENS = 600  # Approximate max tokens for code (adjust based on your embedding model)
MAX_LINES_PER_SNIPPET = 100


def extract_function_code(file_path, function_name, context_lines=5):
    """
    Extract function code from JS file with context
    
    Args:
        file_path: Path to JS file
        function_name: Name of function to extract
        context_lines: Number of lines before/after function
    
    Returns:
        dict with code_snippet, line_start, line_end
    """
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
        
        # Pattern to match function declaration
        patterns = [
            rf'function\s+{re.escape(function_name)}\s*\(',  # function name()
            rf'var\s+{re.escape(function_name)}\s*=\s*function',  # var name = function
            rf'{re.escape(function_name)}\s*:\s*function',  # name: function
            rf'const\s+{re.escape(function_name)}\s*=',  # const name = 
            rf'let\s+{re.escape(function_name)}\s*=',  # let name =
        ]
        
        start_line = None
        for i, line in enumerate(lines):
            for pattern in patterns:
                if re.search(pattern, line):
                    start_line = i
                    break
            if start_line is not None:
                break
        
        if start_line is None:
            return None
        
        # Find end of function (matching braces)
        brace_count = 0
        in_function = False
        end_line = start_line
        
        for i in range(start_line, len(lines)):
            line = lines[i]
            
            # Count braces
            brace_count += line.count('{') - line.count('}')
            
            if '{' in line:
                in_function = True
            
            # Function ends when braces balance
            if in_function and brace_count == 0:
                end_line = i
                break
        
        # Add context lines
        snippet_start = max(0, start_line - context_lines)
        snippet_end = min(len(lines), end_line + context_lines + 1)
        
        # Limit snippet size
        if (snippet_end - snippet_start) > MAX_LINES_PER_SNIPPET:
            snippet_end = snippet_start + MAX_LINES_PER_SNIPPET
        
        code_snippet = ''.join(lines[snippet_start:snippet_end])
        
        return {
            'code_snippet': code_snippet.strip(),
            'line_start': snippet_start + 1,  # 1-indexed
            'line_end': snippet_end,
            'total_lines': snippet_end - snippet_start
        }
        
    except Exception as e:
        print(f"Error extracting {function_name} from {file_path}: {e}")
        return None


def extract_event_listener_code(file_path, selector, event_type, max_lines=50):
    """
    Extract event listener code from JS file
    
    Args:
        file_path: Path to JS file
        selector: CSS selector (e.g., ".npcReview")
        event_type: Event type (e.g., "click")
    
    Returns:
        dict with code snippet and line numbers
    """
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
        
        # Pattern to match event listener
        # $("body").on("click", ".selector", function(){
        # $(".selector").on("click", function(){
        escaped_selector = re.escape(selector)
        patterns = [
            rf'\.on\(["\']({event_type})["\'],\s*["\']({escaped_selector})["\']',
            rf'\$\(["\']({escaped_selector})["\']\)\.on\(["\']({event_type})["\']',
        ]
        
        start_line = None
        for i, line in enumerate(lines):
            for pattern in patterns:
                if re.search(pattern, line, re.IGNORECASE):
                    start_line = i
                    break
            if start_line is not None:
                break
        
        if start_line is None:
            return None
        
        # Find end (matching braces)
        brace_count = 0
        end_line = start_line
        
        for i in range(start_line, min(len(lines), start_line + max_lines)):
            line = lines[i]
            brace_count += line.count('{') - line.count('}')
            
            if brace_count == 0 and '{' in lines[start_line]:
                end_line = i
                break
        
        code_snippet = ''.join(lines[start_line:end_line + 1])
        
        return {
            'code_snippet': code_snippet.strip(),
            'line_start': start_line + 1,
            'line_end': end_line + 1,
            'total_lines': end_line - start_line + 1
        }
        
    except Exception as e:
        print(f"Error extracting event listener from {file_path}: {e}")
        return None


def get_file_summary_code(file_path, max_lines=30):
    """
    Extract first N lines of file as summary code
    """
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()[:max_lines]
        
        return {
            'code_snippet': ''.join(lines).strip(),
            'line_start': 1,
            'line_end': len(lines),
            'total_lines': len(lines)
        }
    except Exception as e:
        print(f"Error reading file {file_path}: {e}")
        return None


def enhance_chunks_with_code():
    """
    Main function to enhance chunks with code snippets
    """
    print("Loading enriched chunks...")
    with open(ENRICHED_CHUNKS_FILE, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    print(f"Found {len(chunks)} chunks")
    
    enhanced_chunks = []
    processed_count = 0
    skipped_count = 0
    
    for chunk in chunks:
        chunk_type = chunk.get('chunk_type')
        file_name = chunk.get('file_name')
        
        if not file_name:
            enhanced_chunks.append(chunk)
            skipped_count += 1
            continue
        
        file_path = JS_CODE_DIR / file_name
        
        if not file_path.exists():
            print(f"Warning: File not found: {file_path}")
            enhanced_chunks.append(chunk)
            skipped_count += 1
            continue
        
        # Process based on chunk type
        code_data = None
        
        if chunk_type == 'js_function':
            function_name = chunk.get('function_name')
            if function_name:
                print(f"Extracting code for function: {function_name} in {file_name}")
                code_data = extract_function_code(file_path, function_name)
        
        elif chunk_type == 'js_file':
            print(f"Extracting summary code for file: {file_name}")
            code_data = get_file_summary_code(file_path, max_lines=30)
        
        elif chunk_type == 'js_event_listener':
            # If you have event listener chunks, extract them
            # (You'll need to add these in your chunks first)
            pass
        
        # Add code data to chunk
        if code_data:
            chunk['code_snippet'] = code_data['code_snippet']
            chunk['line_start'] = code_data['line_start']
            chunk['line_end'] = code_data['line_end']
            chunk['code_lines'] = code_data['total_lines']
            processed_count += 1
        else:
            skipped_count += 1
        
        enhanced_chunks.append(chunk)
    
    # Save enhanced chunks
    print(f"\nSaving enhanced chunks to {OUTPUT_FILE}")
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(enhanced_chunks, f, indent=2, ensure_ascii=False)
    
    print(f"\n✅ Enhancement complete!")
    print(f"   - Total chunks: {len(chunks)}")
    print(f"   - Enhanced with code: {processed_count}")
    print(f"   - Skipped: {skipped_count}")
    print(f"\nOutput saved to: {OUTPUT_FILE}")


if __name__ == "__main__":
    enhance_chunks_with_code()
