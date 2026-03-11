#!/usr/bin/env python3
"""
Code File Ingestion Pipeline — Single Entry Point

Ingests a single .php, .js, or .blade.php file into the existing knowledge base.
Follows the EXACT same pipeline as the original bulk scripts:

    File → Parse → Chunk → Describe (LLM) → Embed (BGE-M3) → Upsert (ChromaDB)

Usage:
    # Ingest a single file
    python -m ingestion.ingest_code_file /path/to/code/code/app/Http/Controllers/HomeController.php

    # Ingest multiple files
    python -m ingestion.ingest_code_file file1.php file2.js file3.blade.php

    # Ingest a file and rebuild BM25 index
    python -m ingestion.ingest_code_file --rebuild-bm25 /path/to/file.php

    # Delete a file's knowledge (when file is removed from codebase)
    python -m ingestion.ingest_code_file --delete /path/to/file.php

    # Dry run (parse + chunk only, no LLM or embedding)
    python -m ingestion.ingest_code_file --dry-run /path/to/file.php
"""

import os
import sys
import json
import time
import logging
import argparse
from pathlib import Path
from typing import List, Dict, Any, Optional
from dotenv import load_dotenv

# Add project root to path
PROJECT_ROOT = Path(__file__).parent.parent
sys.path.insert(0, str(PROJECT_ROOT))

# Load .env from project root (same pattern as backend/main.py, utils/enhance_*.py)
load_dotenv(PROJECT_ROOT / ".env")

# The .env sets HF_HUB_OFFLINE=1 for the backend (avoids network calls at runtime).
# The ingestion pipeline needs HuggingFace model loading, so we unset it here.
# Must happen BEFORE any huggingface_hub import since it caches this as a module constant.
os.environ.pop("HF_HUB_OFFLINE", None)
os.environ.pop("TRANSFORMERS_OFFLINE", None)

from ingestion.chunkers import chunk_php_file, chunk_js_file, chunk_blade_file
from ingestion.description_prompts import (
    # Blade
    BLADE_DESCRIPTION_SYSTEM,
    get_blade_description_prompt,
    # JS
    JS_DESCRIPTION_SYSTEM,
    get_js_function_description_prompt,
    get_js_ajax_description_prompt,
    get_js_file_description_prompt,
    # PHP
    PHP_DESCRIPTION_SYSTEM,
    get_php_class_description_prompt,
    get_php_method_description_prompt,
)
from ingestion.embedder import (
    delete_file_chunks,
    upsert_chunks,
)

logger = logging.getLogger(__name__)

# =============================================================================
# FILE TYPE DETECTION
# =============================================================================

def detect_file_type(file_path: str) -> Optional[str]:
    """
    Detect file type from extension.
    Returns: 'php', 'js', 'blade', or None
    """
    file_path = str(file_path)
    if file_path.endswith(".blade.php"):
        return "blade"
    elif file_path.endswith(".php"):
        return "php"
    elif file_path.endswith(".js"):
        return "js"
    return None


def detect_php_subtype(file_path: str) -> str:
    """
    Detect PHP file subtype from its path to choose the right parser.
    Returns: 'controller', 'helper', or 'general'
    """
    path_str = str(file_path).replace("\\", "/")
    if "/Http/Controllers/" in path_str:
        return "controller"
    elif "/Helpers/" in path_str:
        return "helper"
    else:
        # General PHP file — use helper parser (it doesn't require extends)
        return "general"


# =============================================================================
# DESCRIPTION GENERATOR (uses Groq LLM)
# =============================================================================

