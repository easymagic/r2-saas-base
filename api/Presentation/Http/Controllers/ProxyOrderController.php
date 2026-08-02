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

    function show(){
        $order = $this->proxyOrderService->find($this->request->get('id'));
        return $this->jsonResponseService->success([
            'order' => $order
        ]);
    }

    function destroy(){
        $result = $this->proxyOrderService->delete($this->request->get('id'));
        return $this->jsonResponseService->success([
            'message' => 'Order deleted successfully',
            'result' => $result
        ]);
    }

    function adjustPrice(){
        $id = $this->request->get('order_id',0);
        $price = $this->request->get('price');
        $order = $this->proxyOrderService->adjustPrice($id, $price);
        return $this->jsonResponseService->success([    
            'order' => $order,
            "message" => "Price adjusted successfully"
        ]);
    }

    function assignToBatch(){
        $id = $this->request->get('id');
        $batchId = $this->request->get('batch_id');
        $order = $this->proxyOrderService->assignToBatch($id, $batchId);
        return $this->jsonResponseService->success([
            'order' => $order,
            "message" => "Order assigned to batch successfully"
        ]);
    }

    function assignToAgent(){
        $id = $this->request->get('id');
        $agentId = $this->request->get('agent_id');
        $order = $this->proxyOrderService->assignToAgent($id, $agentId);
        return $this->jsonResponseService->success([
            'order' => $order,
            "message" => "Order assigned to agent successfully"
        ]);
    }

    function publishSettings(){
        $this->proxyOrderService->publishSettings();
        return $this->jsonResponseService->success([
            "message" => "Settings published successfully"
        ]);
    }

    function updateStatus(){
        $id = $this->request->get('id');
        $status = $this->request->get('status');
        $order = $this->proxyOrderService->updateStatus($id, $status);
        return $this->jsonResponseService->success([
            'order' => $order,
            "message" => "Order status updated successfully"
        ]);
    }

    function migrate(){
        $this->proxyOrderService->migrate();
        return $this->jsonResponseService->success([
            "message" => "Proxy order data migrated successfully"
        ]);
    }

}
