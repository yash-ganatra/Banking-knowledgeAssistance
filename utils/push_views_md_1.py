from bookstack_api import BookStackAPI, BookStackConfig, BookStackPipeline
import os
import re

config = BookStackConfig(
    base_url='http://143.244.141.254:8008/',
    token_id='4ZQrO9Uc1LnHOLfKc4RsNG0RwE1JCPdJ',
    token_secret='bgAM9sNZzt5iI8voZCNkxLQrQHipTMkc'
)

api = BookStackAPI(config)

#print(api.get_books())

def create_pages_from_folder(folder_path: str, book_id: int, bookstack_api):
    """
    Create a markdown page in BookStack for each .md file in the given folder.

    :param folder_path: Path to the folder containing .md files
    :param book_id: Target BookStack book ID
    :param bookstack_api: An object that provides create_markdown_page()
    """
    # Get all .md files in folder
    md_files = [f for f in os.listdir(folder_path) if f.endswith('.md')]
    md_files.sort()  # Optional: Sort alphabetically

    for md_file in md_files:
        file_path = os.path.join(folder_path, md_file)
        page_name = os.path.splitext(md_file)[0]  # Remove .md extension

        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()

        print(f"Creating page: {page_name}")
        if page_name.endswith('_views'):
            page_name = page_name[:-6]  # remove '_views'
        parts = page_name.split('_', 1)  # split into ['01', 'admin']
        page_name = ' '.join(parts)
        response = bookstack_api.create_markdown_page(
            book_id=book_id,
            name=page_name,
            markdown_content=content
        )

        print(f"Created page: {response.get('id')} - {response.get('name')}")

book_id = 6

"""
params = {}
params['filter'] = 'book_id:{book_id}'
data = pages.get('data', [])
matching_ids = [item['id'] for item in data if item['book_id'] == 6]
"""

#pages = api.get_pages_for_book(book_id)
#api.del_all_pages_in_book(book_id)

create_pages_from_folder('D:\\_CUBE_Code\\@automate\\MDs', book_id=6, bookstack_api=api)
