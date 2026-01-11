"""
Authentication routes for user signup and login.
Handles user registration, authentication, and token management.
"""

from typing import Optional
from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session
from pydantic import BaseModel, EmailStr, Field
from datetime import timedelta

from database import get_db
from models import User, UserRole
import crud
from auth import (
    get_password_hash, 
    authenticate_user, 
    create_access_token,
    get_current_user,
    ACCESS_TOKEN_EXPIRE_MINUTES
)

router = APIRouter(prefix="/api/auth", tags=["authentication"])


# ===== Pydantic Models =====

class SignupRequest(BaseModel):
    """Request model for user registration"""
    username: str = Field(..., min_length=3, max_length=50, description="Username (3-50 characters, case-insensitive)")
    email: EmailStr = Field(..., description="Valid email address")
    password: str = Field(..., min_length=6, description="Password (minimum 6 characters)")
    full_name: Optional[str] = Field(default=None, description="Full name for display")
    role: Optional[str] = Field(default="team_member", description="User role: admin, team_lead, or team_member")

    class Config:
        json_schema_extra = {
            "example": {
                "username": "john_doe",
                "email": "john@example.com",
                "password": "securepass123",
                "full_name": "John Doe",
                "role": "team_member"
            }
        }


class LoginRequest(BaseModel):
    """Request model for user login (alternative to OAuth2PasswordRequestForm)"""
    username: str
    password: str

    class Config:
        json_schema_extra = {
            "example": {
                "username": "john_doe",
                "password": "securepass123"
            }
        }


class TokenResponse(BaseModel):
    """Response model for authentication tokens"""
    access_token: str
    token_type: str = "bearer"
    user_id: int
    username: str
    email: str
    role: str


class UserResponse(BaseModel):
    """Response model for user information"""
    id: int
    username: str
    email: str
    role: str
    is_active: bool

    class Config:
        from_attributes = True


# ===== Authentication Endpoints =====

@router.post("/signup", response_model=TokenResponse, status_code=status.HTTP_201_CREATED)
def signup(
    signup_data: SignupRequest,
    db: Session = Depends(get_db)
):
    """
    Register a new user account.
    
    Required fields:
    - username: Unique username (3-50 characters)
    - email: Valid email address (must be unique)
    - password: Password (minimum 6 characters)
    - role: User role (admin, team_lead, or team_member) - defaults to team_member
    
    Returns JWT access token upon successful registration.
    """
    # Validate role
    valid_roles = ["admin", "team_lead", "team_member"]
    if signup_data.role not in valid_roles:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Invalid role. Must be one of: {', '.join(valid_roles)}"
        )
    
    # Check if username already exists (case-insensitive)
    existing_user = crud.get_user_by_username(db, username=signup_data.username.lower())
    if existing_user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Username already registered"
        )
    
    # Check if email already exists (case-insensitive)
    existing_email = crud.get_user_by_email(db, email=signup_data.email.lower())
    if existing_email:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Email already registered"
        )
    
    # Convert role string to enum
    role_mapping = {
        "admin": UserRole.ADMIN,
        "team_lead": UserRole.TEAM_LEAD,
        "team_member": UserRole.TEAM_MEMBER
    }
    user_role = role_mapping[signup_data.role]
    
    # Hash password
    hashed_password = get_password_hash(signup_data.password)
    
    # Create user
    new_user = crud.create_user_with_password(
        db=db,
        username=signup_data.username,
        email=signup_data.email,
        hashed_password=hashed_password,
        full_name=signup_data.full_name,
        role=user_role
    )
    
    # Generate access token
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        data={"sub": str(new_user.id)},  # JWT spec requires sub to be a string
        expires_delta=access_token_expires
    )
    
    return {
        "access_token": access_token,
        "token_type": "bearer",
        "user_id": new_user.id,
        "username": new_user.username,
        "email": new_user.email,
        "role": new_user.role.value
    }


@router.post("/login", response_model=TokenResponse)
def login(
    form_data: OAuth2PasswordRequestForm = Depends(),
    db: Session = Depends(get_db)
):
    """
    Login with username and password.
    
    Uses OAuth2 password flow (form data with username and password fields).
    Returns JWT access token upon successful authentication.
    
    Can also be called with JSON body using /api/auth/login-json endpoint.
    """
    user = authenticate_user(db, form_data.username, form_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Account is inactive"
        )
    
    # Generate access token
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        data={"sub": str(user.id)},  # JWT spec requires sub to be a string (was new_user.id)
        expires_delta=access_token_expires
    )
    
    return {
        "access_token": access_token,
        "token_type": "bearer",
        "user_id": user.id,
        "username": user.username,
        "email": user.email,
        "role": user.role.value
    }


@router.post("/login-json", response_model=TokenResponse)
def login_json(
    login_data: LoginRequest,
    db: Session = Depends(get_db)
):
    """
    Login with username and password (JSON body).
    
    Alternative to /login endpoint that accepts JSON instead of form data.
    Useful for frontend applications that prefer JSON requests.
    """
    user = authenticate_user(db, login_data.username, login_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Account is inactive"
        )
    
    # Generate access token
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        data={"sub": str(user.id)},  # JWT spec requires sub to be a string
        expires_delta=access_token_expires
    )
    
    return {
        "access_token": access_token,
        "token_type": "bearer",
        "user_id": user.id,
        "username": user.username,
        "email": user.email,
        "role": user.role.value
    }


@router.get("/me", response_model=UserResponse)
def get_current_user_info(current_user: User = Depends(get_current_user)):
    """
    Get current authenticated user information.
    
    Requires valid JWT token in Authorization header.
    Returns user profile information.
    """
    return {
        "id": current_user.id,
        "username": current_user.username,
        "email": current_user.email,
        "role": current_user.role.value,
        "is_active": current_user.is_active
    }
