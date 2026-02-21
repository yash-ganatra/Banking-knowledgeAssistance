"""
Sync Knowledge Base — SSE Streaming Router

Provides endpoints to sync the codebase knowledge base:
  POST /api/sync/start  — Detect file changes and run ingestion pipeline (SSE stream)
  GET  /api/sync/status — Check if a sync is currently running
"""

import os
import sys
import json
import time
import asyncio
import logging
from pathlib import Path
from typing import Dict, Any, List, Set
from fastapi import APIRouter, Request
from fastapi.responses import StreamingResponse

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/api/sync", tags=["sync"])

# Project layout
PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent  # Banking-knowledgeAssistance/
CODE_ROOT = PROJECT_ROOT / "code" / "code"
MANIFEST_PATH = PROJECT_ROOT / "sync_manifest.json"

# Supported file extensions
SUPPORTED_EXTENSIONS = {".php", ".js"}
BLADE_SUFFIX = ".blade.php"

# Global sync state
_sync_running = False


def _scan_code_files() -> Dict[str, float]:
    """
    Walk CODE_ROOT and return {relative_path: mtime} for every supported
    file (.php, .js, .blade.php).
    """
    files: Dict[str, float] = {}
    if not CODE_ROOT.exists():
        logger.warning(f"Code root does not exist: {CODE_ROOT}")
        return files

    for path in CODE_ROOT.rglob("*"):
        if not path.is_file():
            continue
        name = path.name
        # Check blade first (ends with .blade.php)
        if name.endswith(BLADE_SUFFIX) or path.suffix in SUPPORTED_EXTENSIONS:
            rel = str(path.relative_to(PROJECT_ROOT))
            files[rel] = path.stat().st_mtime
    return files


def _load_manifest() -> Dict[str, float]:
    """Load the previously-saved manifest (or empty dict if none)."""
    if MANIFEST_PATH.exists():
        try:
            with open(MANIFEST_PATH, "r") as f:
                return json.load(f)
        except Exception:
            return {}
    return {}


def _save_manifest(manifest: Dict[str, float]):
    with open(MANIFEST_PATH, "w") as f:
        json.dump(manifest, f, indent=2)


def _diff_files(current: Dict[str, float], previous: Dict[str, float]):
    """Return (added, modified, deleted) lists of relative paths."""
    added = [p for p in current if p not in previous]
    deleted = [p for p in previous if p not in current]
    modified = [
        p for p in current
        if p in previous and current[p] != previous[p]
    ]
    return added, modified, deleted


def _make_event(data: dict) -> str:
    """Format a Server-Sent Event."""
    return f"data: {json.dumps(data)}\n\n"


# ── Step labels (shown in the frontend stepper) ────────────────────────
STEPS = [
    {"key": "scanning",     "label": "Scanning Files"},
    {"key": "parsing",      "label": "Parsing & Chunking"},
    {"key": "descriptions", "label": "Generating Descriptions"},
    {"key": "embedding",    "label": "Embedding to Vector DB"},
    {"key": "bm25",         "label": "Rebuilding BM25 Indices"},
    {"key": "graph",        "label": "Updating Knowledge Graph"},
]


