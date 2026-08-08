<?php

namespace ProxyOrderChangeLog\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use ProxyOrderChangeLog\Business\ProxyOrderChangeLogServiceInterface;

class ProxyOrderChangeLogController
{
    private ProxyOrderChangeLogServiceInterface $proxyOrderChangeLogService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        ProxyOrderChangeLogServiceInterface $proxyOrderChangeLogService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->proxyOrderChangeLogService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }
}
