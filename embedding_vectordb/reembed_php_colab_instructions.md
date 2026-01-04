# Running PHP Re-embedding on Google Colab with T4 GPU

## 🚀 Quick Start Guide

### Step 1: Upload to Google Colab

1. Open Google Colab: https://colab.research.google.com/
2. Create a new notebook
3. Go to **Runtime > Change runtime type > Select GPU (T4)**
4. Upload `reembed_php_with_code.py` or copy its contents into a cell

### Step 2: Setup Google Drive Structure

Create this folder structure in your Google Drive:

```
MyDrive/
└── Banking-knowledgeAssistance/
    ├── chunks/
    │   └── php_metadata_chunks_with_code.json  ← Upload this file
    └── vector_db/
        └── (will be created automatically)
```

### Step 3: Run the Script

In your Colab notebook, create cells with:

#### Cell 1: Run the embedding script
```python
!python reembed_php_with_code.py
```

OR copy the entire script into a cell and run:
```python
# Paste the entire reembed_php_with_code.py content here
# Then at the end add:
if __name__ == "__main__":
    main()
```

---

## 📤 Alternative: Upload File Directly in Colab

If you don't want to use Google Drive:

### Cell 1: Upload chunks file
```python
from google.colab import files

print("📤 Upload your php_metadata_chunks_with_code.json file:")
uploaded = files.upload()

# Move to expected location
import os
os.makedirs('/content/chunks', exist_ok=True)
for filename in uploaded.keys():
    os.rename(filename, f'/content/chunks/{filename}')
    print(f"✓ File uploaded: /content/chunks/{filename}")
```

### Cell 2: Update paths in script
```python
# Modify the configuration section:
BASE_DIR = Path("/content")
ENHANCED_CHUNKS_FILE = BASE_DIR / "chunks" / "php_metadata_chunks_with_code.json"
CHROMA_DIR = BASE_DIR / "vector_db" / "php_code_with_snippets_db"
```

---

## ⚡ Expected Performance on T4 GPU

- **Model Loading**: 2-5 minutes (first time only)
- **Embedding 1063 chunks**: ~15-20 minutes
- **Batch size**: 32 (optimized for T4's 16GB memory)
- **Total time**: ~20-25 minutes

---

## 📥 Downloading Results

After embedding completes:

### Option 1: Keep in Google Drive
The vector DB is automatically saved to Google Drive and will persist across sessions.

### Option 2: Download to Local
```python
from google.colab import files
import shutil

# Create zip file
!zip -r php_vector_db.zip /content/drive/MyDrive/Banking-knowledgeAssistance/vector_db/php_code_with_snippets_db

# Download
files.download('php_vector_db.zip')
```

---

## 🔧 Troubleshooting

### GPU Not Detected
```
⚠️  No GPU detected! This will be much slower.
```
**Solution**: Go to **Runtime > Change runtime type > Select GPU (T4)**

### Out of Memory Error
```python
# Reduce batch size in the configuration:
BATCH_SIZE = 16  # or even 8
```

### File Not Found
```
❌ Error: Enhanced chunks file not found!
```
**Solution**: 
1. Check your Google Drive folder structure
2. Or update `BASE_DIR` variable to match your structure
3. Or use the upload helper function

### Drive Mount Issues
```python
# Force remount Google Drive
from google.colab import drive
drive.mount('/content/drive', force_remount=True)
```

---

## 💡 Pro Tips

### 1. Monitor GPU Usage
```python
# Add this to monitor GPU during embedding
!nvidia-smi
```

### 2. Save Logs
```python
# Redirect output to file
import sys
sys.stdout = open('embedding_log.txt', 'w')

# Run your code...

# Download log
from google.colab import files
files.download('embedding_log.txt')
```

### 3. Resume After Disconnection
The script automatically creates checkpoints in ChromaDB. If disconnected:
- Set `RESET_COLLECTION = False` 
- Re-run to continue from where it left off

### 4. Batch Processing
For very large datasets, process in chunks:
```python
# Process first 500 chunks
chunks_subset = chunks[:500]
embedder.embed_chunks(chunks_subset, batch_size=BATCH_SIZE)
```

---

## 📊 Sample Output

```
🚀 Re-embedding PHP Chunks with Code Snippets
   🌐 Running on Google Colab
================================================================================

🔧 Setting up Google Colab environment...
📦 Installing required packages...
✓ GPU detected: Tesla T4
✓ CUDA version: 12.2
📁 Mounting Google Drive...
✓ Google Drive mounted at /content/drive

📂 Loading enhanced chunks from: php_metadata_chunks_with_code.json
✓ Loaded 1063 chunks
   1056 chunks have code snippets (99.3%)

🔧 Initializing embedder...
   ChromaDB directory: /content/drive/MyDrive/Banking-knowledgeAssistance/vector_db/php_code_with_snippets_db
   Collection name: php_code_chunks
   Batch size: 32 (optimized for T4 GPU)

🤖 Loading BGE-M3 model: BAAI/bge-m3...
   📥 Downloading model (~2GB on first run, will be cached)
   ⏱️  This may take 2-5 minutes on T4 GPU...
✓ Model loaded successfully on CUDA!
✓ Using GPU acceleration with fp16 precision

📊 Processing 1063 chunks...
   ✓ 1056 chunks with code snippets
   ⚠ 7 chunks without code

🔄 Embedding in batches of 32...
Embedding batches: 100%|██████████| 34/34 [18:23<00:00, 32.45s/it]

✅ Successfully embedded 1063 chunks into ChromaDB
✓ Collection 'php_code_chunks' now contains 1063 items

🧪 Testing with Sample Queries
================================================================================
... [test results] ...

✅ Done! Enhanced PHP chunks with code are now embedded.
📁 Vector DB location: /content/drive/MyDrive/Banking-knowledgeAssistance/vector_db/php_code_with_snippets_db
```

---

## 🎯 Next Steps

After successful embedding:

1. **Test locally**: Download the vector DB and test queries
2. **Update inference scripts**: Point to new DB path
3. **Compare results**: Test against old DB (description only)
4. **Deploy**: Use in production RAG system

---

**Need Help?** Check the main [PHP_CODE_ENHANCEMENT_GUIDE.md](../PHP_CODE_ENHANCEMENT_GUIDE.md)
