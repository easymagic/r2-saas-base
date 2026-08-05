<?php 
namespace Business\ProxyOrder\Order;

interface ProxyOrderMailNotificationInterface
{
    public function sendCustomerOrderCreatedNotification(int $proxyOrderId);
    public function sendAdminOrderCreatedNotification(int $proxyOrderId);
    public function sendCustomerOrderStatusChangedNotification(int $proxyOrderId);
    public function sendCustomerOrderReadyForPickupNotification(int $proxyOrderId);

    public function sendAgentOrderAssignedNotification(int $proxyOrderId);
    public function notifyAgentOfNewOrder(int $proxyOrderId);
    public function sendCustomerPriceAdjustedNotification(int $proxyOrderId);
    public function sendCustomerOrderPaidNotification(int $proxyOrderId);
    public function sendCustomerOrderPaymentApprovedNotification(int $proxyOrderId);
}