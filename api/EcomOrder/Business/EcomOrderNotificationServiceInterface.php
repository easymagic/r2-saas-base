<?php 

namespace EcomOrder\Business;

interface EcomOrderNotificationServiceInterface
{
    public function sendOrderInvoiceToCustomer(int $order_id);

    public function sendOrderInvoiceToPlatform(int $order_id);

    public function sendOrderPaidNotificationToCustomer(int $order_id);

    public function sendOrderPaidNotificationToMerchant(int $order_id);

    public function sendOrderPaidNotificationToPlatform(int $order_id);

    public function sendOrderFailedNotificationToCustomer(int $order_id);

    public function sendOrderStatusChangedNotificationToCustomer(int $order_id, string $status);

    public function sendOrderStatusChangedNotificationToMerchant(int $order_id, string $status);

    public function sendOrderAssignedToAgentNotificationToCustomer(int $order_id, int $agent_id);

}