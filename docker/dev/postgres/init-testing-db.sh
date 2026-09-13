#!/bin/sh
# postgresコンテナ初回起動時にのみ実行される(docker-entrypoint-initdb.d)。
# アプリ用DB(POSTGRES_DBで作成済み)とは別に、Pestが使うテスト専用DBを用意する。
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
    CREATE DATABASE laravel_testing OWNER $POSTGRES_USER;
EOSQL
