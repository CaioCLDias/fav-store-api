FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    zip \
    icu-dev \
    mysql-client \
    supervisor

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
 && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
        gd \
        opcache

ENV TZ=America/Sao_Paulo

WORKDIR /var/www/html

COPY . .


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache


CMD ["php", "-S", "0.0.0.0:9000", "-t", "public"]