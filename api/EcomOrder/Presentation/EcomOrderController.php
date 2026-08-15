<?php

namespace EcomOrder\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use EcomOrder\Business\EcomOrderServiceInterface;

class EcomOrderController
{
    private EcomOrderServiceInterface $ecomOrderService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        EcomOrderServiceInterface $ecomOrderService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->ecomOrderService = $ecomOrderService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->ecomOrderService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }
}
