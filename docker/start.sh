#!/usr/bin/env bash
set -e

# Sesuaikan port Apache jika Render menyediakan env $PORT
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on port ${PORT}..."
    sed -ri -e "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -ri -e "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/*.conf
fi

echo "Running migrations..."
# Jalankan migration saat startup di Render
php artisan migrate --force

echo "Clearing and caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Apache server..."
exec apache2-foreground
