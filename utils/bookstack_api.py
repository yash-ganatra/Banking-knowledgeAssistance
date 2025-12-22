"""
BookStack API Helper Library for Python
Generic, reusable utilities for automation and pipeline integration
"""
# generic lib created using llm for bookstack apis -- CG 100625, 0225

import asyncio
import aiohttp
import requests
import json
import logging
from typing import Dict, List, Optional, Union, Any
from urllib.parse import urljoin
from dataclasses import dataclass, asdict
import time
from pathlib import Path

#from requests_toolbelt.utils import dump
#import curlify

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

@dataclass
class BookStackConfig:
    """Configuration for BookStack API"""
    base_url: str
    token_id: str
    token_secret: str
    timeout: int = 30
    max_retries: int = 3
    rate_limit_delay: float = 1.0

class BookStackAPI:
    """Generic BookStack API client for automation pipelines"""
    
    def __init__(self, config: Union[BookStackConfig, Dict]):
        if isinstance(config, dict):
            self.config = BookStackConfig(**config)
        else:
            self.config = config
            
        self.base_url = self.config.base_url.rstrip('/')
        self.headers = {
            'Authorization': f'Token {self.config.token_id}:{self.config.token_secret}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
        self.session = None
    
    # ===== CORE REQUEST METHODS =====
    
    def make_request(self, endpoint: str, method: str = 'GET', 
                     data: Optional[Union[Dict, str]] = None, params: Optional[Dict] = None, send_as_json: bool = True) -> Dict:
        """Make synchronous API request with retry logic"""
        if data is not None and not isinstance(data, dict):
            raise ValueError(f"`data` must be a dict, got {type(data).__name__}")

        #url = urljoin(f"{self.base_url}/api", endpoint.lstrip('/'))
        url = urljoin(self.base_url.rstrip('/') + "/api/", endpoint.lstrip('/'))
        #print(f"Requesting {method} {url}")
        #print(f"Headers: {self.headers}")
        #print(f"Payload: {data}")
        #print(f"Params: {params}")
        
        for attempt in range(self.config.max_retries):
            try:
                response = requests.request(
                    method=method,
                    url=url,
                    headers=self.headers,
                    json=data if send_as_json else None,
                    data=None if send_as_json else data,
                    params=params,
                    timeout=self.config.timeout
                )
                
                if response.ok:
                    return response.json() if response.content else {}
                else:
                    error_msg = f"API request failed: {response.status_code}"
                    try:
                        error_data = response.json()
                        error_msg += f" - {error_data.get('message', response.text)}"
                    except:
                        error_msg += f" - {response.text}"
                    
                    if response.status_code == 429 or response.status_code >= 500:
                        # Retry on rate limit or server errors
                        if attempt < self.config.max_retries - 1:
                            wait_time = (2 ** attempt) * self.config.rate_limit_delay
                            logger.warning(f"Request failed, retrying in {wait_time}s: {error_msg}")
                            time.sleep(wait_time)
                            continue
                    
                    raise Exception(error_msg)
                    
            except requests.exceptions.RequestException as e:
                if attempt < self.config.max_retries - 1:
                    wait_time = (2 ** attempt) * self.config.rate_limit_delay
                    logger.warning(f"Request error, retrying in {wait_time}s: {str(e)}")
                    time.sleep(wait_time)
                    continue
                raise Exception(f"Request failed after {self.config.max_retries} attempts: {str(e)}")
        
        raise Exception("Request failed after all retry attempts")
    
    async def make_async_request(self, endpoint: str, method: str = 'GET',
                               data: Optional[Dict] = None, params: Optional[Dict] = None) -> Dict:
        """Make asynchronous API request"""
        if not self.session:
            self.session = aiohttp.ClientSession(headers=self.headers)
        
        #url = urljoin(f"{self.base_url}/api", endpoint.lstrip('/'))
        url = urljoin(self.base_url.rstrip('/') + "/api/", endpoint.lstrip('/'))
        
        async with self.session.request(method, url, json=data, params=params) as response:
            if response.ok:
                return await response.json() if response.content_length else {}
            else:
                error_text = await response.text()
                raise Exception(f"Async API request failed: {response.status} - {error_text}")
    
    # ===== BOOK OPERATIONS =====
    
    def get_books(self, **params) -> Dict:
        """Get all books with optional parameters"""
        return self.make_request('/books', params=params)
    
    def get_book(self, book_id: int) -> Dict:
        """Get book by ID"""
        return self.make_request(f'/books/{book_id}')
    
    def create_book(self, book_data: Dict) -> Dict:
        """Create a new book"""
        self._validate_required_fields(book_data, ['name'])
        return self.make_request('/books', 'POST', book_data)
    
    def update_book(self, book_id: int, book_data: Dict) -> Dict:
        """Update existing book"""
        return self.make_request(f'/books/{book_id}', 'PUT', book_data)
    
    def delete_book(self, book_id: int) -> Dict:
        """Delete a book"""
        return self.make_request(f'/books/{book_id}', 'DELETE')
    
    # ===== CHAPTER OPERATIONS =====
    
    def get_chapters(self, book_id: int) -> Dict:
        """Get chapters for a book"""
        return self.make_request(f'/books/{book_id}/chapters')
    
    def get_chapter(self, chapter_id: int) -> Dict:
        """Get chapter by ID"""
        return self.make_request(f'/chapters/{chapter_id}')
    
    def create_chapter(self, book_id: int, chapter_data: Dict) -> Dict:
        """Create a new chapter"""
        self._validate_required_fields(chapter_data, ['name'])
        return self.make_request(f'/books/{book_id}/chapters', 'POST', chapter_data)
    
    def update_chapter(self, chapter_id: int, chapter_data: Dict) -> Dict:
        """Update existing chapter"""
        return self.make_request(f'/chapters/{chapter_id}', 'PUT', chapter_data)
    
    def delete_chapter(self, chapter_id: int) -> Dict:
        """Delete a chapter"""
        return self.make_request(f'/chapters/{chapter_id}', 'DELETE')
    
    # ===== PAGE OPERATIONS =====
    
    def get_pages(self, **params) -> Dict:
        """Get all pages with optional filtering"""
        return self.make_request('/pages', params=params)
    
    def get_page(self, page_id: int) -> Dict:
        """Get page by ID"""
        return self.make_request(f'/pages/{page_id}')
    
    def create_page_in_book(self, book_id: int, page_data: Dict) -> Dict:
        """Create a new page in a book"""
        self._validate_required_fields(page_data, ['name'])
        payload = {'book_id': book_id, **page_data}
        #CG
        return self.make_request('/pages', 'POST', payload)
    
    def create_page_in_chapter(self, chapter_id: int, page_data: Dict) -> Dict:
        """Create a new page in a chapter"""
        self._validate_required_fields(page_data, ['name'])
        payload = {'chapter_id': chapter_id, **page_data}
        return self.make_request('/pages', 'POST', payload)
    
    def update_page(self, page_id: int, page_data: Dict) -> Dict:
        """Update an existing page"""
        return self.make_request(f'/pages/{page_id}', 'PUT', page_data)
    
    def delete_page(self, page_id: int) -> Dict:
        """Delete a page"""
        return self.make_request(f'/pages/{page_id}', 'DELETE')
    
    # ===== BULK OPERATIONS =====
    
    def create_pages_batch(self, pages: List[Dict], batch_size: int = 5) -> List[Dict]:
        """Create multiple pages in batches"""
        results = []
        
        for i in range(0, len(pages), batch_size):
            batch = pages[i:i + batch_size]
            batch_results = []
            
            for page_config in batch:
                try:
                    if 'book_id' in page_config:
                        result = self.create_page_in_book(page_config['book_id'], 
                                                        {k: v for k, v in page_config.items() if k != 'book_id'})
                    elif 'chapter_id' in page_config:
                        result = self.create_page_in_chapter(page_config['chapter_id'],
                                                           {k: v for k, v in page_config.items() if k != 'chapter_id'})
                    else:
                        raise ValueError('Page must have either book_id or chapter_id')
                    
                    batch_results.append(result)
                    logger.info(f"Created page: {page_config.get('name', 'Unknown')}")
                    
                except Exception as e:
                    error_result = {'error': str(e), 'page_config': page_config}
                    batch_results.append(error_result)
                    logger.error(f"Failed to create page: {str(e)}")
            
            results.extend(batch_results)
            
            # Rate limiting between batches
            if i + batch_size < len(pages):
                time.sleep(self.config.rate_limit_delay)
        
        return results
    
    def update_pages_batch(self, updates: List[Dict], batch_size: int = 5) -> List[Dict]:
        """Update multiple pages in batches"""
        results = []
        
        for i in range(0, len(updates), batch_size):
            batch = updates[i:i + batch_size]
            batch_results = []
            
            for update in batch:
                try:
                    result = self.update_page(update['page_id'], update['data'])
                    batch_results.append(result)
                    logger.info(f"Updated page ID: {update['page_id']}")
                    
                except Exception as e:
                    error_result = {'error': str(e), 'update': update}
                    batch_results.append(error_result)
                    logger.error(f"Failed to update page: {str(e)}")
            
            results.extend(batch_results)
            
            if i + batch_size < len(updates):
                time.sleep(self.config.rate_limit_delay)
        
        return results
    
    # ===== ASYNC BULK OPERATIONS =====
    
    async def create_pages_batch_async(self, pages: List[Dict], 
                                     batch_size: int = 10, 
                                     concurrent_batches: int = 3) -> List[Dict]:
        """Create multiple pages asynchronously"""
        semaphore = asyncio.Semaphore(concurrent_batches)
        results = []
        
        async def create_page_async(page_config):
            async with semaphore:
                try:
                    if 'book_id' in page_config:
                        endpoint = '/pages'
                        data = {'book_id': page_config['book_id'], 
                               **{k: v for k, v in page_config.items() if k != 'book_id'}}
                    elif 'chapter_id' in page_config:
                        endpoint = '/pages'
                        data = {'chapter_id': page_config['chapter_id'],
                               **{k: v for k, v in page_config.items() if k != 'chapter_id'}}
                    else:
                        raise ValueError('Page must have either book_id or chapter_id')
                    
                    result = await self.make_async_request(endpoint, 'POST', data)
                    logger.info(f"Created page: {page_config.get('name', 'Unknown')}")
                    return result
                    
                except Exception as e:
                    logger.error(f"Failed to create page: {str(e)}")
                    return {'error': str(e), 'page_config': page_config}
        
        # Process in batches
        for i in range(0, len(pages), batch_size):
            batch = pages[i:i + batch_size]
            batch_tasks = [create_page_async(page) for page in batch]
            batch_results = await asyncio.gather(*batch_tasks, return_exceptions=True)
            results.extend(batch_results)
            
            # Small delay between batches
            if i + batch_size < len(pages):
                await asyncio.sleep(self.config.rate_limit_delay)
        
        return results
    
    # ===== TEMPLATE AND CLONING =====
    
    def create_page_from_template(self, template_page_id: int, target_book_id: int, 
                                new_page_data: Dict) -> Dict:
        """Create page from existing template"""
        template = self.get_page(template_page_id)
        
        page_data = {
            'name': new_page_data['name'],
            'html': new_page_data.get('html', template.get('html', '')),
            'markdown': new_page_data.get('markdown', template.get('markdown', '')),
            'tags': new_page_data.get('tags', template.get('tags', [])),
            'priority': new_page_data.get('priority', template.get('priority', 0)),
            **{k: v for k, v in new_page_data.items() 
               if k not in ['name', 'html', 'markdown', 'tags', 'priority']}
        }
        
        return self.create_page_in_book(target_book_id, page_data)
    
    def clone_page(self, source_page_id: int, target_book_id: int, 
                  target_chapter_id: Optional[int] = None, new_name: Optional[str] = None) -> Dict:
        """Clone page to different book/chapter"""
        source_page = self.get_page(source_page_id)
        
        clone_data = {
            'name': new_name or f"{source_page['name']} (Copy)",
            'html': source_page.get('html', ''),
            'markdown': source_page.get('markdown', ''),
            'tags': source_page.get('tags', []),
            'priority': source_page.get('priority', 0)
        }
        
        if target_chapter_id:
            return self.create_page_in_chapter(target_chapter_id, clone_data)
        else:
            return self.create_page_in_book(target_book_id, clone_data)
    
    # ===== SEARCH AND UTILITY =====
    
    def search_pages(self, query: str, **filters) -> Dict:
        """Search for pages"""
        params = {'query': query, **filters}
        return self.get_pages(**params)
    
    def find_page_by_name(self, name: str, book_id: Optional[int] = None) -> Optional[Dict]:
        """Find page by exact name match"""
        params = {}
        if book_id:
            params['filter'] = f'book_id:{book_id}'
        
        pages = self.get_pages(**params)
        
        for page in pages.get('data', []):
            if page['name'].lower() == name.lower():
                return page
        
        return None
    
    def get_page_hierarchy(self, page_id: int) -> Dict:
        """Get page hierarchy (book -> chapter -> page)"""
        page = self.get_page(page_id)
        hierarchy = {
            'page': page,
            'chapter': None,
            'book': None
        }
        
        if page.get('chapter_id'):
            chapter = self.get_chapter(page['chapter_id'])
            hierarchy['chapter'] = chapter
            hierarchy['book'] = self.get_book(chapter['book_id'])
        elif page.get('book_id'):
            hierarchy['book'] = self.get_book(page['book_id'])
        
        return hierarchy
    
    # ===== CONTENT HELPERS =====
    
    def create_html_page(self, book_id: int, name: str, html_content: str, **options) -> Dict:
        """Create page with HTML content"""
        page_data = {
            'name': name,
            'html': self.sanitize_html(html_content),
            'tags': options.get('tags', []),
            'priority': options.get('priority', 0),
            **{k: v for k, v in options.items() if k not in ['tags', 'priority']}
        }
        
        return self.create_page_in_book(book_id, page_data)
    
    def create_markdown_page(self, book_id: int, name: str, markdown_content: str, **options) -> Dict:
        """Create page with Markdown content"""
        page_data = {
            'name': name,
            'markdown': markdown_content,
            'tags': options.get('tags', []),
            'priority': options.get('priority', 0),
            **{k: v for k, v in options.items() if k not in ['tags', 'priority']}
        }
        
        return self.create_page_in_book(book_id, page_data)
    
    def update_page_content(self, page_id: int, content: str, is_markdown: bool = False) -> Dict:
        """Update page content only"""
        update_data = {'markdown': content} if is_markdown else {'html': self.sanitize_html(content)}
        return self.update_page(page_id, update_data)
    
    # ===== VALIDATION AND UTILITIES =====
    
    def _validate_required_fields(self, data: Dict, required_fields: List[str]):
        """Validate required fields are present"""
        missing = [field for field in required_fields if not data.get(field)]
        if missing:
            raise ValueError(f"Missing required fields: {', '.join(missing)}")
    
    def sanitize_html(self, html: str) -> str:
        """Basic HTML sanitization"""
        import re
        # Remove script tags and javascript: URLs
        html = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'<iframe[^>]*>.*?</iframe>', '', html, flags=re.IGNORECASE | re.DOTALL)
        html = re.sub(r'javascript:', '', html, flags=re.IGNORECASE)
        return html
    
    def markdown_to_html(self, markdown: str) -> str:
        """Basic markdown to HTML conversion"""
        import re
        html = markdown
        html = re.sub(r'^### (.*$)', r'<h3>\1</h3>', html, flags=re.MULTILINE)
        html = re.sub(r'^## (.*$)', r'<h2>\1</h2>', html, flags=re.MULTILINE)
        html = re.sub(r'^# (.*$)', r'<h1>\1</h1>', html, flags=re.MULTILINE)
        html = re.sub(r'\*\*(.*?)\*\*', r'<strong>\1</strong>', html)
        html = re.sub(r'\*(.*?)\*', r'<em>\1</em>', html)
        html = re.sub(r'\n', '<br>', html)
        return html
    
    def export_to_json(self, data: Union[Dict, List], filepath: str):
        """Export data to JSON file"""
        Path(filepath).write_text(json.dumps(data, indent=2, default=str))
        logger.info(f"Exported data to {filepath}")
    
    def import_from_json(self, filepath: str) -> Union[Dict, List]:
        """Import data from JSON file"""
        return json.loads(Path(filepath).read_text())
    
    async def close_async_session(self):
        """Close async session"""
        if self.session:
            await self.session.close()
    
    def get_pages_for_book(self, book_id: int) -> Dict:
        return self.make_request(
            endpoint="pages",
            method="GET",
            params={"filter[book_id]": book_id}
        )
    
    def del_all_pages_in_book(self, book_id: int) -> Dict:        
        pages = self.get_pages_for_book(book_id)
        data = pages.get('data', [])
        matching_ids = [item['id'] for item in data if item['book_id'] == 6]
        for page_id in matching_ids:
            print(f"Deleting page: {page_id}")
            self.delete_page(page_id)