class DescriptionGenerator:
    """
    Generates descriptions for chunks using the Groq API.
    Uses the EXACT same prompts as the original bulk enhancement scripts.
    """

    def __init__(self, groq_api_key: str = None, model: str = "llama-3.1-8b-instant"):
        self.api_key = groq_api_key or os.getenv("GROQ_API_KEY")
        if not self.api_key:
            raise ValueError(
                "GROQ_API_KEY not found. Set it in .env or pass via --groq-api-key"
            )
        from groq import Groq
        self.client = Groq(api_key=self.api_key)
        self.model = model
        self.delay = 2.0  # seconds between requests (rate limiting)

    def _call_llm(
        self, system_prompt: str, user_prompt: str, max_tokens: int = 400, temperature: float = 0.2
    ) -> str:
        """Make a single LLM call with retry."""
        max_retries = 3
        for attempt in range(max_retries):
            try:
                response = self.client.chat.completions.create(
                    messages=[
                        {"role": "system", "content": system_prompt},
                        {"role": "user", "content": user_prompt},
                    ],
                    model=self.model,
                    temperature=temperature,
                    max_tokens=max_tokens,
                )
                return response.choices[0].message.content.strip()
            except Exception as e:
                if "rate_limit" in str(e).lower() and attempt < max_retries - 1:
                    wait = self.delay * (attempt + 1) * 2
                    logger.warning(f"  ⏳ Rate limited, waiting {wait}s...")
                    time.sleep(wait)
                elif attempt < max_retries - 1:
                    logger.warning(f"  ⚠️  LLM error (attempt {attempt + 1}): {e}")
                    time.sleep(self.delay)
                else:
                    logger.error(f"  ❌ LLM failed after {max_retries} attempts: {e}")
                    return ""

    def generate_php_descriptions(self, chunks: List[Dict]) -> List[Dict]:
        """Generate descriptions for PHP chunks (class + method)."""
        for chunk in chunks:
            chunk_type = chunk.get("chunk_type", "")

            if chunk_type == "php_class":
                prompt = get_php_class_description_prompt(
                    class_name=chunk.get("class_name", ""),
                    file_path=chunk.get("file_path", ""),
                    method_names=chunk.get("methods", []),
                    dependencies=chunk.get("dependencies", []),
                    code_snippet=chunk.get("code_snippet"),
                )
                desc = self._call_llm(PHP_DESCRIPTION_SYSTEM, prompt, max_tokens=400)
                chunk["class_description"] = desc
                logger.info(f"  📝 Class desc: {desc[:80]}...")

            elif chunk_type == "php_method":
                prompt = get_php_method_description_prompt(
                    class_name=chunk.get("class_name", ""),
                    method_name=chunk.get("method_name", ""),
                    parameters=chunk.get("parameters", ""),
                    return_type=chunk.get("return_type", ""),
                    file_path=chunk.get("file_path", ""),
                    code_snippet=chunk.get("code_snippet"),
                )
                desc = self._call_llm(PHP_DESCRIPTION_SYSTEM, prompt, max_tokens=300)
                chunk["method_description"] = desc
                logger.info(f"  📝 Method desc ({chunk.get('method_name', '')}): {desc[:60]}...")

            time.sleep(self.delay)

        return chunks

    def generate_js_descriptions(self, chunks: List[Dict]) -> List[Dict]:
        """Generate descriptions for JS chunks."""
        for chunk in chunks:
            chunk_type = chunk.get("chunk_type", "")

            if chunk_type == "js_function":
                prompt = get_js_function_description_prompt(
                    function_name=chunk.get("function_name", ""),
                    parameters=chunk.get("parameters", []),
                    code_snippet=chunk.get("code_snippet", ""),
                    dom_selectors=chunk.get("parent_file_dom_selectors"),
                )
                desc = self._call_llm(JS_DESCRIPTION_SYSTEM, prompt, max_tokens=300)

            elif chunk_type == "blade_inline_js":
                # Inline JS function defined inside a <script> block in a Blade template.
                # Uses the same prompt as a regular JS function.
                prompt = get_js_function_description_prompt(
                    function_name=chunk.get("function_name", ""),
                    parameters=chunk.get("parameters", []),
                    code_snippet=chunk.get("code_snippet", ""),
                    dom_selectors=chunk.get("parent_file_dom_selectors"),
                )
                desc = self._call_llm(JS_DESCRIPTION_SYSTEM, prompt, max_tokens=300)

            elif chunk_type == "js_ajax_endpoint":
                prompt = get_js_ajax_description_prompt(
                    endpoint_url=chunk.get("endpoint_url", ""),
                    http_method=chunk.get("http_method", ""),
                    file_name=chunk.get("file_name", ""),
                    code_snippet=chunk.get("code_snippet", ""),
                )
                desc = self._call_llm(JS_DESCRIPTION_SYSTEM, prompt, max_tokens=200)

            elif chunk_type == "js_file":
                prompt = get_js_file_description_prompt(
                    file_name=chunk.get("file_name", ""),
                    code_snippet=chunk.get("code_snippet", ""),
                )
                desc = self._call_llm(JS_DESCRIPTION_SYSTEM, prompt, max_tokens=300)
            else:
                desc = ""

            if desc:
                chunk["description"] = desc
                chunk["description_enhanced"] = True
                logger.info(f"  📝 JS desc ({chunk.get('function_name', '')}): {desc[:60]}...")

            time.sleep(self.delay)

        return chunks

    def generate_blade_descriptions(self, chunks: List[Dict]) -> List[Dict]:
        """Generate descriptions for Blade chunks."""
        for chunk in chunks:
            prompt = get_blade_description_prompt(
                code_snippet=chunk.get("content", ""),
                filename=chunk.get("file_name", ""),
                section=chunk.get("section_name", ""),
            )
            desc = self._call_llm(
                BLADE_DESCRIPTION_SYSTEM, prompt, max_tokens=400, temperature=0.2
            )
            if desc:
                chunk["description"] = desc
                chunk["description_enhanced"] = True
                logger.info(f"  📝 Blade desc ({chunk.get('file_name', '')}): {desc[:60]}...")

            time.sleep(self.delay)

        return chunks


