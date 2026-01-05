#!/usr/bin/env python3
"""
Blade Inference with Strategy 2: Description-First Retrieval

End-to-end inference system for blade templates with:
- Description-first retrieval
- Cross-encoder re-ranking
- Smart snippet extraction
- Token usage optimization
- LLM integration
"""

import os
import sys
from pathlib import Path
import time
from typing import List, Dict
import json

# Add parent directory to path
sys.path.append(str(Path(__file__).parent.parent))

from dotenv import load_dotenv
from groq import Groq

from utils.blade_description_engine import BladeDescriptionEngine

# Load environment variables
load_dotenv()


class BladeInferenceSystem:
    """
    Complete inference system for blade templates
    """
    
    def __init__(
        self,
        groq_api_key: str = None,
        db_path: str = None
    ):
        """
        Initialize inference system
        
        Args:
            groq_api_key: Groq API key
            db_path: Path to ChromaDB
        """
        # Initialize engine
        print("🚀 Initializing Blade Inference System...")
        print("=" * 60)
        
        self.engine = BladeDescriptionEngine(db_path=db_path)
        
        # Initialize LLM client
        api_key = groq_api_key or os.getenv("GROQ_API_KEY")
        if not api_key:
            # Fallback key (for testing only)
            api_key = "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"
        
        self.groq_client = Groq(api_key=api_key)
        
        print("✅ System initialized!")
        print()
    
    def query(
        self,
        query_text: str,
        top_k: int = 5,
        initial_candidates: int = 20,
        max_snippet_chars: int = 2000,
        generate_llm_response: bool = True
    ) -> Dict:
        """
        Execute full query pipeline
        
        Args:
            query_text: User query
            top_k: Number of final results
            initial_candidates: Candidates for re-ranking
            max_snippet_chars: Max snippet size
            generate_llm_response: Whether to generate LLM response
            
        Returns:
            Complete query results
        """
        start_time = time.time()
        
        # Phase 1: Retrieve with engine
        print(f"🔍 Query: {query_text}")
        print("=" * 60)
        
        results = self.engine.query(
            query_text=query_text,
            top_k=top_k,
            initial_candidates=initial_candidates,
            max_snippet_chars=max_snippet_chars,
            use_rerank=True
        )
        
        retrieval_time = time.time() - start_time
        
        # Phase 2: Display results
        self.display_results(results, retrieval_time)
        
        # Phase 3: Generate LLM response
        llm_response = None
        llm_time = 0
        
        if generate_llm_response:
            llm_start = time.time()
            llm_response = self.generate_llm_response(query_text, results)
            llm_time = time.time() - llm_start
            
            print("\n" + "=" * 60)
            print("🤖 LLM RESPONSE:")
            print("=" * 60)
            print(llm_response)
            print()
        
        total_time = time.time() - start_time
        
        # Phase 4: Show metrics
        self.display_metrics(results, retrieval_time, llm_time, total_time)
        
        return {
            'query': query_text,
            'results': results,
            'llm_response': llm_response,
            'metrics': {
                'retrieval_time': retrieval_time,
                'llm_time': llm_time,
                'total_time': total_time,
                'num_results': len(results),
                'total_snippet_chars': sum(r['snippet_length'] for r in results),
                'total_content_chars': sum(r['content_length'] for r in results),
            }
        }
    
    def display_results(self, results: List[Dict], retrieval_time: float):
        """Display retrieval results"""
        print(f"\n✅ Retrieved {len(results)} results in {retrieval_time:.2f}s\n")
        
        for i, result in enumerate(results, 1):
            score = result.get('rerank_score')
            score_str = f"{score:.3f}" if score is not None else "N/A"
            
            print(f"{i}. {result['file_name']} (Score: {score_str})")
            print(f"   Section: {result['section']}")
            print(f"   Description: {result['description'][:80]}...")
            print(f"   Snippet: {result['snippet_length']} chars (from {result['content_length']} chars)")
            
            if result.get('has_form'):
                print(f"   📝 Contains Form")
            
            print()
    
    def generate_llm_response(
        self,
        query_text: str,
        results: List[Dict]
    ) -> str:
        """
        Generate LLM response using retrieved context
        
        Args:
            query_text: User query
            results: Retrieved results
            
        Returns:
            LLM response
        """
        # Format context
        context = self.engine.format_context_for_llm(
            results,
            include_code=True,
            include_descriptions=True
        )
        
        # System prompt
        system_prompt = """You are an expert Laravel Blade developer and code analyst.

Your task is to answer the user's question based STRICTLY on the provided blade template context.

Guidelines:
1. Provide clear, accurate explanations
2. Reference specific files and code when relevant
3. If code snippets are provided, you may include them in your answer
4. If the context doesn't contain enough information, say so
5. Be concise but thorough

Do not hallucinate or make assumptions beyond what's in the context."""
        
        user_prompt = f"""USER QUERY: {query_text}

RETRIEVED CONTEXT:
{context}

Please answer the user's query based on the above context."""
        
        try:
            # Call Groq API
            chat_completion = self.groq_client.chat.completions.create(
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_prompt}
                ],
                model="llama-3.3-70b-versatile",
                temperature=0.2,
                max_tokens=2048
            )
            
            return chat_completion.choices[0].message.content
            
        except Exception as e:
            return f"⚠️ LLM Error: {e}"
    
    def display_metrics(
        self,
        results: List[Dict],
        retrieval_time: float,
        llm_time: float,
        total_time: float
    ):
        """Display performance metrics"""
        total_snippet_chars = sum(r['snippet_length'] for r in results)
        total_content_chars = sum(r['content_length'] for r in results)
        reduction = ((total_content_chars - total_snippet_chars) / total_content_chars * 100) if total_content_chars > 0 else 0
        
        print("=" * 60)
        print("📊 PERFORMANCE METRICS:")
        print("=" * 60)
        print(f"Retrieval time: {retrieval_time:.2f}s")
        print(f"LLM time: {llm_time:.2f}s")
        print(f"Total time: {total_time:.2f}s")
        print(f"\nResults: {len(results)}")
        print(f"Total snippet size: {total_snippet_chars} chars (~{total_snippet_chars//4} tokens)")
        print(f"Original content size: {total_content_chars} chars (~{total_content_chars//4} tokens)")
        print(f"Token reduction: {reduction:.1f}%")
        print("=" * 60)
    
    def batch_test(self, queries: List[str], top_k: int = 3):
        """
        Test multiple queries
        
        Args:
            queries: List of test queries
            top_k: Results per query
        """
        print("\n" + "=" * 60)
        print("🧪 BATCH TEST")
        print("=" * 60)
        
        results_summary = []
        
        for i, query in enumerate(queries, 1):
            print(f"\n[{i}/{len(queries)}] Query: {query}")
            print("-" * 60)
            
            start = time.time()
            results = self.engine.query(
                query_text=query,
                top_k=top_k,
                initial_candidates=15,
                max_snippet_chars=1500,
                use_rerank=True
            )
            duration = time.time() - start
            
            if results:
                top_file = results[0]['file_name']
                top_score = results[0].get('rerank_score', 0)
                snippet_size = sum(r['snippet_length'] for r in results)
                
                print(f"✅ Top result: {top_file} (score: {top_score:.3f})")
                print(f"   Time: {duration:.2f}s, Context: {snippet_size} chars")
                
                results_summary.append({
                    'query': query,
                    'top_file': top_file,
                    'score': top_score,
                    'time': duration,
                    'context_size': snippet_size
                })
            else:
                print(f"❌ No results found")
        
        # Summary
        print("\n" + "=" * 60)
        print("📋 BATCH TEST SUMMARY")
        print("=" * 60)
        
        avg_time = sum(r['time'] for r in results_summary) / len(results_summary) if results_summary else 0
        avg_context = sum(r['context_size'] for r in results_summary) / len(results_summary) if results_summary else 0
        
        print(f"Total queries: {len(queries)}")
        print(f"Successful: {len(results_summary)}")
        print(f"Avg time: {avg_time:.2f}s")
        print(f"Avg context: {int(avg_context)} chars (~{int(avg_context)//4} tokens)")
        print("=" * 60)
        
        return results_summary


