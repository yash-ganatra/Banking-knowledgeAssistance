"""
Database models for user and chat history management.
Uses SQLAlchemy for PostgreSQL integration.
Designed to be scalable for multi-user platform.
"""

from datetime import datetime
from sqlalchemy import Column, Integer, String, Text, DateTime, ForeignKey, Enum, Boolean, Float, JSON
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import relationship
import enum

Base = declarative_base()


class UserRole(str, enum.Enum):
    """User roles for future multi-user support"""
    ADMIN = "admin"
    TEAM_LEAD = "team_lead"
    TEAM_MEMBER = "team_member"


class User(Base):
    """
    User model for authentication and chat ownership.
    Currently supporting single user, but ready for multi-user expansion.
    """
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(50), unique=True, nullable=False, index=True)
    email = Column(String(100), unique=True, nullable=False, index=True)
    hashed_password = Column(String(255), nullable=False)
    full_name = Column(String(100), nullable=True)
    role = Column(Enum(UserRole), default=UserRole.TEAM_MEMBER, nullable=False)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    # Relationships
    conversations = relationship("Conversation", back_populates="user", cascade="all, delete-orphan")

    def __repr__(self):
        return f"<User(id={self.id}, username='{self.username}', role='{self.role}')>"


class Conversation(Base):
    """
    Conversation/Chat session model.
    Each conversation represents a separate chat thread.
    """
    __tablename__ = "conversations"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    title = Column(String(200), nullable=False, default="New Conversation")
    context_type = Column(String(50), nullable=False, default="business")  # business, php, js, blade
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)
    is_archived = Column(Boolean, default=False, nullable=False)

    # Relationships
    user = relationship("User", back_populates="conversations")
    messages = relationship("Message", back_populates="conversation", cascade="all, delete-orphan", order_by="Message.created_at")

    def __repr__(self):
        return f"<Conversation(id={self.id}, title='{self.title}', user_id={self.user_id})>"


class MessageRole(str, enum.Enum):
    """Message roles"""
    USER = "user"
    BOT = "bot"
    SYSTEM = "system"


class Message(Base):
    """
    Individual message within a conversation.
    Stores both user queries and bot responses.
    """
    __tablename__ = "messages"

    id = Column(Integer, primary_key=True, index=True)
    conversation_id = Column(Integer, ForeignKey("conversations.id", ondelete="CASCADE"), nullable=False, index=True)
    role = Column(Enum(MessageRole), nullable=False)
    content = Column(Text, nullable=False)
    context_used = Column(Text, nullable=True)  # Stores the context/sources used for bot responses
    message_metadata = Column(Text, nullable=True)  # JSON string for additional metadata (top_k, rerank settings, etc.)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

    # Relationships
    conversation = relationship("Conversation", back_populates="messages")

    def __repr__(self):
        return f"<Message(id={self.id}, role='{self.role}', conversation_id={self.conversation_id})>"


# ============================================================
# INFERENCE LOGGING MODELS
# ============================================================

class InferenceLog(Base):
    """
    Comprehensive log of each inference request.
    Tracks the complete pipeline from query to response.
    """
    __tablename__ = "inference_logs"

    id = Column(Integer, primary_key=True, index=True)
    
    # Request Info
    query = Column(Text, nullable=False)
    processed_query = Column(Text, nullable=True)  # After preprocessing (abbreviation expansion)
    endpoint = Column(String(50), nullable=False)  # smart, php, js, blade, business
    top_k = Column(Integer, default=5)
    confidence_threshold = Column(Float, nullable=True)
    min_relevance_score = Column(Float, nullable=True)
    
    # Query Expansion (BM25 intent-aware expansion)
    query_expansion_applied = Column(Boolean, default=False)
    expanded_query = Column(Text, nullable=True)  # Query after BM25 abbreviation expansion
    expansion_reason = Column(String(200), nullable=True)  # Why expansion was/wasn't applied
    
    # Routing Decision (for smart router)
    primary_source = Column(String(50), nullable=True)
    secondary_sources = Column(JSON, nullable=True)  # List of secondary sources
    routing_confidence = Column(Float, nullable=True)
    routing_reasoning = Column(Text, nullable=True)
    query_type = Column(String(50), nullable=True)  # documentation, implementation, debugging, etc.
    
    # Retrieval Stats
    sources_queried = Column(JSON, nullable=True)  # List of sources actually queried
    total_chunks_retrieved = Column(Integer, default=0)
    chunks_after_filtering = Column(Integer, default=0)
    chunks_after_reranking = Column(Integer, default=0)
    
    # Hybrid Search Stats
    hybrid_search_used = Column(Boolean, default=False)
    dense_results_count = Column(Integer, nullable=True)
    sparse_results_count = Column(Integer, nullable=True)
    found_by_both_count = Column(Integer, nullable=True)
    
    # Graph Enhancement Stats
    graph_used = Column(Boolean, default=False)
    graph_context = Column(JSON, nullable=True)  # Stores related entities/relationships found
    
    # Timing
    total_time_ms = Column(Float, nullable=True)
    routing_time_ms = Column(Float, nullable=True)
    retrieval_time_ms = Column(Float, nullable=True)
    reranking_time_ms = Column(Float, nullable=True)
    llm_time_ms = Column(Float, nullable=True)
    
    # Token Usage (from LLM response)
    input_tokens = Column(Integer, nullable=True)   # prompt tokens consumed
    output_tokens = Column(Integer, nullable=True)  # completion tokens generated
    
    # User/Session Info
    user_id = Column(Integer, ForeignKey("users.id", ondelete="SET NULL"), nullable=True, index=True)
    conversation_id = Column(Integer, ForeignKey("conversations.id", ondelete="SET NULL"), nullable=True)
    session_id = Column(String(100), nullable=True)  # For tracking anonymous sessions
    
    # Status
    success = Column(Boolean, default=True)
    error_message = Column(Text, nullable=True)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False, index=True)

    # Relationships
    user = relationship("User", foreign_keys=[user_id])
    conversation = relationship("Conversation", foreign_keys=[conversation_id])
    retrieval_details = relationship("RetrievalDetail", back_populates="inference_log", cascade="all, delete-orphan")

    def __repr__(self):
        return f"<InferenceLog(id={self.id}, endpoint='{self.endpoint}', query='{self.query[:50]}...')>"


