# cPanel Deployment Guide (File Manager Method)

Complete step-by-step guide to deploy your Laravel application to cPanel shared hosting **without terminal/SSH access**.

## 🚀 DEPLOYMENT STATUS: ✅ **SUCCESSFULLY COMPLETED**

### ✅ ALL DEPLOYMENT STEPS COMPLETED:
1. **Repository Setup**: Laravel code cloned from GitHub to cPanel (`sales-system` folder) ✅
2. **Subdomain Configuration**: `sales.binapaint.com.my` → `/home/binapain/sales-system/public/` ✅
3. **File Permissions**: Folders (755) and files (644) properly set ✅
4. **PHP Version**: Web interface using PHP 8.4.11 ✅
5. **Vendor Dependencies**: Complete vendor directory with all Laravel packages ✅
6. **Bootstrap Files**: `app.php` and `providers.php` manually uploaded to `/bootstrap/` ✅
7. **Environment Configuration**: `.env` file with APP_KEY and database credentials ✅
8. **Laravel Welcome Page**: Successfully loads at `https://sales.binapaint.com.my` ✅
9. **Database Migration**: Successfully ran via `runner.php` workaround ✅
10. **Laravel Application**: Login/register pages working, Vite assets built ✅

### 🎉 FINAL SOLUTION: Runner.php Workaround
- **Problem**: cPanel lacks terminal access for Artisan commands
- **Solution**: Created `runner.php` in `/public` directory to execute Artisan via web interface
- **Security**: Secret key protection to prevent unauthorized access
- **Result**: All migrations and seeders successfully executed

### 🎯 APPLICATION STATUS: **FULLY FUNCTIONAL**
- Laravel welcome page loads ✅
- Database tables created ✅
- Users and roles seeded ✅
- Login/register functionality working ✅
- Vite assets compiled and uploaded ✅

## 🎯 SUCCESSFUL DEPLOYMENT METHOD USED

### The Runner.php Solution (Recommended for cPanel)

Since cPanel shared hosting doesn't provide terminal/SSH access, we used a smart workaround:

**1. Setup & File Upload:**
- Clone Laravel repository from GitHub → upload to cPanel
- Create subdomain `sales.binapaint.com.my` → document root `/home/binapain/sales-system/public`
- Upload required files:
  - `vendor/` directory (dependencies)
  - `bootstrap/` files (app.php, providers.php)
  - `.env` file (APP_KEY + DB credentials)

**2. Server Configuration:**
- Update PHP version for subdomain → 8.4.11
- Set proper file/folder permissions (755 for folders, 644 for files)
- Confirm subdomain points correctly → Laravel welcome page loads

**3. Runner Setup (Because cPanel has no terminal):**
- Create `runner.php` file in `/public` directory
- Function: Execute Artisan commands via web interface (e.g., migrate:fresh --seed --force)
- Use URL with secret key so not everyone can access
- From there → run migrations & seeders

**4. Database Migration & Seeding:**
- Run migrate:fresh --seed using runner
- All tables created (users, companies, invoices, quotations, proofs, warranties, etc.)
- Seeders run → RolePermissionSeeder sets up roles/permissions

**5. Application Verification:**
- Laravel landing page works ✅
- `/login` initially shows 500 error → because Vite assets missing
- Local build (`npm run build`) → upload `/public/build` to server
- Error resolved → login & register pages now working ✅

### Runner.php Example Code:
```php
<?php
// Place this in /public/runner.php
$secret = 'your-secret-key-here';
if ($_GET['key'] !== $secret) {
    die('Unauthorized');
}

$command = $_GET['cmd'] ?? 'list';
$output = shell_exec("php ../artisan $command 2>&1");
echo "<pre>$output</pre>";
?>
```

**Usage:** `https://sales.binapaint.com.my/runner.php?key=your-secret&cmd=migrate:fresh --seed --force`

---

## Prerequisites

- cPanel hosting account with:
  - PHP 8.3+ support (✅ Configured)
  - MySQL database access (✅ Already set up)
  - Git version control feature (✅ Used)
  - File Manager access (✅ Working)
  - **Note**: This guide assumes NO SSH/Terminal access (common for shared hosting)

## Step 1: Database Setup ✅ ALREADY DONE

