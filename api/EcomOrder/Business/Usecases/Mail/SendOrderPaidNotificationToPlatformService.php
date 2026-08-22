<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderPaidNotificationToPlatformService
{
    private MailServiceInterface $mailService;
    private EcomOrderMailTemplate $mailTemplate;

    public function __construct(
        MailServiceInterface $mailService,
        EcomOrderMailTemplate $mailTemplate
    ) {
        $this->mailService = $mailService;
        $this->mailTemplate = $mailTemplate;
    }

    public function execute(int $order_id)
    {
        $order = $this->mailTemplate->requireOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' paid';
        $body = $this->mailTemplate->renderTemplate('order_paid_platform.html', $this->mailTemplate->orderVars($order, [
            'intro' => 'An ecommerce order has been paid.',
        ]));
        $this->mailService->send($this->mailTemplate->adminEmail(), $subject, $this->mailTemplate->from(), $body);
    }
}
