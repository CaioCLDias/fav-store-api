FROM php:8.4-fpm-alpine

# Instala dependências do sistema
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

# Configura e instala extensões do PHP (incluindo PostgreSQL)
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
 && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
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

# Copia o código do projeto
COPY . .

# Instala Composer (não roda artisan aqui para evitar erro do vendor)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# O start.sh vai rodar composer install, artisan, migrations etc.
CMD ["sh", "/usr/local/bin/start.sh"]
