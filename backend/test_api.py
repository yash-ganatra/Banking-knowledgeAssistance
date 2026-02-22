import requests
from database import SessionLocal
from models import User
import crud

# 1. Get a user to create token
db = SessionLocal()
user = db.query(User).first()
if not user:
    print("No user found")
    exit(1)

# In real API, we need a JWT token. Since we don't know the password to login,
# we might need to forge a token or use the auth module's create_access_token.
from auth import create_access_token
from datetime import timedelta

access_token = create_access_token(
    data={"sub": user.username}, expires_delta=timedelta(minutes=30)
)

headers = {"Authorization": f"Bearer {access_token}"}

# 2. Add test data
conv = crud.create_conversation(db, user_id=user.id, title="Test Endpoint API")
crud.create_message(db, conversation_id=conv.id, role="user", content="UNIQUEENDPOINTWORD")

# 3. Test API without search
response = requests.get("http://localhost:8000/api/chat/conversations", headers=headers)
print(f"GET all status: {response.status_code}")
print(f"Total conversations from API: {len(response.json())}")

# 4. Test API with search
response_search = requests.get("http://localhost:8000/api/chat/conversations?search=UNIQUEENDPOINTWORD", headers=headers)
print(f"GET search status: {response_search.status_code}")
data = response_search.json()
print(f"Total conversations matching search: {len(data)}")
for c in data:
    print(f"Matched ID {c.get('id')} Title {c.get('title')}")

# cleanup
crud.delete_conversation(db, conv.id, user.id)
db.close()
