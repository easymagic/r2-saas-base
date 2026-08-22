<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderPaidNotificationToMerchantService
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
        $merchants = $this->mailTemplate->merchantsForOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' has been paid';
        foreach ($merchants as $merchant) {
            $body = $this->mailTemplate->renderTemplate('order_paid_merchant.html', $this->mailTemplate->orderVars($order, [
                'intro' => 'An order containing your product(s) has been paid.',
                'merchant_name' => $merchant->name,
            ]));
            $this->mailService->send($merchant->email, $subject, $this->mailTemplate->from(), $body);
            $this->mailTemplate->notifyUser(
                (int) $merchant->id,
                $subject,
                'Order #' . (int) $order->id . ' has been paid.'
            );
        }
    }
}
