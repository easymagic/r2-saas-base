<?php
namespace SnappyOrder\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class NotifyAgentOfOrderAssignmentService
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
        $subject = 'New Order Assignment';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->snappyOrderMailTemplate->greeting($agent->name)
            . $this->snappyOrderMailTemplate->intro('A new order has been assigned to you.')
            . $this->snappyOrderMailTemplate->highlightBox('Customer', $user->name)
            . $this->snappyOrderMailTemplate->orderDetailsCard($order, true)
            . $this->snappyOrderMailTemplate->signOff()
        );
        $this->mailService->send($agent->email, $subject, $this->from(), $body);
    }

    private function from(): string
    {
        return $this->envService->get('NOREPLY_EMAIL');
    }
}
