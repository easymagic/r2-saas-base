<?php
namespace EcomOrder\Business\Usecases\Mail;

use R2Packages\Framework\Application\Mail\MailServiceInterface;

class SendOrderStatusChangedNotificationToMerchantService
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
        $merchants = $this->mailTemplate->merchantsForOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' status updated';
        foreach ($merchants as $merchant) {
            $body = $this->mailTemplate->renderTemplate('order_status_changed_merchant.html', $this->mailTemplate->orderVars($order, [
                'intro' => 'Delivery status for an order containing your product(s) has been updated.',
                'merchant_name' => $merchant->name,
                'status' => $status,
            ]));
            $this->mailService->send($merchant->email, $subject, $this->mailTemplate->from(), $body);
            $this->mailTemplate->notifyUser(
                (int) $merchant->id,
                $subject,
                'Order #' . (int) $order->id . ' is now ' . $status . '.'
            );
        }
    }
}
