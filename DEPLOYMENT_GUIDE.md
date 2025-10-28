# FPS v0.1.2.12 - Production Deployment Guide

**Target Environment**: SiteGround Shared Hosting
**URL**: https://c.jones.co/fpsv2
**Server Location**: From project root (`index.php` in root directory)
**Date**: October 28, 2025

---

## Table of Contents

1. [Pre-Deployment Checklist](#1-pre-deployment-checklist)
2. [Local Build Preparation](#2-local-build-preparation)
3. [SiteGround cPanel Setup](#3-siteground-cpanel-setup)
4. [File Upload](#4-file-upload)
5. [Database Setup & Migration](#5-database-setup--migration)
6. [Environment Configuration](#6-environment-configuration)
7. [Post-Deployment Setup](#7-post-deployment-setup)
8. [Testing & Verification](#8-testing--verification)
9. [Troubleshooting](#9-troubleshooting)
10. [Maintenance & Updates](#10-maintenance--updates)

---

## 1. Pre-Deployment Checklist

Before starting deployment, ensure you have:

- [x] SiteGround cPanel login credentials
- [x] FTP/SFTP access details (or SSH if available)
- [x] Domain configured: `c.jones.co`
- [x] SSL certificate installed for HTTPS
- [x] PHP 8.2+ enabled in cPanel
- [x] MySQL 8.0+ database access
- [x] Email account created: `noreply@c.jones.co`
- [x] Access to this project's root directory

---

## 2. Local Build Preparation

### Step 1: Build Production Assets

```bash
cd /Users/Chris/Desktop/fpsV2

# Build frontend assets with Vite
NODE_ENV=production npm run build
```

**Expected Output**: Production assets will be created in `public/build/` directory

**Verify**:
```bash
ls -la public/build/
# Should see manifest.json and assets/ folder
```

### Step 2: Optimize Composer for Production

```bash
# Install production dependencies (no dev packages)
composer install --optimize-autoloader --no-dev

# Generate optimized autoloader
composer dump-autoload --optimize
```

**Important**: After deployment, you can reinstall dev dependencies locally:
```bash
composer install
```

### Step 3: Clear and Optimize Laravel

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate optimized config cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 4: Create Production Environment File

Copy `.env.production.example` to `.env`:
```bash
cp .env.production.example .env
```

**Edit `.env` and update**:
- `APP_KEY` (generate with `php artisan key:generate --show`)
- Database credentials (from SiteGround)
- Email credentials (from SiteGround)
- All `YOUR_*` placeholders

### Step 5: Create Deployment Package

Create a list of files/folders to upload:

**Include**:
- ✅ `app/`
- ✅ `bootstrap/`
- ✅ `config/`
- ✅ `database/` (migrations, seeders)
- ✅ `public/` (including `public/build/`)
- ✅ `resources/` (views, lang files)
- ✅ `routes/`
- ✅ `storage/` (framework, logs, app folders)
- ✅ `vendor/` (Composer dependencies)
- ✅ `.env` (production configured)
- ✅ `artisan`
- ✅ `composer.json`
- ✅ `composer.lock`
- ✅ `index.php` (in root)
- ✅ `.htaccess` (if using Apache)

**Exclude** (not needed on server):
- ❌ `node_modules/`
- ❌ `.git/`
- ❌ `tests/`
- ❌ `.env.example`
- ❌ `package.json`
- ❌ `package-lock.json`
- ❌ `vite.config.js`
- ❌ `.claude/`
- ❌ `docs/`
- ❌ `tasks/`
- ❌ `*.md` files (optional, but DEPLOYMENT_GUIDE.md might be useful)

---

## 3. SiteGround cPanel Setup

### Step 1: Configure PHP Version

1. Log in to SiteGround cPanel
2. Navigate to **Site Tools** → **Devs** → **PHP Manager**
3. Select your domain: `c.jones.co`
4. Set PHP version to **8.2** or higher
5. Click **Confirm**

### Step 2: Enable Required PHP Extensions

In **PHP Manager** → **PHP Options**, ensure these extensions are enabled:
- ✅ BCMath
- ✅ Ctype
- ✅ Fileinfo
- ✅ JSON
- ✅ Mbstring
- ✅ OpenSSL
- ✅ PDO
- ✅ PDO_MySQL
- ✅ Tokenizer
- ✅ XML
- ✅ cURL
- ✅ Zip

### Step 3: Set PHP Memory Limit

In **PHP Manager** → **PHP Options**:
- **memory_limit**: 256M (minimum), 512M (recommended)
- **max_execution_time**: 300 (for Monte Carlo simulations)
- **max_input_time**: 300
- **post_max_size**: 50M
- **upload_max_filesize**: 50M

### Step 4: Create MySQL Database

1. Navigate to **Site Tools** → **MySQL** → **Databases**
2. Click **Create Database**
3. Database name: e.g., `fpsv2_production`
4. Click **Create**

### Step 5: Create MySQL User

1. In **MySQL** → **Databases**
2. Click **Create User**
3. Username: e.g., `fps_user`
4. Password: **Generate strong password** (save it securely!)
5. Click **Create**

### Step 6: Assign User to Database

1. In **MySQL** → **Databases**
2. Find **Add User To Database** section
3. Select user: `fps_user`
4. Select database: `fpsv2_production`
5. Grant **ALL PRIVILEGES**
6. Click **Add**

**Save these credentials**:
```
DB_HOST=localhost
DB_DATABASE=fpsv2_production
DB_USERNAME=fps_user
DB_PASSWORD=YOUR_GENERATED_PASSWORD
```

### Step 7: Create Email Account

1. Navigate to **Email** → **Accounts**
2. Click **Create Email Account**
3. Email: `noreply@c.jones.co`
4. Password: **Generate strong password**
5. Click **Create**

**Save these credentials**:
```
MAIL_HOST=mail.c.jones.co
MAIL_USERNAME=noreply@c.jones.co
MAIL_PASSWORD=YOUR_EMAIL_PASSWORD
```

---

## 4. File Upload

### Option A: FTP/SFTP Upload (Recommended for Initial Deployment)

#### Using FileZilla or Similar FTP Client:

1. **Connect to SiteGround**:
   - Host: `ftp.c.jones.co` (or provided SFTP host)
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Port: 21 (FTP) or 22 (SFTP)

2. **Navigate to Subdirectory**:
   - Remote path: `/home/USERNAME/public_html/fpsv2/`
   - If `fpsv2` doesn't exist, create it

3. **Upload Files**:
   - Upload ALL files from Step 5 of Local Build Preparation
   - **Important**: Maintain directory structure!
   - This may take 15-30 minutes depending on connection speed

4. **Set Permissions**:
   ```
   chmod 755 /public_html/fpsv2/
   chmod 755 /public_html/fpsv2/storage/
   chmod 755 /public_html/fpsv2/storage/framework/
   chmod 755 /public_html/fpsv2/storage/framework/cache/
   chmod 755 /public_html/fpsv2/storage/framework/sessions/
   chmod 755 /public_html/fpsv2/storage/framework/views/
   chmod 755 /public_html/fpsv2/storage/logs/
   chmod 755 /public_html/fpsv2/bootstrap/cache/
   ```

   Make storage and cache directories writable:
   ```
   chmod -R 775 /public_html/fpsv2/storage/
   chmod -R 775 /public_html/fpsv2/bootstrap/cache/
   ```

### Option B: SSH Upload (If SSH Access Enabled)

If SiteGround provides SSH access:

```bash
# From your local machine
cd /Users/Chris/Desktop/

# Create tarball (excluding unnecessary files)
tar -czf fpsv2.tar.gz \
  --exclude='fpsV2/node_modules' \
  --exclude='fpsV2/.git' \
  --exclude='fpsV2/tests' \
  --exclude='fpsV2/.claude' \
  --exclude='fpsV2/docs' \
  --exclude='fpsV2/tasks' \
  fpsV2/

# Upload to server
scp fpsv2.tar.gz USERNAME@c.jones.co:~/

# SSH into server
ssh USERNAME@c.jones.co

# Extract to correct location
cd ~/public_html/
mkdir -p fpsv2
tar -xzf ~/fpsv2.tar.gz -C fpsv2/ --strip-components=1

# Set permissions
chmod -R 775 fpsv2/storage/
chmod -R 775 fpsv2/bootstrap/cache/
```

---

## 5. Database Setup & Migration

### Step 1: Access Server Terminal

**Via SSH** (if available):
```bash
ssh USERNAME@c.jones.co
cd ~/public_html/fpsv2/
```

**Via cPanel Terminal**:
1. Navigate to **Advanced** → **Terminal**
2. Click **Open Terminal**
3. Run: `cd ~/public_html/fpsv2/`

### Step 2: Verify Database Connection

```bash
php artisan db:show
```

**Expected Output**:
```
MySQL 8.0.x
Database: fpsv2_production
```

If connection fails, check `.env` database credentials.

### Step 3: Run Database Migrations

**IMPORTANT**: This creates all 40+ database tables required for FPS.

```bash
php artisan migrate --force
```

**Expected Output**:
```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (XX.XXms)
Migrating: 2014_10_12_100000_create_password_reset_tokens_table
Migrated:  2014_10_12_100000_create_password_reset_tokens_table (XX.XXms)
...
(continues for all migrations)
```

**List of Tables Created** (40+ tables):

**Core Tables**:
- `users`
- `password_reset_tokens`
- `failed_jobs`
- `personal_access_tokens`
- `jobs` (queue system)
- `family_members`

**Protection Module**:
- `protection_profiles`
- `life_insurance_policies`
- `critical_illness_policies`
- `income_protection_policies`

**Savings Module**:
- `savings_accounts`
- `savings_goals`
- `isa_allowance_tracking`

**Investment Module**:
- `investment_accounts`
- `holdings`
- `investment_goals`
- `risk_profiles`
- `monte_carlo_simulations`

**Retirement Module**:
- `dc_pensions`
- `db_pensions`
- `state_pensions`
- `retirement_profiles`

**Estate Planning Module**:
- `assets`
- `liabilities`
- `properties`
- `mortgages`
- `business_interests`
- `chattels`
- `gifts`
- `trusts`
- `iht_profiles`
- `wills`
- `bequests`

**Configuration**:
- `tax_configurations`
- `life_expectancy_tables`
- `spouse_permissions`

**Verify All Tables Created**:
```bash
php artisan db:table --database=mysql
```

Should show all 40+ tables.

### Step 4: Seed Essential Configuration Data

```bash
# Seed UK tax configuration (2025/26 tax year)
php artisan db:seed --class=TaxConfigurationSeeder --force
```

**Expected Output**:
```
Seeding: TaxConfigurationSeeder
Seeded:  TaxConfigurationSeeder (XX.XXms)
```

**This seeds**:
- Income tax bands (£12,570 personal allowance, 20%, 40%, 45%)
- National Insurance thresholds
- ISA allowance (£20,000)
- Pension annual allowance (£60,000)
- IHT: NRB £325k, RNRB £175k
- Life expectancy tables

### Step 5: Create Admin Account

```bash
php artisan tinker
```

Then run:
```php
$admin = new \App\Models\User();
$admin->name = 'FPS Admin';
$admin->email = 'admin@fps.com';
$admin->password = bcrypt('YOUR_SECURE_PASSWORD_HERE');
$admin->email_verified_at = now();
$admin->is_admin = true;
$admin->save();

echo "Admin created with ID: " . $admin->id;
exit
```

**Save admin credentials**:
- Email: `admin@fps.com`
- Password: YOUR_SECURE_PASSWORD_HERE

---

## 6. Environment Configuration

### Step 1: Edit Production .env File

Using cPanel File Manager or FTP:

1. Navigate to `/public_html/fpsv2/`
2. Edit `.env` file
3. Update all configuration values:

```ini
APP_NAME="FPS - Financial Planning System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY
APP_DEBUG=false
APP_URL=https://c.jones.co/fpsv2

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=fpsv2_production
DB_USERNAME=fps_user
DB_PASSWORD=YOUR_DATABASE_PASSWORD

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=c.jones.co
SESSION_PATH=/fpsv2

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.c.jones.co
MAIL_PORT=587
MAIL_USERNAME=noreply@c.jones.co
MAIL_PASSWORD=YOUR_EMAIL_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@c.jones.co
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=c.jones.co
```

### Step 2: Generate Application Key

**If not already generated**:

```bash
php artisan key:generate --force
```

This updates `APP_KEY` in `.env` file.

### Step 3: Cache Configuration

```bash
# Cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Expected Output**:
```
Configuration cache cleared successfully.
Configuration cached successfully.

Route cache cleared successfully.
Routes cached successfully.

Compiled views cleared successfully.
Blade templates cached successfully.
```

---

## 7. Post-Deployment Setup

### Step 1: Configure .htaccess for Subdirectory

Since the app is served from `/fpsv2/` subdirectory, ensure proper `.htaccess`:

**Root `.htaccess`** (`/public_html/fpsv2/.htaccess`):

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller (index.php in root)
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Public folder .htaccess** (`/public_html/fpsv2/public/.htaccess`):

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews
    </IfModule>

    RewriteEngine On

    # Redirect to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/fpsv2/$1 [L,R=301]

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

### Step 2: Set Correct File Permissions

```bash
# Make sure these are correct
find /public_html/fpsv2/storage -type f -exec chmod 664 {} \;
find /public_html/fpsv2/storage -type d -exec chmod 775 {} \;
find /public_html/fpsv2/bootstrap/cache -type f -exec chmod 664 {} \;
find /public_html/fpsv2/bootstrap/cache -type d -exec chmod 775 {} \;
```

### Step 3: Set Up Cron Job for Queue Worker

**Important**: Monte Carlo simulations run as background jobs.

1. Navigate to **cPanel** → **Advanced** → **Cron Jobs**
2. Add new cron job:
   - **Frequency**: Every minute (`* * * * *`)
   - **Command**:
     ```bash
     cd /home/USERNAME/public_html/fpsv2 && /usr/bin/php artisan queue:work --stop-when-empty --max-time=3600
     ```
3. Click **Add New Cron Job**

**Alternative** (process queue every 5 minutes):
```bash
*/5 * * * * cd /home/USERNAME/public_html/fpsv2 && /usr/bin/php artisan queue:work --stop-when-empty
```

### Step 4: Set Up Daily Cleanup Cron

1. Add another cron job:
   - **Frequency**: Daily at 2 AM (`0 2 * * *`)
   - **Command**:
     ```bash
     cd /home/USERNAME/public_html/fpsv2 && /usr/bin/php artisan queue:prune-batches && /usr/bin/php artisan cache:prune-stale-tags
     ```

---

## 8. Testing & Verification

### Step 1: Verify Website Loads

1. Open browser
2. Navigate to: `https://c.jones.co/fpsv2`
3. Should see FPS landing page with:
   - Hero section
   - Module cards (Protection, Savings, Investment, Retirement, Estate)
   - Login/Register buttons

**If you see errors**:
- Check browser console for JavaScript errors
- Check Laravel logs: `/storage/logs/laravel.log`

### Step 2: Test User Registration

1. Click **Register**
2. Fill in registration form:
   - Name
   - Email
   - Password (min 8 characters)
   - Confirm password
3. Click **Register**
4. Should redirect to onboarding or dashboard

### Step 3: Test Admin Login

1. Navigate to: `https://c.jones.co/fpsv2/login`
2. Login with admin credentials:
   - Email: `admin@fps.com`
   - Password: YOUR_SECURE_PASSWORD
3. Should access admin panel at `/admin`

### Step 4: Test Database Connection

```bash
php artisan db:show
```

Should display database info without errors.

### Step 5: Test API Endpoints

```bash
# Test health check
curl https://c.jones.co/fpsv2/api/health

# Should return: {"status":"ok","timestamp":"..."}
```

### Step 6: Test Email (Optional)

```bash
php artisan tinker
```

```php
Mail::raw('Test email from FPS', function($message) {
    $message->to('YOUR_EMAIL@example.com')
            ->subject('FPS Test Email');
});
exit
```

Check your inbox for test email.

### Step 7: Test Queue Worker

1. Login to dashboard
2. Navigate to Investment module
3. Try creating Monte Carlo simulation
4. Should queue job successfully
5. Check queue status:
   ```bash
   php artisan queue:work --once
   ```

### Step 8: Check File Permissions

```bash
# Storage should be writable
ls -la storage/
ls -la storage/logs/
ls -la bootstrap/cache/

# Should see 775 for directories, 664 for files
```

---

## 9. Troubleshooting

### Problem 1: 500 Internal Server Error

**Symptoms**: White screen or generic 500 error

**Solutions**:
1. Check Laravel logs:
   ```bash
   tail -n 50 storage/logs/laravel.log
   ```

2. Check permissions:
   ```bash
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

3. Clear all caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. Check `.env` file exists and is readable

5. Verify `APP_KEY` is set in `.env`

### Problem 2: Assets Not Loading (404 on CSS/JS)

**Symptoms**: Page loads but no styling, broken layout

**Solutions**:
1. Verify `public/build/` folder exists and contains assets

2. Check `.env`:
   ```ini
   ASSET_URL=/fpsv2
   APP_URL=https://c.jones.co/fpsv2
   ```

3. Rebuild assets locally and re-upload:
   ```bash
   npm run build
   # Upload public/build/ via FTP
   ```

4. Check browser console for exact 404 URLs

### Problem 3: Database Connection Failed

**Symptoms**: SQLSTATE[HY000] [2002] Connection refused

**Solutions**:
1. Verify database credentials in `.env`:
   ```bash
   DB_HOST=localhost
   DB_DATABASE=fpsv2_production
   DB_USERNAME=fps_user
   DB_PASSWORD=correct_password
   ```

2. Test connection from cPanel:
   - Navigate to **phpMyAdmin**
   - Try logging in with database credentials

3. Check if database exists:
   ```bash
   php artisan db:show
   ```

4. Verify MySQL is running (contact SiteGround support if not)

### Problem 4: CSRF Token Mismatch

**Symptoms**: 419 Page Expired error on form submissions

**Solutions**:
1. Check session configuration in `.env`:
   ```ini
   SESSION_DRIVER=file
   SESSION_DOMAIN=c.jones.co
   SESSION_PATH=/fpsv2
   SESSION_SECURE_COOKIE=true
   ```

2. Clear session files:
   ```bash
   rm -rf storage/framework/sessions/*
   php artisan cache:clear
   ```

3. Verify cookies are enabled in browser

4. Check if HTTPS is properly configured

### Problem 5: Queue Jobs Not Processing

**Symptoms**: Monte Carlo simulations stuck in queue

**Solutions**:
1. Check cron job is set up correctly:
   ```bash
   crontab -l
   # Should see queue:work cron job
   ```

2. Manually process queue:
   ```bash
   php artisan queue:work --once
   ```

3. Check failed jobs:
   ```bash
   php artisan queue:failed
   ```

4. Retry failed jobs:
   ```bash
   php artisan queue:retry all
   ```

### Problem 6: Email Not Sending

**Symptoms**: Password reset emails not arriving

**Solutions**:
1. Verify email configuration in `.env`:
   ```ini
   MAIL_MAILER=smtp
   MAIL_HOST=mail.c.jones.co
   MAIL_PORT=587
   MAIL_USERNAME=noreply@c.jones.co
   MAIL_PASSWORD=correct_password
   MAIL_ENCRYPTION=tls
   ```

2. Test email account login via webmail

3. Check Laravel logs for email errors:
   ```bash
   tail -n 100 storage/logs/laravel.log | grep -i mail
   ```

4. Try changing port:
   - Port 587 (TLS)
   - Port 465 (SSL)
   - Port 25 (unencrypted)

### Problem 7: Storage Directory Not Writable

**Symptoms**: Logs not writing, cache errors

**Solutions**:
1. Set correct permissions:
   ```bash
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

2. Check ownership (via SSH or cPanel File Manager):
   ```bash
   chown -R USERNAME:USERNAME storage/
   chown -R USERNAME:USERNAME bootstrap/cache/
   ```

3. Verify web server user has write access

---

## 10. Maintenance & Updates

### Regular Maintenance Tasks

**Weekly**:
- Check Laravel logs for errors:
  ```bash
  tail -n 100 storage/logs/laravel.log
  ```
- Check disk space usage
- Monitor database size

**Monthly**:
- Rotate log files:
  ```bash
  php artisan log:clear
  ```
- Optimize database:
  ```bash
  php artisan db:prune
  ```
- Clear stale cache:
  ```bash
  php artisan cache:prune-stale-tags
  ```

### Updating to New Version

1. **Backup Database**:
   - Login to admin panel: `/admin`
   - Navigate to **System** → **Backup/Restore**
   - Click **Create Full Backup**
   - Download backup file

2. **Backup Files**:
   ```bash
   # Via SSH
   cd ~/public_html/
   tar -czf fpsv2-backup-$(date +%Y%m%d).tar.gz fpsv2/
   ```

3. **Upload New Files**:
   - Upload updated files via FTP
   - Overwrite existing files (except `.env`)

4. **Run Migrations**:
   ```bash
   php artisan migrate --force
   ```

5. **Clear Caches**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. **Test Application**:
   - Verify landing page loads
   - Test login
   - Test key features

### Database Backup Schedule

**Automated Backups** (via cron):

Create cron job:
- **Frequency**: Daily at 3 AM (`0 3 * * *`)
- **Command**:
  ```bash
  /usr/bin/mysqldump -u fps_user -p'PASSWORD' fpsv2_production | gzip > /home/USERNAME/backups/fps_$(date +\%Y\%m\%d).sql.gz
  ```

**Retention**:
- Keep last 7 daily backups
- Keep last 4 weekly backups
- Keep last 3 monthly backups

### Security Best Practices

1. **Update Dependencies**:
   ```bash
   composer update --with-all-dependencies
   npm update
   ```

2. **Monitor Failed Login Attempts**:
   ```bash
   tail -f storage/logs/laravel.log | grep -i "failed login"
   ```

3. **Enable HTTPS Redirect** (in `.htaccess`):
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

4. **Set Security Headers** (in `.htaccess`):
   ```apache
   Header always set X-Frame-Options "SAMEORIGIN"
   Header always set X-Content-Type-Options "nosniff"
   Header always set X-XSS-Protection "1; mode=block"
   ```

5. **Limit Database User Permissions**:
   - Only grant necessary privileges
   - Don't use root user for application

---

## Deployment Summary Checklist

Use this final checklist before going live:

- [ ] PHP 8.2+ enabled
- [ ] All PHP extensions enabled
- [ ] MySQL database created
- [ ] Database user created and assigned
- [ ] Email account created
- [ ] All files uploaded to `/public_html/fpsv2/`
- [ ] Storage directories writable (775)
- [ ] `.env` file configured with production values
- [ ] `APP_KEY` generated
- [ ] Database migrations run successfully (40+ tables)
- [ ] Tax configuration seeded
- [ ] Admin account created
- [ ] Configuration cached (`config:cache`, `route:cache`, `view:cache`)
- [ ] `.htaccess` files configured
- [ ] Cron jobs set up (queue worker)
- [ ] SSL certificate installed and HTTPS working
- [ ] Landing page loads at `https://c.jones.co/fpsv2`
- [ ] User registration works
- [ ] Admin login works
- [ ] API endpoints responding
- [ ] Email sending configured (test sent)
- [ ] Queue worker processing jobs
- [ ] All 5 modules accessible (Protection, Savings, Investment, Retirement, Estate)
- [ ] Database backup created
- [ ] Monitoring/logging configured
- [ ] Security headers configured

---

## Support & Resources

**Documentation**:
- `CLAUDE.md` - Development guidelines
- `OCTOBER_2025_FEATURES_UPDATE.md` - Recent features and changes
- `README.md` - Project overview

**SiteGround Support**:
- Support Portal: https://my.siteground.com/support
- Phone: Check your welcome email
- Live Chat: Available in cPanel

**Laravel Documentation**:
- Deployment: https://laravel.com/docs/10.x/deployment
- Configuration: https://laravel.com/docs/10.x/configuration
- Queues: https://laravel.com/docs/10.x/queues

---

**Deployment Guide Version**: 1.0
**FPS Version**: v0.1.2.12
**Last Updated**: October 28, 2025
**Deployment Target**: SiteGround Shared Hosting (c.jones.co/fpsv2)

---

🤖 **Generated with [Claude Code](https://claude.com/claude-code)**
