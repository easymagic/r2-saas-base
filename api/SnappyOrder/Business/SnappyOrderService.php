<?php

namespace SnappyOrder\Business;

use Batch\Data\BatchRepositoryInterface;
use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\PlatformConfigServiceInterface;
use ProxyOrderChangeLog\Business\Dtos\LogDto as ProxyOrderChangeLogDto;
use ProxyOrderChangeLog\Business\ProxyOrderChangeLogServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\AbstractBaseService;
use Shared\Contracts;
use Shared\Query\QueryObject;
use SnappyOrder\Business\Dtos\AssignToAgentDto;
use SnappyOrder\Business\Dtos\AssignToBatchDto;
use SnappyOrder\Business\Dtos\ChangePriceDto;
use SnappyOrder\Business\Dtos\ChangeStatusDto;
use SnappyOrder\Business\Dtos\CreateDto;
use SnappyOrder\Business\Dtos\PayOrderFromWalletDto;
use SnappyOrder\Data\SnappyOrderEntity;
use SnappyOrder\Data\SnappyOrderMigrationRepositoryInterface;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Business\Dtos\WithdrawWalletDto;
use User\Business\Usecases\WithdrawWalletService;
use User\Data\UserRepositoryInterface;
use Wallet\Business\Dtos\LogDto as WalletLogDto;
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
    private WithdrawWalletService $withdrawWalletService;
    private WalletServiceInterface $walletService;
    private ProxyOrderChangeLogServiceInterface $proxyOrderChangeLogService;
    private BatchRepositoryInterface $batchRepository;

    public function __construct(
        SnappyOrderMigrationRepositoryInterface $snappyOrderMigrationRepositoryInterface,
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        SnappyOrderMailServiceInterface $snappyOrderMailService,
        FileUploadServiceInterface $fileUploadService,
        UserRepositoryInterface $userRepository,
        PlatformConfigServiceInterface $platformConfigService,
        WithdrawWalletService $withdrawWalletService,
        WalletServiceInterface $walletService,
        ProxyOrderChangeLogServiceInterface $proxyOrderChangeLogService,
        BatchRepositoryInterface $batchRepository
    ) {
        parent::__construct($snappyOrderRepository);
        $this->snappyOrderMigrationRepositoryInterface = $snappyOrderMigrationRepositoryInterface;
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->snappyOrderMailService = $snappyOrderMailService;
        $this->fileUploadService = $fileUploadService;
        $this->userRepository = $userRepository;
        $this->platformConfigService = $platformConfigService;
        $this->withdrawWalletService = $withdrawWalletService;
        $this->walletService = $walletService;
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
        $this->batchRepository = $batchRepository;
    }

    public function migrate()
    {
        return $this->snappyOrderMigrationRepositoryInterface->migrate();
    }

    /**
     * @param CreateDto $createDto
     * @return SnappyOrderEntity
     */
    public function create(CreateDto $createDto)
    {
        $user = $this->userRepository->find($createDto->user_id);
        Contracts::requireEntityFound($user, 'user');

        $path = '/uploads/snappy_orders';
        $full_path = __DIR__ . '/../../';

        $screen_shot1_path = $this->fileUploadService->uploadFile($createDto->screen_shot1, $path, $full_path);
        $screen_shot2_path = $this->fileUploadService->uploadFile($createDto->screen_shot2, $path, $full_path);
        $screen_shot3_path = $this->fileUploadService->uploadFile($createDto->screen_shot3, $path, $full_path);

        $order = $this->snappyOrderRepository->save(new SnappyOrderEntity([
            'user_id' => $user->id,
            'type' => 'manual',
            'reference' => uniqid('MANUAL_'),
            'link' => $createDto->link,
            'description' => $createDto->description,
            'total_amount_usd' => (string) $createDto->total_amount_usd,
            'screen_shot1' => $screen_shot1_path ? $screen_shot1_path : '',
            'screen_shot2' => $screen_shot2_path ? $screen_shot2_path : '',
            'screen_shot3' => $screen_shot3_path ? $screen_shot3_path : '',
            'status' => 'pending',
            'service_charge_usd' => (float) $this->getServiceCharge(),
            'shipping_cost_usd' => (float) $this->getShippingCost(),
            'dollar_to_naira_rate' => (float) $this->getDollarToNairaRate(),
            'grand_total_naira' => (string) $this->getTotalAmountNaira($createDto->total_amount_usd),
            'price_adjustment_sent' => 0,
        ]));

        $this->snappyOrderMailService->notifyCustomerOfOrderCreation($order->id);
        $this->snappyOrderMailService->notifyAdminOfOrderCreation($order->id);

        return $order;
    }

    /**
     * @param ChangeStatusDto $changeStatusDto
     * @return SnappyOrderEntity
     */
    public function changeStatus(ChangeStatusDto $changeStatusDto)
    {
        $order = $this->snappyOrderRepository->find($changeStatusDto->order_id);
        Contracts::requireEntityFound($order, 'order');

        $previousStatus = $order->status;
        $status = $changeStatusDto->status;

        Contracts::requires($status !== 'pending', 'Status cannot be changed back to pending');
        Contracts::requires(
            $status !== 'cancelled' || $previousStatus === 'pending',
            'Can only cancel pending orders'
        );

        if ($status === 'ready-for-pickup') {
            $otp_code = rand(100000, 999999);
            $order->status = $status;
            $order->pickup_otp_code = (int) $otp_code;
            $order = $this->snappyOrderRepository->save($order);
            $this->snappyOrderMailService->notifyCustomerOfPickupOTP($order->id, (string) $otp_code);
        } else {
            if ($status === 'delivered') {
                Contracts::requiresNotNullOrEmpty($changeStatusDto->pickup_otp_code, 'Pickup OTP code');
                Contracts::requires(
                    $changeStatusDto->pickup_otp_code === (string) $order->pickup_otp_code,
                    'Invalid pickup OTP code'
                );
            }
            $order->status = $status;
            $order = $this->snappyOrderRepository->save($order);
        }

        $this->proxyOrderChangeLogService->log(new ProxyOrderChangeLogDto(
            $order->id,
            'status',
            (string) $previousStatus,
            (string) $status
        ));

        $this->snappyOrderMailService->notifyCustomerOfStatusChange($order->id, $status);

        return $order;
    }

    /**
     * @param AssignToAgentDto $assignToAgentDto
     * @return SnappyOrderEntity
     */
    public function assignToAgent(AssignToAgentDto $assignToAgentDto)
    {
        $agent = $this->userRepository->find($assignToAgentDto->agent_id);
        Contracts::requireEntityFound($agent, 'agent');
        Contracts::requires($agent->role === 'agent', $agent->name . ' is not an agent');

        $order = $this->snappyOrderRepository->find($assignToAgentDto->order_id);
        Contracts::requireEntityFound($order, 'order');

        $previousAgentId = $order->agent_id;
        $order->agent_id = $assignToAgentDto->agent_id;
        $order->status = 'placed';
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->log(new ProxyOrderChangeLogDto(
            $order->id,
            'agent_id',
            (string) $previousAgentId,
            (string) $assignToAgentDto->agent_id
        ));

        $this->snappyOrderMailService->notifyAgenOfOrderAssignment($order->id, $assignToAgentDto->agent_id);
        $this->snappyOrderMailService->notifyCustomerOfAgentAssignment($order->id, $assignToAgentDto->agent_id);

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
        Contracts::requireEntityFound($agent, 'Agent');
        Contracts::requires($agent->role === 'agent', 'User is not an agent');

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
        Contracts::requireEntityFound($customer, 'Customer');

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
        Contracts::requireEntityFound($admin, 'Admin');
        Contracts::requires($admin->isAdmin(), 'User is not an admin');

        return $this->snappyOrderRepository->query($filters);
    }

    /**
     * @return void
     */
    public function publishSettings()
    {
        $this->platformConfigService->set(new SetDto('SERVICE_CHARGE', (string) $this->getServiceCharge()));
        $this->platformConfigService->set(new SetDto('SHIPPING_COST', (string) $this->getShippingCost()));
        $this->platformConfigService->set(new SetDto('DOLLAR_TO_NAIRA_RATE', (string) $this->getDollarToNairaRate()));
    }

    /**
     * @param ChangePriceDto $changePriceDto
     * @return SnappyOrderEntity
     */
    public function changePrice(ChangePriceDto $changePriceDto)
    {
        $order = $this->snappyOrderRepository->find($changePriceDto->order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires($order->status === 'pending', 'Price can only be changed when order status is pending');

        $previousPrice = (string) $order->total_amount_usd;
        $order->total_amount_usd = (string) $changePriceDto->price;
        $order->service_charge_usd = (float) $this->getServiceCharge();
        $order->shipping_cost_usd = (float) $this->getShippingCost();
        $order->dollar_to_naira_rate = (float) $this->getDollarToNairaRate();
        $order->grand_total_naira = (string) $this->getTotalAmountNaira($changePriceDto->price);
        $order->price_adjustment_sent = 1;
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->log(new ProxyOrderChangeLogDto(
            $order->id,
            'total_amount_usd',
            $previousPrice,
            (string) $changePriceDto->price
        ));

        $this->snappyOrderMailService->notifyCustomerOfPriceChange($order->id, $changePriceDto->price);

        return $order;
    }

    /**
     * @param PayOrderFromWalletDto $payOrderFromWalletDto
     * @return SnappyOrderEntity
     */
    public function payOrderFromWallet(PayOrderFromWalletDto $payOrderFromWalletDto)
    {
        $order = $this->snappyOrderRepository->find($payOrderFromWalletDto->order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires((int) $order->price_adjustment_sent === 1, 'Price adjustment not sent');
        Contracts::requires(
            (int) $order->user_id === $payOrderFromWalletDto->user_id,
            'You are not authorized to pay for this order'
        );
        Contracts::requires($order->status === 'pending', 'Order can only be paid when status is pending');

        $amount = (float) $order->grand_total_naira;
        $this->withdrawWalletService->execute(new WithdrawWalletDto($payOrderFromWalletDto->user_id, $amount));
        $this->walletService->log(new WalletLogDto(
            $payOrderFromWalletDto->user_id,
            $amount,
            uniqid('WALLET_WITHDRAWAL_'),
            'withdrawal',
            'Withdrawal from wallet for snappy order #' . $order->id,
            'approved'
        ));

        $order->status = 'paid';
        $order = $this->snappyOrderRepository->save($order);

        $this->snappyOrderMailService->notifyCustomerOfOrderPayment($order->id);

        return $order;
    }

    /**
     * @param AssignToBatchDto $assignToBatchDto
     * @return SnappyOrderEntity
     */
    public function assignToBatch(AssignToBatchDto $assignToBatchDto)
    {
        $batch = $this->batchRepository->find($assignToBatchDto->batch_id);
        Contracts::requireEntityFound($batch, 'Batch');

        $order = $this->snappyOrderRepository->find($assignToBatchDto->order_id);
        Contracts::requireEntityFound($order, 'Order');

        $old_batch_id = (string) $order->batch_id;
        $order->batch_id = $assignToBatchDto->batch_id;
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->log(new ProxyOrderChangeLogDto(
            $order->id,
            'batch_id',
            $old_batch_id,
            (string) $assignToBatchDto->batch_id
        ));

        return $order;
    }

    /**
     * @param int $order_id
     * @return SnappyOrderEntity
     */
    public function unassignFromBatch(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');

        $order = $this->snappyOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires(!empty($order->batch_id), 'Order is not assigned to a batch');

        $old_batch_id = (string) $order->batch_id;
        $order->batch_id = 0;
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->log(new ProxyOrderChangeLogDto(
            $order->id,
            'batch_id',
            $old_batch_id,
            ''
        ));

        return $order;
    }

    /**
     * @param int $id
     * @return SnappyOrderEntity
     */
    public function getById(int $id)
    {
        Contracts::requires($id > 0, 'Order ID is required');

        $order = $this->snappyOrderRepository->find($id);
        Contracts::requireEntityFound($order, 'Order');

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
