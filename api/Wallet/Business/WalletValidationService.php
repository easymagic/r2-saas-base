<?php

namespace Wallet\Business;

use Exception;

class WalletValidationService implements WalletValidationServiceInterface
{

    public function validateTopUpOnline(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status
    ) {
        if (empty($user_id)){
            throw new Exception('User ID is required!');
        }
        if (empty($amount)){
            throw new Exception('Amount is required!');
        }
        if (empty($reference)){
            throw new Exception('Reference is required!');
        }
        if (empty($description)){
            throw new Exception('Description is required!');
        }
        if (empty($status)){
            throw new Exception('Status is required!');
        }
        if (!in_array($status, ['pending', 'approved', 'rejected'])){
            throw new Exception('Status is invalid!');
        }
        if (!in_array($status, ['pending', 'approved', 'rejected'])){
            throw new Exception('Status is invalid!');
        }
    }

    public function validateTopUpManual(
        int $user_id,
        float $amount,
        string $reference,
        string $description,
        string $status,
        array $proof_of_payment_screenshot1,
        array $proof_of_payment_screenshot2 = [],
        array $proof_of_payment_screenshot3 = []
    ) {
        if (empty($user_id)){
            throw new Exception('User ID is required!');
        }
        if (empty($amount)){
            throw new Exception('Amount is required!');
        }
        if (empty($reference)){
            throw new Exception('Reference is required!');
        }
        if (empty($description)){
            throw new Exception('Description is required!');
        }
        if (empty($status)){
            throw new Exception('Status is required!');
        }
        if (!in_array($status, ['pending', 'approved', 'rejected'])){
            throw new Exception('Status is invalid!');
        }
        if (empty($proof_of_payment_screenshot1)){
            throw new Exception('Proof of payment screenshot 1 is required!');
        }
    }

    public function validateApproveManualTopUp(
        int $wallet_id,
        string $status
    ) {
        if (empty($wallet_id)){
            throw new Exception('Wallet ID is required!');
        }
        if (empty($status)){
            throw new Exception('Status is required!');
        }
        if (!in_array($status, ['approved'])){
            throw new Exception('Status is invalid!');
        }
    }

    public function validateRejectManualTopUp(
        int $wallet_id,
        string &$status,
        string $reason
    ) {
        if (empty($wallet_id)){
            throw new Exception('Wallet ID is required!');
        }
        if (empty($status)){
            throw new Exception('Status is required!');
        }
        if (!in_array($status, ['rejected', 'failed'])){
            throw new Exception('Status is invalid!');
        }
        if (empty($reason)){
            throw new Exception('Reason is required!');
        }
    }
}
