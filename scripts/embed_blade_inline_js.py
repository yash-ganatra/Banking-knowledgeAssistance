#!/usr/bin/env python3
"""
Scan ALL blade files for inline <script> JS functions,
identify which are NOT in the JS / Blade ChromaDB collections,
and embed only the missing ones incrementally.

Usage:
    cd Banking-knowledgeAssistance
    venv/bin/python3 scripts/embed_blade_inline_js.py
"""
import hashlib
import json
import re
import sys
from collections import defaultdict
from pathlib import Path

import chromadb
from chromadb.config import Settings
from FlagEmbedding import BGEM3FlagModel

ROOT = Path(__file__).parent.parent
VDB = ROOT / "vector_db"
VIEWS_DIR = ROOT / "code" / "code" / "resources" / "views"

FUNC_PATTERN = re.compile(r"function\s+([a-zA-Z_]\w+)\s*\(([^)]*)\)")


# ────────────────────────────────────────────────────────────────────
# 1. Extract every named function definition inside <script> blocks
# ────────────────────────────────────────────────────────────────────
def extract_blade_inline_functions():
    """Return dict: func_name -> {params, code, files: [(rel_path, line)]}"""
    functions = {}

    for blade_file in sorted(VIEWS_DIR.rglob("*.blade.php")):
        rel = str(blade_file.relative_to(ROOT))
        with open(blade_file, "r", encoding="utf-8", errors="ignore") as f:
            lines = f.readlines()

        in_script = False
        for lineno, line in enumerate(lines, 1):
            if "<script" in line.lower():
                in_script = True
            if "</script" in line.lower():
                in_script = False
            if not in_script:
                continue

            for m in FUNC_PATTERN.finditer(line):
                fname = m.group(1)
                params = m.group(2).strip()

                # Extract function body (follow brace depth, max 60 lines)
                body_lines = [lines[lineno - 1]]
                brace_count = line.count("{") - line.count("}")
                for i in range(lineno, min(lineno + 60, len(lines))):
                    body_lines.append(lines[i])
                    brace_count += lines[i].count("{") - lines[i].count("}")
                    if brace_count <= 0:
                        break
                code = "".join(body_lines).strip()

                if fname not in functions:
                    functions[fname] = {
                        "params": params,
                        "code": code,
                        "files": [],
                    }
                functions[fname]["files"].append((rel, lineno))

    return functions


# ────────────────────────────────────────────────────────────────────
# 2. Gather existing function names from both ChromaDB collections
# ────────────────────────────────────────────────────────────────────
def get_existing_function_names():
    existing = set()

    for db_name, col_name in [
        ("js_chroma_db", "js_code_knowledge"),
        ("blade_views_chroma_db", "blade_views_knowledge"),
    ]:
        try:
            client = chromadb.PersistentClient(
                path=str(VDB / db_name),
                settings=Settings(anonymized_telemetry=False),
            )
            col = client.get_collection(col_name)
            metas = col.get(include=["metadatas"])["metadatas"]
            for m in metas:
                fn = m.get("function_name", "")
                if fn:
                    existing.add(fn)
        except Exception as e:
            print(f"  ⚠  Could not read {db_name}/{col_name}: {e}")

    return existing


# ────────────────────────────────────────────────────────────────────
# 3. Embed missing functions
# ────────────────────────────────────────────────────────────────────
def build_description_text(fname, params, code, files):
    """
    Build a short natural-language description from the code itself.
    This serves as the *document* that gets semantically embedded.
    """
    file_list = ", ".join(sorted(set(f for f, _ in files)))
    return (
        f"JavaScript function `{fname}({params})` defined as inline script "
        f"in blade template(s): {file_list}.\n\n"
        f"Source code:\n{code}"
    )


