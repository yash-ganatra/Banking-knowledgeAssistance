"""
CUBE Documentation RAG Inference with Groq API
Optimized for Google Colab with BGE-M3 embeddings and Mermaid diagram rendering
"""

import chromadb
from chromadb.config import Settings
from sentence_transformers import SentenceTransformer
from groq import Groq
from typing import List, Dict, Optional
import json
import torch
import os
import re
from IPython.display import display, HTML, Markdown


class MermaidRenderer:
    """Render Mermaid diagrams in Jupyter/Colab"""
    
    @staticmethod
    def extract_diagrams(results: List[Dict]) -> List[Dict]:
        """Extract all Mermaid diagrams from results"""
        diagrams = []
        for result in results:
            metadata = result['metadata']
            if metadata.get('mermaid_code'):
                diagrams.append({
                    'page_name': metadata.get('page_name', 'Unknown'),
                    'mermaid_code': metadata['mermaid_code'],
                    'chunk_id': result['id']
                })
        return diagrams
    
    @staticmethod
    def render_in_notebook(mermaid_code: str, title: str = "Diagram"):
        """Render Mermaid diagram in Jupyter/Colab notebook"""
        html = f"""
        <div style="border: 2px solid #4CAF50; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f9f9f9;">
            <h3 style="color: #4CAF50; margin-top: 0;">📊 {title}</h3>
            <div class="mermaid">
{mermaid_code}
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
        <script>
            mermaid.initialize({{startOnLoad: true, theme: 'default'}});
        </script>
        """
        display(HTML(html))
    
    @staticmethod
    def render_all_diagrams(results: List[Dict]):
        """Render all diagrams from query results"""
        diagrams = MermaidRenderer.extract_diagrams(results)
        
        if not diagrams:
            print("ℹ️  No Mermaid diagrams found in results.")
            return
        
        print(f"\n📊 Found {len(diagrams)} Mermaid diagram(s)\n")
        
        for diagram in diagrams:
            MermaidRenderer.render_in_notebook(
                diagram['mermaid_code'],
                title=diagram['page_name']
            )


