# ShopVerse — PHP Edition

A server-rendered e-commerce app built on a **pure PHP stack** (no Node/React toolchain).
Converted from the original React 18 + TypeScript + Vite SPA; all routing, state
(contexts), and persistence now live on the server.

## Stack

| Layer        | Implementation |
|--------------|----------------|
| Language     | PHP 8.1+ (no framework, zero Composer deps) |
| Routing      | Front controller `public/index.php` + `.htaccess` / `vercel.json` rewrite |
| Views        | PHP templates in `views/` (layout, pages, partials) |
| State        | PHP sessions (auth, cart, flash messages, theme-driven UI flags) |
| Database     | Turso (libsql HTTP API) in production → local SQLite (PDO) for dev → JSON-file fallback if neither is available |
| Styling      | Pre-built Tailwind CSS at `public/assets/app.css` (dark mode via class strategy) |
| Auth         | Real credential validation with `password_hash` (bcrypt) + session-fixation protection |

## Run locally

```bash
php -S localhost:8000 -t public public/router.php
# open http://localhost:8000
```

The SQLite schema auto-creates itself on first write (`storage/shopverse.db`).

## Deploy (Vercel or any PHP host)

Vercel notes (fixed after the "unexpected internal error" deploy failure):
- Serverless Functions must live in `/api`: `vercel.json` declares
  `functions: { "api/index.php": ... }` and a catch-all route rewriting every
  request to `/api/index.php`. That function is a thin wrapper that requires
  `public/index.php` (the front controller), so routing logic stays in one file.
  (The previous pattern `public/index.php` matched no function inside `api/`,
  which is what aborted the build.)
- `vercel.json` uses the community **`vercel-php@0.8.0`** runtime (the previously
  referenced `vercel/php@8.3` / `@vercel/php` do not exist on Vercel, which made
  every build fail). No legacy `builds` key is used.
- Sessions are stored in `/tmp` and the SQLite fallback auto-probes writable
  directories — required because Vercel's deployed filesystem is read-only.
- A committed dev `storage/shopverse.db` was removed from git; always set
  `TURSO_DB_URL` + `TURSO_AUTH_TOKEN` in Project Settings → Environment Variables
  for production persistence, then run `php scripts/init-db.php` once to create
  the Turso schema.

- Point the web root at `public/`.
- Set environment variables for cloud storage:
  - `TURSO_DB_URL` (e.g. `libsql://your-db.turso.io`)
  - `TURSO_AUTH_TOKEN`
- Bootstrap the schema once:
  ```bash
  php scripts/init-db.php
  ```
- Without those vars it falls back to local SQLite (persistent hosts) or JSON
  files in `storage/` (must be writable); writes degrade gracefully with a
  user-safe error message instead of a crash.

## Features

- Home, product listing (search / category / price / rating filters + sorting), product detail
- Session-based cart with slide-over sidebar, quantity controls, free shipping ≥ $50, 8% tax
- Register / login / logout (hashed passwords, per-user order history)
- Checkout with server-side validation and order confirmation
- Account settings: profile, saved addresses, payment cards (last-4 only), notification prefs — all persisted
- Newsletter subscription persisted to the database
- Dark / light theme with system-preference fallback (no FOUC)
- 404 page, protected routes, open-redirect-safe `?redirect=` handling

## Structure

```
public/          # web root: index.php (front controller), router.php, assets/
includes/        # functions, data catalog, auth, cart, db abstraction, http client
views/           # layout.php, pages/, partials/, 404.php
data/            # products.json (static catalog)
storage/         # runtime data (gitignored): shopverse.db / *.json fallback
scripts/         # init-db.php (Turso bootstrap)
```
