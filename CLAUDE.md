# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack & runtime

Plain PHP 8.1+ / MySQL 8 (or MariaDB 10.5+) on Apache with `mod_rewrite`. **No build step, no package manager, no test suite, no lint config.** Edits go live as soon as the file is saved — there is nothing to compile or bundle.

Local environment is Laragon (`C:\laragon\www\restaurant`). Default URL: `http://localhost/restaurant/` (or whatever Laragon vhost maps to this directory).

## Database setup

```bash
mysql -u root -p < install/schema_final.sql            # fresh install
mysql -u root -p restaurant_db < install/patch_v1.sql  # only if migrating from legacy database.sql
```

DB credentials are hardcoded in `config/database.php` (constants `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`). There is no `.env` — change them in that file.

`restaurant_db.sql` at the repo root is a full dump (data + schema). `install/schema_final.sql` is the canonical schema for new installs; prefer it over the dump.

## Architecture

### Request lifecycle

Every API endpoint and module page follows the same boilerplate:

```php
require_once __DIR__ . '/../config/database.php';   // boots DB::init() at file load
require_once __DIR__ . '/../includes/functions.php'; // starts session, exposes auth helpers
requireLogin(['administrador', 'mozo', ...]);        // 401/403 + redirect for HTML, JSON for API/AJAX
```

`requireLogin()` auto-detects API/AJAX requests (path contains `/api/`, `X-Requested-With: XMLHttpRequest`, or JSON `Accept`/`Content-Type`) and emits JSON instead of redirecting. Keep this in mind when adding endpoints — never call `header('Location: ...')` from `/api/*`.

### DB layer (`config/database.php`)

Static singleton `DB` class wrapping PDO with `ERRMODE_EXCEPTION` and `EMULATE_PREPARES = false`. Use `DB::query($sql, $params)`, `DB::fetchAll`, `DB::fetchOne`, `DB::lastInsertId`. **Always** pass user input as bound parameters — don't string-concatenate into SQL.

Connection failures `die()` with a JSON error and HTTP 500 — there is no graceful fallback.

### API conventions (`api/*.php`)

Each file is one resource. The dispatch shape is consistent across the directory and should be preserved when adding new endpoints:

- `GET` reads query string params (`$_GET['id']`, `$_GET['estado']`, etc.) and branches on which is set.
- `POST` reads `json_decode(file_get_contents('php://input'), true)` and switches on `$data['action']` (e.g. `agregar_items`, `cobrar`, `cambiar_estado_item`). The action verb pattern is the routing layer — there is no router.
- Responses go through `jsonResponse($data, $code)` which sets headers, encodes with `JSON_UNESCAPED_UNICODE`, and `exit`s.
- Anything else returns `405 Método no permitido`.

Role gating is per-action: callers re-invoke `requireLogin([...])` inside a branch when an action needs stricter roles than the file-level check (see `api/ordenes.php` `cambiar_mesa`).

### Auth & sessions

- `usuarios.password` stores `password_hash(..., PASSWORD_DEFAULT)` (bcrypt). Verify with `password_verify`.
- `login()` populates `$_SESSION['user_id'|'user_nombre'|'user_rol'|'user_usuario'|'login_time']`. `currentUser()` reads from there — there's no DB roundtrip per request.
- Roles enum: `administrador, cajero, mozo, cocina, bar, almacen, compras, supervisor`. After login, `index.php` redirects by role to the matching module under `/modules/`.
- `SESSION_TIMEOUT` is defined (3600s) but **not enforced anywhere** — the constant exists, the check doesn't. If you need timeout, add it to `requireLogin()`.

### Modules (`modules/<role>/index.php`)

Single self-contained PHP files, each ~thousands of lines combining HTML/CSS/JS inline. They authenticate at the top, render the shell, and the embedded JS polls `/api/*.php` on a 5–10s interval. There is no SPA framework — vanilla JS + `fetch`.

When adding a new screen, follow the existing module's pattern (sidebar nav + content area + inline `<style>` using the `--accent`/`--surface` design tokens) rather than introducing a JS framework or external CSS file.

### Domain model highlights

- **Orders are two-tiered**: `ordenes` (header) + `orden_detalle` (line items). Each line item has its own state machine (`pendiente → preparando → listo → entregado`), independent of the order's state (`abierta → en_proceso → lista → pagada`). The KDS in `api/cocina.php` operates on line items, not orders.
- **Inventory is auto-deducted via DB trigger** `trg_descuento_inventario` that fires when an `orden_detalle` row flips to `listo`. Look in `install/schema_final.sql` (or `patch_v1.sql`) before changing item-state transitions — bypassing the trigger silently breaks stock accounting.
- **Payments split across methods**: `pagos` (one row per cobro) + `pago_metodos` (one row per tender — efectivo/yape/plin/tarjeta…). A single payment can mix methods.
- **IGV** = 18% (Peru), defined as both `IGV` and `RESTAURANT_IGV` constants in `config/database.php`. Currency `S/`. Timezone `America/Lima` (set in PHP and on the PDO connection via `SET time_zone`).
- **`numero` fields** on orders/pagos/compras/delivery are human-readable codes generated by `generateNumero($prefix)` (`PREFIX-YYYYMMDD-NNNN`).

### Routing

Apache rewrites short URLs (see `bkhtaccess` for the rules: `/mozos`, `/cocina`, `/caja`, `/admin` → `/modules/<x>/index.php`). The active `.htaccess` at the repo root may be missing — `bkhtaccess` is the reference. `BASE_URL` is auto-derived from `DOCUMENT_ROOT` in `config/database.php`; only override if the auto-detect fails.

## Things to know before editing

- `diagnostico.php` at the root prints PHP version, extensions, DB connectivity, and BASE_URL. The file itself says "ELIMINAR después de usarlo" — treat it as a debug tool, not part of the app.
- Demo seed users (`admin`, `mozo1`, `cocina1`, `cajero1`) all have password `password`. These are loaded by `schema_final.sql` for development; don't ship them.
- The codebase mixes Spanish (domain language: `mesa`, `orden`, `cocina`, `cajero`) and English (PHP/SQL keywords). Match the convention of the file you're editing — new domain code in Spanish, infrastructure in English.
- No CSRF tokens are issued or checked on POST endpoints. Same-origin session cookies are the only protection. If you add a state-changing endpoint that could be triggered cross-origin, this is the gap to be aware of.
