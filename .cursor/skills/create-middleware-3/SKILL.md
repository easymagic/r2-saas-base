---
name: create-middleware-3
description: >-
  Scaffolds an HTTP middleware under api/Presentation/Http/Middlewares that
  implements MiddlewareServiceInterface, injects existing *ServiceInterface
  types via constructor DI, and optionally wires routes in web.php. Use when
  the user invokes /create-middleware-3 or asks to create a middleware by name
  and intent.
disable-model-invocation: true
---

# Create Middleware (3)

Creates a new middleware in `api/Presentation/Http/Middlewares/` matching the
existing GlobalApi / WalletFeedback style. The user only supplies a **name** and
**intent**; the agent discovers and injects the right service interfaces.

## When to use

- User invokes `/create-middleware-3`
- User asks to create/add an HTTP middleware by name + intent

## Inputs (ask only if missing)

1. **Middleware name** — PascalCase, without `Middleware` suffix is fine (agent appends it). Examples: `KycPending`, `WalletFeedback`, `GlobalApiAuthAdmin`
2. **Intent** — what `handle()` should do (auth gate, side-effect before controller, role check, etc.)

Optional (ask only if needed to finish):

- Whether to **wire routes** in `api/Presentation/Http/Routes/web.php` (default: **no** unless user points at routes)
- Which route group / paths to wrap

## Canonical location

```
api/Presentation/Http/Middlewares/{Name}Middleware.php
```

- Namespace: `Presentation\Http\Middlewares`
- Implements: `R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface`
- Entry point: `public function handle()`
- **Do not** register middlewares in `api/Kernel/boot.php` — the container resolves the class when routes reference it

Reference implementations (read before generating if unsure):

| File | Pattern |
|------|---------|
| `GlobalApiMiddleware` | Validate API `x-token` via `ApiCredentialServiceInterface` + `Request` |
| `GlobalApiAuthMiddleware` | API token + user `x-user-token` |
| `GlobalApiAuthAdminMiddleware` | Auth + admin role gate (`getAuthUser()`, throw on failure) |
| `WalletFeedbackMiddleware` | Side-effect middleware: multiple domain service interfaces, no `Request` |

## Workflow

Copy and track:

```
Middleware scaffold:
- [ ] Confirm name + intent
- [ ] Discover ServiceInterfaces (and Request if needed)
- [ ] Create api/Presentation/Http/Middlewares/{Name}Middleware.php
- [ ] Wire routes in web.php only if user requested
```

### Step 1 — Resolve class name

- Final class: `{Name}Middleware` (if user already ended with `Middleware`, do not double-suffix)
- File: `api/Presentation/Http/Middlewares/{Name}Middleware.php`

### Step 2 — Discover dependencies from intent

**Prefer existing `*ServiceInterface` types** already mapped in `api/Kernel/boot.php`. Never invent a new service/module for a middleware unless the user asks.

How to pick:

1. Grep / list `**/*ServiceInterface.php` under `api/` (and framework payment interfaces if already used by peers).
2. Read interface method signatures that match the intent.
3. Inject **interfaces only** (constructor DI), assign to typed private properties — same style as existing middlewares.
4. Inject `R2Packages\Framework\Infrastructure\Framework\Container\Request` when the middleware must read headers/query/body (`$this->request->get('...')`). Skip `Request` when auth context already comes from `ApiCredentialServiceInterface::getAuthUser()` (see `WalletFeedbackMiddleware`).

Common interfaces already used by middlewares:

| Interface | Typical use |
|-----------|-------------|
| `Presentation\ApiCredential\ApiCredentialServiceInterface` | `validateToken`, `validateUserToken`, `getAuthUser` |
| Domain `{Module}\Business\{Module}ServiceInterface` | Domain actions in `handle()` |
| `Log\Business\LogServiceInterface` | `createLog(...)` side effects |
| Framework `PaymentServiceInterface` | Payment verify/status (only if intent needs it and it is already used this way) |

Auth / request conventions from peers:

- API credential header: `x-token`
- User session header: `x-user-token`
- Admin check pattern: `strpos($user->role, 'admin') === false` → throw `\Exception` with unauthorized message

### Step 3 — Generate the class

Match peer style:

- PHP typed properties + typed constructor params (same as existing middleware files; do **not** invent a separate PHP 5.6 middleware dialect)
- `implements MiddlewareServiceInterface`
- All logic in `handle()`; throw `\Exception` (or existing project exceptions) to block the request
- No `boot.php` `map()` for the middleware class
- No new interfaces for the middleware itself

Skeleton: [templates.md](templates.md)

### Step 4 — Route wiring (only if requested)

In `api/Presentation/Http/Routes/web.php`:

1. `use Presentation\Http\Middlewares\{Name}Middleware;`
2. Wrap the target routes:

```php
$route->middleware([
    {Name}Middleware::class
], function (RouteServiceInterface $route) {
    // existing or new routes
});
```

Nest under the correct parent group (`GlobalApiMiddleware` / `GlobalApiAuthMiddleware` / admin) based on intent. Do not restructure unrelated routes.

## Intent → shape hints

| Intent type | Shape |
|-------------|--------|
| Auth / token gate | `Request` + `ApiCredentialServiceInterface`; validate tokens in `handle()` |
| Role / permission gate | Auth services + `getAuthUser()`; throw if not allowed |
| Pre-request side effect | Domain `*ServiceInterface`(s); optional logging; usually no early return needed unless failing closed |
| Compose existing gates | Prefer nesting in `web.php` over duplicating GlobalApi* logic |

## Out of scope unless requested

- New domain modules / services / repositories
- `boot.php` bindings for the middleware
- Changing `MiddlewareServiceInterface` or the router
- Postman / docs updates

## Checklist before finishing

- [ ] Class name ends with `Middleware` once
- [ ] File under `api/Presentation/Http/Middlewares/`
- [ ] Implements `MiddlewareServiceInterface` with `handle()`
- [ ] Dependencies are existing `*ServiceInterface` (+ `Request` only when needed)
- [ ] No `boot.php` registration
- [ ] Routes updated only if user asked
