<?php

namespace Wallet\Business;

interface WalletValidationServiceInterface
{

    public function validateTopUpOnline(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status
    );

    public function validateTopUpManual(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status,
        array $proof_of_payment_screenshot1,
        array $proof_of_payment_screenshot2 = [],
        array $proof_of_payment_screenshot3 = []
    );

    public function validateApproveManualTopUp(
        int $wallet_id,
        string $status
    );

    public function validateRejectManualTopUp(
        int $wallet_id,
        string &$status,
        string $reason
    );
}
