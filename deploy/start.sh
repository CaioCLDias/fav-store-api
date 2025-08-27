#!/bin/bash

echo "🔧 Setting permissions for storage and cache directories..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache


echo "📦 Installing PHP dependencies with Composer..."
composer install --no-dev --optimize-autoloader

echo "🧼 Clearing and caching Laravel configuration..."
cp .env.prod .env
php artisan key:generate
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🛠️ Running database migrations and seeders..."
php artisan migrate --force
php artisan db:seed --force

echo "📄 Generating Swagger API documentation..."
php artisan l5-swagger:generate

echo "🧹 Starting scheduled background commands..."
php artisan users:process-user-deletions &
php artisan chat:update-main-message &
php artisan schedule:work >> storage/logs/schedule.log 2>&1 &

echo "🚀 Testing Ollama API connectivity..."
curl -X POST http://ollama:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{"model": "llama3.2:1b", "prompt": "ready"}' || true

echo "✅ Startup complete. Launching PHP..."
php -S 0.0.0.0:9000 -t public
        