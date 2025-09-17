# Developer Quickstart Guide

Get your Laravel development environment running on WSL with Docker MySQL.

## Prerequisites

✅ **Windows 11/10 with WSL2 enabled**  
✅ **Docker Desktop for Windows** (with WSL2 integration)  
✅ **Laravel project cloned in WSL**  
✅ **PHP 8.1+ installed in WSL** (`php -v` to verify)

## Quick Setup (5 minutes)

### 1. Initial Configuration
```bash
# Copy development environment template
cp .env.example.dev .env

# Edit .env if you want different database credentials
# nano .env
```

### 2. Start Database
```bash
# Start MySQL container
./bin/dev-up

# Wait for "✅ MySQL is ready!" message
```

### 3. Initialize Application
```bash
# Clear any cached config
php artisan config:clear

# Run database migrations
php artisan migrate

# Seed database (if seeders exist)
php artisan db:seed
```

### 4. Start Laravel
```bash
# Start development server
php artisan serve

# Visit: http://localhost:8000
```

🎉 **You're ready to develop!**

## Daily Development Workflow

### Starting Work
```bash
# Start database (if not running)
./bin/dev-up

# Start Laravel server
php artisan serve
```

### Database Operations
```bash
# Connect to database shell
./bin/db-shell

# Create database backup
./bin/db-backup

# Reset database completely
php artisan migrate:fresh --seed
```

### Stopping Work
```bash
# Stop database container (preserves data)
./bin/dev-down

# Stop Laravel server: Ctrl+C
```

## Useful Commands

### Database Management
```bash
# Run new migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# See migration status
php artisan migrate:status

# Fresh database with seeds
php artisan migrate:fresh --seed
```

### Cache Management
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Or clear everything at once
php artisan optimize:clear
```

### Queue & Jobs
```bash
# Process queue jobs
php artisan queue:work

# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## WSL-Specific Tips

### Database Connection
- MySQL runs in Docker on Windows but accessible from WSL via `127.0.0.1`
- Default port: `3306` (configurable in `.env`)
- Connection is automatic when using `127.0.0.1`

### File Permissions
```bash
# Fix storage permissions if needed
chmod -R 775 storage bootstrap/cache

# Fix script permissions
chmod +x bin/*
```

### Performance Optimization
```bash
# Enable OPcache (if available)
php -m | grep -i opcache

# Use specific PHP version
php8.3 artisan serve
```

## Docker Commands (Advanced)

### Direct Container Management
```bash
# List running containers
docker ps

# View container logs
docker compose -f docker/docker-compose.yml logs -f db

# Execute commands in container
docker compose -f docker/docker-compose.yml exec db mysql -u root -p

# Remove everything (including data)
docker compose -f docker/docker-compose.yml down -v
```

### Volume Management
```bash
# List volumes
docker volume ls

# Inspect MySQL data volume
docker volume inspect bina-invoicing-system_mysql_data

# Backup volume data
docker run --rm -v bina-invoicing-system_mysql_data:/data -v $(pwd)/backups:/backup alpine tar czf /backup/mysql_volume_backup.tar.gz -C /data .
```

## Troubleshooting

### Common Issues

**❌ "Connection refused" Error**
```bash
# Check if Docker is running
docker ps

# Restart MySQL container
./bin/dev-down
./bin/dev-up

# Verify database is accessible
./bin/db-shell
```

**❌ "Access denied for user" Error**
```bash
# Check .env credentials match docker-compose.yml
grep -E "(LOCAL_DB_|DB_)" .env

# Reset database container with new credentials
./bin/dev-down
docker compose -f docker/docker-compose.yml down -v
./bin/dev-up
```

**❌ "Foreign key constraint fails"**
```bash
# Reset database with proper order
php artisan migrate:fresh

# Or disable FK checks temporarily
php artisan tinker
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
# Run your operations
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
```

**❌ Laravel Mix/Vite Issues**
```bash
# Install npm dependencies
npm install

# Development build
npm run dev

# Watch for changes
npm run watch
```

**❌ Permission Denied on Scripts**
```bash
# Fix script permissions
chmod +x bin/*
chmod +x .cpanel_deploy/post_deploy.sh
```

### Performance Issues

**Slow Database Queries:**
```bash
# Enable query log in Docker
# Edit docker/mysql/conf.d/my.cnf:
# general_log = 1
# general_log_file = /var/lib/mysql/general.log

# Restart container
./bin/dev-down && ./bin/dev-up

# View logs
docker compose -f docker/docker-compose.yml exec db tail -f /var/lib/mysql/general.log
```

**High Memory Usage:**
```bash
# Check Laravel memory usage
php artisan tinker
memory_get_peak_usage(true) / 1024 / 1024 . ' MB'

# Optimize autoloader
composer dump-autoload --optimize
```

## Environment Variables

### Development (.env)
```env
# Database (matches docker-compose.yml)
LOCAL_DB_PORT=3306
LOCAL_DB_NAME=sales_system_dev
LOCAL_DB_USER=laravel_user
LOCAL_DB_PASS=secret123

# Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Tools
DEBUGBAR_ENABLED=true
LOG_LEVEL=debug
```

### Customization
- **Change database port:** Edit `LOCAL_DB_PORT` in `.env`
- **Change database name:** Edit `LOCAL_DB_NAME` in `.env`
- **Different PHP version:** Use `php8.1`, `php8.2`, `php8.3` commands
- **Enable Redis:** Add Redis service to `docker-compose.yml`

## Testing

### PHPUnit Tests
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage
```

### Browser Testing (Dusk)
```bash
# Install Laravel Dusk
composer require --dev laravel/dusk
php artisan dusk:install

# Run browser tests
php artisan dusk
```

## Next Steps

1. **Set up IDE:** Configure PHPStorm/VSCode with Laravel plugin
2. **Install Debugging:** Set up Xdebug for step-through debugging
3. **Add Redis:** For session/cache storage (optional)
4. **Configure Mail:** Set up local mail testing (Mailhog)
5. **Enable Queues:** Set up Redis for background job processing

---

## Quick Reference

**Essential Files:**
- `.env` - Environment configuration
- `docker/docker-compose.yml` - Database container config
- `bin/dev-*` - Development helper scripts

**Key Commands:**
- `./bin/dev-up` - Start database
- `./bin/dev-down` - Stop database  
- `./bin/db-shell` - Database terminal
- `./bin/db-backup` - Create backup
- `php artisan serve` - Start Laravel

**Logs:**
- Laravel: `storage/logs/laravel.log`
- MySQL: `docker compose -f docker/docker-compose.yml logs db`

Need help? Check the troubleshooting section above or review Laravel documentation.