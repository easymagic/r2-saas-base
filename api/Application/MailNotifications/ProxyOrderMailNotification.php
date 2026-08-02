<?php 
namespace Application\MailNotifications;

use Domain\ProxyOrder\Interfaces\ProxyOrderRepositoryInterface;
use Domain\User\UserRepositoryInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;

class ProxyOrderMailNotification implements ProxyOrderMailNotificationInterface
{

    private MailServiceInterface $mailService;
    private ProxyOrderRepositoryInterface $proxyOrderRepository;
    private EnvServiceInterface $envService;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        MailServiceInterface $mailService,
        ProxyOrderRepositoryInterface $proxyOrderRepository,
        EnvServiceInterface $envService,
        UserRepositoryInterface $userRepository
    ) {
        $this->mailService = $mailService;
        $this->proxyOrderRepository = $proxyOrderRepository;
        $this->envService = $envService;
        $this->userRepository = $userRepository;
    }

    public function sendCustomerOrderCreatedNotification(int $proxyOrderId){
        $proxyOrder = $this->proxyOrderRepository->find($proxyOrderId);
        $user = $this->userRepository->find($proxyOrder->user_id);
        $to = $user->email;
        $subject = 'Order Created';
        $from = $this->envService->get('NOREPLY_EMAIL');
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your order has been created successfully.<br>
        <br>Order ID: ' . $proxyOrder->id . '<br>
        <br>Order Type: ' . $proxyOrder->type . '<br>
        <br>Order Reference: ' . $proxyOrder->reference . '<br>
        <br>Order Link: ' . $proxyOrder->link . '<br>
        <br>Order Description: ' . $proxyOrder->description . '<br>
        <br>Order Amount: ' . $proxyOrder->total_amount_usd . '<br>
        <br>Order Status: ' . $proxyOrder->status . '<br>
        <br>Order Created At: ' . $proxyOrder->created_at . '<br>
        
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendAdminOrderCreatedNotification(int $proxyOrderId){
        $proxyOrder = $this->proxyOrderRepository->find($proxyOrderId);
        $user = $this->userRepository->find($proxyOrder->user_id);
        $to = $this->envService->get('ADMIN_EMAIL');
        $subject = 'Order Created';
        $from = $this->envService->get('NOREPLY_EMAIL');
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br> A new order/request has been created by ' . $user->name . ' successfully.<br>
        <br>Order ID: ' . $proxyOrder->id . '<br>
        <br>Order Type: ' . $proxyOrder->type . '<br>
        <br>Order Reference: ' . $proxyOrder->reference . '<br>
        <br>Order Link: ' . $proxyOrder->link . '<br>
        <br>Order Description: ' . $proxyOrder->description . '<br>
        <br>Order Amount: ' . $proxyOrder->total_amount_usd . '<br>
        <br>Order Status: ' . $proxyOrder->status . '<br>
        <br>Order Created At: ' . $proxyOrder->created_at . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendCustomerOrderStatusChangedNotification(int $proxyOrderId){
        $proxyOrder = $this->proxyOrderRepository->find($proxyOrderId);
        $user = $this->userRepository->find($proxyOrder->user_id);
        $to = $user->email;
        $subject = 'Order Status Changed';
        $from = $this->envService->get('NOREPLY_EMAIL');
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your order status has been changed to ' . $proxyOrder->status . '.<br>
        <br>Order ID: ' . $proxyOrder->id . '<br>
        <br>Order Type: ' . $proxyOrder->type . '<br>
        <br>Order Reference: ' . $proxyOrder->reference . '<br>
        <br>Order Link: ' . $proxyOrder->link . '<br>
        <br>Order Description: ' . $proxyOrder->description . '<br>
        <br>Order Amount: ' . $proxyOrder->total_amount_usd . '<br>
        <br>Order Status: ' . $proxyOrder->status . '<br>
        <br>Order Created At: ' . $proxyOrder->created_at . '<br>
        <br>Order Updated At: ' . $proxyOrder->updated_at . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendCustomerOrderReadyForPickupNotification(int $proxyOrderId){
        $proxyOrder = $this->proxyOrderRepository->find($proxyOrderId);
        $user = $this->userRepository->find($proxyOrder->user_id);
        $to = $user->email;
        $subject = 'Order Ready for Pickup';
        $from = $this->envService->get('NOREPLY_EMAIL');
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your order is ready for pickup.<br>
        <br>Your pickup OTP is: <b>' . $proxyOrder->pickup_otp_code . '</b><br>
        <br>Order ID: ' . $proxyOrder->id . '<br>
        <br>Order Type: ' . $proxyOrder->type . '<br>
        <br>Order Reference: ' . $proxyOrder->reference . '<br>
        <br>Order Link: ' . $proxyOrder->link . '<br>
        <br>Order Description: ' . $proxyOrder->description . '<br>
        <br>Order Amount: ' . $proxyOrder->total_amount_usd . '<br>
        <br>Order Status: ' . $proxyOrder->status . '<br>
        <br>Order Created At: ' . $proxyOrder->created_at . '<br>
        <br>Order Updated At: ' . $proxyOrder->updated_at . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendAgentOrderAssignedNotification(int $proxyOrderId){
        $proxyOrder = $this->proxyOrderRepository->find($proxyOrderId);
        $agent = $this->userRepository->find($proxyOrder->agent_id);
        $user = $this->userRepository->find($proxyOrder->user_id);
        $to = $user->email;
        $subject = 'Order Assigned to You';
        $from = $this->envService->get('NOREPLY_EMAIL');
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your order/request has been assigned to an agent.<br>
        <br>The agent is: ' . $agent->name . '<br>
        <br>Order ID: ' . $proxyOrder->id . '<br>
        <br>Order Type: ' . $proxyOrder->type . '<br>
        <br>Order Reference: ' . $proxyOrder->reference . '<br>
        <br>Order Link: ' . $proxyOrder->link . '<br>
        <br>Order Description: ' . $proxyOrder->description . '<br>
        <br>Order Amount: ' . $proxyOrder->total_amount_usd . '<br>
        <br>Order Status: ' . $proxyOrder->status . '<br>
        <br>Order Created At: ' . $proxyOrder->created_at . '<br>
        <br>Order Updated At: ' . $proxyOrder->updated_at . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function notifyAgentOfNewOrder(int $proxyOrderId){
        $proxyOrder = $this->proxyOrderRepository->find($proxyOrderId);
        $agent = $this->userRepository->find($proxyOrder->agent_id);
        $user = $this->userRepository->find($proxyOrder->user_id);
        $to = $agent->email;
        $subject = 'New Order/Request';
        $from = $this->envService->get('NOREPLY_EMAIL');
        $name = $agent->name;
        $body = '
        Hello ' . $name . ',
        <br><br>A new order/request has been created.<br>
        <br>The customer is: ' . $user->name . '<br>
        <br>Order ID: ' . $proxyOrder->id . '<br>
        <br>Order Type: ' . $proxyOrder->type . '<br>
        <br>Order Reference: ' . $proxyOrder->reference . '<br>
        <br>Order Link: ' . $proxyOrder->link . '<br>
        <br>Order Description: ' . $proxyOrder->description . '<br>
        <br>Order Amount: ' . $proxyOrder->total_amount_usd . '<br>
        <br>Order Status: ' . $proxyOrder->status . '<br>
        <br>Order Created At: ' . $proxyOrder->created_at . '<br>
        <br>Order Updated At: ' . $proxyOrder->updated_at . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendCustomerPriceAdjustedNotification(int $proxyOrderId){
        $proxyOrder = $this->proxyOrderRepository->find($proxyOrderId);
        $user = $this->userRepository->find($proxyOrder->user_id);
        $to = $user->email;
        $subject = 'Price Adjusted';
        $from = $this->envService->get('NOREPLY_EMAIL');
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your order price has been adjusted.<br>
        <br>The new price is: ₦ ' . $proxyOrder->grand_total_naira . '<br>
        <br>Shipping Fee: $ ' . $proxyOrder->shipping_cost_usd . '<br>
        <br>Service Fee: $ ' . $proxyOrder->service_charge_usd . '<br>
        <br>Dollar to Naira Rate: $1 = ₦ ' . $proxyOrder->dollar_to_naira_rate . '<br>
   
        <br>Order ID: ' . $proxyOrder->id . '<br>
        <br>Order Type: ' . $proxyOrder->type . '<br>
        <br>Order Reference: ' . $proxyOrder->reference . '<br>
        <br>Order Link: ' . $proxyOrder->link . '<br>
        <br>Order Description: ' . $proxyOrder->description . '<br>
        <br>Order Amount: ' . $proxyOrder->total_amount_usd . '<br>
        <br>Order Status: ' . $proxyOrder->status . '<br>
        <br>Order Created At: ' . $proxyOrder->created_at . '<br>
        <br>Order Updated At: ' . $proxyOrder->updated_at . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

}