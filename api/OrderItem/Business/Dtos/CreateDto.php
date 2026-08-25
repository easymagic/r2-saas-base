<?php

namespace OrderItem\Business\Dtos;

use Shared\Contracts;

class CreateDto
{
    public int $order_id;
    public int $merchant_id;
    public int $product_id;
    public int $qty;
    public float $total_line_amount;
    public int $settled;
    public float $percentage_to_platform;

    public function __construct(
        int $order_id,
        int $merchant_id,
        int $product_id,
        int $qty,
        float $total_line_amount,
        int $settled,
        float $percentage_to_platform
    ) {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($merchant_id > 0, 'Merchant ID is required');
        Contracts::requires($product_id > 0, 'Product ID is required');
        Contracts::requires($qty > 0, 'Quantity must be greater than 0');
        Contracts::requires($total_line_amount >= 0, 'Total line amount cannot be negative');
        Contracts::requires(in_array($settled, [0, 1], true), 'Settled must be 0 or 1');
        Contracts::requires(
            $percentage_to_platform >= 0 && $percentage_to_platform <= 100,
            'Percentage to platform must be between 0 and 100'
        );

        $this->order_id = $order_id;
        $this->merchant_id = $merchant_id;
        $this->product_id = $product_id;
        $this->qty = $qty;
        $this->total_line_amount = $total_line_amount;
        $this->settled = $settled;
        $this->percentage_to_platform = $percentage_to_platform;
    }
}
