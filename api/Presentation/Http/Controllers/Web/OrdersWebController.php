<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
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
use SnappyOrder\Business\Usecases\PayOrderFromWalletService;
use SnappyOrder\Business\Usecases\PublishSettingsService;
use SnappyOrder\Business\Usecases\UnassignFromBatchService;
use Batch\Business\Usecases\GetBatchListService;
use User\Business\Usecases\FetchUsersAsAdminService;
use Thread\Business\Dtos\CreateThreadDto;
use Thread\Business\Usecases\CreateThreadService;
use Thread\Business\Usecases\GetThreadListForOrderService;
use User\Business\Usecases\GetWalletBalanceService;

class OrdersWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private GetMyOrdersAsCustomerService $getMyOrdersAsCustomerService;
    private GetMyOrdersAsAgentService $getMyOrdersAsAgentService;
    private GetMyOrderAsAdminService $getMyOrderAsAdminService;
    private GetByIdService $getByIdService;
    private CreateService $createService;
    private ChangeStatusService $changeStatusService;
    private PayOrderFromWalletService $payOrderFromWalletService;
    private GetThreadListForOrderService $getThreadListForOrderService;
    private CreateThreadService $createThreadService;
    private GetWalletBalanceService $getWalletBalanceService;
    private ChangePriceService $changePriceService;
    private AssignToAgentService $assignToAgentService;
    private AssignToBatchService $assignToBatchService;
    private UnassignFromBatchService $unassignFromBatchService;
    private PublishSettingsService $publishSettingsService;
    private FetchUsersAsAdminService $fetchUsersAsAdminService;
    private GetBatchListService $getBatchListService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        GetMyOrdersAsCustomerService $getMyOrdersAsCustomerService,
        GetMyOrdersAsAgentService $getMyOrdersAsAgentService,
        GetMyOrderAsAdminService $getMyOrderAsAdminService,
        GetByIdService $getByIdService,
        CreateService $createService,
        ChangeStatusService $changeStatusService,
        PayOrderFromWalletService $payOrderFromWalletService,
        GetThreadListForOrderService $getThreadListForOrderService,
        CreateThreadService $createThreadService,
        GetWalletBalanceService $getWalletBalanceService,
        ChangePriceService $changePriceService,
        AssignToAgentService $assignToAgentService,
        AssignToBatchService $assignToBatchService,
        UnassignFromBatchService $unassignFromBatchService,
        PublishSettingsService $publishSettingsService,
        FetchUsersAsAdminService $fetchUsersAsAdminService,
        GetBatchListService $getBatchListService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->getMyOrdersAsCustomerService = $getMyOrdersAsCustomerService;
        $this->getMyOrdersAsAgentService = $getMyOrdersAsAgentService;
        $this->getMyOrderAsAdminService = $getMyOrderAsAdminService;
        $this->getByIdService = $getByIdService;
        $this->createService = $createService;
        $this->changeStatusService = $changeStatusService;
        $this->payOrderFromWalletService = $payOrderFromWalletService;
        $this->getThreadListForOrderService = $getThreadListForOrderService;
        $this->createThreadService = $createThreadService;
        $this->getWalletBalanceService = $getWalletBalanceService;
        $this->changePriceService = $changePriceService;
        $this->assignToAgentService = $assignToAgentService;
        $this->assignToBatchService = $assignToBatchService;
        $this->unassignFromBatchService = $unassignFromBatchService;
        $this->publishSettingsService = $publishSettingsService;
        $this->fetchUsersAsAdminService = $fetchUsersAsAdminService;
        $this->getBatchListService = $getBatchListService;
    }

    private function listOrders()
    {
        $user = $this->apiCredentialService->getAuthUser();
        if ($user->isAdmin()) {
            return $this->getMyOrderAsAdminService->query((int) $user->id, [])->fetchAll();
        }
        if (strpos($user->role, 'agent') !== false) {
            return $this->getMyOrdersAsAgentService->query((int) $user->id, [])->fetchAll();
        }
        return $this->getMyOrdersAsCustomerService->query((int) $user->id, [])->fetchAll();
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orders = $this->listOrders();
        if (!is_array($orders)) {
            $orders = [];
        }
        View::render('orders/index', [
            'title' => 'Orders',
            'subtitle' => 'Track fulfillment status',
            'nav' => 'orders',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'orders' => $orders,
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function createForm()
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render('orders/create', [
            'title' => 'Create order',
            'subtitle' => 'Submit a product request',
            'nav' => 'create-order',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
            'old' => [],
        ]);
    }

    public function store()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $old = [
            'link' => trim((string) $this->request->get('link', '')),
            'description' => trim((string) $this->request->get('description', '')),
            'total_amount_usd' => trim((string) $this->request->get('total_amount_usd', '')),
        ];
        try {
            $order = $this->createService->execute(new CreateDto(
                (int) $user->id,
                $old['link'],
                $old['description'],
                (array) $this->request->get('screen_shot1', []),
                (array) $this->request->get('screen_shot2', []),
                (array) $this->request->get('screen_shot3', []),
                (float) $old['total_amount_usd']
            ));
            WebSession::flash('success', 'Order #' . $order->id . ' created.');
            WebSession::redirect('/orders/' . $order->id);
        } catch (\Exception $e) {
            View::render('orders/create', [
                'title' => 'Create order',
                'subtitle' => 'Submit a product request',
                'nav' => 'create-order',
                'user' => $user,
                'balance' => $this->getWalletBalanceService->query((int) $user->id),
                'flash' => ['type' => 'error', 'message' => $e->getMessage()],
                'old' => $old,
            ]);
        }
    }

    public function show()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orderId = (int) $this->request->get('order_id');
        try {
            $order = $this->getByIdService->query($orderId);
            $threads = $this->getThreadListForOrderService->query($orderId)->fetchAll();
            if (!is_array($threads)) {
                $threads = [];
            }
            $agents = [];
            $batches = [];
            if ($user->isAdmin()) {
                $allUsers = $this->fetchUsersAsAdminService->query([])->fetchAll();
                if (is_array($allUsers)) {
                    foreach ($allUsers as $u) {
                        if ($u->role === 'agent') {
                            $agents[] = $u;
                        }
                    }
                }
                $batches = $this->getBatchListService->query([])->fetchAll();
                if (!is_array($batches)) {
                    $batches = [];
                }
            }
            View::render('orders/show', [
                'title' => 'Order #' . $order->id,
                'subtitle' => $order->reference,
                'nav' => 'orders',
                'user' => $user,
                'balance' => $this->getWalletBalanceService->query((int) $user->id),
                'order' => $order,
                'threads' => $threads,
                'agents' => $agents,
                'batches' => $batches,
                'flash' => WebSession::pullFlash(),
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/orders');
        }
    }

    public function payFromWallet()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->payOrderFromWalletService->execute(new PayOrderFromWalletDto($orderId, (int) $user->id));
            WebSession::flash('success', 'Order paid from wallet.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/orders/' . $orderId);
    }

    public function changeStatus()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->changeStatusService->execute(new ChangeStatusDto(
                $orderId,
                (string) $this->request->get('status', ''),
                (string) $this->request->get('pickup_otp_code', '')
            ));
            WebSession::flash('success', 'Status updated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/orders/' . $orderId);
    }

    public function postThread()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->createThreadService->execute(new CreateThreadDto(
                $orderId,
                (int) $user->id,
                (string) $this->request->get('message', ''),
                (array) $this->request->get('attachment_url', [])
            ));
            WebSession::flash('success', 'Message sent.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/orders/' . $orderId);
    }

    public function changePrice()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->changePriceService->execute(new ChangePriceDto(
                $orderId,
                (float) $this->request->get('price')
            ));
            WebSession::flash('success', 'Price updated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/orders/' . $orderId);
    }

    public function assignAgent()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->assignToAgentService->execute(new AssignToAgentDto(
                $orderId,
                (int) $this->request->get('agent_id')
            ));
            WebSession::flash('success', 'Agent assigned.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/orders/' . $orderId);
    }

    public function assignBatch()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->assignToBatchService->execute(new AssignToBatchDto(
                $orderId,
                (int) $this->request->get('batch_id')
            ));
            WebSession::flash('success', 'Batch assigned.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/orders/' . $orderId);
    }

    public function unassignBatch()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->unassignFromBatchService->execute($orderId);
            WebSession::flash('success', 'Batch unassigned.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/orders/' . $orderId);
    }

    public function publishSettings()
    {
        try {
            $this->publishSettingsService->execute();
            WebSession::flash('success', 'Order settings published.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin');
    }

}
