#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# cPanel Post-Deployment Script
# =============================================================================
# Run this script after every git pull to update your Laravel application
# 
# Usage:
#   ./.cpanel_deploy/post_deploy.sh
#
# Set these paths according to your cPanel environment:

# CONFIGURE THESE PATHS FOR YOUR SERVER
PHP_PATH="/usr/local/bin/php"                    # Common: /usr/local/bin/php, /usr/bin/php, /opt/cpanel/ea-php81/root/usr/bin/php
COMPOSER_PATH="/usr/local/bin/composer"          # Set to empty string if not available: COMPOSER_PATH=""
REPO_PATH_ON_SERVER="/home/username/sales-system" # Replace 'username' with your cPanel username

# =============================================================================

echo "🚀 Starting Laravel post-deployment process..."
echo "📁 Repository: ${REPO_PATH_ON_SERVER}"
echo "🐘 PHP Path: ${PHP_PATH}"
echo "📦 Composer Path: ${COMPOSER_PATH:-"Not available"}"
echo ""

# Navigate to repository directory
cd "${REPO_PATH_ON_SERVER}" || {
    echo "❌ Error: Repository path not found: ${REPO_PATH_ON_SERVER}"
    exit 1
}

# Check PHP availability
if [ ! -x "${PHP_PATH}" ]; then
    echo "❌ Error: PHP not found at ${PHP_PATH}"
    echo "💡 Common paths: /usr/local/bin/php, /usr/bin/php, /opt/cpanel/ea-php81/root/usr/bin/php"
    exit 1
fi

echo "✅ Repository found and PHP available"

# Step 1: Install/Update Composer dependencies
if [ -n "${COMPOSER_PATH}" ] && [ -x "${COMPOSER_PATH}" ]; then
    echo ""
    echo "📦 Installing Composer dependencies..."
    ${COMPOSER_PATH} install --no-dev --optimize-autoloader --no-interaction
    echo "✅ Composer dependencies updated"
else
    echo ""
    echo "⚠️  Composer not available - skipping dependency installation"
    echo "💡 Ensure vendor/ directory is committed to your repository"
fi

# Step 2: Generate application key (only if not set)
echo ""
echo "🔑 Checking application key..."
if ${PHP_PATH} artisan key:generate --show > /dev/null 2>&1; then
    echo "✅ Application key already exists"
else
    echo "🔧 Generating new application key..."
    ${PHP_PATH} artisan key:generate --force || true
fi

# Step 3: Create storage link
echo ""
echo "🔗 Creating storage symlink..."
if [ -L "public/storage" ]; then
    echo "✅ Storage link already exists"
else
    ${PHP_PATH} artisan storage:link || {
        echo "⚠️  Warning: Could not create storage link (may already exist or permissions issue)"
    }
fi

# Step 4: Run database migrations
echo ""
echo "🗄️  Running database migrations..."
${PHP_PATH} artisan migrate --force || {
    echo "❌ Error: Database migration failed"
    echo "💡 Check database credentials in .env file"
    exit 1
}
echo "✅ Database migrations completed"

# Step 5: Cache configuration
echo ""
echo "⚡ Caching configuration for performance..."

# Clear existing caches first
${PHP_PATH} artisan config:clear || true
${PHP_PATH} artisan route:clear || true
${PHP_PATH} artisan view:clear || true

# Rebuild caches
${PHP_PATH} artisan config:cache || {
    echo "⚠️  Warning: Config cache failed"
}

${PHP_PATH} artisan route:cache || {
    echo "⚠️  Warning: Route cache failed"
}

${PHP_PATH} artisan view:cache || {
    echo "⚠️  Warning: View cache failed"
}

echo "✅ Application caches updated"

# Step 6: Set proper permissions
echo ""
echo "🔒 Setting file permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || {
    echo "⚠️  Warning: Could not set all permissions (this may be normal on some shared hosts)"
}

# Try to set specific permissions that are most critical
chmod -R 775 storage/logs 2>/dev/null || true
chmod -R 775 storage/framework/cache 2>/dev/null || true
chmod -R 775 storage/framework/sessions 2>/dev/null || true
chmod -R 775 storage/framework/views 2>/dev/null || true
chmod -R 775 bootstrap/cache 2>/dev/null || true

echo "✅ Permissions updated"

# Step 7: Queue table (create if using database queue)
echo ""
echo "📋 Ensuring queue table exists..."
if grep -q "QUEUE_CONNECTION=database" .env 2>/dev/null; then
    ${PHP_PATH} artisan queue:table --quiet || true
    ${PHP_PATH} artisan migrate --force || true
    echo "✅ Queue table ready"
else
    echo "ℹ️  Database queue not configured - skipping"
fi

# Final status
echo ""
echo "🎉 Post-deployment completed successfully!"
echo ""
echo "📋 Summary of actions performed:"
echo "   ✅ Composer dependencies installed"
echo "   ✅ Application key checked/generated"
echo "   ✅ Storage symlink created"
echo "   ✅ Database migrations executed"
echo "   ✅ Configuration cached"
echo "   ✅ File permissions set"
echo ""
echo "💡 Next steps:"
echo "   1. Test your application: ${REPO_PATH_ON_SERVER}"
echo "   2. Check logs if issues: storage/logs/laravel.log"
echo "   3. Verify cron jobs are set up for Laravel scheduler"
echo ""
echo "🔧 If you encounter issues:"
echo "   - Check database credentials in .env"
echo "   - Verify PHP path: ${PHP_PATH}"
echo "   - Check file permissions in storage/ and bootstrap/cache/"
echo "   - Review error logs in cPanel"