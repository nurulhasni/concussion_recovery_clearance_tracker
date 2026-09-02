#!/bin/bash
set -e

# Adapt Apache port to Render's dynamic $PORT environment variable
PORT=${PORT:-80}
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Cache configuration, routes, and views for optimal production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations and seeders automatically on startup
php artisan migrate --force --seed

# Start Apache in the foreground
exec apache2-foreground
