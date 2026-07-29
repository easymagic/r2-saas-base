<?php

namespace Application\MailNotifications;

interface AccountMailNotificationServiceInterface
{
    public function sendAccountVerifyOtpToUser(int $accountId);

    public function sendAccountForgotPasswordOtpToUser(int $accountId);

    public function sendAccountTopUpWalletToUser(int $accountId, float $amount);

    public function sendAccountWithdrawWalletToUser(int $accountId, float $amount);
}