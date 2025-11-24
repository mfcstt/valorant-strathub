FROM php:8.2-cli
RUN apt-get update && apt-get install -y libcurl4-openssl-dev libpq-dev git unzip && docker-php-ext-install pdo pdo_pgsql curl && rm -rf /var/lib/apt/lists/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction || true
COPY . .
CMD php -S 0.0.0.0:$PORT -t public public/dev-router.php

