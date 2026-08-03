<?php

namespace Application\MailNotifications;

use Application\MailNotifications\Base\BaseMailThemeInterface;
use Domain\User\UserRepositoryInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;

class AccountMailNotificationService implements AccountMailNotificationServiceInterface
{

    private MailServiceInterface $mailService;
    private UserRepositoryInterface $userRepository;
    private BaseMailThemeInterface $baseMailTheme;

    private string $fromEmail = "noreply@example.com";

    public function __construct(
        MailServiceInterface $mailService,
        UserRepositoryInterface $userRepository,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->mailService = $mailService;
        $this->userRepository = $userRepository;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function sendAccountVerifyOtpToUser(int $accountId)
    {
        $user = $this->userRepository->find($accountId);
        $subject = 'Account Verify OTP';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Use the one-time password below to verify your account.')
            . $this->otpBox($user->otp, 'Verification OTP')
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->fromEmail, $body);
    }

    public function sendAccountForgotPasswordOtpToUser(int $accountId)
    {
        $user = $this->userRepository->find($accountId);
        $subject = 'Account Forgot Password OTP';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('You requested a password reset. Use the one-time password below to continue.')
            . $this->otpBox($user->otp, 'Password reset OTP')
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->fromEmail, $body);
    }

    public function sendAccountTopUpWalletToUser(int $accountId, float $amount)
    {
        $user = $this->userRepository->find($accountId);
        $subject = 'Account Top Up Wallet';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Your wallet has been credited successfully.')
            . $this->amountBox('Amount credited', $amount, true)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->fromEmail, $body);
    }

    public function sendAccountWithdrawWalletToUser(int $accountId, float $amount)
    {
        $user = $this->userRepository->find($accountId);
        $subject = 'Account Withdraw Wallet';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Your wallet has been debited successfully.')
            . $this->amountBox('Amount debited', $amount, false)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->fromEmail, $body);
    }

    private function e(string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function greeting(string $name): string
    {
        return '<p style="margin:0 0 16px 0;font-size:18px;font-weight:bold;color:#0f172a;">Hello '
            . $this->e($name) . ',</p>';
    }

    private function intro(string $html): string
    {
        return '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . $html . '</p>';
    }

    private function signOff(): string
    {
        return '<p style="margin:24px 0 0 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Thank you for using our service.<br><br>'
            . 'Best regards,<br><strong style="color:#0f172a;">The Team</strong>'
            . '</p>';
    }

    private function otpBox($otp, string $label): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px 0;">'
            . '<tr><td align="center" style="background-color:#0f766e;border-radius:10px;padding:20px 16px;">'
            . '<span style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#ccfbf1;margin-bottom:8px;">'
            . $this->e($label) . '</span>'
            . '<span style="display:block;font-family:Georgia,\'Times New Roman\',serif;font-size:32px;font-weight:bold;letter-spacing:6px;color:#ffffff;">'
            . $this->e($otp) . '</span>'
            . '</td></tr></table>';
    }

    private function amountBox(string $label, float $amount, bool $credit): string
    {
        $bg = $credit ? '#f0fdfa' : '#fffbeb';
        $border = $credit ? '#99f6e4' : '#fde68a';
        $labelColor = $credit ? '#0f766e' : '#b45309';
        $prefix = $credit ? '+' : '-';

        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px 0;">'
            . '<tr><td style="background-color:' . $bg . ';border:1px solid ' . $border . ';border-radius:10px;padding:16px;">'
            . '<span style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.06em;color:' . $labelColor . ';font-weight:bold;margin-bottom:6px;">'
            . $this->e($label) . '</span>'
            . '<span style="display:block;font-size:24px;font-weight:bold;color:#0f172a;">'
            . $prefix . ' ₦ ' . $this->e(number_format($amount, 2)) . '</span>'
            . '</td></tr></table>';
    }
}
