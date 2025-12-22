#!/usr/bin/env python3
"""
Clean code snippets in the enhanced chunks JSON file.
Removes excessive whitespace, trailing tabs, and normalizes formatting.
"""

import json
import re
from pathlib import Path

def clean_code_snippet(code):
    """
    Clean a code snippet by:
    1. Removing excessive trailing whitespace
    2. Normalizing line endings
    3. Removing trailing tabs/spaces from each line
    4. Limiting consecutive blank lines to 2
    """
    if not code:
        return code
    
    # Split into lines
    lines = code.split('\n')
    
    # Remove trailing whitespace from each line
    lines = [line.rstrip() for line in lines]
    
    # Remove excessive blank lines at the end
    while lines and not lines[-1]:
        lines.pop()
    
    # Limit consecutive blank lines to 2
    cleaned_lines = []
    blank_count = 0
    
    for line in lines:
        if not line.strip():
            blank_count += 1
            if blank_count <= 2:
                cleaned_lines.append(line)
        else:
            blank_count = 0
            cleaned_lines.append(line)
    
    return '\n'.join(cleaned_lines)


def clean_chunks(input_file, output_file):
    """
    Clean all code snippets in the chunks file.
    """
    print(f"Loading chunks from: {input_file}")
    with open(input_file, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    print(f"Total chunks: {len(chunks)}")
    
    cleaned_count = 0
    total_chars_before = 0
    total_chars_after = 0
    
    for chunk in chunks:
        if 'code_snippet' in chunk and chunk['code_snippet']:
            code_before = chunk['code_snippet']
            code_after = clean_code_snippet(code_before)
            
            if code_before != code_after:
                chunk['code_snippet'] = code_after
                cleaned_count += 1
                
                chars_before = len(code_before)
                chars_after = len(code_after)
                total_chars_before += chars_before
                total_chars_after += chars_after
                
                # Report significant cleanup
                reduction = chars_before - chars_after
                if reduction > 100:
                    print(f"  Cleaned {chunk['chunk_type']} chunk: "
                          f"{chunk.get('function_name', chunk.get('file_name', 'unknown'))}")
                    print(f"    Before: {chars_before} chars, After: {chars_after} chars "
                          f"(removed {reduction} chars)")
    
    print(f"\n{'='*60}")
    print(f"Cleaning Summary:")
    print(f"{'='*60}")
    print(f"Chunks cleaned: {cleaned_count} / {len(chunks)}")
    print(f"Total characters before: {total_chars_before:,}")
    print(f"Total characters after: {total_chars_after:,}")
    print(f"Total characters removed: {(total_chars_before - total_chars_after):,}")
    
    if total_chars_before > 0:
        reduction_pct = ((total_chars_before - total_chars_after) / total_chars_before) * 100
        print(f"Size reduction: {reduction_pct:.1f}%")
    
    print(f"\nSaving cleaned chunks to: {output_file}")
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(chunks, f, indent=2, ensure_ascii=False)
    
    print(f"✓ Cleaned chunks saved successfully!")
    print(f"\n{'='*60}")
    
    # Verify no code was lost
    chunks_with_code = sum(1 for c in chunks if c.get('code_snippet'))
    print(f"Verification: {chunks_with_code} chunks still have code")
    print(f"{'='*60}")


def main():
    # Setup paths
    base_dir = Path(__file__).parent.parent
    input_file = base_dir / "description" / "js_file_chunks_with_code.json"
    output_file = base_dir / "description" / "js_file_chunks_cleaned.json"
    
    if not input_file.exists():
        print(f"Error: Input file not found: {input_file}")
        return
    
    clean_chunks(input_file, output_file)
    
    print("\n✓ All done! Use 'js_file_chunks_cleaned.json' for embedding.")
    print("\nNext steps:")
    print("  1. python utils/verify_enhanced_chunks.py  # Verify cleaned file")
    print("  2. python utils/embed_enhanced_chunks_to_chromadb.py  # Embed cleaned chunks")


if __name__ == "__main__":
    main()
