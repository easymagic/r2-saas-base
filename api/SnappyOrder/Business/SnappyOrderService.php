<?php

namespace SnappyOrder\Business;

use Exception;
use PlatformConfig\Business\PlatformConfigServiceInterface;
use ProxyOrderChangeLog\Business\ProxyOrderChangeLogServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
use SnappyOrder\Data\SnappyOrderEntity;
use SnappyOrder\Data\SnappyOrderMigrationRepositoryInterface;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Business\UserServiceInterface;
use User\Data\UserRepositoryInterface;
use Wallet\Business\WalletServiceInterface;

/**
 * @extends AbstractBaseService<SnappyOrderEntity, SnappyOrderRepositoryInterface>
 */
class SnappyOrderService extends AbstractBaseService implements SnappyOrderServiceInterface
{
    private SnappyOrderMigrationRepositoryInterface $snappyOrderMigrationRepositoryInterface;
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private SnappyOrderMailServiceInterface $snappyOrderMailService;
    private FileUploadServiceInterface $fileUploadService;
    private UserRepositoryInterface $userRepository;
    private PlatformConfigServiceInterface $platformConfigService;
    private UserServiceInterface $userService;
    private WalletServiceInterface $walletService;

    private ProxyOrderChangeLogServiceInterface $proxyOrderChangeLogService;

    public function __construct(
        SnappyOrderMigrationRepositoryInterface $snappyOrderMigrationRepositoryInterface,
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        SnappyOrderMailServiceInterface $snappyOrderMailService,
        FileUploadServiceInterface $fileUploadService,
        UserRepositoryInterface $userRepository,
        PlatformConfigServiceInterface $platformConfigService,
        UserServiceInterface $userService,
        WalletServiceInterface $walletService,
        ProxyOrderChangeLogServiceInterface $proxyOrderChangeLogService
    ) {
        parent::__construct($snappyOrderRepository);
        $this->snappyOrderMigrationRepositoryInterface = $snappyOrderMigrationRepositoryInterface;
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->snappyOrderMailService = $snappyOrderMailService;
        $this->fileUploadService = $fileUploadService;
        $this->userRepository = $userRepository;
        $this->platformConfigService = $platformConfigService;
        $this->userService = $userService;
        $this->walletService = $walletService;
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
    }

    public function migrate()
    {
        return $this->snappyOrderMigrationRepositoryInterface->migrate();
    }

    /**
     * @param int $user_id
     * @param string $link
     * @param string $description
     * @param array $screen_shot1
     * @param array $screen_shot2
     * @param array $screen_shot3
     * @param float $total_amount_usd
     * @return SnappyOrderEntity
     */
    public function create(
        int $user_id,
        string $link,
        string $description,
        array $screen_shot1,
        array $screen_shot2,
        array $screen_shot3,
        float $total_amount_usd
    ) {
        if (empty($user_id)) {
            throw new Exception('User ID is required');
        }
        if (empty($link)) {
            throw new Exception('Link is required');
        }
        if (empty($description)) {
            throw new Exception('Description is required');
        }
        if (empty($total_amount_usd)) {
            throw new Exception('Total amount is required');
        }
        if (empty($screen_shot1)) {
            throw new Exception('Screen shot 1 is required');
        }

        $user = $this->userRepository->find($user_id);
        if ($user->isEmpty()) {
            throw new Exception('User not found');
        }

        $path = '/uploads/snappy_orders';
        $full_path = __DIR__ . '/../../';

        $screen_shot1_path = $this->fileUploadService->uploadFile($screen_shot1, $path, $full_path);
        $screen_shot2_path = $this->fileUploadService->uploadFile($screen_shot2, $path, $full_path);
        $screen_shot3_path = $this->fileUploadService->uploadFile($screen_shot3, $path, $full_path);

        if (!$screen_shot1_path) {
            throw new Exception('Failed to upload screen shot 1');
        }

        $order = $this->snappyOrderRepository->save(0, [
            'user_id' => $user->id,
            'type' => 'snappy',
            'reference' => uniqid('SNAPPY_'),
            'link' => $link,
            'description' => $description,
            'total_amount_usd' => $total_amount_usd,
            'screen_shot1' => $screen_shot1_path,
            'screen_shot2' => $screen_shot2_path,
            'screen_shot3' => $screen_shot3_path,
            'status' => 'pending',
            'service_charge_usd' => $this->getServiceCharge(),
            'shipping_cost_usd' => $this->getShippingCost(),
            'dollar_to_naira_rate' => $this->getDollarToNairaRate(),
            'grand_total_naira' => $this->getTotalAmountNaira($total_amount_usd),
            'price_adjustment_sent' => 1,
        ]);

        $this->snappyOrderMailService->notifyCustomerOfOrderCreation($order->id);
        $this->snappyOrderMailService->notifyAdminOfOrderCreation($order->id);

        return $order;
    }

