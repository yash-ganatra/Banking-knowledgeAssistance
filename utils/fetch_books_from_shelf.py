"""
Standalone script to fetch all books from a BookStack shelf and save to file
"""
from bookstack_api import BookStackAPI, BookStackConfig
import json
import sys
import socket
from datetime import datetime
from pathlib import Path

# Configuration
config = BookStackConfig(
    base_url='http://neuron.appinsource.com:6427/',
    token_id='4ZQrO9Uc1LnHOLfKc4RsNG0RwE1JCPdJ',
    token_secret='bgAM9sNZzt5iI8voZCNkxLQrQHipTMkc'
)
# Initialize API
api = BookStackAPI(config)


def fetch_books_from_shelf(shelf_id, save_to_file=True, output_dir='./output'):
    """
    Fetch all books, chapters, and pages from a specific shelf and optionally save to file
    
    Args:
        shelf_id (int): The ID of the shelf to fetch books from
        save_to_file (bool): Whether to save the results to a file
        output_dir (str): Directory to save the output file
    
    Returns:
        dict: Dictionary containing shelf info, books, chapters, and pages
    """
    try:
        print(f"Fetching complete content from shelf ID: {shelf_id}")
        print("=" * 70)
        
        # Get shelf information
        shelf_info = api.get_shelf(shelf_id)
        shelf_name = shelf_info.get('name', 'Unknown Shelf')
        shelf_slug = shelf_info.get('slug', '')
        shelf_description = shelf_info.get('description', '')
        
        print(f"Shelf Name: {shelf_name}")
        print(f"Shelf Slug: {shelf_slug}")
        if shelf_description:
            print(f"Description: {shelf_description}")
        print("-" * 70)
        
        # Get all books from the shelf
        books = api.get_books_from_shelf(shelf_id)
        
        if not books:
            print("No books found in this shelf.")
            return {
                'shelf': shelf_info,
                'books': [],
                'total_books': 0,
                'total_chapters': 0,
                'total_pages': 0
            }
        
        print(f"Found {len(books)} book(s) in shelf '{shelf_name}'")
        print(f"Fetching detailed content for each book...\n")
        
        # Process and display books with full details
        books_data = []
        total_chapters = 0
        total_pages = 0
        
        for idx, book in enumerate(books, 1):
            book_id = book.get('id')
            book_name = book.get('name', 'Unknown Book')
            
            print(f"{idx}. 📚 {book_name} (ID: {book_id})")
            
            # Get full book details
            full_book = api.get_book(book_id)
            
            book_details = {
                'id': book_id,
                'name': full_book.get('name', book_name),
                'slug': full_book.get('slug', ''),
                'description': full_book.get('description', ''),
                'created_at': full_book.get('created_at', ''),
                'updated_at': full_book.get('updated_at', ''),
                'chapters': [],
                'pages': []
            }
            
            # Get chapters for this book
            chapters_in_book = full_book.get('contents', [])
            
            for content in chapters_in_book:
                if content.get('type') == 'chapter':
                    chapter_id = content.get('id')
                    print(f"   📖 Fetching chapter: {content.get('name')} (ID: {chapter_id})")
                    
                    # Get full chapter details
                    full_chapter = api.get_chapter(chapter_id)
                    
                    chapter_data = {
                        'id': chapter_id,
                        'name': full_chapter.get('name', ''),
                        'slug': full_chapter.get('slug', ''),
                        'description': full_chapter.get('description', ''),
                        'priority': full_chapter.get('priority', 0),
                        'created_at': full_chapter.get('created_at', ''),
                        'updated_at': full_chapter.get('updated_at', ''),
                        'pages': []
                    }
                    
                    # Get pages in this chapter
                    chapter_pages = full_chapter.get('pages', [])
                    for page_ref in chapter_pages:
                        page_id = page_ref.get('id')
                        print(f"      📄 Fetching page: {page_ref.get('name')} (ID: {page_id})")
                        
                        # Get full page content
                        full_page = api.get_page(page_id)
                        chapter_data['pages'].append(full_page)
                        total_pages += 1
                    
                    book_details['chapters'].append(chapter_data)
                    total_chapters += 1
                
                elif content.get('type') == 'page':
                    # Page directly in book (not in a chapter)
                    page_id = content.get('id')
                    print(f"   📄 Fetching page: {content.get('name')} (ID: {page_id})")
                    
                    # Get full page content
                    full_page = api.get_page(page_id)
                    book_details['pages'].append(full_page)
                    total_pages += 1
            
            books_data.append(book_details)
            print()
        
        print(f"Total chapters fetched: {total_chapters}")
        print(f"Total pages fetched: {total_pages}")
        
        # Prepare result data
        result = {
            'timestamp': datetime.now().isoformat(),
            'shelf': {
                'id': shelf_id,
                'name': shelf_name,
                'slug': shelf_slug,
                'description': shelf_description
            },
            'books': books_data,
            'total_books': len(books_data),
            'total_chapters': total_chapters,
            'total_pages': total_pages
        }
        
        # Save to file if requested
        if save_to_file:
            output_path = Path(output_dir)
            output_path.mkdir(parents=True, exist_ok=True)
            
            # Create filename with timestamp
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            filename = f"shelf_{shelf_id}_books_{timestamp}.json"
            filepath = output_path / filename
            
            # Save JSON file
            with open(filepath, 'w', encoding='utf-8') as f:
                json.dump(result, f, indent=2, ensure_ascii=False)
            
            print("-" * 70)
            print(f"✓ Data saved to: {filepath}")
            print(f"  File size: {filepath.stat().st_size} bytes")
        
        return result
        
    except Exception as e:
        print(f"Error fetching books from shelf: {e}")
        import traceback
        traceback.print_exc()
        return None


def main():
    """Main function to run the script"""
    # Configuration
    SHELF_ID = 3
    OUTPUT_DIR = './output'
    
    # Allow shelf ID to be passed as command line argument
    if len(sys.argv) > 1:
        try:
            SHELF_ID = int(sys.argv[1])
        except ValueError:
            print(f"Invalid shelf ID: {sys.argv[1]}. Using default: {SHELF_ID}")
    
    print("BookStack Books Fetcher")
    print("=" * 70)
    print()
    
    # Fetch and save books
    result = fetch_books_from_shelf(
        shelf_id=SHELF_ID,
        save_to_file=True,
        output_dir=OUTPUT_DIR
    )
    
    if result:
        print("\n" + "=" * 70)
        print(f"Summary: Found {result['total_books']} book(s), {result['total_chapters']} chapter(s), and {result['total_pages']} page(s) in shelf '{result['shelf']['name']}'")
        print("=" * 70)
    else:
        print("\nFailed to fetch books from shelf.")
        sys.exit(1)


if __name__ == "__main__":
    main()
