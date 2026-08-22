<?php

namespace Product\Business\Dtos;

use Shared\Contracts;

class DeductStockQtyDto
{
    public int $id;
    public int $qty;

    public function __construct(int $id, int $qty)
    {
        Contracts::requires($id > 0, 'Product ID is required');
        Contracts::requires($qty > 0, 'Quantity must be greater than 0');

        $this->id = $id;
        $this->qty = $qty;
    }
}