async def _run_sync(request: Request):
    """
    Generator that drives the full sync pipeline and yields SSE events.
    """
    global _sync_running
    _sync_running = True

    def event(step_key: str, step_index: int, **extra):
        payload = {
            "step": step_key,
            "step_index": step_index,
            "total_steps": len(STEPS),
            **extra,
        }
        return _make_event(payload)

    try:
        # ── Step 0 — Scanning ──────────────────────────────────────
        yield event("scanning", 0, status="processing", message="Scanning code directory for changes…")
        await asyncio.sleep(0)  # yield control

        current_files = _scan_code_files()
        previous_files = _load_manifest()
        added, modified, deleted = _diff_files(current_files, previous_files)

        total_files = len(added) + len(modified) + len(deleted)
        summary = {
            "files_added": len(added),
            "files_modified": len(modified),
            "files_deleted": len(deleted),
            "total_files": total_files,
        }

        yield event("scanning", 0, status="complete",
                     message=f"Found {len(added)} new, {len(modified)} modified, {len(deleted)} deleted files",
                     **summary)
        await asyncio.sleep(0)

        if total_files == 0:
            yield _make_event({"step": "complete", "status": "complete",
                               "message": "Knowledge base is already up to date!", **summary})
            _sync_running = False
            return

        # Prepare absolute paths for ingestion
        files_to_ingest = added + modified
        files_to_delete = deleted

        # We need to import the ingestion functions — done lazily so the
        # import cost doesn't hit the web server on every request.
        # We run the heavy CPU / IO work via asyncio.to_thread so it
        # doesn't block the event loop.
        from ingestion.ingest_code_file import ingest_file, delete_file_knowledge

        processed = 0

        # ── Delete removed files first ────────────────────────────
        for rel_path in files_to_delete:
            if await request.is_disconnected():
                _sync_running = False
                return

            abs_path = str(PROJECT_ROOT / rel_path)
            processed += 1

            yield event("parsing", 1, status="processing",
                        current_file=rel_path, file_index=processed,
                        message=f"Deleting knowledge for {Path(rel_path).name}…",
                        **summary)
            await asyncio.sleep(0)

            try:
                await asyncio.to_thread(delete_file_knowledge, abs_path)
            except Exception as e:
                logger.error(f"Error deleting {rel_path}: {e}")
                yield event("parsing", 1, status="error",
                            current_file=rel_path, file_index=processed,
                            message=f"Error deleting {Path(rel_path).name}: {str(e)[:100]}",
                            **summary)
                await asyncio.sleep(0)

        # ── Ingest added / modified files ─────────────────────────
        # We run the pipeline per-file and emit events at each milestone.
        # The ingest_file function handles steps 2-4 internally so we
        # wrap it in a helper that yields events per file.

        affected_file_types: Set[str] = set()

        for idx, rel_path in enumerate(files_to_ingest):
            if await request.is_disconnected():
                _sync_running = False
                return

            abs_path = str(PROJECT_ROOT / rel_path)
            file_name = Path(rel_path).name
            processed = len(files_to_delete) + idx + 1

            # Detect type for tracking
            if file_name.endswith(".blade.php"):
                affected_file_types.add("blade")
            elif file_name.endswith(".php"):
                affected_file_types.add("php")
            elif file_name.endswith(".js"):
                affected_file_types.add("js")

            # Step 1 — Parsing
            yield event("parsing", 1, status="processing",
                        current_file=rel_path, file_index=processed,
                        message=f"Parsing & chunking {file_name}…",
                        **summary)
            await asyncio.sleep(0)

            # Step 2 — Descriptions (emitted before calling ingest_file
            # which does parse + describe + embed in one shot)
            yield event("descriptions", 2, status="processing",
                        current_file=rel_path, file_index=processed,
                        message=f"Generating descriptions for {file_name}…",
                        **summary)
            await asyncio.sleep(0)

            # Step 3 — Embedding
            yield event("embedding", 3, status="processing",
                        current_file=rel_path, file_index=processed,
                        message=f"Embedding {file_name} to vector DB…",
                        **summary)
            await asyncio.sleep(0)

            # Actually run the ingestion (parse → describe → embed)
            try:
                result = await asyncio.to_thread(
                    ingest_file,
                    file_path=abs_path,
                    project_root=str(CODE_ROOT),
                    rebuild_bm25=False,  # we do it in bulk after
                )
                if result.get("status") == "error":
                    yield event("embedding", 3, status="error",
                                current_file=rel_path, file_index=processed,
                                message=f"Ingestion error for {file_name}: {'; '.join(result.get('errors', []))}",
                                **summary)
                    await asyncio.sleep(0)
                else:
                    yield event("embedding", 3, status="file_complete",
                                current_file=rel_path, file_index=processed,
                                chunks_embedded=result.get("chunks_embedded", 0),
                                message=f"Embedded {result.get('chunks_embedded', 0)} chunks from {file_name}",
                                **summary)
                    await asyncio.sleep(0)
            except Exception as e:
                logger.error(f"Ingestion failed for {rel_path}: {e}")
                yield event("embedding", 3, status="error",
                            current_file=rel_path, file_index=processed,
                            message=f"Error ingesting {file_name}: {str(e)[:120]}",
                            **summary)
                await asyncio.sleep(0)

        # ── Step 4 — Rebuild BM25 indices ─────────────────────────
        yield event("bm25", 4, status="processing",
                    message="Rebuilding BM25 indices for affected sources…",
                    **summary)
        await asyncio.sleep(0)

        try:
            from scripts.build_bm25_indices import build_from_chromadb

            source_map = {"php": "php_code", "js": "js_code", "blade": "blade_templates"}
            for ft in affected_file_types:
                source_name = source_map.get(ft)
                if source_name:
                    await asyncio.to_thread(build_from_chromadb, source_name)

            yield event("bm25", 4, status="complete",
                        message="BM25 indices rebuilt successfully",
                        **summary)
            await asyncio.sleep(0)
        except Exception as e:
            logger.error(f"BM25 rebuild failed: {e}")
            yield event("bm25", 4, status="error",
                        message=f"BM25 rebuild error: {str(e)[:120]}",
                        **summary)
            await asyncio.sleep(0)

        # ── Step 5 — Rebuild knowledge graph ──────────────────────
        yield event("graph", 5, status="processing",
                    message="Rebuilding Neo4j knowledge graph…",
                    **summary)
        await asyncio.sleep(0)

        try:
            from scripts.build_graph import main as build_graph_main
            await asyncio.to_thread(build_graph_main)

            yield event("graph", 5, status="complete",
                        message="Knowledge graph updated successfully",
                        **summary)
            await asyncio.sleep(0)
        except Exception as e:
            logger.error(f"Graph build failed: {e}")
            yield event("graph", 5, status="error",
                        message=f"Graph build error: {str(e)[:120]}",
                        **summary)
            await asyncio.sleep(0)

        # ── Save manifest ─────────────────────────────────────────
        # Remove deleted files, update all ingested files
        new_manifest = {k: v for k, v in current_files.items() if k not in deleted}
        _save_manifest(new_manifest)

        yield _make_event({
            "step": "complete",
            "status": "complete",
            "message": f"Sync complete! Processed {total_files} files.",
            **summary,
        })

    except Exception as e:
        logger.error(f"Sync failed: {e}", exc_info=True)
        yield _make_event({"step": "error", "status": "error",
                           "message": f"Sync failed: {str(e)[:200]}"})
    finally:
        _sync_running = False


# ── Endpoints ─────────────────────────────────────────────────────────

@router.post("/start")
async def start_sync(request: Request):
    """
    Start a knowledge base sync.  Returns an SSE stream with real-time
    progress events.
    """
    if _sync_running:
        return StreamingResponse(
            iter([_make_event({"step": "error", "status": "error",
                               "message": "A sync is already in progress."})]),
            media_type="text/event-stream",
        )

    return StreamingResponse(
        _run_sync(request),
        media_type="text/event-stream",
        headers={
            "Cache-Control": "no-cache",
            "Connection": "keep-alive",
            "X-Accel-Buffering": "no",
        },
    )


@router.get("/status")
async def sync_status():
    """Check whether a sync is currently running."""
    return {"running": _sync_running}
