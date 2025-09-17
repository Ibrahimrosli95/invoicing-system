# Laravel Development Makefile
# Usage: make <target>

.PHONY: help dev-up dev-down migrate fresh seed test clean backup

# Default target
help:
	@echo "Laravel Development Commands:"
	@echo ""
	@echo "  dev-up      Start development database (MySQL Docker)"
	@echo "  dev-down    Stop development database"
	@echo "  migrate     Run database migrations"
	@echo "  fresh       Fresh database migration"
	@echo "  seed        Run database seeders"
	@echo "  test        Run PHPUnit tests"
	@echo "  clean       Clear all Laravel caches"
	@echo "  backup      Create database backup"
	@echo ""
	@echo "Example: make dev-up && make migrate"

# Development Environment
dev-up:
	@echo "🚀 Starting development environment..."
	./bin/dev-up

dev-down:
	@echo "🛑 Stopping development environment..."
	./bin/dev-down

# Database Operations
migrate:
	@echo "🗄️  Running database migrations..."
	php artisan migrate

fresh:
	@echo "🔄 Fresh database migration..."
	php artisan migrate:fresh

seed:
	@echo "🌱 Running database seeders..."
	php artisan db:seed

# Combined database operations
fresh-seed: fresh seed
	@echo "✅ Fresh database with seeds completed"

# Testing
test:
	@echo "🧪 Running PHPUnit tests..."
	php artisan test

# Maintenance
clean:
	@echo "🧹 Clearing Laravel caches..."
	php artisan optimize:clear

backup:
	@echo "💾 Creating database backup..."
	./bin/db-backup

# Development workflow shortcuts
setup: dev-up migrate seed
	@echo "✅ Development environment ready!"

reset: dev-down dev-up fresh seed
	@echo "✅ Environment reset completed!"

# Production deployment helpers
deploy-check:
	@echo "🔍 Checking deployment readiness..."
	php artisan config:cache --dry-run
	php artisan route:cache --dry-run
	composer validate
	@echo "✅ Deployment checks passed"

# Quick status check
status:
	@echo "📊 Environment Status:"
	@echo -n "Database: "
	@docker compose -f docker/docker-compose.yml ps db --format "table {{.State}}" 2>/dev/null || echo "Not running"
	@echo -n "Laravel: "
	@curl -s -o /dev/null -w "HTTP %{http_code}" http://localhost:8000 2>/dev/null || echo "Not running"
	@echo ""