# =============================================================================
# MAIN PIPELINE
# =============================================================================

def ingest_file(
    file_path: str,
    project_root: str = None,
    groq_api_key: str = None,
    dry_run: bool = False,
    skip_descriptions: bool = False,
    rebuild_bm25: bool = False,
) -> Dict[str, Any]:
    """
    Full ingestion pipeline for a single code file.
    
    Args:
        file_path: Absolute path to the code file
        project_root: Root of the Laravel project (default: code/code/ in workspace)
        groq_api_key: Groq API key for description generation
        dry_run: If True, only parse and chunk — no LLM or embedding
        skip_descriptions: If True, skip LLM description generation (embed with empty descriptions)
        rebuild_bm25: If True, rebuild BM25 index for the affected source after ingestion
    
    Returns:
        Dict with ingestion results
    """
    file_path = str(Path(file_path).resolve())
    
    if project_root is None:
        project_root = str(PROJECT_ROOT / "code" / "code")

    result = {
        "file_path": file_path,
        "file_type": None,
        "chunks_created": 0,
        "chunks_deleted": 0,
        "chunks_embedded": 0,
        "status": "pending",
        "errors": [],
    }

    # Step 1: Detect file type
    file_type = detect_file_type(file_path)
    if not file_type:
        result["status"] = "error"
        result["errors"].append(f"Unsupported file type: {file_path}")
        logger.error(f"❌ Unsupported file type: {file_path}")
        return result

    result["file_type"] = file_type
    logger.info(f"\n{'='*60}")
    logger.info(f"📄 Ingesting: {Path(file_path).name} (type: {file_type})")
    logger.info(f"{'='*60}")

    # Step 2: Parse the file
    logger.info("Step 1/5: Parsing...")
    chunks = []
    inline_js_chunks: list = []  # blade inline-JS functions → routed to JS ChromaDB

    try:
        if file_type == "php":
            php_subtype = detect_php_subtype(file_path)
            
            if php_subtype == "controller":
                from parsers.controller_parser import ControllerParser
                parser = ControllerParser(project_root)
                parser_data = parser.parse_single_file(file_path)
            else:
                from parsers.helper_parser import HelperParser
                parser = HelperParser(project_root)
                parser_data = parser.parse_single_file(file_path)

            if parser_data:
                chunks = chunk_php_file(file_path, parser_data, project_root)
            else:
                logger.warning("  ⚠️  Parser returned no data (no class found?)")

        elif file_type == "js":
            from parsers.js_parser import JSParser
            parser = JSParser(project_root)
            parser_data = parser.parse_single_file(file_path)

            if parser_data:
                chunks = chunk_js_file(file_path, parser_data, project_root)
            else:
                logger.warning("  ⚠️  Parser returned no data")

        elif file_type == "blade":
            # chunk_blade_file() returns both blade-section chunks AND
            # blade_inline_js chunks (JS functions found inside <script> blocks).
            # We split them here: blade chunks go to the blade collection,
            # inline JS chunks go to the JS collection.
            all_blade_chunks = chunk_blade_file(file_path, project_root)
            inline_js_chunks = [
                c for c in all_blade_chunks if c.get("chunk_type") == "blade_inline_js"
            ]
            chunks = [
                c for c in all_blade_chunks if c.get("chunk_type") != "blade_inline_js"
            ]
            if inline_js_chunks:
                logger.info(
                    f"  ℹ️  {len(inline_js_chunks)} inline JS function(s) will be "
                    f"embedded into the JS collection"
                )

    except Exception as e:
        result["status"] = "error"
        result["errors"].append(f"Parsing failed: {str(e)}")
        logger.error(f"  ❌ Parsing failed: {e}")
        return result

    result["chunks_created"] = len(chunks)
    logger.info(f"  ✅ Created {len(chunks)} chunks")

    if not chunks:
        result["status"] = "warning"
        result["errors"].append("No chunks generated")
        return result

    if dry_run:
        result["status"] = "dry_run"
        logger.info("  🔍 Dry run — printing chunks:")
        for c in chunks:
            cid = c.get('chunk_id', c.get('function_name', c.get('endpoint_url', '?')))
            logger.info(f"    - {c.get('chunk_type', '?')}: {cid}")
        return result

    # Step 3: Generate descriptions via LLM
    if not skip_descriptions:
        logger.info("Step 2/5: Generating descriptions (Groq LLM)...")
        try:
            desc_gen = DescriptionGenerator(groq_api_key=groq_api_key)

            if file_type == "php":
                chunks = desc_gen.generate_php_descriptions(chunks)
            elif file_type == "js":
                chunks = desc_gen.generate_js_descriptions(chunks)
            elif file_type == "blade":
                chunks = desc_gen.generate_blade_descriptions(chunks)
                # Also generate descriptions for any inline JS functions found
                if inline_js_chunks:
                    logger.info(
                        f"  📝 Generating descriptions for {len(inline_js_chunks)} "
                        f"inline JS function(s) in blade file..."
                    )
                    inline_js_chunks = desc_gen.generate_js_descriptions(inline_js_chunks)

        except Exception as e:
            result["errors"].append(f"Description generation failed: {str(e)}")
            logger.error(f"  ⚠️  Description generation failed: {e}")
            logger.info("  Continuing with empty descriptions...")
    else:
        logger.info("Step 2/5: Skipping descriptions (--skip-descriptions)")

    # Step 4: Delete stale chunks for this file
    logger.info("Step 3/5: Removing stale chunks...")
    try:
        # Get the relative file path that's stored in metadata
        # This must match what's stored in existing chunks
        if chunks:
            ref_path = chunks[0].get("file_path", "")
            if file_type == "blade":
                ref_path = chunks[0].get("metadata", {}).get("source", ref_path)
            deleted = delete_file_chunks(file_type, ref_path)
            result["chunks_deleted"] = deleted

        # For blade files, also delete stale inline JS chunks from the JS collection.
        # These are keyed by the blade file's relative path stored in metadata["file_path"].
        if file_type == "blade" and inline_js_chunks:
            js_ref_path = inline_js_chunks[0].get("file_path", "")
            deleted_js = delete_file_chunks("js", js_ref_path)
            result["chunks_deleted"] = result.get("chunks_deleted", 0) + deleted_js
    except Exception as e:
        result["errors"].append(f"Delete failed: {str(e)}")
        logger.warning(f"  ⚠️  Delete failed (continuing): {e}")

    # Step 5: Embed and upsert
    logger.info("Step 4/5: Embedding and upserting to ChromaDB...")
    try:
        embedded = upsert_chunks(file_type, chunks)
        result["chunks_embedded"] = embedded

        # For blade files, also upsert inline JS chunks into the JS collection
        if file_type == "blade" and inline_js_chunks:
            embedded_inline_js = upsert_chunks("js", inline_js_chunks)
            result["chunks_embedded"] += embedded_inline_js
            logger.info(
                f"  ✅ Also embedded {embedded_inline_js} inline JS chunk(s) "
                f"into js_code_knowledge"
            )
    except Exception as e:
        result["status"] = "error"
        result["errors"].append(f"Embedding failed: {str(e)}")
        logger.error(f"  ❌ Embedding failed: {e}")
        return result

    # Step 6: Rebuild BM25 (optional)
    if rebuild_bm25:
        logger.info("Step 5/5: Rebuilding BM25 index...")
        try:
            _rebuild_bm25_for_source(file_type)
        except Exception as e:
            result["errors"].append(f"BM25 rebuild failed: {str(e)}")
            logger.warning(f"  ⚠️  BM25 rebuild failed: {e}")
    else:
        logger.info("Step 5/5: Skipping BM25 rebuild (use --rebuild-bm25 to enable)")

    result["status"] = "success"
    logger.info(f"\n✨ Ingestion complete: {result['chunks_embedded']} chunks embedded")
    return result


