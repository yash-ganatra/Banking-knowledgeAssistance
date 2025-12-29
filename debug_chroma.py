import chromadb
import os

base_dir = "/Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance"
vector_db_root = os.path.join(base_dir, "vector_db")

paths = [
    os.path.join(vector_db_root, "business_docs_chroma_db"),
    os.path.join(vector_db_root, "php_chroma_db"),
    os.path.join(vector_db_root, "js_chroma_db"),
    os.path.join(vector_db_root, "cube_optimized_db")
]

for path in paths:
    print(f"Checking path: {path}")
    if os.path.exists(path):
        print("  Exists")
        if os.path.isdir(path):
            try:
                if "chroma.sqlite3" in os.listdir(path):
                    client = chromadb.PersistentClient(path=path)
                    collections = client.list_collections()
                    print(f"  Collections found: {[c.name for c in collections]}")
                else:
                    print("  No chromadb file found")
            except Exception as e:
                print(f"  Error accessing Chroma: {e}")
    else:
        print("  Path does not exist")
