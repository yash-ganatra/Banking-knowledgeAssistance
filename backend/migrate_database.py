"""
Database migration script to add authentication fields to existing users table.
Run this script to update your existing database for the new authentication system.
"""

import os
import sys
from sqlalchemy import create_engine, text
from dotenv import load_dotenv

# Add backend to path
sys.path.insert(0, os.path.dirname(__file__))

load_dotenv()

# Database configuration
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://postgres:postgres@localhost:5432/banking_assistant"
)

def migrate_database():
    """Add authentication fields to existing users table"""
    engine = create_engine(DATABASE_URL)
    
    print("=" * 60)
    print("Database Migration for Authentication System")
    print("=" * 60)
    print(f"\nConnecting to: {DATABASE_URL}")
    
    try:
        with engine.connect() as conn:
            # Start transaction
            trans = conn.begin()
            
            try:
                # Check if hashed_password column exists
                result = conn.execute(text("""
                    SELECT column_name 
                    FROM information_schema.columns 
                    WHERE table_name='users' AND column_name='hashed_password'
                """))
                
                if result.fetchone():
                    print("\n✅ Column 'hashed_password' already exists. No migration needed.")
                else:
                    print("\n📝 Adding 'hashed_password' column...")
                    
                    # Add hashed_password column
                    conn.execute(text("""
                        ALTER TABLE users 
                        ADD COLUMN hashed_password VARCHAR(255) DEFAULT ''
                    """))
                    print("✅ Added 'hashed_password' column")
                    
                    # Update existing users with default hashed password
                    # Note: This is a temporary password. Users should reset their passwords.
                    from auth import get_password_hash
                    default_hashed_password = get_password_hash("changeMe123!")
                    
                    conn.execute(text("""
                        UPDATE users 
                        SET hashed_password = :hashed_password 
                        WHERE hashed_password = ''
                    """), {"hashed_password": default_hashed_password})
                    print("✅ Set default passwords for existing users")
                    
                    # Now make the column NOT NULL
                    conn.execute(text("""
                        ALTER TABLE users 
                        ALTER COLUMN hashed_password SET NOT NULL
                    """))
                    print("✅ Made 'hashed_password' column NOT NULL")
                
                # Check if email column is NOT NULL
                result = conn.execute(text("""
                    SELECT is_nullable 
                    FROM information_schema.columns 
                    WHERE table_name='users' AND column_name='email'
                """))
                
                row = result.fetchone()
                if row and row[0] == 'YES':
                    print("\n📝 Making 'email' column NOT NULL...")
                    
                    # First, update any NULL emails
                    result = conn.execute(text("""
                        UPDATE users 
                        SET email = CONCAT(username, '@default.local')
                        WHERE email IS NULL OR email = ''
                    """))
                    updated_count = result.rowcount
                    if updated_count > 0:
                        print(f"✅ Updated {updated_count} users with default email")
                    
                    # Make email NOT NULL
                    conn.execute(text("""
                        ALTER TABLE users 
                        ALTER COLUMN email SET NOT NULL
                    """))
                    print("✅ Made 'email' column NOT NULL")
                else:
                    print("\n✅ Email column is already NOT NULL")
                
                # Commit transaction
                trans.commit()
                
                print("\n" + "=" * 60)
                print("✅ Migration completed successfully!")
                print("=" * 60)
                print("\n⚠️  IMPORTANT: All existing users now have password 'changeMe123!'")
                print("   They should change their passwords immediately.\n")
                
                return True
                
            except Exception as e:
                trans.rollback()
                print(f"\n❌ Migration failed: {e}")
                print("Transaction rolled back.")
                return False
                
    except Exception as e:
        print(f"\n❌ Database connection failed: {e}")
        return False
    finally:
        engine.dispose()


if __name__ == "__main__":
    print("\n⚠️  WARNING: This will modify your database structure!")
    print("   Make sure you have a backup before proceeding.\n")
    
    response = input("Do you want to continue? (yes/no): ").strip().lower()
    
    if response in ['yes', 'y']:
        success = migrate_database()
        sys.exit(0 if success else 1)
    else:
        print("\n❌ Migration cancelled.")
        sys.exit(0)
