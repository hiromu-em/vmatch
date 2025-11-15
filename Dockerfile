# Railway / Railpack が使用しているベースイメージ
FROM dunglas/frankenphp:php8.4.14-bookworm

# 必要なパッケージをインストールして psql ドライバを有効化
RUN apt-get update && \
    apt-get install -y git zip unzip libpq-dev && \
    docker-php-ext-install pdo_pgsql pgsql

# Composer を使用するためにコピー（Railway のビルドログ参考）
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# アプリを /app にコピー
COPY . /app

# Composer install（Railway の build と整合性を取る）
RUN composer install --optimize-autoloader --no-scripts --no-interaction

