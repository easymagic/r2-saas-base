<?php

namespace Application\ProxyOrder;

use Application\MailNotifications\ProxyOrderMailNotificationInterface;
use Application\PlatformConfig\PlatformConfigServiceInterface;
use Application\ProxyOrder\ProxyOrderServiceInterface;
use Application\User\UserServiceInterface;
use Application\Wallet\WalletServiceInterface;
use Domain\ProxyOrder\Interfaces\ProxyOrderRepositoryInterface;
use Domain\ProxyOrder\ProxyOrderEntity;
use Domain\User\UserRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;

class ProxyOrderService implements ProxyOrderServiceInterface
{

    private ProxyOrderRepositoryInterface $proxyOrderRepository;

    private ProxyOrderMailNotificationInterface $proxyOrderMailNotification;

    private FileUploadServiceInterface $fileUploadService;

    private UserRepositoryInterface $userRepository;

    private PlatformConfigServiceInterface $platformConfigService;

    private ProxyOrderMigrationServiceInterface $proxyOrderMigrationService;

    private UserServiceInterface $userService;

    private WalletServiceInterface $walletService;

    const ALLOWED_STATUSES = [
        'pending',
        'paid',
        'placed',
        'shipped-to-facility',
        'arrived-at-facility',
        'shipped-to-destination-country',
        'arrived-at-destination-country',
        'arrived-at-destination-facility',
        'ready-for-pickup',
        'delivered',
        'cancelled'
    ];
    const PAID_STATUSES = [
        'paid',
        'placed',
        'shipped-to-facility',
        'arrived-at-facility',
        'shipped-to-destination-country',
        'arrived-at-destination-country',
        'arrived-at-destination-facility',
        'ready-for-pickup',
        'delivered'
    ];
    const ALLOWED_TYPES = ['online', 'physical'];

    public function __construct(
        ProxyOrderRepositoryInterface $proxyOrderRepository,
        ProxyOrderMailNotificationInterface $proxyOrderMailNotification,
        FileUploadServiceInterface $fileUploadService,
        UserRepositoryInterface $userRepository,
        PlatformConfigServiceInterface $platformConfigService,
        ProxyOrderMigrationServiceInterface $proxyOrderMigrationService,
        UserServiceInterface $userService,
        WalletServiceInterface $walletService
    ) {
        $this->proxyOrderRepository = $proxyOrderRepository;
        $this->proxyOrderMailNotification = $proxyOrderMailNotification;
        $this->fileUploadService = $fileUploadService;
        $this->userRepository = $userRepository;
        $this->platformConfigService = $platformConfigService;
        $this->proxyOrderMigrationService = $proxyOrderMigrationService;
        $this->userService = $userService;
        $this->walletService = $walletService;
    }

    public function create(
        int $userId,
        string $type,
        string $reference,
        string $link,
        string $description,
        float $total_amount_usd,
        array $screen_shot1,
        mixed $screen_shot2 = [],
        mixed $screen_shot3 = [],
        string $status = 'pending'
    ) {
        if (empty($userId)) {
            throw new \Exception('User ID is required');
        }
        if (empty($type)) {
            throw new \Exception('Type is required');
        }
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new \Exception('Invalid type');
        }
        if (empty($reference)) {
            throw new \Exception('Reference is required');
        }
        if (empty($link)) {
            throw new \Exception('Link is required');
        }
        if (empty($description)) {
            throw new \Exception('Description is required');
        }
        if (empty($total_amount_usd)) {
            throw new \Exception('Total amount is required');
        }
        if (empty($screen_shot1)) {
            throw new \Exception('Screen shot 1 is required');
        }
        $user = $this->userRepository->find($userId);

        $path = '/uploads/proxy_orders';
        $full_path = __DIR__ . '/../../';

        $screen_shot1 = $this->fileUploadService->uploadFile($screen_shot1, $path, $full_path);
        $screen_shot2 = $this->fileUploadService->uploadFile($screen_shot2, $path, $full_path);
        $screen_shot3 = $this->fileUploadService->uploadFile($screen_shot3, $path, $full_path);

        if (!$screen_shot1) {
            throw new \Exception('Failed to upload screen shot 1');
        }

