<?php

namespace EcomOrder\Business;

use App\Shared\Contracts\Contracts;
use BnplPaymentSchedule\Business\BnplPaymentScheduleServiceInterface;
use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use Cart\Business\CartServiceInterface;
use EcomOrder\Data\EcomOrderEntity;
use EcomOrder\Data\EcomOrderMigrationRepositoryInterface;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use OrderItem\Business\OrderItemServiceInterface;
use PlatformConfig\Business\PlatformConfigServiceInterface;
use Product\Data\ProductRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
use User\Business\UserServiceInterface;
use User\Data\UserRepositoryInterface;
use Wallet\Business\WalletServiceInterface;

/**
 * @extends AbstractBaseService<EcomOrderEntity, EcomOrderRepositoryInterface>
 */
class EcomOrderService extends AbstractBaseService implements EcomOrderServiceInterface
{
    private EcomOrderMigrationRepositoryInterface $ecomOrderMigrationRepositoryInterface;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private EcomOrderNotificationServiceInterface $ecomOrderNotificationService;
    private CartServiceInterface $cartService;
    private OrderItemServiceInterface $orderItemService;
    private ProductRepositoryInterface $productRepository;
    private UserRepositoryInterface $userRepository;
    private UserServiceInterface $userService;
    private WalletServiceInterface $walletService;
    private BnplPaymentScheduleServiceInterface $bnplPaymentScheduleService;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private PaymentServiceInterface $paymentService;
    private PlatformConfigServiceInterface $platformConfigService;
    private EcomOrderWorkflow $ecomOrderWorkflow;

    public function __construct(
        EcomOrderMigrationRepositoryInterface $ecomOrderMigrationRepositoryInterface,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        EcomOrderNotificationServiceInterface $ecomOrderNotificationService,
        CartServiceInterface $cartService,
        OrderItemServiceInterface $orderItemService,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository,
        UserServiceInterface $userService,
        WalletServiceInterface $walletService,
        BnplPaymentScheduleServiceInterface $bnplPaymentScheduleService,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        PaymentServiceInterface $paymentService,
        PlatformConfigServiceInterface $platformConfigService,
        EcomOrderWorkflow $ecomOrderWorkflow
    ) {
        parent::__construct($ecomOrderRepository);
        $this->ecomOrderMigrationRepositoryInterface = $ecomOrderMigrationRepositoryInterface;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->ecomOrderNotificationService = $ecomOrderNotificationService;
        $this->cartService = $cartService;
        $this->orderItemService = $orderItemService;
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->walletService = $walletService;
        $this->bnplPaymentScheduleService = $bnplPaymentScheduleService;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->paymentService = $paymentService;
        $this->platformConfigService = $platformConfigService;
        $this->ecomOrderWorkflow = $ecomOrderWorkflow;
    }

    public function migrate()
    {
        return $this->ecomOrderMigrationRepositoryInterface->migrate();
    }

    /**
     * @param int $user_id
     * @param array $filters
     * @return QueryObject
     */
    public function fetchForUser(int $user_id, array $filters = [])
    {
        Contracts::requires($user_id > 0, 'User ID is required');
        $user = $this->userRepository->find($user_id);
        Contracts::requireEntityFound($user, 'User');
        $filters['user_id'] = $user_id;
        return $this->ecomOrderRepository->query($this->sanitizeListFilters($filters));
    }

    /**
     * @param array $filters
     * @return QueryObject
     */
    public function fetchForAdmin(array $filters = []): QueryObject
    {
        return $this->ecomOrderRepository->query($this->sanitizeListFilters($filters));
    }

    /**
     * @param int $agent_id
     * @param array $filters
     * @return QueryObject
     */
    public function fetchForAgent(int $agent_id, array $filters = [])
    {
        Contracts::requires($agent_id > 0, 'Agent ID is required');
        $agent = $this->userRepository->find($agent_id);
        Contracts::requireEntityFound($agent, 'Agent');
        Contracts::requires($agent->role === 'agent', $agent->name . ' is not an agent');
        $filters['agent_id'] = $agent_id;
        return $this->ecomOrderRepository->query($this->sanitizeListFilters($filters));
    }

