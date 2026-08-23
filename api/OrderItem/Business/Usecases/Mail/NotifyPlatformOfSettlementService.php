<?php
namespace OrderItem\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use Exception;
use OrderItem\Business\Usecases\OrderItemSupport;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;

class NotifyPlatformOfSettlementService
{
    private MailServiceInterface $mailService;
    private OrderItemSupport $orderItemSupport;
    private EnvServiceInterface $envService;
    private BaseMailThemeInterface $baseMailTheme;
    private string $fromEmail = 'noreply@example.com';

    public function __construct(
        MailServiceInterface $mailService,
        OrderItemSupport $orderItemSupport,
        EnvServiceInterface $envService,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->mailService = $mailService;
        $this->orderItemSupport = $orderItemSupport;
        $this->envService = $envService;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function execute(int $order_item_id)
    {
        $orderItem = $this->orderItemSupport->requireOrderItem($order_item_id);
        $adminEmail = $this->envService->get('ADMIN_EMAIL');
        if (empty($adminEmail)) {
            throw new Exception('Admin email is not configured');
        }

        $platformShare = $this->orderItemSupport->platformShare($orderItem);
        $subject = 'Order item settled';
        $body = $this->baseMailTheme->wrapTemplate(
            '<p style="margin:0 0 16px 0;font-size:18px;font-weight:bold;color:#0f172a;">Hello Admin,</p>'
            . '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Order item #' . (int) $orderItem->id . ' on order #' . (int) $orderItem->order_id
            . ' has been settled.</p>'
            . '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Merchant ID: ' . (int) $orderItem->merchant_id
            . '<br>Product ID: ' . (int) $orderItem->product_id
            . '<br>Qty: ' . (int) $orderItem->qty
            . '<br>Line amount: ' . number_format((float) $orderItem->total_line_amount, 2)
            . '<br>Platform percentage: ' . number_format((float) $orderItem->percentage_to_platform, 2) . '%'
            . '<br>Platform share: ' . number_format($platformShare, 2) . '</p>'
            . '<p style="margin:24px 0 0 0;font-size:15px;line-height:1.65;color:#475569;">Thank you.</p>'
        );

        $this->mailService->send($adminEmail, $subject, $this->fromEmail, $body);
    }
}
