# 本番(Railway)用のイメージ。ローカル開発はdocker-compose.yml(app/nginx/postgresの3コンテナ)を
# 使い続けるが、本番はnginx+php-fpmを1コンテナにまとめてシンプルにデプロイする。

# ---- Stage 1: フロントエンド資産のビルド ----
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources/ resources/
COPY vite.config.js ./
RUN npm run build

# ---- Stage 2: Composer依存関係のインストール ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev

# ---- Stage 3: 本番実行イメージ ----
FROM php:8.4-fpm-alpine

# gettextはenvsubst(entrypoint.shでPORTをnginx設定へ反映するため)に必要。
RUN apk add --no-cache nginx supervisor gettext postgresql-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath zip opcache

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build

COPY docker/production/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# .dockerignoreでstorage/framework配下のログ・キャッシュの中身は除外しているため、
# ビルドコンテキストにディレクトリ自体が含まれない。view:cache等が動く前提として
# 空でもディレクトリ構造だけは用意しておく必要がある。
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]
