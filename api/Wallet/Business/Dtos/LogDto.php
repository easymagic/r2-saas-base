<?php

namespace Wallet\Business\Dtos;

use Shared\Contracts;

class LogDto
{
    public int $user_id;
    public float $amount;
    public string $reference;
    public string $type;
    public string $description;
    public string $status;

    public function __construct(
        int $user_id,
        float $amount,
        string $reference,
        string $type,
        string $description,
        string $status
    ) {
        Contracts::requires($user_id > 0, 'User ID is required!');
        Contracts::requiresNotNullOrEmpty($reference, 'Reference');
        Contracts::requiresNotNullOrEmpty($type, 'Type');
        Contracts::requiresNotNullOrEmpty($status, 'Status');

        $this->user_id = $user_id;
        $this->amount = $amount;
        $this->reference = $reference;
        $this->type = $type;
        $this->description = $description;
        $this->status = $status;
    }
}
