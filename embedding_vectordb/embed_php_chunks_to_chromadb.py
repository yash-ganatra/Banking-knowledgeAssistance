import json
import chromadb
from chromadb.config import Settings
from FlagEmbedding import BGEM3FlagModel
from tqdm import tqdm
import os
from typing import List, Dict
import numpy as np

class PHPChunksEmbedder:
    """
    Embeds PHP metadata chunks into ChromaDB using BGE-M3 model.
    """
    
    def __init__(self, 
                 chroma_persist_directory: str = "./chroma_db",
                 collection_name: str = "php_code_chunks",
                 model_name: str = "BAAI/bge-m3"):
        """
        Initialize the embedder with ChromaDB and BGE-M3 model.
        
        Args:
            chroma_persist_directory: Directory to persist ChromaDB data
            collection_name: Name of the ChromaDB collection
            model_name: BGE-M3 model name
        """
        print("Initializing ChromaDB client...")
        self.client = chromadb.PersistentClient(
            path=chroma_persist_directory,
            settings=Settings(
                anonymized_telemetry=False,
                allow_reset=True
            )
        )
        
        print(f"Loading BGE-M3 model: {model_name}...")
        self.model = BGEM3FlagModel(model_name, use_fp16=True)
        
        self.collection_name = collection_name
        self.collection = None
        
    def create_or_get_collection(self, reset: bool = False):
        """
        Create or get the ChromaDB collection.
        
        Args:
            reset: If True, delete existing collection and create new one
        """
        if reset:
            try:
                self.client.delete_collection(name=self.collection_name)
                print(f"Deleted existing collection: {self.collection_name}")
            except Exception as e:
                print(f"No existing collection to delete: {e}")
        
        self.collection = self.client.get_or_create_collection(
            name=self.collection_name,
            metadata={"description": "PHP code chunks with metadata and BGE-M3 embeddings"}
        )
        print(f"Collection '{self.collection_name}' ready. Current count: {self.collection.count()}")
        
    def prepare_chunk_text(self, chunk: Dict) -> str:
        """
        Prepare the text representation of a chunk for embedding.
        Combines relevant metadata fields into a comprehensive text.
        Includes code snippet if available for better semantic matching.
        
        Args:
            chunk: Dictionary containing chunk metadata
            
        Returns:
            Formatted text string for embedding
        """
        chunk_type = chunk.get("chunk_type", "")
        
        # Base information
        text_parts = []
        
        if chunk_type == "php_class":
            text_parts.append(f"Class: {chunk.get('class_name', '')}")
            text_parts.append(f"Description: {chunk.get('class_description', '')}")
            text_parts.append(f"File: {chunk.get('file_path', '')}")
            
            methods = chunk.get('methods', [])
            if methods:
                text_parts.append(f"Methods: {', '.join(methods)}")
            
            dependencies = chunk.get('dependencies', [])
            if dependencies:
                text_parts.append(f"Dependencies: {', '.join(dependencies)}")
            
            # Add code snippet if available
            code_snippet = chunk.get('code_snippet')
            if code_snippet:
                text_parts.append(f"\nCode:\n{code_snippet}")
                
        elif chunk_type == "php_method":
            text_parts.append(f"Method: {chunk.get('class_name', '')}.{chunk.get('method_name', '')}")
            text_parts.append(f"Description: {chunk.get('method_description', '')}")
            text_parts.append(f"Parameters: {chunk.get('parameters', '')}")
            text_parts.append(f"Returns: {chunk.get('return_type', '')}")
            text_parts.append(f"File: {chunk.get('file_path', '')}")
            
            # Add code snippet if available
            code_snippet = chunk.get('code_snippet')
            if code_snippet:
                text_parts.append(f"\nCode:\n{code_snippet}")
        
        return "\n".join(text_parts)
    
    def prepare_metadata(self, chunk: Dict) -> Dict:
        """
        Prepare metadata for ChromaDB storage.
        ChromaDB requires all metadata values to be strings, ints, floats, or bools.
        
        Args:
            chunk: Dictionary containing chunk metadata
            
        Returns:
            Cleaned metadata dictionary
        """
        metadata = {}
        
        # Add basic fields
        for key in ["chunk_id", "chunk_type", "language", "file_path"]:
            if key in chunk:
                metadata[key] = str(chunk[key])
        
        # Add type-specific fields
        chunk_type = chunk.get("chunk_type", "")
        
        if chunk_type == "php_class":
            metadata["class_name"] = chunk.get("class_name", "")
            metadata["num_methods"] = int(chunk.get("num_methods", 0))
            # Store lists as comma-separated strings
            metadata["methods"] = ", ".join(chunk.get("methods", []))
            metadata["dependencies"] = ", ".join(chunk.get("dependencies", []))
            
        elif chunk_type == "php_method":
            metadata["class_name"] = chunk.get("class_name", "")
            metadata["method_name"] = chunk.get("method_name", "")
            metadata["return_type"] = chunk.get("return_type", "")
            metadata["parameters"] = str(chunk.get("parameters", ""))
        
        # Add code metadata if available
        if chunk.get("code_snippet"):
            metadata["has_code"] = True
            metadata["code_num_lines"] = int(chunk.get("code_num_lines", 0))
            metadata["code_line_start"] = int(chunk.get("code_line_start", 0))
            metadata["code_line_end"] = int(chunk.get("code_line_end", 0))
        else:
            metadata["has_code"] = False
        
        return metadata
    
    def embed_chunks(self, chunks: List[Dict], batch_size: int = 32):
        """
        Embed all chunks and add them to ChromaDB.
        
        Args:
            chunks: List of chunk dictionaries
            batch_size: Number of chunks to process at once
        """
        print(f"\nProcessing {len(chunks)} chunks...")
        
        for i in tqdm(range(0, len(chunks), batch_size), desc="Embedding batches"):
            batch = chunks[i:i + batch_size]
            
            # Prepare texts for embedding
            texts = [self.prepare_chunk_text(chunk) for chunk in batch]
            
            # Generate embeddings using BGE-M3
            embeddings = self.model.encode(
                texts,
                batch_size=batch_size,
                max_length=8192  # BGE-M3 supports up to 8192 tokens
            )['dense_vecs']
            
            # Prepare data for ChromaDB
            ids = [chunk["chunk_id"] for chunk in batch]
            metadatas = [self.prepare_metadata(chunk) for chunk in batch]
            documents = texts
            
            # Add to ChromaDB
            self.collection.add(
                ids=ids,
                embeddings=embeddings.tolist(),
                metadatas=metadatas,
                documents=documents
            )
        
        print(f"\n✓ Successfully embedded {len(chunks)} chunks into ChromaDB")
        print(f"✓ Collection '{self.collection_name}' now contains {self.collection.count()} items")
    
    def test_search(self, query: str, n_results: int = 5):
        """
        Test the embedded chunks with a search query.
        
        Args:
            query: Search query text
            n_results: Number of results to return
        """
        print(f"\n Testing search with query: '{query}'")
        
        # Encode query using BGE-M3
        query_embedding = self.model.encode(query)['dense_vecs']
        
        # Search in ChromaDB
        results = self.collection.query(
            query_embeddings=query_embedding.tolist(),
            n_results=n_results
        )
        
        print(f"\nTop {n_results} results:")
        for i, (doc, metadata, distance) in enumerate(zip(
            results['documents'][0],
            results['metadatas'][0],
            results['distances'][0]
        ), 1):
            print(f"\n{i}. Distance: {distance:.4f}")
            print(f"   Type: {metadata.get('chunk_type', 'N/A')}")
            print(f"   File: {metadata.get('file_path', 'N/A')}")
            print(f"   Preview: {doc[:200]}...")
        
        return results


