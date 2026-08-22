<?php

namespace Wallet\Business\Dtos;

use Shared\Contracts;

class TopUpOnlineDto
{
    public int $user_id;
    public float $amount;
    public string $reference;
    public string $description;
    public string $status;

    public function __construct(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status
    ) {
        Contracts::requires($user_id > 0, 'User ID is required!');
        Contracts::requires($amount > 0, 'Amount is required!');
        Contracts::requiresNotNullOrEmpty($reference, 'Reference');
        Contracts::requiresNotNullOrEmpty($description, 'Description');
        Contracts::requiresInArray($status, ['pending', 'approved', 'rejected'], 'Status');

        $this->user_id = $user_id;
        $this->amount = $amount;
        $this->reference = $reference;
        $this->description = $description;
        $this->status = $status;
    }
}
