#!/usr/bin/env python3
"""
Chunk Laravel Blade Views
Recursively scans code/code/resources/views for .blade.php files and chunks them by section.
"""

import os
import re
import json
import hashlib
from pathlib import Path

# Configuration
BASE_DIR = Path(__file__).parent.parent
VIEWS_DIR = BASE_DIR / "code/code/resources/views"
OUTPUT_FILE = BASE_DIR / "chunks/blade_views_raw.json"

def get_file_content(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            return f.read()
    except Exception as e:
        print(f"Error reading {file_path}: {e}")
        return None

def extract_metadata(content):
    metadata = {
        "extends": None,
        "sections": [],
        "includes": [],
        "has_form": False,
        "form_actions": []
    }
    
    # Extract @extends
    extends_match = re.search(r"@extends\s*\(['\"](.*?)['\"]\)", content)
    if extends_match:
        metadata['extends'] = extends_match.group(1)
        
    # Extract @includes
    metadata['includes'] = re.findall(r"@include\s*\(['\"](.*?)['\"]\)", content)
    
    # Check for forms and actions
    if "<form" in content:
        metadata['has_form'] = True
        # Try to extract actions
        actions = re.findall(r"<form[^>]*action=['\"](.*?)['\"]", content)
        metadata['form_actions'] = actions
        
    return metadata

def chunk_blade_template(content, file_path, relative_path):
    chunks = []
    base_metadata = extract_metadata(content)
    filename = os.path.basename(file_path)
    
    # 1. Handle Block Sections: @section('key') ... @endsection
    block_pattern = r"@section\s*\(['\"]([^,]*?)['\"]\)(.*?)@endsection"
    block_sections = re.findall(block_pattern, content, re.DOTALL)
    
    if block_sections:
        for section_name, section_content in block_sections:
            cleaned_content = section_content.strip()
            if not cleaned_content: 
                continue
                
            # Check for form in this specific section
            section_has_form = "<form" in cleaned_content
            
            chunk_id_base = f"{relative_path}#section-{section_name}"
            chunk_id = hashlib.md5(chunk_id_base.encode()).hexdigest()
            
            chunk = {
                "chunk_id": chunk_id,
                "file_name": filename,
                "file_path": relative_path,
                "chunk_type": "blade_section",
                "section_name": section_name,
                "content": f"<!-- Section: {section_name} -->\n{cleaned_content}",
                "metadata": {
                    "source": relative_path,
                    "extends": base_metadata['extends'],
                    "includes": base_metadata['includes'], # Might be over-inclusive, but safe
                    "has_form": section_has_form
                }
            }
            chunks.append(chunk)
            base_metadata['sections'].append(section_name)
    
    # 2. If no block sections found, or significant content outside sections, 
    # check for top-level content. For simplicity in this version, if no sections, chunk whole file.
    if not block_sections:
        chunk_id = hashlib.md5(f"{relative_path}#full".encode()).hexdigest()
        chunks.append({
            "chunk_id": chunk_id,
            "file_name": filename,
            "file_path": relative_path,
            "chunk_type": "blade_full",
            "section_name": "full_template",
            "content": content.strip(),
            "metadata": {
                "source": relative_path,
                "extends": base_metadata['extends'],
                "includes": base_metadata['includes'],
                "has_form": base_metadata['has_form']
            }
        })
        
    return chunks

def main():
    print(f"🚀 Starting Blade View Chunking...")
    print(f"📂 Scanning: {VIEWS_DIR}")
    
    if not VIEWS_DIR.exists():
        print(f"❌ Error: Views directory not found at {VIEWS_DIR}")
        return

    all_chunks = []
    files_processed = 0
    
    for file_path in VIEWS_DIR.rglob("*.blade.php"):
        relative_path = str(file_path.relative_to(BASE_DIR))
        content = get_file_content(file_path)
        
        if content:
            file_chunks = chunk_blade_template(content, file_path, relative_path)
            all_chunks.extend(file_chunks)
            files_processed += 1
            
    print(f"✅ Processed {files_processed} files")
    print(f"🧩 Generated {len(all_chunks)} chunks")
    
    # Save to JSON
    OUTPUT_FILE.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(all_chunks, f, indent=2)
        
    print(f"💾 Saved to: {OUTPUT_FILE}")

if __name__ == "__main__":
    main()
