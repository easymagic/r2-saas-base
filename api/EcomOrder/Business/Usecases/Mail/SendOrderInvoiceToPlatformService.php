<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderInvoiceToPlatformService
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
        $subject = 'New order invoice #' . (int) $order->id;
        $body = $this->mailTemplate->renderTemplate('order_invoice_platform.html', $this->mailTemplate->orderVars($order, [
            'intro' => 'A new ecommerce order invoice has been generated.',
        ]));
        $this->mailService->send($this->mailTemplate->adminEmail(), $subject, $this->mailTemplate->from(), $body);
    }
}
