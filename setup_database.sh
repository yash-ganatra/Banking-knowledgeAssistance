#!/bin/bash

# Chat History Setup Script
# This script helps set up the database for the Banking Knowledge Assistant

echo "========================================"
echo "Banking Assistant - Database Setup"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if PostgreSQL is installed
echo "Checking PostgreSQL installation..."
if command -v psql &> /dev/null; then
    echo -e "${GREEN}✓ PostgreSQL is installed${NC}"
    psql --version
else
    echo -e "${RED}✗ PostgreSQL is not installed${NC}"
    echo ""
    echo "Please install PostgreSQL first:"
    echo "  macOS:   brew install postgresql@15"
    echo "  Ubuntu:  sudo apt-get install postgresql postgresql-contrib"
    exit 1
fi

echo ""

# Check if PostgreSQL is running
echo "Checking if PostgreSQL is running..."
if pg_isready &> /dev/null; then
    echo -e "${GREEN}✓ PostgreSQL is running${NC}"
else
    echo -e "${YELLOW}⚠ PostgreSQL is not running${NC}"
    echo "Starting PostgreSQL..."
    
    # Try to start PostgreSQL based on OS
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew services start postgresql@15
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo systemctl start postgresql
    fi
    
    sleep 2
    
    if pg_isready &> /dev/null; then
        echo -e "${GREEN}✓ PostgreSQL started successfully${NC}"
    else
        echo -e "${RED}✗ Could not start PostgreSQL automatically${NC}"
        echo "Please start PostgreSQL manually and run this script again"
        exit 1
    fi
fi

echo ""

# Database name
DB_NAME="banking_assistant"
DB_USER="postgres"

# Check if database exists
echo "Checking if database exists..."
if psql -U $DB_USER -lqt | cut -d \| -f 1 | grep -qw $DB_NAME; then
    echo -e "${YELLOW}⚠ Database '$DB_NAME' already exists${NC}"
    read -p "Do you want to drop and recreate it? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Dropping existing database..."
        dropdb -U $DB_USER $DB_NAME
        echo "Creating new database..."
        createdb -U $DB_USER $DB_NAME
        echo -e "${GREEN}✓ Database recreated${NC}"
    else
        echo "Using existing database"
    fi
else
    echo "Creating database '$DB_NAME'..."
    createdb -U $DB_USER $DB_NAME
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Database created successfully${NC}"
    else
        echo -e "${RED}✗ Failed to create database${NC}"
        exit 1
    fi
fi

echo ""

# Check for .env file
echo "Checking environment configuration..."
if [ -f ".env" ]; then
    echo -e "${GREEN}✓ .env file found${NC}"
else
    echo -e "${YELLOW}⚠ .env file not found${NC}"
    echo "Creating .env from template..."
    
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ Created .env file${NC}"
        echo -e "${YELLOW}⚠ Please edit .env and add your GROQ_API_KEY${NC}"
    else
        echo "Creating basic .env file..."
        cat > .env << EOF
DATABASE_URL=postgresql://postgres:postgres@localhost:5432/banking_assistant
GROQ_API_KEY=your_groq_api_key_here
EOF
        echo -e "${GREEN}✓ Created .env file${NC}"
        echo -e "${YELLOW}⚠ Please edit .env and add your GROQ_API_KEY${NC}"
    fi
fi

echo ""

# Check Python dependencies
echo "Checking Python dependencies..."
if python3 -c "import sqlalchemy" 2>/dev/null; then
    echo -e "${GREEN}✓ SQLAlchemy is installed${NC}"
else
    echo -e "${YELLOW}⚠ SQLAlchemy not found${NC}"
    read -p "Install missing dependencies? (Y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        echo "Installing dependencies..."
        pip install sqlalchemy psycopg2-binary
        echo -e "${GREEN}✓ Dependencies installed${NC}"
    fi
fi

echo ""
echo "========================================"
echo "Setup Complete!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Edit .env file and add your GROQ_API_KEY"
echo "2. Start the backend: cd backend && python main.py"
echo "3. Start the frontend: cd frontend && npm run dev"
echo ""
echo "Database tables will be created automatically on first run."
echo ""
echo "For more information, see:"
echo "  - CHAT_HISTORY_GUIDE.md (quick start)"
echo "  - DATABASE_SETUP.md (detailed documentation)"
echo ""