class CUBEGroqInference:
    def __init__(
        self,
        db_path: str = "/content/chroma_db",
        collection_name: str = "cube_docs_optimized",
        embedding_model: str = "BAAI/bge-m3",
        groq_api_key: Optional[str] = None,
        groq_model: str = "llama-3.1-8b-instant",
        use_gpu: bool = True
    ):
        self.db_path = db_path
        self.collection_name = collection_name
        self.groq_model = groq_model
        
        # GPU configuration
        self.device = "cuda" if use_gpu and torch.cuda.is_available() else "cpu"
        
        print("🔧 Initializing CUBE RAG Inference Engine...")
        print(f"   Device: {self.device.upper()}")
        
        # Load embedding model
        print(f"   Loading embedding model: {embedding_model}")
        self.embedding_model = SentenceTransformer(embedding_model, device=self.device)
        
        # Initialize Groq client
        if groq_api_key is None:
            groq_api_key = os.environ.get("GROQ_API_KEY")
            if not groq_api_key:
                raise ValueError("GROQ_API_KEY not found. Set it via environment variable or pass it to constructor.")
        
        print(f"   Initializing Groq API with model: {groq_model}")
        self.groq_client = Groq(api_key=groq_api_key)
        
        # Connect to ChromaDB
        print(f"   Connecting to database: {db_path}")
        self.client = chromadb.PersistentClient(
            path=db_path,
            settings=Settings(anonymized_telemetry=False)
        )
        self.collection = self.client.get_collection(name=collection_name)
        
        print(f"✓ Connected to collection: {collection_name}")
        print(f"  Total documents: {self.collection.count()}\n")
    
    def retrieve(
        self,
        query: str,
        top_k: int = 5,
        filters: Optional[Dict] = None
    ) -> List[Dict]:
        """Retrieve relevant chunks from vector database"""
        
        # Generate query embedding with BGE-M3
        query_embedding = self.embedding_model.encode(
            [query],
            normalize_embeddings=True,
            convert_to_tensor=False
        )[0].tolist()
        
        # Prepare filters
        where = filters if filters else None
        
        # Retrieve results
        results = self.collection.query(
            query_embeddings=[query_embedding],
            n_results=top_k,
            where=where
        )
        
        # Format results
        formatted_results = []
        for i in range(len(results['documents'][0])):
            formatted_results.append({
                'id': results['ids'][0][i],
                'content': results['documents'][0][i],
                'metadata': results['metadatas'][0][i],
                'distance': results['distances'][0][i] if 'distances' in results else None
            })
        
        return formatted_results
    
    def build_context(self, results: List[Dict], max_tokens: int = 4000) -> str:
        """Build context string from retrieved results"""
        context_parts = []
        current_tokens = 0
        
        for i, result in enumerate(results, 1):
            metadata = result['metadata']
            content = result['content']
            
            # Create chunk header
            page_name = metadata.get('page_name', 'Unknown')
            book_name = metadata.get('book_name', 'Unknown')
            
            chunk_text = f"[Source {i}: {page_name} from {book_name}]\n{content}\n"
            
            # Rough token estimation (4 chars ≈ 1 token)
            chunk_tokens = len(chunk_text) // 4
            
            if current_tokens + chunk_tokens > max_tokens:
                break
            
            context_parts.append(chunk_text)
            current_tokens += chunk_tokens
        
        return "\n---\n\n".join(context_parts)
    
    def generate_response(
        self,
        query: str,
        context: str,
        temperature: float = 0.3,
        max_tokens: int = 2048
    ) -> str:
        """Generate response using Groq API"""
        
        system_prompt = """You are an expert assistant for CUBE Banking Documentation, a digital account opening platform used by bank staff to create various types of customer accounts (Savings, Current, Term Deposits, NRI accounts, etc.).

Your expertise includes:
- Account opening processes and workflows
- Module functionalities (Branch, NPC, Admin, QC, Auditor, Archival, Inward)
- Compliance requirements (FATCA, FEMA, KYC, AML, PMLA, RBI guidelines)
- Risk classification and customer onboarding
- System architecture and API sequences

Guidelines:
- Answer based ONLY on the provided context from the documentation
- Adapt your response style to match the user's question (brief, detailed, step-by-step, etc.)
- If the context is insufficient, clearly state what information is missing
- When relevant, reference the source using [Source N] notation
- For technical/process questions, be precise and structured
- For overview questions, provide clear summaries
- If diagrams or flows are mentioned in context, explain them naturally

Be helpful, accurate, and professional."""

        user_prompt = f"""Context from CUBE documentation:

{context}

---

Question: {query}

Answer:"""

        # Call Groq API
        chat_completion = self.groq_client.chat.completions.create(
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            model=self.groq_model,
            temperature=temperature,
            max_tokens=max_tokens,
            top_p=0.9,
            stream=False
        )
        
        return chat_completion.choices[0].message.content
    
    def query(
        self,
        question: str,
        top_k: int = 5,
        filters: Optional[Dict] = None,
        show_sources: bool = True,
        render_diagrams: bool = True,
        temperature: float = 0.3
    ) -> Dict:
        """
        End-to-end RAG query
        
        Args:
            question: User's question
            top_k: Number of chunks to retrieve
            filters: Optional metadata filters
            show_sources: Print retrieved sources
            render_diagrams: Render Mermaid diagrams if found
            temperature: LLM temperature (0.0-1.0)
        
        Returns:
            Dictionary with answer, sources, and diagrams
        """
        
        print(f"\n{'='*80}")
        print(f"🔍 QUERY: {question}")
        print('='*80)
        
        # Step 1: Retrieve relevant chunks
        print("\n📚 Retrieving relevant documentation...")
        results = self.retrieve(question, top_k=top_k, filters=filters)
        print(f"   Found {len(results)} relevant chunks")
        
        # Step 2: Build context
        context = self.build_context(results, max_tokens=4000)
        
        # Step 3: Generate answer
        print(f"\n🤖 Generating answer with {self.groq_model}...")
        answer = self.generate_response(question, context, temperature=temperature)
        
        # Step 4: Display results
        print(f"\n{'='*80}")
        print("💡 ANSWER:")
        print('='*80)
        print(answer)
        print(f"\n{'='*80}")
        
        # Step 5: Show sources
        if show_sources:
            print("\n📖 SOURCES:")
            print('='*80)
            for i, result in enumerate(results, 1):
                metadata = result['metadata']
                page_name = metadata.get('page_name', 'Unknown')
                book_name = metadata.get('book_name', 'Unknown')
                hierarchy = metadata.get('hierarchy_path', 'N/A')
                
                print(f"\n[Source {i}] {page_name}")
                print(f"  Book: {book_name}")
                print(f"  Path: {hierarchy}")
                print(f"  Preview: {result['content'][:200]}...")
                
                if metadata.get('mermaid_code'):
                    print(f"  📊 Contains Mermaid Diagram")
            print(f"\n{'='*80}")
        
        # Step 6: Render diagrams
        if render_diagrams:
            try:
                MermaidRenderer.render_all_diagrams(results)
            except Exception as e:
                print(f"\n⚠️  Could not render diagrams: {e}")
        
        return {
            'answer': answer,
            'sources': results,
            'context': context,
            'diagrams': MermaidRenderer.extract_diagrams(results)
        }
    
    def interactive_mode(self):
        """Interactive question-answering mode"""
        print("\n" + "="*80)
        print("🎯 CUBE DOCUMENTATION - INTERACTIVE RAG")
        print("="*80)
        print("\nCommands:")
        print("  - Type your question naturally")
        print("  - 'quit' or 'exit' - Exit interactive mode")
        print("  - 'clear' - Clear screen")
        print("="*80)
        
        while True:
            try:
                question = input("\n💬 Your question: ").strip()
                
                if question.lower() in ['quit', 'exit', 'q']:
                    print("\n👋 Goodbye!")
                    break
                
                if question.lower() == 'clear':
                    os.system('clear' if os.name != 'nt' else 'cls')
                    continue
                
                if not question:
                    continue
                
                # Process query
                self.query(
                    question=question,
                    top_k=5,
                    show_sources=True,
                    render_diagrams=True,
                    temperature=0.3
                )
            
            except KeyboardInterrupt:
                print("\n\n👋 Goodbye!")
                break
            except Exception as e:
                print(f"\n❌ Error: {e}")
                import traceback
                traceback.print_exc()


