"""
Optimized Blade Views Inference with Cross-Encoder Re-ranking
Implements smart truncation to handle large blade files efficiently
"""

# ============================================================
# CELL 1: Install Dependencies
# ============================================================
"""
!pip install -q chromadb FlagEmbedding sentence-transformers groq python-dotenv transformers
"""

# ============================================================
# CELL 2: Import and Setup
# ============================================================
import os
import sys
import chromadb
from chromadb.config import Settings
from FlagEmbedding import BGEM3FlagModel
from sentence_transformers import CrossEncoder
from transformers import AutoTokenizer
from groq import Groq
from typing import List, Dict
import json

# Configuration
CHROMA_DB_PATH = "/content/blade_views_chroma_db"  # For Colab
COLLECTION_NAME = "blade_views_knowledge"

# For local use, change to:
# CHROMA_DB_PATH = "../vector_db/blade_views_chroma_db"

# ============================================================
# CELL 3: Initialize ChromaDB
# ============================================================
print(f"📂 Connecting to ChromaDB at: {CHROMA_DB_PATH}")

try:
    client = chromadb.PersistentClient(
        path=CHROMA_DB_PATH,
        settings=Settings(anonymized_telemetry=False)
    )
    
    collection = client.get_collection(name=COLLECTION_NAME)
    print(f"✅ Collection '{COLLECTION_NAME}' loaded with {collection.count()} documents")
    
except Exception as e:
    print(f"❌ Error loading ChromaDB: {e}")
    print("Make sure you've uploaded and extracted blade_views_chroma_db.zip")

# ============================================================
# CELL 4: Load Models
# ============================================================
print("🤖 Loading BGE-M3 embedding model...")
try:
    embedding_model = BGEM3FlagModel("BAAI/bge-m3", use_fp16=False)
    from transformers import AutoTokenizer as HFTokenizer
    embedding_model.tokenizer = HFTokenizer.from_pretrained("BAAI/bge-m3", use_fast=False)
    print("✅ BGE-M3 loaded")
except Exception as e:
    print(f"❌ Failed to load BGE-M3: {e}")

print("🔄 Loading Cross-Encoder for re-ranking...")
try:
    cross_encoder = CrossEncoder("cross-encoder/ms-marco-MiniLM-L-6-v2")
    rerank_tokenizer = AutoTokenizer.from_pretrained("cross-encoder/ms-marco-MiniLM-L-6-v2")
    print("✅ Cross-Encoder loaded")
except Exception as e:
    print(f"❌ Failed to load Cross-Encoder: {e}")
    cross_encoder = None

# ============================================================
# CELL 5: Blade Retrieval Optimizer Class
# ============================================================
class BladeRetrievalOptimizer:
    """Smart truncation and context extraction for large blade files"""
    
    def __init__(self, tokenizer, max_rerank_tokens=400, max_context_chars=1500):
        self.tokenizer = tokenizer
        self.max_rerank_tokens = max_rerank_tokens
        self.max_context_chars = max_context_chars
    
    def truncate_for_reranking(self, content: str) -> str:
        """Truncate content to fit cross-encoder limits"""
        tokens = self.tokenizer.encode(content, add_special_tokens=False, truncation=False)
        
        if len(tokens) <= self.max_rerank_tokens:
            return content
        
        # Truncate and decode
        truncated_tokens = tokens[:self.max_rerank_tokens]
        return self.tokenizer.decode(truncated_tokens, skip_special_tokens=True)
    
    def rerank_with_truncation(self, query: str, results: List[Dict], cross_encoder) -> List[Dict]:
        """Re-rank results with smart truncation"""
        if not cross_encoder:
            return results
        
        pairs = []
        for result in results:
            content = result.get('content', '')
            truncated = self.truncate_for_reranking(content)
            pairs.append([query, truncated])
        
        # Get scores
        scores = cross_encoder.predict(pairs)
        
        # Attach scores and sort
        for result, score in zip(results, scores):
            result['rerank_score'] = float(score)
        
        results.sort(key=lambda x: x['rerank_score'], reverse=True)
        return results
    
    def extract_compact_context(self, result: Dict) -> Dict:
        """Extract only essential parts for LLM"""
        metadata = result.get('metadata', {})
        content = result.get('content', '')
        
        # Truncate code snippet
        if len(content) > self.max_context_chars:
            snippet = content[:self.max_context_chars]
            last_newline = snippet.rfind('\\n')
            if last_newline > self.max_context_chars * 0.8:
                snippet = snippet[:last_newline]
            snippet += "\\n... (content truncated)"
        else:
            snippet = content
        
        return {
            'file': metadata.get('file_name', 'Unknown'),
            'section': result.get('section_name', 'N/A'),
            'description': result.get('description', 'No description')[:500],
            'has_form': metadata.get('has_form', False),
            'code_snippet': snippet,
            'score': result.get('rerank_score', result.get('distance', 0))
        }

