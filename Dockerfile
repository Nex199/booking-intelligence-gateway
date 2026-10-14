FROM php:8.4-cli
WORKDIR /var/www
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    git \
    unzip \
    && docker-php-ext-install pdo_pgsql pcntl bcmath \
    && pecl channel-update pecl.php.net \
    && pecl install redis \
    && docker-php-ext-enable redis
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . .
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]