<?php
namespace EcomOrder\Business\Usecases\Mail;

use Business\MailTheme\BaseMailThemeInterface;
use EcomOrder\Data\EcomOrderEntity;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\Usecases\CreateService as NotificationCreateService;
use OrderItem\Data\OrderItemRepositoryInterface;
use Product\Data\ProductRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;

class EcomOrderMailTemplate
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private OrderItemRepositoryInterface $orderItemRepository;
    private ProductRepositoryInterface $productRepository;
    private UserRepositoryInterface $userRepository;
    private NotificationCreateService $notificationCreateService;
    private EnvServiceInterface $envService;
    private BaseMailThemeInterface $baseMailTheme;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository,
        NotificationCreateService $notificationCreateService,
        EnvServiceInterface $envService,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
        $this->notificationCreateService = $notificationCreateService;
        $this->envService = $envService;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function requireOrder(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        return $order;
    }

    public function orderVars(EcomOrderEntity $order, array $extra = [])
    {
        return [
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
    }

    public function itemsHtml(int $order_id)
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

    public function merchantsForOrder(int $order_id)
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

    public function renderTemplate(string $file, array $vars)
    {
        $dir = defined('MAIL_TEMPLATE_DIR') ? MAIL_TEMPLATE_DIR : (__DIR__ . '/../../../../mail_templates');
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

    public function from()
    {
        $from = $this->envService->get('NOREPLY_EMAIL');
        return !empty($from) ? $from : 'noreply@example.com';
    }

    public function adminEmail()
    {
        $email = $this->envService->get('ADMIN_EMAIL');
        if (empty($email)) {
            throw new Exception('Admin email is not configured');
        }
        return $email;
    }

    public function notifyUser(int $user_id, string $title, string $message)
    {
        if ($user_id <= 0) {
            return;
        }
        $this->notificationCreateService->execute(new NotificationCreateDto($user_id, $title, $message));
    }

    public function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
