#!/usr/bin/env bash
set -e

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Clearing Laravel caches..."
php artisan optimize:clear

echo "Starting queue worker in background..."
nohup php artisan queue:work redis --queue=high,default,broadcasts,notifications,media,ai --sleep=1 --tries=3 --timeout=90 --max-jobs=1000 >> /var/www/html/storage/logs/queue-worker.log 2>&1 &

echo "Starting Reverb server in background..."
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 >> /var/www/html/storage/logs/reverb.log 2>&1 &

echo "Starting Laravel on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"