# ===== PIPELINE UTILITIES =====

class BookStackPipeline:
    """Pipeline utilities for automation workflows"""
    
    def __init__(self, api: BookStackAPI):
        self.api = api
    
    def create_documentation_structure(self, structure: Dict) -> Dict:
        """Create complete documentation structure from config
        
        Example structure:
        {
            "book": {"name": "API Documentation", "description": "..."},
            "chapters": [
                {
                    "name": "Introduction",
                    "pages": [
                        {"name": "Overview", "content": "...", "type": "markdown"},
                        {"name": "Setup", "content": "...", "type": "html"}
                    ]
                }
            ]
        }
        """
        results = {'book': None, 'chapters': [], 'pages': []}
        
        # Create book
        book = self.api.create_book(structure['book'])
        results['book'] = book
        logger.info(f"Created book: {book['name']}")
        
        # Create chapters and pages
        for chapter_config in structure.get('chapters', []):
            chapter = self.api.create_chapter(book['id'], {
                'name': chapter_config['name'],
                'description': chapter_config.get('description', '')
            })
            results['chapters'].append(chapter)
            logger.info(f"Created chapter: {chapter['name']}")
            
            # Create pages in chapter
            for page_config in chapter_config.get('pages', []):
                page_data = {
                    'name': page_config['name'],
                    'tags': page_config.get('tags', []),
                    'priority': page_config.get('priority', 0)
                }
                
                # Handle different content types
                if page_config.get('type') == 'markdown':
                    page_data['markdown'] = page_config['content']
                else:
                    page_data['html'] = page_config['content']
                
                page = self.api.create_page_in_chapter(chapter['id'], page_data)
                results['pages'].append(page)
                logger.info(f"Created page: {page['name']}")
        
        return results
    
    def backup_book(self, book_id: int, output_dir: str) -> str:
        """Backup entire book structure to files"""
        output_path = Path(output_dir)
        output_path.mkdir(exist_ok=True)
        
        # Get book info
        book = self.api.get_book(book_id)
        
        # Create backup structure
        backup_data = {
            'book': book,
            'chapters': [],
            'pages': [],
            'backup_timestamp': time.time()
        }
        
        # Get all pages in book
        pages = self.api.get_pages(filter=f'book_id:{book_id}')
        
        for page in pages.get('data', []):
            full_page = self.api.get_page(page['id'])
            backup_data['pages'].append(full_page)
        
        # Get chapters
        chapters = self.api.get_chapters(book_id)
        backup_data['chapters'] = chapters.get('data', [])
        
        # Save backup
        backup_file = output_path / f"book_{book_id}_backup.json"
        self.api.export_to_json(backup_data, str(backup_file))
        
        return str(backup_file)
    




