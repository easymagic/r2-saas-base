<?php

namespace OrderItem\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use OrderItem\Business\OrderItemServiceInterface;

class OrderItemController
{
    private OrderItemServiceInterface $orderItemService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        OrderItemServiceInterface $orderItemService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->orderItemService = $orderItemService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->orderItemService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function settle()
    {
        $result = $this->orderItemService->settle((int) $this->request->get('order_item_id'));
        $this->jsonResponseService->success([
            'result' => $result,
            'message' => 'Order item settled successfully',
        ]);
    }

    function fetchForOrder()
    {
        $query = $this->orderItemService->fetchForOrder((int) $this->request->get('order_id'));
        $this->jsonResponseService->success([
            'order_items' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Order items fetched successfully',
        ]);
    }

    function fetchForMerchant()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->orderItemService->fetchForMerchant(
            (int) $user->id,
            (int) $this->request->get('settled', 0),
            (int) $this->request->get('product_id', 0),
            (string) $this->request->get('date_from', ''),
            (string) $this->request->get('date_to', '')
        );
        $this->jsonResponseService->success([
            'order_items' => $query->fetch(),
            'count' => $query->count(),
            'total_amount' => $query->sum('total_line_amount'),
            'message' => 'Order items fetched successfully',
        ]);
    }
}
