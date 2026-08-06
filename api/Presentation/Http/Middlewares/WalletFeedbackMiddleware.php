<?php

namespace Presentation\Http\Middlewares;


use Business\Wallet\WalletServiceInterface;
use Data\Wallet\WalletEntity;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;

class WalletFeedbackMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private PaymentServiceInterface $paymentService;
    private WalletServiceInterface $walletService;


    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        PaymentServiceInterface $paymentService,
        WalletServiceInterface $walletService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
    }

    public function handle() {

        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $wallets = $this->walletService->onlinePendingForUser($user_id);
        // print_r($wallets);
        /** @var WalletEntity $wallet */
        foreach ($wallets as $wallet) {
            $this->paymentService->verify($wallet->reference);
            $status = $this->paymentService->getStatus();
            if ($status == 'success') {
                $this->walletService->approveManualTopUp($wallet->id, 'approved');
            } else {
                $this->walletService->rejectManualTopUp($wallet->id, 'failed', 'Payment failed');
            }
        }
    }
}
