# Development Environment Troubleshooting Guide

**Last Updated:** October 30, 2025
**Version:** v0.1.2.13

This document provides solutions to common development environment issues encountered when working on the TenGo application.

---

## Critical: Environment Variable Pollution

### Problem Overview

The most common and insidious issue is **environment variable pollution** where production configuration variables persist in the system environment and override the `.env` file values.

### How It Happens

When preparing for production deployment, if you run commands like:
```bash
export $(cat .env.production | xargs)
# or
source .env.production
```

These set ALL production variables as shell environment variables. In Laravel/Vite, **environment variables take precedence over .env file values**, causing:
- Production URLs to be compiled into JavaScript
- Production database credentials to be used locally
- CORS errors (localhost calling production API)
- Database connection failures

### Symptoms

1. **CORS Errors**: Frontend calling `https://csjones.co/tengo` instead of `http://localhost:8000`
2. **Database Access Denied**: `Access denied for user 'uixybijdvk3yv'@'localhost'` (production DB credentials)
3. **Cache Errors**: `This cache store does not support tagging` (production cache driver)
4. **Vite shows wrong URL**: Output displays `APP_URL: https://csjones.co/tengo`

### Diagnosis

Check what environment variables are currently set:

```bash
# Check all APP_ variables
printenv | grep "^APP_"

# Check all DB_ variables
printenv | grep "^DB_"

# Check VITE variables
printenv | grep "^VITE_"

# Check cache driver
printenv | grep "^CACHE_"
```

If you see production values (`csjones.co`, production database names, etc.), you have environment variable pollution.

### Solution

You have two options:

#### Option A: Use the Startup Script (Recommended)

Use the provided `dev.sh` script which exports correct local variables:

```bash
./dev.sh
```

#### Option B: Manual Export Before Starting Servers

Export all local development variables in the SAME bash session before starting servers:

```bash
export APP_ENV=local && \
export APP_URL=http://localhost:8000 && \
export VITE_API_BASE_URL=http://localhost:8000 && \
export DB_CONNECTION=mysql && \
export DB_HOST=localhost && \
export DB_PORT=3306 && \
export DB_DATABASE=laravel && \
export DB_USERNAME=root && \
export DB_PASSWORD="" && \
export CACHE_DRIVER=array && \
php artisan serve &
sleep 2
npm run dev
```

**CRITICAL**: Both Laravel AND Vite must start in the same bash session with these exports.

### Prevention

1. **Never** run `export $(cat .env.production | xargs)` or similar commands
2. **Never** use `source .env.production`
3. **Always** use the `dev.sh` script for local development
4. Keep `.env.production` as `.env.production.example` until deployment
5. Before deployment, create a fresh terminal session

---

## Common Errors & Solutions

### 1. CORS Errors - Frontend Calling Production URL

**Error:**
```
Access to XMLHttpRequest at 'https://csjones.co/tengo/api/auth/login' from origin 'http://localhost:8000' has been blocked by CORS policy
```

**Root Cause:**
- `VITE_API_BASE_URL` environment variable set to production URL
- Vite compiled JavaScript with production URL baked in

**Solution:**
1. Check environment variables: `printenv | grep VITE_API_BASE_URL`
2. If it shows production URL, export correct value:
   ```bash
   export VITE_API_BASE_URL=http://localhost:8000
   ```
3. Restart BOTH servers (Laravel AND Vite) in the same session
4. Clear Vite cache: `rm -rf node_modules/.vite`
5. Hard refresh browser (Cmd+Shift+R or Ctrl+Shift+R)

---

### 2. Database Connection Failed - Production Credentials

**Error:**
```
SQLSTATE[HY000] [1045] Access denied for user 'uixybijdvk3yv'@'localhost' (using password: YES)
```

**Root Cause:**
- Production database credentials set as environment variables
- Laravel uses environment variables over `.env` file

**Solution:**
1. Check database variables: `printenv | grep "^DB_"`
2. Export correct local credentials:
   ```bash
   export DB_CONNECTION=mysql
   export DB_HOST=localhost
   export DB_PORT=3306
   export DB_DATABASE=laravel
   export DB_USERNAME=root
   export DB_PASSWORD=""
   ```
3. Restart Laravel server in the same session
4. Verify `.env` file has correct credentials:
   ```bash
   grep "^DB_" .env
   ```

---

### 3. Cache Store Does Not Support Tagging

**Error:**
```
Failed to analyze protection coverage: This cache store does not support tagging.
```

**Root Cause:**
- `CACHE_DRIVER=file` doesn't support cache tags
- Application uses `Cache::tags()` extensively throughout codebase

**Solution:**
1. Update `.env`:
   ```
   CACHE_DRIVER=array
   ```
2. Export as environment variable:
   ```bash
   export CACHE_DRIVER=array
   ```
3. Restart Laravel server

**Why `array` driver?**
- Supports cache tagging (required by application)
- No persistence needed for local development
- Simple and fast for testing
- Alternative: Install memcached or redis locally

---

### 4. Session Path Mismatch (500 Error)

**Error:**
```
file_put_contents(/Users/Chris/Desktop/fpsV2/storage/framework/sessions/...): Failed to open stream
```

