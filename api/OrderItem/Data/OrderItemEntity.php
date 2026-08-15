<?php

namespace OrderItem\Data;

use Shared\AbstractBaseEntity;

class OrderItemEntity extends AbstractBaseEntity
{
    public int $order_id = 0;
    public int $merchant_id = 0;
    public int $product_id = 0;
    public int $qty = 0;
    public float $total_line_amount = 0;
    public int $settled = 0;
    public float $percentage_to_platform = 0;
    public string $created_at = '';
    public string $updated_at = '';
}