    /**
     * @param int $user_id
     * @param string $type
     * @param int $number_of_installment
     * @param int $is_guest
     * @param string $customer_name
     * @param string $customer_address
     * @param string $customer_email
     * @param string $reference
     * @param string $cart_uuid
     * @return EcomOrderEntity
     */
    public function checkout(
        int $user_id,
        string $type,
        int $number_of_installment,
        string $customer_name,
        string $customer_address,
        string $customer_email,
        string $reference,
        string $cart_uuid
    ) {


        $this->ecomOrderWorkflow->validateCheckout(
            $user_id,
            $type,
            $number_of_installment,
            $customer_name,
            $customer_address,
            $customer_email,
            $cart_uuid
        )->isPaymentMethodWallet()
        ->isCustomerWalletNotSufficient()
        ->reject("Insufficient wallet balance")
        ->loadTotalAmount()
        
        ;








        // $type = strtolower(trim($type));
        // $cart_uuid = trim($cart_uuid);
        // $customer_name = trim($customer_name);
        // $customer_address = trim($customer_address);
        // $customer_email = trim($customer_email);
        // $reference = trim($reference);
        // $is_guest = $is_guest ? 1 : 0;

        // $shipping_fee = $this->getShippingFee();
        // $service_charge = $this->getServiceCharge();

        // Contracts::requiresInArray($type, ['card', 'wallet', 'bnpl'], 'type');
        // Contracts::requiresNotNullOrEmpty($cart_uuid, 'cart uuid');
        // Contracts::requires($shipping_fee >= 0, 'Shipping fee cannot be negative');
        // Contracts::requires($service_charge >= 0, 'Service charge cannot be negative');
        // Contracts::requires(in_array($is_guest, [0, 1], true), 'is_guest must be 0 or 1');

        // if ($type === 'bnpl') {
        //     Contracts::requires($number_of_installment >= 2, 'BNPL requires at least 2 installments');
        //     Contracts::requires($is_guest === 0 && $user_id > 0, 'BNPL checkout requires a logged in user');
        // } else {
        //     Contracts::requires($number_of_installment <= 1, 'Installments are only allowed for BNPL');
        //     $number_of_installment = 0;
        // }

        // if ($type === 'wallet') {
        //     Contracts::requires($is_guest === 0 && $user_id > 0, 'Wallet checkout requires a logged in user');
        // }

        // $user = null;
        // if ($user_id > 0) {
        //     $user = $this->userRepository->find($user_id);
        //     Contracts::requireEntityFound($user, 'User');
        //     if ($customer_name === '') {
        //         $customer_name = (string) $user->name;
        //     }
        //     if ($customer_email === '') {
        //         $customer_email = (string) $user->email;
        //     }
        //     if ($customer_address === '') {
        //         $customer_address = (string) $user->delivery_address;
        //     }
        // }

        // Contracts::requiresNotNullOrEmpty($customer_name, 'customer name');
        // Contracts::requiresNotNullOrEmpty($customer_address, 'customer address');
        // Contracts::requiresNotNullOrEmpty($customer_email, 'customer email');
        // Contracts::requires(filter_var($customer_email, FILTER_VALIDATE_EMAIL) !== false, 'Customer email is invalid');

        // $cartItems = $this->cartService->getCart($cart_uuid);
        // Contracts::requires(!empty($cartItems), 'Cart is empty');

        // $lines = [];
        // $cartTotal = 0.0;
        // foreach ($cartItems as $cartItem) {
        //     $product = $this->productRepository->find((int) $cartItem->product_id);
        //     Contracts::requireEntityFound($product, 'Product');
        //     Contracts::requires((int) $product->active === 1, 'Product is not available');
        //     Contracts::requires((int) $cartItem->qty > 0, 'Quantity must be greater than 0');
        //     Contracts::requires((int) $product->stock_qty >= (int) $cartItem->qty, 'Insufficient stock for ' . $product->name);

        //     $lineAmount = round((float) $product->price * (int) $cartItem->qty, 2);
        //     $merchantId = (int) $cartItem->merchant_id > 0 ? (int) $cartItem->merchant_id : (int) $product->user_id;
        //     Contracts::requires($merchantId > 0, 'Merchant is required for cart item');

        //     $lines[] = [
        //         'product' => $product,
        //         'qty' => (int) $cartItem->qty,
        //         'line_amount' => $lineAmount,
        //         'merchant_id' => $merchantId,
        //     ];
        //     $cartTotal += $lineAmount;
        // }

        // $computedTotal = round($cartTotal + $shipping_fee + $service_charge, 2);
        // Contracts::requires($computedTotal > 0, 'Total amount must be greater than 0');

        // if ($type === 'wallet') {
        //     Contracts::requires(
        //         (float) $user->wallet_balance >= $computedTotal,
        //         'Wallet balance is not enough'
        //     );
        // }

        // if ($reference === '') {
        //     $reference = uniqid('ECOM-') . '-' . time();
        // }
        // $existingReference = $this->ecomOrderRepository->findBy('reference', $reference);
        // Contracts::requires($existingReference->isEmpty(), 'Order reference already exists');

        // $order = $this->ecomOrderRepository->save(0, [
        //     'user_id' => $user_id > 0 ? $user_id : null,
        //     'type' => $type,
        //     'number_of_installment' => $number_of_installment,
        //     'shipping_fee' => $shipping_fee,
        //     'service_charge' => $service_charge,
        //     'total_amount' => $computedTotal,
        //     'is_guest' => $is_guest,
        //     'customer_name' => $customer_name,
        //     'customer_address' => $customer_address,
        //     'customer_email' => $customer_email,
        //     'reference' => $reference,
        //     'payment_status' => 'pending',
        //     'delivery_status' => 'pending',
        // ]);

        // $percentageToPlatform = $this->getPercentageToPlatform();
        // foreach ($lines as $line) {
        //     $this->orderItemService->create(
        //         (int) $order->id,
        //         (int) $line['merchant_id'],
        //         (int) $line['product']->id,
        //         (int) $line['qty'],
        //         (float) $line['line_amount'],
        //         0,
        //         $percentageToPlatform
        //     );
        //     $this->productRepository->save((int) $line['product']->id, [
        //         'stock_qty' => (int) $line['product']->stock_qty - (int) $line['qty'],
        //     ]);
        // }

        // $this->cartService->clearCart($cart_uuid);

        // $this->ecomOrderNotificationService->sendOrderInvoiceToCustomer((int) $order->id);
        // $this->ecomOrderNotificationService->sendOrderInvoiceToPlatform((int) $order->id);

        // if ($type === 'wallet') {
        //     $this->userService->withdrawWallet($user_id, $computedTotal);
        //     $this->walletService->log(
        //         $user_id,
        //         $computedTotal,
        //         uniqid('WALLET_WITHDRAWAL_'),
        //         'withdrawal',
        //         'Withdrawal from wallet for ecom order #' . $order->id,
        //         'approved'
        //     );
        //     return $this->updatePaymentStatusAsPaid((int) $order->id);
        // }

        // $chargeAmount = $computedTotal;
        // if ($type === 'bnpl') {
        //     $installmentAmount = round($computedTotal / $number_of_installment, 2);
        //     $this->bnplPaymentScheduleService->createSchedules(
        //         (int) $order->id,
        //         $number_of_installment,
        //         $installmentAmount,
        //         $reference,
        //         ''
        //     );
        //     $chargeAmount = $installmentAmount;
        // }

        // $this->paymentService->initiate($customer_email, $chargeAmount, $reference);
        // $order->payment_url = (string) $this->paymentService->getAuthUrl();

        // return $order;
    }

    public function publishSettings()
    {
        $this->platformConfigService->set('ECOM_SHIPPING_FEE', (string) $this->getShippingFee());
        $this->platformConfigService->set('ECOM_SERVICE_CHARGE', (string) $this->getServiceCharge());
        $this->platformConfigService->set('ECOM_PERCENTAGE_TO_PLATFORM', (string) $this->getPercentageToPlatform());
    }

    public function updateDeliveryStatus(int $order_id, string $delivery_status)
    {
        $delivery_status = trim($delivery_status);
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requiresInArray(
            $delivery_status,
            ['pending', 'picked-up', 'on-the-way', 'delivered'],
            'delivery_status'
        );

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $order = $this->ecomOrderRepository->save($order_id, [
            'delivery_status' => $delivery_status,
        ]);

        $this->ecomOrderNotificationService->sendOrderStatusChangedNotificationToCustomer($order_id, $delivery_status);
        $this->ecomOrderNotificationService->sendOrderStatusChangedNotificationToMerchant($order_id, $delivery_status);

        return $order;
    }

    public function updatePaymentStatusAsPaid(int $order_id)
    {
        $order = $this->requirePendingPaymentOrder($order_id);
        $order = $this->ecomOrderRepository->save($order_id, [
            'payment_status' => 'paid',
        ]);
        $this->ecomOrderNotificationService->sendOrderPaidNotificationToCustomer($order_id);
        $this->ecomOrderNotificationService->sendOrderPaidNotificationToMerchant($order_id);
        $this->ecomOrderNotificationService->sendOrderPaidNotificationToPlatform($order_id);
        return $order;
    }

    public function updatePaymentStatusAsPartiallyPaid(int $order_id)
    {
        $order = $this->requirePendingPaymentOrder($order_id);
        return $this->ecomOrderRepository->save($order_id, [
            'payment_status' => 'part-paid',
        ]);
    }

    public function updatePaymentStatusAsFailed(int $order_id)
    {
        $order = $this->requirePendingPaymentOrder($order_id);
        $order = $this->ecomOrderRepository->save($order_id, [
            'payment_status' => 'failed',
        ]);
        $this->ecomOrderNotificationService->sendOrderFailedNotificationToCustomer($order_id);
        return $order;
    }

    public function assignToAgent(int $order_id, int $agent_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($agent_id > 0, 'Agent ID is required');

        $agent = $this->userRepository->find($agent_id);
        Contracts::requireEntityFound($agent, 'Agent');
        Contracts::requires($agent->role === 'agent', $agent->name . ' is not an agent');

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $order = $this->ecomOrderRepository->save($order_id, [
            'agent_id' => $agent_id,
        ]);

        $this->ecomOrderNotificationService->sendOrderAssignedToAgentNotificationToCustomer($order_id, $agent_id);
        return $order;
    }

    public function paymentFeedback(int $order_id, string $reference)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $reference = trim($reference);
        Contracts::requiresNotNullOrEmpty($reference, 'reference');

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires($order->reference === $reference, 'Payment reference does not match this order');

        if ($order->payment_status === 'paid') {
            return $order;
        }
        Contracts::requires(
            in_array($order->payment_status, ['pending', 'part-paid'], true),
            'Order is not awaiting payment'
        );

        $this->paymentService->verify($reference);
        $status = $this->paymentService->getStatus();

        if ($status === 'success') {
            if ($order->type === 'bnpl') {
                $authorizationCode = (string) $this->paymentService->getAuthorizationCode();
                $this->markFirstBnplSchedulePaid($order, $authorizationCode);
                $pending = $this->bnplPaymentScheduleRepository->query([
                    'order_id' => (int) $order->id,
                    'payment_status' => 'pending',
                ])->fetchAll();
                if (empty($pending)) {
                    return $this->updatePaymentStatusAsPaid((int) $order->id);
                }
                return $this->updatePaymentStatusAsPartiallyPaid((int) $order->id);
            }
            return $this->updatePaymentStatusAsPaid((int) $order->id);
        }

