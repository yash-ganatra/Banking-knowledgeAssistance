#!/usr/bin/env python3
"""
Extract PHP code snippets and add them to existing PHP chunks.
This script reads php_metadata_chunks_for_chromadb.json and adds code_snippet field.
"""

import json
import os
import re
from pathlib import Path
from typing import Dict, Optional, Tuple

# Configuration
BASE_DIR = Path(__file__).parent.parent
CHUNKS_FILE = BASE_DIR / "chunks" / "php_metadata_chunks_for_chromadb.json"
OUTPUT_FILE = BASE_DIR / "chunks" / "php_metadata_chunks_with_code.json"
PHP_CODE_DIR = BASE_DIR / "code" / "code"

# Token limits for code snippets
MAX_CODE_LINES = 150  # Maximum lines per code snippet
CONTEXT_LINES = 3  # Lines before/after for context


def normalize_path(file_path: str) -> Path:
    """
    Convert Windows-style path to Unix and create absolute path.
    
    Args:
        file_path: Path from chunk (e.g., 'code\\code\\app\\Console\\...')
    
    Returns:
        Absolute Path object
    """
    # Convert backslashes to forward slashes
    normalized = file_path.replace('\\', '/')
    
    # Remove 'code/code/' prefix if present
    if normalized.startswith('code/code/'):
        normalized = normalized[10:]
    
    # Create absolute path
    return PHP_CODE_DIR / normalized


def extract_class_code(file_path: Path, class_name: str) -> Optional[Dict]:
    """
    Extract class definition code from PHP file.
    
    Args:
        file_path: Path to PHP file
        class_name: Name of class to extract
    
    Returns:
        Dict with code_snippet, line_start, line_end, or None
    """
    if not file_path.exists():
        print(f"⚠️  File not found: {file_path}")
        return None
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
    except Exception as e:
        print(f"❌ Error reading {file_path}: {e}")
        return None
    
    # Find class declaration
    class_pattern = re.compile(rf'^\s*class\s+{re.escape(class_name)}\s+', re.IGNORECASE)
    class_start = None
    
    for i, line in enumerate(lines):
        if class_pattern.search(line):
            class_start = i
            break
    
    if class_start is None:
        print(f"⚠️  Class '{class_name}' not found in {file_path}")
        return None
    
    # Find end of class (matching braces)
    brace_count = 0
    class_end = class_start
    in_class = False
    
    for i in range(class_start, len(lines)):
        line = lines[i]
        
        # Count braces
        brace_count += line.count('{') - line.count('}')
        
        if '{' in line:
            in_class = True
        
        # Class ends when braces balance
        if in_class and brace_count == 0:
            class_end = i
            break
    
    # Limit to MAX_CODE_LINES
    if class_end - class_start > MAX_CODE_LINES:
        class_end = class_start + MAX_CODE_LINES
    
    # Extract code with context
    snippet_start = max(0, class_start - CONTEXT_LINES)
    snippet_end = min(len(lines), class_end + CONTEXT_LINES + 1)
    
    code_snippet = ''.join(lines[snippet_start:snippet_end])
    
    return {
        'code_snippet': code_snippet.strip(),
        'line_start': snippet_start + 1,  # 1-indexed
        'line_end': snippet_end,
        'num_lines': snippet_end - snippet_start,
        'truncated': (class_end - class_start) > MAX_CODE_LINES
    }


