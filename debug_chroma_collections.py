import chromadb
from chromadb.config import Settings
import os

# Path to the observed DB
DB_PATH = "/Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance/vector_db/blade_views_chroma_db"

print(f"Checking DB at: {DB_PATH}")
if not os.path.exists(DB_PATH):
    print("❌ DB Directory does not exist!")
    exit()

try:
    client = chromadb.PersistentClient(path=DB_PATH, settings=Settings(anonymized_telemetry=False))
    print("✅ Client connected.")
    
    colls = client.list_collections()
    if not colls:
        print("⚠️  No collections found in this DB.")
    else:
        print(f"📚 Found {len(colls)} collections:")
        for c in colls:
            print(f" - {c.name}")

except Exception as e:
    print(f"❌ Error: {e}")
