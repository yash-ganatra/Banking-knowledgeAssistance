#!/usr/bin/env python3
"""
Smart Snippet Extractor for Blade Templates

Extracts query-relevant parts from large blade files without truncation.
Uses semantic similarity to identify and extract the most relevant sections.
"""

import re
from typing import List, Dict, Tuple
from bs4 import BeautifulSoup, Comment
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np


class SmartSnippetExtractor:
    """
    Intelligently extracts query-relevant snippets from blade templates
    """
    
    def __init__(self, model_name: str = "sentence-transformers/all-MiniLM-L6-v2"):
        """
        Initialize with a lightweight sentence transformer for scoring
        
        Args:
            model_name: Sentence transformer model for similarity scoring
        """
        # Use lightweight model for fast snippet scoring
        self.model = SentenceTransformer(model_name)
        
    def extract_relevant_snippet(
        self, 
        content: str, 
        query: str, 
        max_chars: int = 2000,
        preserve_structure: bool = True
    ) -> str:
        """
        Extract most relevant parts of content for the query
        
        Args:
            content: Full blade template content
            query: User query
            max_chars: Maximum characters to extract
            preserve_structure: Whether to maintain HTML structure
            
        Returns:
            Extracted snippet containing most relevant parts
        """
        # If content is already small, return as is
        if len(content) <= max_chars:
            return content
        
        # Split into semantic blocks
        blocks = self.split_into_semantic_blocks(content)
        
        if not blocks:
            # Fallback: simple truncation with end marker
            return content[:max_chars] + "\n... (content truncated)"
        
        # Score blocks against query
        scored_blocks = self.score_blocks_against_query(blocks, query)
        
        # Assemble snippet from top-scoring blocks
        snippet = self.assemble_snippet(scored_blocks, max_chars, preserve_structure)
        
        return snippet
    
    def split_into_semantic_blocks(self, content: str) -> List[Dict[str, any]]:
        """
        Split blade content into semantic blocks (forms, divs, scripts, etc.)
        
        Args:
            content: Full blade template content
            
        Returns:
            List of blocks with metadata
        """
        blocks = []
        
        try:
            # Parse HTML with BeautifulSoup
            soup = BeautifulSoup(content, 'html.parser')
            
            # Extract forms (high priority)
            for idx, form in enumerate(soup.find_all('form')):
                blocks.append({
                    'type': 'form',
                    'content': str(form),
                    'priority': 1.5,  # Forms often contain key information
                    'position': len(blocks)
                })
            
            # Extract major divs with classes or IDs
            for div in soup.find_all('div', class_=True):
                if len(str(div)) > 100:  # Only meaningful divs
                    blocks.append({
                        'type': 'div',
                        'content': str(div),
                        'priority': 1.0,
                        'position': len(blocks),
                        'classes': ' '.join(div.get('class', []))
                    })
            
            # Extract sections
            for section in soup.find_all('section'):
                if len(str(section)) > 100:
                    blocks.append({
                        'type': 'section',
                        'content': str(section),
                        'priority': 1.2,
                        'position': len(blocks)
                    })
            
            # Extract script blocks (for JS-heavy queries)
            for script in soup.find_all('script'):
                script_content = script.string
                if script_content and len(script_content.strip()) > 50:
                    blocks.append({
                        'type': 'script',
                        'content': str(script),
                        'priority': 0.8,
                        'position': len(blocks)
                    })
            
            # Extract style blocks
            for style in soup.find_all('style'):
                style_content = style.string
                if style_content and len(style_content.strip()) > 50:
                    blocks.append({
                        'type': 'style',
                        'content': str(style),
                        'priority': 0.5,
                        'position': len(blocks)
                    })
            
        except Exception as e:
            # Fallback: split by blade directives
            blocks = self._fallback_split(content)
        
        # If no blocks found, split by paragraphs/sections
        if not blocks:
            blocks = self._split_by_paragraphs(content)
        
        return blocks
    
    def _fallback_split(self, content: str) -> List[Dict[str, any]]:
        """
        Fallback: split by blade directives when HTML parsing fails
        """
        blocks = []
        
        # Split by major blade directives
        patterns = [
            r'@section\([^)]+\).*?@endsection',
            r'@if\([^)]+\).*?@endif',
            r'@foreach\([^)]+\).*?@endforeach',
            r'<form.*?</form>',
        ]
        
        for pattern in patterns:
            matches = re.finditer(pattern, content, re.DOTALL)
            for match in matches:
                blocks.append({
                    'type': 'blade_directive',
                    'content': match.group(0),
                    'priority': 1.0,
                    'position': len(blocks)
                })
        
        return blocks
    
    def _split_by_paragraphs(self, content: str) -> List[Dict[str, any]]:
        """
        Last resort: split by double newlines
        """
        blocks = []
        paragraphs = content.split('\n\n')
        
        for idx, para in enumerate(paragraphs):
            if len(para.strip()) > 50:
                blocks.append({
                    'type': 'paragraph',
                    'content': para,
                    'priority': 1.0,
                    'position': idx
                })
        
        return blocks
    
    def score_blocks_against_query(
        self, 
        blocks: List[Dict], 
        query: str
    ) -> List[Tuple[Dict, float]]:
        """
        Score each block's relevance to the query
        
        Args:
            blocks: List of content blocks
            query: User query
            
        Returns:
            List of (block, score) tuples sorted by score
        """
        if not blocks:
            return []
        
        # Get query embedding
        query_embedding = self.model.encode([query], convert_to_numpy=True)
        
        # Get block embeddings (use text content only)
        block_texts = []
        for block in blocks:
            # Extract text from HTML for better embedding
            try:
                soup = BeautifulSoup(block['content'], 'html.parser')
                text = soup.get_text(separator=' ', strip=True)
                # Also include blade directives
                text += ' ' + ' '.join(re.findall(r'@\w+', block['content']))
            except:
                text = block['content']
            
            block_texts.append(text[:500])  # Limit for embedding
        
        # Encode all blocks
        block_embeddings = self.model.encode(block_texts, convert_to_numpy=True)
        
        # Calculate similarities
        similarities = cosine_similarity(query_embedding, block_embeddings)[0]
        
        # Combine with priority
        scored_blocks = []
        for block, similarity in zip(blocks, similarities):
            # Boost score by block priority
            final_score = similarity * block.get('priority', 1.0)
            scored_blocks.append((block, final_score))
        
        # Sort by score descending
        scored_blocks.sort(key=lambda x: x[1], reverse=True)
        
        return scored_blocks
    
    def assemble_snippet(
        self, 
        scored_blocks: List[Tuple[Dict, float]], 
        max_chars: int,
        preserve_structure: bool = True
    ) -> str:
        """
        Assemble final snippet from top-scoring blocks
        
        Args:
            scored_blocks: Scored blocks
            max_chars: Maximum characters
            preserve_structure: Whether to reorder by original position
            
        Returns:
            Assembled snippet
        """
        if not scored_blocks:
            return ""
        
        # Select blocks until max_chars
        selected_blocks = []
        current_chars = 0
        
        for block, score in scored_blocks:
            block_length = len(block['content'])
            
            if current_chars + block_length <= max_chars:
                selected_blocks.append(block)
                current_chars += block_length
            elif current_chars < max_chars * 0.8:
                # If we have space, truncate this block to fit
                remaining = max_chars - current_chars
                truncated_content = block['content'][:remaining]
                truncated_block = block.copy()
                truncated_block['content'] = truncated_content + "..."
                selected_blocks.append(truncated_block)
                break
            else:
                break
        
        if not selected_blocks:
            # Take at least the top block, truncated
            top_block = scored_blocks[0][0]
            return top_block['content'][:max_chars] + "\n... (truncated)"
        
        # Reorder by original position if preserving structure
        if preserve_structure:
            selected_blocks.sort(key=lambda b: b['position'])
        
        # Assemble with markers
        snippet_parts = []
        for block in selected_blocks:
            block_type = block.get('type', 'unknown')
            snippet_parts.append(f"<!-- {block_type.upper()} -->")
            snippet_parts.append(block['content'])
        
        snippet = '\n\n'.join(snippet_parts)
        
        # Add truncation marker if needed
        if current_chars >= max_chars * 0.9:
            snippet += "\n\n... (content truncated for brevity)"
        
        return snippet
    
    def extract_form_focused(
        self, 
        content: str, 
        query: str, 
        max_chars: int = 2000
    ) -> str:
        """
        Specialized extraction that prioritizes form elements
        Useful for queries about forms, CSRF, validation, etc.
        
        Args:
            content: Full blade content
            query: User query
            max_chars: Maximum characters
            
        Returns:
            Form-focused snippet
        """
        # Check if query is about forms
        form_keywords = ['form', 'submit', 'csrf', 'validation', 'input', 'button']
        is_form_query = any(keyword in query.lower() for keyword in form_keywords)
        
        if not is_form_query:
            # Use regular extraction
            return self.extract_relevant_snippet(content, query, max_chars)
        
        # Extract all forms
        try:
            soup = BeautifulSoup(content, 'html.parser')
            forms = soup.find_all('form')
            
            if not forms:
                return self.extract_relevant_snippet(content, query, max_chars)
            
            # Score forms against query
            form_blocks = []
            for idx, form in enumerate(forms):
                form_blocks.append({
                    'type': 'form',
                    'content': str(form),
                    'priority': 2.0,  # High priority
                    'position': idx
                })
            
            scored_forms = self.score_blocks_against_query(form_blocks, query)
            
            # Return top form(s) that fit in max_chars
            snippet_parts = []
            current_chars = 0
            
            for form_block, score in scored_forms:
                form_content = form_block['content']
                if current_chars + len(form_content) <= max_chars:
                    snippet_parts.append(f"<!-- FORM (relevance: {score:.2f}) -->")
                    snippet_parts.append(form_content)
                    current_chars += len(form_content) + 50
                else:
                    break
            
            if snippet_parts:
                return '\n\n'.join(snippet_parts)
            else:
                # Fallback
                return self.extract_relevant_snippet(content, query, max_chars)
                
        except Exception as e:
            return self.extract_relevant_snippet(content, query, max_chars)


