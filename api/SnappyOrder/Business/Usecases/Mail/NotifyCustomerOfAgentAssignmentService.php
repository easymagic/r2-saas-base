<?php
namespace SnappyOrder\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class NotifyCustomerOfAgentAssignmentService
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

    public function execute(int $order_id, int $agent_id)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $agent = $this->userRepository->find($agent_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Assigned to Agent';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->snappyOrderMailTemplate->greeting($user->name)
            . $this->snappyOrderMailTemplate->intro('Your order has been assigned to an agent.')
            . $this->snappyOrderMailTemplate->highlightBox('Assigned agent', $agent->name)
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
