<?php

namespace Application\Wallet;

use Domain\Wallet\WalletEntity;

interface WalletServiceInterface
{

    /**
     * Top up online
     * @param int $user_id
     * @param float $amount
     * @param string $reference
     * @param string $type
     * @param string $description
     * @param string $status
     * @return WalletEntity
     */
    public function topUpOnline(
        int $user_id,
        float $amount,
        string $reference,
        string $type,
        string $description,
        string $status,
        string $payment_url
    );

    public function topUpManual(
        int $user_id,
        float $amount,
        string $reference,
        string $type,
        string $description,
        string $status,
        array $proof_of_payment_screenshot1,
        array $proof_of_payment_screenshot2 = [],
        array $proof_of_payment_screenshot3 = []
    );

    public function approveManualTopUp(
        int $wallet_id,
        string $status
    );

    public function rejectManualTopUp(
        int $wallet_id,
        string $status,
        string $reason
    );

    public function migrate();
}
