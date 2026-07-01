# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Personal PEA (French stock savings plan) portfolio tracker. Three services wired together via Docker Compose:

- `backend/app` — Laravel 12 REST API (PHP 8.2, Sanctum cookie auth). SQLite in dev/CI, **MySQL in prod** (external `docker-mysql` container on `antibes`). Runs on **official images**: `php:8.2-fpm-alpine` (`pea_php`) behind `nginx:alpine` (`pea_nginx`) — no custom base image. Multi-stage `backend/Dockerfile` with `prod`/`dev` targets; nginx image built from `backend/docker/nginx/`.
- `frontend/app` — Nuxt 3 SPA (Vue 3, Tailwind v4, Pinia, `nuxt-auth-sanctum`)
- `scraper/` — tiny Flask service that scrapes Yahoo Finance for a ticker's current price

Production runs on a VPS behind Traefik at `api-pea.dev-fullstack.net` (Laravel) and `pea.dev-fullstack.net` (Nuxt). The compose `antibes` network is external and shared with other projects.

## Architecture

### Domain model (Laravel)

Four user-scoped entities. Every row belongs to a user; queries **must** filter by `user_id`.

- **Allocation** — target allocation line (name, `isin`, `ticker`, `type` ETF/Action, `target_percent`). The editable plan.
- **Position** — current holding per `isin` (`quantity`, `current_price`). Exposes an `average_price` accessor computed from related buy `Transaction`s. `Position::transactions()` self-joins on `isin` **and** `user_id` — any new relation touching transactions must preserve that scoping or it will leak across users.
- **Transaction** — buy/sell event (`isin`, `quantity`, `price`, `type`, `date`).
- **User** — standard Laravel user with Sanctum tokens.

Writes go through Action classes in `app/Actions/<Resource>/` (e.g. `StoreTransactionAction`), which keep cross-entity side effects in one place — creating a transaction also upserts the matching `Position` quantity. Follow that pattern when adding write paths; controllers stay thin and extend `ApiController` for `successResponse` / `errorResponse`.

### Price updates

