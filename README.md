# Dental SaaS

Persian-first, RTL-first multi-tenant dental clinic SaaS. Modular monolith.

```
backend/         Laravel 13 API (app/Domain/*, app/Application/*, app/Infrastructure/*, app/Shared/*)
frontend/        Next.js 16 (App Router, TypeScript, RTL, Persian/Jalali)
docs/            Architecture, migration (legacy feature/business-rule extraction), implementation roadmap
infrastructure/  Docker, nginx, deployment configs
scripts/         One-off operational scripts
legacy/          Read-only reference: the two WordPress plugins this SaaS replaces (see docs/migration)
```

- Target architecture & domain boundaries: `docs/architecture/target-state.md`
- Legacy system analysis (what to migrate as *business requirements*, not code): `docs/architecture/current-state.md`, `docs/migration/feature-inventory.md`, `docs/migration/business-rules.md`
- Phase-by-phase build plan: `docs/implementation/roadmap.md`

## Local development (without Docker)

Backend (PHP 8.4 / Composer required):

```sh
cd backend
composer install
php artisan serve --port=8100
```

Frontend (Node 20+):

```sh
cd frontend
npm install
npm run dev   # http://localhost:3100
```

Backend defaults to SQLite for local dev with no extra setup. Copy `backend/.env.example` → `backend/.env` and `frontend/.env.example` → `frontend/.env.local` to customize.

## Local development (Docker)

```sh
docker compose up
```

Runs backend (`:8100`), frontend (`:3100`), PostgreSQL (`:5442`), and Redis (`:6389`). Not yet verified on this machine (Docker isn't installed here) — verify on first use.

## Quality gates

Backend (`backend/`): `php artisan test`, `vendor/bin/pint --test`, `composer stan` (phpstan/Larastan needs `--memory-limit=512M`, wired into the composer script).

Frontend (`frontend/`): `npm run typecheck`, `npm run lint`, `npm run test`, `npm run build`.

## Ports

Chosen to avoid clashing with other local projects on this machine: API `8100`, web `3100`, Postgres `5442`, Redis `6389` (all non-default to leave 8000/3000/5432/6379 free for other projects).
