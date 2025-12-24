from bookstack_api import BookStackAPI, BookStackConfig

config = BookStackConfig(
    base_url='http://neuron.appinsource.com:6427/',
    token_id='4ZQrO9Uc1LnHOLfKc4RsNG0RwE1JCPdJ',
    token_secret='bgAM9sNZzt5iI8voZCNkxLQrQHipTMkc'
)

api = BookStackAPI(config)

# Get all shelves
shelves = api.get_shelves()

# Display shelf IDs and names
for shelf in shelves.get('data', []):
    print(f"Shelf ID: {shelf['id']}, Name: {shelf['name']}")