def main():
    """Main function for CLI usage"""
    print("""
╔══════════════════════════════════════════════════════════╗
║  Blade Inference System - Strategy 2                     ║
║  Description-First Retrieval with Smart Snippets         ║
╚══════════════════════════════════════════════════════════╝
""")
    
    try:
        # Initialize system
        system = BladeInferenceSystem()
        
        # Display system info
        stats = system.engine.get_stats()
        print("\n📊 System Info:")
        print(f"  Database: {stats['total_documents']} blade templates")
        print(f"  Embedding: {stats['embedding_model']}")
        print(f"  Re-ranker: {stats['cross_encoder']}")
        print()
        
        # Example queries
        example_queries = [
            "How does the login form protect against CSRF?",
            "Show me the user chat interface implementation",
            "What forms require approval workflow?",
            "How is pagination implemented in blade views?",
            "Where is authentication checking done in navigation?",
        ]
        
        print("=" * 60)
        print("🔍 EXAMPLE QUERIES:")
        print("=" * 60)
        for i, q in enumerate(example_queries, 1):
            print(f"{i}. {q}")
        print()
        
        # Interactive mode
        print("=" * 60)
        print("💡 Enter a query or press Enter to use example query #1")
        print("=" * 60)
        
        user_input = input("\nYour query: ").strip()
        
        if not user_input:
            query = example_queries[0]
            print(f"Using example query: {query}\n")
        else:
            query = user_input
        
        # Execute query
        result = system.query(
            query_text=query,
            top_k=5,
            initial_candidates=20,
            max_snippet_chars=2000,
            generate_llm_response=True
        )
        
        # Ask if user wants batch test
        print("\n" + "=" * 60)
        batch_test = input("Run batch test with all example queries? (y/n): ").strip().lower()
        
        if batch_test == 'y':
            system.batch_test(example_queries, top_k=3)
        
        print("\n✅ Inference complete!")
        
    except KeyboardInterrupt:
        print("\n\n⚠️  Interrupted by user")
    except Exception as e:
        print(f"\n❌ Error: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()