def embed_missing(missing_funcs, all_funcs, model):
    """Add missing functions to both JS and Blade ChromaDB."""

    # Open both collections
    js_client = chromadb.PersistentClient(
        path=str(VDB / "js_chroma_db"),
        settings=Settings(anonymized_telemetry=False),
    )
    js_col = js_client.get_collection("js_code_knowledge")

    blade_client = chromadb.PersistentClient(
        path=str(VDB / "blade_views_chroma_db"),
        settings=Settings(anonymized_telemetry=False),
    )
    blade_col = blade_client.get_collection("blade_views_knowledge")

    # Batch all descriptions for a single encode call
    names_list = sorted(missing_funcs)
    descriptions = []
    for fname in names_list:
        info = all_funcs[fname]
        desc = build_description_text(fname, info["params"], info["code"], info["files"])
        descriptions.append(desc)

    print(f"\n⚡ Encoding {len(descriptions)} descriptions with BGE-M3 …")
    embeddings = model.encode(descriptions)["dense_vecs"].tolist()
    print("✅ Encoding complete\n")

    js_added = 0
    blade_added = 0

    for idx, fname in enumerate(names_list):
        info = all_funcs[fname]
        vec = embeddings[idx]
        desc = descriptions[idx]
        primary_file = info["files"][0][0]  # first file
        primary_line = info["files"][0][1]
        all_files = sorted(set(f for f, _ in info["files"]))

        # ── JS ChromaDB ──
        js_id = hashlib.md5(f"blade_inline_js_{fname}".encode()).hexdigest()
        existing = js_col.get(ids=[js_id])
        if not existing["ids"]:
            doc_text = "\n\n".join(filter(None, [
                f"File: {Path(primary_file).name} (inline JS)",
                f"Function: {fname}",
                f"Description: JavaScript function `{fname}({info['params']})` "
                f"defined as inline script in blade template(s): {', '.join(all_files)}.",
                f"Code:\n{info['code']}",
                f"Parameters: {info['params']}" if info["params"] else None,
            ]))

            js_meta = {
                "file_name": Path(primary_file).name + " (inline JS)",
                "file_path": primary_file,
                "chunk_type": "js_function",
                "function_name": fname,
                "line_start": primary_line,
                "source_type": "blade_inline_js",
                "description_enhanced": "yes",
            }
            js_col.add(ids=[js_id], embeddings=[vec], documents=[doc_text], metadatas=[js_meta])
            js_added += 1

        # ── Blade ChromaDB (one entry per blade file) ──
        for fpath, fline in info["files"]:
            blade_id = hashlib.md5(f"blade_inline_js_{fname}_{fpath}".encode()).hexdigest()
            existing = blade_col.get(ids=[blade_id])
            if not existing["ids"]:
                document = (
                    f"<!-- Inline JavaScript: {fname} — defined in {Path(fpath).name} -->\n"
                    f"<script>\n{info['code']}\n</script>"
                )
                blade_meta = {
                    "source": fpath,
                    "file_name": Path(fpath).name,
                    "section": f"inline_js_{fname}",
                    "function_name": fname,
                    "has_form": "false",
                    "description": f"JavaScript function `{fname}({info['params']})` defined as inline script.",
                }
                blade_col.add(ids=[blade_id], embeddings=[vec], documents=[document], metadatas=[blade_meta])
                blade_added += 1

        print(f"  ✅ {fname}")

    print(f"\nJS ChromaDB:    +{js_added} chunks  (total: {js_col.count()})")
    print(f"Blade ChromaDB: +{blade_added} chunks (total: {blade_col.count()})")


# ────────────────────────────────────────────────────────────────────
# Main
# ────────────────────────────────────────────────────────────────────
def main():
    print("=" * 60)
    print("Blade Inline JS → ChromaDB Incremental Embedder")
    print("=" * 60)

    print("\n📂 Scanning blade files for inline JS functions …")
    all_funcs = extract_blade_inline_functions()
    print(f"   Found {len(all_funcs)} unique named functions")

    print("\n🔍 Checking existing ChromaDB collections …")
    existing = get_existing_function_names()
    print(f"   {len(existing)} function names already embedded")

    missing = set(all_funcs.keys()) - existing
    print(f"   {len(missing)} functions MISSING → need embedding")

    if not missing:
        print("\n🎉 Nothing to do — all inline JS functions are already embedded!")
        return

    print(f"\n🤖 Loading BGE-M3 model …")
    model = BGEM3FlagModel("BAAI/bge-m3", use_fp16=False)
    print("✅ Model loaded")

    embed_missing(missing, all_funcs, model)

    print("\n🎉 Done! All blade inline JS functions are now searchable.")


if __name__ == "__main__":
    main()