class RetrievalDetail(Base):
    """
    Detailed log of each chunk retrieved during inference.
    Tracks chunks at different stages: initial retrieval, after filtering, after reranking.
    """
    __tablename__ = "retrieval_details"

    id = Column(Integer, primary_key=True, index=True)
    inference_log_id = Column(Integer, ForeignKey("inference_logs.id", ondelete="CASCADE"), nullable=False, index=True)
    
    # Chunk Info
    chunk_id = Column(String(200), nullable=False)
    source = Column(String(50), nullable=False)  # php_code, js_code, blade_templates, business_docs
    content_preview = Column(Text, nullable=True)  # First 500 chars of content
    
    # Metadata
    file_path = Column(String(500), nullable=True)
    file_name = Column(String(200), nullable=True)
    class_name = Column(String(200), nullable=True)
    method_name = Column(String(200), nullable=True)
    
    # Scores at different stages
    initial_rank = Column(Integer, nullable=True)
    initial_distance = Column(Float, nullable=True)
    
    # Hybrid search scores
    bm25_score = Column(Float, nullable=True)
    bm25_rank = Column(Integer, nullable=True)
    hybrid_rrf_score = Column(Float, nullable=True)
    found_by_both = Column(Boolean, default=False)
    search_methods = Column(JSON, nullable=True)  # ["dense", "sparse"]
    
    # After RRF fusion
    rrf_score = Column(Float, nullable=True)
    rrf_rank = Column(Integer, nullable=True)
    
    # After cross-encoder reranking
    cross_encoder_score = Column(Float, nullable=True)
    final_rank = Column(Integer, nullable=True)
    
    # Stage tracking
    stage = Column(String(50), nullable=False)  # initial, after_hybrid, after_rrf, after_rerank, final
    included_in_context = Column(Boolean, default=False)  # Was this chunk used in LLM context?
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

    # Relationships
    inference_log = relationship("InferenceLog", back_populates="retrieval_details")

    def __repr__(self):
        return f"<RetrievalDetail(id={self.id}, chunk_id='{self.chunk_id}', stage='{self.stage}')>"


# ============================================================
# LOG ANALYZER MODELS
# ============================================================

class LogAnalysisJob(Base):
    """
    Stores parsed and analyzed log file results.
    Analysis is generated automatically after parse/upload and
    persisted so UI can fetch with a simple "view" action.
    """
    __tablename__ = "log_analysis_jobs"

    id = Column(Integer, primary_key=True, index=True)
    file_name = Column(String(255), nullable=True)
    file_hash = Column(String(64), nullable=False, index=True)
    status = Column(String(30), nullable=False, default="processing", index=True)  # processing, completed, failed

    total_entries = Column(Integer, nullable=True)
    unique_errors = Column(Integer, nullable=True)

    # JSON snapshots for quick retrieval by UI
    parse_result = Column(JSON, nullable=True)
    analysis_result = Column(JSON, nullable=True)
    error_message = Column(Text, nullable=True)

    created_at = Column(DateTime, default=datetime.utcnow, nullable=False, index=True)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    def __repr__(self):
        return f"<LogAnalysisJob(id={self.id}, file_name='{self.file_name}', status='{self.status}')>"
