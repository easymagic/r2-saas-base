<?php

namespace Wallet\Business\Dtos;

use Shared\Contracts;

class RejectManualTopUpDto
{
    public int $wallet_id;
    public string $status;
    public string $reason;

    public function __construct(int $wallet_id, string $status, string $reason)
    {
        Contracts::requires($wallet_id > 0, 'Wallet ID is required!');
        Contracts::requiresInArray($status, ['rejected', 'failed'], 'Status');
        Contracts::requiresNotNullOrEmpty($reason, 'Reason');

        $this->wallet_id = $wallet_id;
        $this->status = $status;
        $this->reason = $reason;
    }
}
