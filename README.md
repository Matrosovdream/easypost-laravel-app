# ShipDesk

Role-based shipping platform built on Laravel + Vue + EasyPost.

## Stack

- Laravel 12 · PHP 8.3
- Vue 3 · TypeScript · PrimeVue · Tailwind v4
- Postgres · Redis · Horizon · Reverb · Meilisearch

## Quick start

```bash
cp .env.example .env
docker compose -f compose.dev.yaml up -d
docker compose -f compose.dev.yaml exec workspace npm install
docker compose -f compose.dev.yaml exec workspace npm run dev
```

Then open:

- App → http://localhost:8080
- Vite HMR → http://localhost:5173
- Adminer → http://localhost:9091

## Tests

```bash
docker compose -f compose.dev.yaml exec php-fpm php artisan test
```