# ===== USAGE EXAMPLES =====

"""
# Basic usage
config = BookStackConfig(
    base_url='https://your-bookstack.com',
    token_id='your_token_id',
    token_secret='your_token_secret'
)

api = BookStackAPI(config)

# Create a simple page
page = api.create_html_page(
    book_id=1,
    name='My Documentation Page',
    html_content='<h1>Hello World</h1><p>This is my content.</p>',
    tags=['documentation', 'api']
)

# Batch create pages
pages_to_create = [
    {
        'book_id': 1,
        'name': 'Page 1',
        'html': '<h1>Page 1 Content</h1>',
        'tags': ['batch']
    },
    {
        'book_id': 1,
        'name': 'Page 2',
        'markdown': '# Page 2\n\nMarkdown content here.',
        'tags': ['batch', 'markdown']
    }
]

results = api.create_pages_batch(pages_to_create)

# Use pipeline for complex operations
pipeline = BookStackPipeline(api)

structure = {
    'book': {'name': 'Code Documentation', 'description': 'Generated docs'},
    'chapters': [
        {
            'name': 'API Reference',
            'pages': [
                {
                    'name': 'Overview',
                    'content': '# API Overview\n\nThis is the API documentation.',
                    'type': 'markdown',
                    'tags': ['api']
                }
            ]
        }
    ]
}

results = pipeline.create_documentation_structure(structure)

# Async usage
async def async_example():
    async with aiohttp.ClientSession() as session:
        api.session = session
        results = await api.create_pages_batch_async(pages_to_create)
        await api.close_async_session()

# asyncio.run(async_example())
"""