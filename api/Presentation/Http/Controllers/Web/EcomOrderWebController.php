<?php

namespace Presentation\Http\Controllers\Web;

use EcomOrder\Business\Dtos\AssignToAgentDto;
use EcomOrder\Business\Dtos\UpdateDeliveryStatusDto;
use EcomOrder\Business\Usecases\AnnotateOrdersWithItemsService;
use EcomOrder\Business\Usecases\AssignToAgentService;
use EcomOrder\Business\Usecases\FetchForAdminService;
use EcomOrder\Business\Usecases\FetchForUserService;
use EcomOrder\Business\Usecases\FindByIdService;
use EcomOrder\Business\Usecases\UpdateDeliveryStatusService;
use OrderItem\Business\Usecases\FetchForOrderService;
use OrderItem\Business\Usecases\SettleService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\FetchUsersAsAdminService;
use User\Business\Usecases\GetWalletBalanceService;

class EcomOrderWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private FetchForUserService $fetchForUserService;
    private FetchForAdminService $fetchForAdminService;
    private FindByIdService $findByIdService;
    private AnnotateOrdersWithItemsService $annotateOrdersWithItemsService;
    private FetchForOrderService $fetchForOrderService;
    private AssignToAgentService $assignToAgentService;
    private UpdateDeliveryStatusService $updateDeliveryStatusService;
    private SettleService $settleService;
    private FetchUsersAsAdminService $fetchUsersAsAdminService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        FetchForUserService $fetchForUserService,
        FetchForAdminService $fetchForAdminService,
        FindByIdService $findByIdService,
        AnnotateOrdersWithItemsService $annotateOrdersWithItemsService,
        FetchForOrderService $fetchForOrderService,
        AssignToAgentService $assignToAgentService,
        UpdateDeliveryStatusService $updateDeliveryStatusService,
        SettleService $settleService,
        FetchUsersAsAdminService $fetchUsersAsAdminService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->fetchForUserService = $fetchForUserService;
        $this->fetchForAdminService = $fetchForAdminService;
        $this->findByIdService = $findByIdService;
        $this->annotateOrdersWithItemsService = $annotateOrdersWithItemsService;
        $this->fetchForOrderService = $fetchForOrderService;
        $this->assignToAgentService = $assignToAgentService;
        $this->updateDeliveryStatusService = $updateDeliveryStatusService;
        $this->settleService = $settleService;
        $this->fetchUsersAsAdminService = $fetchUsersAsAdminService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    private function userLayout($view, $data)
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render($view, array_merge([
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
        ], $data));
    }

    private function adminLayout($view, $data)
    {
        $this->userLayout($view, array_merge(['layout_nav' => 'admin'], $data));
    }

    private function agentsList()
    {
        $agents = [];
        $users = $this->fetchUsersAsAdminService->query([])->fetchAll();
        if (is_array($users)) {
            foreach ($users as $u) {
                if ($u->role === 'agent') {
                    $agents[] = $u;
                }
            }
        }
        return $agents;
    }

    private function loadOrder($orderId, $annotate = true)
    {
        $order = $this->findByIdService->query($orderId);
        if ($annotate) {
            $orders = $this->annotateOrdersWithItemsService->execute([$order]);
            $order = $orders[0];
        }
        return $order;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orders = $this->fetchForUserService->query((int) $user->id, [])->fetchAll();
        if (!is_array($orders)) {
            $orders = [];
        }
        $orders = $this->annotateOrdersWithItemsService->execute($orders);
        $this->userLayout('ecom-orders/index', [
            'title' => 'Store orders',
            'subtitle' => 'E-commerce purchases',
            'nav' => 'ecom-orders',
            'orders' => $orders,
        ]);
    }

    public function show()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orderId = (int) $this->request->get('order_id');
        try {
            $order = $this->loadOrder($orderId);
            if ((int) $order->user_id !== (int) $user->id && !$user->isAdmin()) {
                throw new \Exception('Order not found');
            }
            $this->userLayout('ecom-orders/show', [
                'title' => 'Order #' . $order->id,
                'subtitle' => $order->reference,
                'nav' => 'ecom-orders',
                'order' => $order,
                'isAdmin' => $user->isAdmin(),
                'agents' => $user->isAdmin() ? $this->agentsList() : [],
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/ecom-orders');
        }
    }

    public function adminIndex()
    {
        $filters = [
            'payment_status' => $this->request->get('payment_status', ''),
            'delivery_status' => $this->request->get('delivery_status', ''),
        ];
        $orders = $this->fetchForAdminService->query($filters)->fetchAll();
        if (!is_array($orders)) {
            $orders = [];
        }
        $orders = $this->annotateOrdersWithItemsService->execute($orders);
        $this->adminLayout('admin/ecom-orders', [
            'title' => 'Ecom orders',
            'subtitle' => 'Store checkout orders',
            'nav' => 'admin-ecom-orders',
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    public function adminShow()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $order = $this->loadOrder($orderId);
            $items = $this->fetchForOrderService->query($orderId)->fetchAll();
            if (!is_array($items)) {
                $items = [];
            }
            $this->adminLayout('admin/ecom-order-show', [
                'title' => 'Ecom order #' . $order->id,
                'subtitle' => $order->reference,
                'nav' => 'admin-ecom-orders',
                'order' => $order,
                'items' => $items,
                'agents' => $this->agentsList(),
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/admin/ecom-orders');
        }
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
        WebSession::redirect('/admin/ecom-orders/' . $orderId);
    }

    public function updateDelivery()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->updateDeliveryStatusService->execute(new UpdateDeliveryStatusDto(
                $orderId,
                (string) $this->request->get('delivery_status')
            ));
            WebSession::flash('success', 'Delivery status updated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/ecom-orders/' . $orderId);
    }

    public function settleItem()
    {
        $orderId = (int) $this->request->get('order_id');
        try {
            $this->settleService->execute((int) $this->request->get('order_item_id'));
            WebSession::flash('success', 'Order item settled.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/ecom-orders/' . $orderId);
    }


}
