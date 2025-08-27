FROM php:8.4-fpm-alpine

# Dependências do sistema + extensões
RUN apk add --no-cache \
    bash git curl unzip \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    libxml2-dev oniguruma-dev icu-dev zip \
    postgresql-client postgresql-dev \
    supervisor

# GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) gd

# Extensões PHP
RUN docker-php-ext-install -j$(nproc) \
      pdo_pgsql \   # <<-- Postgres
      mbstring \
      exif \
      pcntl \
      bcmath \
      intl \
      opcache

ENV TZ=America/Sao_Paulo
WORKDIR /var/www/html

# Copia o app
COPY . .

# Composer (instala binário) e dependências da app em build-time
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist


CMD ["php", "-S", "0.0.0.0:9000", "-t", "public"]
