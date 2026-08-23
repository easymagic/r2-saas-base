<?php

namespace EcomOrder\Presentation;

use EcomOrder\Business\Dtos\AssignToAgentDto;
use EcomOrder\Business\Dtos\CheckoutDto;
use EcomOrder\Business\Dtos\UpdateDeliveryStatusDto;
use EcomOrder\Business\Usecases\AssignToAgentService;
use EcomOrder\Business\Usecases\CheckoutService;
use EcomOrder\Business\Usecases\FetchForAdminService;
use EcomOrder\Business\Usecases\FetchForAgentService;
use EcomOrder\Business\Usecases\FetchForUserService;
use EcomOrder\Business\Usecases\FindByIdService;
use EcomOrder\Business\Usecases\GetPendingPaymentsService;
use EcomOrder\Business\Usecases\MigrateService;
use EcomOrder\Business\Usecases\PublishSettingsService;
use EcomOrder\Business\Usecases\UpdateDeliveryStatusService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class EcomOrderController
{
    private MigrateService $migrateService;
    private CheckoutService $checkoutService;
    private FetchForUserService $fetchForUserService;
    private FetchForAdminService $fetchForAdminService;
    private FetchForAgentService $fetchForAgentService;
    private FindByIdService $findByIdService;
    private UpdateDeliveryStatusService $updateDeliveryStatusService;
    private AssignToAgentService $assignToAgentService;
    private GetPendingPaymentsService $getPendingPaymentsService;
    private PublishSettingsService $publishSettingsService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        MigrateService $migrateService,
        CheckoutService $checkoutService,
        FetchForUserService $fetchForUserService,
        FetchForAdminService $fetchForAdminService,
        FetchForAgentService $fetchForAgentService,
        FindByIdService $findByIdService,
        UpdateDeliveryStatusService $updateDeliveryStatusService,
        AssignToAgentService $assignToAgentService,
        GetPendingPaymentsService $getPendingPaymentsService,
        PublishSettingsService $publishSettingsService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->migrateService = $migrateService;
        $this->checkoutService = $checkoutService;
        $this->fetchForUserService = $fetchForUserService;
        $this->fetchForAdminService = $fetchForAdminService;
        $this->fetchForAgentService = $fetchForAgentService;
        $this->findByIdService = $findByIdService;
        $this->updateDeliveryStatusService = $updateDeliveryStatusService;
        $this->assignToAgentService = $assignToAgentService;
        $this->getPendingPaymentsService = $getPendingPaymentsService;
        $this->publishSettingsService = $publishSettingsService;
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

    function checkout()
    {

        $order = $this->checkoutService->execute(new CheckoutDto(
            (int) $this->request->get('user_id', 0),
            (string) $this->request->get('type', ''),
            (int) $this->request->get('number_of_installment', 0),
            (string) $this->request->get('customer_name', ''),
            (string) $this->request->get('customer_address', ''),
            (string) $this->request->get('customer_email', ''),
            (string) $this->request->get('uuid', $this->request->get('cart_uuid', ''))
        ));

        $payload = [
            'order' => $order,
            'message' => 'Order checked out successfully',
        ];
        if ($order !== null && !empty($order->payment_url)) {
            $payload['payment_url'] = $order->payment_url;
        }
        $this->jsonResponseService->success($payload);
    }

    function fetchForUser()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->fetchForUserService->query((int) $user->id, $this->request->all());
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Orders fetched successfully',
        ]);
    }

    function fetchForAdmin()
    {
        $query = $this->fetchForAdminService->query($this->request->all());
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Orders fetched successfully',
        ]);
    }

    function fetchForAgent()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->fetchForAgentService->query((int) $user->id, $this->request->all());
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Agent orders fetched successfully',
        ]);
    }

    function getById()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $order = $this->findByIdService->query((int) $this->request->get('order_id'));

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
        $order = $this->updateDeliveryStatusService->execute(new UpdateDeliveryStatusDto(
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
        $order = $this->assignToAgentService->execute(new AssignToAgentDto(
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
        $query = $this->getPendingPaymentsService->execute();
        $this->jsonResponseService->success([
            'orders' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Pending payments processed successfully',
        ]);
    }

    function publishSettings()
    {
        $this->publishSettingsService->execute();
        $this->jsonResponseService->success([
            'message' => 'Settings published successfully',
        ]);
    }
}
