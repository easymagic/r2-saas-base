# r2-saas-base

Classic **server-rendered PHP UI** on the R2 framework. All features are exposed as web pages and form POSTs — no separate React or JSON API layer.

## Requirements

- PHP >= 7.4
- Composer
- MySQL (configured in `api/.env`)

## Setup

```bash
composer install
cp api/.env.example api/.env
# edit api/.env (DB_*, mail, Paystack, …)
```

## Run locally

```bash
composer serve
# → http://localhost:8080/login
# Uses api/router.php so module folders (Cart/, Wallet/, …) do not shadow routes.
```

CLI:

```bash
php api/scafold.php charge-bnpl
```

## Web routes (high level)

| Area | Paths |
|------|--------|
| Auth | `/`, `/login`, `/register`, `/forgot-password`, `/reset-password` |
| Customer | `/dashboard`, `/profile`, `/orders`, `/wallet`, `/notifications` |
| Admin | `/admin`, `/admin/users`, `/admin/wallet/topups`, `/admin/batches`, `/admin/platform-config`, `/admin/logs`, … |
| Migrations | `GET /migrations` (unauthenticated — runs all module migrations) |

Auth uses PHP sessions (`WebSession`) and calls domain usecases in-process.

## Layout

```text
.
├── api/
│   ├── index.php              # Entry point
│   ├── views/                 # PHP templates
│   ├── Presentation/Http/
│   │   ├── Controllers/Web/   # Web controllers
│   │   ├── Routes/web-routes.php
│   │   └── Middlewares/       # WebAuth, WebAdmin, WalletFeedback, …
│   └── User/, Wallet/, SnappyOrder/, …
├── index.php                  # Optional root → api/index.php
└── composer.json
```

Domain JSON `Presentation/*Controller.php` files and `/v2/*` API routes have been removed in favor of web controllers.
