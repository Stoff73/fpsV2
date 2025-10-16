# FPS Deployment Guide for SiteGround

This guide provides step-by-step instructions for deploying the Financial Planning System (FPS) to your SiteGround hosting account using **Site Tools** (their modern control panel).

## Deployment Overview

- **URL**: https://csjones.co/fps
- **Server**: SiteGround.co.uk
- **Control Panel**: Site Tools (https://tools.siteground.com)
- **Installation Path**: `public_html/fps_laravel/` (Laravel application root)
- **Web Access Path**: `public_html/fps` → symlink to `fps_laravel/public`
- **Laravel Version**: 10.x
- **PHP Version Required**: 8.2+
- **Database**: MySQL 8.0+

> **Note**: This guide uses a **symlink setup** where `fps/` is a symbolic link to `fps_laravel/public/`. This is the recommended secure approach for Laravel subdirectory deployment, keeping application code outside the web root.
>
> SiteGround uses **Site Tools** as their proprietary hosting management system. This guide has been updated to reflect Site Tools instead of cPanel.

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [How to SSH Into Your SiteGround Server](#how-to-ssh-into-your-siteground-server)
3. [Server Requirements](#server-requirements)
4. [Step 1: Prepare Your Local Application](#step-1-prepare-your-local-application)
5. [Step 2: Configure SiteGround Server](#step-2-configure-siteground-server)
6. [Step 3: Upload Application Files](#step-3-upload-application-files)
7. [Step 4: Configure Database](#step-4-configure-database)
8. [Step 5: Configure Environment Variables](#step-5-configure-environment-variables)
9. [Step 6: Set Up Directory Structure with Symlink](#step-6-set-up-directory-structure-with-symlink)
10. [Step 7: Install Dependencies](#step-7-install-dependencies)
11. [Step 8: Upload Production Build Files](#step-8-upload-production-build-files)
12. [Step 9: Configure Web Server](#step-9-configure-web-server)
13. [Step 10: Run Migrations and Seeders](#step-10-run-migrations-and-seeders)
14. [Step 11: Optimize for Production](#step-11-optimize-for-production)
15. [Step 12: Set File Permissions](#step-12-set-file-permissions)
16. [Step 13: Test Deployment](#step-13-test-deployment)
17. [Troubleshooting](#troubleshooting)
18. [Recreating the Database on the Server](#recreating-the-database-on-the-server)
19. [Post-Deployment Maintenance](#post-deployment-maintenance)

---

## Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] SiteGround account credentials (Site Tools access)
- [ ] SSH access enabled (SiteGround supports SSH)
- [ ] FTP/SFTP credentials (or use File Manager)
- [ ] Database credentials ready
- [ ] Git repository access (https://github.com/Stoff73/fpsV2.git)
- [ ] Local application tested and working
- [ ] All tests passing (`./vendor/bin/pest`)
- [ ] Frontend built for production (`npm run build`)

---

## How to SSH Into Your SiteGround Server

SSH access is required for most deployment tasks. Follow these steps to set up SSH on your Mac.

### Step 1: Enable SSH in Site Tools

1. Log in to **Site Tools** at https://tools.siteground.com
2. Navigate to **Devs** → **SSH Keys Manager**
3. Click **Generate New Key**
4. Enter a **key name** (e.g., "my-macbook")
5. Either generate a random passphrase or enter your own (save this passphrase securely!)
6. Click **Generate**

### Step 2: Download Your Private Key

1. In **SSH Keys Manager**, find your newly created key
2. Click the **three dots (⋮)** next to it → **Private Key**
3. Copy the entire private key content (including the `-----BEGIN` and `-----END` lines)
4. On your Mac, open Terminal and create the SSH directory if it doesn't exist:
   ```bash
   mkdir -p ~/.ssh
   ```
5. Create the private key file:
   ```bash
   touch ~/.ssh/siteground_key
   ```
6. Open the file in a text editor:
   ```bash
   open -e ~/.ssh/siteground_key
   ```
7. Paste the private key content into the file that opens in TextEdit
8. Save and close TextEdit

> **Note**: If you get a "cannot open file for writing" error with `nano`, use `open -e` (TextEdit) instead, or use `vim` if you're familiar with it.

### Step 3: Set Correct Permissions

```bash
chmod 600 ~/.ssh/siteground_key
```

This ensures your private key is only readable by you (required for SSH security).

### Step 4: Get Your SSH Credentials

1. Back in **Site Tools** → **SSH Keys Manager**
2. Click the **three dots (⋮)** next to your key → **SSH Credentials**
3. You'll see:
   - **Username** (e.g., `uXXXXXXX`)
   - **Host** (your server hostname, e.g., `sg2-xx.siteground.biz`)
   - **Port** (usually `18765` for SiteGround)

### Step 5: Connect via SSH

Open Terminal on your Mac and run:

```bash
ssh -i ~/.ssh/siteground_key -p 18765 USERNAME@HOST
```

Replace:
- `USERNAME` with your SSH username from Site Tools
- `HOST` with your server hostname from Site Tools

**Example:**
```bash
ssh -i ~/.ssh/siteground_key -p 18765 u123456789@sg2-xx.siteground.biz
```

### Step 6: Enter Your Passphrase

When prompted, enter the passphrase you created when generating the SSH key.

You should now be connected to your server! You'll see a command prompt like:

```
[u123456789@sgXX ~]$
```

### Optional: Create an SSH Alias (Recommended)

To avoid typing the long SSH command every time, create an alias:

1. Open (or create) your SSH config file:
   ```bash
   open -e ~/.ssh/config
   ```

2. Add this configuration (replace with your actual credentials):
   ```
   Host siteground
       HostName sg2-xx.siteground.biz
       User u123456789
       Port 18765
       IdentityFile ~/.ssh/siteground_key
   ```

3. Save and close TextEdit

Now you can connect with just:
```bash
ssh siteground
```

### Common SSH Commands Once Connected

```bash
# Navigate to your FPS project
cd ~/public_html/fps

# Check current directory
pwd

# List files
ls -la

# Check PHP version
php -v

# Run artisan commands
php artisan --version

# Exit SSH session
exit
```

---

## Server Requirements

Verify your SiteGround server meets these requirements:

### PHP Requirements
- **PHP Version**: 8.2 or higher
- **PHP Extensions**:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PCRE
  - PDO
  - PDO_MySQL
  - Tokenizer
  - XML
  - Zip

### Database
- MySQL 8.0+ or MariaDB 10.3+

### Server Configuration
- Composer 2.x
- Node.js 18+ and npm (for building assets)
- Memory limit: At least 256MB (512MB recommended)
- Max execution time: 120 seconds minimum

---

## Step 1: Prepare Your Local Application

### 1.1 Configure Vite for Subdirectory Deployment

**IMPORTANT:** Before building assets, ensure `vite.config.js` is configured for subdirectory deployment.

Your [vite.config.js](vite.config.js) should have:

```javascript
export default defineConfig({
    // For subdirectory deployment (e.g., https://csjones.co/fps)
    // The base path must include /build/ for proper asset resolution
    base: '/fps/build/',
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
        vue(),
    ],
    // ... rest of config
});
```

This is **critical** for assets to load correctly on the server.

### 1.2 Build Frontend Assets

```bash
# Navigate to your project directory
cd /Users/CSJ/Desktop/fpsV2

# Install all dependencies (including dev dependencies needed for build)
npm install

# Build for production
npm run build
```

**Note:** You must use `npm install` (not `npm ci --production`) because build tools like `vite` and `laravel-vite-plugin` are dev dependencies required for building.

This creates optimized assets in `public/build/`.

### 1.3 Optimize Composer Dependencies

```bash
# Install production dependencies only (no dev packages)
composer install --optimize-autoloader --no-dev
```

### 1.4 Run Tests (Optional but Recommended)

```bash
# Ensure all tests pass before deployment
./vendor/bin/pest
```

### 1.5 Create Deployment Archive (Optional)

You can create a zip file excluding unnecessary files:

```bash
# Create a clean archive
zip -r fps-deployment.zip . \
  -x "*.git*" \
  -x "node_modules/*" \
  -x "tests/*" \
  -x "*.md" \
  -x ".env*" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*"
```

---

## Step 2: Configure SiteGround Server

### 2.1 Enable SSH Access

1. Log in to SiteGround Site Tools
2. Go to **SSH Access** (under Advanced)
3. Enable SSH access
4. Note your SSH username and port (usually 18765)

### 2.2 Verify PHP Version

1. In Site Tools, go to **Select PHP Version**
2. Select **PHP 8.2** or higher
3. Enable required extensions (see Server Requirements above)
4. Click **Save**

### 2.3 Check Composer Availability

SSH into your server and verify Composer:

```bash
ssh username@csjones.co -p 18765
composer --version
```

If Composer is not installed, contact SiteGround support or install it manually.

---

## Step 3: Upload Application Files

**Important:** With the symlink setup, you'll upload files to `fps_laravel/` and create a symlink at `fps/`.

### Option A: Git Clone (Recommended)

SSH into your server:

```bash
ssh -i ~/.ssh/siteground_key -p 18765 u163-ptanegf9edny@uk71.siteground.eu
cd ~/www/csjones.co/public_html
git clone https://github.com/Stoff73/fpsV2.git fps_laravel
cd fps_laravel
```

### Option B: FTP/SFTP Upload

1. Use an FTP client (FileZilla, Cyberduck, etc.)
2. Connect to your SiteGround server via SFTP
3. Navigate to `~/www/csjones.co/public_html/`
4. Create `fps_laravel` folder
5. Upload all files from your local project to `public_html/fps_laravel/`

**Important**: Upload the entire project, including hidden files like `.htaccess`

### Option C: Site Tools File Manager

1. Log in to Site Tools
2. Open **File Manager**
3. Navigate to `~/www/csjones.co/public_html/`
4. Create new folder: `fps_laravel`
5. Upload `fps-deployment.zip` (if you created it)
6. Extract the archive

---

## Step 4: Configure Database

### 4.1 Create MySQL Database

1. In Site Tools, go to **MySQL Databases**
2. Create a new database: `csjones_fps` (or your preferred name)
3. Create a new MySQL user: `csjones_fpsuser`
4. Set a strong password (save it securely)
5. Add user to database with **ALL PRIVILEGES**
6. Note the database host (usually `localhost`)

### 4.2 Note Database Credentials

You'll need these for the `.env` file:

```
DB_HOST=localhost
DB_DATABASE=csjones_fps
DB_USERNAME=csjones_fpsuser
DB_PASSWORD=your_secure_password
```

---

## Step 5: Configure Environment Variables

### 5.1 Create .env File

SSH into your server:

```bash
cd ~/www/csjones.co/public_html/fps_laravel
cp .env.example .env
nano .env
```

### 5.2 Update .env Configuration

```env
# Application
APP_NAME="Financial Planning System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://csjones.co/fps

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=csjones_fps
DB_USERNAME=csjones_fpsuser
DB_PASSWORD=your_secure_password

# Cache
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Security
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=csjones.co

# Mail (configure based on your SMTP settings)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@csjones.co"
MAIL_FROM_NAME="${APP_NAME}"

# Asset URL (important for subfolder deployment)
ASSET_URL=https://csjones.co/fps
```

**Important Settings for Subfolder Deployment:**
- `APP_URL=https://csjones.co/fps`
- `ASSET_URL=https://csjones.co/fps`
- `SESSION_SECURE_COOKIE=true` (for HTTPS)

Save and exit (`Ctrl+X`, then `Y`, then `Enter`).

### 5.3 Generate Application Key

```bash
cd ~/www/csjones.co/public_html/fps_laravel
php artisan key:generate
```

This will update the `APP_KEY` in your `.env` file.

---

## Step 6: Set Up Directory Structure with Symlink

### 6.1 Verify Directory Structure

Your directory structure should look like this with the **symlink setup**:

```
~/www/csjones.co/public_html/
├── fps → fps_laravel/public  # Symbolic link (web-accessible)
└── fps_laravel/               # Laravel application root (NOT web-accessible)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/                # Laravel's public directory
    │   ├── build/             # Compiled Vue.js assets
    │   │   ├── .vite/
    │   │   │   └── manifest.json
    │   │   ├── assets/
    │   │   └── manifest.json  # Symlink to .vite/manifest.json
    │   ├── index.php          # Laravel entry point
    │   └── .htaccess
    ├── resources/
    ├── routes/
    ├── storage/
    │   ├── app/
    │   ├── framework/
    │   └── logs/
    ├── vendor/
    ├── .env
    ├── artisan
    └── composer.json
```

### 6.2 Create Symbolic Link (CRITICAL)

This is the **most important step** for secure Laravel subdirectory deployment. The symlink makes only the `public` directory web-accessible while keeping application code secure.

```bash
cd ~/www/csjones.co/public_html

# Create the symlink (if it doesn't already exist)
ln -s fps_laravel/public fps

# Verify the symlink was created correctly
ls -la fps
# Should show: fps -> fps_laravel/public

# Verify the symlink works
ls -la fps/
# Should show contents of fps_laravel/public/ (index.php, .htaccess, etc.)
```

**Important:** If the `fps` symlink already exists and points to the correct location, you don't need to recreate it.

---

## Step 7: Install Dependencies

### 7.1 Install PHP Dependencies

```bash
cd ~/www/csjones.co/public_html/fps_laravel
composer install --optimize-autoloader --no-dev
```

This installs all PHP packages from `composer.json`.

### 7.2 Verify Installation

```bash
php artisan --version
```

You should see: `Laravel Framework 10.x.x`

---

## Step 8: Upload Production Build Files

After building assets locally with the correct Vite configuration, you need to upload them to the server.

### 8.1 Build Assets Locally (if not already done)

On your **local machine**:

```bash
cd /Users/CSJ/Desktop/fpsV2

# Ensure vite.config.js has base: '/fps/build/'
# Then build production assets
npm run build
```

This creates the `public/build/` directory with all compiled assets.

### 8.2 Upload Build Files to Server

**Option A: SFTP/FTP (Recommended - Fastest)**

Using FileZilla, Cyberduck, or any FTP client:

1. Connect to your server via SFTP
2. Navigate to remote: `~/www/csjones.co/public_html/fps_laravel/public/`
3. **Delete** the existing `build/` directory on the server (if it exists)
4. **Upload** your local `public/build/` directory to the server

**Option B: Commit and Push via Git**

On your **local machine**:

```bash
git add public/build/
git commit -m "build: Add production assets"
git push origin main
```

Then on the **server**:

```bash
cd ~/www/csjones.co/public_html/fps_laravel
git pull origin main
```

### 8.3 Create Manifest Symlink (CRITICAL)

Laravel's Vite integration expects `manifest.json` at `public/build/manifest.json`, but Vite creates it at `public/build/.vite/manifest.json`. We need to create a symlink:

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Create symlink so Laravel can find the manifest
ln -s .vite/manifest.json public/build/manifest.json

# Verify the symlink was created
ls -la public/build/manifest.json
# Should show: manifest.json -> .vite/manifest.json

# Verify the symlink works
cat public/build/manifest.json | head -5
# Should show JSON content
```

**This step is essential** - without it, you'll get a "Vite manifest not found" error.

### 8.4 Verify Build Files

```bash
# Check build directory structure
ls -la public/build/
# Should show: .vite/, assets/, manifest.json (symlink)

# Check assets were uploaded
ls -la public/build/assets/ | head -10
# Should show .js and .css files with recent timestamps

# Verify accessible via web symlink
ls -la ~/www/csjones.co/public_html/fps/build/
# Should show the same files (accessed through the fps symlink)
```

---

## Step 9: Configure Web Server

With the symlink setup, **no additional web server configuration is needed!** The `fps` symlink points directly to `fps_laravel/public`, which is Laravel's public directory.

### Verify Web Server Configuration

The existing `.htaccess` in `fps_laravel/public/.htaccess` should handle all routing automatically.

```bash
# Verify .htaccess exists and is readable
cat ~/www/csjones.co/public_html/fps_laravel/public/.htaccess | head -20
```

You should see Laravel's default `.htaccess` with mod_rewrite rules. **No changes needed** - the symlink handles everything.

### Why This Works

- URL `https://csjones.co/fps/` → symlink `fps/` → actual directory `fps_laravel/public/`
- URL `https://csjones.co/fps/build/assets/app.js` → `fps_laravel/public/build/assets/app.js`
- Laravel's `.htaccess` in `public/` handles all routing
- Application code in `fps_laravel/` remains secure (not web-accessible)

---

## Step 10: Run Migrations and Seeders

### 10.1 Run Database Migrations

```bash
cd ~/www/csjones.co/public_html/fps_laravel
php artisan migrate --force
```

The `--force` flag is required in production environments.

### 10.2 Seed Tax Configuration

```bash
php artisan db:seed --class=TaxConfigurationSeeder --force
```

This populates UK tax rules (NRB, RNRB, ISA allowances, etc.).

### 10.3 (Optional) Seed Demo User

For testing purposes, you can create a demo user with sample data:

```bash
php artisan db:seed --class=DemoUserSeeder --force
```

This creates a demo user (`demo@example.com` / `password`) with sample data across all modules.

### 10.4 Verify Database

Check that tables were created:

```bash
php artisan tinker
>>> \DB::select('SHOW TABLES');
>>> exit
```

You should see all FPS tables (users, investments, trusts, gifts, etc.).

---

## Step 11: Optimize for Production

### 11.1 Cache Configuration

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Cache config files
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### 11.2 Optimize Autoloader

```bash
composer dump-autoload --optimize
```

### 11.3 Clear Old Caches (if redeploying)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Step 12: Set File Permissions

Proper file permissions are critical for security and functionality.

### 12.1 Set Ownership (if you have sudo access)

```bash
# Set web server user as owner (usually 'nobody' or 'www-data' on SiteGround)
chown -R $USER:$USER ~/www/csjones.co/public_html/fps_laravel
```

### 12.2 Set Directory Permissions

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Set general permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Set artisan as executable
chmod 755 artisan

# Make storage and cache writable
chmod -R 775 storage bootstrap/cache
```

### 12.3 Protect Sensitive Files

```bash
# Protect .env file
chmod 600 .env

# Protect database file if using SQLite (not applicable for MySQL)
# chmod 600 database/database.sqlite
```

---

## Step 13: Test Deployment

### 13.1 Access the Application

Visit: **https://csjones.co/fps**

You should see the FPS login/register page with proper styling and no console errors.

### 13.2 Verify Assets Load Correctly

1. Open browser DevTools (F12)
2. Go to **Console** tab - should be no MIME type errors
3. Go to **Network** tab - verify all `.js` and `.css` files load with status 200
4. Check that paths look like: `https://csjones.co/fps/build/assets/app-xxx.js`

### 13.3 Register a Test User

1. Click **Register**
2. Create a test account
3. Verify email functionality (if configured)
4. Log in

### 13.4 Test Key Features

- [ ] Dashboard loads correctly with charts
- [ ] Navigation works (Protection, Savings, Investment, Retirement, Estate, UK Taxes)
- [ ] Create test data in each module
- [ ] Verify calculations (IHT, Monte Carlo, etc.)
- [ ] Check trust creation and IHT planning
- [ ] Verify charts render correctly (ApexCharts)
- [ ] Test responsive design on mobile

### 13.5 Check Logs for Errors

```bash
tail -f ~/www/csjones.co/public_html/fps_laravel/storage/logs/laravel.log
```

If you see errors, troubleshoot using the guide below.

---

## Troubleshooting

### Issue 1: Vite Manifest Not Found (500 Error)

**Error:** `Vite manifest not found at: /home/customer/www/csjones.co/public_html/fps_laravel/public/build/manifest.json`

**Cause:** Laravel's Vite integration expects `manifest.json` at `public/build/manifest.json`, but Vite creates it at `public/build/.vite/manifest.json`.

**Solution:**

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Create symlink so Laravel can find the manifest
ln -s .vite/manifest.json public/build/manifest.json

# Verify the symlink was created
ls -la public/build/manifest.json
# Should show: manifest.json -> .vite/manifest.json

# Clear view cache
php artisan view:clear

# Test the app
```

This is the **most common issue** with the symlink deployment setup.

### Issue 2: 500 Internal Server Error (General)

**Possible Causes:**
- `.env` file missing or misconfigured
- File permissions incorrect
- PHP extensions missing
- Symlink not created correctly

**Solutions:**
```bash
# Check Laravel logs
tail -100 ~/www/csjones.co/public_html/fps_laravel/storage/logs/laravel.log

# Verify .env exists
ls -la ~/www/csjones.co/public_html/fps_laravel/.env

# Verify symlink exists
ls -la ~/www/csjones.co/public_html/fps
# Should show: fps -> fps_laravel/public

# Check file permissions
ls -la ~/www/csjones.co/public_html/fps_laravel/storage

# Test PHP
php -v
php -m  # List installed modules
```

### Issue 3: Database Connection Error

**Error:** `SQLSTATE[HY000] [1045] Access denied`

**Solutions:**
```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Verify database credentials in .env
cat .env | grep DB_

# Test database connection
php artisan tinker
>>> \DB::connection()->getPdo();
```

If connection fails, verify:
- Database name is correct
- Username has privileges
- Password is correct
- Host is correct (usually `localhost`)

### Issue 4: CSS/JS Assets Not Loading (MIME Type Error)

**Symptoms:**
- Page loads but has no styling or JavaScript errors in console
- Browser console shows: `Failed to load module script: Expected a JavaScript-or-Wasm module script but the server responded with a MIME type of "text/html"`
- Files like `Login-CHtZ5lY7.js` cannot be loaded

**Cause:** Asset paths are incorrect for subfolder deployment. Vite needs to know the base path where assets will be served.

**Solutions:**

1. **Update `vite.config.js`** (most important):
```javascript
export default defineConfig({
    // For subdirectory deployment (e.g., https://csjones.co/fps)
    // The base path must include /build/ for proper asset resolution
    base: '/fps/build/',
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
        vue(),
    ],
    // ... rest of config
});
```

2. **Rebuild assets locally** with the updated configuration:
```bash
# On your local machine
cd /Users/CSJ/Desktop/fpsV2
npm run build
```

3. **Upload the new build files** to the server:
```bash
# Via SSH/SFTP, upload the entire public/build/ directory
# Or use git to commit and pull on server
```

4. **Verify `ASSET_URL` in server `.env`**:
```env
ASSET_URL=https://csjones.co/fps
```

5. **Clear config cache on server**:
```bash
php artisan config:clear
php artisan config:cache
```

6. **Verify build directory exists on server**:
```bash
ls -la ~/public_html/fps/public/build/
# Should show: assets/, .vite/, manifest.json
```

7. **Check `.htaccess` rewrite rules** (see Step 8)

**Important Notes:**
- The `base: '/fps/build/'` in `vite.config.js` tells Vite where assets will be served from
- The `ASSET_URL` in `.env` tells Laravel where to find assets
- Both must match your deployment structure
- Always rebuild assets after changing `vite.config.js`

### Issue 4: 404 on Routes

**Symptoms:** Homepage works but `/fps/dashboard` returns 404

**Cause:** `.htaccess` not working or `mod_rewrite` disabled

**Solutions:**

1. Verify `mod_rewrite` is enabled (contact SiteGround if not)

2. Check `.htaccess` exists in `public` folder:
```bash
cat ~/public_html/fps/public/.htaccess
```

3. Test rewrite rules:
```bash
# Create test file
echo "Rewrite works" > ~/public_html/fps/public/test.txt

# Access via browser
# https://csjones.co/fps/test.txt
```

### Issue 5: Storage Permissions Error

**Error:** `The stream or file "storage/logs/laravel.log" could not be opened`

**Solution:**
```bash
cd ~/public_html/fps
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache
```

### Issue 6: Session/CSRF Token Mismatch

**Error:** `CSRF token mismatch` or `419 Page Expired`

**Causes:**
- Cookies not set correctly
- Session driver misconfigured
- HTTPS/HTTP mismatch

**Solutions:**

1. Update `.env`:
```env
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.csjones.co
SANCTUM_STATEFUL_DOMAINS=csjones.co,www.csjones.co
```

2. Clear config:
```bash
php artisan config:clear
php artisan config:cache
```

3. Clear browser cookies and try again

### Issue 7: Queue Jobs Not Processing

**Symptoms:** Monte Carlo simulations stuck, jobs not running

**Solution:**

1. Verify queue driver in `.env`:
```env
QUEUE_CONNECTION=database
```

2. Run queue worker (requires keeping SSH session open or using supervisor):
```bash
php artisan queue:work database --tries=3
```

3. For production, set up a cron job:
```bash
# Edit crontab
crontab -e

# Add this line (runs queue every minute)
* * * * * cd ~/public_html/fps && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd ~/public_html/fps && php artisan queue:work database --stop-when-empty >> /dev/null 2>&1
```

### Issue 8: Memory Limit Exceeded

**Error:** `Allowed memory size of X bytes exhausted`

**Solution:**

1. Increase PHP memory limit in `.htaccess`:
```apache
php_value memory_limit 512M
```

2. Or create `php.ini` in project root:
```ini
memory_limit = 512M
max_execution_time = 120
```

3. Contact SiteGround to increase limits if needed

---

## Recreating the Database on the Server

If you need to completely rebuild the database (e.g., after schema changes, corrupted data, or fresh deployment), follow these steps carefully.

### Option 1: Fresh Migration (Recommended for Development/Testing)

This will **DROP ALL TABLES** and recreate them from scratch. **WARNING: All data will be lost!**

```bash
# SSH into server
ssh username@csjones.co -p 18765
cd ~/public_html/fps

# Drop all tables and re-migrate
php artisan migrate:fresh --force

# Seed tax configuration
php artisan db:seed --class=TaxConfigurationSeeder --force

# (Optional) Seed demo user with sample data
php artisan db:seed --class=DemoUserSeeder --force

# Verify tables were created
php artisan tinker
>>> \DB::select('SHOW TABLES');
>>> exit
```

**When to use this:**
- Fresh deployment
- Development/staging environment
- Testing database schema changes
- **NEVER use on production with live user data**

---

### Option 2: Drop and Recreate Database via Site Tools (Production Safe)

This method allows you to backup first and is safer for production environments.

#### Step 1: Backup Existing Database

```bash
# SSH into server
ssh username@csjones.co -p 18765

# Create backup directory if it doesn't exist
mkdir -p ~/backups

# Create database backup with timestamp
mysqldump -u csjones_fpsuser -p csjones_fps > ~/backups/fps_backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup was created
ls -lh ~/backups/
```

**Save this backup file!** Download it via FTP/SFTP or Site Tools File Manager.

#### Step 2: Drop Database via Site Tools

1. Log in to **SiteGround Site Tools**
2. Go to **MySQL Databases**
3. Scroll to **Current Databases**
4. Find `csjones_fps` database
5. Click **Delete** (confirm deletion)

#### Step 3: Recreate Database

1. Still in **MySQL Databases** in Site Tools
2. Under **Create New Database**:
   - Database Name: `fps` (will become `csjones_fps`)
   - Click **Create Database**
3. Go back to **MySQL Databases**
4. Under **Add User to Database**:
   - User: Select `csjones_fpsuser`
   - Database: Select `csjones_fps`
   - Click **Add**
5. On the **Manage User Privileges** page:
   - Check **ALL PRIVILEGES**
   - Click **Make Changes**

#### Step 4: Run Fresh Migrations

```bash
# SSH into server
ssh username@csjones.co -p 18765
cd ~/public_html/fps

# Verify database connection
php artisan tinker
>>> \DB::connection()->getPdo();
>>> exit

# Run fresh migrations
php artisan migrate --force

# Seed tax configuration
php artisan db:seed --class=TaxConfigurationSeeder --force

# (Optional) Seed demo user
php artisan db:seed --class=DemoUserSeeder --force
```

#### Step 5: Verify Database

```bash
# Check tables were created
php artisan tinker
>>> \DB::select('SHOW TABLES');
>>> \App\Models\User::count();  # Should be 0 (or 1 if you seeded demo user)
>>> exit
```

---

### Option 3: Drop and Recreate via Command Line (Advanced)

For users comfortable with MySQL command line:

#### Step 1: Backup Database

```bash
# Create backup
mysqldump -u csjones_fpsuser -p csjones_fps > ~/backups/fps_backup_$(date +%Y%m%d_%H%M%S).sql
```

#### Step 2: Drop All Tables

```bash
# Connect to MySQL
mysql -u csjones_fpsuser -p

# Select database
USE csjones_fps;

# Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

# Generate DROP TABLE statements for all tables
SELECT CONCAT('DROP TABLE IF EXISTS `', table_name, '`;')
FROM information_schema.tables
WHERE table_schema = 'csjones_fps';

# Copy the output and execute each DROP TABLE statement
# Example:
# DROP TABLE IF EXISTS `users`;
# DROP TABLE IF EXISTS `dc_pensions`;
# ... (repeat for all tables)

# Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

# Exit MySQL
EXIT;
```

#### Step 3: Run Fresh Migrations

```bash
cd ~/public_html/fps

# Run migrations
php artisan migrate --force

# Seed tax configuration
php artisan db:seed --class=TaxConfigurationSeeder --force
```

---

### Option 4: Restore from Backup

If you need to restore a previous database state:

```bash
# SSH into server
ssh username@csjones.co -p 18765

# Drop current database (via Site Tools or MySQL command line)
# Then recreate empty database (via Site Tools)

# Restore from backup file
mysql -u csjones_fpsuser -p csjones_fps < ~/backups/fps_backup_20251016_120000.sql

# Verify restoration
mysql -u csjones_fpsuser -p
USE csjones_fps;
SHOW TABLES;
SELECT COUNT(*) FROM users;
EXIT;
```

---

### Option 5: Automated Database Recreation Script

Create a shell script for quick database recreation (useful for frequent rebuilds):

```bash
# Create script
nano ~/recreate_fps_db.sh
```

Add this content:

```bash
#!/bin/bash

# Configuration
DB_NAME="csjones_fps"
DB_USER="csjones_fpsuser"
PROJECT_DIR="$HOME/public_html/fps"
BACKUP_DIR="$HOME/backups"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}FPS Database Recreation Script${NC}"
echo "========================================"

# Ask for confirmation
read -p "This will DROP ALL DATA in $DB_NAME. Are you sure? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo -e "${RED}Aborted.${NC}"
    exit 1
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup existing database
echo -e "${YELLOW}Creating backup...${NC}"
BACKUP_FILE="$BACKUP_DIR/fps_backup_$(date +%Y%m%d_%H%M%S).sql"
mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_FILE"
echo -e "${GREEN}Backup created: $BACKUP_FILE${NC}"

# Navigate to project
cd "$PROJECT_DIR" || exit

# Run fresh migration
echo -e "${YELLOW}Running fresh migrations...${NC}"
php artisan migrate:fresh --force

# Seed tax configuration
echo -e "${YELLOW}Seeding tax configuration...${NC}"
php artisan db:seed --class=TaxConfigurationSeeder --force

# Ask about demo user
read -p "Seed demo user with sample data? (yes/no): " seed_demo
if [ "$seed_demo" == "yes" ]; then
    php artisan db:seed --class=DemoUserSeeder --force
    echo -e "${GREEN}Demo user seeded.${NC}"
fi

# Clear caches
echo -e "${YELLOW}Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
echo -e "${YELLOW}Building caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}Database recreation complete!${NC}"
echo "========================================"
echo "Backup saved to: $BACKUP_FILE"
echo "Tables created: $(php artisan tinker --execute="\DB::select('SHOW TABLES')" | wc -l)"
echo "Ready to use!"
```

Make it executable and run:

```bash
# Make script executable
chmod +x ~/recreate_fps_db.sh

# Run the script
~/recreate_fps_db.sh
```

---

### Database Recreation Checklist

Use this checklist when recreating the database:

- [ ] **Backup current database** (if any data exists)
- [ ] **Download backup file** to local machine (for safety)
- [ ] **Verify backup file** is not empty/corrupt
- [ ] **Drop all tables** or delete database
- [ ] **Recreate database** with same name
- [ ] **Re-add database user** with ALL PRIVILEGES
- [ ] **Test database connection** (`php artisan tinker`)
- [ ] **Run migrations** (`php artisan migrate --force`)
- [ ] **Seed tax configuration** (`db:seed --class=TaxConfigurationSeeder`)
- [ ] **Verify tables created** (`SHOW TABLES`)
- [ ] **Test application login/register**
- [ ] **Clear all caches** (config, route, view)
- [ ] **Test all modules** (Protection, Savings, Investment, Retirement, Estate, UK Taxes)

---

### Common Database Recreation Issues

#### Issue 1: Foreign Key Constraint Errors

**Error:** `Cannot drop table 'users' referenced by a foreign key constraint`

**Solution:**
```bash
# In MySQL console
SET FOREIGN_KEY_CHECKS = 0;
# Drop tables
SET FOREIGN_KEY_CHECKS = 1;
```

#### Issue 2: Migration Already Run

**Error:** `Migration already ran: 2024_01_01_000000_create_users_table`

**Solution:**
```bash
# Clear migration records
php artisan migrate:fresh --force
# Or manually truncate migrations table
mysql -u csjones_fpsuser -p
TRUNCATE migrations;
EXIT;
```

#### Issue 3: Database Connection After Recreation

**Error:** `SQLSTATE[HY000] [1049] Unknown database 'csjones_fps'`

**Solution:**
- Verify database was actually created in Site Tools
- Check database name in `.env` matches Site Tools database name
- Test connection: `php artisan tinker` → `\DB::connection()->getPdo()`

#### Issue 4: Seeder Class Not Found

**Error:** `Target class [TaxConfigurationSeeder] does not exist`

**Solution:**
```bash
# Regenerate autoload files
composer dump-autoload

# Run seeder again
php artisan db:seed --class=TaxConfigurationSeeder --force
```

---

## Post-Deployment Maintenance

### Update Application

When you push changes to GitHub:

```bash
# SSH into server
ssh username@csjones.co -p 18765
cd ~/public_html/fps

# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run new migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers (if running)
php artisan queue:restart
```

### Rebuild Frontend Assets

If you've made Vue.js changes:

```bash
# On your local machine
cd /Users/CSJ/Desktop/fpsV2
npm run build

# Upload new build files via FTP/SFTP
# Or commit build files to git and pull on server
```

### Backup Database

Set up automated backups via Site Tools:

1. Go to **Backup Wizard** in Site Tools
2. Choose **Backup** → **Full Backup**
3. Set up automated daily backups
4. Download backups regularly

Or use command line:

```bash
# Create backup
mysqldump -u csjones_fpsuser -p csjones_fps > ~/backups/fps_$(date +%Y%m%d).sql

# Restore from backup
mysql -u csjones_fpsuser -p csjones_fps < ~/backups/fps_20251015.sql
```

### Monitor Logs

```bash
# Watch Laravel logs
tail -f ~/public_html/fps/storage/logs/laravel.log

# Watch Apache error logs (path varies by server)
tail -f ~/logs/error_log
```

### Security Updates

```bash
# Update Composer dependencies
composer update --no-dev

# Check for security advisories
composer audit
```

### Performance Monitoring

- Monitor response times via browser DevTools
- Check database query performance
- Monitor server resources in SiteGround control panel

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong database password
- [ ] `.env` file permissions set to 600
- [ ] HTTPS enforced (SiteGround provides free SSL)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Regular backups configured
- [ ] File permissions set correctly (644 for files, 755 for dirs)
- [ ] Composer security audit passing
- [ ] Web server configured to prevent directory listing
- [ ] Error reporting disabled in production

---

## Support Resources

- **SiteGround Documentation**: https://www.siteground.com/kb/
- **Laravel Deployment Guide**: https://laravel.com/docs/10.x/deployment
- **FPS GitHub Repository**: https://github.com/Stoff73/fpsV2
- **SiteGround Support**: Available 24/7 via chat/ticket

---

## Quick Reference Commands

```bash
# SSH Connection
ssh username@csjones.co -p 18765

# Navigate to project
cd ~/public_html/fps

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Cache everything
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Check Laravel version
php artisan --version

# View logs
tail -100 storage/logs/laravel.log
```

---

## Deployment Checklist

Use this checklist for each deployment:

- [ ] 1. Local tests passing
- [ ] 2. Frontend built (`npm run build`)
- [ ] 3. Code committed and pushed to GitHub
- [ ] 4. SSH into SiteGround server
- [ ] 5. Pull latest code (`git pull`)
- [ ] 6. Install/update dependencies (`composer install`)
- [ ] 7. Run new migrations (`php artisan migrate --force`)
- [ ] 8. Clear old caches
- [ ] 9. Rebuild production caches
- [ ] 10. Test application in browser
- [ ] 11. Check logs for errors
- [ ] 12. Verify all modules working
- [ ] 13. Create database backup

---

## Conclusion

Your FPS application should now be successfully deployed to SiteGround and accessible at **https://csjones.co/fps**.

For questions or issues, refer to the Troubleshooting section or contact support.

**Last Updated**: October 16, 2025
**Version**: 1.1.0

## Recent Updates (v1.1.0)

- Added comprehensive database recreation instructions (5 different methods)
- Added automated database recreation script
- Added database recreation checklist
- Added troubleshooting for common database recreation issues
- Updated migration and seeder instructions with DemoUserSeeder option
- Added UK Taxes & Allowances module deployment notes
- Added investment holdings percentage-based allocation migration notes