# Initialize optimizer
if cross_encoder:
    optimizer = BladeRetrievalOptimizer(rerank_tokenizer)
    print("✅ Optimizer initialized")

# ============================================================
# CELL 6: Search Function with Optimization
# ============================================================
def search_blade_views_optimized(
    query: str, 
    top_k: int = 5,
    initial_k: int = 15,
    use_rerank: bool = True,
    use_compact: bool = True
):
    """
    Optimized blade view search with re-ranking and compact context
    
    Args:
        query: User query
        top_k: Final number of results
        initial_k: Number of candidates to retrieve before re-ranking
        use_rerank: Whether to use cross-encoder re-ranking
        use_compact: Whether to use compact context extraction
    """
    # Step 1: Initial retrieval with BGE-M3
    query_embedding = embedding_model.encode(query, max_length=8192)['dense_vecs'].tolist()
    
    n_retrieve = initial_k if use_rerank else top_k
    results = collection.query(
        query_embeddings=[query_embedding], 
        n_results=n_retrieve
    )
    
    # Format results
    formatted_results = []
    if results['documents']:
        for i in range(len(results['documents'][0])):
            formatted_results.append({
                'id': results['ids'][0][i],
                'content': results['documents'][0][i],
                'metadata': results['metadatas'][0][i],
                'description': results['metadatas'][0][i].get('description', ''),
                'section_name': results['metadatas'][0][i].get('section_name', ''),
                'distance': results['distances'][0][i] if 'distances' in results else None
            })
    
    print(f"🔍 Initial retrieval: {len(formatted_results)} candidates")
    
    # Step 2: Re-rank with cross-encoder (if enabled)
    if use_rerank and cross_encoder and len(formatted_results) > top_k:
        print(f"🔄 Re-ranking with cross-encoder (truncating to 400 tokens)...")
        formatted_results = optimizer.rerank_with_truncation(query, formatted_results, cross_encoder)
        formatted_results = formatted_results[:top_k]
        print(f"✅ Re-ranked to top {len(formatted_results)}")
    
    # Step 3: Extract compact context (if enabled)
    if use_compact and optimizer:
        print(f"📦 Extracting compact context (max {optimizer.max_context_chars} chars per chunk)...")
        compact_results = [optimizer.extract_compact_context(r) for r in formatted_results]
        return formatted_results, compact_results
    
    return formatted_results, formatted_results

# ============================================================
# CELL 7: LLM Integration
# ============================================================
GROQ_API_KEY = "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"
groq_client = Groq(api_key=GROQ_API_KEY)

def format_compact_context(compact_results: List[Dict]) -> str:
    """Format compact results for LLM"""
    context_parts = []
    
    for i, result in enumerate(compact_results, 1):
        part = f"--- Result {i} (Score: {result['score']:.3f}) ---\\n"
        part += f"File: {result['file']}\\n"
        
        if result['section'] != 'full_template':
            part += f"Section: {result['section']}\\n"
        
        part += f"Description: {result['description']}\\n"
        
        if result.get('has_form'):
            part += "Contains Form: Yes\\n"
        
        part += f"\\nCode Preview:\\n{result['code_snippet']}\\n"
        context_parts.append(part)
    
    return "\\n\\n".join(context_parts)

