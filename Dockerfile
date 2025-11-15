# Railway / Railpack が使用しているベースイメージ
FROM php:8.4-fpm

# 必要なパッケージをインストールして psql ドライバを有効化
RUN apt-get update && \
    apt-get install -y git zip unzip libpq-dev && \
    docker-php-ext-install pdo_pgsql pgsql

# Composer を使用するためにコピー
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# アプリを /app にコピー
COPY . /app

# Composer install
RUN composer install --optimize-autoloader --no-scripts --no-interaction
