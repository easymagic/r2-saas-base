<?php

namespace EcomOrder\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use EcomOrder\Business\Dtos\AssignToAgentDto;
use EcomOrder\Business\Dtos\CheckoutDto;
use EcomOrder\Business\Dtos\UpdateDeliveryStatusDto;
use EcomOrder\Business\EcomOrderServiceInterface;

class EcomOrderController
{
    private EcomOrderServiceInterface $ecomOrderService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        EcomOrderServiceInterface $ecomOrderService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->ecomOrderService = $ecomOrderService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->ecomOrderService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function checkout()
    {
        $userId = 0;
        $isGuest = 1;
        $xUserToken = (string) $this->request->get('x-user-token', '');
        if ($xUserToken !== '') {
            $this->apiCredentialService->validateUserToken($xUserToken);
            $user = $this->apiCredentialService->getAuthUser();
            $userId = (int) $user->id;
            $isGuest = 0;
        }

        $order = $this->ecomOrderService->checkout(new CheckoutDto(
            $userId,
            (string) $this->request->get('type', ''),
            (int) $this->request->get('number_of_installment', 0),
            (string) $this->request->get('customer_name', ''),
            (string) $this->request->get('customer_address', ''),
            (string) $this->request->get('customer_email', ''),
            (string) $this->request->get('reference', ''),
            (string) $this->request->get('uuid', $this->request->get('cart_uuid', ''))
        ));

        $payload = [
            'order' => $order,
            'message' => 'Order checked out successfully',
        ];
        if (!empty($order->payment_url)) {
            $payload['payment_url'] = $order->payment_url;
        }
        $this->jsonResponseService->success($payload);
    }

    function fetchForUser()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->ecomOrderService->fetchForUser((int) $user->id, $this->request->all());
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Orders fetched successfully',
        ]);
    }

    function fetchForAdmin()
    {
        $query = $this->ecomOrderService->fetchForAdmin($this->request->all());
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Orders fetched successfully',
        ]);
    }

    function fetchForAgent()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->ecomOrderService->fetchForAgent((int) $user->id, $this->request->all());
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Agent orders fetched successfully',
        ]);
    }

    function getById()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $order = $this->ecomOrderService->find((int) $this->request->get('order_id'));

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

    function updateDeliveryStatus()
    {
        $order = $this->ecomOrderService->updateDeliveryStatus(new UpdateDeliveryStatusDto(
            (int) $this->request->get('order_id'),
            (string) $this->request->get('delivery_status', '')
        ));
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Delivery status updated successfully',
        ]);
    }

    function assignToAgent()
    {
        $order = $this->ecomOrderService->assignToAgent(new AssignToAgentDto(
            (int) $this->request->get('order_id'),
            (int) $this->request->get('agent_id')
        ));
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order assigned to agent successfully',
        ]);
    }

    function getPendingPayments()
    {
        $query = $this->ecomOrderService->getPendingPayments();
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Pending payments processed successfully',
        ]);
    }

    function publishSettings()
    {
        $this->ecomOrderService->publishSettings();
        $this->jsonResponseService->success([
            'message' => 'Settings published successfully',
        ]);
    }
}