def delete_file_knowledge(file_path: str, project_root: str = None) -> Dict[str, Any]:
    """
    Delete all knowledge for a file that has been removed from the codebase.
    
    Removes from:
    1. ChromaDB vector store
    2. (BM25 rebuild should be done separately with --rebuild-bm25)
    
    Args:
        file_path: Path to the deleted file
        project_root: Root of the Laravel project
    
    Returns:
        Dict with deletion results
    """
    if project_root is None:
        project_root = str(PROJECT_ROOT / "code" / "code")

    file_type = detect_file_type(file_path)
    if not file_type:
        return {"status": "error", "error": f"Unsupported file type: {file_path}"}

    # Build the relative path that would be stored in metadata
    try:
        rel_path = str(Path(file_path).relative_to(Path(project_root).parent.parent))
    except ValueError:
        rel_path = str(file_path)
    rel_path = rel_path.replace("\\", "/")

    logger.info(f"🗑️  Deleting knowledge for: {rel_path}")
    deleted = delete_file_chunks(file_type, rel_path)

    return {
        "status": "success",
        "file_path": file_path,
        "file_type": file_type,
        "chunks_deleted": deleted,
    }


def _rebuild_bm25_for_source(file_type: str):
    """Rebuild BM25 index for the affected source."""
    source_map = {
        "php": "php_code",
        "js": "js_code",
        "blade": "blade_templates",
    }
    source_name = source_map.get(file_type)
    if not source_name:
        return

    try:
        from scripts.build_bm25_indices import build_from_chromadb

        success = build_from_chromadb(source_name)
        if success:
            logger.info(f"  ✅ BM25 index rebuilt for {source_name}")
        else:
            logger.warning(f"  ⚠️  BM25 index rebuild returned False for {source_name}")
    except ImportError:
        logger.warning("  ⚠️  Could not import build_bm25_indices — run manually")


