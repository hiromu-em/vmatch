FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

WORKDIR /app

COPY composer.json composer.lock /app/

RUN composer install --optimize-autoloader --no-scripts --no-interaction
