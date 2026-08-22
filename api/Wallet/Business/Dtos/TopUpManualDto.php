<?php

namespace Wallet\Business\Dtos;

use Shared\Contracts;

class TopUpManualDto
{
    public int $user_id;
    public float $amount;
    public string $reference;
    public string $description;
    public string $status;
    public array $proof_of_payment_screenshot1;
    public mixed $proof_of_payment_screenshot2;
    public mixed $proof_of_payment_screenshot3;

    public function __construct(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status,
        array $proof_of_payment_screenshot1,
        mixed $proof_of_payment_screenshot2,
        mixed $proof_of_payment_screenshot3
    ) {
        Contracts::requires($user_id > 0, 'User ID is required!');
        Contracts::requires($amount > 0, 'Amount is required!');
        Contracts::requiresNotNullOrEmpty($reference, 'Reference');
        Contracts::requiresNotNullOrEmpty($description, 'Description');
        Contracts::requiresInArray($status, ['pending', 'approved', 'rejected'], 'Status');
        Contracts::requires(!empty($proof_of_payment_screenshot1), 'Proof of payment screenshot 1 is required!');

        $this->user_id = $user_id;
        $this->amount = $amount;
        $this->reference = $reference;
        $this->description = $description;
        $this->status = $status;
        $this->proof_of_payment_screenshot1 = $proof_of_payment_screenshot1;
        $this->proof_of_payment_screenshot2 = $proof_of_payment_screenshot2;
        $this->proof_of_payment_screenshot3 = $proof_of_payment_screenshot3;
    }
}
