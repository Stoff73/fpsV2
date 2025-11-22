# FPS Deployment - Ready to Deploy

**Target URL**: https://csjones.co/fpsv2
**Date Prepared**: October 24, 2025
**Version**: v0.1.2.2

---

## ✅ Pre-Deployment Checklist - COMPLETED

- [x] Vite configuration updated for `/fpsv2/build/` base path
- [x] Production assets built (`npm run build`)
- [x] Manifest symlink created (`public/build/manifest.json → .vite/manifest.json`)
- [x] Production composer dependencies installed (`--no-dev --optimize-autoloader`)
- [x] Production `.env.production` template created

---

## 📦 What's Been Built

### 1. Frontend Assets (Production-Ready)
- **Location**: `public/build/`
- **Total Size**: ~1.2 MB (gzipped: ~350 KB)
- **Main Bundle**: `app-D1_y5H1a.js` (842 KB / 235 KB gzipped)
- **CSS Bundle**: `css-BDH0iljc.css` (57 KB / 9.4 KB gzipped)
- **Total Assets**: 43 files (JS + CSS)

### 2. Backend Dependencies
- **Composer packages**: Production-only (dev dependencies removed)
- **Optimized autoloader**: Generated for production
- **Removed packages**: 49 dev packages (PHPUnit, Pest, Laravel Pint, etc.)

### 3. Configuration Files
- **`.env.production`**: Template with all production settings
- **`vite.config.js`**: Configured for subdirectory deployment
- **`public/build/manifest.json`**: Symlinked correctly

---

## 🚀 Deployment Steps to SiteGround

### Step 1: Upload Files via SSH/SFTP

**Upload these directories and files to**: `~/www/csjones.co/public_html/fps_laravel/`

#### Required Files/Directories:
```
fpsV2/
├── app/                    # All Laravel application code
├── bootstrap/              # Framework bootstrap files
├── config/                 # Configuration files
├── database/               # Migrations and seeders
├── public/                 # Public web directory (IMPORTANT!)
│   ├── build/              # Production assets (from npm run build)
│   ├── index.php           # Laravel entry point
│   └── .htaccess           # Web server configuration
├── resources/              # Views and raw assets
├── routes/                 # Route definitions
├── storage/                # App storage (logs, cache, sessions)
├── vendor/                 # Composer dependencies (from composer install --no-dev)
├── .env.production         # Rename to .env on server
├── artisan                 # Artisan CLI
└── composer.json           # Dependency definitions
```

#### Files to EXCLUDE from upload:
```
❌ node_modules/           # Don't upload (huge, not needed)
❌ tests/                   # Don't upload (testing files)
❌ .git/                    # Don't upload (git repository)
❌ .env                     # Don't upload (local environment)
❌ *.md                     # Don't upload (documentation files)
❌ storage/logs/*           # Don't upload (old logs)
❌ storage/framework/cache/* # Don't upload (old cache)
```

### Step 2: SSH Into SiteGround

```bash
# Connect via SSH (see deployment.md for SSH setup instructions)
ssh -i ~/.ssh/siteground_key -p 18765 u163-ptanegf9edny@uk71.siteground.eu
```

### Step 3: Set Up Directory Structure with Symlink

```bash
# Navigate to public_html
cd ~/www/csjones.co/public_html

# Verify fps_laravel directory exists and contains uploaded files
ls -la fps_laravel/

# Create symbolic link (fpsv2 → fps_laravel/public)
ln -s fps_laravel/public fpsv2

# Verify symlink created correctly
ls -la fpsv2
# Should show: fpsv2 -> fps_laravel/public

# Verify symlink works
ls -la fpsv2/
# Should show contents of fps_laravel/public/ (index.php, .htaccess, build/, etc.)
```

### Step 4: Configure Environment

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Copy production environment template to .env
cp .env.production .env

# Edit .env with nano or vim
nano .env

# Update these critical values:
# - DB_DATABASE=YOUR_DATABASE_NAME
# - DB_USERNAME=YOUR_DATABASE_USER
# - DB_PASSWORD=YOUR_DATABASE_PASSWORD
# - MAIL_* settings (if using email)

# Save and exit (Ctrl+X, Y, Enter)

# Generate application key
php artisan key:generate
```

### Step 5: Create Database

**Via SiteGround Site Tools**:
1. Log in to Site Tools (https://tools.siteground.com)
2. Go to **MySQL Databases**
3. Create database: `csjones_fps`
4. Create user: `csjones_fpsuser`
5. Set strong password
6. Add user to database with **ALL PRIVILEGES**

### Step 6: Run Migrations and Seeders

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Test database connection
php artisan tinker
>>> \DB::connection()->getPdo();
>>> exit

# Run migrations
php artisan migrate --force

# Seed UK tax configuration (CRITICAL - contains all tax rules)
php artisan db:seed --class=TaxConfigurationSeeder --force

# (Optional) Seed demo user for testing
php artisan db:seed --class=DemoUserSeeder --force
# Demo credentials: demo@fps.com / password

# Verify database setup
php artisan tinker
>>> \DB::select('SHOW TABLES');
>>> \App\Models\User::count();
>>> exit
```

### Step 7: Create Manifest Symlink (CRITICAL!)

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Create symlink for Vite manifest
ln -s .vite/manifest.json public/build/manifest.json

# Verify symlink
ls -la public/build/manifest.json
# Should show: manifest.json -> .vite/manifest.json

# Test it works
cat public/build/manifest.json | head -5
# Should show JSON content
```

### Step 8: Set File Permissions

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Set general permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Make artisan executable
chmod 755 artisan

# Make storage and bootstrap/cache writable
chmod -R 775 storage bootstrap/cache

# Protect .env file
chmod 600 .env
```