def main():
    """
    Main function for Google Colab
    
    Setup:
    1. Upload chroma_db.zip to Colab
    2. Extract: !unzip -q chroma_db.zip -d /content/
    3. Set Groq API key: os.environ['GROQ_API_KEY'] = 'your-key-here'
    4. Run this script
    """
    
    # Check for Groq API key
    if 'GROQ_API_KEY' not in os.environ:
        print("⚠️  GROQ_API_KEY not found!")
        print("\nTo set it, run:")
        print("import os")
        print("os.environ['GROQ_API_KEY'] = 'your-groq-api-key-here'")
        return
    
    # Initialize inference engine
    engine = CUBEGroqInference(
        db_path="/content/chroma_db",
        collection_name="cube_docs_optimized",
        embedding_model="BAAI/bge-m3",
        groq_model="llama-3.1-8b-instant",  # 8B: fast, cost-efficient, sufficient for RAG
        use_gpu=True
    )
    
    # Example queries
    print("\n" + "="*80)
    print("📋 EXAMPLE QUERIES")
    print("="*80)
    
    examples = [
        "What are the requirements for opening an NRI account?",
        "How does the NPC clearance process work?",
        "What is risk classification and how is it determined?",
        "Explain the Branch module functionality"
        
    ]
    
    print("\nExample questions you can ask:")
    for i, example in enumerate(examples, 1):
        print(f"  {i}. {example}")
    
    # Run example query
    print("\n" + "="*80)
    response = input("\nRun example query #1? (y/n): ").strip().lower()
    if response == 'y':
        engine.query(
            question=examples[0],
            top_k=5,
            show_sources=True,
            render_diagrams=True
        )
    
    # Start interactive mode
    print("\n" + "="*80)
    response = input("\nStart interactive mode? (y/n): ").strip().lower()
    if response == 'y':
        engine.interactive_mode()
    else:
        print("\n💡 To use the engine programmatically:")
        print("```python")
        print("result = engine.query('Your question here')")
        print("print(result['answer'])")
        print("```")


if __name__ == "__main__":
    main()
