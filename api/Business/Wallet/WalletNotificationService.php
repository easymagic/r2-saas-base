<?php

namespace Business\Wallet;

use Data\User\UserRepositoryInterface;
use Data\Wallet\WalletRepositoryInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;

class WalletNotificationService implements WalletNotificationServiceInterface
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

    public function sendManualTopUpNotificationToAdmin(string $admin_email, int $wallet_id)
    {
        $wallet = $this->walletRepository->find($wallet_id);
        $user = $this->userRepository->find($wallet->user_id);
        $to = $admin_email;
        $subject = 'Manual Top Up Notification';
        $body = 'Manual Top Up Notification to Admin';
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

    public function sendManualTopUpNotificationToUser(int $wallet_id)
    {
        $wallet = $this->walletRepository->find($wallet_id);
        $user = $this->userRepository->find($wallet->user_id);
        $to = $user->email;
        $subject = 'Manual Top Up Notification';
        $from = 'noreply@example.com';
        $body  = '
        Hello ' . $user->name . ',
        <br><br>
        Your manual top up request has been submitted with amount =N= ' . $wallet->amount . '.
        <br><br>
        Please wait for the admin to review your request.
        <br><br>
        Thank you for your attention.
        <br><br>';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendApproveManualTopUpNotificationToUser(int $wallet_id)
    {
        $wallet = $this->walletRepository->find($wallet_id);
        $user = $this->userRepository->find($wallet->user_id);
        $to = $user->email;
        $subject = 'Top Up Wallet Notification';
        $from = 'noreply@example.com';
        $body  = '
        Hello ' . $user->name . ',
        <br><br>
        Your top up wallet request has been approved with amount =N= ' . $wallet->amount . '.
        <br><br>
        Thank you for your attention.
        <br><br>';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendRejectManualTopUpNotificationToUser(int $wallet_id)
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
