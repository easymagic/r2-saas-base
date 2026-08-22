<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderInvoiceToCustomerService
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
        $subject = 'Your order invoice #' . (int) $order->id;
        $body = $this->mailTemplate->renderTemplate('order_invoice_customer.html', $this->mailTemplate->orderVars($order, [
            'intro' => 'Thank you for your order. Here is your invoice.',
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->mailTemplate->from(), $body);
        $this->mailTemplate->notifyUser(
            (int) $order->user_id,
            $subject,
            'Invoice for order #' . (int) $order->id . ' has been sent to your email.'
        );
    }
}
