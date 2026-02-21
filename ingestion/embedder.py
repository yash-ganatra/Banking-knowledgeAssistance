#!/usr/bin/env python3
"""
Embedder for the Ingestion Pipeline

Handles:
1. Text preparation (matching EXACTLY the same composite text format per file type)
2. Embedding generation using BAAI/bge-m3
3. Upserting into the correct ChromaDB collection
4. Deleting stale chunks for a file before re-ingesting

Uses the EXACT same:
- Text preparation as embed_blade_chunks.py, embed_js_chunk_to_chromadb.py, reembed_php_with_code.py
- Metadata flattening as the original embedding scripts
- Collection names and DB paths as main.py
"""

import json
import os
import hashlib
import logging
from pathlib import Path
from typing import List, Dict, Any, Optional

import chromadb
from chromadb.config import Settings

logger = logging.getLogger(__name__)

# =============================================================================
# CONFIGURATION — matches main.py startup paths EXACTLY
# =============================================================================
PROJECT_ROOT = Path(__file__).parent.parent
VECTOR_DB_ROOT = PROJECT_ROOT / "vector_db"

# Collection configs matching main.py engine initialization
COLLECTION_CONFIGS = {
    "php": {
        "db_path": str(VECTOR_DB_ROOT / "php_vector_db"),
        "collection_name": "php_code_chunks",
        "metadata": {
            "description": "PHP code chunks with descriptions and code snippets",
            "embedding_model": "BAAI/bge-m3",
            "includes_code": "true",
        },
    },
    "js": {
        "db_path": str(VECTOR_DB_ROOT / "js_chroma_db"),
        "collection_name": "js_code_knowledge",
        "metadata": {
            "description": "JavaScript code with enhanced descriptions",
            "embedding_model": "BAAI/bge-m3",
        },
    },
    "blade": {
        "db_path": str(VECTOR_DB_ROOT / "blade_views_chroma_db"),
        "collection_name": "blade_views_knowledge",
        "metadata": {"embedding_model": "BAAI/bge-m3"},
    },
}


# =============================================================================
# TEXT PREPARATION — matches existing embedding scripts EXACTLY
# =============================================================================

def prepare_php_text(chunk: Dict) -> str:
    """
    Prepare text for PHP chunk embedding.
    Matches EXACTLY: reembed_php_with_code.py -> PHPChunksReEmbedder.prepare_chunk_text()
    """
    chunk_type = chunk.get("chunk_type", "")
    text_parts = []

    if chunk_type == "php_class":
        text_parts.append(f"Class: {chunk.get('class_name', '')}")
        text_parts.append(f"Description: {chunk.get('class_description', '')}")
        text_parts.append(f"File: {chunk.get('file_path', '')}")

        methods = chunk.get("methods", [])
        if methods:
            text_parts.append(f"Methods: {', '.join(methods)}")

        dependencies = chunk.get("dependencies", [])
        if dependencies:
            text_parts.append(f"Dependencies: {', '.join(dependencies)}")

        code_snippet = chunk.get("code_snippet")
        if code_snippet:
            text_parts.append(f"\nCode:\n{code_snippet}")

    elif chunk_type == "php_method":
        text_parts.append(
            f"Method: {chunk.get('class_name', '')}.{chunk.get('method_name', '')}"
        )
        text_parts.append(
            f"Description: {chunk.get('method_description', '')}"
        )
        text_parts.append(f"Parameters: {chunk.get('parameters', '')}")
        text_parts.append(f"Returns: {chunk.get('return_type', '')}")
        text_parts.append(f"File: {chunk.get('file_path', '')}")

        code_snippet = chunk.get("code_snippet")
        if code_snippet:
            text_parts.append(f"\nCode:\n{code_snippet}")

    return "\n".join(text_parts)


def prepare_js_text(chunk: Dict) -> str:
    """
    Prepare text for JS chunk embedding.
    Matches EXACTLY: embed_js_chunk_to_chromadb.py -> create_chunk_text()
    """
    parts = []

    if chunk.get("file_name"):
        parts.append(f"File: {chunk['file_name']}")

    if chunk.get("function_name"):
        parts.append(f"Function: {chunk['function_name']}")

    if chunk.get("endpoint_url"):
        parts.append(f"Endpoint: {chunk['endpoint_url']}")

    if chunk.get("description"):
        parts.append(f"Description: {chunk['description']}")

    if chunk.get("code_snippet"):
        parts.append(f"Code:\n{chunk['code_snippet']}")

    if chunk.get("parameters"):
        params = ", ".join(chunk["parameters"])
        parts.append(f"Parameters: {params}")

    if chunk.get("parent_file_dom_selectors"):
        selectors = ", ".join(chunk["parent_file_dom_selectors"][:5])
        parts.append(f"DOM Selectors: {selectors}")

    return "\n\n".join(parts)


def prepare_blade_text(chunk: Dict) -> str:
    """
    Prepare text for Blade chunk embedding.
    Blade embeds DESCRIPTIONS only (not code). Code is stored as the document.
    Matches EXACTLY: embed_blade_chunks.py logic.
    
    Returns tuple: (text_to_embed, document_to_store)
    But since we handle blade specially in the upsert, we return the description.
    """
    return chunk.get("description", "")


