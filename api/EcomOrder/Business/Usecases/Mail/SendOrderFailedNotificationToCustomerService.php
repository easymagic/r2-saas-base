<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderFailedNotificationToCustomerService
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
        $subject = 'Payment failed for order #' . (int) $order->id;
        $body = $this->mailTemplate->renderTemplate('order_failed_customer.html', $this->mailTemplate->orderVars($order, [
            'intro' => 'We could not complete payment for your order.',
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->mailTemplate->from(), $body);
        $this->mailTemplate->notifyUser(
            (int) $order->user_id,
            $subject,
            'Payment for order #' . (int) $order->id . ' failed.'
        );
    }
}
