<?php

namespace Wallet\Business;

use Shared\AbstractBaseServiceInterface;
use Wallet\Data\WalletEntity;

/**
 * Wallet Service Interface
 * @extends AbstractBaseServiceInterface<WalletEntity>
 */
interface WalletServiceInterface extends AbstractBaseServiceInterface
{

    /**
     * Top up online
     * @param int $user_id
     * @param float $amount
     * @param string $reference
     * @param string $description
     * @param string $status
     * @return WalletEntity
     */
    public function topUpOnline(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status,
        // string $payment_url
    );

    public function log(
        int $user_id,
        float $amount,
        string $reference,
        string $type,
        string $description,
        string $status
    );

    public function topUpManual(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status,
        array $proof_of_payment_screenshot1,
        mixed $proof_of_payment_screenshot2 = [],
        mixed $proof_of_payment_screenshot3 = []
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

    public function onlinePendingForUser(int $user_id);
}