def main():
    """
    Main function to load chunks and embed them into ChromaDB.
    """
    # Configuration
    CHUNKS_FILE = "php_metadata_chunks_for_chromadb.json"
    CHROMA_DIR = "./chroma_db"
    COLLECTION_NAME = "php_code_chunks"
    BATCH_SIZE = 16  # Adjust based on your GPU memory
    RESET_COLLECTION = True  # Set to True to start fresh
    
    # Load chunks
    print(f"Loading chunks from {CHUNKS_FILE}...")
    with open(CHUNKS_FILE, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    print(f"Loaded {len(chunks)} chunks")
    
    # Initialize embedder
    embedder = PHPChunksEmbedder(
        chroma_persist_directory=CHROMA_DIR,
        collection_name=COLLECTION_NAME
    )
    
    # Create/get collection
    embedder.create_or_get_collection(reset=RESET_COLLECTION)
    
    # Embed chunks
    embedder.embed_chunks(chunks, batch_size=BATCH_SIZE)
    
    # Test with sample queries
    print("\n" + "="*50)
    print("TESTING SEARCH FUNCTIONALITY")
    print("="*50)
    
    test_queries = [
        "How does authentication work?",
        "API queue management",
        "User interface forms and validation"
    ]
    
    for query in test_queries:
        embedder.test_search(query, n_results=3)
        print("\n" + "-"*50)


if __name__ == "__main__":
    main()
