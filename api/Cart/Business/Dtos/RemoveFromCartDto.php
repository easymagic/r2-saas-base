<?php

namespace Cart\Business\Dtos;

use Shared\Contracts;

class RemoveFromCartDto
{
    public string $uuid;
    public int $productId;

    public function __construct(string $uuid, int $productId)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');
        Contracts::requires($productId > 0, 'Product ID is required');

        $this->uuid = $uuid;
        $this->productId = $productId;
    }
}
