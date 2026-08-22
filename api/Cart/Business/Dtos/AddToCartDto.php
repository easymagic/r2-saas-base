<?php

namespace Cart\Business\Dtos;

use Shared\Contracts;

class AddToCartDto
{
    public string $uuid;
    public int $productId;
    public int $qty;

    public function __construct(string $uuid, int $productId, int $qty)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');
        Contracts::requires($productId > 0, 'Product ID is required');
        Contracts::requires($qty > 0, 'Quantity must be greater than 0');

        $this->uuid = $uuid;
        $this->productId = $productId;
        $this->qty = $qty;
    }
}
