<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderPaidNotificationToCustomerService
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
        $subject = 'Payment received for order #' . (int) $order->id;
        $body = $this->mailTemplate->renderTemplate('order_paid_customer.html', $this->mailTemplate->orderVars($order, [
            'intro' => 'We have received your payment. Thank you!',
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->mailTemplate->from(), $body);
        $this->mailTemplate->notifyUser(
            (int) $order->user_id,
            $subject,
            'Payment for order #' . (int) $order->id . ' was successful.'
        );
    }
}
