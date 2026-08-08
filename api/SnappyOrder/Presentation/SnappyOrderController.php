<?php

namespace SnappyOrder\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use SnappyOrder\Business\SnappyOrderServiceInterface;

class SnappyOrderController
{
    private SnappyOrderServiceInterface $snappyOrderService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        SnappyOrderServiceInterface $snappyOrderService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->snappyOrderService = $snappyOrderService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $this->snappyOrderService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => true,
        ]);
    }

    function create()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $order = $this->snappyOrderService->create(
            $user->id,
            $this->request->get('link'),
            $this->request->get('description'),
            $this->request->get('screen_shot1', []),
            $this->request->get('screen_shot2', []),
            $this->request->get('screen_shot3', []),
            (float) $this->request->get('total_amount_usd')
        );
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order created successfully',
        ]);
    }

    function changeStatus()
    {
        $order = $this->snappyOrderService->changeStatus(
            (int) $this->request->get('order_id'),
            $this->request->get('status')
        );
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order status updated successfully',
        ]);
    }

    function assignToAgent()
    {
        $order = $this->snappyOrderService->assignToAgent(
            (int) $this->request->get('order_id'),
            (int) $this->request->get('agent_id')
        );
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order assigned to agent successfully',
        ]);
    }

    function getMyOrdersAsAgent()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->snappyOrderService->getMyOrdersAsAgent(
            $user->id,
            $this->request->all()
        );
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Agent orders fetched successfully',
        ]);
    }

    function getMyOrdersAsCustomer()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->snappyOrderService->getMyOrdersAsCustomer(
            $user->id,
            $this->request->all()
        );
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Customer orders fetched successfully',
        ]);
    }

    function getMyOrderAsAdmin()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->snappyOrderService->getMyOrderAsAdmin(
            $user->id,
            $this->request->all()
        );
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Admin orders fetched successfully',
        ]);
    }

    function publishSettings()
    {
        $this->snappyOrderService->publishSettings();
        $this->jsonResponseService->success([
            'message' => 'Settings published successfully',
        ]);
    }

    function payOrderFromWallet()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $order = $this->snappyOrderService->payOrderFromWallet(
            (int) $this->request->get('order_id'),
            $user->id
        );
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order paid from wallet successfully',
        ]);
    }
}
