<?php

namespace Presentation\Http\Controllers;

use Application\ProxyOrder\ProxyOrderServiceInterface;
use Domain\ProxyOrder\Interfaces\ProxyOrderRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class ProxyOrderController
{
    private ProxyOrderServiceInterface $proxyOrderService;
    private Request $request;
    private JsonResponseServiceInterface $jsonResponseService;
    private ProxyOrderRepositoryInterface $proxyOrderRepository;

    public function __construct(
        ProxyOrderServiceInterface $proxyOrderService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ProxyOrderRepositoryInterface $proxyOrderRepository
    ) {
        $this->proxyOrderService = $proxyOrderService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->proxyOrderRepository = $proxyOrderRepository;
    }

    public function index()
    {
        $proxyOrders = $this->proxyOrderRepository->filter($this->request->all())->fetch();
        return $this->jsonResponseService->success([
            'proxy_orders' => $proxyOrders
        ]);
    }

    // To do create remaining methods for the controller
}
