<?php

namespace OrderItem\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use OrderItem\Business\OrderItemServiceInterface;

class OrderItemController
{
    private OrderItemServiceInterface $orderItemService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        OrderItemServiceInterface $orderItemService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->orderItemService = $orderItemService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->orderItemService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }
}