# =============================================================================
# CLI
# =============================================================================

def main():
    parser = argparse.ArgumentParser(
        description="Ingest code files into the Banking Knowledge Assistant",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  # Ingest a PHP controller
  python -m ingestion.ingest_code_file code/code/app/Http/Controllers/HomeController.php

  # Ingest a JS file with BM25 rebuild
  python -m ingestion.ingest_code_file --rebuild-bm25 code/code/public/js/bank.js

  # Ingest a Blade template
  python -m ingestion.ingest_code_file code/code/resources/views/auth/login.blade.php

  # Delete a removed file's knowledge
  python -m ingestion.ingest_code_file --delete code/code/app/Http/Controllers/OldController.php

  # Dry run (no LLM, no embedding)
  python -m ingestion.ingest_code_file --dry-run code/code/app/Http/Controllers/HomeController.php

  # Skip LLM descriptions (faster, embed with empty descriptions)
  python -m ingestion.ingest_code_file --skip-descriptions code/code/public/js/bank.js
""",
    )
    parser.add_argument(
        "files",
        nargs="+",
        help="Path(s) to code files to ingest (.php, .js, .blade.php)",
    )
    parser.add_argument(
        "--project-root",
        default=None,
        help="Root of the Laravel project (default: code/code/ in workspace)",
    )
    parser.add_argument(
        "--groq-api-key",
        default=None,
        help="Groq API key (default: from GROQ_API_KEY env variable)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Parse and chunk only — no LLM calls or embedding",
    )
    parser.add_argument(
        "--skip-descriptions",
        action="store_true",
        help="Skip LLM description generation (faster, but lower quality)",
    )
    parser.add_argument(
        "--rebuild-bm25",
        action="store_true",
        help="Rebuild BM25 index for affected source(s) after ingestion",
    )
    parser.add_argument(
        "--delete",
        action="store_true",
        help="Delete knowledge for the given file(s) instead of ingesting",
    )
    parser.add_argument(
        "--verbose",
        action="store_true",
        help="Enable debug logging",
    )

    args = parser.parse_args()

    # Configure logging
    log_level = logging.DEBUG if args.verbose else logging.INFO
    logging.basicConfig(
        level=log_level,
        format="%(asctime)s - %(levelname)s - %(message)s",
        datefmt="%H:%M:%S",
    )

    print("\n" + "=" * 60)
    print("🚀 Code File Ingestion Pipeline")
    print("=" * 60)

    results = []

    for file_path in args.files:
        # Resolve to absolute path
        abs_path = str(Path(file_path).resolve())

        if args.delete:
            r = delete_file_knowledge(abs_path, project_root=args.project_root)
        else:
            r = ingest_file(
                file_path=abs_path,
                project_root=args.project_root,
                groq_api_key=args.groq_api_key,
                dry_run=args.dry_run,
                skip_descriptions=args.skip_descriptions,
                rebuild_bm25=args.rebuild_bm25,
            )
        results.append(r)

    # Print summary
    print("\n" + "=" * 60)
    print("📊 INGESTION SUMMARY")
    print("=" * 60)

    for r in results:
        status_icon = {
            "success": "✅",
            "error": "❌",
            "warning": "⚠️",
            "dry_run": "🔍",
        }.get(r["status"], "❓")

        print(f"\n{status_icon} {Path(r['file_path']).name}")
        print(f"   Type: {r.get('file_type', 'unknown')}")
        if not args.delete:
            print(f"   Chunks created: {r.get('chunks_created', 0)}")
            print(f"   Chunks deleted: {r.get('chunks_deleted', 0)}")
            print(f"   Chunks embedded: {r.get('chunks_embedded', 0)}")
        else:
            print(f"   Chunks deleted: {r.get('chunks_deleted', 0)}")
        if r.get("errors"):
            for err in r["errors"]:
                print(f"   ⚠️  {err}")

    total_success = sum(1 for r in results if r["status"] == "success")
    print(f"\n{'='*60}")
    print(f"Total: {total_success}/{len(results)} files ingested successfully")
    print("=" * 60)

    # Return non-zero exit code if any errors
    sys.exit(0 if total_success == len(results) else 1)


if __name__ == "__main__":
    main()