# Utility functions
def extract_snippet(content: str, query: str, max_chars: int = 2000) -> str:
    """
    Convenience function for quick snippet extraction
    
    Args:
        content: Full blade content
        query: User query  
        max_chars: Maximum characters
        
    Returns:
        Extracted snippet
    """
    extractor = SmartSnippetExtractor()
    return extractor.extract_relevant_snippet(content, query, max_chars)


# Example usage
if __name__ == "__main__":
    print("Smart Snippet Extractor - Test")
    print("=" * 50)
    
    # Sample large blade content
    sample_content = """
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login</title>
        <style>
            .login-form { padding: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Login Page</h1>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit">Login</button>
            </form>
            
            <div class="footer">
                <p>Copyright 2024</p>
            </div>
        </div>
        
        <script>
            console.log('Login page loaded');
        </script>
    </body>
    </html>
    """ * 50  # Make it large
    
    # Test extraction
    extractor = SmartSnippetExtractor()
    
    print(f"\nOriginal content: {len(sample_content)} characters")
    
    # Test with form query
    query = "How does CSRF protection work in the login form?"
    snippet = extractor.extract_relevant_snippet(sample_content, query, max_chars=500)
    
    print(f"\nQuery: {query}")
    print(f"Extracted snippet: {len(snippet)} characters")
    print(f"\nSnippet preview:\n{snippet[:300]}...")
    
    print("\n✅ Smart Snippet Extractor ready for use!")
