FROM php:8.4-fpm-alpine

# Pacotes do sistema
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
    postgresql-client \
    postgresql-dev \
    supervisor

# GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" gd

# Extensões PHP (sem comentários e sem espaços após a barra invertida)
RUN docker-php-ext-install -j"$(nproc)" \
    pdo_pgsql \
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

# Composer e deps da app no build
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist


CMD ["php", "-S", "0.0.0.0:9000", "-t", "public"]
