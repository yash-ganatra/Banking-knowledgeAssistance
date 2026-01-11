"""
Authentication utilities for user login and JWT token management.
Handles password hashing and token generation/verification.
"""

import os
from datetime import datetime, timedelta
from typing import Optional
import bcrypt
from jose import JWTError, jwt
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from sqlalchemy.orm import Session
from dotenv import load_dotenv

from database import get_db
import crud

load_dotenv()

# Configuration
SECRET_KEY = os.getenv("SECRET_KEY", "your-secret-key-change-this-in-production-min-32-chars")
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

# OAuth2 scheme for token authentication
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/api/auth/login")


def verify_password(plain_password: str, hashed_password: str) -> bool:
    """Verify a password against its hash"""
    return bcrypt.checkpw(plain_password.encode('utf-8'), hashed_password.encode('utf-8'))


def get_password_hash(password: str) -> str:
    """Generate password hash"""
    salt = bcrypt.gensalt()
    hashed = bcrypt.hashpw(password.encode('utf-8'), salt)
    return hashed.decode('utf-8')


def create_access_token(data: dict, expires_delta: Optional[timedelta] = None) -> str:
    """
    Create a JWT access token.
    
    Args:
        data: Dictionary to encode in the token (typically contains 'sub' with user_id)
        expires_delta: Optional custom expiration time
        
    Returns:
        Encoded JWT token string
    """
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt


def decode_access_token(token: str) -> Optional[dict]:
    """
    Decode and verify a JWT token.
    
    Args:
        token: JWT token string
        
    Returns:
        Decoded token payload or None if invalid
    """
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        return payload
    except JWTError:
        return None


def get_current_user(token: str = Depends(oauth2_scheme), db: Session = Depends(get_db)):
    """
    Dependency to get the current authenticated user from JWT token.
    
    Usage in routes:
        @router.get("/protected")
        def protected_route(current_user: User = Depends(get_current_user)):
            ...
    """
    import logging
    logger = logging.getLogger(__name__)
    
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Could not validate credentials",
        headers={"WWW-Authenticate": "Bearer"},
    )
    
    try:
        payload = decode_access_token(token)
        if payload is None:
            logger.error("Token decode failed - invalid token")
            raise credentials_exception
        
        user_id_str = payload.get("sub")
        if user_id_str is None:
            logger.error(f"Token payload missing 'sub' field: {payload}")
            raise credentials_exception
        
        # Convert string back to integer
        try:
            user_id = int(user_id_str)
        except (ValueError, TypeError) as e:
            logger.error(f"Invalid user_id in token: {user_id_str}")
            raise credentials_exception
        
        logger.info(f"Validating user_id: {user_id} (type: {type(user_id)})")
        
        user = crud.get_user_by_id(db, user_id=user_id)
        if user is None:
            logger.error(f"User not found with id: {user_id}")
            raise credentials_exception
        
        if not user.is_active:
            logger.error(f"User {user_id} is not active")
            raise credentials_exception
        
        logger.info(f"Authentication successful for user: {user.username} (id: {user.id})")
        return user
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Unexpected error in get_current_user: {str(e)}")
        raise credentials_exception


def get_current_active_user(current_user = Depends(get_current_user)):
    """
    Dependency to ensure the current user is active.
    
    Usage in routes:
        @router.get("/protected")
        def protected_route(current_user: User = Depends(get_current_active_user)):
            ...
    """
    if not current_user.is_active:
        raise HTTPException(status_code=400, detail="Inactive user")
    return current_user


def authenticate_user(db: Session, username: str, password: str):
    """
    Authenticate a user by username and password.
    
    Args:
        db: Database session
        username: Username
        password: Plain text password
        
    Returns:
        User object if authentication successful, False otherwise
    """
    user = crud.get_user_by_username(db, username=username)
    if not user:
        return False
    if not verify_password(password, user.hashed_password):
        return False
    return user