### Step 9: Optimize for Production

```bash
cd ~/www/csjones.co/public_html/fps_laravel

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 10: Test Deployment

Visit: **https://csjones.co/fpsv2**

**Expected Result**: FPS login/register page with proper styling

**Check**:
- [ ] Page loads without errors
- [ ] All CSS/JS assets load (check browser DevTools Network tab)
- [ ] No 404 errors for `/fpsv2/build/assets/*` files
- [ ] No MIME type errors in console
- [ ] Can register a new user
- [ ] Can log in
- [ ] Dashboard loads correctly with charts
- [ ] All module pages accessible (Protection, Savings, Investment, Retirement, Estate, UK Taxes)

---

## 🔧 Troubleshooting Common Issues

### Issue 1: Vite Manifest Not Found (500 Error)

**Error**: `Vite manifest not found at: .../public/build/manifest.json`

**Solution**:
```bash
cd ~/www/csjones.co/public_html/fps_laravel
ln -s .vite/manifest.json public/build/manifest.json
php artisan view:clear
```

### Issue 2: Assets Not Loading (404 on CSS/JS)

**Symptoms**: Page loads but no styling, 404 errors for `/fpsv2/build/assets/*`

**Check**:
1. Verify `ASSET_URL` in `.env`:
   ```env
   ASSET_URL=https://csjones.co/fpsv2
   ```
2. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```
3. Verify build directory exists:
   ```bash
   ls -la public/build/assets/ | head
   ```

### Issue 3: CSRF Token Mismatch (419 Error)

**Error**: `CSRF token mismatch` or `419 Page Expired`

**Solution**: Update `.env`:
```env
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.csjones.co
SANCTUM_STATEFUL_DOMAINS=csjones.co,www.csjones.co
```

Then clear config:
```bash
php artisan config:clear
php artisan config:cache
```

### Issue 4: Database Connection Failed

**Error**: `SQLSTATE[HY000] [1045] Access denied`

**Solution**:
1. Verify database credentials in `.env`
2. Test connection:
   ```bash
   php artisan tinker
   >>> \DB::connection()->getPdo();
   ```
3. Check database user has correct privileges in Site Tools

### Issue 5: Storage Permissions Error

**Error**: `The stream or file "storage/logs/laravel.log" could not be opened`

**Solution**:
```bash
cd ~/www/csjones.co/public_html/fps_laravel
chmod -R 775 storage bootstrap/cache
```

---

## 📊 Production Environment Details

### Application Settings
- **Environment**: production
- **Debug Mode**: false (disabled)
- **URL**: https://csjones.co/fpsv2
- **Asset URL**: https://csjones.co/fpsv2

### Database
- **Connection**: mysql
- **Host**: localhost
- **Database**: csjones_fps
- **Tables**: 40+ tables (users, protection, savings, investment, retirement, estate, etc.)

### Cache & Sessions
- **Cache Driver**: file
- **Session Driver**: file
- **Queue**: database

### Security
- **HTTPS**: Required
- **Secure Cookies**: Enabled
- **Session Domain**: .csjones.co
- **Sanctum Stateful Domains**: csjones.co

---

## 📈 Post-Deployment Verification

### 1. Check Application Logs
```bash
tail -50 ~/www/csjones.co/public_html/fps_laravel/storage/logs/laravel.log
```

### 2. Test Key Features
- [ ] User registration
- [ ] User login
- [ ] Dashboard loads
- [ ] Create Protection profile
- [ ] Add savings account
- [ ] Add investment account
- [ ] Add pension
- [ ] Estate planning IHT calculation
- [ ] UK Taxes page loads correctly
- [ ] Charts render (ApexCharts)
- [ ] Forms submit without errors

### 3. Performance Check
- [ ] Page load time < 3 seconds
- [ ] No console errors in browser DevTools
- [ ] All assets loading with HTTP 200 status
- [ ] Database queries executing quickly

---

## 🔄 Updating the Application

When pushing updates:

```bash
# SSH into server
ssh -i ~/.ssh/siteground_key -p 18765 u163-ptanegf9edny@uk71.siteground.eu

# Navigate to project
cd ~/www/csjones.co/public_html/fps_laravel

# Pull latest changes (if using git)
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
```

**If frontend assets changed**: Rebuild locally with `npm run build`, then upload new `public/build/` directory via SFTP.

---

## 📞 Support Information

**Application**: Financial Planning System (FPS)
**Version**: v0.1.2.2
**Tech Stack**: Laravel 10.x + Vue.js 3 + MySQL 8.0
**Documentation**: See `deployment.md` for detailed SiteGround deployment guide

---

## ✅ Deployment Checklist Summary

- [x] **Local Build Complete**: Production assets built with correct base path
- [x] **Dependencies Optimized**: Production composer packages installed
- [x] **Configuration Ready**: `.env.production` template created
- [ ] **Files Uploaded**: Upload to `~/www/csjones.co/public_html/fps_laravel/`
- [ ] **Symlink Created**: `fpsv2 → fps_laravel/public`
- [ ] **Environment Configured**: `.env` file set up with database credentials
- [ ] **Database Created**: MySQL database and user created in Site Tools
- [ ] **Migrations Run**: Database schema created
- [ ] **Tax Config Seeded**: UK tax rules populated
- [ ] **Manifest Symlink**: `public/build/manifest.json` symlinked
- [ ] **Permissions Set**: File and directory permissions configured
- [ ] **Production Optimized**: Caches created
- [ ] **Tested**: Application accessible and functional at https://csjones.co/fpsv2

---

**Ready to Deploy!** 🚀

Follow the steps above in order, and your FPS application will be live at https://csjones.co/fpsv2.
