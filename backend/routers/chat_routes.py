"""
API routes for chat history management.
Handles conversation and message CRUD operations.
"""

from typing import List, Optional
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from pydantic import BaseModel
from datetime import datetime

from database import get_db
from models import MessageRole, User
import crud
from auth import get_current_user

router = APIRouter(prefix="/api/chat", tags=["chat-history"])


# ===== Pydantic Models =====

class MessageCreate(BaseModel):
    role: str  # 'user' or 'bot'
    content: str
    context_used: Optional[str] = None
    metadata: Optional[dict] = None


class MessageResponse(BaseModel):
    id: int
    role: str
    content: str
    context_used: Optional[str] = None
    created_at: datetime

    class Config:
        from_attributes = True


class ConversationCreate(BaseModel):
    title: Optional[str] = "New Conversation"
    context_type: str = "business"


class ConversationUpdate(BaseModel):
    title: Optional[str] = None


class ConversationResponse(BaseModel):
    id: int
    title: str
    context_type: str
    created_at: datetime
    updated_at: datetime
    is_archived: bool
    message_count: Optional[int] = None

    class Config:
        from_attributes = True


class ConversationDetailResponse(ConversationResponse):
    messages: List[MessageResponse]


# ===== Conversation Endpoints =====

@router.post("/conversations", response_model=ConversationResponse, status_code=status.HTTP_201_CREATED)
def create_conversation(
    conversation: ConversationCreate,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Create a new conversation for the authenticated user.
    """
    user = current_user
    
    new_conversation = crud.create_conversation(
        db=db,
        user_id=user.id,
        title=conversation.title,
        context_type=conversation.context_type
    )
    
    return {
        **new_conversation.__dict__,
        "message_count": 0
    }


@router.get("/conversations", response_model=List[ConversationResponse])
def get_conversations(
    include_archived: bool = False,
    limit: int = 50,
    search: Optional[str] = None,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Get all conversations for the authenticated user.
    Returns conversations ordered by most recent activity.
    """
    user = current_user
    conversations = crud.get_user_conversations(
        db=db,
        user_id=user.id,
        include_archived=include_archived,
        limit=limit,
        search_query=search
    )
    
    # Add message count to each conversation
    result = []
    for conv in conversations:
        result.append({
            **conv.__dict__,
            "message_count": len(conv.messages)
        })
    
    return result


@router.get("/conversations/{conversation_id}", response_model=ConversationDetailResponse)
def get_conversation(
    conversation_id: int,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Get a specific conversation with all its messages.
    """
    user = current_user
    conversation = crud.get_conversation(db=db, conversation_id=conversation_id, user_id=user.id)
    
    if not conversation:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Conversation not found"
        )
    
    messages = [
        {
            "id": msg.id,
            "role": msg.role.value,
            "content": msg.content,
            "context_used": msg.context_used,
            "created_at": msg.created_at
        }
        for msg in conversation.messages
    ]
    
    return {
        **conversation.__dict__,
        "message_count": len(messages),
        "messages": messages
    }


@router.patch("/conversations/{conversation_id}", response_model=ConversationResponse)
def update_conversation(
    conversation_id: int,
    update_data: ConversationUpdate,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Update a conversation (currently only title).
    """
    user = current_user
    
    if update_data.title:
        conversation = crud.update_conversation_title(
            db=db,
            conversation_id=conversation_id,
            user_id=user.id,
            new_title=update_data.title
        )
    else:
        conversation = crud.get_conversation(db=db, conversation_id=conversation_id, user_id=user.id)
    
    if not conversation:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Conversation not found"
        )
    
    return {
        **conversation.__dict__,
        "message_count": len(conversation.messages)
    }


@router.delete("/conversations/{conversation_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_conversation(
    conversation_id: int,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Delete a conversation and all its messages.
    """
    user = current_user
    deleted = crud.delete_conversation(db=db, conversation_id=conversation_id, user_id=user.id)
    
    if not deleted:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Conversation not found"
        )
    
    return None


@router.post("/conversations/{conversation_id}/archive", response_model=ConversationResponse)
def archive_conversation(
    conversation_id: int,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Archive a conversation (soft delete).
    """
    user = current_user
    conversation = crud.archive_conversation(db=db, conversation_id=conversation_id, user_id=user.id)
    
    if not conversation:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Conversation not found"
        )
    
    return {
        **conversation.__dict__,
        "message_count": len(conversation.messages)
    }


# ===== Message Endpoints =====

@router.post("/conversations/{conversation_id}/messages", response_model=MessageResponse, status_code=status.HTTP_201_CREATED)
def create_message(
    conversation_id: int,
    message: MessageCreate,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Add a message to a conversation.
    """
    user = current_user
    conversation = crud.get_conversation(db=db, conversation_id=conversation_id, user_id=user.id)
    
    if not conversation:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Conversation not found"
        )
    
    # Convert string role to MessageRole enum
    try:
        role_enum = MessageRole(message.role)
    except ValueError:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Invalid role. Must be one of: {[r.value for r in MessageRole]}"
        )
    
    new_message = crud.create_message(
        db=db,
        conversation_id=conversation_id,
        role=role_enum,
        content=message.content,
        context_used=message.context_used,
        metadata=message.metadata
    )
    
    return {
        "id": new_message.id,
        "role": new_message.role.value,
        "content": new_message.content,
        "context_used": new_message.context_used,
        "created_at": new_message.created_at
    }


@router.get("/conversations/{conversation_id}/messages", response_model=List[MessageResponse])
def get_messages(
    conversation_id: int,
    limit: Optional[int] = None,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    """
    Get all messages for a conversation.
    """
    user = current_user
    conversation = crud.get_conversation(db=db, conversation_id=conversation_id, user_id=user.id)
    
    if not conversation:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Conversation not found"
        )
    
    messages = crud.get_conversation_messages(db=db, conversation_id=conversation_id, limit=limit)
    
    return [
        {
            "id": msg.id,
            "role": msg.role.value,
            "content": msg.content,
            "context_used": msg.context_used,
            "created_at": msg.created_at
        }
        for msg in messages
    ]
