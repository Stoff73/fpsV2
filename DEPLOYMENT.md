# FPS Deployment Guide for SiteGround

This guide provides step-by-step instructions for deploying the Financial Planning System (FPS) to your SiteGround hosting account.

## Deployment Overview

- **URL**: https://csjones.co/fps
- **Server**: SiteGround.co.uk
- **Installation Path**: `public_html/fps/`
- **Laravel Version**: 10.x
- **PHP Version Required**: 8.2+
- **Database**: MySQL 8.0+

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Requirements](#server-requirements)
3. [Step 1: Prepare Your Local Application](#step-1-prepare-your-local-application)
4. [Step 2: Configure SiteGround Server](#step-2-configure-siteground-server)
5. [Step 3: Upload Application Files](#step-3-upload-application-files)
6. [Step 4: Configure Database](#step-4-configure-database)
7. [Step 5: Configure Environment Variables](#step-5-configure-environment-variables)
8. [Step 6: Set Up Directory Structure](#step-6-set-up-directory-structure)
9. [Step 7: Install Dependencies](#step-7-install-dependencies)
10. [Step 8: Configure Web Server](#step-8-configure-web-server)
11. [Step 9: Run Migrations and Seeders](#step-9-run-migrations-and-seeders)
12. [Step 10: Optimize for Production](#step-10-optimize-for-production)
13. [Step 11: Set File Permissions](#step-11-set-file-permissions)
14. [Step 12: Test Deployment](#step-12-test-deployment)
15. [Troubleshooting](#troubleshooting)
16. [Post-Deployment Maintenance](#post-deployment-maintenance)

---

## Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] SiteGround account credentials (cPanel access)
- [ ] SSH access enabled (SiteGround supports SSH)
- [ ] FTP/SFTP credentials (or use File Manager)
- [ ] Database credentials ready
- [ ] Git repository access (https://github.com/Stoff73/fpsV2.git)
- [ ] Local application tested and working
- [ ] All tests passing (`./vendor/bin/pest`)
- [ ] Frontend built for production (`npm run build`)

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

### 1.1 Build Frontend Assets

```bash
# Navigate to your project directory
cd /Users/CSJ/Desktop/fpsV2

# Install production dependencies
npm ci --production

# Build for production
npm run build
```

This creates optimized assets in `public/build/`.

### 1.2 Optimize Composer Dependencies

```bash
# Install production dependencies only (no dev packages)
composer install --optimize-autoloader --no-dev
```

### 1.3 Run Tests (Optional but Recommended)

```bash
# Ensure all tests pass before deployment
./vendor/bin/pest
```

### 1.4 Create Deployment Archive (Optional)

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

1. Log in to SiteGround cPanel
2. Go to **SSH Access** (under Advanced)
3. Enable SSH access
4. Note your SSH username and port (usually 18765)

### 2.2 Verify PHP Version

1. In cPanel, go to **Select PHP Version**
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

You have three options for uploading files:

### Option A: Git Clone (Recommended)

SSH into your server:

```bash
ssh username@csjones.co -p 18765
cd public_html
git clone https://github.com/Stoff73/fpsV2.git fps
cd fps
```

### Option B: FTP/SFTP Upload

1. Use an FTP client (FileZilla, Cyberduck, etc.)
2. Connect to your SiteGround server via SFTP
3. Navigate to `public_html/`
4. Create `fps` folder
5. Upload all files from your local project to `public_html/fps/`

**Important**: Upload the entire project, including hidden files like `.htaccess`

### Option C: cPanel File Manager

1. Log in to cPanel
2. Open **File Manager**
3. Navigate to `public_html/`
4. Create new folder: `fps`
5. Upload `fps-deployment.zip` (if you created it)
6. Extract the archive

---

## Step 4: Configure Database

### 4.1 Create MySQL Database

1. In cPanel, go to **MySQL Databases**
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
cd ~/public_html/fps
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
cd ~/public_html/fps
php artisan key:generate
```

This will update the `APP_KEY` in your `.env` file.

---

## Step 6: Set Up Directory Structure

### 6.1 Verify Directory Structure

Your directory structure should look like this:

```
public_html/fps/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          # Laravel's public directory
│   ├── build/       # Compiled Vue.js assets
│   ├── index.php    # Laravel entry point
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

### 6.2 Create Symbolic Link (Important)

Laravel's public directory needs to be accessible at `/fps`. We'll configure this in Step 8.

---

## Step 7: Install Dependencies

### 7.1 Install PHP Dependencies

```bash
cd ~/public_html/fps
composer install --optimize-autoloader --no-dev
```

This installs all PHP packages from `composer.json`.

### 7.2 Verify Installation

```bash
php artisan --version
```

You should see: `Laravel Framework 10.x.x`

---

## Step 8: Configure Web Server

Since you're accessing the app at `https://csjones.co/fps`, we need to configure the web server to point to Laravel's `public` directory.

### Option A: .htaccess in fps folder (Recommended)

Create an `.htaccess` file in `public_html/fps/`:

```bash
cd ~/public_html/fps
nano .htaccess
```

Add this content:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirect all requests to the public subfolder
    RewriteCond %{REQUEST_URI} !^/fps/public/
    RewriteRule ^(.*)$ /fps/public/$1 [L]
</IfModule>
```

Save and exit.

### Option B: Update Laravel's public/.htaccess

Edit `public_html/fps/public/.htaccess`:

```bash
nano ~/public_html/fps/public/.htaccess
```

Update the rewrite base:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    RewriteBase /fps/

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
```

### Option C: Subdomain Configuration (Alternative Approach)

For cleaner URLs, consider creating a subdomain:
- Subdomain: `fps.csjones.co`
- Document root: `public_html/fps/public/`

This avoids the `/fps` path but requires DNS/subdomain setup in cPanel.

---

## Step 9: Run Migrations and Seeders

### 9.1 Run Database Migrations

```bash
cd ~/public_html/fps
php artisan migrate --force
```

The `--force` flag is required in production environments.

### 9.2 Seed Tax Configuration

```bash
php artisan db:seed --class=TaxConfigurationSeeder --force
```

This populates UK tax rules (NRB, RNRB, ISA allowances, etc.).

### 9.3 Verify Database

Check that tables were created:

```bash
php artisan tinker
>>> \DB::select('SHOW TABLES');
>>> exit
```

You should see all FPS tables (users, investments, trusts, gifts, etc.).

---

## Step 10: Optimize for Production

### 10.1 Cache Configuration

```bash
cd ~/public_html/fps

# Cache config files
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### 10.2 Optimize Autoloader

```bash
composer dump-autoload --optimize
```

### 10.3 Clear Old Caches (if redeploying)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Step 11: Set File Permissions

Proper file permissions are critical for security and functionality.

### 11.1 Set Ownership (if you have sudo access)

```bash
# Set web server user as owner (usually 'nobody' or 'www-data' on SiteGround)
chown -R $USER:$USER ~/public_html/fps
```

### 11.2 Set Directory Permissions

```bash
cd ~/public_html/fps

# Set general permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Set artisan as executable
chmod 755 artisan

# Make storage and cache writable
chmod -R 775 storage bootstrap/cache
```

### 11.3 Protect Sensitive Files

```bash
# Protect .env file
chmod 600 .env

# Protect database file if using SQLite (not applicable for MySQL)
# chmod 600 database/database.sqlite
```

---

## Step 12: Test Deployment

### 12.1 Access the Application

Visit: **https://csjones.co/fps**

You should see the FPS login/register page.

### 12.2 Register a Test User

1. Click **Register**
2. Create a test account
3. Verify email functionality (if configured)
4. Log in

### 12.3 Test Key Features

- [ ] Dashboard loads correctly
- [ ] Navigation works (Protection, Savings, Investment, Retirement, Estate)
- [ ] Create test data in each module
- [ ] Verify calculations (IHT, Monte Carlo, etc.)
- [ ] Check trust creation and IHT planning
- [ ] Verify charts render correctly (ApexCharts)
- [ ] Test responsive design on mobile

### 12.4 Check Logs for Errors

```bash
tail -f ~/public_html/fps/storage/logs/laravel.log
```

If you see errors, troubleshoot using the guide below.

---

## Troubleshooting

### Issue 1: 500 Internal Server Error

**Possible Causes:**
- `.env` file missing or misconfigured
- File permissions incorrect
- PHP extensions missing
- `.htaccess` misconfigured

**Solutions:**
```bash
# Check Laravel logs
tail -100 ~/public_html/fps/storage/logs/laravel.log

# Verify .env exists
ls -la ~/public_html/fps/.env

# Check file permissions
ls -la ~/public_html/fps/storage

# Test PHP
php -v
php -m  # List installed modules
```

### Issue 2: Database Connection Error

**Error:** `SQLSTATE[HY000] [1045] Access denied`

**Solutions:**
```bash
# Verify database credentials in .env
cat ~/public_html/fps/.env | grep DB_

# Test database connection
php artisan tinker
>>> \DB::connection()->getPdo();
```

If connection fails, verify:
- Database name is correct
- Username has privileges
- Password is correct
- Host is correct (usually `localhost`)

### Issue 3: CSS/JS Assets Not Loading

**Symptoms:** Page loads but has no styling, JavaScript errors in console

**Cause:** Asset paths incorrect for subfolder deployment

**Solutions:**

1. Verify `ASSET_URL` in `.env`:
```env
ASSET_URL=https://csjones.co/fps
```

2. Clear config cache:
```bash
php artisan config:clear
php artisan config:cache
```

3. Check `public/build/manifest.json` exists:
```bash
ls -la ~/public_html/fps/public/build/
```

4. Verify `.htaccess` rewrite rules (see Step 8)

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

Set up automated backups via cPanel:

1. Go to **Backup Wizard** in cPanel
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

**Last Updated**: October 15, 2025
**Version**: 1.0.0
