#!/usr/bin/env python3
"""
Quick database migration - no prompts
Adds hashed_password column to users table
"""

import os
import sys
from sqlalchemy import create_engine, text
from dotenv import load_dotenv

sys.path.insert(0, os.path.dirname(__file__))
load_dotenv()

DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://postgres:postgres@localhost:5432/banking_assistant"
)

def migrate():
    engine = create_engine(DATABASE_URL)
    
    print("🔄 Running database migration...")
    print(f"Database: {DATABASE_URL}")
    
    try:
        with engine.connect() as conn:
            trans = conn.begin()
            
            try:
                # Check if column exists
                result = conn.execute(text("""
                    SELECT column_name 
                    FROM information_schema.columns 
                    WHERE table_name='users' AND column_name='hashed_password'
                """))
                
                if result.fetchone():
                    print("✅ Column 'hashed_password' already exists")
                else:
                    # Add column
                    conn.execute(text("""
                        ALTER TABLE users 
                        ADD COLUMN hashed_password VARCHAR(255) DEFAULT ''
                    """))
                    print("✅ Added 'hashed_password' column")
                    
                    # Update with default password (password123)
                    conn.execute(text("""
                        UPDATE users 
                        SET hashed_password = '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyMpghEU.H9S'
                        WHERE hashed_password = ''
                    """))
                    print("✅ Set default passwords (password123)")
                    
                    # Make NOT NULL
                    conn.execute(text("""
                        ALTER TABLE users 
                        ALTER COLUMN hashed_password SET NOT NULL
                    """))
                    print("✅ Made 'hashed_password' NOT NULL")
                
                # Fix email column
                result = conn.execute(text("""
                    SELECT is_nullable 
                    FROM information_schema.columns 
                    WHERE table_name='users' AND column_name='email'
                """))
                
                row = result.fetchone()
                if row and row[0] == 'YES':
                    conn.execute(text("""
                        UPDATE users 
                        SET email = CONCAT(username, '@default.local')
                        WHERE email IS NULL OR email = ''
                    """))
                    
                    conn.execute(text("""
                        ALTER TABLE users 
                        ALTER COLUMN email SET NOT NULL
                    """))
                    print("✅ Made 'email' NOT NULL")
                else:
                    print("✅ Email column already NOT NULL")
                
                trans.commit()
                print("\n✅ Migration completed successfully!")
                print("Default password for all users: password123")
                return True
                
            except Exception as e:
                trans.rollback()
                print(f"\n❌ Migration failed: {e}")
                return False
                
    except Exception as e:
        print(f"\n❌ Database connection failed: {e}")
        return False
    finally:
        engine.dispose()

if __name__ == "__main__":
    success = migrate()
    sys.exit(0 if success else 1)
