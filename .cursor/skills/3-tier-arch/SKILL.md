---
name: 3-tier-arch
description: >-
  Scaffolds a modular 3-tier PHP module (Data, Business, Presentation) with
  repository interfaces, repositories, service interfaces, services, migration
  interfaces, migrations, controllers, and a module-specific route file under
  api/Presentation/Http/Routes, following api/User and api/Cart. Domain methods
  use api/Shared/Contracts.php for business rules. Use when the user invokes
  /3-tier-arch or asks to create a new module, repository, service, migration,
  controller, or routes in the modular 3-tier layout under api/.
disable-model-invocation: true
---

# 3-Tier Architecture Module Scaffold

Creates a new module under `api/{Module}/` with three folders — **Data**, **Business**, and **Presentation** — in modular form: repository interfaces, repository, service interfaces, services, migration interfaces, migration, and controllers. Also creates a **module-specific route file** under `api/Presentation/Http/Routes/`. Mirror `api/User` / `api/Cart`.

## When to use

- User invokes `/3-tier-arch`
- User asks to scaffold a new domain module (repo + service + migration + controller + routes)

## Before generating

Collect (ask if missing):

1. **Module name** (PascalCase singular preferred): e.g. `User`, `Wallet`, `Order`
2. **Table name** (snake_plural): e.g. `users`, `wallets`
3. **Fields** — only from an explicit schema / `@schema.sql` / user list. Do not invent columns.
4. Optional domain methods for the service / controller (beyond `migrate()`)
5. Optional auth middleware needs for routes (public vs `GlobalApiAuthMiddleware` vs admin)

## Layout (canonical)

```
api/{Module}/
  Data/
    {Module}Entity.php
    {Module}RepositoryInterface.php
    {Module}Repository.php
    {Module}MigrationRepositoryInterface.php
    {Module}MigrationRepository.php
  Business/
    {Module}ServiceInterface.php
    {Module}Service.php
  Presentation/
    {Module}Controller.php

api/Presentation/Http/Routes/
  {kebab}-routes.php
```

Reference implementation: `api/User` (module folders) + `api/Presentation/Http/Routes/cart-routes.php` / `user-routes.php` (per-module routes).

Do **not** add validation services or notification services unless the user asks. Do **not** dump routes into a shared `web.php` — each module gets its own `{kebab}-routes.php`.

## Business rules — `App\Shared\Contracts\Contracts`

Put precondition / business-rule checks in **Business** services via `api/Shared/Contracts.php` (`App\Shared\Contracts\Contracts`). Do **not** invent a per-module validator, scatter raw `if (...) throw new Exception(...)`, or push these checks into controllers/repos.

Import and call static helpers at the top of domain methods (see `CartService`, `NotificationService`, `SnappyOrderService`):

```php
use App\Shared\Contracts\Contracts;

Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');
Contracts::requires($qty > 0, 'Quantity must be greater than 0');
Contracts::requireEntityFound($product, 'Product');
Contracts::requiresInArray($status, $allowed, 'status');
```

| Helper | Use when |
|--------|----------|
| `requiresNotNull($value, $argumentName)` | Value must not be null |
| `requiresNotNullOrEmpty($value, $argumentName)` | Required string/id/scalar must be present and non-empty |
| `requires($condition, $message)` | Domain invariant (qty, stock, status transitions, etc.) |
| `requiresInArray($value, $array, $argumentName)` | Value must be one of an allowed set |
| `requireEntityFound($entity, $argumentName)` | Loaded `AbstractBaseEntity` must not be empty |

Keep services neat: trim/normalize inputs first, then Contracts guards, then repository work. Controllers only read request input and call the service — no business-rule Exceptions in Presentation.

## Naming

| Piece | Pattern | Example |
|-------|---------|---------|
| Namespace root | `{Module}` | `User` |
| Entity | `{Module}Entity` | `UserEntity` |
| Repo interface | `{Module}RepositoryInterface` | `UserRepositoryInterface` |
| Repo | `{Module}Repository` | `UserRepository` |
| Migration interface | `{Module}MigrationRepositoryInterface` | `UserMigrationRepositoryInterface` |
| Migration | `{Module}MigrationRepository` | `UserMigrationRepository` |
| Service interface | `{Module}ServiceInterface` | `UserServiceInterface` |
| Service | `{Module}Service` | `UserService` |
| Controller | `{Module}Controller` | `UserController` |
| Route file | `{kebab}-routes.php` | `cart-routes.php`, `user-kyc-routes.php` |
| URL resource | plural kebab/snake under `v2` | `carts`, `products`, `user-kycs` |
| Table | snake_plural | `users` |

Namespaces:

