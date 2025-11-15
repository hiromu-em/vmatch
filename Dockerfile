# Railway / Railpack が使用しているベースイメージ
FROM php:8.4-apache

# 必要なパッケージをインストールして psql ドライバを有効化
RUN apt-get update && \
    apt-get install -y git zip unzip libpq-dev && \
    docker-php-ext-install pdo_pgsql pgsql

WORKDIR /app

COPY . /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Composer install
RUN composer install --optimize-autoloader --no-scripts --no-interaction

RUN sed -i 's/80/8080/' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 8080
