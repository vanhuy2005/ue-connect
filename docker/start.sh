#!/usr/bin/env bash
set -e

echo "Setting up Nginx PORT..."
export PORT=${PORT:-10000}
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Clearing Laravel caches..."
php artisan optimize:clear

echo "Fixing permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Starting Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf