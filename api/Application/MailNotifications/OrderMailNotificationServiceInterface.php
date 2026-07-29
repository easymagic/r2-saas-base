<?php

namespace Application\MailNotifications;

interface OrderMailNotificationServiceInterface
{
    public function sendOrderCreatedEmailToCustomer(int $orderId);
    public function sendOrderCreatedEmailToAdmin(int $orderId);
    public function sendOrderPaidEmailToCustomer(int $orderId);
    public function sendPriceAdjustedEmailToCustomer(int $orderId);
    public function sendDeliveryStatusChangedEmailToCustomer(int $orderId);
    public function sendOrderAssignedToAgentEmailToCustomer(int $orderId);
    public function sendOrderAssignedToAgentEmailToAgent(int $orderId);
}