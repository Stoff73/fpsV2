# FPS Deployment - Quick Reference Card

**Version**: v0.1.2.12
**Target**: SiteGround (https://c.jones.co/fpsv2)

---

## Quick Start (5 Minutes)

### 1. Build Locally
```bash
cd /Users/Chris/Desktop/fpsV2
./deploy.sh
```

### 2. Create Database (SiteGround cPanel)
- Database: `fpsv2_production`
- User: `fps_user`
- Grant ALL PRIVILEGES

### 3. Upload Files (FTP/SFTP)
Upload to: `/public_html/fpsv2/`

**Include**:
- app/, bootstrap/, config/, database/, public/, resources/, routes/, storage/, vendor/
- artisan, composer.json, composer.lock, index.php, .htaccess

**Exclude**:
- node_modules/, .git/, tests/, package*.json, vite.config.js

### 4. Configure Environment (SSH/cPanel Terminal)
```bash
cd ~/public_html/fpsv2
cp .env.production.example .env
nano .env  # Edit database credentials
php artisan key:generate --force
```

### 5. Run Migrations
```bash
php artisan migrate --force
php artisan db:seed --class=TaxConfigurationSeeder --force
```

### 6. Create Admin
```bash
php artisan tinker
```
```php
$admin = new \App\Models\User();
$admin->name = 'Admin';
$admin->email = 'admin@fps.com';
$admin->password = bcrypt('YOUR_PASSWORD');
$admin->email_verified_at = now();
$admin->is_admin = true;
$admin->save();
exit
```

### 7. Set Permissions
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### 8. Test
Visit: https://c.jones.co/fpsv2

---

## Environment Variables (Critical)

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://c.jones.co/fpsv2

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=fpsv2_production
DB_USERNAME=fps_user
DB_PASSWORD=YOUR_DB_PASSWORD

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_DOMAIN=c.jones.co
SESSION_PATH=/fpsv2
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
```

---

## Cron Jobs (SiteGround cPanel)

### Queue Worker (Every Minute)
```bash
* * * * * cd /home/USERNAME/public_html/fpsv2 && /usr/bin/php artisan queue:work --stop-when-empty
```

### Daily Cleanup (2 AM)
```bash
0 2 * * * cd /home/USERNAME/public_html/fpsv2 && /usr/bin/php artisan queue:prune-batches
```

---

## Troubleshooting

### 500 Error
```bash
tail -n 50 storage/logs/laravel.log
chmod -R 775 storage/ bootstrap/cache/
php artisan cache:clear
```

### Assets 404
- Check `public/build/` exists
- Verify ASSET_URL=/fpsv2 in .env
- Rebuild: `npm run build`

### Database Error
```bash
php artisan db:show  # Test connection
```
Check .env database credentials

### CSRF Token Error
```bash
rm -rf storage/framework/sessions/*
php artisan cache:clear
```
Check SESSION_DOMAIN and SESSION_PATH in .env

---

## Post-Deployment Checklist

- [ ] Site loads: https://c.jones.co/fpsv2
- [ ] User can register
- [ ] Admin can login
- [ ] All 5 modules accessible
- [ ] Database has 45+ tables
- [ ] Queue worker cron job active
- [ ] SSL/HTTPS working
- [ ] Email sending configured

---

## Key Commands

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Database
php artisan migrate --force
php artisan db:seed --force
php artisan db:show

# Queue
php artisan queue:work --once
php artisan queue:failed
php artisan queue:retry all

# Logs
tail -f storage/logs/laravel.log
```

---

## Support Resources

- **Full Guide**: DEPLOYMENT_GUIDE.md
- **Database Guide**: DATABASE_MIGRATION_CHECKLIST.md
- **Development Guide**: CLAUDE.md

---

**Deployment Time**: ~30 minutes (first time)
**SiteGround Support**: https://my.siteground.com/support

🤖 **Generated with [Claude Code](https://claude.com/claude-code)**
