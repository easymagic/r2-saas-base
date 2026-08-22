<?php
namespace SnappyOrder\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class NotifyCustomerOfOrderPaymentService
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

    public function execute(int $order_id)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Paid';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->snappyOrderMailTemplate->greeting($user->name)
            . $this->snappyOrderMailTemplate->intro('Your order has been paid successfully. Thank you!')
            . $this->snappyOrderMailTemplate->statusBanner('Payment status', $order->status)
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
