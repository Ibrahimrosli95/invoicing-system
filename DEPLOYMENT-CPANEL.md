# 🚀 Laravel Sales System - cPanel Shared Hosting Deployment Guide

## 📋 Pre-Deployment Checklist

### 1. **Hosting Requirements**
- ✅ PHP 8.1+ (ea-php83 recommended)
- ✅ MySQL 5.7+ or MySQL 8.0
- ✅ Composer access (or pre-built vendor folder)
- ✅ Git deployment support in cPanel
- ✅ File Manager access
- ✅ Database creation privileges

### 2. **Repository Setup**
- ✅ Push all code to Git repository
- ✅ Include `.cpanel.yml` in root
- ✅ Ensure `vendor/` folder is included (if no Composer access)
- ✅ Include production `.env` file template

## 🔧 Step-by-Step Deployment

### Step 1: Configure cPanel Git Deployment

1. **Access cPanel → Git Version Control**
2. **Create New Repository:**
   - Repository URL: `https://github.com/yourusername/bina-invoicing-system.git`
   - Repository Path: `/home/binapain/repositories/sales-system`
   - Branch: `main`

3. **Set Deployment Path:**
   - Check "Enable Automatic Deployment"
   - The `.cpanel.yml` will handle the rest

### Step 2: Database Setup

1. **Create MySQL Database:**
   - Database Name: `binapain_sales_system`
   - Username: `binapain_sales_user`
   - Password: `[Generate Strong Password]`

2. **Import Database Schema:**
   ```sql
   -- Upload and import your database.sql file
   -- Or run migrations after deployment
   ```

### Step 3: Environment Configuration

1. **Copy `.env.production` to `.env`**
2. **Update Database Credentials:**
   ```env
   DB_DATABASE=binapain_sales_system
   DB_USERNAME=binapain_sales_user
   DB_PASSWORD=your_actual_password
   ```

3. **Update Domain Settings:**
   ```env
   APP_URL=https://yourdomain.com/sales-system
   ```

### Step 4: Dependencies Installation

**Option A: Pre-built Vendor (Recommended for Shared Hosting)**
```bash
# On your local machine:
composer install --no-dev --optimize-autoloader
zip -r vendor.zip vendor/
# Upload vendor.zip to repository
```

**Option B: Server-side Composer (if available)**
```bash
# Via SSH or Terminal in cPanel:
cd /home/binapain/sales-system
composer install --no-dev --optimize-autoloader
```

### Step 5: File Permissions Fix

```bash
# If you have SSH access:
chmod -R 755 /home/binapain/sales-system
chmod -R 777 /home/binapain/sales-system/storage
chmod -R 777 /home/binapain/sales-system/bootstrap/cache
chmod 644 /home/binapain/sales-system/.env
```

### Step 6: Laravel Optimization

**Option A: Via SSH (if available)**
```bash
# Run these commands via SSH:
cd /home/binapain/sales-system

# Clear caches
/usr/local/bin/ea-php83 artisan config:clear
/usr/local/bin/ea-php83 artisan route:clear
/usr/local/bin/ea-php83 artisan view:clear
/usr/local/bin/ea-php83 artisan cache:clear

# Generate production caches
/usr/local/bin/ea-php83 artisan config:cache
/usr/local/bin/ea-php83 artisan route:cache
/usr/local/bin/ea-php83 artisan view:cache

# Generate app key (if needed)
/usr/local/bin/ea-php83 artisan key:generate --force

# Run migrations
/usr/local/bin/ea-php83 artisan migrate --force

# Create storage link
/usr/local/bin/ea-php83 artisan storage:link
```

**Option B: Via Web Runner (No SSH Access)**
🎯 **Use the included web-based Artisan runner**

1. **Set Up Secure Key:**
   - Edit `/public_html/sales-system/runner.php`
   - Edit `/public_html/sales-system/artisan-runner.php`
   - Replace `YOUR_SECURE_KEY_HERE` with a strong, unique key (e.g., `MyS3cur3K3y!2024`)
   - Keep this key private and secure!

2. **Access the Artisan Runner:**
   - URL: `https://yourdomain.com/sales-system/artisan-runner.php?key=YOUR_ACTUAL_KEY`
   - Or the simple version: `https://yourdomain.com/sales-system/runner.php?key=YOUR_ACTUAL_KEY`

3. **Run these commands in order:**
   - `config:cache` - Cache configuration
   - `route:cache` - Cache routes
   - `view:cache` - Cache views
   - `migrate` - Set up database
   - `storage:link` - Link storage directory
   - `about` - Verify Laravel installation

4. **Example URLs:**
   ```
   https://yourdomain.com/sales-system/artisan-runner.php?key=YOUR_ACTUAL_KEY&cmd=config:cache
   https://yourdomain.com/sales-system/artisan-runner.php?key=YOUR_ACTUAL_KEY&cmd=migrate
   ```

