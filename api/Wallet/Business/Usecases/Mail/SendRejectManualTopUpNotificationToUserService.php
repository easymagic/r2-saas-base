<?php
namespace Wallet\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;
use User\Data\UserRepositoryInterface;
use Wallet\Data\WalletRepositoryInterface;

class SendRejectManualTopUpNotificationToUserService
{
    private MailServiceInterface $mailService;
    private WalletRepositoryInterface $walletRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        MailServiceInterface $mailService,
        WalletRepositoryInterface $walletRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->mailService = $mailService;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
    }

    public function execute(int $wallet_id)
    {
        $wallet = $this->walletRepository->find($wallet_id);
        $user = $this->userRepository->find($wallet->user_id);
        $to = $user->email;
        $subject = 'Top Up Wallet Notification';
        $from = 'noreply@example.com';
        $body  = '
        Hello ' . $user->name . ',
        <br><br>
        Your top up wallet request has been rejected with amount =N= ' . $wallet->amount . '.
        <br><br>
        Reason: ' . $wallet->reason . '.
        <br><br>
        Thank you for your attention.
        <br><br>';
        $this->mailService->send($to, $subject, $from, $body);
    }
}