        $order = $this->proxyOrderRepository->save(0, [
            'user_id' => $user->id,
            'type' => $type,
            'reference' => $reference,
            'link' => $link,
            'description' => $description,
            'total_amount_usd' => $total_amount_usd,
            'screen_shot1' => $screen_shot1,
            'screen_shot2' => $screen_shot2,
            'screen_shot3' => $screen_shot3,
            'status' => $status,
            'service_charge_usd' => $this->getServiceCharge(),
            'shipping_cost_usd' => $this->getShippingCost(),
            'dollar_to_naira_rate' => $this->getDollarToNairaRate(),
            'total_amount_naira' => $this->getTotalAmountNaira($total_amount_usd)
        ]);

        $this->proxyOrderMailNotification->sendCustomerOrderCreatedNotification($order->id);
        $this->proxyOrderMailNotification->sendAdminOrderCreatedNotification($order->id);
        return $order;
    }

    /**
     * @param int $id
     * @return ProxyOrderEntity
     */
    public function find(int $id)
    {
        return $this->proxyOrderRepository->find($id);
    }


    /**
     * @param int $id
     * @param string $status
     * @return ProxyOrderEntity
     */
    public function updateStatus(int $id, string $status)
    {
        if (empty($id)) {
            throw new \Exception('ID is required');
        }
        if (empty($status)) {
            throw new \Exception('Status is required');
        }
        if (!in_array($status, self::ALLOWED_STATUSES)) {
            throw new \Exception('Invalid status');
        }

        $order = $this->proxyOrderRepository->find($id);

        $positionIncomingStatus = array_search($status, self::ALLOWED_STATUSES);
        $positionCurrentStatus = array_search($order->status, self::ALLOWED_STATUSES);
        if ($positionIncomingStatus < $positionCurrentStatus) {
            throw new \Exception('Invalid status!');
        }

        if ($status == 'ready-for-pickup') {
            $otp_code = rand(100000, 999999);
            $this->proxyOrderRepository->save($id, [
                'status' => $status,
                'pickup_otp_code' => $otp_code
            ]);
            $this->proxyOrderMailNotification->sendCustomerOrderReadyForPickupNotification($id);
        } else {
            $this->proxyOrderRepository->save($id, [
                'status' => $status
            ]);
        }

        $this->proxyOrderMailNotification->sendCustomerOrderStatusChangedNotification($id);
        return $this->proxyOrderRepository->find($id);
    }


    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        if (empty($id)) {
            throw new \Exception('ID is required');
        }
        $order = $this->proxyOrderRepository->find($id);
        $this->proxyOrderRepository->delete($order->id);
        return true;
    }

    /**
     * @param int $id
     * @param float $price
     * @return ProxyOrderEntity
     */
    function adjustPrice(int $id, float $price)
    {
        if (empty($id)) {
            throw new \Exception('ID is required');
        }
        if (empty($price)) {
            throw new \Exception('Price is required');
        }
        $order = $this->proxyOrderRepository->find($id);
        if ($order->status != 'pending') {
            throw new \Exception('Order is not pending');
        }

        $this->proxyOrderRepository->save($order->id, [
            'total_amount_usd' => $price,
            'service_charge_usd' => $this->getServiceCharge(),
            'shipping_cost_usd' => $this->getShippingCost(),
            'dollar_to_naira_rate' => $this->getDollarToNairaRate(),
            'total_amount_naira' => $this->getTotalAmountNaira($price),
            'price_adjustment_sent' => 1
        ]);
        $this->proxyOrderMailNotification->sendCustomerPriceAdjustedNotification($id);
        return $this->proxyOrderRepository->find($id);
    }

    private function getServiceCharge()
    {
        return $this->platformConfigService->get('SERVICE_CHARGE', 100);
    }

    private function getShippingCost()
    {
        return $this->platformConfigService->get('SHIPPING_COST', 100);
    }

    private function getDollarToNairaRate()
    {
        return $this->platformConfigService->get('DOLLAR_TO_NAIRA_RATE', 10);
    }

    private function getTotalAmountNaira(float $newAmount = 0)
    {
        return ($newAmount + $this->getServiceCharge() + $this->getShippingCost()) * $this->getDollarToNairaRate();
    }
    /**
     * @param int $id
     * @param int $batchId
     * @return ProxyOrderEntity
     */
    function assignToBatch(int $id, int $batchId)
    {
        if (empty($id)) {
            throw new \Exception('ID is required');
        }
        if (empty($batchId)) {
            throw new \Exception('Batch ID is required');
        }
        $order = $this->proxyOrderRepository->find($id);
        $order = $this->proxyOrderRepository->save($order->id, [
            'batch_id' => $batchId
        ]);
        return $order;
    }

    /**
     * @param int $id
     * @param int $agentId
     * @return ProxyOrderEntity
     */
    function assignToAgent(int $id, int $agentId)
    {
        if (empty($id)) {
            throw new \Exception('ID is required');
        }
        if (empty($agentId)) {
            throw new \Exception('Agent ID is required');
        }
        $agent = $this->userRepository->find($agentId);
        if ($agent->role !== 'agent') {
            throw new \Exception($agent->name . ' is not an agent');
        }
        $order = $this->proxyOrderRepository->find($id);
        $order = $this->proxyOrderRepository->save($order->id, [
            'agent_id' => $agentId
        ]);
        $this->proxyOrderMailNotification->sendAgentOrderAssignedNotification($id);
        $this->proxyOrderMailNotification->notifyAgentOfNewOrder($id);
        return $order;
    }

    /**
     * Publish the settings to the platform config
     * @return void
     */
    function publishSettings()
    {
        $this->platformConfigService->set('SERVICE_CHARGE', $this->getServiceCharge());
        $this->platformConfigService->set('SHIPPING_COST', $this->getShippingCost());
        $this->platformConfigService->set('DOLLAR_TO_NAIRA_RATE', $this->getDollarToNairaRate());
    }

    /**
     * Get the dashboard stats
     * @return array
     */
    function dashboardStats()
    {
        $pendingOrdersCount = $this->proxyOrderRepository->filterByPending()->count();
        $paidOrdersCount = $this->proxyOrderRepository->filterByPaid()->count();
        $placedOrdersCount = $this->proxyOrderRepository->filter(['status' => 'placed'])->count();
        $paidOrdersSum = $this->proxyOrderRepository->filterByPaid()->sum('total_amount_naira');

        return [
            'pending_orders_count' => $pendingOrdersCount,
            'paid_orders_count' => $paidOrdersCount,
            'placed_orders_count' => $placedOrdersCount,
            'paid_orders_sum' => $paidOrdersSum
        ];
    }

    /**
     * Get the dashboard stats for a specific user
     * @param int $userId
     * @return array
     */
    function myDashboardStats(int $userId)
    {
        $pendingOrdersCount = $this->proxyOrderRepository->filterByUserId($userId)->filterByPending()->count();
        $deliveredOrdersCount = $this->proxyOrderRepository->filterByUserId($userId)->filter([
            "status" => "delivered"
        ])->count();
        $cancelledOrdersCount = $this->proxyOrderRepository->filterByUserId($userId)->filter([
            "status" => "cancelled"
        ])->count();
        return [
            "pending_orders_count" => $pendingOrdersCount,
            "delivered_orders_count" => $deliveredOrdersCount,
            "cancelled_orders_count" => $cancelledOrdersCount
        ];
    }

    /**
     * Migrate the proxy order data
     * @return void
     */
    function migrate()
    {
        $this->proxyOrderMigrationService->migrate();
    }

    /**
     * Pay from wallet
     * @param int $proxyOrderId
     * @param int $userId
     * @return ProxyOrderEntity
     */
    function payFromWallet(int $proxyOrderId, int $userId)
    {
        $order =$this->proxyOrderRepository->find($proxyOrderId);
        if ((int) $order->price_adjustment_sent !== 1){
            throw new \Exception('Price adjustment not sent');
        }
        if ($order->user_id !== $userId){
            throw new \Exception('You are not authorized to pay for this order');
        }
        $amount = $order->grand_total_naira;
        $this->userService->withdrawWallet($userId, $amount);
        $this->walletService->log(
            $userId,
            $amount,
            uniqid("WALLET_WITHDRAWAL_"),
            'withdrawal',
            'Withdrawal from wallet',
            'approved'
        );

        $order = $this->proxyOrderRepository->save($order->id, [
            'status' => 'paid'
        ]);
        $this->proxyOrderMailNotification->sendCustomerOrderPaidNotification($order->id);
        return $order;
    }

    /**
     * Approve payment
     * @param int $proxyOrderId
     * @return ProxyOrderEntity
     */
    function approvePayment(int $proxyOrderId)
    {
        $order = $this->proxyOrderRepository->find($proxyOrderId);
        if ((int) $order->approve_payment !== 0){
            throw new \Exception('Payment already approved');
        }
        $order = $this->proxyOrderRepository->save($order->id, [
            'approve_payment' => 1
        ]);
        $this->proxyOrderMailNotification->sendCustomerOrderPaymentApprovedNotification($order->id);
        return $order;
    }
}
