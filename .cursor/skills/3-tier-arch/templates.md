# Templates (from `api/User`)

Replace `{Module}` (e.g. `User`), `{table}` (e.g. `users`), and field blocks with the confirmed schema.

---

## `api/{Module}/Data/{Module}Entity.php`

```php
<?php

namespace {Module}\Data;

use Shared\AbstractBaseEntity;

class {Module}Entity extends AbstractBaseEntity
{
    // public string $name = '';
    // public string $created_at = '';
    // public string $updated_at = '';
}
```

Add one typed public property per schema field (defaults like `UserEntity`).

---

## `api/{Module}/Data/{Module}RepositoryInterface.php`

```php
<?php

namespace {Module}\Data;

use Shared\AbstractBaseRepositoryInterface;

/**
 * @extends AbstractBaseRepositoryInterface<{Module}Entity>
 */
interface {Module}RepositoryInterface extends AbstractBaseRepositoryInterface
{
}
```

---

## `api/{Module}/Data/{Module}Repository.php`

```php
<?php

namespace {Module}\Data;

use Shared\AbstractBaseRepository;
use {Module}\Data\{Module}Entity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * @extends AbstractBaseRepository<{Module}Entity>
 */
class {Module}Repository extends AbstractBaseRepository implements {Module}RepositoryInterface
{
   protected string $table = '{table}';
   protected string $sql = "SELECT * FROM {table} WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = {Module}Entity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
      // Optional filters (see UserRepository):
      // $this->addFilter("search", function (string $value, string &$sql, array &$params) {
      //   $sql .= " AND (name LIKE :search)";
      //   $params['search'] = "%".$value."%";
      // });
   }
}
```

---

## `api/{Module}/Data/{Module}MigrationRepositoryInterface.php`

```php
<?php

namespace {Module}\Data;

interface {Module}MigrationRepositoryInterface
{
    public function migrate();
}
```

---

## `api/{Module}/Data/{Module}MigrationRepository.php`

```php
<?php

namespace {Module}\Data;

use {Module}\Data\{Module}MigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class {Module}MigrationRepository implements {Module}MigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('{table}')
        // ->field('name')->definition('VARCHAR(255) DEFAULT NULL')->run()
        // ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
        // ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
        ;

        return "ok";
    }
}
```

Chain one `->field(...)->definition(...)->run()` per schema column (see `UserMigrationRepository`).

---

## `api/{Module}/Business/{Module}ServiceInterface.php`

```php
<?php

namespace {Module}\Business;

use Shared\AbstractBaseServiceInterface;
use {Module}\Data\{Module}Entity;

/**
 * @extends AbstractBaseServiceInterface<{Module}Entity>
 */
interface {Module}ServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    // Add domain methods only when requested, e.g.:
    // /**
    //  * @param string $name
    //  * @return {Module}Entity
    //  */
    // public function create(string $name);
}
```

---

## `api/{Module}/Business/{Module}Service.php`

```php
<?php

namespace {Module}\Business;

use App\Shared\Contracts\Contracts;
use Shared\AbstractBaseService;
use {Module}\Data\{Module}RepositoryInterface;
use {Module}\Data\{Module}Entity;
use {Module}\Data\{Module}MigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<{Module}Entity, {Module}RepositoryInterface>
 */
class {Module}Service extends AbstractBaseService implements {Module}ServiceInterface
{
    private {Module}MigrationRepositoryInterface ${module}MigrationRepositoryInterface;
    private {Module}RepositoryInterface ${module}Repository;

    public function __construct(
        {Module}MigrationRepositoryInterface ${module}MigrationRepositoryInterface,
        {Module}RepositoryInterface ${module}Repository
    ) {
        parent::__construct(${module}Repository);
        $this->{module}MigrationRepositoryInterface = ${module}MigrationRepositoryInterface;
        $this->{module}Repository = ${module}Repository;
    }

    public function migrate()
    {
        return $this->{module}MigrationRepositoryInterface->migrate();
    }

    // Domain methods: normalize inputs → Contracts::* → repository.
    // Example:
    // public function create(string $name)
    // {
    //     $name = trim($name);
    //     Contracts::requiresNotNullOrEmpty($name, 'name');
    //     return $this->{module}Repository->save(0, [
    //         'name' => $name,
    //     ]);
    // }
}
```

`{module}` = camelCase of module name (`User` → `user`).