# =============================================================================
# METADATA PREPARATION — matches existing embedding scripts EXACTLY
# =============================================================================

def prepare_php_metadata(chunk: Dict) -> Dict:
    """
    Prepare metadata for PHP chunk.
    Matches EXACTLY: reembed_php_with_code.py -> PHPChunksReEmbedder.prepare_metadata()
    """
    metadata = {}

    for key in ["chunk_id", "chunk_type", "language", "file_path"]:
        if key in chunk and chunk[key] is not None:
            metadata[key] = str(chunk[key])
        elif key in chunk:
            metadata[key] = ""

    chunk_type = chunk.get("chunk_type", "")

    if chunk_type == "php_class":
        metadata["class_name"] = chunk.get("class_name") or ""
        metadata["num_methods"] = int(chunk.get("num_methods") or 0)
        metadata["methods"] = ", ".join(chunk.get("methods") or [])
        metadata["dependencies"] = ", ".join(chunk.get("dependencies") or [])

    elif chunk_type == "php_method":
        metadata["class_name"] = chunk.get("class_name") or ""
        metadata["method_name"] = chunk.get("method_name") or ""
        metadata["return_type"] = chunk.get("return_type") or ""
        params = chunk.get("parameters")
        metadata["parameters"] = str(params) if params is not None else ""

    if chunk.get("code_snippet"):
        metadata["has_code"] = True
        metadata["code_num_lines"] = int(chunk.get("code_num_lines") or 0)
        metadata["code_line_start"] = int(chunk.get("code_line_start") or 0)
        metadata["code_line_end"] = int(chunk.get("code_line_end") or 0)
    else:
        metadata["has_code"] = False

    return metadata


def prepare_js_metadata(chunk: Dict) -> Dict:
    """
    Prepare metadata for JS chunk.
    Matches EXACTLY: embed_js_chunk_to_chromadb.py -> metadata preparation in embed_chunks()
    """
    metadata = {
        "chunk_type": chunk.get("chunk_type", ""),
        "file_name": chunk.get("file_name", ""),
        "file_path": chunk.get("file_path", ""),
        "function_name": chunk.get("function_name", ""),
        "feature_domain": chunk.get("feature_domain", ""),
        "endpoint_url": chunk.get("endpoint_url", ""),
        "has_code": "yes" if chunk.get("code_snippet") else "no",
        "line_start": chunk.get("line_start", 0),
        "line_end": chunk.get("line_end", 0),
        "code_lines": chunk.get("code_lines", 0),
        "description_enhanced": "yes" if chunk.get("description_enhanced") else "no",
    }

    if chunk.get("parameters"):
        metadata["parameters"] = json.dumps(chunk["parameters"])

    return metadata


def prepare_blade_metadata(chunk: Dict) -> Dict:
    """
    Prepare metadata for Blade chunk.
    Matches EXACTLY: embed_blade_chunks.py -> batch_metadatas preparation
    """
    meta = chunk.get("metadata", {}).copy()
    meta["file_name"] = chunk.get("file_name", "")
    meta["section"] = chunk.get("section_name", "")
    meta["description"] = chunk.get("description", "")

    # ChromaDB metadata must be flat primitives
    clean_meta = {}
    for k, v in meta.items():
        if isinstance(v, (str, int, float, bool)):
            clean_meta[k] = v
        else:
            clean_meta[k] = str(v)
    return clean_meta


# =============================================================================
# CHUNK ID GENERATION — matches existing scripts
# =============================================================================

def get_php_chunk_id(chunk: Dict) -> str:
    """PHP chunks already have chunk_id from the chunker."""
    return chunk["chunk_id"]


def get_js_chunk_id(chunk: Dict, index: int = 0) -> str:
    """
    Matches: embed_js_chunk_to_chromadb.py -> create_chunk_id()
    """
    base = (
        f"{index}_{chunk.get('file_name', '')}_"
        f"{chunk.get('chunk_type', '')}_"
        f"{chunk.get('function_name', '')}_"
        f"{chunk.get('endpoint_url', '')}_"
        f"{chunk.get('line_start', 0)}"
    )
    return hashlib.md5(base.encode()).hexdigest()


def get_blade_chunk_id(chunk: Dict) -> str:
    """Blade chunks already have chunk_id from the chunker."""
    return chunk["chunk_id"]


# =============================================================================
# EMBEDDING MODEL
# =============================================================================

_model = None


