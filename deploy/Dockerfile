FROM php:8.4-fpm-alpine

# Dependências do sistema e extensões PHP
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

# Fuso horário
ENV TZ=America/Sao_Paulo

# Diretório de trabalho
WORKDIR /var/www/html

# Copia código-fonte
COPY . .

# Instala Composer (não roda artisan aqui)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# O start.sh vai cuidar do resto (composer install, artisan, migrate, seed, etc.)
CMD ["sh", "/usr/local/bin/start.sh"]
