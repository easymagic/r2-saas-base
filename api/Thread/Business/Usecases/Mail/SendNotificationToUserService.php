<?php
namespace Thread\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use Thread\Data\ThreadRepositoryInterface;
use User\Data\UserRepositoryInterface;

class SendNotificationToUserService
{
    private MailServiceInterface $mailService;
    private ThreadRepositoryInterface $threadRepository;
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private UserRepositoryInterface $userRepository;
    private BaseMailThemeInterface $baseMailTheme;
    private string $fromEmail = 'noreply@example.com';

    public function __construct(
        MailServiceInterface $mailService,
        ThreadRepositoryInterface $threadRepository,
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        UserRepositoryInterface $userRepository,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->mailService = $mailService;
        $this->threadRepository = $threadRepository;
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->userRepository = $userRepository;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function execute(int $thread_id)
    {
        Contracts::requires($thread_id > 0, 'Thread ID is required');

        $thread = $this->threadRepository->find($thread_id);
        Contracts::requireEntityFound($thread, 'Thread');

        $order = $this->snappyOrderRepository->find($thread->order_id);
        Contracts::requireEntityFound($order, 'Order');

        $customer = $this->userRepository->find($order->user_id);
        Contracts::requireEntityFound($customer, 'Customer');

        $sender = $this->userRepository->find($thread->sender_id);
        $senderName = $sender->isEmpty() ? 'Support' : $sender->name;

        $subject = 'New Message on Order #' . $order->id;
        $body = $this->baseMailTheme->wrapTemplate(
            '<p style="margin:0 0 16px 0;font-size:18px;font-weight:bold;color:#0f172a;">Hello '
            . htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'You have a new message from <strong>'
            . htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8')
            . '</strong> on order #' . (int) $order->id . '.</p>'
            . '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Message: ' . htmlspecialchars($thread->message, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p style="margin:24px 0 0 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Thank you for your business.</p>'
        );

        $this->mailService->send($customer->email, $subject, $this->fromEmail, $body);
    }
}
