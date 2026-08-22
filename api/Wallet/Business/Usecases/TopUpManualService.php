<?php
namespace Wallet\Business\Usecases;

use Exception;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;
use Wallet\Business\Dtos\TopUpManualDto;
use Wallet\Business\Usecases\Mail\SendManualTopUpNotificationToAdminService;
use Wallet\Data\WalletEntity;
use Wallet\Data\WalletRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;

class TopUpManualService
{
    private WalletRepositoryInterface $walletRepository;
    private UserRepositoryInterface $userRepository;
    private FileUploadServiceInterface $fileUploadService;
    private EnvServiceInterface $envService;
    private SendManualTopUpNotificationToAdminService $sendManualTopUpNotificationToAdminService;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        UserRepositoryInterface $userRepository,
        FileUploadServiceInterface $fileUploadService,
        EnvServiceInterface $envService,
        SendManualTopUpNotificationToAdminService $sendManualTopUpNotificationToAdminService
    ) {
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
        $this->envService = $envService;
        $this->sendManualTopUpNotificationToAdminService = $sendManualTopUpNotificationToAdminService;
    }

    public function execute(TopUpManualDto $topUpManualDto)
    {
        $user = $this->userRepository->find($topUpManualDto->user_id);
        Contracts::requireEntityFound($user, 'User');

        $path = '/uploads/proof_of_payment_screenshot';
        $full_path = __DIR__ . '/../../../';

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
        $this->sendManualTopUpNotificationToAdminService->execute($admin_email, $wallet->id);

        return $wallet;
    }
}
