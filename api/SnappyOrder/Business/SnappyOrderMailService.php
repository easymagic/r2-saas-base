<?php

namespace SnappyOrder\Business;

use Business\MailTheme\BaseMailThemeInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use SnappyOrder\Data\SnappyOrderEntity;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class SnappyOrderMailService implements SnappyOrderMailServiceInterface
{
    private MailServiceInterface $mailService;
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private EnvServiceInterface $envService;
    private UserRepositoryInterface $userRepository;
    private BaseMailThemeInterface $baseMailTheme;

    public function __construct(
        MailServiceInterface $mailService,
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        EnvServiceInterface $envService,
        UserRepositoryInterface $userRepository,
        BaseMailThemeInterface $baseMailTheme
    ) {
        $this->mailService = $mailService;
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->envService = $envService;
        $this->userRepository = $userRepository;
        $this->baseMailTheme = $baseMailTheme;
    }

    public function notifyCustomerOfOrderCreation(int $order_id)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Created';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Your order has been created successfully. Here are the details:')
            . $this->orderDetailsCard($order)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->from(), $body);
    }

    public function notifyAdminOfOrderCreation(int $order_id)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Created';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting('Admin')
            . $this->intro('A new order was created by <strong>' . $this->e($user->name) . '</strong>.')
            . $this->orderDetailsCard($order)
            . $this->signOff()
        );
        $this->mailService->send($this->envService->get('ADMIN_EMAIL'), $subject, $this->from(), $body);
    }

    public function notifyCustomerOfStatusChange(int $order_id, string $status)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Status Changed';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Your order status has been updated.')
            . $this->statusBanner('New status', $status)
            . $this->orderDetailsCard($order, true)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->from(), $body);
    }

    public function notifyCustomerOfOrderPayment(int $order_id)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Paid';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Your order has been paid successfully. Thank you!')
            . $this->statusBanner('Payment status', $order->status)
            . $this->orderDetailsCard($order, true)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->from(), $body);
    }

    public function notifyCustomerOfPickupOTP(int $order_id, string $otp)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Ready for Pickup';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Great news — your order is ready for pickup. Present the OTP below at the facility.')
            . $this->otpBox($otp)
            . $this->orderDetailsCard($order, true)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->from(), $body);
    }

    public function notifyAgenOfOrderAssignment(int $order_id, int $agent_id)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $agent = $this->userRepository->find($agent_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'New Order Assignment';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($agent->name)
            . $this->intro('A new order has been assigned to you.')
            . $this->highlightBox('Customer', $user->name)
            . $this->orderDetailsCard($order, true)
            . $this->signOff()
        );
        $this->mailService->send($agent->email, $subject, $this->from(), $body);
    }

    public function notifyCustomerOfAgentAssignment(int $order_id, int $agent_id)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $agent = $this->userRepository->find($agent_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Assigned to Agent';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Your order has been assigned to an agent.')
            . $this->highlightBox('Assigned agent', $agent->name)
            . $this->orderDetailsCard($order, true)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->from(), $body);
    }

    public function notifyCustomerOfPriceChange(int $order_id, float $price)
    {
        $order = $this->snappyOrderRepository->find($order_id);
        $user = $this->userRepository->find($order->user_id);
        $subject = 'Order Price Updated';
        $body = $this->baseMailTheme->wrapTemplate(
            $this->greeting($user->name)
            . $this->intro('Your order price has been adjusted. Log in to view the updated amount and complete payment.')
            . $this->highlightBox('New amount (USD)', '$ ' . number_format($price, 2))
            . $this->orderDetailsCard($order, true)
            . $this->signOff()
        );
        $this->mailService->send($user->email, $subject, $this->from(), $body);
    }

    private function from(): string
    {
        return $this->envService->get('NOREPLY_EMAIL');
    }

    private function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function greeting(string $name): string
    {
        return '<p style="margin:0 0 16px 0;font-size:18px;font-weight:bold;color:#0f172a;">Hello '
            . $this->e($name) . ',</p>';
    }

    private function intro(string $html): string
    {
        return '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . $html . '</p>';
    }

    private function signOff(): string
    {
        return '<p style="margin:24px 0 0 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Thank you for using our service.<br><br>'
            . 'Best regards,<br><strong style="color:#0f172a;">The Team</strong>'
            . '</p>';
    }

    private function statusBanner(string $label, string $status): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px 0;">'
            . '<tr><td style="background-color:#f0fdfa;border:1px solid #99f6e4;border-radius:8px;padding:14px 16px;">'
            . '<span style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.06em;color:#0f766e;font-weight:bold;margin-bottom:6px;">'
            . $this->e($label) . '</span>'
            . '<span style="display:inline-block;background-color:#0f766e;color:#ffffff;font-size:13px;font-weight:bold;'
            . 'padding:6px 12px;border-radius:999px;text-transform:capitalize;">'
            . $this->e(str_replace('-', ' ', $status)) . '</span>'
            . '</td></tr></table>';
    }

    private function highlightBox(string $label, string $value): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px 0;">'
            . '<tr><td style="background-color:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;">'
            . '<span style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.06em;color:#b45309;font-weight:bold;margin-bottom:4px;">'
            . $this->e($label) . '</span>'
            . '<span style="font-size:16px;font-weight:bold;color:#0f172a;">' . $this->e($value) . '</span>'
            . '</td></tr></table>';
    }

    private function otpBox($otp): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px 0;">'
            . '<tr><td align="center" style="background-color:#0f766e;border-radius:10px;padding:20px 16px;">'
            . '<span style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#ccfbf1;margin-bottom:8px;">Pickup OTP</span>'
            . '<span style="display:block;font-family:Georgia,\'Times New Roman\',serif;font-size:32px;font-weight:bold;letter-spacing:6px;color:#ffffff;">'
            . $this->e($otp) . '</span>'
            . '</td></tr></table>';
    }

    private function orderDetailsCard(SnappyOrderEntity $order, bool $includeUpdatedAt = false): string
    {
        $link = $this->e($order->link);
        $rows = [
            'Order ID' => '#' . $order->id,
            'Type' => $order->type,
            'Reference' => $order->reference,
            'Link' => '<a href="' . $link . '" style="color:#0f766e;text-decoration:underline;word-break:break-all;">' . $link . '</a>',
            'Description' => $order->description,
            'Amount (USD)' => '$ ' . number_format((float) $order->total_amount_usd, 2),
            'Status' => str_replace('-', ' ', $order->status),
            'Created at' => $order->created_at,
        ];

        if ($includeUpdatedAt) {
            $rows['Updated at'] = $order->updated_at;
        }

        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 8px 0;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">'
            . '<tr><td style="padding:12px 16px;background-color:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:13px;font-weight:bold;color:#0f172a;">Order details</td></tr>'
            . '<tr><td style="padding:8px 16px 12px 16px;">' . $this->detailRows($rows, ['Link']) . '</td></tr>'
            . '</table>';
    }

    /**
     * @param array $rows
     * @param array $rawHtmlKeys
     */
    private function detailRows(array $rows, array $rawHtmlKeys = []): string
    {
        $html = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">';
        $i = 0;
        foreach ($rows as $label => $value) {
            $border = $i === 0 ? '' : 'border-top:1px solid #f1f5f9;';
            $valueHtml = in_array($label, $rawHtmlKeys, true) ? $value : $this->e($value);
            $html .= '<tr>'
                . '<td style="padding:10px 0;' . $border . 'width:38%;vertical-align:top;font-size:13px;color:#64748b;font-weight:bold;">'
                . $this->e($label) . '</td>'
                . '<td style="padding:10px 0;' . $border . 'vertical-align:top;font-size:14px;color:#0f172a;">'
                . $valueHtml . '</td>'
                . '</tr>';
            $i++;
        }
        $html .= '</table>';
        return $html;
    }
}
