# Quiz-Portfolio

自由記述の解答をClaude APIが採点する、個人用の学習アプリ(ポートフォリオ提出版)。要件の詳細は [doc/requirements.md](doc/requirements.md) を参照。プロトタイプ(lara-quiz2)での検証を踏まえて再実装し、本番デプロイ(Railway)まで行う。

## 開発環境

ローカルにPHP/Composerは不要。Docker(docker-compose)で完結させる。

```sh
docker compose up -d               # app(PHP-FPM) / nginx / postgres を起動
docker compose exec app composer install
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

- アプリ: http://localhost:8000
- DB: PostgreSQL(サービス名 `db`、ホストからは `localhost:5432`)

テスト実行:

```sh
docker compose exec app php artisan test
```

## 技術スタック

- Laravel + Blade(Livewireは使わない)
- Tailwind CSS + Alpine.js(モバイルファーストのレスポンシブUI)
- PostgreSQL
- AI採点: Anthropic Claude API
- テスト: Pest
- デプロイ: Railway
