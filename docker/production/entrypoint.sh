#!/bin/sh
set -e

export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

# 起動時に実際のランタイム環境変数を使ってconfig cacheを作る。
# ビルド時点ではRailwayのランタイム環境変数(ANTHROPIC_API_KEY, DB_URL等)が
# まだ存在しないため、ビルド時にconfig:cacheすると空値が焼き付いてしまう。
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
