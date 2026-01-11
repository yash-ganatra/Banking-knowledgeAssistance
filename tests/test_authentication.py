"""
Test script for authentication endpoints.
Run this after starting the server to verify auth functionality.
"""

import requests
import json

BASE_URL = "http://localhost:8000"

def test_signup():
    """Test user signup"""
    print("\n=== Testing Signup ===")
    response = requests.post(
        f"{BASE_URL}/api/auth/signup",
        json={
            "username": "test_user",
            "email": "test@example.com",
            "password": "testpass123",
            "role": "team_member"
        }
    )
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    
    if response.status_code == 201:
        return response.json()["access_token"]
    return None

def test_login():
    """Test user login"""
    print("\n=== Testing Login (JSON) ===")
    response = requests.post(
        f"{BASE_URL}/api/auth/login-json",
        json={
            "username": "test_user",
            "password": "testpass123"
        }
    )
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    
    if response.status_code == 200:
        return response.json()["access_token"]
    return None

def test_get_current_user(token):
    """Test getting current user info"""
    print("\n=== Testing Get Current User ===")
    headers = {"Authorization": f"Bearer {token}"}
    response = requests.get(
        f"{BASE_URL}/api/auth/me",
        headers=headers
    )
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")

def test_duplicate_signup():
    """Test duplicate username error"""
    print("\n=== Testing Duplicate Signup (Should Fail) ===")
    response = requests.post(
        f"{BASE_URL}/api/auth/signup",
        json={
            "username": "test_user",
            "email": "another@example.com",
            "password": "password123",
            "role": "team_member"
        }
    )
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")

def test_invalid_login():
    """Test login with wrong password"""
    print("\n=== Testing Invalid Login (Should Fail) ===")
    response = requests.post(
        f"{BASE_URL}/api/auth/login-json",
        json={
            "username": "test_user",
            "password": "wrongpassword"
        }
    )
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")

def test_default_user_login():
    """Test login with default user"""
    print("\n=== Testing Default User Login ===")
    response = requests.post(
        f"{BASE_URL}/api/auth/login-json",
        json={
            "username": "default_user",
            "password": "password123"
        }
    )
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    
    if response.status_code == 200:
        return response.json()["access_token"]
    return None

def main():
    """Run all tests"""
    print("=" * 60)
    print("Authentication System Test")
    print("=" * 60)
    print("\nMake sure the server is running: uvicorn main:app --reload")
    print("Starting tests...\n")
    
    try:
        # Test default user first
        default_token = test_default_user_login()
        if default_token:
            test_get_current_user(default_token)
        
        # Test signup
        token = test_signup()
        if token:
            # Test getting current user with signup token
            test_get_current_user(token)
            
            # Test login with the new user
            login_token = test_login()
            if login_token:
                test_get_current_user(login_token)
        
        # Test error cases
        test_duplicate_signup()
        test_invalid_login()
        
        print("\n" + "=" * 60)
        print("Tests completed!")
        print("=" * 60)
        
    except requests.exceptions.ConnectionError:
        print("\n❌ ERROR: Could not connect to server!")
        print("Make sure the server is running:")
        print("  cd backend && uvicorn main:app --reload")
    except Exception as e:
        print(f"\n❌ ERROR: {e}")

if __name__ == "__main__":
    main()