def get_embedding_model():
    """
    Lazy-load the BAAI/bge-m3 model (singleton).
    Uses FlagEmbedding for consistency with existing embedding scripts.
    """
    global _model
    if _model is None:
        logger.info("🤖 Loading BAAI/bge-m3 embedding model...")
        try:
            from FlagEmbedding import BGEM3FlagModel
            import torch

            use_fp16 = torch.cuda.is_available()
            device = "cuda" if use_fp16 else "cpu"
            _model = BGEM3FlagModel("BAAI/bge-m3", use_fp16=use_fp16, device=device)
            logger.info(f"✅ BGE-M3 loaded on {device.upper()}")
        except ImportError:
            logger.warning(
                "FlagEmbedding not available, falling back to SentenceTransformer"
            )
            from sentence_transformers import SentenceTransformer

            _model = SentenceTransformer("BAAI/bge-m3")
            logger.info("✅ BGE-M3 loaded via SentenceTransformer")
    return _model


def encode_texts(texts: List[str]) -> List[List[float]]:
    """
    Encode texts to dense vectors using BGE-M3.
    Handles both FlagEmbedding and SentenceTransformer backends.
    """
    model = get_embedding_model()

    try:
        # FlagEmbedding API
        result = model.encode(texts, max_length=8192)
        if isinstance(result, dict):
            return result["dense_vecs"].tolist()
        return result.tolist()
    except (AttributeError, TypeError):
        # SentenceTransformer API
        return model.encode(texts, normalize_embeddings=True).tolist()


# =============================================================================
# CHROMADB UPSERT / DELETE
# =============================================================================

def get_collection(file_type: str):
    """
    Get or create the ChromaDB collection for the given file type.
    Uses get_or_create_collection to avoid destroying existing data.
    """
    config = COLLECTION_CONFIGS[file_type]
    client = chromadb.PersistentClient(
        path=config["db_path"],
        settings=Settings(anonymized_telemetry=False),
    )
    collection = client.get_or_create_collection(
        name=config["collection_name"],
        metadata=config["metadata"],
    )
    return collection


def delete_file_chunks(file_type: str, file_path: str) -> int:
    """
    Delete all existing chunks for a given file from the collection.
    This handles the 'modified file' case — remove old chunks before re-inserting.
    
    Args:
        file_type: 'php', 'js', or 'blade'
        file_path: The relative file_path value stored in chunk metadata
    
    Returns:
        Number of chunks deleted
    """
    collection = get_collection(file_type)
    count_before = collection.count()

    # Determine which metadata key to filter on
    if file_type == "blade":
        # Blade uses 'source' in metadata (the file path)
        # Also try file_name for safety
        try:
            collection.delete(where={"source": file_path})
        except Exception:
            pass
        # Also delete by file_name
        file_name = Path(file_path).name
        try:
            collection.delete(where={"file_name": file_name})
        except Exception:
            pass
    else:
        # PHP and JS use 'file_path' in metadata
        try:
            collection.delete(where={"file_path": file_path})
        except Exception as e:
            logger.warning(f"Delete by file_path failed: {e}")

    count_after = collection.count()
    deleted = count_before - count_after
    if deleted > 0:
        logger.info(f"  🗑️  Deleted {deleted} stale chunks for {file_path}")
    return deleted


def upsert_chunks(
    file_type: str,
    chunks: List[Dict[str, Any]],
) -> int:
    """
    Embed and upsert chunks into the appropriate ChromaDB collection.
    
    Args:
        file_type: 'php', 'js', or 'blade'
        chunks: List of chunk dicts (with descriptions already filled)
    
    Returns:
        Number of chunks upserted
    """
    if not chunks:
        return 0

    collection = get_collection(file_type)

    # Prepare data for each file type
    ids = []
    documents = []
    metadatas = []
    texts_to_embed = []

    for i, chunk in enumerate(chunks):
        if file_type == "php":
            chunk_id = get_php_chunk_id(chunk)
            doc_text = prepare_php_text(chunk)
            meta = prepare_php_metadata(chunk)
            embed_text = doc_text  # PHP embeds the composite text

        elif file_type == "js":
            chunk_id = get_js_chunk_id(chunk, index=i)
            doc_text = prepare_js_text(chunk)
            meta = prepare_js_metadata(chunk)
            embed_text = doc_text  # JS embeds the composite text

        elif file_type == "blade":
            chunk_id = get_blade_chunk_id(chunk)
            doc_text = chunk.get("content", "")  # Store raw code as document
            meta = prepare_blade_metadata(chunk)
            embed_text = chunk.get("description", "")  # Embed description only

            # Skip if no description (can't embed empty text meaningfully)
            if not embed_text or not embed_text.strip():
                logger.warning(
                    f"  ⚠️  Skipping blade chunk {chunk_id} — no description"
                )
                continue
        else:
            continue

        ids.append(chunk_id)
        documents.append(doc_text)
        metadatas.append(meta)
        texts_to_embed.append(embed_text)

    if not ids:
        return 0

    # Generate embeddings
    logger.info(f"  ⚡ Embedding {len(ids)} chunks...")
    embeddings = encode_texts(texts_to_embed)

    # Upsert into ChromaDB
    collection.upsert(
        ids=ids,
        embeddings=embeddings,
        documents=documents,
        metadatas=metadatas,
    )

    logger.info(
        f"  ✅ Upserted {len(ids)} chunks into {COLLECTION_CONFIGS[file_type]['collection_name']}"
    )
    return len(ids)
