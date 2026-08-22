<?php

namespace EcomOrder\Business;

use Business\MailTheme\BaseMailThemeInterface;
use EcomOrder\Data\EcomOrderEntity;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\NotificationServiceInterface;
use OrderItem\Data\OrderItemRepositoryInterface;
use Product\Data\ProductRepositoryInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use User\Data\UserRepositoryInterface;

class EcomOrderNotificationService implements EcomOrderNotificationServiceInterface
{
    private MailServiceInterface $mailService;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private OrderItemRepositoryInterface $orderItemRepository;
    private ProductRepositoryInterface $productRepository;
    private UserRepositoryInterface $userRepository;
    private NotificationServiceInterface $notificationService;
    private EnvServiceInterface $envService;
    private BaseMailThemeInterface $baseMailTheme;

    public function __construct(
        MailServiceInterface $mailService,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository,
        NotificationServiceInterface $notificationService,
        EnvServiceInterface $envService,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->mailService = $mailService;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
        $this->envService = $envService;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function sendOrderInvoiceToCustomer(int $order_id)
    {
        $order = $this->requireOrder($order_id);
        $subject = 'Your order invoice #' . (int) $order->id;
        $body = $this->renderTemplate('order_invoice_customer.html', $this->orderVars($order, [
            'intro' => 'Thank you for your order. Here is your invoice.',
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->from(), $body);
        $this->notifyUser(
            (int) $order->user_id,
            $subject,
            'Invoice for order #' . (int) $order->id . ' has been sent to your email.'
        );
    }

    public function sendOrderInvoiceToPlatform(int $order_id)
    {
        $order = $this->requireOrder($order_id);
        $subject = 'New order invoice #' . (int) $order->id;
        $body = $this->renderTemplate('order_invoice_platform.html', $this->orderVars($order, [
            'intro' => 'A new ecommerce order invoice has been generated.',
        ]));
        $this->mailService->send($this->adminEmail(), $subject, $this->from(), $body);
    }

    public function sendOrderPaidNotificationToCustomer(int $order_id)
    {
        $order = $this->requireOrder($order_id);
        $subject = 'Payment received for order #' . (int) $order->id;
        $body = $this->renderTemplate('order_paid_customer.html', $this->orderVars($order, [
            'intro' => 'We have received your payment. Thank you!',
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->from(), $body);
        $this->notifyUser(
            (int) $order->user_id,
            $subject,
            'Payment for order #' . (int) $order->id . ' was successful.'
        );
    }

    public function sendOrderPaidNotificationToMerchant(int $order_id)
    {
        $order = $this->requireOrder($order_id);
        $merchants = $this->merchantsForOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' has been paid';
        foreach ($merchants as $merchant) {
            $body = $this->renderTemplate('order_paid_merchant.html', $this->orderVars($order, [
                'intro' => 'An order containing your product(s) has been paid.',
                'merchant_name' => $merchant->name,
            ]));
            $this->mailService->send($merchant->email, $subject, $this->from(), $body);
            $this->notifyUser(
                (int) $merchant->id,
                $subject,
                'Order #' . (int) $order->id . ' has been paid.'
            );
        }
    }

    public function sendOrderPaidNotificationToPlatform(int $order_id)
    {
        $order = $this->requireOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' paid';
        $body = $this->renderTemplate('order_paid_platform.html', $this->orderVars($order, [
            'intro' => 'An ecommerce order has been paid.',
        ]));
        $this->mailService->send($this->adminEmail(), $subject, $this->from(), $body);
    }

    public function sendOrderFailedNotificationToCustomer(int $order_id)
    {
        $order = $this->requireOrder($order_id);
        $subject = 'Payment failed for order #' . (int) $order->id;
        $body = $this->renderTemplate('order_failed_customer.html', $this->orderVars($order, [
            'intro' => 'We could not complete payment for your order.',
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->from(), $body);
        $this->notifyUser(
            (int) $order->user_id,
            $subject,
            'Payment for order #' . (int) $order->id . ' failed.'
        );
    }

    public function sendOrderStatusChangedNotificationToCustomer(int $order_id, string $status)
    {
        $order = $this->requireOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' status updated';
        $body = $this->renderTemplate('order_status_changed_customer.html', $this->orderVars($order, [
            'intro' => 'Your order delivery status has been updated.',
            'status' => $status,
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->from(), $body);
        $this->notifyUser(
            (int) $order->user_id,
            $subject,
            'Order #' . (int) $order->id . ' is now ' . $status . '.'
        );
    }

    public function sendOrderStatusChangedNotificationToMerchant(int $order_id, string $status)
    {
        $order = $this->requireOrder($order_id);
        $merchants = $this->merchantsForOrder($order_id);
        $subject = 'Order #' . (int) $order->id . ' status updated';
        foreach ($merchants as $merchant) {
            $body = $this->renderTemplate('order_status_changed_merchant.html', $this->orderVars($order, [
                'intro' => 'Delivery status for an order containing your product(s) has been updated.',
                'merchant_name' => $merchant->name,
                'status' => $status,
            ]));
            $this->mailService->send($merchant->email, $subject, $this->from(), $body);
            $this->notifyUser(
                (int) $merchant->id,
                $subject,
                'Order #' . (int) $order->id . ' is now ' . $status . '.'
            );
        }
    }

    public function sendOrderAssignedToAgentNotificationToCustomer(int $order_id, int $agent_id)
    {
        $order = $this->requireOrder($order_id);
        $agent = $this->userRepository->find($agent_id);
        if ($agent->isEmpty()) {
            throw new Exception('Agent not found');
        }
        $subject = 'Order #' . (int) $order->id . ' assigned to an agent';
        $body = $this->renderTemplate('order_assigned_agent_customer.html', $this->orderVars($order, [
            'intro' => 'Your order has been assigned to a delivery agent.',
            'agent_name' => $agent->name,
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->from(), $body);
        $this->notifyUser(
            (int) $order->user_id,
            $subject,
            'Order #' . (int) $order->id . ' was assigned to ' . $agent->name . '.'
        );
    }

    /**
     * @param int $order_id
     * @return EcomOrderEntity
     */
    private function requireOrder(int $order_id)
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }
        $order = $this->ecomOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            throw new Exception('Order not found');
        }
        return $order;
    }

    /**
     * @param EcomOrderEntity $order
     * @param array $extra
     * @return array
     */
    private function orderVars(EcomOrderEntity $order, array $extra = [])
    {
        $vars = [
            'customer_name' => $this->e($order->customer_name),
            'customer_email' => $this->e($order->customer_email),
            'customer_address' => $this->e($order->customer_address),
            'order_id' => (string) (int) $order->id,
            'reference' => $this->e($order->reference),
            'type' => $this->e($order->type),
            'payment_status' => $this->e($order->payment_status),
            'delivery_status' => $this->e($order->delivery_status),
            'status' => $this->e(isset($extra['status']) ? $extra['status'] : $order->delivery_status),
            'total_amount' => number_format((float) $order->total_amount, 2),
            'shipping_fee' => number_format((float) $order->shipping_fee, 2),
            'service_charge' => number_format((float) $order->service_charge, 2),
            'number_of_installment' => (string) (int) $order->number_of_installment,
            'created_at' => $this->e($order->created_at),
            'items_html' => $this->itemsHtml((int) $order->id),
            'merchant_name' => $this->e(isset($extra['merchant_name']) ? $extra['merchant_name'] : 'Merchant'),
            'agent_name' => $this->e(isset($extra['agent_name']) ? $extra['agent_name'] : ''),
            'intro' => isset($extra['intro']) ? $extra['intro'] : '',
        ];
        return $vars;
    }

    /**
     * @param int $order_id
     * @return string
     */
    private function itemsHtml(int $order_id)
    {
        $items = $this->orderItemRepository->query(['order_id' => $order_id])->fetchAll();
        $rows = '';
        foreach ($items as $item) {
            $product = $this->productRepository->find((int) $item->product_id);
            $name = $product->isEmpty() ? ('Product #' . (int) $item->product_id) : $product->name;
            $rows .= '<tr>'
                . '<td style="padding:8px 0;border-top:1px solid #f1f5f9;font-size:14px;color:#0f172a;">'
                . $this->e($name) . '</td>'
                . '<td style="padding:8px 0;border-top:1px solid #f1f5f9;font-size:14px;color:#0f172a;text-align:center;">'
                . (int) $item->qty . '</td>'
                . '<td style="padding:8px 0;border-top:1px solid #f1f5f9;font-size:14px;color:#0f172a;text-align:right;">'
                . number_format((float) $item->total_line_amount, 2) . '</td>'
                . '</tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="3" style="padding:8px 0;font-size:14px;color:#64748b;">No items</td></tr>';
        }
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 16px 0;">'
            . '<tr>'
            . '<td style="padding:0 0 8px 0;font-size:12px;font-weight:bold;color:#64748b;">Item</td>'
            . '<td style="padding:0 0 8px 0;font-size:12px;font-weight:bold;color:#64748b;text-align:center;">Qty</td>'
            . '<td style="padding:0 0 8px 0;font-size:12px;font-weight:bold;color:#64748b;text-align:right;">Amount</td>'
            . '</tr>'
            . $rows
            . '</table>';
    }

    /**
     * @param int $order_id
     * @return array
     */
    private function merchantsForOrder(int $order_id)
    {
        $items = $this->orderItemRepository->query(['order_id' => $order_id])->fetchAll();
        $merchants = [];
        foreach ($items as $item) {
            $merchantId = (int) $item->merchant_id;
            if ($merchantId <= 0 || isset($merchants[$merchantId])) {
                continue;
            }
            $merchant = $this->userRepository->find($merchantId);
            if ($merchant->isEmpty()) {
                continue;
            }
            $merchants[$merchantId] = $merchant;
        }
        return $merchants;
    }

    /**
     * @param string $file
     * @param array $vars
     * @return string
     */
    private function renderTemplate(string $file, array $vars)
    {
        $dir = defined('MAIL_TEMPLATE_DIR') ? MAIL_TEMPLATE_DIR : (__DIR__ . '/../../mail_templates');
        $path = rtrim($dir, '/') . '/' . $file;
        if (!is_file($path)) {
            throw new Exception('Mail template not found: ' . $file);
        }
        $html = file_get_contents($path);
        if ($html === false) {
            throw new Exception('Unable to read mail template: ' . $file);
        }
        foreach ($vars as $key => $value) {
            $html = str_replace('{{' . $key . '}}', (string) $value, $html);
        }
        return $this->baseMailTheme->wrapTemplate($html);
    }

    private function from()
    {
        $from = $this->envService->get('NOREPLY_EMAIL');
        return !empty($from) ? $from : 'noreply@example.com';
    }

    private function adminEmail()
    {
        $email = $this->envService->get('ADMIN_EMAIL');
        if (empty($email)) {
            throw new Exception('Admin email is not configured');
        }
        return $email;
    }

    private function notifyUser(int $user_id, string $title, string $message)
    {
        if ($user_id <= 0) {
            return;
        }
        $this->notificationService->create(new NotificationCreateDto($user_id, $title, $message));
    }

    private function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
