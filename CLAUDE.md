# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Communication rules

- **Always respond to the user in Spanish**, regardless of the language the request was written in.
- **Always propose changes before making them, especially anything touching the database** (schema, migrations, seeders, queries, model relationships/`onDelete` behavior). Describe the proposed change and its consequences, then wait for the user to decide — don't implement first and explain after.
- **Whenever there's any doubt, ask the user first instead of guessing.** If a request is ambiguous, underspecified, or could reasonably be implemented in more than one way (naming, scope, which field/behavior to use, UX details, etc.), stop and ask a clarifying question before writing code. Don't silently pick an interpretation and proceed.

## Project Overview

Client Finder is a Laravel 12 + Inertia + React application for looking up clients and their insurance policies ("pólizas") by name, DNI/NIE/CIF, license plate ("matrícula"), or policy number. Source data comes from Excel and is imported into SQLite.

Background and rationale docs live in `02-DOCS/wiki/proyecto/` — they are more current than this file for project history, so read them before making architectural changes:
- `02-DOCS/wiki/proyecto/01-client-finder-documentacion.md`
- `02-DOCS/wiki/proyecto/02-base-de-datos.md`
- `02-DOCS/wiki/proyecto/03-decisiones.md`
- `02-DOCS/wiki/proyecto/04-arquitectura.md`
- `02-DOCS/wiki/proyecto/05-instalacion.md`
- `02-DOCS/wiki/proyecto/pendientes.md` — open items, maintained by Claude Code (format: `02-DOCS/wiki/harness/formato-pendientes.md`, shared by all rsc projects)

## Knowledge map

| What | Where |
|------|-------|
| Project constitution (SDD non-negotiables) | `02-DOCS/wiki/sdd/constitution.md` |
| User profile (register, language) | `02-DOCS/wiki/harness/user-profile.md` |
| Full wiki index | `02-DOCS/wiki/index.md` |

## Commands

### Backend (PHP 8.2 / Laravel 12, run from repo root)
```
composer install
php artisan migrate          # apply migrations (SQLite by default, DB_CONNECTION=sqlite)
php artisan migrate:fresh    # drop and re-run all migrations — DESTRUCTIVE, see Guardrails below
php artisan serve            # http://localhost:8000
php artisan route:list       # inspect registered routes
vendor/bin/pest              # run the full test suite (Pest)
vendor/bin/pest tests/Feature/ClienteSearchTest.php   # single file
vendor/bin/pest --filter="search clients"             # single test by name
./vendor/bin/pint            # PHP code style (Laravel Pint)
```

### Frontend (Node, run from repo root)
```
npm install
npm run dev                  # Vite dev server, http://localhost:5173
npm run build                # production build
npm run build:ssr            # production build, SSR bundle
npm run lint                 # eslint . --fix
npm run format                # prettier --write resources/
npm run format:check          # prettier --check resources/
```

`composer run dev` starts the PHP server, queue listener, and Vite dev server concurrently (see `composer.json`). The app is served from `http://localhost:8000`; Vite (`:5173`) only serves assets to it.

## Architecture

**Stack**: Laravel 12 backend, Inertia.js bridging to React (no client-side router — Inertia handles navigation), Tailwind CSS, SQLite. Started from the Laravel React starter kit, so auth/settings pages and `resources/js/components/ui/*` (shadcn/Radix) still exist as `.tsx`. Per `02-DOCS/wiki/proyecto/03-decisiones.md`, **new application code uses `.js`/`.jsx`, not `.ts`/`.tsx`** — `react-router` was deliberately removed since Inertia owns routing.

**Domain model** (see `02-DOCS/wiki/proyecto/02-base-de-datos.md` / `02-DOCS/wiki/proyecto/03-decisiones.md` for the reasoning):
- Tables use a `tbl_` prefix and Spanish names: `tbl_clientes`, `tbl_polizas`, `tbl_companias`, `tbl_ramos`, `tbl_tipos_vencimiento`, `tbl_estados`.
- `Cliente` 1:N `Poliza` (`cliente_id`, cascade on delete). A `Poliza` also `belongsTo` `Compania` (restrict on delete), `Ramo` (restrict on delete), `TipoVencimiento` (nullable, null on delete), and `Estado` (nullable, null on delete). There is also `users` (Laravel table, no prefix) with `rol` (`admin`/`empleado`), `activo` and `tema`.
- Every table carries `activo`, `created_at`/`updated_at`, and `deleted_at` (all models use `SoftDeletes`).
- Model names are singular (`Cliente`, `Poliza`, `Compania`, `Ramo`), controllers end in `Controller`, and domain code/comments are written in Spanish.

