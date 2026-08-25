<?php

namespace SnappyOrder\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use Shared\Contracts;
use SnappyOrder\Business\Dtos\AssignToAgentDto;
use SnappyOrder\Business\Dtos\AssignToBatchDto;
use SnappyOrder\Business\Dtos\ChangePriceDto;
use SnappyOrder\Business\Dtos\ChangeStatusDto;
use SnappyOrder\Business\Dtos\CreateDto;
use SnappyOrder\Business\Dtos\PayOrderFromWalletDto;
use SnappyOrder\Business\Usecases\AssignToAgentService;
use SnappyOrder\Business\Usecases\AssignToBatchService;
use SnappyOrder\Business\Usecases\ChangePriceService;
use SnappyOrder\Business\Usecases\ChangeStatusService;
use SnappyOrder\Business\Usecases\CreateService;
use SnappyOrder\Business\Usecases\GetByIdService;
use SnappyOrder\Business\Usecases\GetMyOrderAsAdminService;
use SnappyOrder\Business\Usecases\GetMyOrdersAsAgentService;
use SnappyOrder\Business\Usecases\GetMyOrdersAsCustomerService;
use SnappyOrder\Business\Usecases\MigrateService;
use SnappyOrder\Business\Usecases\PayOrderFromWalletService;
use SnappyOrder\Business\Usecases\PublishSettingsService;
use SnappyOrder\Business\Usecases\UnassignFromBatchService;

class SnappyOrderController
{
    private MigrateService $migrateService;
    private CreateService $createService;
    private ChangeStatusService $changeStatusService;
    private ChangePriceService $changePriceService;
    private AssignToAgentService $assignToAgentService;
    private AssignToBatchService $assignToBatchService;
    private UnassignFromBatchService $unassignFromBatchService;
    private GetMyOrdersAsAgentService $getMyOrdersAsAgentService;
    private GetMyOrdersAsCustomerService $getMyOrdersAsCustomerService;
    private GetMyOrderAsAdminService $getMyOrderAsAdminService;
    private GetByIdService $getByIdService;
    private PublishSettingsService $publishSettingsService;
    private PayOrderFromWalletService $payOrderFromWalletService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        MigrateService $migrateService,
        CreateService $createService,
        ChangeStatusService $changeStatusService,
        ChangePriceService $changePriceService,
        AssignToAgentService $assignToAgentService,
        AssignToBatchService $assignToBatchService,
        UnassignFromBatchService $unassignFromBatchService,
        GetMyOrdersAsAgentService $getMyOrdersAsAgentService,
        GetMyOrdersAsCustomerService $getMyOrdersAsCustomerService,
        GetMyOrderAsAdminService $getMyOrderAsAdminService,
        GetByIdService $getByIdService,
        PublishSettingsService $publishSettingsService,
        PayOrderFromWalletService $payOrderFromWalletService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->migrateService = $migrateService;
        $this->createService = $createService;
        $this->changeStatusService = $changeStatusService;
        $this->changePriceService = $changePriceService;
        $this->assignToAgentService = $assignToAgentService;
        $this->assignToBatchService = $assignToBatchService;
        $this->unassignFromBatchService = $unassignFromBatchService;
        $this->getMyOrdersAsAgentService = $getMyOrdersAsAgentService;
        $this->getMyOrdersAsCustomerService = $getMyOrdersAsCustomerService;
        $this->getMyOrderAsAdminService = $getMyOrderAsAdminService;
        $this->getByIdService = $getByIdService;
        $this->publishSettingsService = $publishSettingsService;
        $this->payOrderFromWalletService = $payOrderFromWalletService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => true,
        ]);
    }

    function create()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $order = $this->createService->execute(new CreateDto(
            (int) $user->id,
            (string) $this->request->get('link'),
            (string) $this->request->get('description'),
            (array) $this->request->get('screen_shot1', []),
            (array) $this->request->get('screen_shot2', []),
            (array) $this->request->get('screen_shot3', []),
            (float) $this->request->get('total_amount_usd')
        ));
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order created successfully',
        ]);
    }

    function changeStatus()
    {
        $order = $this->changeStatusService->execute(new ChangeStatusDto(
            (int) $this->request->get('order_id'),
            (string) $this->request->get('status'),
            (string) $this->request->get('pickup_otp_code', '')
        ));
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
        $order = $this->changePriceService->execute(new ChangePriceDto(
            (int) $this->request->get('order_id'),
            (float) $price
        ));
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order price updated successfully',
        ]);
    }

    function assignToAgent()
    {
        $order = $this->assignToAgentService->execute(new AssignToAgentDto(
            (int) $this->request->get('order_id'),
            (int) $this->request->get('agent_id')
        ));
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order assigned to agent successfully',
        ]);
    }

    function assignToBatch()
    {
        $order = $this->assignToBatchService->execute(new AssignToBatchDto(
            (int) $this->request->get('order_id'),
            (int) $this->request->get('batch_id')
        ));
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order assigned to batch successfully',
        ]);
    }

    function unassignFromBatch()
    {
        $order = $this->unassignFromBatchService->execute(
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
        $query = $this->getMyOrdersAsAgentService->query(
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
        $query = $this->getMyOrdersAsCustomerService->query(
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
        $query = $this->getMyOrderAsAdminService->query(
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
        $order = $this->getByIdService->query($orderId);

        $role = strtolower((string) $user->role);
        $isAdmin = strpos($role, 'admin') !== false;
        $isOwner = (int) $order->user_id === (int) $user->id;
        $isAssignedAgent = $role === 'agent' && (int) $order->agent_id === (int) $user->id;

        Contracts::requires($isAdmin || $isOwner || $isAssignedAgent, 'You are not authorized to view this order');

        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order fetched successfully',
        ]);
    }

    function publishSettings()
    {
        $this->publishSettingsService->execute();
        $this->jsonResponseService->success([
            'message' => 'Settings published successfully',
        ]);
    }

    function payOrderFromWallet()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $order = $this->payOrderFromWalletService->execute(new PayOrderFromWalletDto(
            (int) $this->request->get('order_id'),
            (int) $user->id
        ));
        $this->jsonResponseService->success([
            'order' => $order,
            'message' => 'Order paid from wallet successfully',
        ]);
    }
}
