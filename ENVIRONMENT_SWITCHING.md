# Environment Switching Guide - TenGo

Quick reference for switching between local development and production environments.

---

## Quick Switch Commands

### Switch to Local Development

```bash
# Copy local environment config
cp .env.local .env

# OR manually ensure these settings in .env:
# APP_ENV=local
# APP_DEBUG=true
# APP_URL=http://localhost:8000
# CACHE_DRIVER=file
# SESSION_DRIVER=file
# QUEUE_CONNECTION=sync
# Comment out: # VITE_API_BASE_URL=
# Comment out: # ASSET_URL=
# Comment out: # SESSION_PATH=

# Clear caches
php artisan config:clear
php artisan cache:clear

# Start dev servers (run in separate terminals)
php artisan serve        # Terminal 1
npm run dev             # Terminal 2
```

### Switch to Production Build

```bash
# Copy production environment config
cp .env.production.backup .env

# OR manually ensure these settings in .env:
# APP_ENV=production
# APP_DEBUG=false
# APP_URL=https://csjones.co/tengo
# CACHE_DRIVER=memcached (or redis)
# SESSION_DRIVER=database
# SESSION_PATH=/tengo
# VITE_API_BASE_URL=https://csjones.co/tengo
# ASSET_URL=https://csjones.co/tengo
# QUEUE_CONNECTION=database

# Build production assets
NODE_ENV=production npm run build

# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Key Environment Differences

| Setting | Local Development | Production |
|---------|------------------|------------|
| **APP_ENV** | `local` | `production` |
| **APP_DEBUG** | `true` | `false` |
| **APP_URL** | `http://localhost:8000` | `https://csjones.co/tengo` |
| **VITE_API_BASE_URL** | Empty (commented out) | `https://csjones.co/tengo` |
| **ASSET_URL** | Empty (commented out) | `https://csjones.co/tengo` |
| **SESSION_PATH** | Empty (commented out) | `/tengo` |
| **CACHE_DRIVER** | `file` | `memcached` |
| **SESSION_DRIVER** | `file` | `database` |
| **QUEUE_CONNECTION** | `sync` | `database` |
| **LOG_LEVEL** | `debug` | `error` |
| **MAIL_MAILER** | `log` | `smtp` |

---

## Why These Settings Matter

### VITE_API_BASE_URL
- **Local**: Empty → Frontend uses `window.location.origin` (http://localhost:8000)
- **Production**: Set to full URL → Frontend knows where API is in subfolder deployment
- **Issue if wrong**: CORS errors, API calls fail

### APP_URL vs VITE_API_BASE_URL
- **APP_URL**: Used by Laravel backend for generating URLs
- **VITE_API_BASE_URL**: Used by Vue.js frontend for API calls
- **Both must match** in production for subfolder deployment

### SESSION_PATH
- **Local**: Not needed (root domain)
- **Production**: `/tengo` → Required for cookies to work in subfolder
- **Issue if wrong**: Session/authentication fails

### ASSET_URL
- **Local**: Not needed (Vite dev server handles it)
- **Production**: Must match deployment path for assets to load
- **Issue if wrong**: CSS/JS/images won't load (404 errors)

---

## Common Issues & Solutions

### Issue: CORS Errors in Local Development
**Symptom**: `Access to XMLHttpRequest blocked by CORS policy`
**Cause**: `VITE_API_BASE_URL` set to production URL
**Fix**: Comment out `VITE_API_BASE_URL` in `.env`, restart Vite

### Issue: 404 on Assets in Production
**Symptom**: JS/CSS files return 404
**Cause**: `ASSET_URL` or `VITE_API_BASE_URL` incorrect
**Fix**: Ensure both match deployment path (`https://csjones.co/tengo`)

### Issue: Session/Auth Not Working
**Symptom**: User logged out immediately after login
**Cause**: `SESSION_PATH` incorrect or missing
**Fix**: Set `SESSION_PATH=/tengo` for subfolder deployment

### Issue: Stale Config After Switch
**Symptom**: Changes to `.env` not taking effect
**Fix**: Always clear caches after switching:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## Development Workflow

### Daily Development (Local)
1. Ensure `.env` has local settings
2. Run both servers: `php artisan serve` + `npm run dev`
3. Access: http://localhost:8000
4. API calls go to: http://localhost:8000/api/*

### Testing Production Build Locally
1. Build assets: `npm run build`
2. Serve with Laravel: `php artisan serve`
3. Access: http://localhost:8000 (will use built assets, not Vite HMR)

### Deploying to Production
1. Switch to production env: `cp .env.production.backup .env`
2. Build: `NODE_ENV=production npm run build`
3. Optimize: `composer install --no-dev --optimize-autoloader`
4. Upload to server
5. On server: `php artisan config:cache && php artisan migrate`

---

## File Reference

- **`.env`** - Active environment (DON'T commit to git)
- **`.env.local`** - Template for local development
- **`.env.production.backup`** - Template for production
- **`.env.example`** - Generic template for new setups

---

**Pro Tip**: Always verify your environment after switching:
```bash
php artisan tinker --execute="echo config('app.env'); echo config('app.url');"
```

Expected output:
- Local: `local http://localhost:8000`
- Production: `production https://csjones.co/tengo`

---

**Last Updated**: October 30, 2025
**Version**: v0.1.2.13
