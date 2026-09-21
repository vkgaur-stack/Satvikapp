#!/bin/bash

# Beneficiary & Donor Management System - Automated Deployment Script
# Usage: bash deploy.sh

set -e

echo "======================================"
echo "🚀 Deployment Script"
echo "Beneficiary & Donor Management System"
echo "======================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Check prerequisites
echo -e "${YELLOW}Step 1: Checking prerequisites...${NC}"

if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP not found. Please install PHP 8.2+${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP found$(php -v | head -n 1)${NC}"

if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer not found. Please install Composer${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer found${NC}"

if ! command -v mysql &> /dev/null; then
    echo -e "${YELLOW}⚠ MySQL client not found (will still work if MySQL is running)${NC}"
else
    echo -e "${GREEN}✓ MySQL found${NC}"
fi

echo ""

# Step 2: Install dependencies
echo -e "${YELLOW}Step 2: Installing dependencies with Composer...${NC}"
composer install --no-interaction --prefer-dist
echo -e "${GREEN}✓ Dependencies installed${NC}"

echo ""

# Step 3: Setup environment
echo -e "${YELLOW}Step 3: Setting up environment file...${NC}"

if [ -f .env ]; then
    echo -e "${YELLOW}⚠ .env already exists. Backing up to .env.backup${NC}"
    cp .env .env.backup
fi

cp .env.example .env
echo -e "${GREEN}✓ .env created from .env.example${NC}"

echo ""
echo -e "${YELLOW}Please edit .env with your database credentials:${NC}"
echo "  1. Open .env"
echo "  2. Set database.default.password = your_mysql_password"
echo "  3. Set app.baseURL = your_domain_or_ip"
echo ""

# Step 4: Database migration
echo -e "${YELLOW}Step 4: Running database migrations...${NC}"

php spark migrate
echo -e "${GREEN}✓ Database tables created${NC}"

echo ""

# Step 5: Seed default data
echo -e "${YELLOW}Step 5: Seeding default roles and users...${NC}"

php spark db:seed RoleSeeder
php spark db:seed UserSeeder
echo -e "${GREEN}✓ Default data seeded${NC}"

echo ""

# Step 6: Set permissions
echo -e "${YELLOW}Step 6: Setting file permissions...${NC}"

chmod -R 755 app/
chmod -R 755 public/
chmod -R 755 writable/

if command -v sudo &> /dev/null; then
    echo "Setting ownership (requires sudo)..."
    sudo chown -R www-data:www-data . 2>/dev/null || echo "⚠ Skipped ownership change (run: sudo chown -R www-data:www-data .)"
fi

echo -e "${GREEN}✓ Permissions set${NC}"

echo ""
echo "======================================"
echo -e "${GREEN}✅ Deployment Complete!${NC}"
echo "======================================"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Edit .env with your database credentials if not done yet"
echo "2. Restart your web server:"
echo "   Apache: sudo systemctl restart apache2"
echo "   Nginx:  sudo systemctl restart nginx"
echo ""
echo -e "${YELLOW}Access your app:${NC}"
echo "   http://localhost:8000 (development)"
echo "   or http://yourdomain.com (production)"
echo ""
echo -e "${YELLOW}Test Login:${NC}"
echo "   Email: admin@example.com"
echo "   Password: password123"
echo ""
echo "======================================"
