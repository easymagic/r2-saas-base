<?php
namespace Wallet\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\TopUpWalletDto;
use User\Business\Usecases\TopUpWalletService;
use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Usecases\Mail\SendApproveManualTopUpNotificationToUserService;
use Wallet\Data\WalletRepositoryInterface;

class ApproveManualTopUpService
{
    private WalletRepositoryInterface $walletRepository;
    private TopUpWalletService $topUpWalletService;
    private SendApproveManualTopUpNotificationToUserService $sendApproveManualTopUpNotificationToUserService;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        TopUpWalletService $topUpWalletService,
        SendApproveManualTopUpNotificationToUserService $sendApproveManualTopUpNotificationToUserService
    ) {
        $this->walletRepository = $walletRepository;
        $this->topUpWalletService = $topUpWalletService;
        $this->sendApproveManualTopUpNotificationToUserService = $sendApproveManualTopUpNotificationToUserService;
    }

    public function execute(ApproveManualTopUpDto $approveManualTopUpDto)
    {
        $wallet = $this->walletRepository->find($approveManualTopUpDto->wallet_id);
        Contracts::requireEntityFound($wallet, 'Wallet');

        $wallet->status = $approveManualTopUpDto->status;
        $this->walletRepository->save($wallet);

        $this->topUpWalletService->execute(new TopUpWalletDto(
            (int) $wallet->user_id,
            (float) $wallet->amount
        ));

        $this->sendApproveManualTopUpNotificationToUserService->execute($wallet->id);

        return $wallet;
    }
}
