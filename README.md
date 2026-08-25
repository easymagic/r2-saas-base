# r2-saas-base

Composer project with separate **api** and **frontend** apps, sharing one root `vendor/` autoloader.

## Requirements

- PHP >= 5.6
- Composer

## Setup

```bash
composer install
```

## Run locally

```bash
# API  → http://localhost:8080
composer serve-api

# Frontend → http://localhost:8000
composer serve-frontend
```

## Layout

```text
.
├── api/
│   ├── public/          # API document root
│   └── src/             # Api\ namespace
├── frontend/
│   ├── public/          # Frontend document root
│   ├── src/             # Frontend\ namespace
│   └── views/           # PHP views
├── composer.json
└── vendor/              # Shared Composer dependencies
```

Namespaces:

- `Api\` → `api/src/`
- `Frontend\` → `frontend/src/`
