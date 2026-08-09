<?php

namespace Wallet\Business;

use Shared\AbstractBaseService;
use Wallet\Business\WalletServiceInterface;
use Wallet\Business\WalletNotificationServiceInterface;
use User\Business\UserServiceInterface;
use User\Data\UserRepositoryInterface;
use Wallet\Data\WalletRepositoryInterface;
use Exception;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Wallet\Data\WalletEntity;
use Wallet\Data\WalletMigrationRepositoryInterface;

/**
 * Wallet Service
 * @extends AbstractBaseService<WalletEntity,WalletRepositoryInterface>
 */
class WalletService extends AbstractBaseService implements WalletServiceInterface
{
    private WalletRepositoryInterface $walletRepository;
    private WalletNotificationServiceInterface $walletNotificationService;
    private WalletValidationServiceInterface $walletValidationService;
    private EnvServiceInterface $envService;
    private PaymentServiceInterface $paymentService;
    private UserRepositoryInterface $userRepository;
    private FileUploadServiceInterface $fileUploadService;
    private WalletMigrationRepositoryInterface $walletMigrationRepository;

    private UserServiceInterface $userService;

    public function __construct(
        WalletValidationServiceInterface $walletValidationService,
        WalletRepositoryInterface $walletRepository,
        WalletNotificationServiceInterface $walletNotificationService,
        EnvServiceInterface $envService,
        PaymentServiceInterface $paymentService,
        UserRepositoryInterface $userRepository,
        FileUploadServiceInterface $fileUploadService,
        WalletMigrationRepositoryInterface $walletMigrationRepository,
        UserServiceInterface $userService
    ) {
        parent::__construct($walletRepository);
        $this->walletValidationService = $walletValidationService;
        $this->walletRepository = $walletRepository;
        $this->walletNotificationService = $walletNotificationService;
        $this->envService = $envService;
        $this->paymentService = $paymentService;
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
        $this->walletMigrationRepository = $walletMigrationRepository;
        $this->userService = $userService;
    }


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
    ) {
        // die('top up online');
        $this->walletValidationService->validateTopUpOnline(
            $user_id,
            $amount,
            $reference,
            $description,
            $status
        );
        // die('top up online2');

        $user = $this->userRepository->find($user_id);

        // die("test");

        $email = $user->email;

        $this->paymentService->initiate(
            $email,
            $amount,
            $reference
        );

        // die('test2');

        $payment_url = $this->paymentService->getAuthUrl();

        $wallet = $this->walletRepository->save(0, [
            'user_id' => $user_id,
            'amount' => $amount,
            'reference' => $reference,
            'type' => 'online',
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
        string $description,
        string $status,
        array $proof_of_payment_screenshot1,
        mixed $proof_of_payment_screenshot2,
        mixed $proof_of_payment_screenshot3
    ) {
        $this->walletValidationService->validateTopUpManual(
            $user_id,
            $amount,
            $reference,
            $description,
            $status,
            $proof_of_payment_screenshot1,
            $proof_of_payment_screenshot2,
            $proof_of_payment_screenshot3
        );

        $user = $this->userRepository->find($user_id);

        $email = $user->email;

        // $this->paymentService->initiate(
        //     $email,
        //     $amount,
        //     $reference
        // );

        $path = '/uploads/proof_of_payment_screenshot';
        $full_path = __DIR__ . '/../../';

        $proof_of_payment_screenshot1 = $this->fileUploadService->uploadFile($proof_of_payment_screenshot1, $path, $full_path);
        $proof_of_payment_screenshot2 = $this->fileUploadService->uploadFile($proof_of_payment_screenshot2, $path, $full_path);
        $proof_of_payment_screenshot3 = $this->fileUploadService->uploadFile($proof_of_payment_screenshot3, $path, $full_path);

        if (!$proof_of_payment_screenshot1) {
            throw new Exception('Failed to upload proof of payment screenshot 1!');
        }

        if (!$proof_of_payment_screenshot2) {
            $proof_of_payment_screenshot2 = '';
        }

        if (!$proof_of_payment_screenshot3) {
            $proof_of_payment_screenshot3 = '';
        }

        $wallet = $this->walletRepository->save(0, [
            'user_id' => $user_id,
            'amount' => $amount,
            'reference' => $reference,
            'type' => 'manual',
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

        // echo "got here " . $status;

        // top up wallet
        $this->userService->topUpWallet($wallet->user_id, $wallet->amount);

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

    public function migrate()
    {
        return $this->walletMigrationRepository->migrate();
    }

    public function onlinePendingForUser(int $user_id)
    {
        return $this->walletRepository->filter([
            "online" => true,
            "status" => "pending",
            "user_id" => $user_id
        ])->fetchAll();
    }

    public function log(
        int $user_id,
        float $amount,
        string $reference,
        string $type,
        string $description,
        string $status
    ) {
        return $this->walletRepository->save(0,[
            'user_id' => $user_id,
            'amount' => $amount,
            'reference' => $reference,
            'type' => $type,
            'description' => $description,
            'status' => $status,
        ]);
    }
}
