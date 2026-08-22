<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class TopUpWalletDto
{
    public int $id;
    public float $amount;

    public function __construct(int $id, float $amount)
    {
        Contracts::requires($id > 0, 'ID is required');
        Contracts::requires($amount > 0, 'Amount is required');

        $this->id = $id;
        $this->amount = $amount;
    }
}
