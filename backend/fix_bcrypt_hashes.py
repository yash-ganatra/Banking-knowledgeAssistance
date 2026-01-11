"""
Fix password hashes in database to use new bcrypt format
"""
import bcrypt
from sqlalchemy import create_engine, text
from database import DATABASE_URL

def fix_password_hashes():
    """Update password hashes to new bcrypt format"""
    engine = create_engine(DATABASE_URL)
    
    # Generate new hash for password123
    password = "password123"
    salt = bcrypt.gensalt()
    new_hash = bcrypt.hashpw(password.encode('utf-8'), salt).decode('utf-8')
    
    print(f"New hash generated: {new_hash[:30]}...")
    
    with engine.connect() as conn:
        # Update all users with the new hash
        result = conn.execute(
            text("UPDATE users SET hashed_password = :hash"),
            {"hash": new_hash}
        )
        conn.commit()
        print(f"✅ Updated {result.rowcount} users with new password hash")
        print("Password for all users: password123")

if __name__ == "__main__":
    fix_password_hashes()
