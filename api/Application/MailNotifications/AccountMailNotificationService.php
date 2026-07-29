<?php

namespace Application\MailNotifications;

use Domain\User\UserRepositoryInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;

class AccountMailNotificationService implements AccountMailNotificationServiceInterface
{

    private MailServiceInterface $mailService;
    private UserRepositoryInterface $userRepository;

    private string $fromEmail = "noreply@example.com";

    public function __construct(
        MailServiceInterface $mailService,
        UserRepositoryInterface $userRepository
    ) {
        $this->mailService = $mailService;
        $this->userRepository = $userRepository;
    }

    public function sendAccountVerifyOtpToUser(int $accountId) {
        $user = $this->userRepository->find($accountId);
        $to = $user->email;
        $subject = 'Account Verify OTP';
        $from = $this->fromEmail;
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your account verify OTP is ' . $user->otp . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendAccountForgotPasswordOtpToUser(int $accountId) {
        $user = $this->userRepository->find($accountId);
        $to = $user->email;
        $subject = 'Account Forgot Password OTP';
        $from = $this->fromEmail;
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your account forgot password OTP is ' . $user->otp . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendAccountTopUpWalletToUser(int $accountId, float $amount) {
        $user = $this->userRepository->find($accountId);
        $to = $user->email;
        $subject = 'Account Top Up Wallet';
        $from = $this->fromEmail;
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your account has been successfully credited with amount ' . $amount . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }

    public function sendAccountWithdrawWalletToUser(int $accountId, float $amount) {
        $user = $this->userRepository->find($accountId);
        $to = $user->email;
        $subject = 'Account Withdraw Wallet';
        $from = $this->fromEmail;
        $name = $user->name;
        $body = '
        Hello ' . $name . ',
        <br><br>Your account has been successfully debited with amount ' . $amount . '<br>
        <br>Thank you for using our service.<br><br>Best regards,<br>The Team';
        $this->mailService->send($to, $subject, $from, $body);
    }
}
