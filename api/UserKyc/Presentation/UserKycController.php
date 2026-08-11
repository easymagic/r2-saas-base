<?php

namespace UserKyc\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use UserKyc\Business\UserKycServiceInterface;

class UserKycController
{
    private UserKycServiceInterface $userKycService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        UserKycServiceInterface $userKycService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->userKycService = $userKycService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->userKycService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }
}
