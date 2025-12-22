#!/usr/bin/env python3
"""
Optimize chunk sizes for embedding models
Splits large code snippets if needed
"""

import json
from pathlib import Path

BASE_DIR = Path(__file__).parent.parent
INPUT_FILE = BASE_DIR / "description" / "js_file_chunks_with_code.json"
OUTPUT_FILE = BASE_DIR / "description" / "js_file_chunks_optimized.json"

# Token limits (adjust based on your embedding model)
MAX_TOKENS_PER_CHUNK = 512  # Common limit for many models
CHARS_PER_TOKEN = 4  # Rough estimate

def estimate_tokens(text):
    """Estimate token count"""
    return len(text) // CHARS_PER_TOKEN

def split_large_code(chunk, max_tokens=MAX_TOKENS_PER_CHUNK):
    """
    Split chunk if code is too large
    Returns list of chunks
    """
    code = chunk.get('code_snippet', '')
    
    if not code:
        return [chunk]
    
    estimated_tokens = estimate_tokens(code)
    
    # If within limit, return as-is
    if estimated_tokens <= max_tokens:
        return [chunk]
    
    print(f"Splitting large chunk: {chunk.get('function_name')} ({estimated_tokens} tokens)")
    
    # Split code into smaller parts
    lines = code.split('\n')
    max_lines = int(max_tokens * CHARS_PER_TOKEN / 80)  # Assuming 80 chars per line
    
    chunks = []
    for i in range(0, len(lines), max_lines):
        part_lines = lines[i:i+max_lines]
        part_code = '\n'.join(part_lines)
        
        # Create new chunk
        new_chunk = chunk.copy()
        new_chunk['code_snippet'] = part_code
        new_chunk['is_partial'] = True
        new_chunk['part_number'] = len(chunks) + 1
        new_chunk['line_start'] = chunk.get('line_start', 0) + i
        new_chunk['line_end'] = chunk.get('line_start', 0) + i + len(part_lines)
        
        # Add part indicator to description
        if new_chunk.get('description'):
            new_chunk['description'] = f"[Part {len(chunks)+1}] {new_chunk['description']}"
        
        chunks.append(new_chunk)
    
    return chunks

def optimize_chunks():
    """
    Optimize all chunks
    """
    print("Loading chunks...")
    with open(INPUT_FILE, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    print(f"Found {len(chunks)} chunks")
    
    optimized = []
    split_count = 0
    
    for chunk in chunks:
        result = split_large_code(chunk)
        optimized.extend(result)
        
        if len(result) > 1:
            split_count += 1
    
    print(f"\nOptimization complete:")
    print(f"  Original chunks: {len(chunks)}")
    print(f"  Optimized chunks: {len(optimized)}")
    print(f"  Chunks split: {split_count}")
    
    # Save
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(optimized, f, indent=2, ensure_ascii=False)
    
    print(f"\nSaved to: {OUTPUT_FILE}")
    
    # Statistics
    token_counts = [estimate_tokens(c.get('code_snippet', '')) for c in optimized if c.get('code_snippet')]
    if token_counts:
        print(f"\nToken statistics:")
        print(f"  Max tokens: {max(token_counts)}")
        print(f"  Avg tokens: {sum(token_counts)//len(token_counts)}")
        print(f"  Chunks > {MAX_TOKENS_PER_CHUNK} tokens: {sum(1 for t in token_counts if t > MAX_TOKENS_PER_CHUNK)}")

if __name__ == "__main__":
    optimize_chunks()
