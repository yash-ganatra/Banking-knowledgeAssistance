"""
Database migration script to add inference logging tables.

Run this script after updating models.py to create the new tables.
"""

import os
import sys

# Add the backend directory to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from database import engine, Base
from models import InferenceLog, RetrievalDetail

def create_inference_tables():
    """Create the inference logging tables if they don't exist"""
    
    print("Creating inference logging tables...")
    
    # Create tables for the inference logging models only
    # This won't recreate existing tables
    InferenceLog.__table__.create(engine, checkfirst=True)
    RetrievalDetail.__table__.create(engine, checkfirst=True)
    
    print("✅ Inference logging tables created successfully!")
    print("\nTables created:")
    print("  - inference_logs")
    print("  - retrieval_details")
    print("\nYou can now use the /inference-logs API endpoints.")

if __name__ == "__main__":
    create_inference_tables()
