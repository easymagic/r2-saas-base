<?php

namespace Application\Wallet;

use Application\MailNotifications\Wallet\WalletNotificationServiceInterface;
use Domain\User\UserRepositoryInterface;
use Domain\Wallet\WalletRepositoryInterface;
use Exception;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Domain\Wallet\WalletEntity;

class WalletService implements WalletServiceInterface
{
    private WalletRepositoryInterface $walletRepository;
    private WalletNotificationServiceInterface $walletNotificationService;
    private WalletValidationServiceInterface $walletValidationService;
    private EnvServiceInterface $envService;
    private PaymentServiceInterface $paymentService;
    private UserRepositoryInterface $userRepository;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(
        WalletValidationServiceInterface $walletValidationService,
        WalletRepositoryInterface $walletRepository,
        WalletNotificationServiceInterface $walletNotificationService,
        EnvServiceInterface $envService,
        PaymentServiceInterface $paymentService,
        UserRepositoryInterface $userRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->walletValidationService = $walletValidationService;
        $this->walletRepository = $walletRepository;
        $this->walletNotificationService = $walletNotificationService;
        $this->envService = $envService;
        $this->paymentService = $paymentService;
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
    }


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
    ) {
        $this->walletValidationService->validateTopUpOnline(
            $user_id,
            $amount,
            $reference,
            $type,
            $description,
            $status
        );

        $user = $this->userRepository->find($user_id);

        $email = $user->email;

        $this->paymentService->initiate(
            $email,
            $amount,
            $reference
        );

        $payment_url = $this->paymentService->getAuthUrl();

        $wallet = $this->walletRepository->save(0, [
            'user_id' => $user_id,
            'amount' => $amount,
            'reference' => $reference,
            'type' => $type,
            'description' => $description,
            'status' => "pending",
            'payment_url' => $payment_url
        ]);

        return $wallet;
    }

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
    ) {
        $this->walletValidationService->validateTopUpManual(
            $user_id,
            $amount,
            $reference,
            $type,
            $description,
            $status,
            $proof_of_payment_screenshot1,
            $proof_of_payment_screenshot2,
            $proof_of_payment_screenshot3
        );

        $user = $this->userRepository->find($user_id);

        $email = $user->email;

        $this->paymentService->initiate(
            $email,
            $amount,
            $reference
        );

        $proof_of_payment_screenshot1 = $this->fileUploadService->uploadFile($proof_of_payment_screenshot1, 'proof_of_payment_screenshot1');
        $proof_of_payment_screenshot2 = $this->fileUploadService->uploadFile($proof_of_payment_screenshot2, 'proof_of_payment_screenshot2');
        $proof_of_payment_screenshot3 = $this->fileUploadService->uploadFile($proof_of_payment_screenshot3, 'proof_of_payment_screenshot3');

        if (!$proof_of_payment_screenshot1){
            throw new Exception('Failed to upload proof of payment screenshot 1!');
        }

        if (!$proof_of_payment_screenshot2){
            $proof_of_payment_screenshot2 = '';
        }

        if (!$proof_of_payment_screenshot3){
            $proof_of_payment_screenshot3 = '';
        }

        $wallet = $this->walletRepository->save(0, [
            'user_id' => $user_id,
            'amount' => $amount,
            'reference' => $reference,
            'type' => $type,
            'description' => $description,
            'status' => $status,
            'proof_of_payment_screenshot1' => $proof_of_payment_screenshot1,
            'proof_of_payment_screenshot2' => $proof_of_payment_screenshot2,
            'proof_of_payment_screenshot3' => $proof_of_payment_screenshot3
        ]);

        $admin_email = $this->envService->get('ADMIN_EMAIL');

        $this->walletNotificationService->sendManualTopUpNotificationToAdmin($admin_email, $wallet->id);

        return $wallet;
    }

    public function approveManualTopUp(
        int $wallet_id,
        string $status
    ) {
        $this->walletValidationService->validateApproveManualTopUp(
            $wallet_id,
            $status
        );

        $wallet = $this->walletRepository->find($wallet_id);

        $this->walletRepository->save($wallet_id, [
            "status" => $status,
        ]);

        $this->walletNotificationService->sendApproveManualTopUpNotificationToUser($wallet->id);

        return $wallet;
    }

    public function rejectManualTopUp(
        int $wallet_id,
        string $status,
        string $reason
    ) {
        $this->walletValidationService->validateRejectManualTopUp(
            $wallet_id,
            $status,
            $reason
        );

        $wallet = $this->walletRepository->find($wallet_id);

        $this->walletRepository->save($wallet_id, [
            "status" => $status,
            "reason" => $reason
        ]);

        $this->walletNotificationService->sendRejectManualTopUpNotificationToUser($wallet->id);

        return $wallet;
    }
}
