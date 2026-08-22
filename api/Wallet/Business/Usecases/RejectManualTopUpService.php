<?php
namespace Wallet\Business\Usecases;

use Shared\Contracts;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\Usecases\Mail\SendRejectManualTopUpNotificationToUserService;
use Wallet\Data\WalletRepositoryInterface;

class RejectManualTopUpService
{
    private WalletRepositoryInterface $walletRepository;
    private SendRejectManualTopUpNotificationToUserService $sendRejectManualTopUpNotificationToUserService;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        SendRejectManualTopUpNotificationToUserService $sendRejectManualTopUpNotificationToUserService
    ) {
        $this->walletRepository = $walletRepository;
        $this->sendRejectManualTopUpNotificationToUserService = $sendRejectManualTopUpNotificationToUserService;
    }

    public function execute(RejectManualTopUpDto $rejectManualTopUpDto)
    {
        $wallet = $this->walletRepository->find($rejectManualTopUpDto->wallet_id);
        Contracts::requireEntityFound($wallet, 'Wallet');

        $wallet->status = $rejectManualTopUpDto->status;
        $wallet->reason = $rejectManualTopUpDto->reason;
        $this->walletRepository->save($wallet);

        $this->sendRejectManualTopUpNotificationToUserService->execute($wallet->id);

        return $wallet;
    }
}