5. **🚨 IMPORTANT: Delete runner files after deployment!**
   ```
   Delete: /public_html/sales-system/runner.php
   Delete: /public_html/sales-system/artisan-runner.php
   ```

## 📁 Directory Structure After Deployment

```
/home/binapain/
├── sales-system/              # Laravel application
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   └── .env
├── public_html/
│   └── sales-system/          # Public web directory
│       ├── assets/
│       ├── css/
│       ├── js/
│       ├── index.php          # Modified to point to /home/binapain/sales-system
│       └── .htaccess
```

## 🔧 Manual Steps (if .cpanel.yml fails)

### 1. File Manager Method

1. **Upload Files:**
   - Extract repository to `/home/binapain/sales-system/`
   - Copy `public/*` to `/home/binapain/public_html/sales-system/`

2. **Edit public/index.php:**
   ```php
   // Change this line:
   require __DIR__.'/../vendor/autoload.php';
   // To:
   require '/home/binapain/sales-system/vendor/autoload.php';

   // Change this line:
   $app = require_once __DIR__.'/../bootstrap/app.php';
   // To:
   $app = require_once '/home/binapain/sales-system/bootstrap/app.php';
   ```

3. **Create PHP Script for Artisan Commands:**
   ```php
   <?php
   // Create: /home/binapain/public_html/sales-system/deploy.php
   system('/usr/local/bin/ea-php83 /home/binapain/sales-system/artisan config:cache');
   system('/usr/local/bin/ea-php83 /home/binapain/sales-system/artisan route:cache');
   system('/usr/local/bin/ea-php83 /home/binapain/sales-system/artisan view:cache');
   echo "Deployment completed!";
   ?>
   ```

## 🐛 Common Issues & Solutions

### Issue 1: "Class not found" Errors
**Solution:**
```bash
composer dump-autoload --optimize
/usr/local/bin/ea-php83 artisan config:clear
```

### Issue 2: Permission Denied
**Solution:**
```bash
chmod -R 755 /home/binapain/sales-system
chmod -R 777 /home/binapain/sales-system/storage
```

### Issue 3: Database Connection Failed
**Solution:**
- Verify database credentials in `.env`
- Check database user permissions
- Ensure database exists

### Issue 4: 500 Internal Server Error
**Solution:**
1. Check error logs: `/home/binapain/sales-system/storage/logs/laravel.log`
2. Verify file permissions
3. Check `.htaccess` file
4. Ensure all dependencies are installed

### Issue 5: Assets Not Loading
**Solution:**
```bash
# Generate asset manifest
/usr/local/bin/ea-php83 artisan config:cache
# Verify public path in config/app.php
```

## 🔐 Security Considerations

1. **Hide Laravel Application:**
   - Never expose `/home/binapain/sales-system/` via web
   - Only public folder should be web-accessible

2. **Environment Security:**
   ```bash
   chmod 600 /home/binapain/sales-system/.env
   ```

3. **Database Security:**
   - Use strong passwords
   - Limit user privileges
   - Regular backups

4. **File Upload Security:**
   - Configure proper MIME type checking
   - Set upload size limits
   - Scan uploaded files

## 📊 Post-Deployment Verification

1. **Access Application:**
   - URL: `https://yourdomain.com/sales-system`
   - Login: Check authentication system

2. **Test Core Features:**
   - ✅ User registration/login
   - ✅ Lead management
   - ✅ Quotation creation
   - ✅ Invoice generation
   - ✅ PDF download
   - ✅ Enhanced builders

3. **Performance Check:**
   - Page load times < 3 seconds
   - Database queries optimized
   - Caching enabled

## 📱 Mobile & Responsive Testing

- Test all enhanced builders on mobile devices
- Verify touch interactions work properly
- Check responsive design breakpoints

## 🚀 Production Optimization

1. **Enable OPcache:**
   ```ini
   ; In php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=4000
   ```

2. **Configure Queue Processing:**
   - Set up cron jobs for queue:work
   - Configure reliable queue driver

3. **Backup Strategy:**
   - Daily database backups
   - Weekly full application backups
   - Test restore procedures

## 📞 Support & Maintenance

- **Documentation:** Keep CLAUDE.md updated
- **Monitoring:** Set up error tracking
- **Updates:** Regular Laravel security updates
- **Backups:** Automated backup verification

---

## 🎯 Quick Deployment Commands

```bash
# One-liner for post-deployment setup:
cd /home/binapain/sales-system && /usr/local/bin/ea-php83 artisan config:cache && /usr/local/bin/ea-php83 artisan route:cache && /usr/local/bin/ea-php83 artisan view:cache && /usr/local/bin/ea-php83 artisan migrate --force && echo "Deployment complete!"
```

---

**🏆 Your Laravel Sales System is now ready for production on cPanel shared hosting!**