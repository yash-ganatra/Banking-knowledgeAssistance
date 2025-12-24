"""
Phase 1: HTML Cleaning & Text Extraction for BookStack Data
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
        
        for element in soup.descendants:
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
            
            elif element.name == 'li':
                # List item
                text = element.get_text(strip=True)
                if text and not any(text in line for line in lines[-3:]):  # Avoid duplicates
                    # Determine indentation based on nesting
                    indent = self._get_list_indent(element)
                    lines.append(f"{indent}- {text}")
            
            elif element.name == 'br':
                lines.append('')
        
        return '\n'.join(lines)
    
    def _get_list_indent(self, element) -> str:
        """Get indentation level for nested lists"""
        indent_level = 0
        parent = element.parent
        
        while parent:
            if parent.name in ['ul', 'ol']:
                indent_level += 1
            parent = parent.parent
        
        return '  ' * max(0, indent_level - 1)
    
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
            # Remove mermaid block from markdown
            markdown_content = re.sub(mermaid_pattern, '', markdown_content, flags=re.DOTALL)
        
        # Check for HTML-style mermaid
        if '<div class="mermaid">' in markdown_content:
            html_mermaid = self._extract_mermaid(markdown_content)
            if html_mermaid:
                mermaid_diagram = html_mermaid
        
        # Clean markdown (basic cleaning)
        text = markdown_content.strip()
        
        return text, mermaid_diagram
    
    def clean_page(self, page: Dict) -> Dict:
        """Clean a single page and extract content"""
        page_id = page.get('id', 'unknown')
        
        try:
            # Determine content source
            html_content = page.get('html', '')
            markdown_content = page.get('markdown', '')
            
            cleaned_text = ""
            mermaid_diagram = None
            content_format = "html"
            
            # Prefer HTML if available
            if html_content and html_content.strip():
                cleaned_text, mermaid_diagram = self.clean_html_to_text(html_content)
                self.stats['html_pages'] += 1
            elif markdown_content and markdown_content.strip():
                cleaned_text, mermaid_diagram = self.process_markdown(markdown_content)
                content_format = "markdown"
                self.stats['markdown_pages'] += 1
            
            # Build cleaned page object
            cleaned_page = {
                'id': page_id,
                'book_id': page.get('book_id'),
                'chapter_id': page.get('chapter_id'),
                'name': page.get('name', ''),
                'slug': page.get('slug', ''),
                'content': cleaned_text,
                'content_format': content_format,
                'has_mermaid': mermaid_diagram is not None,
                'mermaid_diagram': mermaid_diagram,
                'priority': page.get('priority', 0),
                'created_at': page.get('created_at', ''),
                'updated_at': page.get('updated_at', '')
            }
            
            self.stats['pages_processed'] += 1
            return cleaned_page
            
        except Exception as e:
            error_msg = f"Error processing page {page_id}: {str(e)}"
            self.stats['errors'].append(error_msg)
            print(f"❌ {error_msg}")
            return None
    
    def process_book_data(self, input_file: str, output_file: str):
        """Process entire BookStack data file"""
        print("=" * 70)
        print("Phase 1: HTML Cleaning & Text Extraction")
        print("=" * 70)
        
        # Load input data
        print(f"\n📂 Loading: {input_file}")
        with open(input_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        print(f"✓ Loaded {data['total_books']} books, {data['total_chapters']} chapters, {data['total_pages']} pages")
        
        # Process all books
        cleaned_books = []
        
        for book in data['books']:
            print(f"\n📚 Processing Book: {book['name']}")
            
            cleaned_book = {
                'id': book['id'],
                'name': book['name'],
                'slug': book['slug'],
                'description': book.get('description', ''),
                'created_at': book.get('created_at', ''),
                'updated_at': book.get('updated_at', ''),
                'chapters': [],
                'pages': []
            }
            
            # Process chapters
            for chapter in book.get('chapters', []):
                print(f"  📖 Chapter: {chapter['name']} ({len(chapter.get('pages', []))} pages)")
                
                cleaned_chapter = {
                    'id': chapter['id'],
                    'name': chapter['name'],
                    'slug': chapter['slug'],
                    'description': chapter.get('description', ''),
                    'priority': chapter.get('priority', 0),
                    'pages': []
                }
                
                # Process pages in chapter
                for page in chapter.get('pages', []):
                    cleaned_page = self.clean_page(page)
                    if cleaned_page:
                        cleaned_chapter['pages'].append(cleaned_page)
                        print(f"    ✓ {page['name']} {'🎨' if cleaned_page['has_mermaid'] else ''}")
                
                cleaned_book['chapters'].append(cleaned_chapter)
            
            # Process standalone pages (not in chapters)
            for page in book.get('pages', []):
                cleaned_page = self.clean_page(page)
                if cleaned_page:
                    cleaned_book['pages'].append(cleaned_page)
                    print(f"  ✓ {page['name']} {'🎨' if cleaned_page['has_mermaid'] else ''}")
            
            cleaned_books.append(cleaned_book)
        
        # Prepare output
        output_data = {
            'timestamp': data['timestamp'],
            'shelf': data['shelf'],
            'total_books': data['total_books'],
            'total_chapters': data['total_chapters'],
            'total_pages': data['total_pages'],
            'processing_stats': self.stats,
            'books': cleaned_books
        }
        
        # Save cleaned data
        output_path = Path(output_file)
        output_path.parent.mkdir(parents=True, exist_ok=True)
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(output_data, f, indent=2, ensure_ascii=False)
        
        # Print summary
        print("\n" + "=" * 70)
        print("✅ Processing Complete!")
        print("=" * 70)
        print(f"📊 Statistics:")
        print(f"  - Pages processed: {self.stats['pages_processed']}")
        print(f"  - HTML pages: {self.stats['html_pages']}")
        print(f"  - Markdown pages: {self.stats['markdown_pages']}")
        print(f"  - Mermaid diagrams found: {self.stats['mermaid_diagrams_found']}")
        print(f"  - Errors: {len(self.stats['errors'])}")
        print(f"\n💾 Output saved to: {output_file}")
        print(f"📦 File size: {output_path.stat().st_size / 1024:.2f} KB")
        
        if self.stats['errors']:
            print(f"\n⚠️  Errors encountered:")
            for error in self.stats['errors'][:5]:  # Show first 5 errors
                print(f"  - {error}")


def main():
    """Main execution"""
    # File paths
    input_file = './output/shelf_3_books_20251223_140046.json'
    output_file = './output/shelf_3_cleaned_phase1.json'
    
    # Process
    cleaner = BookStackHTMLCleaner()
    cleaner.process_book_data(input_file, output_file)
    
    # Show sample
    print("\n" + "=" * 70)
    print("📝 Sample Cleaned Content:")
    print("=" * 70)
    
    with open(output_file, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    # Show first page with content
    for book in data['books']:
        for chapter in book['chapters']:
            if chapter['pages']:
                sample_page = chapter['pages'][0]
                print(f"\n📄 Page: {sample_page['name']}")
                print(f"📚 Book: {book['name']}")
                print(f"📖 Chapter: {chapter['name']}")
                if sample_page['has_mermaid']:
                    print(f"🎨 Contains Mermaid Diagram: Yes")
                print(f"\n--- Content Preview (first 500 chars) ---")
                print(sample_page['content'][:500])
                if sample_page['has_mermaid']:
                    print(f"\n--- Mermaid Diagram ---")
                    print(sample_page['mermaid_diagram'][:300] if sample_page['mermaid_diagram'] else "N/A")
                return


if __name__ == "__main__":
    main()
