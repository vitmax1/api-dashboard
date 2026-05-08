#!/bin/bash
set -e

echo "Initializing Laravel application for Railway..."

# Create storage directories if they don't exist
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/database

# Set permissions
chmod -R 775 storage bootstrap/cache

# Create SQLite database if it doesn't exist
touch storage/database/database.sqlite

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Cache configuration
php artisan config:cache

# Run migrations
php artisan migrate --force

echo "Initialization complete!"
