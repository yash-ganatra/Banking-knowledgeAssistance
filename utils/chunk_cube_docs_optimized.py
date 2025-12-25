"""
Optimized Chunking Strategy for CUBE Banking Documentation
Creates semantically rich chunks with hierarchical metadata for maximum retrieval accuracy
"""

import json
import re
from typing import List, Dict, Any, Set
from datetime import datetime
from collections import defaultdict
import tiktoken

# Initialize tokenizer
encoding = tiktoken.get_encoding("cl100k_base")


class CUBEDocumentChunker:
    def __init__(self, json_file_path: str):
        self.json_file_path = json_file_path
        self.data = None
        self.chunks = []
        self.chunk_id_counter = 1
        
        # Chunking parameters
        self.max_chunk_tokens = 600
        self.overlap_tokens = 100
        self.min_chunk_tokens = 150
        
        # DISABLED: Concept extraction patterns cause noise in semantic search
        # Semantic embeddings already capture these concepts naturally
        # Only use explicit filters when needed (account_type, module)
        self.concept_patterns = {}
        
        # Use only for explicit metadata filtering (not for concept tags)
        self.account_type_patterns = r'\b(savings|current|term deposit|delight|NRI|NRO|NRE|FCNR|elite)\b'
        self.compliance_patterns = r'\b(FATCA|FEMA|RBI|PMLA|AML)\b'
        
        # High-value cross-reference topics (for synthetic chunks)
        self.cross_ref_topics = {
            'nri_complete': {
                'title': 'NRI Account Opening - Complete Guide',
                'page_ids': [275, 277, 283, 285],
                'keywords': ['NRI', 'NRO', 'NRE', 'residential status', 'OCI', 'VISA', 'declaration']
            },
            'npc_process': {
                'title': 'NPC Clearance Process - End to End',
                'page_ids': [302, 307],
                'keywords': ['NPC', 'L1', 'L2', 'L3', 'reviewer', 'clearance', 'discrepancy']
            },
            'admin_apis': {
                'title': 'Admin API Sequence - Customer & Account Creation',
                'page_ids': [303, 308],
                'keywords': ['admin', 'API', 'customer ID', 'account ID', 'funding', 'KYC']
            },
            'compliance_kyc': {
                'title': 'Risk Classification and Compliance Requirements',
                'page_ids': [280, 278, 285],
                'keywords': ['risk', 'KYC', 'FATCA', 'AML', 'OVD', 'declaration']
            },
            'funding_methods': {
                'title': 'Account Funding Methods Comparison',
                'page_ids': [281],
                'keywords': ['funding', 'NEFT', 'IMPS', 'cheque', 'NILIP', 'zero balance']
            },
            'qc_audit_flow': {
                'title': 'QC and Audit Verification Process',
                'page_ids': [304, 309],
                'keywords': ['QC', 'audit', 'quality control', 'L3', 'archival']
            }
        }
    
    def load_data(self):
        """Load JSON data"""
        with open(self.json_file_path, 'r', encoding='utf-8') as f:
            self.data = json.load(f)
        print(f"✓ Loaded data: {self.data['total_books']} books, {self.data['total_pages']} pages")
    
    def count_tokens(self, text: str) -> int:
        """Count tokens in text"""
        return len(encoding.encode(text))
    
    def clean_html_content(self, html_text: str) -> str:
        """Clean HTML content and preserve structure"""
        # Remove extra whitespace
        text = re.sub(r'\n\s*\n', '\n\n', html_text)
        text = re.sub(r' +', ' ', text)
        return text.strip()
    
    def extract_account_types(self, text: str) -> List[str]:
        """Extract ONLY account types for filtering (not general concepts)"""
        account_types = []
        text_lower = text.lower()
        
        # Only extract if it's a primary topic (appears in title or first paragraph)
        matches = re.findall(self.account_type_patterns, text_lower[:500], re.IGNORECASE)
        if matches:
            account_types = list(set([m.lower() for m in matches if m]))
        
        return account_types
    
    def extract_compliance_terms(self, text: str) -> List[str]:
        """Extract ONLY major compliance terms (FATCA, FEMA, etc.)"""
        terms = []
        text_lower = text.lower()
        
        matches = re.findall(self.compliance_patterns, text_lower, re.IGNORECASE)
        if matches:
            terms = list(set([m.upper() for m in matches if m]))
        
        return terms
    
    def extract_module_from_title(self, page_name: str, chapter_name: str = "") -> str:
        """
        Extract module ONLY if page is dedicated to that module.
        Only checks page title and chapter name (not content).
        """
        combined = (page_name + " " + chapter_name).lower()
        
        # Module-specific pages (strict matching)
        if 'branch module' in combined or 'branch sale' in combined:
            return 'branch'
        elif 'npc' in combined and any(x in combined for x in ['clearance', 'module', 'review', 'flow']):
            return 'npc'
        elif 'admin' in combined and any(x in combined for x in ['module', 'flow', 'api']):
            return 'admin'
        elif any(x in combined for x in ['qc', 'quality control']):
            return 'qc'
        elif 'audit' in combined and 'module' in combined:
            return 'auditor'
        elif 'inward' in combined:
            return 'inward'
        elif 'archival' in combined and 'module' in combined:
            return 'archival'
        
        return None
    
    def convert_mermaid_to_text(self, mermaid_code: str, page_name: str) -> str:
        """Convert mermaid diagram to descriptive text"""
        description = f"Flowchart: {page_name}\n\n"
        
        # Extract nodes and relationships
        nodes = re.findall(r'([A-Z0-9]+)\[(.*?)\]', mermaid_code)
        arrows = re.findall(r'([A-Z0-9]+)\s*(?:-->|--)\s*([A-Z0-9]+)', mermaid_code)
        
        if nodes:
            description += "Process Steps:\n"
            for node_id, node_text in nodes:
                clean_text = node_text.strip()
                description += f"- {clean_text}\n"
        
        if arrows:
            description += "\nProcess Flow:\n"
            node_map = {node_id: text for node_id, text in nodes}
            for source, target in arrows:
                source_text = node_map.get(source, source)
                target_text = node_map.get(target, target)
                description += f"{source_text} → {target_text}\n"
        
        return description
    
    def split_long_content(self, content: str, metadata: Dict) -> List[Dict]:
        """Split long content into smaller semantic chunks"""
        chunks = []
        
        # Try to split by numbered sections first
        sections = re.split(r'\n\n(?=\d+\.)', content)
        
        if len(sections) == 1:
            # No numbered sections, split by paragraphs
            sections = re.split(r'\n\n+', content)
        
        current_chunk = ""
        current_tokens = 0
        
        for section in sections:
            section = section.strip()
            if not section:
                continue
            
            section_tokens = self.count_tokens(section)
            
            # If single section exceeds max, force split
            if section_tokens > self.max_chunk_tokens:
                if current_chunk:
                    chunks.append({
                        'content': current_chunk.strip(),
                        'metadata': metadata.copy(),
                        'tokens': current_tokens
                    })
                    current_chunk = ""
                    current_tokens = 0
                
                # Split by sentences
                sentences = re.split(r'(?<=[.!?])\s+', section)
                for sent in sentences:
                    sent_tokens = self.count_tokens(sent)
                    if current_tokens + sent_tokens > self.max_chunk_tokens and current_chunk:
                        chunks.append({
                            'content': current_chunk.strip(),
                            'metadata': metadata.copy(),
                            'tokens': current_tokens
                        })
                        current_chunk = sent + " "
                        current_tokens = sent_tokens
                    else:
                        current_chunk += sent + " "
                        current_tokens += sent_tokens
            
            # Normal section accumulation
            elif current_tokens + section_tokens > self.max_chunk_tokens:
                if current_chunk:
                    chunks.append({
                        'content': current_chunk.strip(),
                        'metadata': metadata.copy(),
                        'tokens': current_tokens
                    })
                
                # Add overlap from previous chunk
                overlap_text = current_chunk.split()[-20:] if current_chunk else []
                current_chunk = " ".join(overlap_text) + "\n\n" + section
                current_tokens = self.count_tokens(current_chunk)
            else:
                if current_chunk:
                    current_chunk += "\n\n" + section
                else:
                    current_chunk = section
                current_tokens += section_tokens
        
        # Add remaining content
        if current_chunk and current_tokens > self.min_chunk_tokens:
            chunks.append({
                'content': current_chunk.strip(),
                'metadata': metadata.copy(),
                'tokens': current_tokens
            })
        
        return chunks
    
    def create_page_chunk(self, page: Dict, chapter: Dict, book: Dict) -> List[Dict]:
        """Create chunk(s) from a single page"""
        chunks = []
        
        # Build hierarchy path
        hierarchy = f"{self.data['shelf']['name']} > {book['name']} > {chapter['name']} > {page['name']}"
        
        # Base metadata
        base_metadata = {
            'chunk_id': f"page_{page['id']}",
            'page_id': page['id'],
            'page_name': page['name'],
            'page_slug': page['slug'],
            'chapter_id': chapter['id'],
            'chapter_name': chapter['name'],
            'chapter_slug': chapter['slug'],
            'book_id': book['id'],
            'book_name': book['name'],
            'book_slug': book['slug'],
            'shelf_name': self.data['shelf']['name'],
            'hierarchy_path': hierarchy,
            'priority': page['priority'],
            'has_mermaid': page['has_mermaid'],
            'content_format': page['content_format']
        }
        
        # Process main content
        content = self.clean_html_content(page['content'])
        
        # REMOVED: Generic concept extraction (causes noise)
        # Semantic embeddings capture concepts better than keyword matching
        
        # Extract ONLY high-value filterable fields
        account_types = self.extract_account_types(content + " " + page['name'])
        compliance_terms = self.extract_compliance_terms(content)
        module = self.extract_module_from_title(page['name'], chapter.get('name', ''))
        
        # Add to metadata only if found
        if account_types:
            base_metadata['account_types'] = account_types
        if compliance_terms:
            base_metadata['compliance_terms'] = compliance_terms
        if module:
            base_metadata['module'] = module  # Singular, not plural
        
        # ENHANCED: Combine diagram with page content for better context
        if page['has_mermaid'] and page['mermaid_diagram']:
            diagram_text = self.convert_mermaid_to_text(page['mermaid_diagram'], page['name'])
            # Append diagram description to page content
            content = content + "\n\n" + diagram_text if content else diagram_text
            base_metadata['mermaid_code'] = page['mermaid_diagram']  # Preserve for rendering
            base_metadata['is_mermaid'] = True
            base_metadata['chunk_type'] = 'page_with_diagram'
        
        # Check if content needs splitting
        content_tokens = self.count_tokens(content)
        
        if content_tokens > self.max_chunk_tokens:
            # Split into multiple chunks
            split_chunks = self.split_long_content(content, base_metadata)
            for idx, chunk_data in enumerate(split_chunks):
                chunk_data['metadata']['chunk_id'] = f"page_{page['id']}_part{idx+1}"
                chunk_data['metadata']['part_number'] = idx + 1
                chunk_data['metadata']['total_parts'] = len(split_chunks)
                # Preserve mermaid_code in all parts if present
                if 'mermaid_code' in base_metadata:
                    chunk_data['metadata']['mermaid_code'] = base_metadata['mermaid_code']
                chunks.append(chunk_data)
        else:
            # Single chunk with combined content
            chunks.append({
                'content': content,
                'metadata': base_metadata,
                'tokens': content_tokens
            })
        
        return chunks
    
    def create_synthetic_chunks(self) -> List[Dict]:
        """Create synthetic cross-reference chunks for complex topics"""
        synthetic_chunks = []
        
        # Build page lookup
        page_lookup = {}
        for book in self.data['books']:
            for chapter in book.get('chapters', []):
                for page in chapter.get('pages', []):
                    page_lookup[page['id']] = {
                        'page': page,
                        'chapter': chapter,
                        'book': book
                    }
        
        for topic_key, topic_info in self.cross_ref_topics.items():
            combined_content = f"# {topic_info['title']}\n\n"
            combined_content += "This is a comprehensive summary combining information from multiple documentation pages.\n\n"
            
            source_pages = []
            all_concepts = set()
            
            for page_id in topic_info['page_ids']:
                if page_id in page_lookup:
                    page_data = page_lookup[page_id]
                    page = page_data['page']
                    chapter = page_data['chapter']
                    book = page_data['book']
                    
                    content = self.clean_html_content(page['content'])
                    combined_content += f"\n## {page['name']}\n"
                    combined_content += f"(From: {book['name']} > {chapter['name']})\n\n"
                    combined_content += content + "\n\n"
                    
                    source_pages.append({
                        'page_id': page['id'],
                        'page_name': page['name'],
                        'book_name': book['name']
                    })
                    
                    all_concepts.update(self.extract_concepts(content))
            
            # Create synthetic chunk
            tokens = self.count_tokens(combined_content)
            
            # If too long, create summary instead
            if tokens > self.max_chunk_tokens * 1.5:
                # Extract key points only
                summary_content = f"# {topic_info['title']}\n\n"
                summary_content += "## Overview\n\n"
                
                for page_id in topic_info['page_ids']:
                    if page_id in page_lookup:
                        page = page_lookup[page_id]['page']
                        content = self.clean_html_content(page['content'])
                        
                        # Extract first 2 paragraphs or bullet points
                        paragraphs = content.split('\n\n')[:2]
                        summary_content += f"\n### {page['name']}\n"
                        summary_content += '\n\n'.join(paragraphs) + "\n"
                
                combined_content = summary_content
                tokens = self.count_tokens(combined_content)
            
            synthetic_chunks.append({
                'content': combined_content,
                'metadata': {
                    'chunk_id': f"synthetic_{topic_key}",
                    'chunk_type': 'synthetic_summary',
                    'topic': topic_key,
                    'title': topic_info['title'],
                    'source_pages': source_pages,
                    'keywords': topic_info['keywords'],
                    'concept_tags': list(all_concepts),
                    'is_synthetic': True
                },
                'tokens': tokens
            })
        
        return synthetic_chunks
    
    def process_all_pages(self):
        """Process all pages in the documentation"""
        print("\n📄 Processing pages...")
        
        for book in self.data['books']:
            book_name = book['name']
            print(f"\n  Processing book: {book_name}")
            
            for chapter in book.get('chapters', []):
                chapter_name = chapter['name']
                
                for page in chapter.get('pages', []):
                    page_chunks = self.create_page_chunk(page, chapter, book)
                    self.chunks.extend(page_chunks)
        
        print(f"\n✓ Created {len(self.chunks)} page-level chunks")
    
    def add_synthetic_chunks(self):
        """Add synthetic cross-reference chunks"""
        print("\n🔗 Creating synthetic cross-reference chunks...")
        
        synthetic_chunks = self.create_synthetic_chunks()
        self.chunks.extend(synthetic_chunks)
        
        print(f"✓ Created {len(synthetic_chunks)} synthetic chunks")
    
    def generate_statistics(self) -> Dict:
        """Generate chunking statistics"""
        stats = {
            'total_chunks': len(self.chunks),
            'page_chunks': sum(1 for c in self.chunks if not c['metadata'].get('is_synthetic')),
            'synthetic_chunks': sum(1 for c in self.chunks if c['metadata'].get('is_synthetic')),
            'diagram_chunks': sum(1 for c in self.chunks if c['metadata'].get('is_mermaid')),
            'avg_tokens': sum(c['tokens'] for c in self.chunks) / len(self.chunks) if self.chunks else 0,
            'max_tokens': max((c['tokens'] for c in self.chunks), default=0),
            'min_tokens': min((c['tokens'] for c in self.chunks), default=0),
            'chunks_by_book': defaultdict(int),
            'unique_concepts': set()
        }
        
        for chunk in self.chunks:
            book_name = chunk['metadata'].get('book_name', 'Synthetic')
            stats['chunks_by_book'][book_name] += 1
            
            concepts = chunk['metadata'].get('concept_tags', [])
            stats['unique_concepts'].update(concepts)
        
        stats['unique_concepts'] = list(stats['unique_concepts'])
        stats['chunks_by_book'] = dict(stats['chunks_by_book'])
        
        return stats
    
    def save_chunks(self, output_file: str):
        """Save chunks to JSON file"""
        output_data = {
            'metadata': {
                'source_file': self.json_file_path,
                'generated_at': datetime.now().isoformat(),
                'chunking_strategy': 'page_based_with_semantic_splitting',
                'max_chunk_tokens': self.max_chunk_tokens,
                'overlap_tokens': self.overlap_tokens
            },
            'statistics': self.generate_statistics(),
            'chunks': self.chunks
        }
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(output_data, f, indent=2, ensure_ascii=False)
        
        print(f"\n✓ Saved {len(self.chunks)} chunks to {output_file}")
    
    def print_statistics(self):
        """Print chunking statistics"""
        stats = self.generate_statistics()
        
        print("\n" + "="*60)
        print("CHUNKING STATISTICS")
        print("="*60)
        print(f"Total Chunks: {stats['total_chunks']}")
        print(f"  - Page-based chunks: {stats['page_chunks']}")
        print(f"  - Synthetic chunks: {stats['synthetic_chunks']}")
        print(f"  - Diagram chunks: {stats['diagram_chunks']}")
        print(f"\nToken Statistics:")
        print(f"  - Average: {stats['avg_tokens']:.0f} tokens")
        print(f"  - Maximum: {stats['max_tokens']} tokens")
        print(f"  - Minimum: {stats['min_tokens']} tokens")
        print(f"\nUnique Concepts Extracted: {len(stats['unique_concepts'])}")
        print(f"\nChunks by Book:")
        for book, count in stats['chunks_by_book'].items():
            print(f"  - {book}: {count} chunks")
        print("="*60 + "\n")
    
    def run(self, output_file: str):
        """Run the complete chunking pipeline"""
        print("\n" + "="*60)
        print("CUBE DOCUMENTATION CHUNKING - OPTIMIZED STRATEGY")
        print("="*60)
        
        self.load_data()
        self.process_all_pages()
        # self.add_synthetic_chunks()  # DISABLED - relies on concept extraction which was removed
        self.print_statistics()
        self.save_chunks(output_file)
        
        print("✅ Chunking complete!")
        print(f"📁 Output saved to: {output_file}")


