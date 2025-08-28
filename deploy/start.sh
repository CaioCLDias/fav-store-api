#!/bin/bash
set -e

echo "🔧 Setting permissions for storage and cache directories..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "📦 Installing PHP dependencies with Composer (idempotent)..."
composer install --no-dev --optimize-autoloader --no-interaction || true

echo "🧼 Clearing and caching Laravel configuration..."
cp -f deploy/.env.prod .env   
php artisan key:generate --force
php artisan jwt:secret --force
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🛠️ Running database migrations and seeders..."
# Tenta algumas vezes enquanto o Postgres sobe (rede externa pode demorar)
for i in {1..10}; do
  if php artisan migrate --force; then
    break
  fi
  echo "⏳ DB not ready yet, retrying in 5s ($i/10)..."
  sleep 5
done
php artisan db:seed --force || true

echo "📄 Generating Swagger API documentation..."
php artisan l5-swagger:generate || true


echo "✅ Startup complete. Launching PHP..."
exec php -S 0.0.0.0:9000 -t public
