#!/bin/sh
set -e

echo "👉 Adjusting permissions..."
chown -R www-data:www-data /app/var /app/public || true
chmod -R 775 /app/var || true

echo "✅ Starting Symfony (APP_ENV=${APP_ENV:-prod}) on port ${PORT:-8080}"

# Start PHP-FPM in the background
php-fpm -D

# Start Nginx in the foreground (Cloud Run requires this)
exec nginx -c /etc/nginx/nginx.conf -g "daemon off;"