**Root Cause:**
- Cached configuration pointing to old project path
- Laravel config cache not cleared after moving project

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
rm -rf bootstrap/cache/*.php
```

---

### 5. Vite Manifest Not Found

**Error:**
```
Vite manifest not found at: /public/build/manifest.json
```

**Root Cause:**
- Trying to run Laravel without Vite dev server
- Or trying to access production build that doesn't exist

**Solution:**
- Always run BOTH servers for local development:
  ```bash
  # Terminal 1
  php artisan serve

  # Terminal 2
  npm run dev
  ```
- Or use the `dev.sh` script which starts both

---

### 6. Demo User Doesn't Exist (401 Unauthorized)

**Error:**
```
POST http://localhost:8000/api/auth/login 401 (Unauthorized)
```

**Root Cause:**
- Fresh database without demo user
- Or trying to login with wrong credentials

**Solution:**

Create demo user:
```bash
export DB_DATABASE=laravel && \
export DB_USERNAME=root && \
export DB_PASSWORD="" && \
php artisan db:seed --class=DemoUserSeeder --force
```

Demo credentials:
- Email: `demo@fps.com`
- Password: `password`

---

## Environment Setup Checklist

Use this checklist when setting up or troubleshooting your development environment:

- [ ] Check for environment variable pollution: `printenv | grep -E "^APP_|^DB_|^VITE_|^CACHE_"`
- [ ] Verify `.env` file has correct local values (not production)
- [ ] Clear all Laravel caches: `php artisan config:clear && php artisan cache:clear`
- [ ] Clear Vite cache: `rm -rf node_modules/.vite`
- [ ] Kill all running server processes: `pkill -9 php && pkill -9 node`
- [ ] Start servers using `dev.sh` script or manual export method
- [ ] Verify Vite output shows: `APP_URL: http://localhost:8000`
- [ ] Hard refresh browser to clear JavaScript cache
- [ ] Check Laravel logs if issues persist: `tail -50 storage/logs/laravel.log`

---

## Environment Files Overview

### `.env` (Local Development - ACTIVE)
- Used for daily local development
- Database: `laravel` (local MySQL)
- Cache: `array` (supports tagging)
- Mail: `log` driver (no emails sent)
- URL: `http://localhost:8000`

### `.env.production.example` (Production Template)
- Template for production deployment
- Database: SiteGround credentials (placeholders)
- Cache: `file` (shared hosting compatible)
- Mail: SMTP (SiteGround email)
- URL: `https://csjones.co/tengo`
- **NEVER rename to `.env.production` until ready to deploy**

### `.env.development` (Development Template)
- Reference template for local development
- Shows all required local settings
- Copy to `.env` if needed

---

## Quick Reference: Starting Development Servers

### Method 1: Startup Script (Recommended)
```bash
./dev.sh
```

### Method 2: Manual (Two Terminals)
```bash
# Terminal 1 - Backend
php artisan serve

# Terminal 2 - Frontend
npm run dev
```

### Method 3: Single Command (Background Processes)
```bash
./dev.sh
```

---

## When Things Go Wrong

### Step 1: Diagnostic Information
```bash
# Check environment variables
printenv | grep -E "^APP_|^DB_|^VITE_|^CACHE_"

# Check .env file
cat .env | grep -E "^APP_|^DB_|^VITE_|^CACHE_"

# Check running processes
ps aux | grep -E "(php artisan serve|vite|node.*vite)"

# Check recent errors
tail -50 storage/logs/laravel.log
```

### Step 2: Nuclear Reset
If all else fails, perform a complete reset:

```bash
# 1. Kill all processes
pkill -9 php
pkill -9 node

# 2. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
rm -rf bootstrap/cache/*.php
rm -rf node_modules/.vite
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*

# 3. Verify .env file
cat .env | grep -E "^APP_|^DB_|^VITE_|^CACHE_"

# 4. Start fresh with script
./dev.sh
```

### Step 3: Still Broken?

Check these common issues:
1. Is MySQL running? `mysql -u root -e "SELECT 1;"`
2. Does database exist? `mysql -u root -e "SHOW DATABASES LIKE 'laravel';"`
3. Are Node modules installed? `ls node_modules | wc -l` (should be 300+)
4. Is Composer up to date? `composer install`
5. Is port 8000 available? `lsof -ti:8000` (should be empty)
6. Is port 5173 available? `lsof -ti:5173` (should be empty)

---

## Prevention Best Practices

1. **Always use `dev.sh` for local development** - Never manually export production variables
2. **Keep production config in `.example` files** - Only create `.env.production` during deployment
3. **Clear environment before deployment** - Open fresh terminal, don't reuse development terminal
4. **Document changes to environment variables** - Update `.env.example` when adding new variables
5. **Use different database names** - Production DB should have a different name than local
6. **Test in fresh terminal** - Occasionally start a new terminal to ensure scripts work

---

## Contact & Support

If you encounter issues not covered here:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for frontend errors
3. Review CLAUDE.md for coding standards
4. Check DEPLOYMENT.md for production-specific issues

**Remember:** 95% of development environment issues are caused by environment variable pollution. Always check `printenv` first!
