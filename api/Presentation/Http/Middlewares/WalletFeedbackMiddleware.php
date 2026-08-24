<?php

namespace Presentation\Http\Middlewares;

use Log\Business\Dtos\CreateLogDto;
use Log\Business\LogServiceInterface;
use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\Usecases\ApproveManualTopUpService;
use Wallet\Business\Usecases\OnlinePendingForUserService;
use Wallet\Business\Usecases\RejectManualTopUpService;
use Wallet\Data\WalletEntity;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;

class WalletFeedbackMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private PaymentServiceInterface $paymentService;
    private OnlinePendingForUserService $onlinePendingForUserService;
    private ApproveManualTopUpService $approveManualTopUpService;
    private RejectManualTopUpService $rejectManualTopUpService;
    private LogServiceInterface $logService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        PaymentServiceInterface $paymentService,
        OnlinePendingForUserService $onlinePendingForUserService,
        ApproveManualTopUpService $approveManualTopUpService,
        RejectManualTopUpService $rejectManualTopUpService,
        LogServiceInterface $logService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->paymentService = $paymentService;
        $this->onlinePendingForUserService = $onlinePendingForUserService;
        $this->approveManualTopUpService = $approveManualTopUpService;
        $this->rejectManualTopUpService = $rejectManualTopUpService;
        $this->logService = $logService;
    }

    public function handle()
    {

        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $wallets = $this->onlinePendingForUserService->query($user_id);
        /** @var WalletEntity $wallet */
        foreach ($wallets as $wallet) {
            $this->paymentService->verify($wallet->reference);
            $status = $this->paymentService->getStatus();
            $error = $this->paymentService->getError();

            $this->logService->createLog(new CreateLogDto('wallet_feedback', json_encode($wallet), json_encode([
                "status" => $status,
            ]), 'info'));

            if ($status == 'success') {
                $status = [
                    'status' => $status
                ];
                $this->logService->createLog(new CreateLogDto('wallet_feedback', json_encode($wallet), json_encode([
                    "status" => $status,
                ]), 'success'));
                $this->approveManualTopUpService->execute(new ApproveManualTopUpDto((int) $wallet->id, 'approved'));
            } else if ($status !== 'abandoned') {
                $status = [
                    'status' => $status
                ];
                $this->logService->createLog(new CreateLogDto('wallet_feedback', json_encode($wallet), json_encode([
                    "status" => $status,
                    "error" => $error,
                ]), 'error'));
                $this->rejectManualTopUpService->execute(new RejectManualTopUpDto((int) $wallet->id, 'failed', 'Payment failed'));
            }
        }
    }
}
