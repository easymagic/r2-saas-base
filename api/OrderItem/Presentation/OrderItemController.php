<?php

namespace OrderItem\Presentation;

use OrderItem\Business\Dtos\FetchForMerchantDto;
use OrderItem\Business\Usecases\FetchForMerchantService;
use OrderItem\Business\Usecases\FetchForOrderService;
use OrderItem\Business\Usecases\MigrateService;
use OrderItem\Business\Usecases\SettleService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class OrderItemController
{
    private MigrateService $migrateService;
    private SettleService $settleService;
    private FetchForOrderService $fetchForOrderService;
    private FetchForMerchantService $fetchForMerchantService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        MigrateService $migrateService,
        SettleService $settleService,
        FetchForOrderService $fetchForOrderService,
        FetchForMerchantService $fetchForMerchantService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->migrateService = $migrateService;
        $this->settleService = $settleService;
        $this->fetchForOrderService = $fetchForOrderService;
        $this->fetchForMerchantService = $fetchForMerchantService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function settle()
    {
        $result = $this->settleService->execute((int) $this->request->get('order_item_id'));
        $this->jsonResponseService->success([
            'result' => $result,
            'message' => 'Order item settled successfully',
        ]);
    }

    function fetchForOrder()
    {
        $query = $this->fetchForOrderService->query((int) $this->request->get('order_id'));
        $this->jsonResponseService->success([
            'order_items' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Order items fetched successfully',
        ]);
    }

    function fetchForMerchant()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->fetchForMerchantService->query(new FetchForMerchantDto(
            (int) $user->id,
            (int) $this->request->get('settled', 0),
            (int) $this->request->get('product_id', 0),
            (string) $this->request->get('date_from', ''),
            (string) $this->request->get('date_to', '')
        ));
        $this->jsonResponseService->success([
            'order_items' => $query->fetch(),
            'count' => $query->count(),
            'total_amount' => $query->sum('total_line_amount'),
            'message' => 'Order items fetched successfully',
        ]);
    }
}
