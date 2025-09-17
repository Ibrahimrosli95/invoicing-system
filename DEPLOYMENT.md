# cPanel Deployment Guide

Complete step-by-step guide to deploy your Laravel application to cPanel shared hosting.

## Prerequisites

- cPanel hosting account with:
  - PHP 8.1+ support
  - MySQL database access
  - Git version control feature
  - SSH access (recommended) or File Manager
  - Composer (optional but recommended)

## Step 1: Database Setup

1. **Login to cPanel** → Navigate to **MySQL® Databases**

2. **Create Database:**
   - Database Name: `username_sales_system` (note the prefix)
   - Click "Create Database"

3. **Create Database User:**
   - Username: `username_app_user`
   - Password: Generate a strong password
   - Click "Create User"

4. **Grant Privileges:**
   - Select your database and user
   - Grant "ALL PRIVILEGES"
   - Click "Make Changes"

5. **Note down these credentials for later:**
   ```
   DB_HOST=localhost
   DB_DATABASE=username_sales_system
   DB_USERNAME=username_app_user
   DB_PASSWORD=your_generated_password
   ```

## Step 2: Git Repository Setup

### Option A: First-time deployment

1. **cPanel → Git™ Version Control**
2. **Clone Repository:**
   - Repository URL: `https://github.com/yourusername/your-repo.git`
   - Repository Path: `sales-system` (or your preferred folder)
   - Repository Name: `Sales System`
3. Click "Create"

### Option B: Existing repository

1. **Connect existing repository:**
   - Navigate to your existing folder in cPanel File Manager
   - Initialize git: `git init`
   - Add remote: `git remote add origin https://github.com/yourusername/your-repo.git`
   - Pull: `git pull origin main`

## Step 3: Domain Configuration

### Option A: Subdomain setup
1. **cPanel → Subdomains**
2. Create subdomain: `app.yourdomain.com`
3. **Document Root:** `/path/to/sales-system/public`

### Option B: Main domain (.htaccess fallback)
If you can't set document root, create `.htaccess` in your domain root:
```apache
RewriteEngine On
RewriteRule ^(.*)$ sales-system/public/$1 [L]
```

## Step 4: Environment Configuration

1. **Copy production environment:**
   ```bash
   cp .env.example.prod .env
   ```

2. **Edit .env file with your credentials:**
   ```env
   APP_URL=https://yourdomain.com
   DB_HOST=localhost
   DB_DATABASE=username_sales_system
   DB_USERNAME=username_app_user
   DB_PASSWORD=your_generated_password
   DOMAIN=yourdomain.com
   CPANEL_DB_NAME=username_sales_system
   CPANEL_DB_USER=username_app_user
   CPANEL_DB_PASS=your_generated_password
   ```

## Step 5: Deployment Process

### If Composer is available on server:

```bash
# Navigate to your repository
cd /home/username/sales-system

# Install dependencies (production optimized)
composer install --no-dev --optimize-autoloader

# Generate application key (first time only)
php artisan key:generate

# Create storage symlink
php artisan storage:link

# Run database migrations
php artisan migrate --force

# Cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 775 storage bootstrap/cache
```

### If Composer is NOT available:

1. **On your local machine:**
   ```bash
   composer install --no-dev --optimize-autoloader
   git add vendor/
   git commit -m "Add vendor directory for production"
   git push origin main
   ```

2. **On the server:**
   ```bash
   cd /home/username/sales-system
   git pull origin main
   php artisan key:generate
   php artisan storage:link
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   chmod -R 775 storage bootstrap/cache
   ```

## Step 6: Cron Jobs Setup

### Laravel Scheduler (Required)
1. **cPanel → Cron Jobs**
2. **Add new cron job:**
   ```
   * * * * * /usr/local/bin/php /home/username/sales-system/artisan schedule:run >> /dev/null 2>&1
   ```

### Queue Worker (Optional - if using queues)
```
* * * * * /usr/local/bin/php /home/username/sales-system/artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

**Note:** Replace `/usr/local/bin/php` with your server's PHP path. Common paths:
- `/usr/local/bin/php`
- `/usr/bin/php`
- `/opt/cpanel/ea-php81/root/usr/bin/php` (for EA-PHP)

## Step 7: Automated Deployment

### Using post_deploy.sh script

1. **Make script executable:**
   ```bash
   chmod +x .cpanel_deploy/post_deploy.sh
   ```

2. **Edit paths in script:**
   ```bash
   # Update these variables at the top of post_deploy.sh
   PHP_PATH="/usr/local/bin/php"
   COMPOSER_PATH="/usr/local/bin/composer"
   REPO_PATH_ON_SERVER="/home/username/sales-system"
   ```

3. **Run after each deployment:**
   ```bash
   cd /home/username/sales-system
   ./.cpanel_deploy/post_deploy.sh
   ```

### Git Hooks Integration (Advanced)
To run automatically after git pull, add to `.git/hooks/post-merge`:
```bash
#!/bin/bash
cd /home/username/sales-system
./.cpanel_deploy/post_deploy.sh
```

## Step 8: Verification

1. **Visit your domain:** `https://yourdomain.com`
2. **Check Laravel welcome page or login**
3. **Test database connection**
4. **Verify file uploads work**
5. **Check logs:** `tail -f storage/logs/laravel.log`

## Troubleshooting

### Common Issues

**500 Internal Server Error:**
- Check storage permissions: `chmod -R 775 storage bootstrap/cache`
- Verify .env file exists and APP_KEY is set
- Check error logs in cPanel

**Database Connection Error:**
- Verify database credentials in .env
- Ensure database and user exist in cPanel
- Check DB_HOST (usually 'localhost' for cPanel)

**File Not Found (404):**
- Verify document root points to `/public` folder
- Check .htaccess rules
- Verify file permissions

**Composer Issues:**
- Try `composer install --no-dev --no-scripts`
- Include vendor/ in git if composer unavailable
- Use PHP 8.1+ compatible packages only

**Performance Issues:**
- Ensure config is cached: `php artisan config:cache`
- Enable OPcache if available
- Check shared hosting resource limits

## Maintenance

### Regular Updates
1. **Pull latest code:** `git pull origin main`
2. **Run post-deploy script:** `./.cpanel_deploy/post_deploy.sh`
3. **Clear caches if needed:** `php artisan cache:clear`

### Monitoring
- Set up error monitoring (Sentry, Bugsnag)
- Monitor logs: `storage/logs/laravel.log`
- Check cron job execution in cPanel

---

## Quick Reference

**Key Paths to Remember:**
- Repository: `/home/username/sales-system`
- Public folder: `/home/username/sales-system/public`
- Logs: `/home/username/sales-system/storage/logs/laravel.log`
- Environment: `/home/username/sales-system/.env`

**Essential Commands:**
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```