def main():
    # Configuration
    input_file = "/Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance/utils/output/final_clean_shelf3.json"
    output_file = "/Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance/chunks/cube_optimized_chunks.json"
    
    # Run chunker
    chunker = CUBEDocumentChunker(input_file)
    chunker.run(output_file)
    
    # Print sample chunks
    print("\n" + "="*60)
    print("SAMPLE CHUNKS")
    print("="*60)
    
    # Show first regular chunk
    regular_chunks = [c for c in chunker.chunks if not c['metadata'].get('is_synthetic')]
    if regular_chunks:
        print("\n--- Sample Page Chunk ---")
        sample = regular_chunks[0]
        print(f"Chunk ID: {sample['metadata']['chunk_id']}")
        print(f"Hierarchy: {sample['metadata']['hierarchy_path']}")
        print(f"Concepts: {', '.join(sample['metadata'].get('concept_tags', []))}")
        print(f"Tokens: {sample['tokens']}")
        print(f"\nContent Preview:\n{sample['content'][:300]}...")
    
    # Show synthetic chunk
    synthetic_chunks = [c for c in chunker.chunks if c['metadata'].get('is_synthetic')]
    if synthetic_chunks:
        print("\n--- Sample Synthetic Chunk ---")
        sample = synthetic_chunks[0]
        print(f"Topic: {sample['metadata']['topic']}")
        print(f"Title: {sample['metadata']['title']}")
        print(f"Keywords: {', '.join(sample['metadata']['keywords'])}")
        print(f"Source Pages: {len(sample['metadata']['source_pages'])} pages")
        print(f"Tokens: {sample['tokens']}")
        print(f"\nContent Preview:\n{sample['content'][:300]}...")
    
    print("\n" + "="*60)


if __name__ == "__main__":
    main()
