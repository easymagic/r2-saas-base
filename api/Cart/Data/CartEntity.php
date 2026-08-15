<?php

namespace Cart\Data;

use Shared\AbstractBaseEntity;

class CartEntity extends AbstractBaseEntity
{
    public string $cart_sess_uuid = '';
    public int $product_id = 0;
    public int $qty = 0;
    public float $price_total = 0;
    public string $created_at = '';
    public string $updated_at = '';
}
