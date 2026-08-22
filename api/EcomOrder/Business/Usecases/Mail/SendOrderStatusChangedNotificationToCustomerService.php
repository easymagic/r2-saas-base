<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderStatusChangedNotificationToCustomerService
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

    public function execute(int $order_id, string $status)
    {
        $order = $this->mailTemplate->requireOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' status updated';
        $body = $this->mailTemplate->renderTemplate('order_status_changed_customer.html', $this->mailTemplate->orderVars($order, [
            'intro' => 'Your order delivery status has been updated.',
            'status' => $status,
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->mailTemplate->from(), $body);
        $this->mailTemplate->notifyUser(
            (int) $order->user_id,
            $subject,
            'Order #' . (int) $order->id . ' is now ' . $status . '.'
        );
    }
}
