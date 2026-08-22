<?php

namespace Presentation\Http\Middlewares;

use Log\Business\Dtos\CreateLogDto;
use Log\Business\LogServiceInterface;
use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\WalletServiceInterface;
use Wallet\Data\WalletEntity;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;

class WalletFeedbackMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private PaymentServiceInterface $paymentService;
    private WalletServiceInterface $walletService;
    private LogServiceInterface $logService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        PaymentServiceInterface $paymentService,
        WalletServiceInterface $walletService,
        LogServiceInterface $logService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
        $this->logService = $logService;
    }

    public function handle()
    {

        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $wallets = $this->walletService->onlinePendingForUser($user_id);
        // print_r($wallets);
        /** @var WalletEntity $wallet */
        foreach ($wallets as $wallet) {
            $this->paymentService->verify($wallet->reference);
            $status = $this->paymentService->getStatus();
            $error = $this->paymentService->getError();

            // echo $status;

            // if (empty($status)){
            $this->logService->createLog(new CreateLogDto('wallet_feedback', json_encode($wallet), json_encode([
                "status" => $status,
            ]), 'info'));
            // }

            if ($status == 'success') {
                $status = [
                    'status' => $status
                ];
                $this->logService->createLog(new CreateLogDto('wallet_feedback', json_encode($wallet), json_encode([
                    "status" => $status,
                ]), 'success'));
                $this->walletService->approveManualTopUp(new ApproveManualTopUpDto((int) $wallet->id, 'approved'));
            } else if ($status !== 'abandoned') {
                $status = [
                    'status' => $status
                ];
                $this->logService->createLog(new CreateLogDto('wallet_feedback', json_encode($wallet), json_encode([
                    "status" => $status,
                    "error" => $error,
                ]), 'error'));
                $this->walletService->rejectManualTopUp(new RejectManualTopUpDto((int) $wallet->id, 'failed', 'Payment failed'));
            }
        }
    }
}
