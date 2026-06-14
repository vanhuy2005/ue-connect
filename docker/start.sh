#!/usr/bin/env bash
set -e

echo "Setting up Nginx PORT..."
export PORT=${PORT:-10000}
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Checking if Career Pathway data needs to be imported..."
php artisan tinker --execute 'try { if (\App\Models\CareerProgram::count() === 0) { echo "Importing Career Pathway data...\n"; \Illuminate\Support\Facades\Artisan::call("career-pathway:import"); } else { echo "Career Pathway data already exists. Skipping import.\n"; } } catch (\Exception $e) { echo "Skipping import: " . $e->getMessage() . "\n"; }'

echo "Clearing Laravel caches..."
php artisan optimize:clear

echo "Fixing permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Starting Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf