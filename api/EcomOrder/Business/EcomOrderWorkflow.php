<?php

namespace EcomOrder\Business;

use Shared\Contracts;
use Cart\Business\CartServiceInterface;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Exception;
use OrderItem\Business\OrderItemServiceInterface;
use Product\Business\ProductServiceInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use Shared\Workflow;
use User\Business\UserServiceInterface;

class EcomOrderWorkflow extends Workflow
{

    private int $user_id = 0;
    private string $type = '';
    private int $number_of_installment = 0;
    private int $is_guest = 0;
    private string $customer_name = '';
    private string $customer_address = '';
    private string $customer_email = '';
    private string $reference = '';
    private string $cart_uuid = '';

    private float $shipping_fee = 0;
    private float $service_charge = 0;
    private float $total_amount = 0;

    private array $validPaymentMethods = ['card', 'wallet', 'bnpl'];

    private UserServiceInterface $userService;
    private CartServiceInterface $cartService;
    private EcomOrderRepositoryInterface $orderRepository;
    private MailServiceInterface $emailService;
    private OrderItemServiceInterface $orderItemService;
    private ProductServiceInterface $productService;

    
    public function __construct(
        UserServiceInterface $userService,
        CartServiceInterface $cartService,
        EcomOrderRepositoryInterface $orderRepository,
        MailServiceInterface $emailService,
        OrderItemServiceInterface $orderItemService,
        ProductServiceInterface $productService
    ) {
        $this->userService = $userService;
        $this->cartService = $cartService;
        $this->orderRepository = $orderRepository;
        $this->emailService = $emailService;
        $this->orderItemService = $orderItemService;
        $this->productService = $productService;
    }


    function validateCheckout(
        int $user_id,
        string $type,
        int $number_of_installment,
        string $customer_name,
        string $customer_address,
        string $customer_email,
        string $cart_uuid
    ) {
        $this->user_id = $user_id;
        $this->type = $type;
        $this->number_of_installment = $number_of_installment;
        $this->is_guest = empty($user_id) ? 1 : 0;
        $this->customer_name = $customer_name;
        $this->customer_address = $customer_address;
        $this->customer_email = $customer_email;
        $this->cart_uuid = $cart_uuid;

        Contracts::requiresNotNullOrEmpty($this->type, 'Payment Type');
        Contracts::requiresInArray($this->type, $this->validPaymentMethods, 'Payment Type');
        Contracts::requiresNotNullOrEmpty($this->number_of_installment, 'Number of Installment');
        // Contracts::requiresNotNullOrEmpty($this->is_guest, 'Is Guest');
        Contracts::requiresNotNullOrEmpty($this->customer_name, 'Customer Name');
        Contracts::requiresNotNullOrEmpty($this->customer_address, 'Customer Address');
        Contracts::requiresNotNullOrEmpty($this->customer_email, 'Customer Email');

        $this->reference = uniqid("REF-");

        Contracts::requiresNotNullOrEmpty($this->cart_uuid, 'Cart UUID');

        return $this;
    }

    function cartIsValid()
    {
        
        $cartSumTotal = $this->cartService->getCartTotal($this->cart_uuid);
        $this->flowShouldContinue($cartSumTotal > 0);
        return $this;
    }

    private function getSubTotalAmount()
    {
        $total = $this->cartService->getCartTotal($this->cart_uuid);
        Contracts::requires($total > 0, 'Cart is empty');
        return $total - $this->shipping_fee - $this->service_charge;
    }

    function loadTotalAmount()
    {
        $this->total_amount = $this->getSubTotalAmount();
        return $this;
    }

    function isPaymentMethodWallet()
    {
        $this->flowShouldContinue($this->type == 'wallet');
        return $this;
    }

    function isPaymentMethodCard()
    {
        $this->flowShouldContinue($this->type == 'card');
        return $this;
    }

    function isPaymentMethodBnpl()
    {
        $this->flowShouldContinue($this->type == 'bnpl');
        return $this;
    }


    function isGuest()
    {
        $this->flowShouldContinue(empty($this->user_id));
        return $this;
    }

    function isNotGuest()
    {
        $this->flowShouldContinue(!empty($this->user_id));
        return $this;
    }

    function isNumberOfInstallmentValid()
    {
        $this->flowShouldContinue($this->number_of_installment > 0 && $this->number_of_installment <= 12);
        return $this;
    }

    function isCustomerWalletNotSufficient()
    {
        $balance = $this->userService->getWalletBalance($this->user_id);
        $this->flowShouldContinue($balance < $this->total_amount);
        return $this;
    }

    function deductWalletBalance()
    {
        $this->userService->withdrawWallet($this->user_id, $this->total_amount);
        return $this;
    }

    function createOrder(int &$order_id)
    {
        if(!$this->pass()){
            return $this;
        }
        $order = $this->orderRepository->save(0, [
            'user_id' => $this->user_id,
            'type' => $this->type,
            'number_of_installment' => $this->number_of_installment,
            'is_guest' => $this->is_guest,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'customer_email' => $this->customer_email,
        ]);
        $order_id = $order->id;
    }

    function createOrderItems(int $order_id)
    {
        if(!$this->pass()){
            return $this;
        }

        $cartItems = $this->cartService->getCart($this->cart_uuid);
        $percentageToPlatform = 0.05;
        foreach($cartItems as $cartItem){
            $product = $this->productService->find($cartItem->product_id);
            // deduct stock qty from product
            $this->productService->deductStockQty($cartItem->product_id, $cartItem->qty);
            $merchantId = $product->user_id;
            $totalLineAmount = $cartItem->price_total;
            $settled = 0;
            $this->orderItemService->create(
                $order_id,
                $merchantId,
                $cartItem->product_id,
                $cartItem->qty,
                $totalLineAmount,
                $settled,
                $percentageToPlatform
            );
        }
        return $this;
    }

    function debitWalletBalance(){
        if (!$this->pass()){
            return $this;
        }
        $this->userService->withdrawWallet($this->user_id, $this->total_amount);
        return $this;
    }

    function markOrderAsPaid(int $order_id){
        if (!$this->pass()){
            return $this;
        }
        $this->orderRepository->save($order_id, ['status' => 'paid']);
        return $this;
    }

    function markOrderAsPartiallyPaid(int $order_id){
        if (!$this->pass()){
            return $this;
        }
        $this->orderRepository->save($order_id, ['status' => 'part-paid']);
        return $this;
    }

    function notifyCustomerOfOrderCreationReceipt(int $order_id)
    {
        if(!$this->pass()){
            return $this;
        }
        $order = $this->orderRepository->find($order_id);
        ob_start();
        include MAIL_TEMPLATE_DIR . '/order_created.mail.php';
        $emailContent = ob_get_clean();
        $this->emailService->send(
            $order->customer_email,
            'Order Checkout Success (Invoice)',
            'noreply@example.com',
            $emailContent
        );
        return $this;
    }

    function notifyPlatformOfOrderCreation(int $order_id)
    {
        if(!$this->pass()){
            return $this;
        }
        $order = $this->orderRepository->find($order_id);
        ob_start();
        include MAIL_TEMPLATE_DIR . '/order_created.mail.php';
        $emailContent = ob_get_clean();
        $this->emailService->send(
            $order->customer_email,
            'Order Checkout Success (Invoice)',
            'noreply@example.com',
            $emailContent
        );
        return $this;
    }

    function notifyMerchantOfProductSold(int $order_id){
        if(!$this->pass()){
            return $this;
        }
        $orderItems = $this->orderItemService->fetchForOrder($order_id)->fetchAll();
        foreach($orderItems as $orderItem){
            $merchantId = $orderItem->merchant_id;
            $user = $this->userService->find($merchantId);
            
            ob_start();
            include MAIL_TEMPLATE_DIR . '/product_sold.mail.php';
            $emailContent = ob_get_clean();
            $this->emailService->send(
                $user->email,
                'Product Sold',
                'noreply@example.com',
                $emailContent
            );

        }
        return $this;
    }

}
