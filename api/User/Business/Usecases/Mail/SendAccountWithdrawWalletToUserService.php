<?php
namespace User\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use User\Data\UserRepositoryInterface;

class SendAccountWithdrawWalletToUserService
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

    public function execute(int $accountId, float $amount)
    {
        $user = $this->userRepository->find($accountId);
        $subject = 'Account Withdraw Wallet';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->accountMailTemplate->greeting($user->name)
            . $this->accountMailTemplate->intro('Your wallet has been debited successfully.')
            . $this->accountMailTemplate->amountBox('Amount debited', $amount, false)
            . $this->accountMailTemplate->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->fromEmail, $body);
    }
}
