#!/usr/bin/env bash
set -e

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Clearing Laravel caches..."
php artisan optimize:clear

echo "Starting Laravel on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"