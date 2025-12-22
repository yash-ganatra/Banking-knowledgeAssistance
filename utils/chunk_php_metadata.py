import json
from pathlib import Path
from typing import List, Dict, Any


def extract_related_classes(imports: List[str]) -> List[str]:
    """Extract class names from imports."""
    related = []
    for imp in imports:
        # Get the last part after the last backslash
        parts = imp.split('\\')
        if parts:
            class_name = parts[-1]
            related.append(class_name)
    return related


def create_method_chunk(
    file_data: Dict[str, Any],
    class_data: Dict[str, Any],
    method_data: Dict[str, Any],
    chunk_id: str
) -> Dict[str, Any]:
    """Create a method-level chunk."""
    
    # Extract parameters
    params = method_data.get('parameters', '')
    if params and params != 'null':
        params = params
    else:
        params = ''
    
    # Build return type
    return_type = method_data.get('return_type', 'void')
    if return_type == 'null':
        return_type = 'void'
    
    # Extract related classes from imports
    related_classes = extract_related_classes(file_data.get('imports', []))
    
    # Build the chunk with flat structure
    chunk = {
        'chunk_id': chunk_id,
        'chunk_type': 'php_method',
        'language': 'php',
        'file_path': file_data['file_path'],
        'class_name': class_data['name'],
        'method_name': method_data['name'],
        'parameters': params,
        'return_type': return_type,
        'method_description': method_data.get('description', 'N/A'),
        'dependencies': file_data.get('imports', []),
        'related_classes': related_classes
    }
    
    return chunk


def create_class_chunk(
    file_data: Dict[str, Any],
    class_data: Dict[str, Any],
    chunk_id: str
) -> Dict[str, Any]:
    """Create a class-level chunk for overview queries."""
    
    # Get all method names
    method_names = [m['name'] for m in class_data.get('methods', [])]
    
    # Extract related classes from imports
    related_classes = extract_related_classes(file_data.get('imports', []))
    
    # Build the chunk with flat structure
    chunk = {
        'chunk_id': chunk_id,
        'chunk_type': 'php_class',
        'language': 'php',
        'file_path': file_data['file_path'],
        'class_name': class_data['name'],
        'class_description': class_data.get('description', 'N/A'),
        'methods': method_names,
        'num_methods': len(method_names),
        'dependencies': file_data.get('imports', []),
        'related_classes': related_classes
    }
    
    return chunk


def chunk_php_metadata(input_file: str, output_file: str):
    """
    Transform PHP metadata into method-level chunks for ChromaDB.
    
    Args:
        input_file: Path to php_metadata_with_descriptions.json
        output_file: Path to save chunked output
    """
    print(f"Loading PHP metadata from: {input_file}")
    
    with open(input_file, 'r', encoding='utf-8') as f:
        php_data = json.load(f)
    
    chunks = []
    chunk_counter = 1
    
    print(f"Processing {len(php_data)} files...")
    
    for file_data in php_data:
        for class_data in file_data.get('classes', []):
            
            # Create class-level chunk
            class_chunk_id = f"php_class_{chunk_counter}"
            class_chunk = create_class_chunk(file_data, class_data, class_chunk_id)
            chunks.append(class_chunk)
            chunk_counter += 1
            
            # Create method-level chunks
            for method_data in class_data.get('methods', []):
                method_chunk_id = f"php_method_{chunk_counter}"
                method_chunk = create_method_chunk(
                    file_data,
                    class_data,
                    method_data,
                    method_chunk_id
                )
                chunks.append(method_chunk)
                chunk_counter += 1
    
    print(f"\nGenerated {len(chunks)} chunks:")
    print(f"  - Method-level chunks: {sum(1 for c in chunks if c['chunk_type'] == 'php_method')}")
    print(f"  - Class-level chunks: {sum(1 for c in chunks if c['chunk_type'] == 'php_class')}")
    
    # Save to file
    print(f"\nSaving chunks to: {output_file}")
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(chunks, f, indent=2, ensure_ascii=False)
    
    print(f"✓ Chunking complete!")
    print(f"Total chunks: {len(chunks)}")
    
    return chunks


def preview_chunks(chunks: List[Dict[str, Any]], num_samples: int = 3):
    """Preview sample chunks."""
    print(f"\n=== Previewing {num_samples} sample chunks ===\n")
    
    for i, chunk in enumerate(chunks[:num_samples]):
        print(f"--- Chunk {i+1}: {chunk['chunk_id']} ---")
        print(f"Type: {chunk['chunk_type']}")
        print(f"\nChunk keys: {list(chunk.keys())}")
        print(f"\nSample data:")
        for key, value in list(chunk.items())[:5]:
            if isinstance(value, str) and len(value) > 100:
                print(f"  {key}: {value[:100]}...")
            elif isinstance(value, list) and len(value) > 3:
                print(f"  {key}: {value[:3]}... ({len(value)} items)")
            else:
                print(f"  {key}: {value}")
        print("\n" + "="*60 + "\n")


if __name__ == "__main__":
    # File paths
    input_file = "php_metadata_with_descriptions.json"
    output_file = "php_metadata_chunks_for_chromadb.json"
    
    # Run chunking
    chunks = chunk_php_metadata(input_file, output_file)
    
    # Preview samples
    preview_chunks(chunks, num_samples=3)
    
    print("\n" + "="*50)
    print("Next steps:")
    print("1. Review the generated chunks in:", output_file)
    print("2. Use these chunks to ingest into ChromaDB")
    print("3. Use individual keys for filtering")
    print("="*50)
    
    print("\n" + "="*50)
    print("Next steps:")
    print("1. Review the generated chunks in:", output_file)
    print("2. Use these chunks to ingest into ChromaDB")
    print("3. The 'content' field is for embedding")
    print("4. The 'metadata' field is for filtering")
    print("="*50)