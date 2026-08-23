<?php
namespace OrderItem\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\Usecases\CreateService as NotificationCreateService;
use OrderItem\Business\Usecases\OrderItemSupport;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use User\Data\UserRepositoryInterface;

class NotifyMerchantOfSettlementService
{
    private MailServiceInterface $mailService;
    private OrderItemSupport $orderItemSupport;
    private UserRepositoryInterface $userRepository;
    private NotificationCreateService $notificationCreateService;
    private BaseMailThemeInterface $baseMailTheme;
    private string $fromEmail = 'noreply@example.com';

    public function __construct(
        MailServiceInterface $mailService,
        OrderItemSupport $orderItemSupport,
        UserRepositoryInterface $userRepository,
        NotificationCreateService $notificationCreateService,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->mailService = $mailService;
        $this->orderItemSupport = $orderItemSupport;
        $this->userRepository = $userRepository;
        $this->notificationCreateService = $notificationCreateService;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function execute(int $order_item_id)
    {
        $orderItem = $this->orderItemSupport->requireOrderItem($order_item_id);
        $merchant = $this->userRepository->find((int) $orderItem->merchant_id);
        if ($merchant->isEmpty()) {
            throw new Exception('Merchant not found');
        }

        $merchantShare = $this->orderItemSupport->merchantShare($orderItem);
        $title = 'Order item settled';
        $message = 'Order item #' . (int) $orderItem->id
            . ' for order #' . (int) $orderItem->order_id
            . ' has been settled. Merchant share: ' . number_format($merchantShare, 2) . '.';

        $this->notificationCreateService->execute(new NotificationCreateDto((int) $merchant->id, $title, $message));

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
}
