<?php

namespace Presentation\Http\Middlewares;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;

class WebAuthMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(ApiCredentialServiceInterface $apiCredentialService)
    {
        $this->apiCredentialService = $apiCredentialService;
    }

    public function handle()
    {
        WebSession::start();
        $token = WebSession::userToken();
        if ($token === '') {
            WebSession::flash('error', 'Please sign in to continue.');
            WebSession::redirect('/login');
        }
        try {
            $this->apiCredentialService->validateUserToken($token);
        } catch (\Exception $e) {
            WebSession::logout();
            WebSession::flash('error', 'Your session expired. Please sign in again.');
            WebSession::redirect('/login');
        }
    }
}