- Data: `{Module}\Data`
- Business: `{Module}\Business`
- Presentation: `{Module}\Presentation`

`{kebab}` = kebab-case module name (`Cart` → `cart`, `UserKyc` → `user-kyc`, `SnappyOrder` → `snappy-order`).

## Workflow

Copy and track:

```
Module scaffold:
- [ ] Confirm Module, table, fields
- [ ] Create Data/{Module}Entity.php
- [ ] Create Data/{Module}RepositoryInterface.php
- [ ] Create Data/{Module}Repository.php
- [ ] Create Data/{Module}MigrationRepositoryInterface.php
- [ ] Create Data/{Module}MigrationRepository.php
- [ ] Create Business/{Module}ServiceInterface.php
- [ ] Create Business/{Module}Service.php
- [ ] Create Presentation/{Module}Controller.php
- [ ] Create api/Presentation/Http/Routes/{kebab}-routes.php
- [ ] Register bindings in api/Kernel/boot.php
```

### Step rules

1. **Match `api/User` style** — same extends/implements, `@extends` phpdoc, constructor DI, `migrate()` wiring, controller request/response pattern.
2. **PHP** — match syntax already used in `api/User` (typed properties, typed params). Do not downgrade to PHP 5.6 for these modules.
3. **Fields** — entity properties, migration `->field()` definitions, and any save arrays must only use fields the user/schema provided. Always include `id` via `AbstractBaseEntity`; add `created_at` / `updated_at` only if in the field list.
4. **Repository** — extend `Shared\AbstractBaseRepository`, implement `{Module}RepositoryInterface`, set `$table`, `$sql`, `$size`, `$hydrateClass`. Inject `DbServiceInterface`. Add `addFilter` only for requested filterable columns.
5. **Service** — extend `Shared\AbstractBaseService`, implement `{Module}ServiceInterface`, call `parent::__construct($repository)`, inject migration repo + repository. Expose `migrate()` that delegates to the migration repository. Add only requested domain methods. For those methods, enforce business rules with `App\Shared\Contracts\Contracts` (see above) — not ad-hoc throws or a separate validation layer.
6. **Migration** — inject `R2Packages\Framework\Infrastructure\Framework\Db\Migration`, use `withTable(...)->field(...)->definition(...)->run()` chain like `UserMigrationRepository`.
7. **Controller** — place in `Presentation/`. Inject `{Module}ServiceInterface`, `Request`, `JsonResponseServiceInterface` (and `ApiCredentialServiceInterface` only if auth is needed). Read inputs via `$this->request->get(...)`, call the service, respond with `$this->jsonResponseService->success([...])` like `UserController`. Controllers are **not** mapped in `boot.php` (container resolves them from routes).
8. **Routes** — create `api/Presentation/Http/Routes/{kebab}-routes.php` (one file per module). Mirror siblings like `cart-routes.php` / `notification-routes.php`: `$appServiceContainer->loadRoutes(...)`, wrap with `GlobalApiMiddleware`, nest under `prefix("v2", ...)`, map controller methods. Add `GlobalApiAuthMiddleware` / `GlobalApiAuthAdminMiddleware` only when the module needs auth. Do **not** put new routes in a monolithic `web.php` or another module’s route file. No extra registration step — placing the file in `Routes/` is enough.
9. **DI** — append `use` imports and `$appServiceContainer->container()->map(Interface::class, Concrete::class)` in `api/Kernel/boot.php` for:
   - `{Module}ServiceInterface` → `{Module}Service`
   - `{Module}RepositoryInterface` → `{Module}Repository`
   - `{Module}MigrationRepositoryInterface` → `{Module}MigrationRepository`

## Templates

Use exact skeletons in [templates.md](templates.md). Replace `{Module}`, `{module}`, `{kebab}`, `{table}`, `{resource}`, and field placeholders.

## Minimal service / controller / routes surface

Default beyond inherited `AbstractBaseServiceInterface`:

- Service: `public function migrate();`
- Controller: `migrate()` that calls the service and returns JSON success
- Routes: `{resource}/migrate` GET under `v2` + `GlobalApiMiddleware` (plus one route per requested domain action)

Add domain action methods only when the user specifies them (see `UserServiceInterface` / `UserController`). Enforce rules in the service with `Contracts`, not in the controller. Wire each new controller action in that module’s `{kebab}-routes.php`.

## Out of scope unless requested

- Separate validation / mail / notification helper classes (use `Contracts` in the service instead of a new validator)
- Extra tables or join repositories
- Changing Shared base classes (including extending `Contracts` unless the user asks)
- Custom middleware classes (reuse existing `Presentation\Http\Middlewares\*` unless asked)
