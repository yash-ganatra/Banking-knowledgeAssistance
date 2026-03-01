"""
One-time script: add rsenc inline-JS chunks to blade and JS chunk files.
Run from the project root:  python3 scripts/add_rsenc_chunks.py
"""
import json
import hashlib
from pathlib import Path

ROOT = Path(__file__).parent.parent

RSENC_CODE = (
    'function rsenc(data, publicKey) {\n'
    '    var publicKey = document.getElementById(\'pubki\');\n'
    '    if(publicKey && publicKey != null){\n'
    '        publicKey = decPubStr(_encPubStrB)+publicKey.value+decPubStr(_encPubStrE);\n'
    '        const f_publicKey = KEYUTIL.getKey(publicKey);\n'
    '        const encryptedData = KJUR.crypto.Cipher.encrypt(data, f_publicKey);\n'
    '        const encryptedBase64 = hextob64(encryptedData);\n'
    '        return encryptedBase64;\n'
    '    } else {\n'
    '        alert(\'Encryption Failed!\');\n'
    '        return false;\n'
    '    }\n'
    '}\n\n'
    'function decPubStr(_i) {\n'
    '  let _d = [];\n'
    '  for (let i = 0; i < _i.length; i++) {\n'
    '    const xv = _i[i]; const flpd = parseInt(xv, 16);\n'
    '    const cc = ~flpd; const _o = String.fromCharCode(cc);\n'
    '    _d += _o;\n'
    '  }\n'
    '  return _d;\n'
    '}'
)

DESCRIPTION = (
    "The `rsenc` function performs RSA public-key encryption of sensitive data (e.g. PAN numbers) "
    "on the client side. It reads the RSA public key from the hidden `#pubki` HTML element, "
    "reconstructs it by calling `decPubStr()` to decode obfuscated key prefix/suffix strings, "
    "then uses the jsrsasign library (KEYUTIL.getKey, KJUR.crypto.Cipher.encrypt, hextob64) to "
    "encrypt the input `data` and return it as a Base64-encoded string. If the public key element "
    "is missing it alerts 'Encryption Failed!' and returns false. "
    "The function is defined as inline JavaScript inside blade templates (addaccount.blade.php, "
    "addovddocuments.blade.php, amenduploaddocument_v2.blade.php) and is called before AJAX "
    "requests to protect PAN numbers in transit."
)

BLADE_FILES = [
    ("addaccount.blade.php",
     "code/code/resources/views/bank/addaccount.blade.php", 1005),
    ("addovddocuments.blade.php",
     "code/code/resources/views/bank/addovddocuments.blade.php", 819),
    ("amenduploaddocument_v2.blade.php",
     "code/code/resources/views/amend/amenduploaddocument_v2.blade.php", 641),
]


def update_blade_chunks():
    path = ROOT / "chunks" / "blade_views_enhanced.json"
    with open(path) as f:
        data = json.load(f)

    existing_ids = {c["chunk_id"] for c in data}
    added = 0

    for file_name, file_path, line_start in BLADE_FILES:
        chunk_id = hashlib.md5(("rsenc_inline_js_" + file_name).encode()).hexdigest()
        if chunk_id in existing_ids:
            print(f"  Skip (already exists): {file_name}")
            continue

        content = (
            f"<!-- Inline JavaScript: rsenc and decPubStr — defined in {file_name} -->\n"
            f"<script>\n{RSENC_CODE}\n</script>"
        )
        data.append({
            "chunk_id": chunk_id,
            "file_name": file_name,
            "file_path": file_path,
            "chunk_type": "blade_inline_js",
            "section_name": "inline_js_rsenc",
            "content": content,
            "metadata": {
                "source": file_path,
                "function_name": "rsenc",
                "also_defines": "decPubStr",
                "line_start": line_start,
                "js_library": "jsrsasign",
                "purpose": "RSA public-key encryption of PAN and other sensitive fields",
                "has_form": False,
            },
            "description": DESCRIPTION,
            "description_enhanced": True,
        })
        added += 1
        print(f"  Added blade chunk: {file_name}")

    with open(path, "w") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
    print(f"blade_views_enhanced.json: {len(data)} total chunks (+{added} new)\n")


def update_js_chunks():
    path = ROOT / "description" / "js_file_chunks_cleaned.json"
    with open(path) as f:
        data = json.load(f)

    chunk_id = hashlib.md5(b"rsenc_blade_inline_js_consolidated").hexdigest()
    if any(c.get("chunk_id") == chunk_id for c in data):
        print("  rsenc chunk already in js_file_chunks_cleaned.json — skipping")
        return

    data.append({
        "chunk_id": chunk_id,
        "chunk_type": "js_function",
        "function_name": "rsenc",
        "also_defines": "decPubStr",
        "file_name": "addaccount.blade.php (inline JS)",
        "file_path": "code/code/resources/views/bank/addaccount.blade.php",
        "also_in_files": [
            "code/code/resources/views/bank/addovddocuments.blade.php",
            "code/code/resources/views/amend/amenduploaddocument_v2.blade.php",
        ],
        "line_start": 1005,
        "parameters": ["data", "publicKey"],
        "js_library": "jsrsasign (KEYUTIL, KJUR.crypto.Cipher, hextob64)",
        "source_type": "blade_inline_js",
        "description": DESCRIPTION,
        "description_enhanced": True,
        "code_snippet": RSENC_CODE,
    })

    with open(path, "w") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
    print(f"  js_file_chunks_cleaned.json: added rsenc chunk (total: {len(data)})\n")


if __name__ == "__main__":
    print("=== Updating blade_views_enhanced.json ===")
    update_blade_chunks()
    print("=== Updating js_file_chunks_cleaned.json ===")
    update_js_chunks()
    print("Done.")
