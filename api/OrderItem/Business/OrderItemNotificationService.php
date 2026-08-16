<?php

namespace OrderItem\Business;

use Business\MailTheme\BaseMailThemeInterface;
use Exception;
use Notification\Business\NotificationServiceInterface;
use OrderItem\Data\OrderItemEntity;
use OrderItem\Data\OrderItemRepositoryInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use User\Data\UserRepositoryInterface;

class OrderItemNotificationService implements OrderItemNotificationServiceInterface
{
    private MailServiceInterface $mailService;
    private OrderItemRepositoryInterface $orderItemRepository;
    private UserRepositoryInterface $userRepository;
    private NotificationServiceInterface $notificationService;
    private EnvServiceInterface $envService;
    private BaseMailThemeInterface $baseMailTheme;
    private string $fromEmail = 'noreply@example.com';

    public function __construct(
        MailServiceInterface $mailService,
        OrderItemRepositoryInterface $orderItemRepository,
        UserRepositoryInterface $userRepository,
        NotificationServiceInterface $notificationService,
        EnvServiceInterface $envService,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->mailService = $mailService;
        $this->orderItemRepository = $orderItemRepository;
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
        $this->envService = $envService;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function notifyMerchantOfSettlement(int $order_item_id)
    {
        $orderItem = $this->requireOrderItem($order_item_id);
        $merchant = $this->userRepository->find((int) $orderItem->merchant_id);
        if ($merchant->isEmpty()) {
            throw new Exception('Merchant not found');
        }

        $merchantShare = $this->merchantShare($orderItem);
        $title = 'Order item settled';
        $message = 'Order item #' . (int) $orderItem->id
            . ' for order #' . (int) $orderItem->order_id
            . ' has been settled. Merchant share: ' . number_format($merchantShare, 2) . '.';

        $this->notificationService->create((int) $merchant->id, $title, $message);

        $body = $this->baseMailTheme->wrapTemplate(
            '<p style="margin:0 0 16px 0;font-size:18px;font-weight:bold;color:#0f172a;">Hello '
            . htmlspecialchars($merchant->name, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Order item #' . (int) $orderItem->id . ' on order #' . (int) $orderItem->order_id
            . ' has been settled.</p>'
            . '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Line amount: ' . number_format((float) $orderItem->total_line_amount, 2)
            . '<br>Platform percentage: ' . number_format((float) $orderItem->percentage_to_platform, 2) . '%'
            . '<br>Your share: ' . number_format($merchantShare, 2) . '</p>'
            . '<p style="margin:24px 0 0 0;font-size:15px;line-height:1.65;color:#475569;">Thank you.</p>'
        );

        $this->mailService->send($merchant->email, $title, $this->fromEmail, $body);
    }

    public function notifyPlatformOfSettlement(int $order_item_id)
    {
        $orderItem = $this->requireOrderItem($order_item_id);
        $adminEmail = $this->envService->get('ADMIN_EMAIL');
        if (empty($adminEmail)) {
            throw new Exception('Admin email is not configured');
        }

        $platformShare = $this->platformShare($orderItem);
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

    /**
     * @param int $order_item_id
     * @return OrderItemEntity
     */
    private function requireOrderItem(int $order_item_id)
    {
        if (empty($order_item_id)) {
            throw new Exception('Order item ID is required');
        }

        $orderItem = $this->orderItemRepository->find($order_item_id);
        if ($orderItem->isEmpty()) {
            throw new Exception('Order item not found');
        }

        return $orderItem;
    }

    /**
     * @param OrderItemEntity $orderItem
     * @return float
     */
    private function platformShare(OrderItemEntity $orderItem)
    {
        return (float) $orderItem->total_line_amount * ((float) $orderItem->percentage_to_platform / 100);
    }

    /**
     * @param OrderItemEntity $orderItem
     * @return float
     */
    private function merchantShare(OrderItemEntity $orderItem)
    {
        return (float) $orderItem->total_line_amount - $this->platformShare($orderItem);
    }
}
