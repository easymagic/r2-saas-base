<?php

namespace Presentation\Http\Middlewares;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;
use UserKyc\Business\UserKycServiceInterface;

class ProductCheckMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private UserKycServiceInterface $userKycService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        UserKycServiceInterface $userKycService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->userKycService = $userKycService;
    }

    public function handle()
    {
        $user = $this->apiCredentialService->getAuthUser();

        if (!$this->userKycService->isKycCompleted((int) $user->id)) {
            throw new \Exception('Unauthorized, KYC must be approved to manage or view your products');
        }
    }
}
