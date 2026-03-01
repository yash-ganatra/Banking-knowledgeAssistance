#!/usr/bin/env python3
"""
Incrementally embeds ONLY the rsenc function chunk into the existing
JS and Blade ChromaDB collections — without touching any other documents.
"""
import hashlib
import json
import sys
from pathlib import Path

import chromadb
from chromadb.config import Settings
from FlagEmbedding import BGEM3FlagModel

ROOT = Path(__file__).parent.parent
VDB  = ROOT / "vector_db"

# ── chunk data ────────────────────────────────────────────────────────────────
RSENC_CODE = (
    "function rsenc(data, publicKey) {\n"
    "    var publicKey = document.getElementById('pubki');\n"
    "    if(publicKey && publicKey != null){\n"
    "        publicKey = decPubStr(_encPubStrB)+publicKey.value+decPubStr(_encPubStrE);\n"
    "        const f_publicKey = KEYUTIL.getKey(publicKey);\n"
    "        const encryptedData = KJUR.crypto.Cipher.encrypt(data, f_publicKey);\n"
    "        const encryptedBase64 = hextob64(encryptedData);\n"
    "        return encryptedBase64;\n"
    "    } else {\n"
    "        alert('Encryption Failed!');\n"
    "        return false;\n"
    "    }\n"
    "}\n\n"
    "function decPubStr(_i) {\n"
    "  let _d = [];\n"
    "  for (let i = 0; i < _i.length; i++) {\n"
    "    const xv = _i[i]; const flpd = parseInt(xv, 16);\n"
    "    const cc = ~flpd; const _o = String.fromCharCode(cc);\n"
    "    _d += _o;\n"
    "  }\n"
    "  return _d;\n"
    "}"
)

DESCRIPTION = (
    "The `rsenc` function performs RSA public-key encryption of sensitive data "
    "(e.g. PAN numbers) on the client side. It reads the RSA public key from the "
    "hidden `#pubki` HTML element, reconstructs it by calling `decPubStr()` to decode "
    "obfuscated key prefix/suffix strings, then uses the jsrsasign library "
    "(KEYUTIL.getKey, KJUR.crypto.Cipher.encrypt, hextob64) to encrypt the input `data` "
    "and return it as a Base64-encoded string. If the public key element is missing it "
    "alerts 'Encryption Failed!' and returns false. "
    "The function is defined as inline JavaScript inside blade templates "
    "(addaccount.blade.php, addovddocuments.blade.php, amenduploaddocument_v2.blade.php) "
    "and is called before AJAX requests to protect PAN numbers in transit."
)

# ── load model once ───────────────────────────────────────────────────────────
print("🤖 Loading BGE-M3 model (this takes ~30s)...")
model = BGEM3FlagModel("BAAI/bge-m3", use_fp16=False)
print("✅ Model loaded\n")


def embed(text: str):
    return model.encode([text])["dense_vecs"][0].tolist()


# ══════════════════════════════════════════════════════════════════════════════
# 1. JS ChromaDB  —  collection: js_code_knowledge
# ══════════════════════════════════════════════════════════════════════════════
def embed_into_js_chroma():
    client = chromadb.PersistentClient(
        path=str(VDB / "js_chroma_db"),
        settings=Settings(anonymized_telemetry=False),
    )
    col = client.get_collection("js_code_knowledge")

    chunk_id = hashlib.md5(b"rsenc_blade_inline_js_consolidated").hexdigest()

    # Skip if already embedded
    existing = col.get(ids=[chunk_id])
    if existing["ids"]:
        print(f"⏭  JS ChromaDB: rsenc already embedded (id={chunk_id}), skipping.")
        return

    # Document text mirrors create_chunk_text() from embed_js_chunk_to_chromadb.py
    doc_text = "\n\n".join([
        "File: addaccount.blade.php (inline JS)",
        "Function: rsenc",
        f"Description: {DESCRIPTION}",
        f"Code:\n{RSENC_CODE}",
        "Parameters: data, publicKey",
    ])

    metadata = {
        "file_name":           "addaccount.blade.php (inline JS)",
        "file_path":           "code/code/resources/views/bank/addaccount.blade.php",
        "chunk_type":          "js_function",
        "function_name":       "rsenc",
        "also_defines":        "decPubStr",
        "line_start":          1005,
        "js_library":          "jsrsasign (KEYUTIL, KJUR.crypto.Cipher, hextob64)",
        "source_type":         "blade_inline_js",
        "description":         DESCRIPTION,
        "description_enhanced": "yes",
    }

    # Embed the description (same strategy as the original embedder)
    vec = embed(DESCRIPTION)

    col.add(ids=[chunk_id], embeddings=[vec], documents=[doc_text], metadatas=[metadata])
    print(f"✅ JS ChromaDB: added rsenc chunk (id={chunk_id}) — total docs now: {col.count()}")


# ══════════════════════════════════════════════════════════════════════════════
# 2. Blade ChromaDB  —  collection: blade_views_knowledge
# ══════════════════════════════════════════════════════════════════════════════
BLADE_FILES = [
    ("addaccount.blade.php",
     "code/code/resources/views/bank/addaccount.blade.php",          1005),
    ("addovddocuments.blade.php",
     "code/code/resources/views/bank/addovddocuments.blade.php",      819),
    ("amenduploaddocument_v2.blade.php",
     "code/code/resources/views/amend/amenduploaddocument_v2.blade.php", 641),
]

def embed_into_blade_chroma():
    client = chromadb.PersistentClient(
        path=str(VDB / "blade_views_chroma_db"),
        settings=Settings(anonymized_telemetry=False),
    )
    col = client.get_collection("blade_views_knowledge")

    # Pre-compute the embedding once (description is identical across all 3)
    vec = embed(DESCRIPTION)

    added = 0
    for file_name, file_path, line_start in BLADE_FILES:
        chunk_id = hashlib.md5(("rsenc_inline_js_" + file_name).encode()).hexdigest()

        existing = col.get(ids=[chunk_id])
        if existing["ids"]:
            print(f"  ⏭  Blade ChromaDB: {file_name} rsenc already embedded, skipping.")
            continue

        # Document stored = the inline JS content (matches embed_blade_chunks.py pattern)
        document = (
            f"<!-- Inline JavaScript: rsenc and decPubStr — defined in {file_name} -->\n"
            f"<script>\n{RSENC_CODE}\n</script>"
        )

        metadata = {
            "source":          file_path,
            "file_name":       file_name,
            "section":         "inline_js_rsenc",
            "function_name":   "rsenc",
            "also_defines":    "decPubStr",
            "line_start":      line_start,
            "js_library":      "jsrsasign",
            "purpose":         "RSA public-key encryption of PAN and other sensitive fields",
            "has_form":        "false",
            "description":     DESCRIPTION,
        }

        col.add(ids=[chunk_id], embeddings=[vec], documents=[document], metadatas=[metadata])
        added += 1
        print(f"  ✅ Blade ChromaDB: added rsenc chunk for {file_name}")

    print(f"Blade ChromaDB: +{added} chunks — total docs now: {col.count()}")


# ── main ──────────────────────────────────────────────────────────────────────
print("═" * 60)
print("Embedding rsenc into JS ChromaDB...")
print("═" * 60)
embed_into_js_chroma()

print()
print("═" * 60)
print("Embedding rsenc into Blade ChromaDB...")
print("═" * 60)
embed_into_blade_chroma()

print("\n🎉 Done. rsenc is now searchable in both vector databases.")
