"""
CRUD operations for database entities.
Provides reusable functions for database operations.
"""

from typing import List, Optional
from sqlalchemy.orm import Session
from sqlalchemy import desc
from datetime import datetime
import json

from models import User, Conversation, Message, UserRole, MessageRole


# ===== User Operations =====

def get_user_by_id(db: Session, user_id: int) -> Optional[User]:
    """Get user by ID"""
    return db.query(User).filter(User.id == user_id).first()


def get_user_by_username(db: Session, username: str) -> Optional[User]:
    """Get user by username (case-insensitive)"""
    return db.query(User).filter(User.username.ilike(username)).first()


def get_user_by_email(db: Session, email: str) -> Optional[User]:
    """Get user by email (case-insensitive)"""
    return db.query(User).filter(User.email.ilike(email)).first()


def get_default_user(db: Session) -> User:
    """Get or create default user for single-user mode"""
    user = db.query(User).filter(User.username == "default_user").first()
    if not user:
        user = User(
            username="default_user",
            full_name="Default User",
            email="user@banking-assistant.local",
            role=UserRole.TEAM_MEMBER,
            is_active=True
        )
        db.add(user)
        db.commit()
        db.refresh(user)
    return user


def create_user(db: Session, username: str, email: Optional[str] = None, 
                full_name: Optional[str] = None, role: UserRole = UserRole.TEAM_MEMBER) -> User:
    """Create a new user (legacy - without password)"""
    user = User(
        username=username,
        email=email,
        full_name=full_name,
        role=role,
        is_active=True,
        hashed_password=""  # Default empty for legacy users
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    return user


def create_user_with_password(db: Session, username: str, email: str, 
                               hashed_password: str, full_name: Optional[str] = None,
                               role: UserRole = UserRole.TEAM_MEMBER) -> User:
    """Create a new user with password (for authentication)"""
    user = User(
        username=username.lower(),  # Store in lowercase for case-insensitive login
        email=email.lower(),  # Also normalize email
        hashed_password=hashed_password,
        full_name=full_name,
        role=role,
        is_active=True
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    return user


# ===== Conversation Operations =====

def create_conversation(db: Session, user_id: int, title: str = "New Conversation", 
                       context_type: str = "business") -> Conversation:
    """Create a new conversation"""
    conversation = Conversation(
        user_id=user_id,
        title=title,
        context_type=context_type
    )
    db.add(conversation)
    db.commit()
    db.refresh(conversation)
    return conversation


def get_conversation(db: Session, conversation_id: int, user_id: int) -> Optional[Conversation]:
    """Get a specific conversation for a user"""
    return db.query(Conversation).filter(
        Conversation.id == conversation_id,
        Conversation.user_id == user_id
    ).first()


def get_user_conversations(db: Session, user_id: int, include_archived: bool = False,
                          limit: int = 50) -> List[Conversation]:
    """Get all conversations for a user, ordered by most recent"""
    query = db.query(Conversation).filter(Conversation.user_id == user_id)
    
    if not include_archived:
        query = query.filter(Conversation.is_archived == False)
    
    return query.order_by(desc(Conversation.updated_at)).limit(limit).all()


def update_conversation_title(db: Session, conversation_id: int, user_id: int, 
                             new_title: str) -> Optional[Conversation]:
    """Update conversation title"""
    conversation = get_conversation(db, conversation_id, user_id)
    if conversation:
        conversation.title = new_title
        conversation.updated_at = datetime.utcnow()
        db.commit()
        db.refresh(conversation)
    return conversation


def archive_conversation(db: Session, conversation_id: int, user_id: int) -> Optional[Conversation]:
    """Archive a conversation"""
    conversation = get_conversation(db, conversation_id, user_id)
    if conversation:
        conversation.is_archived = True
        conversation.updated_at = datetime.utcnow()
        db.commit()
        db.refresh(conversation)
    return conversation


def delete_conversation(db: Session, conversation_id: int, user_id: int) -> bool:
    """Delete a conversation and all its messages"""
    conversation = get_conversation(db, conversation_id, user_id)
    if conversation:
        db.delete(conversation)
        db.commit()
        return True
    return False


# ===== Message Operations =====

def create_message(db: Session, conversation_id: int, role: MessageRole, 
                  content: str, context_used: Optional[str] = None,
                  metadata: Optional[dict] = None) -> Message:
    """Create a new message in a conversation"""
    message = Message(
        conversation_id=conversation_id,
        role=role,
        content=content,
        context_used=context_used,
        message_metadata=json.dumps(metadata) if metadata else None
    )
    db.add(message)
    
    # Update conversation's updated_at timestamp
    conversation = db.query(Conversation).filter(Conversation.id == conversation_id).first()
    if conversation:
        conversation.updated_at = datetime.utcnow()
    
    db.commit()
    db.refresh(message)
    return message


def get_conversation_messages(db: Session, conversation_id: int, 
                             limit: Optional[int] = None) -> List[Message]:
    """Get all messages for a conversation, ordered by creation time"""
    query = db.query(Message).filter(Message.conversation_id == conversation_id).order_by(Message.created_at)
    
    if limit:
        query = query.limit(limit)
    
    return query.all()


def get_recent_messages(db: Session, conversation_id: int, count: int = 10) -> List[Message]:
    """Get the most recent N messages from a conversation"""
    return db.query(Message).filter(
        Message.conversation_id == conversation_id
    ).order_by(desc(Message.created_at)).limit(count).all()[::-1]  # Reverse to get chronological order


def delete_message(db: Session, message_id: int) -> bool:
    """Delete a specific message"""
    message = db.query(Message).filter(Message.id == message_id).first()
    if message:
        db.delete(message)
        db.commit()
        return True
    return False


# ===== Utility Functions =====

def generate_conversation_title(first_message: str, max_length: int = 50) -> str:
    """
    Generate a conversation title from the first message.
    Useful for auto-naming conversations.
    """
    if len(first_message) <= max_length:
        return first_message
    return first_message[:max_length-3] + "..."


def get_conversation_summary(db: Session, conversation_id: int) -> dict:
    """
    Get a summary of a conversation including message count and latest activity.
    """
    conversation = db.query(Conversation).filter(Conversation.id == conversation_id).first()
    if not conversation:
        return None
    
    message_count = db.query(Message).filter(Message.conversation_id == conversation_id).count()
    
    return {
        "id": conversation.id,
        "title": conversation.title,
        "context_type": conversation.context_type,
        "message_count": message_count,
        "created_at": conversation.created_at.isoformat(),
        "updated_at": conversation.updated_at.isoformat(),
        "is_archived": conversation.is_archived
    }
