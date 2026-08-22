<?php
namespace User\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use User\Data\UserRepositoryInterface;

class SendAccountForgotPasswordOtpToUserService
{
    private MailServiceInterface $mailService;
    private UserRepositoryInterface $userRepository;
    private BaseMailThemeInterface $baseMailTheme;
    private AccountMailTemplate $accountMailTemplate;
    private string $fromEmail = "noreply@example.com";

    public function __construct(
        MailServiceInterface $mailService,
        UserRepositoryInterface $userRepository,
        BaseMailThemeInterface $baseMailTheme,
        AccountMailTemplate $accountMailTemplate
    ) {
        $this->mailService = $mailService;
        $this->userRepository = $userRepository;
        $this->baseMailTheme = $baseMailTheme;
        $this->accountMailTemplate = $accountMailTemplate;
    }

    public function execute(int $accountId)
    {
        $user = $this->userRepository->find($accountId);
        $subject = 'Account Forgot Password OTP';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->accountMailTemplate->greeting($user->name)
            . $this->accountMailTemplate->intro('You requested a password reset. Use the one-time password below to continue.')
            . $this->accountMailTemplate->otpBox($user->otp, 'Password reset OTP')
            . $this->accountMailTemplate->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->fromEmail, $body);
    }
}
