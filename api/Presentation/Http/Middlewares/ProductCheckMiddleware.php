<?php

namespace Presentation\Http\Middlewares;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;
use UserKyc\Business\Usecases\IsKycCompletedService;

class ProductCheckMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private IsKycCompletedService $isKycCompletedService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        IsKycCompletedService $isKycCompletedService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->isKycCompletedService = $isKycCompletedService;
    }

    public function handle()
    {
        $user = $this->apiCredentialService->getAuthUser();

        if (!$this->isKycCompletedService->query((int) $user->id)) {
            throw new \Exception('Unauthorized, KYC must be approved to manage or view your products');
        }
    }
}
