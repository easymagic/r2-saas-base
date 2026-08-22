<?php
namespace SnappyOrder\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class NotifyCustomerOfPriceChangeService
{
    private MailServiceInterface $mailService;
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private UserRepositoryInterface $userRepository;
    private BaseMailThemeInterface $baseMailTheme;
    private SnappyOrderMailTemplate $snappyOrderMailTemplate;
    private EnvServiceInterface $envService;

    public function __construct(
        MailServiceInterface $mailService,
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        UserRepositoryInterface $userRepository,
        BaseMailThemeInterface $baseMailTheme,
        SnappyOrderMailTemplate $snappyOrderMailTemplate,
        EnvServiceInterface $envService
    ) {
        $this->mailService = $mailService;
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->userRepository = $userRepository;
        $this->baseMailTheme = $baseMailTheme;
        $this->snappyOrderMailTemplate = $snappyOrderMailTemplate;
        $this->envService = $envService;
    }

    public function execute(int $order_id, float $price)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Price Updated';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->snappyOrderMailTemplate->greeting($user->name)
            . $this->snappyOrderMailTemplate->intro('Your order price has been adjusted. Log in to view the updated amount and complete payment.')
            . $this->snappyOrderMailTemplate->highlightBox('New amount (USD)', '$ ' . number_format($price, 2))
            . $this->snappyOrderMailTemplate->orderDetailsCard($order, true)
            . $this->snappyOrderMailTemplate->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->from(), $body);
    }

    private function from(): string
    {
        return $this->envService->get('NOREPLY_EMAIL');
    }
}
