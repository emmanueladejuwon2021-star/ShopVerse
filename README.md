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

Vercel notes:
- Serverless Functions live in `/api`: `vercel.json` declares
  `functions: { "api/*.php": { "runtime": "vercel-php@0.9.0", "includeFiles": "{includes,views,data}/**" } }`.
  `api/index.php` serves as the front controller for all dynamic routes.
- Static assets (CSS, images) are served directly from `public/assets/` via Vercel CDN routing rules.
- Executable `.php` files are kept out of `public/` so Vercel never serves PHP code as static file downloads.
- Sessions are stored in `/tmp` and the SQLite fallback auto-probes writable
  directories — required because Vercel's deployed filesystem is read-only.
- Always set `TURSO_DB_URL` + `TURSO_AUTH_TOKEN` in Project Settings → Environment Variables
  for production persistence, then run `php scripts/init-db.php` once to create
  the Turso schema.

- Local development:
  ```bash
  php -S localhost:8000 router.php
  ```

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
api/             # serverless entry point: index.php (front controller)
public/          # static web assets: assets/app.css, assets/products/
includes/        # functions, data catalog, auth, cart, db abstraction, http client
views/           # layout.php, pages/, partials/, 404.php
data/            # products.json (static catalog)
storage/         # runtime data (gitignored): shopverse.db / *.json fallback
scripts/         # init-db.php (Turso bootstrap)
router.php       # local dev router
```

### Paystack environment variables (Project Settings → Environment Variables)

| Variable | Required | Notes |
|---|---|---|
| `PAYSTACK_SECRET_KEY` | yes | `sk_test_…` or `sk_live_…`. Checkout gracefully falls back to Cash-on-Delivery until set. |
| `PAYSTACK_PUBLIC_KEY` | optional | reserved for future inline JS (Paystack Popout) use; the hosted flow only needs the secret key. |
| `PAYSTACK_WEBHOOK_SECRET` | recommended | HMAC-SHA512 verification of `POST /payments/webhook`; defaults to the secret key if unset. |
| `PAYSTACK_CURRENCY` | optional | default `NGN`. If you change it, update the `$100` coupon threshold in `includes/cart.php`. |
| `APP_BASE_URL` | on Vercel | canonical base URL used to build the Paystack `callback_url` (e.g. `https://your-app.vercel.app`). Falls back to request headers when unset. |

After deploying with a live key, register the webhook URL
`https://<your-domain>/payments/webhook` in the Paystack dashboard and set its
signing secret to the same value as `PAYSTACK_WEBHOOK_SECRET`.
