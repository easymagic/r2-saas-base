# Middleware templates

Replace `{Name}` (e.g. `KycPending` → class `KycPendingMiddleware`).
Replace constructor deps / `handle()` body from the user's intent and existing interfaces.

---

## Default skeleton (Request + ApiCredential)

Use when the middleware reads headers/params and/or validates tokens.

```php
<?php

namespace Presentation\Http\Middlewares;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;

class {Name}Middleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;

    public function __construct(ApiCredentialServiceInterface $apiCredentialService, Request $request)
    {
        $this->request = $request;
        $this->apiCredentialService = $apiCredentialService;
    }

    public function handle()
    {
        // Intent-driven logic using $this->request->get(...) and service interfaces
    }
}
```

---

## Side-effect skeleton (domain services, no Request)

Use when work is driven by `getAuthUser()` / domain lookups (see `WalletFeedbackMiddleware`).

```php
<?php

namespace Presentation\Http\Middlewares;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;
// use {Module}\Business\{Module}ServiceInterface;

class {Name}Middleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    // private {Module}ServiceInterface ${module}Service;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService
        // , {Module}ServiceInterface ${module}Service
    ) {
        $this->apiCredentialService = $apiCredentialService;
        // $this->{module}Service = ${module}Service;
    }

    public function handle()
    {
        $user = $this->apiCredentialService->getAuthUser();
        // Intent-driven side effects via injected *ServiceInterface methods
    }
}
```

---

## Admin / role gate fragment

From `GlobalApiAuthAdminMiddleware` — adapt role string checks; do not invent new auth headers.

```php
$xToken = $this->request->get('x-token');
$xUserToken = $this->request->get('x-user-token');
$this->apiCredentialService->validateToken($xToken);
$this->apiCredentialService->validateUserToken($xUserToken);
$user = $this->apiCredentialService->getAuthUser();
if (strpos($user->role, 'admin') === false) {
    throw new \Exception('Unauthorized , only admin can access this resource');
}
```

---

## Route wrap fragment (`web.php`)

Only when the user asks to attach the middleware:

```php
use Presentation\Http\Middlewares\{Name}Middleware;

$route->middleware([
    {Name}Middleware::class
], function (RouteServiceInterface $route) {
    // routes...
});
```
