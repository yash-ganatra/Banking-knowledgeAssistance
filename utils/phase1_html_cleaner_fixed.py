"""
Phase 1: HTML Cleaning & Text Extraction for BookStack Data (FIXED)
Extracts clean text from HTML while preserving Mermaid diagrams separately
"""

import json
import re
from bs4 import BeautifulSoup
from pathlib import Path
from typing import Dict, List, Optional, Tuple
import html


class BookStackHTMLCleaner:
    """Clean and extract text from BookStack HTML content"""
    
    def __init__(self):
        self.stats = {
            'pages_processed': 0,
            'mermaid_diagrams_found': 0,
            'html_pages': 0,
            'markdown_pages': 0,
            'errors': []
        }
    
    def clean_html_to_text(self, html_content: str) -> Tuple[str, Optional[str]]:
        """
        Extract clean text from HTML and detect Mermaid diagrams
        
        Returns:
            Tuple[str, Optional[str]]: (cleaned_text, mermaid_diagram)
        """
        if not html_content or html_content.strip() == "":
            return "", None
        
        # First, extract Mermaid diagrams before removing HTML
        mermaid_diagram = self._extract_mermaid(html_content)
        
        # Parse HTML
        soup = BeautifulSoup(html_content, 'html.parser')
        
        # Remove Mermaid divs (already extracted)
        for mermaid_div in soup.find_all('div', class_='mermaid'):
            mermaid_div.decompose()
        
        # Remove script and style tags
        for tag in soup(['script', 'style', 'iframe']):
            tag.decompose()
        
        # Extract text with better formatting
        text = self._extract_formatted_text(soup)
        
        # Clean the text
        text = self._clean_text(text)
        
        return text, mermaid_diagram
    
    def _extract_mermaid(self, html_content: str) -> Optional[str]:
        """Extract Mermaid diagram from HTML"""
        soup = BeautifulSoup(html_content, 'html.parser')
        mermaid_div = soup.find('div', class_='mermaid')
        
        if mermaid_div:
            # Get the mermaid code
            mermaid_code = mermaid_div.get_text(strip=True)
            if mermaid_code:
                self.stats['mermaid_diagrams_found'] += 1
                return mermaid_code
        
        return None
    
    def _extract_formatted_text(self, soup: BeautifulSoup) -> str:
        """Extract text with proper formatting for headings, lists, etc."""
        lines = []
        
        # Process direct children recursively to avoid duplicates
        for element in soup.children:
            self._process_element(element, lines, indent_level=0)
        
        return '\n'.join(lines)
    
    def _process_element(self, element, lines: List[str], indent_level: int = 0):
        """Recursively process HTML elements to avoid duplicates"""
        if not hasattr(element, 'name'):
            # It's a text node
            text = str(element).strip()
            if text:
                lines.append(text)
            return
        
        if element.name in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']:
            # Add heading with clear separation
            text = element.get_text(strip=True)
            if text:
                lines.append(f"\n{text}\n")
        
        elif element.name == 'p':
            # Paragraph
            text = element.get_text(strip=True)
            if text:
                lines.append(text)
        
        elif element.name in ['ul', 'ol']:
            # List container - process children
            for child in element.children:
                self._process_element(child, lines, indent_level)
        
        elif element.name == 'li':
            # List item - get direct text only
            text = self._get_direct_text(element)
            if text:
                indent = '  ' * indent_level
                lines.append(f"{indent}- {text}")
            
            # Process nested lists
            for child in element.children:
                if hasattr(child, 'name') and child.name in ['ul', 'ol']:
                    for nested_child in child.children:
                        self._process_element(nested_child, lines, indent_level + 1)
        
        elif element.name == 'br':
            lines.append('')
        
        elif element.name == 'div':
            # Process div children
            for child in element.children:
                self._process_element(child, lines, indent_level)
        
        else:
            # For other elements, just get text
            text = element.get_text(strip=True)
            if text and element.name not in ['script', 'style']:
                lines.append(text)
    
    def _get_direct_text(self, element) -> str:
        """Get only the direct text content of an element, not nested tags"""
        texts = []
        for child in element.children:
            if not hasattr(child, 'name'):
                # It's a text node
                text = str(child).strip()
                if text:
                    texts.append(text)
            elif child.name not in ['ul', 'ol']:
                # Get text from inline elements, but not from nested lists
                text = child.get_text(strip=True)
                if text:
                    texts.append(text)
        return ' '.join(texts)
    
    def _clean_text(self, text: str) -> str:
        """Clean and normalize text"""
        # Decode HTML entities
        text = html.unescape(text)
        
        # Remove excessive whitespace
        text = re.sub(r' +', ' ', text)
        
        # Remove excessive newlines (more than 2)
        text = re.sub(r'\n{3,}', '\n\n', text)
        
        # Remove leading/trailing whitespace from each line
        lines = [line.strip() for line in text.split('\n')]
        text = '\n'.join(lines)
        
        # Remove empty bullet points
        text = re.sub(r'\n\s*-\s*\n', '\n', text)
        
        return text.strip()
    
    def process_markdown(self, markdown_content: str) -> Tuple[str, Optional[str]]:
        """
        Process markdown content (some pages use markdown instead of HTML)
        
        Returns:
            Tuple[str, Optional[str]]: (cleaned_text, mermaid_diagram)
        """
        if not markdown_content or markdown_content.strip() == "":
            return "", None
        
        # Extract Mermaid from markdown code blocks
        mermaid_pattern = r'```mermaid\s*(.*?)```'
        mermaid_match = re.search(mermaid_pattern, markdown_content, re.DOTALL)
        
        mermaid_diagram = None
        if mermaid_match:
            mermaid_diagram = mermaid_match.group(1).strip()
            self.stats['mermaid_diagrams_found'] += 1
            # Remove mermaid block from text
            markdown_content = re.sub(mermaid_pattern, '', markdown_content, flags=re.DOTALL)
        
        # Clean markdown (basic cleaning - can be enhanced)
        text = markdown_content.strip()
        
        return text, mermaid_diagram
    
    def process_page(self, page: Dict) -> Dict:
        """Process a single page and extract clean content"""
        try:
            self.stats['pages_processed'] += 1
            
            # Determine content type
            html_content = page.get('html', '')
            markdown_content = page.get('markdown', '')
            
            # Process based on content type
            if html_content and html_content.strip():
                self.stats['html_pages'] += 1
                clean_text, mermaid = self.clean_html_to_text(html_content)
                content_format = 'html'
            elif markdown_content and markdown_content.strip():
                self.stats['markdown_pages'] += 1
                clean_text, mermaid = self.process_markdown(markdown_content)
                content_format = 'markdown'
            else:
                clean_text = ""
                mermaid = None
                content_format = 'empty'
            
            # Create cleaned page structure
            cleaned_page = {
                'id': page['id'],
                'book_id': page['book_id'],
                'chapter_id': page.get('chapter_id'),
                'name': page['name'],
                'slug': page['slug'],
                'content': clean_text,
                'content_format': content_format,
                'has_mermaid': mermaid is not None,
                'mermaid_diagram': mermaid,
                'priority': page.get('priority', 0),
                'created_at': page.get('created_at'),
                'updated_at': page.get('updated_at')
            }
            
            return cleaned_page
            
        except Exception as e:
            error_msg = f"Error processing page {page.get('id', 'unknown')}: {str(e)}"
            self.stats['errors'].append(error_msg)
            print(f"⚠️  {error_msg}")
            
            # Return minimal page structure on error
            return {
                'id': page.get('id'),
                'book_id': page.get('book_id'),
                'chapter_id': page.get('chapter_id'),
                'name': page.get('name', 'Error'),
                'slug': page.get('slug', ''),
                'content': '',
                'content_format': 'error',
                'has_mermaid': False,
                'mermaid_diagram': None,
                'priority': page.get('priority', 0),
                'created_at': page.get('created_at'),
                'updated_at': page.get('updated_at')
            }
    
    def process_shelf_data(self, shelf_data: Dict) -> Dict:
        """Process entire shelf data structure"""
        print("Starting Phase 1: HTML Cleaning & Text Extraction")
        print("=" * 70)
        
        cleaned_data = {
            'timestamp': shelf_data['timestamp'],
            'shelf': shelf_data['shelf'],
            'total_books': shelf_data['total_books'],
            'total_chapters': shelf_data['total_chapters'],
            'total_pages': shelf_data['total_pages'],
            'processing_stats': {},
            'books': []
        }
        
        # Process each book
        for book in shelf_data['books']:
            print(f"\n📚 Processing book: {book['name']}")
            
            cleaned_book = {
                'id': book['id'],
                'name': book['name'],
                'slug': book['slug'],
                'description': book.get('description', ''),
                'created_at': book.get('created_at'),
                'updated_at': book.get('updated_at'),
                'chapters': []
            }
            
            # Process chapters
            for chapter in book.get('chapters', []):
                print(f"  📖 Chapter: {chapter['name']}")
                
                cleaned_chapter = {
                    'id': chapter['id'],
                    'name': chapter['name'],
                    'slug': chapter['slug'],
                    'description': chapter.get('description', ''),
                    'priority': chapter.get('priority', 0),
                    'pages': []
                }
                
                # Process pages
                for page in chapter.get('pages', []):
                    cleaned_page = self.process_page(page)
                    cleaned_chapter['pages'].append(cleaned_page)
                
                cleaned_book['chapters'].append(cleaned_chapter)
            
            # Process pages directly in book (no chapter)
            for page in book.get('pages', []):
                # Need to add these to a default chapter or handle separately
                # For now, we'll skip as your data seems chapter-organized
                pass
            
            cleaned_data['books'].append(cleaned_book)
        
        # Add processing statistics
        cleaned_data['processing_stats'] = self.stats
        
        print("\n" + "=" * 70)
        print("Phase 1 Complete!")
        print(f"✓ Processed {self.stats['pages_processed']} pages")
        print(f"✓ Found {self.stats['mermaid_diagrams_found']} Mermaid diagrams")
        print(f"✓ Errors: {len(self.stats['errors'])}")
        
        return cleaned_data


def main():
    """Main execution function"""
    
    # File paths
    input_file = Path('./output/shelf_3_books_20251223_140046.json')
    output_file = Path('./output/shelf_3_cleaned_phase1_fixed.json')
    
    print(f"Reading from: {input_file}")
    print(f"Output will be saved to: {output_file}")
    print()
    
    # Load data
    with open(input_file, 'r', encoding='utf-8') as f:
        shelf_data = json.load(f)
    
    # Process data
    cleaner = BookStackHTMLCleaner()
    cleaned_data = cleaner.process_shelf_data(shelf_data)
    
    # Save cleaned data
    output_file.parent.mkdir(parents=True, exist_ok=True)
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(cleaned_data, f, indent=2, ensure_ascii=False)
    
    print(f"\n✅ Cleaned data saved to: {output_file}")
    print(f"📊 File size: {output_file.stat().st_size:,} bytes")


if __name__ == "__main__":
    main()