Since your database credentials are already set up, you can skip the database creation steps. You should have:
```
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

## Step 2: GitHub Authentication & Repository Setup

### Method 1: Make Repository Public (Recommended for cPanel)

1. **Make Repository Public:**
   - Go to your GitHub repository: `https://github.com/Ibrahimrosli95/invoicing-system`
   - Click **Settings** (repository settings, not account settings)
   - Scroll down to **Danger Zone**
   - Click **"Change repository visibility"**
   - Select **"Make public"**
   - Type the repository name to confirm
   - Click **"I understand, change repository visibility"**

2. **Clone Public Repository in cPanel:**
   - **Login to cPanel** → Find **"Git™ Version Control"**
   - **Clone Repository:**
     - Repository URL: `https://github.com/Ibrahimrosli95/invoicing-system.git`
     - Repository Path: `sales-system` (this will be your folder name)
     - Repository Name: `Bina Sales System`
     - Click **"Create"**

### Method 2: Use Personal Access Token (For Private Repository)

1. **Create GitHub Personal Access Token:**
   - Go to GitHub → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)**
   - Click **"Generate new token (classic)"**
   - **Note:** `cPanel deployment access`
   - **Expiration:** Choose appropriate duration
   - **Scopes:** Check `repo` (Full control of private repositories)
   - Click **"Generate token"**
   - **Copy the token immediately** (you won't see it again!)

2. **Clone with Token in cPanel:**
   - **Login to cPanel** → Find **"Git™ Version Control"**
   - **Clone Repository:**
     - Repository URL: `https://YOUR_TOKEN@github.com/Ibrahimrosli95/invoicing-system.git`
     - Replace `YOUR_TOKEN` with the token you just created
     - Repository Path: `sales-system`
     - Repository Name: `Bina Sales System`
     - Click **"Create"**

### Method 3: Manual Upload (Fallback)

If Git cloning fails, you can manually upload the files:

1. **Download Repository as ZIP:**
   - Go to your GitHub repository
   - Click the green **"Code"** button
   - Select **"Download ZIP"**
   - Extract the ZIP file on your computer

2. **Upload via File Manager:**
   - **cPanel** → **File Manager**
   - Navigate to your home directory
   - Create folder named `sales-system`
   - Upload all extracted files to this folder
   - Make sure the `public` folder is inside `sales-system`

3. **Note the path** shown (usually something like `/home/yourusername/sales-system`)

## Step 3: Domain Configuration

### Method 1: Subdomain (Recommended)
1. **cPanel → Subdomains**
2. **Create subdomain:** `app.yourdomain.com`
3. **Document Root:** Set to `/home/yourusername/sales-system/public`

### Method 2: Main Domain Redirect
If using your main domain, create `.htaccess` file in your domain root folder:
1. Go to **File Manager** → Navigate to `public_html/`
2. Create new file named `.htaccess`
3. Add this content:
```apache
RewriteEngine On
RewriteRule ^(.*)$ sales-system/public/$1 [L]
```

## Step 4: Environment Configuration (File Manager Method)

1. **Open File Manager** → Navigate to your `sales-system` folder

2. **Copy the environment template:**
   - Right-click `.env.example.prod` → Copy
   - Paste in same folder and rename to `.env`

3. **Edit the `.env` file:**
   - Right-click `.env` → Edit
   - Update with your information:
   ```env
   APP_NAME="Bina Sales System"
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://app.yourdomain.com

   # Your Database Configuration (already set up)
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password

   # Email Configuration (optional)
   MAIL_MAILER=smtp
   MAIL_HOST=mail.yourdomain.com
   MAIL_PORT=587
   MAIL_USERNAME=noreply@yourdomain.com
   MAIL_PASSWORD=your_email_password
   MAIL_FROM_ADDRESS=noreply@yourdomain.com
   ```

## Step 5: Deployment Process (File Manager Method)

Since most cPanel shared hosting doesn't provide SSH access, we'll use **cPanel Terminal** (if available) or **File Manager** workarounds.

### Method 1: Using cPanel Terminal (If Available)

Some cPanel hosts provide a Terminal feature in cPanel:

1. **cPanel → Terminal** (if available)
2. **Navigate to your project:**
   ```bash
   cd sales-system
   ```
3. **Run the automated deployment script:**
   ```bash
   chmod +x .cpanel_deploy/post_deploy.sh
   ./.cpanel_deploy/post_deploy.sh
   ```

### Method 2: File Manager Workaround (No Terminal)

If no Terminal access, we'll create PHP scripts to run the necessary commands:

#### Step 5a: Create Deployment Script

1. **File Manager** → Navigate to `sales-system` folder
2. **Create new file:** `deploy.php`
3. **Add this content:**

```php
<?php
// Simple deployment script for cPanel without SSH
echo "<h2>Deployment Script for Bina Sales System</h2>";

// Change to project directory
$projectPath = __DIR__;
echo "<p>Project Path: $projectPath</p>";

// Generate Application Key
echo "<h3>1. Generating Application Key</h3>";
$output = shell_exec('php artisan key:generate --force 2>&1');
echo "<pre>$output</pre>";

// Create Storage Link
echo "<h3>2. Creating Storage Link</h3>";
$output = shell_exec('php artisan storage:link 2>&1');
echo "<pre>$output</pre>";

// Run Migrations
echo "<h3>3. Running Database Migrations</h3>";
$output = shell_exec('php artisan migrate --force 2>&1');
echo "<pre>$output</pre>";

// Seed Database
echo "<h3>4. Seeding Database</h3>";
$output = shell_exec('php artisan db:seed --force 2>&1');
echo "<pre>$output</pre>";

// Cache Configuration
echo "<h3>5. Caching Configuration</h3>";
$output = shell_exec('php artisan config:cache 2>&1');
echo "<pre>$output</pre>";

$output = shell_exec('php artisan route:cache 2>&1');
echo "<pre>$output</pre>";

$output = shell_exec('php artisan view:cache 2>&1');
echo "<pre>$output</pre>";

// Set Permissions
echo "<h3>6. Setting Permissions</h3>";
$storage = $projectPath . '/storage';
$bootstrap = $projectPath . '/bootstrap/cache';

if (chmod($storage, 0775)) {
    echo "<p>✅ Storage permissions set</p>";
} else {
    echo "<p>⚠️ Could not set storage permissions</p>";
}

if (chmod($bootstrap, 0775)) {
    echo "<p>✅ Bootstrap cache permissions set</p>";
} else {
    echo "<p>⚠️ Could not set bootstrap permissions</p>";
}

echo "<h3>✅ Deployment Complete!</h3>";
echo "<p><strong>Important:</strong> Delete this deploy.php file after deployment for security!</p>";
?>
```

#### Step 5b: Run Deployment Script

1. **Visit your deployment script:** `https://app.yourdomain.com/deploy.php`
2. **Wait for all steps to complete**
3. **Check for any error messages**
4. **DELETE the deploy.php file immediately after use!**

### Method 3: Manual File Manager Setup

If PHP scripts don't work, follow these manual steps:

#### 5c.1: Generate Application Key
1. **Create file:** `generate_key.php`
2. **Content:**
```php
<?php
require_once 'vendor/autoload.php';
$key = 'base64:' . base64_encode(random_bytes(32));
echo "Generated key: " . $key;
// Manually add this to your .env file as APP_KEY=
?>
```
3. **Run it, copy the key, add to `.env`**
4. **Delete the file**

## Step 6: Cron Jobs Setup

### Laravel Scheduler (Required)
1. **cPanel → Cron Jobs**
2. **Add new cron job (runs every minute):**
   ```
   * * * * * /usr/local/bin/php /home/yourusername/sales-system/artisan schedule:run >> /dev/null 2>&1
   ```

### Finding Your PHP Path
Since paths vary by host, try these common locations:
- `/usr/local/bin/php` (most common)
- `/usr/bin/php`
- `/opt/cpanel/ea-php81/root/usr/bin/php`
- `/opt/cpanel/ea-php82/root/usr/bin/php`

### Test PHP Path
Create a temporary file `phpinfo.php` in your sales-system folder:
```php
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP Path: " . PHP_BINARY . "\n";
phpinfo();
?>
```
Visit it to see your PHP path, then delete the file.

## Step 7: Verification & Testing

### Test Your Application

1. **Visit your application:** `https://app.yourdomain.com`
2. **You should see the login page**
3. **Test login with these default accounts:**
   - **Admin:** `admin@binagroup.com` / `admin123`
   - **Sales:** `test@example.com` / `password`

### Verification Checklist

✅ **Application loads without errors**
✅ **Login page appears**
✅ **Database connection working** (no connection errors)
✅ **File uploads work** (try creating a user avatar)
✅ **Navigation menus display correctly**
✅ **No PHP errors** (check cPanel Error Logs)

### Check Error Logs
- **cPanel → Errors** (check for PHP/Laravel errors)
- **File Manager:** `sales-system/storage/logs/laravel.log`

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

## Maintenance (File Manager Method)

### Regular Updates
1. **Update Code via cPanel Git:**
   - Go to **cPanel → Git™ Version Control**
   - Click on your repository name
   - Click **"Pull or Deploy"** → **"Update from Remote"**
   - Wait for update to complete

2. **Run Update Script:**
   - Create `update.php` in your sales-system folder:
   ```php
   <?php
   // Update script for cPanel maintenance
   echo "<h2>System Update</h2>";

   // Clear caches
   echo "<h3>1. Clearing Caches</h3>";
   $output = shell_exec('php artisan cache:clear 2>&1');
   echo "<pre>$output</pre>";

   $output = shell_exec('php artisan config:clear 2>&1');
   echo "<pre>$output</pre>";

   $output = shell_exec('php artisan view:clear 2>&1');
   echo "<pre>$output</pre>";

   // Run any new migrations
   echo "<h3>2. Running New Migrations</h3>";
   $output = shell_exec('php artisan migrate --force 2>&1');
   echo "<pre>$output</pre>";

   // Rebuild caches
   echo "<h3>3. Rebuilding Caches</h3>";
   $output = shell_exec('php artisan config:cache 2>&1');
   echo "<pre>$output</pre>";

   $output = shell_exec('php artisan route:cache 2>&1');
   echo "<pre>$output</pre>";

   $output = shell_exec('php artisan view:cache 2>&1');
   echo "<pre>$output</pre>";

   echo "<h3>✅ Update Complete!</h3>";
   echo "<p><strong>Remember:</strong> Delete this update.php file after use!</p>";
   ?>
   ```
   - Visit: `https://app.yourdomain.com/update.php`
   - **Delete the update.php file immediately after use!**

### Cache Management (File Manager)
**Clear Caches Script** (`clear-cache.php`):
```php
<?php
// Cache clearing script
echo "<h2>Cache Management</h2>";

echo "<h3>Clearing Application Cache</h3>";
$output = shell_exec('php artisan cache:clear 2>&1');
echo "<pre>$output</pre>";

echo "<h3>Clearing Config Cache</h3>";
$output = shell_exec('php artisan config:clear 2>&1');
echo "<pre>$output</pre>";

echo "<h3>Clearing View Cache</h3>";
$output = shell_exec('php artisan view:clear 2>&1');
echo "<pre>$output</pre>";

echo "<h3>Clearing Route Cache</h3>";
$output = shell_exec('php artisan route:clear 2>&1');
echo "<pre>$output</pre>";

echo "<p>✅ All caches cleared!</p>";
echo "<p><strong>Delete this file after use!</strong></p>";
?>
```

### File Permissions (File Manager)
**Set Permissions Script** (`fix-permissions.php`):
```php
<?php
// File permissions script
echo "<h2>File Permissions Fix</h2>";

$storage = __DIR__ . '/storage';
$bootstrap = __DIR__ . '/bootstrap/cache';

// Function to recursively set permissions
function setPermissions($dir, $permission) {
    if (is_dir($dir)) {
        chmod($dir, $permission);
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    setPermissions($path, $permission);
                } else {
                    chmod($path, $permission);
                }
            }
        }
    }
}

// Set storage permissions
echo "<h3>Setting Storage Permissions</h3>";
try {
    setPermissions($storage, 0775);
    echo "<p>✅ Storage permissions set to 775</p>";
} catch (Exception $e) {
    echo "<p>⚠️ Error setting storage permissions: " . $e->getMessage() . "</p>";
}

// Set bootstrap cache permissions
echo "<h3>Setting Bootstrap Cache Permissions</h3>";
try {
    setPermissions($bootstrap, 0775);
    echo "<p>✅ Bootstrap cache permissions set to 775</p>";
} catch (Exception $e) {
    echo "<p>⚠️ Error setting bootstrap permissions: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Delete this file after use!</strong></p>";
?>
```

## 🔧 Current Issue Resolution: PHP Extension Mismatch

### **Problem**: Laravel Artisan Commands Fail
**Symptoms:**
- Laravel welcome page loads successfully ✅
- Login shows 500 error ❌
- Artisan commands fail with: `Call to undefined function Illuminate\Support\mb_split()`

### **Root Cause**:
cPanel has separate PHP environments:
- **Web PHP (8.4.11)**: Has mbstring extension (Laravel web interface works)
- **CLI PHP**: Missing mbstring extension (Artisan commands fail)

### **Solution Steps**:

#### **Step 1: Enable mbstring Extension**
1. **cPanel → Select PHP Version** (or "PHP Selector")
2. **Click "Extensions" tab**
3. **Enable checkboxes:**
   - ☑️ **mbstring** (critical)
   - ☑️ **fileinfo** (recommended)
4. **Click "Save"**

#### **Step 2: Test Extension Fix**
Create `test-extensions.php` in `/public/` folder:
```php
<?php
echo "<h2>PHP Extension Test</h2>";
$output = shell_exec('/opt/cpanel/ea-php84/root/usr/bin/php -m | grep mbstring 2>&1');
if (strpos($output, 'mbstring') !== false) {
    echo "<p>✅ mbstring enabled for CLI</p>";

    // Test Artisan
    $output = shell_exec('/opt/cpanel/ea-php84/root/usr/bin/php artisan --version 2>&1');
    if (strpos($output, 'Laravel Framework') !== false) {
        echo "<p>🎉 Artisan working! Ready for database setup.</p>";
    }
} else {
    echo "<p>❌ mbstring still missing</p>";
}
?>
```

#### **Step 3: Run Database Migrations**
Once Artisan works, create `database-setup.php`:
```php
<?php
$php = '/opt/cpanel/ea-php84/root/usr/bin/php';
chdir(__DIR__ . '/..');

echo "<h2>Database Setup</h2>";
$output = shell_exec("$php artisan migrate --force 2>&1");
echo "<pre>$output</pre>";

$output = shell_exec("$php artisan db:seed --force 2>&1");
echo "<pre>$output</pre>";

echo "<p>Setup complete! <a href='/login'>Test Login</a></p>";
?>
```

### **Expected Result**:
- Login page works without 500 error
- Can login with: `admin@binagroup.com` / `admin123`

---

### Monitoring & Logs
1. **View Laravel Logs:**
   - **File Manager** → Navigate to `sales-system/storage/logs/`
   - Download and view `laravel.log` file
   - Check for error messages and warnings

2. **Error Log Locations:**
   - **Laravel Logs:** `sales-system/storage/logs/laravel.log`
   - **cPanel Error Logs:** Available in cPanel → Errors section
   - **PHP Error Logs:** Usually in `public_html/error_logs/`

3. **Check Application Health:**
   - Create `health-check.php`:
   ```php
   <?php
   require_once 'vendor/autoload.php';

   echo "<h2>Application Health Check</h2>";

   // Check database connection
   echo "<h3>Database Connection</h3>";
   try {
       $pdo = new PDO('mysql:host=localhost;dbname=your_database_name', 'your_username', 'your_password');
       echo "<p>✅ Database connection successful</p>";
   } catch (PDOException $e) {
       echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
   }

   // Check storage directories
   echo "<h3>Directory Permissions</h3>";
   $dirs = ['storage', 'bootstrap/cache'];
   foreach ($dirs as $dir) {
       if (is_writable($dir)) {
           echo "<p>✅ {$dir} is writable</p>";
       } else {
           echo "<p>❌ {$dir} is not writable</p>";
       }
   }

   // Check .env file
   echo "<h3>Configuration</h3>";
   if (file_exists('.env')) {
       echo "<p>✅ .env file exists</p>";
   } else {
       echo "<p>❌ .env file missing</p>";
   }

   echo "<p><strong>Delete this file after checking!</strong></p>";
   ?>
   ```

---

## Quick Reference

**Key Paths to Remember:**
- Repository: `/home/yourusername/sales-system`
- Public folder: `/home/yourusername/sales-system/public`
- Logs: `/home/yourusername/sales-system/storage/logs/laravel.log`
- Environment: `/home/yourusername/sales-system/.env`

**File Manager Utilities:**
```php
// Essential maintenance scripts (create as needed, delete after use)
deploy.php          // Initial deployment
update.php          // Regular updates
clear-cache.php     // Cache management
fix-permissions.php // File permissions
health-check.php    // System diagnostics
generate_key.php    // Emergency key generation
```

**No-Terminal Essential Tasks:**
- **Deploy:** Run `deploy.php` via browser
- **Update:** Use cPanel Git + `update.php`
- **Cache:** Run `clear-cache.php`
- **Permissions:** Run `fix-permissions.php`
- **Monitor:** Check `health-check.php` and log files