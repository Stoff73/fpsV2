# Hotfix Deployment Guide - SiteGround Production

**Environment**: https://csjones.co/tengo
**Server**: SiteGround (uk71.siteground.eu)
**SSH Port**: 18765
**User**: u163-ptanegf9edny

---

## When to Use This Guide

Use this guide for **quick production hotfixes** when:
- Single file or small code change needed
- No database migrations required
- No new dependencies (composer/npm packages)
- Need to fix critical production bug quickly

**For full deployments**, use: `DEPLOYMENT_GUIDE_SITEGROUND.md`

---

## Prerequisites

### Required Information

- SSH credentials: `u163-ptanegf9edny@csjones.co`
- SSH port: `18765`
- Application path: `~/www/csjones.co/tengo-app/`

### Files to Deploy

Identify the exact files that changed:
```bash
# From local machine (project directory)
git status
git diff HEAD
```

---

## Deployment Methods

### Method 1: SFTP Upload (Recommended - No SSH Key Required)

**When to use**: Quick file uploads without SSH key setup

**Steps**:

1. **Connect via SFTP** (from local machine):
```bash
sftp -P 18765 u163-ptanegf9edny@csjones.co
```

2. **Navigate to target directory**:
```bash
# For controllers
cd www/csjones.co/tengo-app/app/Http/Controllers/Api

# For models
cd www/csjones.co/tengo-app/app/Models

# For requests
cd www/csjones.co/tengo-app/app/Http/Requests

# For views
cd www/csjones.co/tengo-app/resources/js/views

# For components
cd www/csjones.co/tengo-app/resources/js/components
```

3. **Upload files**:
```bash
# Upload single file
put app/Http/Controllers/Api/SavingsController.php

# Upload from different local path (if not in project root)
put path/to/local/file.php

# Check uploaded file
ls -la
```

4. **Repeat for each file**, then:
```bash
quit
```

5. **Clear Laravel cache** (via separate SSH connection):
```bash
ssh -p 18765 u163-ptanegf9edny@csjones.co
cd ~/www/csjones.co/tengo-app
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

### Method 2: SCP (Requires SSH Key)

**When to use**: When SSH key is configured (faster than SFTP)

**One-time Setup**:
```bash
# From local machine
ssh-copy-id -p 18765 u163-ptanegf9edny@csjones.co
# Enter password when prompted
```

**Upload Files**:
```bash
# From local machine (project directory)

# Upload controller
scp -P 18765 app/Http/Controllers/Api/SavingsController.php \
  u163-ptanegf9edny@csjones.co:~/www/csjones.co/tengo-app/app/Http/Controllers/Api/

# Upload model
scp -P 18765 app/Models/SavingsAccount.php \
  u163-ptanegf9edny@csjones.co:~/www/csjones.co/tengo-app/app/Models/

# Upload validation request
scp -P 18765 app/Http/Requests/Savings/StoreSavingsAccountRequest.php \
  u163-ptanegf9edny@csjones.co:~/www/csjones.co/tengo-app/app/Http/Requests/Savings/
```

**Clear Cache**:
```bash
ssh -p 18765 u163-ptanegf9edny@csjones.co \
  "cd ~/www/csjones.co/tengo-app && php artisan config:clear && php artisan route:clear && php artisan view:clear"
```

---

### Method 3: SiteGround File Manager (GUI)

**When to use**: No command line access or SSH issues

**Steps**:

1. Log into SiteGround account
2. Navigate to: **Site Tools → File Manager**
3. Browse to: `www/csjones.co/tengo-app/`
4. Navigate to the file's directory
5. Click **Upload** button (top right)
6. Select the file from your local machine
7. Confirm overwrite when prompted
8. Repeat for each file

**Clear Cache** (via SSH or SiteGround Terminal):
```bash
cd ~/www/csjones.co/tengo-app
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Post-Deployment Steps

### 1. Clear Laravel Caches (Required)

```bash
cd ~/www/csjones.co/tengo-app

# Clear configuration cache
php artisan config:clear

# Clear route cache (if routes changed)
php artisan route:clear

# Clear view cache (if blade templates changed)
php artisan view:clear

# Clear application cache (optional)
php artisan cache:clear
```

### 2. Verify File Permissions

```bash
# Check uploaded files have correct permissions
ls -la app/Http/Controllers/Api/SavingsController.php
# Should show: -rw-r--r-- (644)

# If permissions wrong, fix:
chmod 644 app/Http/Controllers/Api/SavingsController.php
```

### 3. Check Laravel Logs

```bash
tail -50 ~/www/csjones.co/tengo-app/storage/logs/laravel.log
```

Look for any errors after deployment.

---

## Testing Deployed Fix

### Browser Testing

1. **Clear browser cache**: Hard refresh (Cmd+Shift+R / Ctrl+Shift+R)
2. **Or use incognito mode** to avoid cached JavaScript
3. Navigate to affected feature
4. Test the bug is fixed

### Check Network Tab

1. Open DevTools → Network tab
2. Perform action that was failing
3. Check API responses:
   - Should be HTTP 200 (success)
   - No 500 errors (server error)
   - No 422 errors (validation error)

