<?php

namespace EcomOrder\Business\Dtos;

use Shared\Contracts;

class UpdateDeliveryStatusDto
{
    public int $order_id;
    public string $delivery_status;

    public function __construct(int $order_id, string $delivery_status)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $delivery_status = trim($delivery_status);
        Contracts::requiresInArray(
            $delivery_status,
            ['pending', 'picked-up', 'on-the-way', 'delivered'],
            'delivery_status'
        );

        $this->order_id = $order_id;
        $this->delivery_status = $delivery_status;
    }
}