        if ($status === 'abandoned') {
            return $order;
        }

        return $this->updatePaymentStatusAsFailed((int) $order->id);
    }

    /**
     * @param int $user_id
     * @return EcomOrderEntity[]
     */
    public function pendingPaymentsForUser(int $user_id)
    {
        Contracts::requires($user_id > 0, 'User ID is required');
        $user = $this->userRepository->find($user_id);
        Contracts::requireEntityFound($user, 'User');

        return $this->ecomOrderRepository->query([
            'user_id' => $user_id,
            'payment_status' => 'pending',
            'payable_types' => 1,
        ])->fetchAll();
    }

    public function getPendingPayments()
    {
        $dueSchedules = $this->bnplPaymentScheduleRepository->query([
            'payment_status' => 'pending',
            'due_on_or_before' => date('Y-m-d'),
        ])->fetchAll();

        foreach ($dueSchedules as $schedule) {
            if (trim((string) $schedule->authorization_code) === '') {
                continue;
            }
            try {
                $this->bnplPaymentScheduleService->chargeSchedule((int) $schedule->id);
            } catch (\Exception $e) {
                $this->bnplPaymentScheduleService->increaseNumberOfAttempts((int) $schedule->id);
            }
        }

        return $this->ecomOrderRepository->query([
            'pending_payments' => 1,
        ]);
    }

    /**
     * @param array $filters
     * @return array
     */
    private function sanitizeListFilters(array $filters)
    {
        $allowed = [
            'payment_status',
            'delivery_status',
            'type',
            'search',
            'date_from',
            'date_to',
            'reference',
            'user_id',
            'agent_id',
        ];
        $clean = [];
        foreach ($allowed as $key) {
            if (!isset($filters[$key])) {
                continue;
            }
            if ($filters[$key] === '' || $filters[$key] === null) {
                continue;
            }
            $clean[$key] = $filters[$key];
        }
        return $clean;
    }

    /**
     * @param int $order_id
     * @return EcomOrderEntity
     */
    private function requirePendingPaymentOrder(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires(
            in_array($order->payment_status, ['pending', 'part-paid'], true),
            'Order payment status cannot be updated'
        );
        return $order;
    }

    /**
     * @param EcomOrderEntity $order
     * @param string $authorizationCode
     * @return void
     */
    private function markFirstBnplSchedulePaid(EcomOrderEntity $order, string $authorizationCode)
    {
        $schedules = $this->bnplPaymentScheduleRepository->query([
            'order_id' => (int) $order->id,
        ])->fetchAll();
        Contracts::requires(!empty($schedules), 'BNPL schedules not found for this order');

        $first = $schedules[0];
        foreach ($schedules as $schedule) {
            $payload = [];
            if ($authorizationCode !== '') {
                $payload['authorization_code'] = $authorizationCode;
            }
            if ((int) $schedule->id === (int) $first->id && $schedule->payment_status === 'pending') {
                $payload['payment_status'] = 'paid';
                $payload['paid_at'] = date('Y-m-d H:i:s');
            }
            if (!empty($payload)) {
                $this->bnplPaymentScheduleRepository->save((int) $schedule->id, $payload);
            }
        }
    }

    private function getShippingFee()
    {
        return (float) $this->platformConfigService->get('ECOM_SHIPPING_FEE', 100);
    }

    private function getServiceCharge()
    {
        return (float) $this->platformConfigService->get('ECOM_SERVICE_CHARGE', 100);
    }

    private function getPercentageToPlatform()
    {
        return (float) $this->platformConfigService->get('ECOM_PERCENTAGE_TO_PLATFORM', 10);
    }
}
