#!/bin/bash
# Quick start script to enhance PHP chunks with code and re-embed

echo "=========================================="
echo "🚀 PHP Code Enhancement Pipeline"
echo "=========================================="
echo ""

# Set base directory
BASE_DIR="/Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance"
cd "$BASE_DIR"

echo "📍 Working directory: $BASE_DIR"
echo ""

# Step 1: Extract PHP code
echo "=========================================="
echo "Step 1: Extracting PHP Code from Source Files"
echo "=========================================="
echo ""

if [ ! -f "chunks/php_metadata_chunks_for_chromadb.json" ]; then
    echo "❌ Error: php_metadata_chunks_for_chromadb.json not found!"
    echo "   Please ensure the file exists in chunks/ directory"
    exit 1
fi

python utils/extract_php_code.py

if [ $? -ne 0 ]; then
    echo "❌ Error during code extraction!"
    exit 1
fi

echo ""
echo "✅ Step 1 Complete: Code extracted successfully!"
echo ""

# Check if enhanced chunks were created
if [ ! -f "chunks/php_metadata_chunks_with_code.json" ]; then
    echo "❌ Error: Enhanced chunks file not created!"
    exit 1
fi

# Step 2: Re-embed with code
echo "=========================================="
echo "Step 2: Re-embedding Chunks with Code Snippets"
echo "=========================================="
echo ""
echo "⏳ This may take 15-30 minutes depending on your hardware..."
echo ""

python embedding_vectordb/reembed_php_with_code.py

if [ $? -ne 0 ]; then
    echo "❌ Error during re-embedding!"
    exit 1
fi

echo ""
echo "=========================================="
echo "✅ All Steps Complete!"
echo "=========================================="
echo ""
echo "📊 Summary:"
echo "   ✓ Code extracted from PHP files"
echo "   ✓ Enhanced chunks created with code snippets"
echo "   ✓ New vector DB created with code embeddings"
echo ""
echo "📁 New Vector DB Location:"
echo "   vector_db/php_code_with_snippets_db/"
echo ""
echo "📝 Next Steps:"
echo "   1. Update your inference scripts to use the new vector DB"
echo "   2. Test with code-specific queries"
echo "   3. Compare results with old DB"
echo ""
echo "💡 See PHP_CODE_ENHANCEMENT_GUIDE.md for detailed documentation"
echo ""
