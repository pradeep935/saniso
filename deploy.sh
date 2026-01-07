#!/bin/bash
# deploy.sh - Manual deployment script for Ecom Saniso
# Usage: ./deploy.sh [production|staging]

set -e

ENVIRONMENT=${1:-staging}

# Set project path based on environment
if [ "$ENVIRONMENT" == "production" ]; then
    PROJECT_PATH="/home/i23/public_html"
    BRANCH="main"
    DB_NAME="ecommerce"
elif [ "$ENVIRONMENT" == "staging" ]; then
    PROJECT_PATH="/home/i23/staging"
    BRANCH="develop"
    DB_NAME="ecommerce_staging"
else
    echo "❌ Error: Environment must be 'production' or 'staging'"
    exit 1
fi

echo "🚀 Starting deployment to $ENVIRONMENT..."

cd "$PROJECT_PATH"

# Verify environment is valid
if [[ "$ENVIRONMENT" != "production" && "$ENVIRONMENT" != "staging" ]]; then
    echo "❌ Error: Environment must be 'production' or 'staging'"
    exit 1
fi

# Backup database before deployment
echo "💾 Creating database backup..."
DB_BACKUP_FILE="backups/pre-deploy-$(date +%Y%m%d-%H%M%S).sql"
mysqldump -u root "${DB_NAME}" > "$DB_BACKUP_FILE" 2>/dev/null || echo "⚠️  Database backup skipped (no DB connection)"

# Fetch latest code
echo "📥 Fetching latest code from GitHub..."
git fetch origin

# Checkout appropriate branch
echo "🔒 Deploying from $BRANCH branch..."
git checkout "$BRANCH" || { echo "❌ Failed to checkout $BRANCH"; exit 1; }
git pull origin "$BRANCH" || { echo "❌ Failed to pull from $BRANCH"; exit 1; }

# Install dependencies
echo "📦 Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "📦 Installing npm dependencies..."
npm ci --production

# Build assets
echo "🔨 Building assets..."
npm run build || echo "⚠️  Asset build skipped"

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Restart services
echo "🔄 Restarting services..."
pm2 restart all || echo "⚠️  PM2 restart skipped"
sudo systemctl restart php-fpm || echo "⚠️  PHP-FPM restart skipped"

# Get current version/commit
CURRENT_SHA=$(git rev-parse --short HEAD)
CURRENT_TAG=$(git describe --tags --always 2>/dev/null || echo "no-tag")

echo ""
echo "✅ Deployment successful!"
echo "   Environment: $ENVIRONMENT"
echo "   Branch: $BRANCH"
echo "   Commit: $CURRENT_SHA"
echo "   Version: $CURRENT_TAG"
echo "   Database backup: $DB_BACKUP_FILE"
echo ""
echo "💡 To rollback:"
echo "   git reset --hard <previous-commit-sha>"
echo "   ./deploy.sh $ENVIRONMENT"
