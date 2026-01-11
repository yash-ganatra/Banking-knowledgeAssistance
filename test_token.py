import os
from jose import jwt

# Test if the SECRET_KEY is accessible
SECRET_KEY = os.getenv("SECRET_KEY", "your-secret-key-change-this-in-production-min-32-chars")
print(f"SECRET_KEY length: {len(SECRET_KEY)}")
print(f"SECRET_KEY (first 20 chars): {SECRET_KEY[:20]}...")

# Create a test token with STRING user_id (as per JWT spec)
test_token = jwt.encode({"sub": "1", "exp": 9999999999}, SECRET_KEY, algorithm="HS256")
print(f"\nTest token created: {test_token[:50]}...")

# Try to decode it
try:
    decoded = jwt.decode(test_token, SECRET_KEY, algorithms=["HS256"])
    print(f"✅ Test token decoded successfully: {decoded}")
    print(f"✅ User ID from token: {decoded['sub']} (type: {type(decoded['sub'])})")
    print(f"✅ Converted to int: {int(decoded['sub'])}")
except Exception as e:
    print(f"❌ ERROR decoding test token: {e}")
