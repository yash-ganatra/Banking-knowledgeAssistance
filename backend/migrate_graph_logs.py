import os
import sys
from sqlalchemy import text

# Add the backend directory to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from database import engine

def migrate_graph_logs():
    """Add graph usage columns to inference_logs table"""
    print("Migrating inference_logs table...")
    
    with engine.connect() as conn:
        try:
            # Add graph_used column
            conn.execute(text("ALTER TABLE inference_logs ADD COLUMN IF NOT EXISTS graph_used BOOLEAN DEFAULT FALSE"))
            print("✅ Added 'graph_used' column")
            
            # Add graph_context column
            conn.execute(text("ALTER TABLE inference_logs ADD COLUMN IF NOT EXISTS graph_context JSON"))
            print("✅ Added 'graph_context' column")
            
            conn.commit()
            print("\nMigration complete successfully!")
            
        except Exception as e:
            print(f"❌ Migration failed: {e}")
            # If it failed because column exists, that's fine (though IF NOT EXISTS handles it usually in newer PG)
            # But let's print error just in case.

if __name__ == "__main__":
    migrate_graph_logs()
