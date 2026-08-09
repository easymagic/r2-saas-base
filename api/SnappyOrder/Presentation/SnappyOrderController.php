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

    function changePrice()
    {
        $price = $this->request->get('price');
        if ($price === null || $price === '') {
            $price = $this->request->get('total_amount_usd');
        }
        $order = $this->snappyOrderService->changePrice(
            (int) $this->request->get('order_id'),
            (float) $price
        );
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order price updated successfully',
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

    function assignToBatch()
    {
        $order = $this->snappyOrderService->assignToBatch(
            (int) $this->request->get('order_id'),
            (int) $this->request->get('batch_id')
        );
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order assigned to batch successfully',
        ]);
    }

    function unassignFromBatch()
    {
        $order = $this->snappyOrderService->unassignFromBatch(
            (int) $this->request->get('order_id')
        );
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order unassigned from batch successfully',
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

    function getById()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orderId = (int) $this->request->get('order_id');
        $order = $this->snappyOrderService->getById($orderId);

        $role = strtolower((string) $user->role);
        $isAdmin = strpos($role, 'admin') !== false;
        $isOwner = (int) $order->user_id === (int) $user->id;
        $isAssignedAgent = $role === 'agent' && (int) $order->agent_id === (int) $user->id;

        if (!$isAdmin && !$isOwner && !$isAssignedAgent) {
            throw new \Exception('You are not authorized to view this order');
        }

        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order fetched successfully',
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