Always `use App\Shared\Contracts\Contracts` when adding domain methods. Prefer `Contracts::requires*`, `requiresInArray`, and `requireEntityFound` over inline `throw new Exception(...)`.

---

## `api/{Module}/Presentation/{Module}Controller.php`

```php
<?php

namespace {Module}\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use {Module}\Business\{Module}ServiceInterface;

class {Module}Controller
{
    private {Module}ServiceInterface ${module}Service;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        {Module}ServiceInterface ${module}Service,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->{module}Service = ${module}Service;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->{module}Service->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    // Add action methods only when requested (see UserController):
    // function create()
    // {
    //     $name = $this->request->get('name');
    //     $item = $this->{module}Service->create($name);
    //     $this->jsonResponseService->success([
    //         '{module}' => $item,
    //         'message' => 'Created successfully',
    //     ]);
    // }
}
```

Inject `Presentation\ApiCredential\ApiCredentialServiceInterface` only when auth is required (see `UserController::me`).

Do not register the controller in `boot.php`. Wire HTTP endpoints in a **module-specific** route file (next section), not in another module’s routes and not in a shared `web.php`.

---

## `api/Presentation/Http/Routes/{kebab}-routes.php`

One file per module. Name: kebab-case module + `-routes.php` (e.g. `Cart` → `cart-routes.php`, `UserKyc` → `user-kyc-routes.php`). See existing files in `api/Presentation/Http/Routes/`.

`{resource}` = plural URL segment (e.g. `carts`, `products`, `notifications`).

```php
<?php

use {Module}\Presentation\{Module}Controller;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("{resource}/migrate", [{Module}Controller::class, "migrate"]);

            // Add domain routes only when requested (see cart-routes.php / notification-routes.php):
            // $route->post("{resource}", [{Module}Controller::class, "create"]);
            //
            // Auth-gated example (see product-routes.php / notification-routes.php):
            // $route->middleware([
            //     GlobalApiAuthMiddleware::class
            // ], function (RouteServiceInterface $route) {
            //     $route->get("{resource}", [{Module}Controller::class, "fetch"]);
            // });
        });
    });
});
```

Import extra middlewares only when needed:

- `Presentation\Http\Middlewares\GlobalApiAuthMiddleware`
- `Presentation\Http\Middlewares\GlobalApiAuthAdminMiddleware`

Place static path segments (e.g. `{resource}/admin`, `{resource}/slug/{slug}`) **before** parameterized `{resource}/{id}` routes so IDs do not capture those labels (see `product-routes.php` / `category-routes.php`).

---

## `api/Kernel/boot.php` registration

Add `use` imports (group with other module imports):

```php
use {Module}\Business\{Module}Service;
use {Module}\Business\{Module}ServiceInterface;
use {Module}\Data\{Module}MigrationRepository;
use {Module}\Data\{Module}MigrationRepositoryInterface;
use {Module}\Data\{Module}Repository;
use {Module}\Data\{Module}RepositoryInterface;
```

Add maps (same style as User bindings):

```php
$appServiceContainer->container()->map({Module}ServiceInterface::class, {Module}Service::class);
$appServiceContainer->container()->map({Module}MigrationRepositoryInterface::class, {Module}MigrationRepository::class);
$appServiceContainer->container()->map({Module}RepositoryInterface::class, {Module}Repository::class);
```

---

## Concrete references

| File | Role |
|------|------|
| `api/User/Data/UserEntity.php` | Entity fields |
| `api/User/Data/UserRepositoryInterface.php` | Empty repo interface extending Shared |
| `api/User/Data/UserRepository.php` | Table + filters |
| `api/User/Data/UserMigrationRepositoryInterface.php` | `migrate()` |
| `api/User/Data/UserMigrationRepository.php` | Migration field chain |
| `api/User/Business/UserServiceInterface.php` | Domain + `migrate()` |
| `api/User/Business/UserService.php` | DI + `migrate()` delegation |
| `api/User/Presentation/UserController.php` | HTTP actions + JSON responses |
| `api/Kernel/boot.php` | Interface → class maps |
| `api/Shared/Contracts.php` | Shared business-rule helpers |
| `api/Cart/Business/CartService.php` | Domain methods using `Contracts` |
| `api/Presentation/Http/Routes/cart-routes.php` | Simple per-module routes |
| `api/Presentation/Http/Routes/notification-routes.php` | Auth-gated per-module routes |
| `api/Presentation/Http/Routes/product-routes.php` | Nested auth/admin + route order |