### Check Console

1. Open DevTools → Console tab
2. Perform action
3. Should see no errors

---

## Rollback Procedure

If the hotfix causes new issues:

### Quick Rollback

1. **Via Git** (if you have previous version):
```bash
# From local machine
git checkout HEAD~1 -- app/Http/Controllers/Api/SavingsController.php
```

2. **Re-upload the previous version** using SFTP/SCP

3. **Clear cache** as above

### Full Rollback

If multiple files affected or unsure:

```bash
# Via SSH
cd ~/www/csjones.co/tengo-app
git status
git checkout -- .
php artisan config:clear
php artisan route:clear
```

---

## Common File Paths

Quick reference for common deployment targets:

```
Controllers:    ~/www/csjones.co/tengo-app/app/Http/Controllers/Api/
Models:         ~/www/csjones.co/tengo-app/app/Models/
Requests:       ~/www/csjones.co/tengo-app/app/Http/Requests/
Services:       ~/www/csjones.co/tengo-app/app/Services/
Agents:         ~/www/csjones.co/tengo-app/app/Agents/
Views (Vue):    ~/www/csjones.co/tengo-app/resources/js/views/
Components:     ~/www/csjones.co/tengo-app/resources/js/components/
Routes:         ~/www/csjones.co/tengo-app/routes/api.php
Config:         ~/www/csjones.co/tengo-app/config/
Migrations:     ~/www/csjones.co/tengo-app/database/migrations/
```

---

## Troubleshooting

### SFTP Connection Issues

**Error**: `Permission denied (publickey)`

**Solution**: You're trying to use SCP instead of SFTP. Use `sftp` command, not `scp`:
```bash
sftp -P 18765 u163-ptanegf9edny@csjones.co  # ✅ Correct
scp -P 18765 ...  # ❌ Wrong (needs SSH key)
```

### Cache Clear Fails

**Error**: `Failed to clear cache. Make sure you have the appropriate permissions.`

**Solution**: This is often a file permissions issue on storage directories:
```bash
cd ~/www/csjones.co/tengo-app
chmod -R 775 storage bootstrap/cache
```

### File Not Taking Effect

**Problem**: Deployed file but changes not showing

**Checklist**:
- [ ] Cleared Laravel config cache? (`php artisan config:clear`)
- [ ] Cleared browser cache? (Hard refresh)
- [ ] Viewing in incognito mode? (Bypasses cache)
- [ ] Uploaded to correct path? (Check `pwd` in SFTP)
- [ ] File permissions correct? (Should be 644)

### Can't Connect to Server

**Problem**: SSH/SFTP connection times out

**Solutions**:
1. Check VPN if required
2. Verify port: Must be `18765` not `22`
3. Check username: `u163-ptanegf9edny` (not just your name)
4. Contact SiteGround support if server down

---

## Example: Complete Hotfix Deployment

**Scenario**: Fix validation error in SavingsController

```bash
# 1. Make changes locally and commit
git add app/Http/Controllers/Api/SavingsController.php
git commit -m "fix: Correct validation logic"

# 2. Connect via SFTP
sftp -P 18765 u163-ptanegf9edny@csjones.co

# 3. Navigate and upload
cd www/csjones.co/tengo-app/app/Http/Controllers/Api
put app/Http/Controllers/Api/SavingsController.php
ls -la SavingsController.php
quit

# 4. Clear cache (separate terminal/SSH session)
ssh -p 18765 u163-ptanegf9edny@csjones.co
cd ~/www/csjones.co/tengo-app
php artisan config:clear
php artisan route:clear
exit

# 5. Test in browser
# Navigate to https://csjones.co/tengo/
# Test the fixed feature
# Check DevTools console for errors
```

---

## Safety Checklist

Before deploying hotfix:

- [ ] Changes tested locally?
- [ ] Changes committed to git?
- [ ] Know which files to upload?
- [ ] Have rollback plan ready?
- [ ] Low-traffic time? (if possible)
- [ ] Ready to test immediately after deployment?

After deploying hotfix:

- [ ] Cache cleared?
- [ ] Laravel logs checked?
- [ ] Feature tested in production?
- [ ] No new errors in console?
- [ ] User reported issue resolved?

---

## When NOT to Use Hotfix Deployment

Use full deployment process instead if:

- ❌ Database migrations required
- ❌ New composer/npm dependencies added
- ❌ Environment variables changed
- ❌ Multiple complex changes across many files
- ❌ Frontend build required (Vue/Vite assets)

**In these cases**: Follow `DEPLOYMENT_GUIDE_SITEGROUND.md`

---

## Related Documentation

- **Full Deployment**: `DEPLOYMENT_GUIDE_SITEGROUND.md`
- **Deployment Summary**: `DEPLOYMENT_SUMMARY.md`
- **Deployment Checklist**: `DEPLOYMENT_CHECKLIST.md`
- **Consistency Verification**: `DEPLOYMENT_CONSISTENCY_VERIFICATION.md`

---

**Last Updated**: November 13, 2025
**Tested Method**: SFTP upload (Method 1)
**Status**: ✅ Verified working

🤖 Generated with [Claude Code](https://claude.com/claude-code)
