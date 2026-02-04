#!/bin/bash

# Quick Fix Script for Authentication Setup
# This script will install dependencies and migrate the database

echo "=========================================="
echo "Authentication System Quick Fix"
echo "=========================================="

cd "$(dirname "$0")"

echo ""
echo "Step 1: Installing missing dependencies..."
echo "----------------------------------------"
source venv/bin/activate
pip install email-validator 'python-jose[cryptography]' 'passlib[bcrypt]' python-multipart

echo ""
echo "Step 2: Running database migration..."
echo "----------------------------------------"
cd backend
python3 migrate_database.py << EOF
yes
EOF

echo ""
echo "=========================================="
echo "✅ Setup Complete!"
echo "=========================================="
echo ""
echo "You can now start the server with:"
echo "  cd backend && uvicorn main:app --reload"
echo ""
