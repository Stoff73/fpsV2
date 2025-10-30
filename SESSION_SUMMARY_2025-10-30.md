# Development Environment Debugging Session Summary

**Date:** October 30, 2025
**Session Duration:** ~2 hours
**Issue:** Development environment broken after production deployment preparation
**Status:** ✅ RESOLVED - Fully documented and prevented for future

---

## Problem Overview

The development environment was completely broken after preparing for production deployment. Multiple critical errors prevented the application from running locally:

1. **CORS Errors** - Frontend calling production URL instead of localhost
2. **Database Connection Failures** - Trying to connect with production credentials
3. **Cache Driver Errors** - "This cache store does not support tagging"
4. **500 Internal Server Errors** - Multiple cascading failures

---

## Root Cause Analysis

### Primary Cause: Environment Variable Pollution

The core issue was **environment variables set in the shell that override `.env` file values**. When preparing for deployment, production environment variables were exported (likely via `export $(cat .env.production | xargs)` or similar), which persisted in the shell session.

**Why This Breaks Everything:**

In Laravel and Vite, environment variables take **precedence** over `.env` file values:

```
Priority Order (highest to lowest):
1. Shell environment variables (export APP_URL=...)
2. .env file (APP_URL=...)
3. Default values in code
```

This meant that even though `.env` had correct local values, the shell environment variables were overriding them.

### Specific Issues Identified

| Issue | Symptom | Root Cause | Solution |
|-------|---------|------------|----------|
| **CORS Errors** | Frontend calling `https://csjones.co/tengo` | `VITE_API_BASE_URL` environment variable set to production URL | Export `VITE_API_BASE_URL=http://localhost:8000` before starting Vite |
| **Database Access Denied** | `Access denied for user 'uixybijdvk3yv'@'localhost'` | All `DB_*` environment variables set to production values | Export correct local DB credentials |
| **Cache Tagging Error** | `This cache store does not support tagging` | `CACHE_DRIVER=file` doesn't support tags | Change to `CACHE_DRIVER=array` for local dev |
| **Wrong APP_URL in Vite** | Vite output shows production URL | `APP_URL` environment variable set | Export `APP_URL=http://localhost:8000` |

---

## Debugging Process

### Phase 1: Symptom Investigation

1. **Frontend CORS errors detected** - API calls going to production domain
2. **Added diagnostic logging** to `bootstrap.js` and `api.js` to trace URL source
3. **Confirmed** Vite was compiling with production URL baked into JavaScript

### Phase 2: Root Cause Discovery

1. **Checked Vite output** - Showed `APP_URL: https://csjones.co/tengo` (wrong!)
2. **Checked `.env` file** - Had correct local values
3. **Checked environment variables** with `printenv` - **FOUND PRODUCTION VALUES**
4. **Discovered** all production environment variables were set globally

### Phase 3: Cascading Issues

As we fixed each layer, new issues emerged:

1. **Fixed frontend URL** → Revealed database credential issues
2. **Fixed database credentials** → Revealed cache driver incompatibility
3. **Fixed cache driver** → Application finally worked

### Phase 4: Systematic Solution

Implemented proper environment variable management:

```bash
# Export ALL local development variables in SAME bash session
export APP_ENV=local
export APP_URL=http://localhost:8000
export VITE_API_BASE_URL=http://localhost:8000
export DB_CONNECTION=mysql
export DB_HOST=localhost
export DB_DATABASE=laravel
export DB_USERNAME=root
export DB_PASSWORD=""
export CACHE_DRIVER=array

# Then start BOTH servers in that session
php artisan serve &
sleep 2
npm run dev
```

**CRITICAL**: Both Laravel AND Vite must start in the SAME session with these exports.

---

## Solutions Implemented

### 1. Created Startup Script (`dev.sh`)

A comprehensive bash script that:
- Kills any existing server processes
- Checks MySQL connectivity
- Verifies/creates database
- Clears all caches (Laravel + Vite)
- Exports correct local environment variables
- Starts both Laravel and Vite servers
- Provides clear status output and debugging info

**Usage:**
```bash
./dev.sh
```

### 2. Updated `.env` File

Fixed local development configuration:
- Database: `laravel` (local) instead of `dbow3dj6o4qnc4` (production)
- Username: `root` instead of `uixybijdvk3yv`
- Cache driver: `array` instead of `file` (supports cache tagging)
- URLs: `http://localhost:8000` instead of `https://csjones.co/tengo`

### 3. Created Troubleshooting Guide

**File:** `DEV_ENVIRONMENT_TROUBLESHOOTING.md`

Comprehensive documentation including:
- Root cause explanation with examples
- Common error patterns and solutions
- Diagnostic commands to check for contamination
- Step-by-step recovery procedures
- Prevention best practices
- Nuclear reset procedure for worst-case scenarios

### 4. Updated CLAUDE.md

Added **Section 4: Environment Variable Contamination**:
- Prominent warning at the top of critical instructions
- Clear explanation of the problem
- Diagnosis commands for future Claude Code instances
- Solution templates
- Reference links to troubleshooting guide
- Common symptom checklist

This ensures future Claude Code sessions will:
1. Check for contamination FIRST when debugging
2. Know how to properly start development servers
3. Understand the environment separation requirements

### 5. Updated DEPLOYMENT.md

Added critical section at the top:
- Warning about environment separation
- Explanation of the pollution problem
- Instructions to use fresh terminal for deployment
- Post-deployment return-to-development procedure

---

## Files Modified/Created

