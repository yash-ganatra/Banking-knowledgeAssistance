"""
Database configuration and connection management.
Handles PostgreSQL connection with SQLAlchemy.
"""

import os
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, Session
from sqlalchemy.pool import QueuePool
from contextlib import contextmanager
from typing import Generator
import logging
from dotenv import load_dotenv

from models import Base

load_dotenv()
logger = logging.getLogger(__name__)

# Database configuration
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://postgres:postgres@localhost:5432/banking_assistant"
)

# Create engine with connection pooling for production scalability
engine = create_engine(
    DATABASE_URL,
    poolclass=QueuePool,
    pool_size=5,  # Number of connections to keep open
    max_overflow=10,  # Number of connections that can be created beyond pool_size
    pool_timeout=30,  # Seconds to wait before giving up on getting a connection
    pool_recycle=3600,  # Recycle connections after 1 hour
    echo=False,  # Set to True for SQL query logging during development
)

# Session factory
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)


def init_db():
    """
    Initialize database: create all tables if they don't exist.
    Call this on application startup.
    """
    try:
        Base.metadata.create_all(bind=engine)
        logger.info("Database initialized successfully")
        
        # Create default user if not exists
        from models import User, UserRole
        from auth import get_password_hash
        db = SessionLocal()
        try:
            default_user = db.query(User).filter(User.username == "default_user").first()
            if not default_user:
                default_user = User(
                    username="default_user",
                    full_name="Default User",
                    email="user@banking-assistant.local",
                    hashed_password=get_password_hash("password123"),  # Default password
                    role=UserRole.TEAM_MEMBER,
                    is_active=True
                )
                db.add(default_user)
                db.commit()
                logger.info("Default user created with password: password123")
        finally:
            db.close()
            
    except Exception as e:
        logger.error(f"Error initializing database: {e}")
        raise


def get_db() -> Generator[Session, None, None]:
    """
    Dependency for FastAPI to get database session.
    Use this in route dependencies.
    
    Usage:
        @app.get("/items")
        def get_items(db: Session = Depends(get_db)):
            ...
    """
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


@contextmanager
def get_db_context():
    """
    Context manager for database session.
    Use this for manual session management.
    
    Usage:
        with get_db_context() as db:
            user = db.query(User).first()
    """
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def close_db():
    """
    Close database connections.
    Call this on application shutdown.
    """
    engine.dispose()
    logger.info("Database connections closed")