def generate_answer(query: str, compact_results: List[Dict]) -> str:
    """Generate LLM answer with compact context"""
    context = format_compact_context(compact_results)
    
    # Calculate approximate token usage
    approx_tokens = len(context) / 4  # Rough estimate
    print(f"📊 Context size: {len(context)} chars (~{int(approx_tokens)} tokens)")
    
    system_prompt = """You are an expert Laravel Blade developer.
Answer the user query based on the provided context.
Provide a clear EXPLANATION. Include code snippets ONLY if directly relevant.
If the context doesn't contain enough information, say so."""

    user_prompt = f"USER QUERY: {query}\\n\\nRETRIEVED CONTEXT:\\n{context}\\n\\nAnswer:"
    
    try:
        chat_completion = groq_client.chat.completions.create(
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            model="llama-3.3-70b-versatile",
            temperature=0.2,
        )
        return chat_completion.choices[0].message.content
    except Exception as e:
        return f"⚠️ LLM Error: {e}"

# ============================================================
# CELL 8: Interactive Query (RUN THIS TO TEST)
# ============================================================

# 🔧 CONFIGURE YOUR QUERY HERE
USER_QUERY = "How does the login form protect against CSRF?"

# 🔧 CONFIGURATION
USE_RERANKING = True      # Enable cross-encoder re-ranking
USE_COMPACT_CONTEXT = True  # Use compact context extraction
TOP_K = 5                  # Final number of results
INITIAL_K = 15             # Candidates for re-ranking

print("="*60)
print(f"🔍 Query: {USER_QUERY}")
print("="*60)

# Search with optimization
full_results, compact_results = search_blade_views_optimized(
    query=USER_QUERY,
    top_k=TOP_K,
    initial_k=INITIAL_K,
    use_rerank=USE_RERANKING,
    use_compact=USE_COMPACT_CONTEXT
)

# Display results
print(f"\\n✅ Found {len(compact_results)} results:\\n")
for i, result in enumerate(compact_results, 1):
    score = result.get('score', 0)
    print(f"{i}. {result['file']} (Score: {score:.3f})")
    print(f"   {result['description'][:100]}...")

# Generate answer
print("\\n" + "="*60)
print("🤖 GENERATING ANSWER...")
print("="*60)

answer = generate_answer(USER_QUERY, compact_results)
print(answer)

# ============================================================
# CELL 9: Comparison (Optional - Show Improvement)
# ============================================================
print("\\n" + "="*60)
print("📊 COMPARISON: WITH vs WITHOUT OPTIMIZATION")
print("="*60)

# Without optimization (old way)
print("\\n❌ Without optimization:")
query_embedding = embedding_model.encode(USER_QUERY, max_length=8192)['dense_vecs'].tolist()
old_results = collection.query(query_embeddings=[query_embedding], n_results=3)

total_chars_old = sum(len(doc) for doc in old_results['documents'][0])
print(f"   - 3 full chunks: {total_chars_old} chars (~{total_chars_old/4:.0f} tokens)")
print(f"   - No re-ranking")

# With optimization (new way)
print("\\n✅ With optimization:")
total_chars_new = sum(len(r['code_snippet']) for r in compact_results)
print(f"   - {len(compact_results)} compact chunks: {total_chars_new} chars (~{total_chars_new/4:.0f} tokens)")
print(f"   - Cross-encoder re-ranking: {'Yes' if USE_RERANKING else 'No'}")
print(f"   - Token reduction: {((total_chars_old - total_chars_new) / total_chars_old * 100):.1f}%")

# ============================================================
# CELL 10: Batch Testing (Optional)
# ============================================================
TEST_QUERIES = [
    "How does the login form protect against CSRF?",
    "Show me the user chat interface",
    "What forms require approval workflow?",
    "How is the navigation menu structured?",
    "Where is pagination implemented?"
]

print("\\n" + "="*60)
print("🧪 BATCH TEST: Multiple Queries")
print("="*60)

for i, test_query in enumerate(TEST_QUERIES, 1):
    print(f"\\n{i}. {test_query}")
    
    full_results, compact_results = search_blade_views_optimized(
        query=test_query,
        top_k=3,
        initial_k=10,
        use_rerank=True,
        use_compact=True
    )
    
    print(f"   Top result: {compact_results[0]['file']}")
    print(f"   Score: {compact_results[0]['score']:.3f}")
    
    total_chars = sum(len(r['code_snippet']) for r in compact_results)
    print(f"   Total context: {total_chars} chars (~{total_chars/4:.0f} tokens)")

print("\\n✅ Batch test complete!")
