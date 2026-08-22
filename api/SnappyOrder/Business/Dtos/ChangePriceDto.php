<?php

namespace SnappyOrder\Business\Dtos;

use Shared\Contracts;

class ChangePriceDto
{
    public int $order_id;
    public float $price;

    public function __construct(int $order_id, float $price)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($price > 0, 'Price is required');

        $this->order_id = $order_id;
        $this->price = $price;
    }
}
