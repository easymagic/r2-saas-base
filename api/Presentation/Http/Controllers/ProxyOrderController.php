<?php

namespace Presentation\Http\Controllers;

use Application\ProxyOrder\ProxyOrderServiceInterface;
use Domain\ProxyOrder\Interfaces\ProxyOrderRepositoryInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class ProxyOrderController
{
    private ProxyOrderServiceInterface $proxyOrderService;
    private Request $request;
    private JsonResponseServiceInterface $jsonResponseService;
    private ProxyOrderRepositoryInterface $proxyOrderRepository;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        ProxyOrderServiceInterface $proxyOrderService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ProxyOrderRepositoryInterface $proxyOrderRepository,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->proxyOrderService = $proxyOrderService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->proxyOrderRepository = $proxyOrderRepository;
        $this->apiCredentialService = $apiCredentialService;
    }

    public function myOrders()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $userId = $user->id;
        $proxyOrders = $this->proxyOrderRepository
            ->filterByUserId($userId)
            ->filter($this->request->all())
            ->fetch();
        return $this->jsonResponseService->success([
            'proxy_orders' => $proxyOrders
        ]);
    }

    public function adminOrders()
    {
        $proxyOrders = $this->proxyOrderRepository
            ->filter($this->request->all())
            ->fetch();
        return $this->jsonResponseService->success([
            'proxy_orders' => $proxyOrders
        ]);
    }

    public function createOrder()
    {
        $user = $this->apiCredentialService->getAuthUser();

        $order = $this->proxyOrderService->create(
            $user->id,
            $this->request->get('type'),
            $this->request->get('reference'),
            $this->request->get('link'),
            $this->request->get('description'),
            $this->request->get('total_amount_usd'),
            $this->request->get('screen_shot1'),
            $this->request->get('screen_shot2'),
            $this->request->get('screen_shot3'),
            'pending'
        );

        return $this->jsonResponseService->success([
            'order' => $order
        ]);
    }

    // To do create remaining methods for the controller
}
