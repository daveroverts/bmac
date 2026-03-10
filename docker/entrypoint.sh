#!/bin/sh
set -e

# Run database migrations automatically on startup.
# Uses --force to allow running in production.
# Uses --isolated to prevent multiple containers from migrating at the same time.
php artisan migrate --force --isolated

# Clear and cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the default serversideup entrypoint (PHP-FPM + Nginx)
exec /init
