# TenGo v0.2.7 - SiteGround Deployment Guide

**Target Environment**: SiteGround Shared/Cloud Hosting
**Production URL**: https://csjones.co/tengo
**Deployment Type**: Subdirectory (/tengo)
**Tech Stack**: Laravel 10.x + Vue.js 3 + Vite + MySQL 8.0+ + Memcached

---

## Table of Contents

1. [Pre-Deployment Preparation](#1-pre-deployment-preparation)
2. [SiteGround Account Setup](#2-siteground-account-setup)
3. [Database Setup](#3-database-setup)
4. [File Upload & Structure](#4-file-upload--structure)
5. [Configuration](#5-configuration)
6. [Build & Optimization](#6-build--optimization)
7. [Post-Deployment Verification](#7-post-deployment-verification)
8. [Troubleshooting](#8-troubleshooting)
9. [Rollback Procedures](#9-rollback-procedures)
10. [Maintenance](#10-maintenance)

---

## 1. Pre-Deployment Preparation

### 1.1 Local Environment Verification

**Critical**: Complete these steps BEFORE any deployment actions.

```bash
# Verify git status - should be clean or only approved changes
git status

# Verify you're on the main branch
git branch --show-current

# Verify all changes are committed
git diff --stat

# Check PHP version compatibility (requires 8.1+)
php -v

# Verify composer.json and package.json
cat composer.json | grep '"php"'
```

### 1.2 Production Build Test

**Test the build locally to catch any issues BEFORE deployment:**

```bash
# Clean previous builds
rm -rf public/build/*

# Run production build
NODE_ENV=production npm run build

# Verify build succeeded
ls -lh public/build/manifest.json
ls public/build/assets/ | wc -l

# Expected: manifest.json exists, 100+ asset files in public/build/assets/
```

**Build Success Indicators**:
- ✅ Build completes in ~15-20 seconds
- ✅ `public/build/manifest.json` created
- ✅ `public/build/assets/` contains ~100+ JS/CSS files
- ✅ Warning about chunk size is NORMAL (large InvestmentDashboard component)

### 1.3 Database Migration Review

**Review all pending migrations to ensure they're safe for production:**

```bash
# List migration status
php artisan migrate:status

# Review migration files for destructive operations
ls -la database/migrations/
```

**Red Flags** (STOP deployment if found):
- ❌ `dropColumn()`, `dropTable()` - Data loss risk
- ❌ `migrate:fresh` or `migrate:refresh` - Will wipe database
- ❌ Migrations without `down()` method - Cannot rollback

**Safe Operations**:
- ✅ `create_table` - New tables
- ✅ `addColumn()` with `->nullable()` - Non-breaking additions
- ✅ Enum additions with default values

### 1.4 Create Deployment Package

**Create a clean, secure deployment archive:**

⚠️ **CRITICAL**: Never include credential files (`.env.production`, `.env.development`) in deployment packages!

#### Step 1: Remove Any Existing Archive

```bash
# Remove old deployment archive (prevents "can't add to itself" error)
rm -f tengo-v0.2.7-deployment.tar.gz

# Verify no archives exist
ls -lh *.tar.gz 2>/dev/null || echo "No tar.gz files found - good!"
```

#### Step 2: Create Deployment Archive

```bash
# From project root
cd /Users/Chris/Desktop/fpsApp/tengo

# Create secure deployment package
# COPYFILE_DISABLE=1 prevents macOS extended attribute files (._* files) from being included
COPYFILE_DISABLE=1 tar -czf tengo-v0.2.7-deployment.tar.gz \
  --exclude='tengo-v0.2.7-deployment.tar.gz' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='.env' \
  --exclude='.env.local' \
  --exclude='.env.production' \
  --exclude='.env.development' \
  --exclude='public/hot' \
  --exclude='.claude' \
  --exclude='*.old' \
  --exclude='*.log' \
  --exclude='.DS_Store' \
  --exclude='public/.htaccess.laravel-default' \
  .
```

**Exclusions Explained**:
- `node_modules/`, `vendor/` - Will be installed on server
- `.git/` - Version control (not needed in production)
- `storage/logs/*`, `storage/framework/*` - Server-generated files
- `.env*` files **except** `.env.example` and `.env.production.example` - **NEVER deploy credentials!**
- `public/hot` - Vite dev server file (dev only)
- `.claude/`, `*.old` - Development artifacts
- `public/.htaccess.laravel-default` - Backup file (not needed)

#### Step 3: Verify Archive is Secure

```bash
# Check archive size (should be ~2-3 MB)
ls -lh tengo-v0.2.7-deployment.tar.gz

# CRITICAL: Verify only .example files are included (NO credentials!)
tar -tzf tengo-v0.2.7-deployment.tar.gz | grep "\.env"

# Expected output (ONLY these files):
# ./.env.example
# ./.env.production.example
```

**✅ Security Check**: If you see `.env.production` or `.env.development` in the output, **STOP!** Recreate the archive - those files contain credentials!

#### Step 4: Verify Archive Contents

```bash
# List first 20 files to verify structure
tar -tzf tengo-v0.2.7-deployment.tar.gz | head -20

# Verify production .htaccess is included
tar -tzf tengo-v0.2.7-deployment.tar.gz | grep "public/.htaccess"
# Should show: ./public/.htaccess (production version with RewriteBase /tengo/)
```

**Expected Results**:
- ✅ Archive size: ~2-3 MB
- ✅ Only `.env.example` and `.env.production.example` present
- ✅ Production `.htaccess` included (with RewriteBase /tengo/)
- ✅ Built assets in `public/build/` directory

---

## 2. SiteGround Account Setup

### 2.1 Access SiteGround Site Tools

1. Log in to SiteGround: https://my.siteground.com
2. Navigate to **Site Tools** for csjones.co
3. You'll need access to:
   - **File Manager** (or FTP/SSH)
   - **MySQL** (Database management)
   - **PHP Manager** (Version settings)
   - **Cron Jobs** (Queue worker - optional)

### 2.2 PHP Version Configuration

**TenGo requires PHP 8.1 or higher**

1. Go to **Site Tools > Devs > PHP Manager**
2. Select your domain: **csjones.co**
3. Change PHP version to **8.2** or **8.3** (recommended)
4. Click **Confirm**

**Required PHP Extensions** (usually enabled by default):
- ✅ mbstring
- ✅ xml
- ✅ curl
- ✅ zip
- ✅ pdo_mysql
- ✅ tokenizer
- ✅ json
- ✅ bcmath
- ✅ ctype
- ✅ fileinfo

### 2.3 Directory Structure Planning

**SiteGround Standard Directory Structure:**

```
/home/customer/www/csjones.co/
├── public_html/              # Web-accessible root (DO NOT put Laravel here)
│   ├── (existing site files)
│   └── tengo/                # This will be a symlink to Laravel's public folder
└── tengo-app/                # Laravel application root (OUTSIDE public_html)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/               # Laravel's public folder (symlink target)
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── ...
```

**Why This Structure?**
- **Security**: Application code outside web root prevents direct access
- **Standard Practice**: Laravel best practice
- **SiteGround Compatible**: Works with their hosting environment

---

## 3. Database Setup

### 3.1 Create MySQL Database

1. Go to **Site Tools > MySQL > Databases**
2. Click **Create Database**
3. **Database Name**: `tengo_production` (or similar)
4. **Collation**: `utf8mb4_unicode_ci`
5. Click **Create**

**Note**: SiteGround will prefix your database name with your username (e.g., `cgsjones_tengo_production`)

### 3.2 Create Database User

1. Go to **Site Tools > MySQL > Users**
2. Click **Create User**
3. **Username**: `tengo_user`
4. **Password**: Generate strong password (save this securely!)
5. **Access Hosts**: Select `Any Host` (or `localhost` if available)
6. Click **Create**

**Note**: Like databases, SiteGround prefixes usernames (e.g., `cgsjones_tengo_user`)

### 3.3 Grant User Permissions

1. Go to **Site Tools > MySQL > Databases**
2. Find your `tengo_production` database
3. Click **Manage Users**
4. Select your `tengo_user`
5. Grant **ALL PRIVILEGES**
6. Click **Save**

### 3.4 Note Database Credentials

**Save these for .env configuration:**

```
DB_HOST=localhost (usually)
DB_PORT=3306
DB_DATABASE=cgsjones_tengo_production (with prefix)
DB_USERNAME=cgsjones_tengo_user (with prefix)
DB_PASSWORD=[your generated password]
```

---

## 4. File Upload & Structure

### 4.1 CRITICAL: .htaccess Configuration Audit Results

**⚠️ SECURITY & FUNCTIONALITY CRITICAL - READ THIS SECTION COMPLETELY**

A comprehensive .htaccess audit has identified critical issues that **MUST** be addressed to prevent 403 Forbidden errors and security vulnerabilities.

#### Root .htaccess File - DO NOT DEPLOY

**Location**: `/Users/Chris/Desktop/fpsApp/tengo/.htaccess` (root directory)

**Status**: 🔴 **CRITICAL - DELETE BEFORE DEPLOYMENT**

**Issues**:
1. This file should **NOT exist** in production on SiteGround
2. Laravel applications should **ONLY** have .htaccess in the `public/` directory
3. Contains incorrect storage blocking pattern (relative path instead of absolute)
4. Will cause routing conflicts and unexpected 403 errors

**Action Required**:
```bash
# BEFORE creating deployment package, verify this file is excluded:
# The deployment package creation command already excludes this, but verify:
cd /Users/Chris/Desktop/fpsApp/tengo
ls -la .htaccess  # This file should NOT be in the deployment package
```

**On SiteGround** (if accidentally deployed):
```bash
# Remove root .htaccess immediately
cd ~/tengo-app
rm .htaccess  # Delete if it exists
```

#### Public Directory .htaccess - INCOMPLETE

**Location**: `public/.htaccess`

**Status**: ⚠️ **WARNING - REPLACE WITH PRODUCTION VERSION**

**Missing Critical Features**:
1. ❌ Missing `RewriteBase /tengo/` (will cause **ALL routes to fail** in subdirectory)
2. ❌ Missing security headers
3. ❌ Missing sensitive file protection (.env, composer.json)
4. ❌ Missing storage directory blocking

**Impact if Not Fixed**:
- All API routes return 404
- Frontend routing breaks completely
- Storage files potentially accessible
- Sensitive configuration files exposed

#### Production .htaccess - RECOMMENDED

**Location**: `.htaccess.production`

**Status**: ✅ **PRODUCTION READY - USE THIS FILE**

**Features**:
- ✅ Contains `RewriteBase /tengo/` for subdirectory routing
- ✅ Blocks `/tengo/storage/` with correct absolute path
- ✅ Protects sensitive files (.env, .git, composer files)
- ✅ Security headers configured
- ✅ Compression and caching enabled
- ✅ 266 lines of production-hardened configuration

**Deployment Instructions**:
```bash
# On SiteGround via SSH
cd ~/tengo-app

# Deploy production .htaccess (REQUIRED)
cp .htaccess.production public/.htaccess

# Clean up - remove production template
rm .htaccess.production

# Remove root .htaccess if it exists (CRITICAL)
rm .htaccess 2>/dev/null || true

# Verify correct .htaccess is in place
cat public/.htaccess | grep "RewriteBase"
# Should output: RewriteBase /tengo/

# Set correct permissions
chmod 644 public/.htaccess
```

**Verification Checklist**:
- [ ] Root `.htaccess` does NOT exist in `~/tengo-app/`
- [ ] Production `.htaccess` copied to `~/tengo-app/public/.htaccess`
- [ ] File contains `RewriteBase /tengo/`
- [ ] File permissions set to 644
- [ ] `.htaccess.production` template removed from root

---

### 4.2 CRITICAL: SiteGround Directory Structure

**⚠️ IMPORTANT**: SiteGround uses a different directory structure than standard hosting!

#### SiteGround Actual Directory Structure

**Home Directory**: `/home/u163-ptanegf9edny/` (your actual username will differ)

**Full Path Structure**:
```
/home/u163-ptanegf9edny/
└── www/
    └── csjones.co/
        ├── tengo-app/                    # ← Application root (create here)
        │   ├── app/
        │   ├── config/
        │   ├── database/
        │   ├── public/                   # ← Laravel's public directory
        │   │   ├── index.php
        │   │   ├── .htaccess
        │   │   └── build/
        │   ├── storage/
        │   └── ...
        └── public_html/                  # ← Web-accessible directory
            ├── tengo -> ../tengo-app/public  # ← Symlink (relative path)
            └── other-sites/
```

**Path Aliases (SSH shortcuts)**:
- `~` = `/home/u163-ptanegf9edny/`
- `~/www/csjones.co/` = Full path to your domain directory
- `~/www/csjones.co/public_html/` = Web-accessible directory
- `~/www/csjones.co/tengo-app/` = Application root (where you'll extract)

#### Key Differences from Standard Hosting

**Standard Hosting** (documented in many guides):
```
~/
├── public_html/
└── tengo-app/
```

**SiteGround** (actual structure):
```
~/
└── www/
    └── csjones.co/
        ├── public_html/
        └── tengo-app/
```

**Why This Matters**:
- ✅ All paths in this guide must include `~/www/csjones.co/`
- ✅ Symlinks must be relative (`../tengo-app/public`) not absolute
- ✅ File uploads go to `~/www/csjones.co/public_html/`
- ✅ Application extracts to `~/www/csjones.co/tengo-app/`

#### Verification Commands

**Before proceeding, verify your directory structure**:

```bash
# Check your home directory
pwd
# Should show something like: /home/u163-ptanegf9edny/www/csjones.co

# List domain directory
ls -la ~/www/csjones.co/
# Should show: public_html/ and any other directories

# Check if tengo-app already exists
ls -la ~/www/csjones.co/ | grep tengo
# If tengo-app exists, it's from a previous deployment

# Check public_html contents
ls -la ~/www/csjones.co/public_html/ | grep tengo
# May show existing symlink or deployment package
```

---

### 4.3 Upload Deployment Package

**Option A: Upload via File Manager** (Easiest)

1. Go to **Site Tools > File Manager**
2. Navigate to `~/www/csjones.co/public_html/`
3. Click **Upload** and select your `tengo-v0.2.7-deployment.tar.gz`
4. Wait for upload to complete (2.2 MB should take ~10-30 seconds)
5. **Do NOT extract here** - we'll extract via SSH for proper structure

**Option B: Upload via SFTP** (Recommended for reliability)

```bash
# Get SFTP credentials from Site Tools > Devs > SSH Keys Manager
# Use an SFTP client (FileZilla, Cyberduck, Transmit, etc.)

Host: csjones.co
Username: u163-ptanegf9edny (your actual username)
Password: [your SSH password or use SSH key]
Port: 18765 (SiteGround SSH port)

# Upload to: /www/csjones.co/public_html/
# File: tengo-v0.2.7-deployment.tar.gz
```

**Upload Verification**:
```bash
# Via SSH, verify upload
ssh [username]@csjones.co -p18765
cd ~/www/csjones.co/public_html
ls -lh tengo-v0.2.7-deployment.tar.gz
# Should show: 2.2M file size
```

---

### 4.4 Extract Deployment Package & Create Structure

**⚠️ CRITICAL**: Extract to correct location for security and proper structure!

**Step 1: Navigate to Domain Directory**

```bash
# Connect via SSH
ssh [username]@csjones.co -p18765

# Navigate to domain directory (NOT public_html)
cd ~/www/csjones.co

# Verify location
pwd
# Should show: /home/u163-ptanegf9edny/www/csjones.co
```

**Step 2: Create Application Directory**

```bash
# Create tengo-app directory
mkdir -p tengo-app

# Navigate into it
cd tengo-app

# Extract deployment package here
tar -xzf ../public_html/tengo-v0.2.7-deployment.tar.gz

# Verify extraction
ls -la | head -20
# Should show: app/, bootstrap/, config/, database/, public/, etc.
```

**Step 3: Deploy Production .htaccess**

```bash
# Still in ~/www/csjones.co/tengo-app/

# Deploy production .htaccess (CRITICAL!)
cp .htaccess.production public/.htaccess

# Remove root .htaccess if exists
rm .htaccess 2>/dev/null || true

# Clean up production template
rm .htaccess.production

# Verify RewriteBase is set
cat public/.htaccess | grep "RewriteBase"
# Should output: RewriteBase /tengo/

# Set correct permissions
chmod 644 public/.htaccess
```

---

### 4.5 Create Symlink for Public Directory

**⚠️ CRITICAL**: Use relative path for symlink (not absolute)!

**SSH Method** (REQUIRED):

```bash
# Navigate to public_html
cd ~/www/csjones.co/public_html

# Remove old broken symlink if exists
rm tengo 2>/dev/null || true

# Create symlink using RELATIVE path
ln -s ../tengo-app/public tengo

# Verify symlink created correctly
ls -la | grep tengo
# Should show: tengo -> ../tengo-app/public

# Test symlink works
ls tengo/
# Should show Laravel's public directory: index.php, .htaccess, build/, robots.txt
```

**Why Relative Path (`../tengo-app/public`)?**
- ✅ Works regardless of absolute path changes
- ✅ More portable and maintainable
- ✅ Standard practice for symlinks

**Verification**:
```bash
# Check symlink target exists
readlink tengo
# Output: ../tengo-app/public

# Verify target is accessible
ls -la tengo/index.php
# Should show Laravel's entry point file
```

**If SSH is not available:**
- Contact SiteGround support to enable SSH access
- OR manually copy contents of `tengo-app/public/` to `public_html/tengo/` (less ideal, requires copying on every update)

---

### 4.6 File & Directory Permissions

**⚠️ CRITICAL: Incorrect permissions are a common cause of 403 Forbidden and 500 Internal Server errors**

**SiteGround Permission Requirements** (based on security audit):

#### Standard File Permissions (644)

All regular files should be readable by the web server but not writable:

```bash
# Connect via SSH
ssh [username]@csjones.co -p18765
cd ~/www/csjones.co/tengo-app

# Set all files to 644 (rw-r--r--)
find . -type f -exec chmod 644 {} \;
```

**Files affected**:
- All `.php` files
- All `.htaccess` files (including `public/.htaccess`)
- All `.json` files (composer.json, package.json, etc.)
- All `.env` files
- All configuration files

#### Standard Directory Permissions (755)

All directories should be readable and executable:

```bash
# Set all directories to 755 (rwxr-xr-x)
find . -type d -exec chmod 755 {} \;
```

**Directories affected**:
- `app/`, `bootstrap/`, `config/`, `database/`
- `public/`, `resources/`, `routes/`
- `vendor/` (after composer install)

#### Writable Directories (775)

Laravel requires write access to specific directories for logs, cache, and sessions:

```bash
# Set writable directories to 775 (rwxrwxr-x)
chmod 775 storage bootstrap/cache

# Set all subdirectories within storage
chmod -R 775 storage/app storage/framework storage/logs
chmod 775 storage/framework/cache storage/framework/sessions storage/framework/views
```

**Critical writable directories**:
- `storage/` and all subdirectories
- `storage/app/`
- `storage/framework/`
- `storage/logs/`
- `storage/framework/cache/`
- `storage/framework/sessions/`
- `storage/framework/views/`
- `bootstrap/cache/`

#### Complete Permission Setup Script

**Recommended: Run this complete script after every deployment**

```bash
#!/bin/bash
# TenGo - Production Permission Setup

cd ~/www/csjones.co/tengo-app

echo "Setting standard file permissions (644)..."
find . -type f -exec chmod 644 {} \;

echo "Setting standard directory permissions (755)..."
find . -type d -exec chmod 755 {} \;

echo "Setting writable directories (775)..."
chmod 775 storage bootstrap/cache
chmod -R 775 storage/app storage/framework storage/logs
chmod 775 storage/framework/cache storage/framework/sessions storage/framework/views

echo "Verifying critical permissions..."
ls -la public/.htaccess     # Should be: -rw-r--r-- (644)
ls -la public/index.php     # Should be: -rw-r--r-- (644)
ls -ld storage/             # Should be: drwxrwxr-x (775)
ls -ld bootstrap/cache/     # Should be: drwxrwxr-x (775)

echo "Permissions set successfully!"
```

**Save this as `set-permissions.sh` and run after deployment:**

```bash
chmod +x set-permissions.sh
./set-permissions.sh
```

#### Permission Verification

**Verify permissions are correct:**

```bash
# Check critical files
ls -la ~/tengo-app/public/.htaccess      # Must be readable: 644
ls -la ~/tengo-app/public/index.php      # Must be readable: 644
ls -la ~/tengo-app/.env                  # Must be readable: 644

# Check writable directories
ls -ld ~/tengo-app/storage/              # Must be writable: 775
ls -ld ~/tengo-app/bootstrap/cache/      # Must be writable: 775
ls -ld ~/tengo-app/storage/logs/         # Must be writable: 775

# Test Laravel can write to storage
touch ~/tengo-app/storage/logs/test.log
# If this fails, permissions are incorrect
rm ~/tengo-app/storage/logs/test.log
```

#### Common Permission Issues

**403 Forbidden Errors**:
- **Cause**: Files are not readable (permissions too restrictive)
- **Solution**: Ensure files are 644, directories are 755
- **Check**: `chmod 644 public/.htaccess` and `chmod 644 public/index.php`

**500 Internal Server Errors**:
- **Cause**: Laravel cannot write to storage or cache
- **Solution**: Ensure storage directories are 775
- **Check**: `chmod -R 775 storage bootstrap/cache`

**Assets Not Loading**:
- **Cause**: Files in `public/build/` not readable
- **Solution**: `chmod -R 644 public/build/*`

**Logs Not Being Written**:
- **Cause**: `storage/logs/` not writable
- **Solution**: `chmod -R 775 storage/logs/`

---

## 5. Configuration

### 5.1 Create Production .env File

**Via File Manager or SSH:**

```bash
# SSH Method
ssh [username]@csjones.co -p18765
cd ~/tengo-app

# Copy example file
cp .env.example .env

# Edit with nano or vim
nano .env
```

**Production .env Configuration:**

```env
# Application
APP_NAME="TenGo - Financial Planning System"
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_SEE_BELOW
APP_DEBUG=false
APP_URL=https://csjones.co/tengo
APP_TIMEZONE=Europe/London

# Database (use your SiteGround credentials)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cgsjones_tengo_production
DB_USERNAME=cgsjones_tengo_user
DB_PASSWORD=[your_secure_password]

# Cache (Memcached if available, otherwise file)
CACHE_DRIVER=memcached
CACHE_PREFIX=tengo_

# Memcached (SiteGround usually provides this)
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.csjones.co
SESSION_SECURE_COOKIE=true

# Queue (use database for simplicity)
QUEUE_CONNECTION=database

# Mail (configure SMTP for production)
MAIL_MAILER=smtp
MAIL_HOST=[your_smtp_host]
MAIL_PORT=587
MAIL_USERNAME=[your_smtp_username]
MAIL_PASSWORD=[your_smtp_password]
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@csjones.co"
MAIL_FROM_NAME="${APP_NAME}"

# Vite (CRITICAL for subdirectory deployment)
VITE_APP_NAME="${APP_NAME}"
VITE_API_BASE_URL=https://csjones.co/tengo
```

**Generate APP_KEY:**

```bash
# Via SSH
cd ~/tengo-app
php artisan key:generate

# This will automatically update your .env file with a secure key
```

**Verify .env Security:**

```bash
# .env should NOT be web-accessible
# Verify it's in application root (~/tengo-app/), NOT in public folder
ls -la ~/tengo-app/.env           # Should exist
ls -la ~/tengo-app/public/.env    # Should NOT exist
```

### 5.2 Memcached Configuration

**Check if Memcached is available:**

```bash
# Via SSH
php -m | grep memcached

# If it returns "memcached", you're good!
```

**If Memcached is NOT available:**
- Contact SiteGround support to enable it
- OR change `.env` to use file-based caching:

```env
CACHE_DRIVER=file
```

---

## 6. Build & Optimization

### 6.1 Install Composer Dependencies

**Via SSH (REQUIRED):**

```bash
# Connect via SSH
ssh [username]@csjones.co -p18765

# Navigate to application
cd ~/tengo-app

# Install production dependencies (NO dev dependencies)
composer install --no-dev --optimize-autoloader

# Verify vendor directory created
ls -la vendor/

# This will take 2-3 minutes
```

**Expected Output:**
- ✅ ~50-100 packages installed
- ✅ `vendor/` directory created with Laravel framework
- ✅ Autoloader optimized

**If Composer Not Found:**

```bash
# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Move to user bin
mv composer.phar ~/composer

# Use with:
php ~/composer install --no-dev --optimize-autoloader
```

### 6.2 Upload Pre-Built Assets

**IMPORTANT**: Do NOT build assets on SiteGround server (limited resources).

**Build locally, then upload:**

```bash
# On your LOCAL machine
cd /Users/Chris/Desktop/fpsApp/tengo

# Build production assets
NODE_ENV=production npm run build

# Create archive of built assets
cd public
tar -czf build-assets.tar.gz build/

# Upload build-assets.tar.gz via FTP to ~/tengo-app/public/
```

**On SiteGround server:**

```bash
# Via SSH
cd ~/tengo-app/public

# Extract assets (if uploaded as archive)
tar -xzf build-assets.tar.gz

# Verify assets exist
ls -la build/manifest.json
ls build/assets/ | wc -l

# Should show manifest.json and 100+ files
```

### 6.3 Run Database Migrations

**Via SSH:**

```bash
cd ~/tengo-app

# Run migrations (--force required for production)
php artisan migrate --force

# Expected output: All migrations should run successfully
# First migration creates users, tax_configurations, protection tables, etc.
```

**If Migrations Fail:**
- Check database credentials in `.env`
- Test database connection: `php artisan db:show`
- Review error messages carefully

### 6.4 Seed Critical Data

**Tax Configuration Seeder (CRITICAL):**

```bash
# Seed UK tax configuration data
php artisan db:seed --class=TaxConfigurationSeeder --force

# This populates tax_configurations table with UK tax rates
```

**Optional Demo Data (for testing):**

```bash
# Create admin account: admin@fps.com / admin123456
php artisan db:seed --class=AdminUserSeeder --force

# Create demo user: demo@fps.com / password
php artisan db:seed --class=DemoUserSeeder --force
```

### 6.5 Optimize Laravel for Production

**Run all optimization commands:**

```bash
cd ~/tengo-app

# Clear all caches first
php artisan optimize:clear

# Cache configuration (production)
php artisan config:cache

# Cache routes (production)
php artisan route:cache

# Cache views (production)
php artisan view:cache

# Optimize overall
php artisan optimize

# Verify optimizations
ls -la bootstrap/cache/
# Should show: config.php, routes-v7.php, services.php
```

### 6.6 Configure .htaccess for Subdirectory

**Edit ~/tengo-app/public/.htaccess:**

```bash
# Via SSH
cd ~/tengo-app/public
nano .htaccess
```

**Replace with this optimized version:**

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Set base for subdirectory deployment
    # IMPORTANT: This tells Laravel it's running in /tengo subdirectory
    RewriteBase /tengo/

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent access to .env file
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Prevent access to storage files directly
RedirectMatch 403 ^/tengo/storage/
```

**Save and exit** (Ctrl+O, Enter, Ctrl+X in nano)

---

## 7. Post-Deployment Verification

### 7.1 Smoke Tests

**Test via browser:**

1. **Homepage**: https://csjones.co/tengo
   - ✅ Should load TenGo landing page
   - ✅ No 404 or 500 errors
   - ✅ CSS/JS assets loaded (check browser dev tools)

2. **Login Page**: https://csjones.co/tengo/login
   - ✅ Form displays correctly
   - ✅ No console errors

3. **Test Login**: admin@fps.com / admin123456
   - ✅ Login succeeds
   - ✅ Redirects to dashboard
   - ✅ Dashboard loads with no errors

4. **API Test**: https://csjones.co/tengo/api/health (create this endpoint)
   - ✅ Returns JSON response

### 7.2 403 Forbidden Error Testing

**⚠️ CRITICAL: Verify no 403 errors and confirm security blocking works correctly**

This comprehensive test suite verifies:
1. Application routes are accessible (no false 403s)
2. Sensitive files are properly blocked (correct 403s)
3. .htaccess configuration is working as intended

#### Test 1: Root URL Accessibility

```bash
# From your local machine
curl -I https://csjones.co/tengo
```

**Expected Result**: `HTTP/2 200 OK`
**If 403**: Root .htaccess may be present or public/.htaccess missing RewriteBase

#### Test 2: API Routes Accessibility

```bash
# Test API endpoint (should NOT return 403 or 404)
curl -I https://csjones.co/tengo/api/auth/login

# Expected: HTTP/2 405 (Method Not Allowed - correct, needs POST)
# OR: HTTP/2 422 (Validation error - also acceptable)
# WRONG: HTTP/2 404 (routing broken - check RewriteBase)
# WRONG: HTTP/2 403 (access denied - .htaccess issue)
```

#### Test 3: Asset Loading (No 403s on CSS/JS)

```bash
# Test manifest file
curl -I https://csjones.co/tengo/build/manifest.json

# Expected: HTTP/2 200 OK
# If 404: Assets not deployed
# If 403: Permission error on public/build/ directory

# Test a sample asset (get filename from manifest.json)
curl -I https://csjones.co/tengo/build/assets/app-[hash].js

# Expected: HTTP/2 200 OK
```

#### Test 4: Storage Directory Blocking (SHOULD Return 403)

```bash
# Test storage directory access (should be BLOCKED)
curl -I https://csjones.co/tengo/storage/logs/laravel.log

# Expected: HTTP/2 403 Forbidden (CORRECT - security working)
# If 200: SECURITY ISSUE - storage not blocked
# If 404: Also acceptable (directory not found)

# Test storage app directory
curl -I https://csjones.co/tengo/storage/app/

# Expected: HTTP/2 403 Forbidden (CORRECT)
```

#### Test 5: Sensitive Files Blocking (SHOULD Return 403)

```bash
# Test .env file (should be BLOCKED)
curl -I https://csjones.co/tengo/.env

# Expected: HTTP/2 403 Forbidden (CORRECT)
# If 200: CRITICAL SECURITY ISSUE

# Test composer.json (should be BLOCKED)
curl -I https://csjones.co/tengo/composer.json

# Expected: HTTP/2 403 Forbidden (CORRECT)

# Test .git directory (should be BLOCKED)
curl -I https://csjones.co/tengo/.git/config

# Expected: HTTP/2 403 Forbidden (CORRECT)

# Test package.json (should be BLOCKED)
curl -I https://csjones.co/tengo/package.json

# Expected: HTTP/2 403 Forbidden (CORRECT)
```

#### Test 6: Admin Routes (Expected 403 for Non-Admin)

```bash
# Admin routes should return 403 for unauthenticated users
curl -I https://csjones.co/tengo/api/admin/tax-config

# Expected: HTTP/2 403 Forbidden (CORRECT - authentication required)
# This is INTENDED behavior, not a bug
```

#### Test 7: Browser-Based Testing

**Open Browser DevTools** (F12 → Network tab):

1. Visit https://csjones.co/tengo
2. Check Network tab for any red (failed) requests
3. Filter by "403" status code
4. **Expected**: No 403 errors on CSS, JS, or API calls
5. **Verify**: No console errors related to blocked resources

**Check Console** (F12 → Console tab):
- ✅ No CORS errors
- ✅ No 403 errors on API requests
- ✅ No "Failed to load resource" errors

#### Test 8: Post-Login Route Testing

1. Login as admin: admin@fps.com / admin123456
2. Navigate to each module:
   - https://csjones.co/tengo/protection
   - https://csjones.co/tengo/savings
   - https://csjones.co/tengo/investment
   - https://csjones.co/tengo/retirement
   - https://csjones.co/tengo/estate
3. Check Network tab for any 403 errors
4. **Expected**: All module pages load with HTTP 200

#### Comprehensive Test Script

**Save as `test-403-errors.sh` and run from local machine:**

```bash
#!/bin/bash
# TenGo - 403 Error Testing Suite

BASE_URL="https://csjones.co/tengo"

echo "🔍 TenGo 403 Error Testing Suite"
echo "=================================="
echo ""

# Test 1: Root URL
echo "✓ Testing root URL..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL)
if [ "$STATUS" -eq 200 ]; then
  echo "  ✅ Root URL: $STATUS OK"
else
  echo "  ❌ Root URL: $STATUS (Expected 200)"
fi

# Test 2: API Route
echo "✓ Testing API route..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/api/auth/login)
if [ "$STATUS" -eq 405 ] || [ "$STATUS" -eq 422 ]; then
  echo "  ✅ API Route: $STATUS (Expected 405 or 422)"
elif [ "$STATUS" -eq 403 ]; then
  echo "  ❌ API Route: $STATUS (ROUTING ISSUE - check RewriteBase)"
elif [ "$STATUS" -eq 404 ]; then
  echo "  ❌ API Route: $STATUS (ROUTING BROKEN - check .htaccess)"
else
  echo "  ⚠️  API Route: $STATUS (Unexpected)"
fi

# Test 3: Assets
echo "✓ Testing asset loading..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/build/manifest.json)
if [ "$STATUS" -eq 200 ]; then
  echo "  ✅ Assets: $STATUS OK"
else
  echo "  ❌ Assets: $STATUS (Expected 200)"
fi

# Test 4: Storage blocking (should be 403)
echo "✓ Testing storage blocking..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/storage/logs/laravel.log)
if [ "$STATUS" -eq 403 ] || [ "$STATUS" -eq 404 ]; then
  echo "  ✅ Storage Blocked: $STATUS (Correct - security working)"
else
  echo "  ❌ Storage Accessible: $STATUS (SECURITY ISSUE)"
fi

# Test 5: .env blocking (should be 403)
echo "✓ Testing .env file blocking..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/.env)
if [ "$STATUS" -eq 403 ] || [ "$STATUS" -eq 404 ]; then
  echo "  ✅ .env Blocked: $STATUS (Correct - security working)"
else
  echo "  ❌ .env Accessible: $STATUS (CRITICAL SECURITY ISSUE)"
fi

# Test 6: composer.json blocking (should be 403)
echo "✓ Testing composer.json blocking..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/composer.json)
if [ "$STATUS" -eq 403 ] || [ "$STATUS" -eq 404 ]; then
  echo "  ✅ composer.json Blocked: $STATUS (Correct)"
else
  echo "  ❌ composer.json Accessible: $STATUS (Security concern)"
fi

echo ""
echo "=================================="
echo "Test suite complete!"
echo ""
echo "Expected Results Summary:"
echo "  - Root URL: 200 ✅"
echo "  - API Routes: 405/422 ✅"
echo "  - Assets: 200 ✅"
echo "  - Storage: 403/404 ✅ (blocked)"
echo "  - .env: 403/404 ✅ (blocked)"
echo "  - composer.json: 403/404 ✅ (blocked)"
```

**Run the test:**
```bash
chmod +x test-403-errors.sh
./test-403-errors.sh
```

#### Troubleshooting 403 Errors

**If Root URL Returns 403**:
```bash
# Via SSH
cd ~/tengo-app

# Check if root .htaccess exists (should NOT)
ls -la .htaccess
# If exists: rm .htaccess

# Verify public/.htaccess has RewriteBase
grep "RewriteBase" public/.htaccess
# Should show: RewriteBase /tengo/

# Check public/index.php permissions
ls -la public/index.php
# Should be: -rw-r--r-- (644)
```

**If API Routes Return 404**:
```bash
# Missing RewriteBase in .htaccess
cd ~/tengo-app/public
cat .htaccess | grep "RewriteBase"

# If missing, re-deploy production .htaccess:
cd ~/tengo-app
cp .htaccess.production public/.htaccess
```

**If Assets Return 403**:
```bash
# Permission issue on build directory
cd ~/tengo-app
chmod -R 644 public/build/*
chmod 755 public/build public/build/assets
```

**If Storage/Sensitive Files Return 200** (SECURITY ISSUE):
```bash
# .htaccess not working or missing
cd ~/tengo-app
cp .htaccess.production public/.htaccess
chmod 644 public/.htaccess

# Verify Apache is reading .htaccess
# Check SiteGround Site Tools > Error Log for [alert] messages
```

---

### 7.3 Check Laravel Logs

```bash
# Via SSH
cd ~/tengo-app

# View recent logs
tail -50 storage/logs/laravel.log

# Watch logs in real-time
tail -f storage/logs/laravel.log

# Check for errors
grep -i "error" storage/logs/laravel.log | tail -20
```

**Common Errors to Look For:**
- ❌ Database connection errors
- ❌ File permission errors
- ❌ Missing asset files (404s)
- ❌ Cache errors

### 7.3 Check Web Server Logs

**SiteGround Error Logs:**

1. Go to **Site Tools > Statistics > Error Log**
2. Review recent errors
3. Look for:
   - 404 errors on assets (missing files)
   - 500 errors (Laravel application errors)
   - Permission denied errors

### 7.4 Performance Check

**Test page load times:**

```bash
# From your local machine
curl -w "@curl-format.txt" -o /dev/null -s https://csjones.co/tengo
```

**Create curl-format.txt:**
```
time_namelookup:  %{time_namelookup}\n
time_connect:  %{time_connect}\n
time_total:  %{time_total}\n
```

**Expected Results:**
- ✅ Total time < 2 seconds (first load)
- ✅ Total time < 500ms (cached)

### 7.5 Verify Caching

```bash
# Via SSH
cd ~/tengo-app

# Test cache write/read
php artisan tinker

# In tinker:
Cache::put('test', 'value', 60);
Cache::get('test');
# Should return: "value"
exit
```

### 7.6 Test Critical User Flows

**Login and test each module:**

1. **Protection Module**: https://csjones.co/tengo/protection
   - Add life insurance policy
   - View coverage analysis
   - Verify calculations correct

2. **Savings Module**: https://csjones.co/tengo/savings
   - Add savings account
   - Check ISA tracker
   - Verify emergency fund calculation

3. **Investment Module**: https://csjones.co/tengo/investment
   - Add investment account
   - Add holdings
   - Check portfolio analysis

4. **Retirement Module**: https://csjones.co/tengo/retirement
   - Add DC pension
   - View projections
   - Check state pension integration

5. **Estate Module**: https://csjones.co/tengo/estate
   - Add property
   - View IHT calculation
   - Check gifting features

**Expected**: All modules load, forms work, calculations display correctly

---

## 8. Troubleshooting

### 8.1 500 Internal Server Error

**Symptoms**: White screen, generic 500 error

**Diagnosis:**

```bash
# Check Laravel log
tail -50 ~/tengo-app/storage/logs/laravel.log

# Check web server error log via Site Tools

# Common causes:
# 1. .env file missing or incorrect
# 2. APP_KEY not set
# 3. Database connection failed
# 4. File permissions incorrect
```

**Solutions:**

```bash
# Verify .env exists
cat ~/tengo-app/.env | grep APP_KEY

# Regenerate APP_KEY if missing
cd ~/tengo-app
php artisan key:generate

# Test database connection
php artisan db:show

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

### 8.2 404 on Assets (CSS/JS Not Loading)

**Symptoms**: Page loads but unstyled, console shows 404 for CSS/JS

**Diagnosis:**

```bash
# Check if assets exist
ls ~/tengo-app/public/build/manifest.json
ls ~/tengo-app/public/build/assets/ | head -10

# Check symlink
ls -la ~/public_html/tengo
```

**Solutions:**

```bash
# Re-upload assets
# On local machine:
cd /Users/Chris/Desktop/fpsApp/tengo
NODE_ENV=production npm run build
# Upload public/build/ via FTP

# Verify Vite config in .env
grep VITE ~/tengo-app/.env
# Should be: VITE_API_BASE_URL=https://csjones.co/tengo
```

### 8.3 API Calls Failing (CORS Errors)

**Symptoms**: API calls return CORS errors in browser console

**Diagnosis:**

```bash
# Check APP_URL in .env
grep APP_URL ~/tengo-app/.env

# Should match: https://csjones.co/tengo
```

**Solution:**

```bash
# Update .env
nano ~/tengo-app/.env
# Set: APP_URL=https://csjones.co/tengo

# Clear config cache
cd ~/tengo-app
php artisan config:clear
php artisan config:cache
```

### 8.4 Database Connection Failed

**Symptoms**: "SQLSTATE[HY000] [1045] Access denied"

**Diagnosis:**

```bash
# Test database credentials
mysql -h localhost -u cgsjones_tengo_user -p
# Enter password from .env

# If connection fails, credentials are wrong
```

**Solution:**

1. Go to SiteGround Site Tools > MySQL
2. Verify database name and username (with prefix)
3. Reset database user password if needed
4. Update `.env` with correct credentials
5. Clear config cache: `php artisan config:clear && php artisan config:cache`

### 8.5 Memcached Errors

**Symptoms**: "This cache store does not support tagging"

**Diagnosis:**

```bash
# Check if Memcached is available
php -m | grep memcached
```

**Solution:**

If Memcached not available:

```bash
# Update .env to use file cache
nano ~/tengo-app/.env
# Change: CACHE_DRIVER=file

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```

### 8.6 Routes Not Working (404 on /login)

**Symptoms**: Homepage works but /login returns 404

**Diagnosis:**

```bash
# Check .htaccess
cat ~/tengo-app/public/.htaccess | grep RewriteBase

# Should show: RewriteBase /tengo/
```

**Solution:**

```bash
# Update .htaccess with RewriteBase
nano ~/tengo-app/public/.htaccess

# Add after RewriteEngine On:
RewriteBase /tengo/

# Clear route cache
php artisan route:clear
php artisan route:cache
```

### 8.7 Session Errors / Login Loops

**Symptoms**: Can't stay logged in, constant redirect to login

**Diagnosis:**

```bash
# Check session configuration
grep SESSION ~/tengo-app/.env

# Check storage permissions
ls -la ~/tengo-app/storage/framework/sessions/
```

**Solution:**

```bash
# Update .env session config
nano ~/tengo-app/.env

SESSION_DRIVER=database
SESSION_DOMAIN=.csjones.co
SESSION_SECURE_COOKIE=true

# Clear sessions
php artisan session:table  # If not already migrated
php artisan migrate --force

# Clear config
php artisan config:clear
php artisan config:cache
```

---

## 9. Rollback Procedures

### 9.1 Emergency Rollback

**If deployment fails catastrophically:**

```bash
# 1. Note current state
cd ~/tengo-app
git log --oneline -5 > rollback-checkpoint.txt

# 2. Backup current .env
cp .env .env.backup

# 3. Restore previous version
# If you have backup: tengo-app.backup/
mv tengo-app tengo-app.failed
mv tengo-app.backup tengo-app

# 4. Rollback database migrations (CAREFUL)
cd ~/tengo-app
php artisan migrate:rollback --step=1 --force

# 5. Clear all caches
php artisan optimize:clear

# 6. Verify site operational
curl -I https://csjones.co/tengo
```

### 9.2 Database Rollback

**Via Admin Panel (Preferred):**

1. Login: https://csjones.co/tengo/admin
2. Admin account: admin@fps.com / admin123456
3. Navigate to **Database Backups**
4. Select backup from before deployment
5. Click **Restore**

**Via MySQL (Manual):**

```bash
# If you have SQL backup file
mysql -u cgsjones_tengo_user -p cgsjones_tengo_production < backup-before-deployment.sql
```

### 9.3 Code Rollback

**If using Git on server:**

```bash
cd ~/tengo-app

# View recent commits
git log --oneline -10

# Rollback to specific commit
git reset --hard [commit-hash]

# Reinstall dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
php artisan optimize:clear
php artisan config:cache
```

---

## 10. Maintenance

### 10.1 Updating the Application

**For future deployments:**

```bash
# 1. Backup database via admin panel
# 2. Note current git commit for rollback

cd ~/tengo-app
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader

# 4. Upload new assets (built locally)
# public/build/ via FTP

# 5. Run new migrations
php artisan migrate --force

# 6. Clear caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Verify site works
```

### 10.2 Log Management

**Rotate logs to prevent disk space issues:**

```bash
# Create cron job for log rotation
# Via Site Tools > Cron Jobs

# Add daily cron:
# Time: 02:00 AM daily
# Command:
cd ~/tengo-app && find storage/logs/ -name "*.log" -mtime +7 -delete

# This deletes logs older than 7 days
```

### 10.3 Cache Maintenance

**Clear caches periodically:**

```bash
# Create weekly cache clear cron job
# Time: 03:00 AM Sunday
# Command:
cd ~/tengo-app && php artisan optimize:clear && php artisan config:cache && php artisan route:cache
```

### 10.4 Database Backup Schedule

**Set up automated backups via Admin Panel:**

1. Login to TenGo admin: https://csjones.co/tengo/admin
2. Navigate to **System Settings > Database Backups**
3. Configure automatic backups:
   - Frequency: Daily
   - Retention: 30 days
   - Time: 01:00 AM

**Manual Backup:**

```bash
# Via SSH
cd ~/tengo-app
php artisan backup:run

# Backup saved to: storage/app/backups/
```

### 10.5 Monitoring

**Key Metrics to Monitor:**

1. **Error Log**: Site Tools > Statistics > Error Log
   - Check daily for unusual errors

2. **Disk Space**: Site Tools > Statistics > Disk Space
   - Ensure sufficient space (TenGo ~500MB)

3. **Database Size**: Site Tools > MySQL > Databases
   - Monitor growth, optimize if needed

4. **Laravel Logs**: `~/tengo-app/storage/logs/laravel.log`
   - Check for application errors

**Set up alerts:**
- SiteGround can email on high disk usage
- Monitor uptime with service like UptimeRobot (free)

### 10.6 Security Updates

**Keep Laravel and dependencies updated:**

```bash
# Check for security updates (local machine)
cd /Users/Chris/Desktop/fpsApp/tengo
composer outdated

# Update Laravel framework
composer update laravel/framework

# Test locally, then deploy updated files
```

**Security Checklist:**
- ✅ APP_DEBUG=false in production
- ✅ .env file NOT web-accessible
- ✅ Database password is strong (16+ characters)
- ✅ HTTPS enabled (SiteGround auto-provides Let's Encrypt)
- ✅ Regular backups enabled

---

## Quick Reference Commands

```bash
# Connect to server
ssh [username]@csjones.co -p18765

# Navigate to app
cd ~/tengo-app

# View logs
tail -50 storage/logs/laravel.log

# Clear caches
php artisan optimize:clear

# Cache for production
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Run migrations
php artisan migrate --force

# List routes
php artisan route:list

# Test database connection
php artisan db:show

# Enter interactive mode
php artisan tinker
```

---

## Support & Resources

**SiteGround Support:**
- Site Tools: https://my.siteground.com
- Knowledge Base: https://www.siteground.com/kb/
- Support Chat: Available 24/7 from Site Tools

**Laravel Documentation:**
- Deployment: https://laravel.com/docs/10.x/deployment
- Configuration: https://laravel.com/docs/10.x/configuration

**TenGo Application:**
- Version: v0.2.7
- Admin Access: admin@fps.com / admin123456
- Demo Access: demo@fps.com / password

---

**Deployment Guide Version**: 1.0
**Last Updated**: November 12, 2025
**Application Version**: TenGo v0.2.7

Built with Claude Code by Anthropic