`FinancialScraperService` calls the Python scraper via `config('services.scraper.url')` (`SCRAPER_URL` env, defaults to `http://localhost:5001`; in compose it's the `scraper` hostname). The `app:get-positions-price` Artisan command iterates allocations with a ticker, fetches the price, and updates the matching `Position` by ISIN. It's scheduled every 6 hours in `routes/console.php`; a dedicated `pea_scheduler` container (same image as `pea_php`) runs `php artisan schedule:work` in dev and prod — no cron involved.

### Auth flow

Cookie-based Sanctum. Frontend hits `/sanctum/csrf-cookie` then `/api/login`; session cookie authenticates subsequent API calls. `/api/login` and `/api/logout` use the `web` middleware group; everything else uses `auth:sanctum`. The Nuxt side is configured in `nuxt.config.ts` under `runtimeConfig.public.sanctum` — `baseUrl` defaults to the prod API and is overridden at build time via the `NUXT_PUBLIC_SANCTUM_BASE_URL` Docker build-arg.

### Frontend shape

Pages call composables (`composables/use*.ts`) which wrap `useApiFetch` (thin layer on `useSanctumFetch` that toasts errors). Types live in `types/`. There are no stores beyond an empty `stores/index.ts` and no middleware yet — auth redirects are handled by `nuxt-auth-sanctum`'s `globalMiddleware`.

## Commands

**Dev runs in Docker**, not on the host. Don't install PHP, Node, or Python locally. Containers: `pea_php` (php-fpm), `pea_nginx` (web, routed by Traefik), `pea_scheduler`, `pea_nuxt`, `pea_scraper` on the external `antibes` network. The API is reachable on `http://localhost:8080` (nginx).

Compose is split into **explicit overlays** (no auto-merge anymore): `docker-compose.yml` (base) + `docker-compose.dev.yml` (dev) or `docker-compose.prod.yml` (prod). A `Makefile` wraps the `-f` flags — **use it**:

```bash
make init      # FIRST run on a fresh clone: starts dev + composer install + sqlite + migrate (into the bind-mount)
make up        # start/rebuild the dev stack
make test      # Pest with coverage
make logs      # tail php/nginx/scheduler
# raw equivalent: docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

In dev, `pea_php`/`pea_scheduler` use the `dev` image target (**toolchain only**: PHP + Xdebug + Composer) and bind-mount the whole app (`./backend/app → /var/www/html`) — code and `vendor` live on the host, hence `make init` once. In prod the image bakes app + `vendor` (no mount).

### Backend (`pea_php`)

```bash
DC="docker compose -f docker-compose.yml -f docker-compose.dev.yml"
$DC exec php ./vendor/bin/pest tests/Feature/Http   # single directory
$DC exec php ./vendor/bin/pest --filter=summary     # single test
$DC exec php ./vendor/bin/pint                       # code style
$DC exec php php artisan migrate
$DC exec php php artisan app:get-positions-price     # manual price refresh
```

Testing uses `RefreshDatabase` against a separate SQLite file (`database/database-testing.sqlite`) configured in `phpunit.xml`.

### Frontend (`pea_nuxt`)

```bash
$DC exec nuxt npm run dev      # Nuxt dev server
$DC exec nuxt npm run build    # production build to .output/
```

No tests or linter wired up on the frontend.

### Scraper (`pea_scraper`)

```bash
curl "http://localhost:5001/price?ticker=CW8.PA"   # from host, smoke-test (dev exposes 5001)
```

### Prod

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml pull   # pulls from ghcr.io
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d   # or: make prod-pull && make prod-up
```

## CI/CD

- `.github/workflows/tests-laravel.yml` runs Pest on PRs to `main` (PHP 8.2, SQLite, coverage via xdebug).
- `.github/workflows/deploy-on-main.yml` uses `dorny/paths-filter` to detect which of `frontend/`, `backend/`, `scraper/` changed, builds only the changed images, pushes to `ghcr.io/jeremieflahaut/pea/*`. The VPS (`/home/debian/projects/pea`) is **not a git checkout**: the workflow `scp`s `docker-compose.yml` + `docker-compose.prod.yml` to it (so it needs no git auth — images come from GHCR), then SSHes in to `docker compose -f docker-compose.yml -f docker-compose.prod.yml pull` + `up -d --remove-orphans`. The SSH steps use `script_stop: true` so a failing command fails the action (don't drop it — without it errors are swallowed and the deploy lies green). A `backend/` change builds **two** images — `php` (`--target prod`) and `nginx` — and recreates `php`, `nginx`, `scheduler`, then runs `artisan migrate --force` + `package:discover` + `config:cache`. Migrations must be safe to apply unattended.
- Prod env lives **only on the VPS** at `env/laravel.env` (gitignored): must set `LOG_CHANNEL=stderr` and `DB_CONNECTION=mysql` (host `mysql_mysql`). `config:cache` bakes it on each deploy.
- Logs go to **stdout/stderr** (set `LOG_CHANNEL=stderr` in the prod env); read them with `docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f php nginx scheduler`. No file logging / logrotate.
- **Prod DB caveats** — the prod MySQL is the **shared `docker-mysql` project** (separate repo, container `mysql_mysql`, image `mysql:5` **EOL**, on the external `antibes` network, data bind-mounted via `DB_FOLDER`, phpMyAdmin at `pma.dev-fullstack.net`). It is **not** part of pea's IaC (`docker-compose.prod.yml`) and is **shared across multiple workspace projects** → wide blast radius. Backups are **manual-only**. Dev/CI run SQLite, prod runs MySQL 5 → a real parity gap. When reasoning about pea prod data/migrations/backups, target `docker-mysql`, not pea.