def extract_method_code(file_path: Path, class_name: str, method_name: str) -> Optional[Dict]:
    """
    Extract method definition code from PHP file.
    
    Args:
        file_path: Path to PHP file
        class_name: Name of class containing the method
        method_name: Name of method to extract
    
    Returns:
        Dict with code_snippet, line_start, line_end, or None
    """
    if not file_path.exists():
        print(f"⚠️  File not found: {file_path}")
        return None
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
    except Exception as e:
        print(f"❌ Error reading {file_path}: {e}")
        return None
    
    # Find method declaration (public/private/protected static function name)
    method_patterns = [
        re.compile(rf'^\s*(public|private|protected)\s+static\s+function\s+{re.escape(method_name)}\s*\(', re.IGNORECASE),
        re.compile(rf'^\s*static\s+(public|private|protected)\s+function\s+{re.escape(method_name)}\s*\(', re.IGNORECASE),
        re.compile(rf'^\s*(public|private|protected)\s+function\s+{re.escape(method_name)}\s*\(', re.IGNORECASE),
        re.compile(rf'^\s*static\s+function\s+{re.escape(method_name)}\s*\(', re.IGNORECASE),
        re.compile(rf'^\s*function\s+{re.escape(method_name)}\s*\(', re.IGNORECASE),
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
        # Silent failure - don't print warning for every missing method
        return None
    
    # Find end of method (matching braces)
    brace_count = 0
    method_end = method_start
    in_method = False
    
    for i in range(method_start, len(lines)):
        line = lines[i]
        
        # Count braces (ignore braces in strings/comments)
        brace_count += line.count('{') - line.count('}')
        
        if '{' in line:
            in_method = True
        
        # Method ends when braces balance
        if in_method and brace_count == 0:
            method_end = i
            break
    
    # If we never found the closing brace, the method might be incomplete
    if not in_method or brace_count != 0:
        return None
    
    # Limit to MAX_CODE_LINES
    if method_end - method_start > MAX_CODE_LINES:
        method_end = method_start + MAX_CODE_LINES
    
    # Extract code with context
    snippet_start = max(0, method_start - CONTEXT_LINES)
    snippet_end = min(len(lines), method_end + CONTEXT_LINES + 1)
    
    code_snippet = ''.join(lines[snippet_start:snippet_end])
    
    return {
        'code_snippet': code_snippet.strip(),
        'line_start': snippet_start + 1,  # 1-indexed
        'line_end': snippet_end,
        'num_lines': snippet_end - snippet_start,
        'truncated': (method_end - method_start) > MAX_CODE_LINES
    }


def enhance_chunks_with_code(input_file: Path, output_file: Path):
    """
    Add code snippets to existing PHP chunks.
    
    Args:
        input_file: Path to php_metadata_chunks_for_chromadb.json
        output_file: Path to save enhanced chunks
    """
    print("=" * 80)
    print("📦 Enhancing PHP Chunks with Code Snippets")
    print("=" * 80)
    
    print(f"\n📂 Loading chunks from: {input_file}")
    with open(input_file, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    print(f"✓ Loaded {len(chunks)} chunks")
    
    enhanced_chunks = []
    stats = {
        'total': len(chunks),
        'class_chunks': 0,
        'method_chunks': 0,
        'code_extracted': 0,
        'code_not_found': 0,
        'file_not_found': 0,
        'files_processed': set()
    }
    
    print(f"\n🔍 Extracting code from PHP files...")
    
    for i, chunk in enumerate(chunks):
        chunk_type = chunk.get('chunk_type')
        file_path = normalize_path(chunk.get('file_path', ''))
        
        # Show progress every 200 chunks to reduce output
        if (i + 1) % 200 == 0:
            print(f"   Processed {i + 1}/{len(chunks)} chunks... ({stats['code_extracted']} with code)")
        
        # Track unique files
        stats['files_processed'].add(str(file_path))
        
        # Extract code based on chunk type
        code_data = None
        
        if chunk_type == 'php_class':
            stats['class_chunks'] += 1
            class_name = chunk.get('class_name')
            code_data = extract_class_code(file_path, class_name)
        
        elif chunk_type == 'php_method':
            stats['method_chunks'] += 1
            class_name = chunk.get('class_name')
            method_name = chunk.get('method_name')
            code_data = extract_method_code(file_path, class_name, method_name)
        
        # Add code to chunk
        if code_data:
            chunk['code_snippet'] = code_data['code_snippet']
            chunk['code_line_start'] = code_data['line_start']
            chunk['code_line_end'] = code_data['line_end']
            chunk['code_num_lines'] = code_data['num_lines']
            chunk['code_truncated'] = code_data['truncated']
            stats['code_extracted'] += 1
        else:
            chunk['code_snippet'] = None
            chunk['code_line_start'] = None
            chunk['code_line_end'] = None
            chunk['code_num_lines'] = 0
            chunk['code_truncated'] = False
            
            if not file_path.exists():
                stats['file_not_found'] += 1
            else:
                stats['code_not_found'] += 1
        
        enhanced_chunks.append(chunk)
    
    # Save enhanced chunks
    print(f"\n💾 Saving enhanced chunks to: {output_file}")
    output_file.parent.mkdir(parents=True, exist_ok=True)
    
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(enhanced_chunks, f, indent=2, ensure_ascii=False)
    
    print(f"✓ Saved {len(enhanced_chunks)} enhanced chunks")
    
    # Print statistics
    print("\n" + "=" * 80)
    print("📊 Statistics")
    print("=" * 80)
    print(f"Total chunks:              {stats['total']}")
    print(f"  ├─ Class chunks:         {stats['class_chunks']}")
    print(f"  └─ Method chunks:        {stats['method_chunks']}")
    print(f"\nUnique PHP files:          {len(stats['files_processed'])}")
    print(f"\nCode extraction:")
    print(f"  ✓ Successfully extracted: {stats['code_extracted']} ({stats['code_extracted']/stats['total']*100:.1f}%)")
    print(f"  ⚠ File not found:        {stats['file_not_found']} ({stats['file_not_found']/stats['total']*100:.1f}%)")
    print(f"  ⚠ Code not extracted:    {stats['code_not_found']} ({stats['code_not_found']/stats['total']*100:.1f}%)")
    print("\n💡 Tip: Code not extracted could be due to:")
    print("   - Abstract methods (declarations only)")
    print("   - Interface methods")
    print("   - Methods with unusual formatting")
    print("=" * 80)
    
    return enhanced_chunks


def main():
    """Main execution function."""
    
    # Check input file exists
    if not CHUNKS_FILE.exists():
        print(f"❌ Error: Input file not found: {CHUNKS_FILE}")
        print(f"   Please ensure {CHUNKS_FILE.name} exists.")
        return
    
    # Check PHP code directory exists
    if not PHP_CODE_DIR.exists():
        print(f"❌ Error: PHP code directory not found: {PHP_CODE_DIR}")
        print(f"   Please ensure the code directory exists.")
        return
    
    # Run enhancement
    enhanced_chunks = enhance_chunks_with_code(CHUNKS_FILE, OUTPUT_FILE)
    
    # Print sample
    if enhanced_chunks:
        print("\n📝 Sample Enhanced Chunk (first with code):")
        print("-" * 80)
        for chunk in enhanced_chunks:
            if chunk.get('code_snippet'):
                print(f"Chunk ID: {chunk['chunk_id']}")
                print(f"Type: {chunk['chunk_type']}")
                print(f"Class: {chunk.get('class_name', 'N/A')}")
                if chunk['chunk_type'] == 'php_method':
                    print(f"Method: {chunk.get('method_name', 'N/A')}")
                print(f"Code Lines: {chunk['code_num_lines']} (lines {chunk['code_line_start']}-{chunk['code_line_end']})")
                print(f"Truncated: {chunk['code_truncated']}")
                print(f"\nCode Snippet Preview (first 500 chars):")
                print(chunk['code_snippet'][:500])
                print("\n" + "-" * 80)
                break
    
    print("\n✅ Done! Enhanced chunks saved to:")
    print(f"   {OUTPUT_FILE}")


if __name__ == "__main__":
    main()
