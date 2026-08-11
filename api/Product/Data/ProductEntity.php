<?php

namespace Product\Data;

use Shared\AbstractBaseEntity;

class ProductEntity extends AbstractBaseEntity
{
    public string $name = '';
    public string $description = '';
    public string $image = '';
    public float $price = 0;
    public string $uuid = '';
    public float $old_price = 0;
    public int $stock_qty = 0;
    public int $active = 1;
    public string $slug = '';
    public int $user_id = 0;
    public int $category_id = 0;
    public string $created_at = '';
    public string $updated_at = '';
}
