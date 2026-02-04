#!/bin/bash

# Installation script for authentication system
# Run this script to install all required dependencies

echo "=========================================="
echo "Installing Authentication Dependencies"
echo "=========================================="

# Navigate to project directory
cd "$(dirname "$0")"

echo ""
echo "Installing authentication packages..."
python3 -m pip install 'python-jose[cryptography]' 'passlib[bcrypt]' python-multipart

echo ""
echo "Verifying installation..."
python3 -c "import jose; import passlib; print('✅ Authentication packages installed successfully!')" 2>/dev/null || echo "⚠️  Please run: pip install 'python-jose[cryptography]' 'passlib[bcrypt]' python-multipart"

echo ""
echo "=========================================="
echo "Installation Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Start the server: cd backend && uvicorn main:app --reload"
echo "2. Test authentication: python tests/test_authentication.py"
echo "3. View API docs: http://localhost:8000/docs"
echo ""
