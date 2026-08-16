<?php

namespace OrderItem\Business;

interface OrderItemNotificationServiceInterface
{
    public function notifyMerchantOfSettlement(int $order_item_id);
    public function notifyPlatformOfSettlement(int $order_item_id);
}