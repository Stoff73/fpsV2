#!/bin/bash

###############################################################################
# FPS Production Deployment Script
# Version: 1.0
# For: SiteGround deployment (c.jones.co/fpsv2)
###############################################################################

set -e  # Exit on error

echo "========================================="
echo "FPS v0.1.2.12 - Production Build Script"
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo "→ $1"
}

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_error "Error: artisan file not found. Please run this script from the Laravel root directory."
    exit 1
fi

print_success "Found Laravel installation"
echo ""

# Step 1: Clean previous builds
print_info "Step 1: Cleaning previous builds..."
rm -rf public/build/
rm -rf public/hot
print_success "Cleaned previous builds"
echo ""

# Step 2: Install Composer dependencies (production)
print_info "Step 2: Installing Composer dependencies (production only)..."
composer install --optimize-autoloader --no-dev
print_success "Composer dependencies installed"
echo ""

# Step 3: Build frontend assets
print_info "Step 3: Building frontend assets with Vite..."
NODE_ENV=production npm run build
if [ -d "public/build" ]; then
    print_success "Frontend assets built successfully"
else
    print_error "Build failed - public/build directory not created"
    exit 1
fi
echo ""

# Step 4: Clear Laravel caches
print_info "Step 4: Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"
echo ""

# Step 5: Optimize Laravel
print_info "Step 5: Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Laravel optimized"
echo ""

# Step 6: Check .env.production.example exists
print_info "Step 6: Checking environment template..."
if [ -f ".env.production.example" ]; then
    print_success ".env.production.example found"
else
    print_warning ".env.production.example not found - you'll need to configure .env manually"
fi
echo ""

# Step 7: Create deployment package info
print_info "Step 7: Creating deployment summary..."
BUILD_DATE=$(date '+%Y-%m-%d %H:%M:%S')
cat > deployment-info.txt << EOF
FPS Production Build
====================
Version: v0.1.2.12
Build Date: ${BUILD_DATE}
Target: SiteGround (c.jones.co/fpsv2)

Build Status:
✓ Composer dependencies installed (production)
✓ Frontend assets built with Vite
✓ Laravel caches cleared
✓ Laravel configuration cached

Next Steps:
1. Upload all files to SiteGround via FTP/SFTP
2. Create MySQL database on SiteGround
3. Copy .env.production.example to .env and configure
4. Run: php artisan migrate --force
5. Run: php artisan db:seed --class=TaxConfigurationSeeder --force
6. Create admin user via tinker
7. Set up cron jobs for queue worker
8. Test application

See DEPLOYMENT_GUIDE.md for detailed instructions.
EOF
print_success "Deployment info created: deployment-info.txt"
echo ""

# Step 8: Show file sizes
print_info "Step 8: Build statistics..."
if command -v du &> /dev/null; then
    PUBLIC_SIZE=$(du -sh public/build/ 2>/dev/null | cut -f1)
    VENDOR_SIZE=$(du -sh vendor/ 2>/dev/null | cut -f1)
    echo "  • Frontend assets (public/build/): ${PUBLIC_SIZE}"
    echo "  • Backend dependencies (vendor/): ${VENDOR_SIZE}"
fi
echo ""

# Step 9: Reinstall dev dependencies for local development
print_warning "Reinstalling dev dependencies for local development..."
composer install
print_success "Dev dependencies restored"
echo ""

# Final summary
echo "========================================="
echo "Build Complete!"
echo "========================================="
echo ""
print_success "Production build ready for deployment"
echo ""
echo "Files to upload to SiteGround:"
echo "  • app/"
echo "  • bootstrap/"
echo "  • config/"
echo "  • database/"
echo "  • public/ (including public/build/)"
echo "  • resources/"
echo "  • routes/"
echo "  • storage/"
echo "  • vendor/"
echo "  • .env (copy from .env.production.example and configure)"
echo "  • artisan"
echo "  • composer.json"
echo "  • composer.lock"
echo "  • index.php"
echo ""
echo "DO NOT upload:"
echo "  ✗ node_modules/"
echo "  ✗ .git/"
echo "  ✗ tests/"
echo "  ✗ package.json / package-lock.json"
echo "  ✗ vite.config.js"
echo ""
print_info "Read DEPLOYMENT_GUIDE.md for complete deployment instructions"
print_info "Read DATABASE_MIGRATION_CHECKLIST.md for database setup"
echo ""
print_success "Ready to deploy to: https://c.jones.co/fpsv2"
echo ""
