<?php

namespace Wallet\Business;

use Shared\AbstractBaseService;
use Shared\Contracts;
use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Dtos\LogDto;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\Dtos\TopUpManualDto;
use Wallet\Business\Dtos\TopUpOnlineDto;
use Wallet\Business\WalletNotificationServiceInterface;
use User\Business\Dtos\TopUpWalletDto;
use User\Business\Usecases\TopUpWalletService;
use User\Data\UserRepositoryInterface;
use Wallet\Data\WalletRepositoryInterface;
use Exception;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Wallet\Data\WalletEntity;
use Wallet\Data\WalletMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<WalletEntity,WalletRepositoryInterface>
 */
class WalletService extends AbstractBaseService implements WalletServiceInterface
{
    private WalletRepositoryInterface $walletRepository;
    private WalletNotificationServiceInterface $walletNotificationService;
    private EnvServiceInterface $envService;
    private PaymentServiceInterface $paymentService;
    private UserRepositoryInterface $userRepository;
    private FileUploadServiceInterface $fileUploadService;
    private WalletMigrationRepositoryInterface $walletMigrationRepository;
    private TopUpWalletService $topUpWalletService;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        WalletNotificationServiceInterface $walletNotificationService,
        EnvServiceInterface $envService,
        PaymentServiceInterface $paymentService,
        UserRepositoryInterface $userRepository,
        FileUploadServiceInterface $fileUploadService,
        WalletMigrationRepositoryInterface $walletMigrationRepository,
        TopUpWalletService $topUpWalletService
    ) {
        parent::__construct($walletRepository);
        $this->walletRepository = $walletRepository;
        $this->walletNotificationService = $walletNotificationService;
        $this->envService = $envService;
        $this->paymentService = $paymentService;
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
        $this->walletMigrationRepository = $walletMigrationRepository;
        $this->topUpWalletService = $topUpWalletService;
    }

    public function topUpOnline(TopUpOnlineDto $topUpOnlineDto)
    {
        $user = $this->userRepository->find($topUpOnlineDto->user_id);
        Contracts::requireEntityFound($user, 'User');

        $this->paymentService->initiate(
            $user->email,
            $topUpOnlineDto->amount,
            $topUpOnlineDto->reference
        );

        $payment_url = $this->paymentService->getAuthUrl();

        return $this->walletRepository->save(new WalletEntity([
            'user_id' => $topUpOnlineDto->user_id,
            'amount' => $topUpOnlineDto->amount,
            'reference' => $topUpOnlineDto->reference,
            'type' => 'online',
            'description' => $topUpOnlineDto->description,
            'status' => 'pending',
            'payment_url' => $payment_url,
        ]));
    }

    public function topUpManual(TopUpManualDto $topUpManualDto)
    {
        $user = $this->userRepository->find($topUpManualDto->user_id);
        Contracts::requireEntityFound($user, 'User');

        $path = '/uploads/proof_of_payment_screenshot';
        $full_path = __DIR__ . '/../../';

        $proof1 = $this->fileUploadService->uploadFile($topUpManualDto->proof_of_payment_screenshot1, $path, $full_path);
        $proof2 = $this->fileUploadService->uploadFile($topUpManualDto->proof_of_payment_screenshot2, $path, $full_path);
        $proof3 = $this->fileUploadService->uploadFile($topUpManualDto->proof_of_payment_screenshot3, $path, $full_path);

        if (!$proof1) {
            throw new Exception('Failed to upload proof of payment screenshot 1!');
        }

        $wallet = $this->walletRepository->save(new WalletEntity([
            'user_id' => $topUpManualDto->user_id,
            'amount' => $topUpManualDto->amount,
            'reference' => $topUpManualDto->reference,
            'type' => 'manual',
            'description' => $topUpManualDto->description,
            'status' => $topUpManualDto->status,
            'proof_of_payment_screenshot1' => $proof1,
            'proof_of_payment_screenshot2' => $proof2 ?: '',
            'proof_of_payment_screenshot3' => $proof3 ?: '',
        ]));

        $admin_email = $this->envService->get('ADMIN_EMAIL');
        $this->walletNotificationService->sendManualTopUpNotificationToAdmin($admin_email, $wallet->id);

        return $wallet;
    }

    public function approveManualTopUp(ApproveManualTopUpDto $approveManualTopUpDto)
    {
        $wallet = $this->walletRepository->find($approveManualTopUpDto->wallet_id);
        Contracts::requireEntityFound($wallet, 'Wallet');

        $wallet->status = $approveManualTopUpDto->status;
        $this->walletRepository->save($wallet);

        $this->topUpWalletService->execute(new TopUpWalletDto(
            (int) $wallet->user_id,
            (float) $wallet->amount
        ));

        $this->walletNotificationService->sendApproveManualTopUpNotificationToUser($wallet->id);

        return $wallet;
    }

    public function rejectManualTopUp(RejectManualTopUpDto $rejectManualTopUpDto)
    {
        $wallet = $this->walletRepository->find($rejectManualTopUpDto->wallet_id);
        Contracts::requireEntityFound($wallet, 'Wallet');

        $wallet->status = $rejectManualTopUpDto->status;
        $wallet->reason = $rejectManualTopUpDto->reason;
        $this->walletRepository->save($wallet);

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
            'online' => true,
            'status' => 'pending',
            'user_id' => $user_id,
        ])->fetchAll();
    }

    public function log(LogDto $logDto)
    {
        return $this->walletRepository->save(new WalletEntity([
            'user_id' => $logDto->user_id,
            'amount' => $logDto->amount,
            'reference' => $logDto->reference,
            'type' => $logDto->type,
            'description' => $logDto->description,
            'status' => $logDto->status,
        ]));
    }
}
