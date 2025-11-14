# Railpack のフレンケンPHPイメージをベースにする
FROM dunglas/frankenphp:php8.4.14-bookworm

# 必要なパッケージをインストールして pdo_pgsql を有効化
RUN apt-get update && apt-get install -y \
        libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 作業ディレクトリ
WORKDIR /app

COPY Vmatch/composer.json Vmatch/composer.lock /app/
RUN composer install --optimize-autoloader --no-scripts --no-interaction