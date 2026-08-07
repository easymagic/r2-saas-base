---
name: 3-tier-arch
description: >-
  Scaffolds a modular 3-tier PHP module (Data + Business) with repository
  interfaces, repositories, service interfaces, services, migration interfaces,
  and migrations, following api/User. Use when the user invokes /3-tier-arch or
  asks to create a new module, repository, service, or migration in the modular
  3-tier layout under api/.
disable-model-invocation: true
---

# 3-Tier Architecture Module Scaffold

Creates a new module under `api/{Module}/` in modular form: repository interfaces, repository, service interfaces, services, migration interfaces, and migration. Mirror `api/User`.

## When to use

- User invokes `/3-tier-arch`
- User asks to scaffold a new domain module (repo + service + migration)

## Before generating

Collect (ask if missing):

1. **Module name** (PascalCase singular preferred): e.g. `User`, `Wallet`, `Order`
2. **Table name** (snake_plural): e.g. `users`, `wallets`
3. **Fields** — only from an explicit schema / `@schema.sql` / user list. Do not invent columns.
4. Optional domain methods for the service interface (beyond `migrate()`)

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
```

Reference implementation: `api/User` (same folders and naming).

Do **not** add Presentation/controllers, validation services, or notification services unless the user asks.

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
| Table | snake_plural | `users` |

Namespaces:

- Data: `{Module}\Data`
- Business: `{Module}\Business`

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
- [ ] Register bindings in api/Kernel/boot.php
```

### Step rules

1. **Match `api/User` style** — same extends/implements, `@extends` phpdoc, constructor DI, `migrate()` wiring.
2. **PHP** — match syntax already used in `api/User` (typed properties, typed params). Do not downgrade to PHP 5.6 for these modules.
3. **Fields** — entity properties, migration `->field()` definitions, and any save arrays must only use fields the user/schema provided. Always include `id` via `AbstractBaseEntity`; add `created_at` / `updated_at` only if in the field list.
4. **Repository** — extend `Shared\AbstractBaseRepository`, implement `{Module}RepositoryInterface`, set `$table`, `$sql`, `$size`, `$hydrateClass`. Inject `DbServiceInterface`. Add `addFilter` only for requested filterable columns.
5. **Service** — extend `Shared\AbstractBaseService`, implement `{Module}ServiceInterface`, call `parent::__construct($repository)`, inject migration repo + repository. Expose `migrate()` that delegates to the migration repository. Add only requested domain methods.
6. **Migration** — inject `R2Packages\Framework\Infrastructure\Framework\Db\Migration`, use `withTable(...)->field(...)->definition(...)->run()` chain like `UserMigrationRepository`.
7. **DI** — append `use` imports and `$appServiceContainer->container()->map(Interface::class, Concrete::class)` in `api/Kernel/boot.php` for:
   - `{Module}ServiceInterface` → `{Module}Service`
   - `{Module}RepositoryInterface` → `{Module}Repository`
   - `{Module}MigrationRepositoryInterface` → `{Module}MigrationRepository`

## Templates

Use exact skeletons in [templates.md](templates.md). Replace `{Module}`, `{module}`, `{table}`, and field placeholders.

## Minimal service surface

Default service interface methods beyond inherited `AbstractBaseServiceInterface`:

- `public function migrate();`

Add domain methods only when the user specifies them (see `UserServiceInterface` for the style of docblocks + signatures).

## Out of scope unless requested

- Controllers / routes
- Validation / mail / notification helpers
- Extra tables or join repositories
- Changing Shared base classes