### Created:
1. **`dev.sh`** - Development server startup script (executable)
2. **`DEV_ENVIRONMENT_TROUBLESHOOTING.md`** - Comprehensive troubleshooting guide
3. **`SESSION_SUMMARY_2025-10-30.md`** - This document

### Modified:
1. **`.env`** - Fixed database credentials and cache driver
2. **`CLAUDE.md`** - Added Section 4 with environment variable warnings
3. **`DEPLOYMENT.md`** - Added environment separation warning at top

### Temporarily Modified (then cleaned up):
1. **`resources/js/bootstrap.js`** - Added diagnostic logging (removed after fix)
2. **`resources/js/services/api.js`** - Added diagnostic logging (removed after fix)

---

## Lessons Learned

### For Developers

1. **Never reuse terminals** between development and deployment
2. **Always use `./dev.sh`** for local development (enforces correct environment)
3. **Check `printenv` first** when debugging mysterious issues
4. **Keep `.env.production` as `.example`** until actual deployment
5. **Clear browser cache** after fixing environment issues (hard refresh: Cmd+Shift+R)

### For Future Claude Code Sessions

1. **Check for environment contamination FIRST** when debugging dev environment
2. **Use systematic-debugging skill** for all troubleshooting (as per CLAUDE.md)
3. **Never export production variables** during development work
4. **Always start servers with proper environment** using dev.sh or manual exports
5. **Refer to DEV_ENVIRONMENT_TROUBLESHOOTING.md** for comprehensive guidance

### Architecture Insights

1. **Laravel/Vite priority**: Environment variables > `.env` file > defaults
2. **Vite compilation**: URL values get baked into JavaScript at compile time
3. **Cache tagging**: Only `array`, `memcached`, and `redis` drivers support tags
4. **Session persistence**: Exported variables persist across all commands in that session
5. **Process isolation**: Each bash session has its own environment; servers inherit parent environment

---

## Prevention Checklist

Use this checklist to avoid this issue in the future:

**Before Development Session:**
- [ ] Open fresh terminal (or verify no production env vars with `printenv`)
- [ ] Use `./dev.sh` script to start servers
- [ ] Verify Vite output shows `APP_URL: http://localhost:8000`
- [ ] Check browser console for correct API URL
- [ ] Verify database connects to local `laravel` database

**Before Deployment:**
- [ ] Open **NEW** terminal (don't reuse development terminal)
- [ ] Verify no local env vars with `printenv | grep "^APP_\|^DB_"`
- [ ] Copy `.env.production.example` to `.env.production` on server
- [ ] Never run `export $(cat .env.production | xargs)` locally

**After Deployment:**
- [ ] Close deployment terminal
- [ ] Open **NEW** terminal for development
- [ ] Use `./dev.sh` to return to development
- [ ] Verify development environment works

---

## Quick Reference

### Starting Development Servers

**Method 1: Startup Script (Recommended)**
```bash
./dev.sh
```

**Method 2: Manual (for understanding)**
```bash
export APP_ENV=local && \
export APP_URL=http://localhost:8000 && \
export VITE_API_BASE_URL=http://localhost:8000 && \
export DB_CONNECTION=mysql && \
export DB_HOST=localhost && \
export DB_DATABASE=laravel && \
export DB_USERNAME=root && \
export DB_PASSWORD="" && \
export CACHE_DRIVER=array && \
php artisan serve &
sleep 2
npm run dev
```

### Checking for Contamination

```bash
# Check all environment variables that might cause issues
printenv | grep -E "^APP_|^DB_|^VITE_|^CACHE_"

# If you see production values (csjones.co, production DB names), you have contamination
```

### Nuclear Reset (when everything fails)

```bash
# 1. Kill all processes
pkill -9 php && pkill -9 node

# 2. Clear all caches
php artisan config:clear && php artisan cache:clear
rm -rf node_modules/.vite
rm -rf bootstrap/cache/*.php

# 3. Verify .env is correct
cat .env | grep -E "^APP_|^DB_|^CACHE_"

# 4. Start fresh
./dev.sh
```

---

## Success Metrics

After implementing these solutions:

✅ Development environment starts cleanly with one command
✅ No CORS errors - all API calls go to localhost
✅ Database connects successfully to local MySQL
✅ Cache tagging works correctly with array driver
✅ Vite compiles with correct localhost URL
✅ Application dashboard loads without errors
✅ Login works with demo user credentials
✅ All module data loads successfully

**Development environment is now fully functional and documented!**

---

## Documentation Hierarchy

For future reference, here's how to use the documentation:

1. **Quick Start** → Use `./dev.sh`
2. **Problems?** → Check `DEV_ENVIRONMENT_TROUBLESHOOTING.md`
3. **Claude Code Guidance** → See `CLAUDE.md` Section 4
4. **Deployment** → Read environment separation section in `DEPLOYMENT.md`
5. **Deep Dive** → This session summary

---

## Final Notes

This issue took ~2 hours to fully diagnose and resolve because:

1. **Multiple layers** - Frontend, backend, database, cache all had issues
2. **Hidden root cause** - Environment variables are invisible until you check
3. **Cascading failures** - Each fix revealed the next layer's problem
4. **Unfamiliar territory** - Vite's environment variable handling isn't obvious

However, now that it's documented and prevented:

- **Future sessions**: < 5 minutes to diagnose ("check printenv first")
- **Prevention**: Use `./dev.sh` script, never have the issue
- **Recovery**: Follow troubleshooting guide, systematic approach

**The time invested in documentation will save hours in future sessions.**

---

**Session completed:** October 30, 2025 @ 09:20 GMT
**Result:** Development environment fully operational and future-proofed