    /**
     * @param int $order_id
     * @param string $status
     * @return SnappyOrderEntity
     */
    public function changeStatus(int $order_id, string $status)
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }
        if (empty($status)) {
            throw new Exception('Status is required');
        }
        if (!in_array($status, SnappyOrderRepositoryInterface::ALLOWED_STATUSES, true)) {
            throw new Exception('Invalid status');
        }

        $order = $this->snappyOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            throw new Exception('Order not found');
        }

        if ($status === 'pending' && $order->status !== 'pending') {
            throw new Exception('Status cannot be changed back to pending');
        }

        if ($status === 'cancelled' && $order->status !== 'pending') {
            throw new Exception('Can only cancel pending orders');
        }

        if ($status === 'completed') {
            $otp_code = rand(100000, 999999);
            $order = $this->snappyOrderRepository->save($order_id, [
                'status' => $status,
                'pickup_otp_code' => $otp_code,
            ]);
            $this->snappyOrderMailService->notifyCustomerOfPickupOTP($order->id, (string) $otp_code);
        } else {
            $order = $this->snappyOrderRepository->save($order_id, [
                'status' => $status,
            ]);
        }

        $this->proxyOrderChangeLogService->log($order->id, 'status', $order->status, $status);

        $this->snappyOrderMailService->notifyCustomerOfStatusChange($order->id, $status);

        return $order;
    }

    /**
     * @param int $order_id
     * @param int $agent_id
     * @return SnappyOrderEntity
     */
    public function assignToAgent(int $order_id, int $agent_id)
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }
        if (empty($agent_id)) {
            throw new Exception('Agent ID is required');
        }

        $agent = $this->userRepository->find($agent_id);
        if ($agent->isEmpty()) {
            throw new Exception('Agent not found');
        }
        if ($agent->role !== 'agent') {
            throw new Exception($agent->name . ' is not an agent');
        }

        $order = $this->snappyOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            throw new Exception('Order not found');
        }

        $order = $this->snappyOrderRepository->save($order->id, [
            'agent_id' => $agent_id,
            'status' => 'assigned',
        ]);

        $this->proxyOrderChangeLogService->log($order->id, 'agent_id', $order->agent_id, $agent_id);

        $this->snappyOrderMailService->notifyAgenOfOrderAssignment($order->id, $agent_id);
        $this->snappyOrderMailService->notifyCustomerOfAgentAssignment($order->id, $agent_id);

        return $order;
    }

    /**
     * @param int $agent_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrdersAsAgent(int $agent_id, array $filters = [])
    {
        $agent = $this->userRepository->find($agent_id);
        if ($agent->isEmpty()) {
            throw new Exception('Agent not found');
        }
        if ($agent->role !== 'agent') {
            throw new Exception('User is not an agent');
        }

        $filters['agent_id'] = $agent_id;
        return $this->snappyOrderRepository->query($filters);
    }

    /**
     * @param int $customer_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrdersAsCustomer(int $customer_id, array $filters = [])
    {
        $customer = $this->userRepository->find($customer_id);
        if ($customer->isEmpty()) {
            throw new Exception('Customer not found');
        }

        $filters['user_id'] = $customer_id;
        return $this->snappyOrderRepository->query($filters);
    }

    /**
     * @param int $admin_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrderAsAdmin(int $admin_id, array $filters = [])
    {
        $admin = $this->userRepository->find($admin_id);
        if ($admin->isEmpty()) {
            throw new Exception('Admin not found');
        }
        if (!$admin->isAdmin()) {
            throw new Exception('User is not an admin');
        }

        return $this->snappyOrderRepository->query($filters);
    }

    /**
     * Publish the settings to the database
     * @return void
     */
    public function publishSettings()
    {
        $this->platformConfigService->set('SERVICE_CHARGE', $this->getServiceCharge());
        $this->platformConfigService->set('SHIPPING_COST', $this->getShippingCost());
        $this->platformConfigService->set('DOLLAR_TO_NAIRA_RATE', $this->getDollarToNairaRate());
    }

    /**
     * @param int $order_id
     * @param float $price
     * @return SnappyOrderEntity
     */
    public function changePrice(int $order_id, float $price)
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }
        if (empty($price)) {
            throw new Exception('Price is required');
        }

        $order = $this->snappyOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            throw new Exception('Order not found');
        }
        if ($order->status !== 'pending') {
            throw new Exception('Price can only be changed when order status is pending');
        }

        $order = $this->snappyOrderRepository->save($order->id, [
            'total_amount_usd' => $price,
            'service_charge_usd' => $this->getServiceCharge(),
            'shipping_cost_usd' => $this->getShippingCost(),
            'dollar_to_naira_rate' => $this->getDollarToNairaRate(),
            'grand_total_naira' => $this->getTotalAmountNaira($price),
            'price_adjustment_sent' => 1,
        ]);

        $this->proxyOrderChangeLogService->log($order->id, 'total_amount_usd', $order->total_amount_usd, $price);

        $this->snappyOrderMailService->notifyCustomerOfPriceChange($order->id, $price);

        return $order;
    }

    /**
     * @param int $order_id
     * @param int $user_id
     * @return SnappyOrderEntity
     */
    public function payOrderFromWallet(int $order_id, int $user_id)
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }
        if (empty($user_id)) {
            throw new Exception('User ID is required');
        }

        $order = $this->snappyOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            throw new Exception('Order not found');
        }
        if ((int) $order->price_adjustment_sent !== 1) {
            throw new Exception('Price adjustment not sent');
        }
        if ((int) $order->user_id !== $user_id) {
            throw new Exception('You are not authorized to pay for this order');
        }
        if ($order->status !== 'pending') {
            throw new Exception('Order can only be paid when status is pending');
        }

        $amount = (float) $order->grand_total_naira;
        $this->userService->withdrawWallet($user_id, $amount);
        $this->walletService->log(
            $user_id,
            $amount,
            uniqid('WALLET_WITHDRAWAL_'),
            'withdrawal',
            'Withdrawal from wallet for snappy order #' . $order->id,
            'approved'
        );

        $order = $this->snappyOrderRepository->save($order->id, [
            'status' => 'paid',
        ]);

        $this->snappyOrderMailService->notifyCustomerOfOrderPayment($order->id);

        return $order;
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

    private function getTotalAmountNaira(float $amount = 0)
    {
        return ($amount + $this->getServiceCharge() + $this->getShippingCost()) * $this->getDollarToNairaRate();
    }
}