**Frontend structure**: Inertia entry point is `resources/js/main.jsx`. Pages live in `resources/js/pages/*.jsx` and are resolved by name via `Inertia::render('PageName')` on the PHP side. The search UI (`BuscadorPage.jsx`, `Buscador.jsx`, `ResultadoCliente.jsx`, `TarjetaPoliza.jsx`) is plain JSX; shadcn-style UI primitives under `resources/js/components/ui/` are TSX and configured via `components.json` (aliases `@/components`, `@/lib`, `@/hooks`, etc., matching `tsconfig.json` paths).

## Two Search Implementations (both intentional, different purposes)

There are **two separate search implementations** — this is by design, not an inconsistency to merge:

1. **`SearchController@search`** (`app/Http/Controllers/SearchController.php`) is the JSON search API, wired at `GET /api/search` (login required: `web` + `auth` middleware, rate-limited to 60/min by `throttle:busqueda`) (registered via the `api:` entry in `bootstrap/app.php`, pointing at `routes/api.php`). It supports `type=cliente` (nombre/email/teléfono/identificación) and `type=poliza` (numero_poliza/matrícula). It's the intended backend for the `BuscadorPage.jsx` buscador — but `BuscadorPage.jsx` currently searches an in-memory demo array (`resources/js/data/clientesDemo.js`) instead of calling it, until real data is imported and someone wires up the `fetch('/api/search?...')` call.
2. **`DashboardController@index`** (`app/Http/Controllers/DashboardController.php`), wired at the `auth`-protected `GET /dashboard` in `routes/web.php`, does client/policy search server-side and returns it as Inertia props to the `dashboard` page. `tests/Feature/ClienteSearchTest.php` exercises this path, not `SearchController`.

Don't consolidate these into one without the user explicitly asking for that — see Scope guardrail below.

## Guardrails for Claude Code

This app handles real personal data (names, DNI/NIE/CIF, policy numbers). These rules exist to prevent accidental data loss, scope creep, and silent architectural drift.

### Destructive operations — always ask first
- Never run `migrate:fresh`, `migrate:rollback` beyond the last batch, or any command that drops/truncates tables, unless the user has explicitly pointed you at a disposable local/dev database.
- Never hard-delete rows (`forceDelete()`, raw `DELETE`/`TRUNCATE`) — the app uses `SoftDeletes` everywhere on purpose.
- Never delete or rewrite a migration that has already been applied; add a new migration for schema changes instead.
- Never run destructive git operations (`push --force`, `reset --hard`, rebasing shared branches) without explicit confirmation.

### Schema & data model
- Keep the `tbl_` prefix and Spanish table/column names for anything new.
- Respect existing `onDelete` behavior (`cascade` for `Cliente → Poliza`, `restrict` for `Compania`/`Ramo`, `set null` for `TipoVencimiento`). Flag the consequence before changing it — e.g., switching `Compania` from `restrict` to `cascade` would let deleting a company silently wipe out its policies.
- Don't add a table or column without `activo`, timestamps, and `deleted_at` unless there's a stated reason not to.

### Search logic
- `SearchController` (JSON API, `/api/search`) and `DashboardController` (authenticated dashboard search) are both live and serve different pages — see "Two Search Implementations" above. Don't change one assuming it'll fix the other.
- When real data replaces `resources/js/data/clientesDemo.js` in `BuscadorPage.jsx`, that's when it should start calling `/api/search` — flag it if asked to do that swap so scope stays clear.

### Frontend conventions
- New application code: `.js`/`.jsx` only. Don't add new `.ts`/`.tsx` files outside the existing `resources/js/components/ui/` primitives.
- Don't reintroduce `react-router` or any client-side router — Inertia owns navigation.
- Use the existing `components.json` aliases (`@/components`, `@/lib`, `@/hooks`) rather than relative imports for shared code.

### Dependencies & tooling
- Don't add new Composer or npm packages without stating why and getting confirmation first — check whether `composer.json`/`package.json` already covers the need.
- rsc harness: refresh it with `npx @ericrisco/rsc@<catalogVersion> sync`, never `rsc onboard`. rsc 3.3.1 scans `vendor/` and mistakes this project for Python; CI fails if `python` reappears in `.rsc.json` skills/agents (fix: `rsc uninstall <id>`).
- Run `vendor/bin/pest` after backend changes and `npm run lint` after frontend changes before considering a change done. Don't skip or comment out failing tests to "make it green."

### Data privacy
- DNI/NIE/CIF, full names, and policy numbers are personal data under Spanish/EU law (RGPD/LOPD). Never print them to logs, commit them in seed/example data, or paste them into commit messages or code comments.
- When writing tests or sample data, use clearly fake values (e.g. `00000000T`), never real-looking or copied production data.

### Scope
- Stick to the task asked. Don't refactor both search implementations into one "while you're in there" unless the user explicitly asks for that consolidation — it's a bigger architectural decision.
- Domain code, comments, and commit messages describing domain/business logic stay in Spanish, consistent with the rest of the codebase; tooling/config comments can stay in English.
