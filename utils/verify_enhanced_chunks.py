#!/usr/bin/env python3
"""
Verify enhanced chunks have code snippets
"""

import json
from pathlib import Path
from collections import defaultdict

BASE_DIR = Path(__file__).parent.parent
ENHANCED_FILE = BASE_DIR / "description" / "js_file_chunks_with_code.json"

def verify_chunks():
    with open(ENHANCED_FILE, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    stats = defaultdict(int)
    chunks_with_code = []
    chunks_without_code = []
    
    for chunk in chunks:
        chunk_type = chunk.get('chunk_type')
        has_code = 'code_snippet' in chunk and chunk['code_snippet']
        
        stats['total'] += 1
        stats[chunk_type] += 1
        
        if has_code:
            stats['with_code'] += 1
            chunks_with_code.append({
                'type': chunk_type,
                'name': chunk.get('function_name') or chunk.get('file_name'),
                'lines': chunk.get('code_lines', 0)
            })
        else:
            stats['without_code'] += 1
            chunks_without_code.append({
                'type': chunk_type,
                'name': chunk.get('function_name') or chunk.get('file_name')
            })
    
    # Print report
    print("=" * 60)
    print("ENHANCED CHUNKS VERIFICATION REPORT")
    print("=" * 60)
    print(f"\nTotal Chunks: {stats['total']}")
    print(f"Chunks WITH code: {stats['with_code']} ({stats['with_code']/stats['total']*100:.1f}%)")
    print(f"Chunks WITHOUT code: {stats['without_code']} ({stats['without_code']/stats['total']*100:.1f}%)")
    
    print("\n" + "-" * 60)
    print("BREAKDOWN BY CHUNK TYPE")
    print("-" * 60)
    for chunk_type, count in sorted(stats.items()):
        if chunk_type not in ['total', 'with_code', 'without_code']:
            print(f"  {chunk_type}: {count}")
    
    # Show sample with code
    print("\n" + "-" * 60)
    print("SAMPLE CHUNKS WITH CODE (first 5)")
    print("-" * 60)
    for i, chunk in enumerate(chunks_with_code[:5]):
        print(f"{i+1}. [{chunk['type']}] {chunk['name']} - {chunk['lines']} lines")
    
    # Show chunks without code
    if chunks_without_code:
        print("\n" + "-" * 60)
        print(f"CHUNKS WITHOUT CODE ({len(chunks_without_code)} total)")
        print("-" * 60)
        for i, chunk in enumerate(chunks_without_code[:10]):
            print(f"{i+1}. [{chunk['type']}] {chunk['name']}")
        if len(chunks_without_code) > 10:
            print(f"... and {len(chunks_without_code) - 10} more")
    
    print("\n" + "=" * 60)
    
    # Check token approximation
    total_tokens = 0
    for chunk in chunks_with_code:
        # Rough estimate: 1 token ≈ 4 characters
        if chunk.get('lines'):
            total_tokens += chunk['lines'] * 80 // 4  # Avg 80 chars per line
    
    avg_tokens = total_tokens // len(chunks_with_code) if chunks_with_code else 0
    print(f"\nEstimated avg tokens per chunk with code: ~{avg_tokens}")
    print("=" * 60)

if __name__ == "__main__":
    verify_chunks()
