-- Database Migration Script for Authentication
-- Adds hashed_password column to users table
-- Run this with: psql -U postgres -d banking_assistant -f add_password_column.sql

-- Add hashed_password column if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name='users' AND column_name='hashed_password'
    ) THEN
        ALTER TABLE users ADD COLUMN hashed_password VARCHAR(255) DEFAULT '';
        RAISE NOTICE 'Added hashed_password column';
    ELSE
        RAISE NOTICE 'hashed_password column already exists';
    END IF;
END $$;

-- Update existing users with a default hashed password
-- This is the bcrypt hash for "password123"
UPDATE users 
SET hashed_password = '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyMpghEU.H9S'
WHERE hashed_password = '' OR hashed_password IS NULL;

-- Make hashed_password NOT NULL
ALTER TABLE users ALTER COLUMN hashed_password SET NOT NULL;

-- Make email NOT NULL if it isn't already
DO $$ 
BEGIN
    -- Update any NULL emails first
    UPDATE users 
    SET email = CONCAT(username, '@default.local')
    WHERE email IS NULL OR email = '';
    
    -- Now make it NOT NULL
    ALTER TABLE users ALTER COLUMN email SET NOT NULL;
    
    RAISE NOTICE 'Email column is now NOT NULL';
END $$;

-- Verify the changes
SELECT 'Migration completed successfully!' AS status;
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'users' 
AND column_name IN ('hashed_password', 'email')
ORDER BY column_name;
