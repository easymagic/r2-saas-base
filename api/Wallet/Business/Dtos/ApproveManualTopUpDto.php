<?php

namespace Wallet\Business\Dtos;

use Shared\Contracts;

class ApproveManualTopUpDto
{
    public int $wallet_id;
    public string $status;

    public function __construct(int $wallet_id, string $status)
    {
        Contracts::requires($wallet_id > 0, 'Wallet ID is required!');
        Contracts::requiresInArray($status, ['approved'], 'Status');

        $this->wallet_id = $wallet_id;
        $this->status = $status;
    }
}
