<?php

namespace Presentation\Http\Middlewares;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;

class WebAdminMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(ApiCredentialServiceInterface $apiCredentialService)
    {
        $this->apiCredentialService = $apiCredentialService;
    }

    public function handle()
    {
        $user = $this->apiCredentialService->getAuthUser();
        if (!$user || strpos($user->role, 'admin') === false) {
            WebSession::flash('error', 'Admin access required.');
            WebSession::redirect('/dashboard');
        }
    }
}
