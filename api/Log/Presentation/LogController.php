<?php

namespace Log\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use Log\Business\LogServiceInterface;

class LogController
{
    private LogServiceInterface $logService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        LogServiceInterface $logService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->logService = $logService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->logService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function fetch()
    {
        $logs = $this->logService->fetchLogs($this->request->all());
        $this->jsonResponseService->success([
            'logs' => $logs->fetch(),
            'count' => $logs->count(),
            'message' => 'Logs fetched successfully',
        ]);
    }
}
