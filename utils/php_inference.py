import chromadb
from chromadb.config import Settings
from FlagEmbedding import BGEM3FlagModel

# Configuration
CHROMA_DIR = "./chroma_db"
COLLECTION_NAME = "php_code_chunks"

# Initialize ChromaDB
client = chromadb.PersistentClient(
    path=CHROMA_DIR,
    settings=Settings(anonymized_telemetry=False)
)

collection = client.get_collection(name=COLLECTION_NAME)

# Load embedding model ONCE
model = BGEM3FlagModel(
    "BAAI/bge-m3",
    use_fp16=False  # Mac-safe
)
print("✅ Setup complete. Model and ChromaDB loaded.")


---------------------------------------------------------


# Change your query here
query = "3. How is account number created"
OUTPUT_FILE = "retrieved_chunks.txt"
TOP_K = 20

# Encode query
query_embedding = model.encode(query)["dense_vecs"]

# Query ChromaDB
results = collection.query(
    query_embeddings=query_embedding.tolist(),
    n_results=TOP_K
)

docs = results["documents"][0]
distances = results["distances"][0]   # ⬅️ relevance scores
metas = results["metadatas"][0]

# Save retrieved chunks with distance
with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
    f.write(f"Query: {query}\n")
    f.write("=" * 80 + "\n\n")

    for i, (doc, dist, meta) in enumerate(zip(docs, distances, metas), 1):
        f.write(f"Result {i}\n")
        f.write(f"Distance (lower = better): {dist:.6f}\n")
        f.write(f"Type: {meta.get('chunk_type', 'N/A')}\n")
        f.write(f"File: {meta.get('file_path', 'N/A')}\n")
        f.write("-" * 40 + "\n")
        f.write(doc)
        f.write("\n\n" + "=" * 80 + "\n\n")

        print(f"\n{i}. Distance: {dist:.6f}")
        print(f"   {doc[:200]}...")

print(f"\n✅ Results with relevance scores saved to '{OUTPUT_FILE}'")


# ========================================
# GROQ LLM SETUP
# ========================================

from groq import Groq

# Set your Groq API key here
GROQ_API_KEY = "your_groq_api_key_here"  # Replace with your actual API key

# Initialize Groq client
groq_client = Groq(api_key=GROQ_API_KEY)

print("\n✅ Groq client initialized.")


# ========================================
# LLM INFERENCE WITH GROQ
# ========================================

def get_llm_response(user_query, retrieved_chunks):
    """
    Generate explanation using Groq LLM based on retrieved context
    """
    # Build context from retrieved chunks
    context = "\n\n".join([
        f"[Chunk {i+1} - {meta.get('chunk_type', 'N/A')} from {meta.get('file_path', 'N/A')}]\n{doc}"
        for i, (doc, meta) in enumerate(zip(retrieved_chunks['docs'], retrieved_chunks['metas']))
    ])
    
    system_prompt = """You are an expert PHP Laravel developer and architect with deep knowledge of the Laravel framework, MVC patterns, database design, and enterprise application architecture.

CRITICAL INSTRUCTIONS - NO HALLUCINATION:
1. ONLY use information explicitly present in the provided code context
2. DO NOT make assumptions about code that is not shown
3. DO NOT invent function names, class names, or implementation details
4. If information is not available in the context, clearly state "This information is not available in the provided code"
5. ONLY explain what you can directly see in the retrieved code chunks
6. DO NOT speculate about database tables, columns, or relationships unless explicitly shown in the code
7. If a complete workflow cannot be determined from the provided context, acknowledge the gaps

Your role is to:
1. Analyze ONLY the provided code context thoroughly
2. Explain the workflow based STRICTLY on what is shown in the code
3. Describe how different components interact, but ONLY based on the retrieved code
4. Highlight design patterns and business logic that are EXPLICITLY visible
5. Provide a comprehensive explanation WITHOUT generating any code
6. Be honest about limitations - if the context doesn't show something, say so

Focus on explanation and understanding based solely on the provided code. Be detailed and technical, but never invent or assume information beyond what is explicitly shown."""

    user_message = f"""Based on the following code context from a PHP Laravel application, please explain the complete workflow and implementation details for this query:

USER QUERY: {user_query}

RETRIEVED CODE CONTEXT:
{context}

IMPORTANT: Base your explanation STRICTLY on the code provided above. Do not make assumptions or add information not present in the context.

Please provide a comprehensive explanation of how this functionality works, including:
- Overall workflow and architecture (based only on visible code)
- Key components involved (controllers, models, helpers, etc.) that are shown
- Data flow and processing steps as seen in the code
- Database interactions if explicitly visible
- Business logic and validation rules that are present
- Any important patterns or design decisions evident in the code

If any aspect is not clear or not present in the provided code context, explicitly state that the information is not available.

Remember: 
1. Provide detailed explanation only, do not generate code
2. ONLY explain what is explicitly shown in the retrieved code chunks
3. Do not hallucinate or assume implementation details"""

    try:
        # Call Groq API
        chat_completion = groq_client.chat.completions.create(
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_message}
            ],
            model="llama-3.3-70b-versatile",  # You can change to other models like "mixtral-8x7b-32768"
            temperature=0.3,
            max_tokens=4096,
            top_p=0.9
        )
        
        return chat_completion.choices[0].message.content
    
    except Exception as e:
        return f"Error generating LLM response: {str(e)}"


# Generate LLM explanation
print("\n" + "="*80)
print("🤖 GENERATING LLM EXPLANATION...")
print("="*80 + "\n")

retrieved_data = {
    'docs': docs,
    'metas': metas,
    'distances': distances
}

llm_response = get_llm_response(query, retrieved_data)

# Save LLM response
LLM_OUTPUT_FILE = "llm_explanation.txt"
with open(LLM_OUTPUT_FILE, "w", encoding="utf-8") as f:
    f.write(f"Query: {query}\n")
    f.write("=" * 80 + "\n\n")
    f.write("LLM EXPLANATION:\n")
    f.write("-" * 80 + "\n\n")
    f.write(llm_response)
    f.write("\n\n" + "=" * 80 + "\n")

print(llm_response)
print(f"\n✅ LLM explanation saved to '{LLM_OUTPUT_FILE}'")
