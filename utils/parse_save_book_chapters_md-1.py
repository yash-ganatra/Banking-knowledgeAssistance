from bookstack_api import BookStackAPI, BookStackConfig, BookStackPipeline
import requests
import json
import os
import re
import sys
sys.stdout.reconfigure(encoding='utf-8')
from markdownify import markdownify as md


config = BookStackConfig(
    base_url='http://143.244.141.254:8008/',
    token_id='4ZQrO9Uc1LnHOLfKc4RsNG0RwE1JCPdJ',
    token_secret='bgAM9sNZzt5iI8voZCNkxLQrQHipTMkc'
)

api = BookStackAPI(config)

#print(api.get_book(8))


def fetch_and_parse_book(bookid):
    try:
        book_meta_info = api.get_book(bookid)
        #data = book_meta_info.json()
        #print(book_meta_info)
        #print('==============')
    except requests.exceptions.RequestException as e:
        print(f"Error fetching data: {e}")
        return
    except json.JSONDecodeError as e:
        print(f"Error parsing JSON: {e}")
        return
    
    # Parse the response and iterate through the structure
    parse_book_data(book_meta_info)

def parse_book_data(data):
    book_id = data.get('id')
    book_name = data.get('name', 'Unknown Book')
    
    print(f"Processing Book: {book_name} (ID: {book_id})")
    print("-" * 50)
    
    # Iterate through contents (chapters)
    for chapter in data.get('contents', []):
        chapter_id = chapter.get('id')
        chapter_name = chapter.get('name', 'Unknown Chapter')
        
        # Iterate through pages in each chapter
        for page in chapter.get('pages', []):
            page_id = page.get('id')
            page_name = page.get('name', 'Unknown Page')
            page_book_id = page.get('book_id')
            page_chapter_id = page.get('chapter_id')
            
            # Print the innermost id (page id) along with book and chapter ids
            #print(f"Page ID: {page_id} | Book ID: {page_book_id} | Chapter ID: {page_chapter_id} | Page Name: {page_name}")
            page_json = api.get_page(page_id)
            print(page_json)
            break
            response_text = md(page_json.get('html'))
            cleaned_text = re.sub(r'https?://(?:\d{1,3}\.){3}\d{1,3}\S*', '', response_text)    # remove pvt ips/urls
            print(cleaned_text)
    
    

if __name__ == "__main__":
    fetch_and_parse_book(bookid = 8)    
    print("\n" + "="*60 + "\n")

