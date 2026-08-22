<?php
namespace Wallet\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;
use User\Data\UserRepositoryInterface;
use Wallet\Data\WalletRepositoryInterface;

class SendManualTopUpNotificationToAdminService
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

    public function execute(string $admin_email, int $wallet_id)
    {
        $wallet = $this->walletRepository->find($wallet_id);
        $user = $this->userRepository->find($wallet->user_id);
        $to = $admin_email;
        $subject = 'Manual Top Up Notification';
        $from = 'noreply@example.com';
        $body  = '
     Hello Admin,
     <br><br>
     A new manual top up request has been submitted by ' . $user->name . ' with amount =N= ' . $wallet->amount . '.
     <br><br>
     Please review the request and approve or reject it.
     <br><br>
     Thank you for your attention.
     <br><br>';
        $this->mailService->send($to, $subject, $from, $body);
    }
}
