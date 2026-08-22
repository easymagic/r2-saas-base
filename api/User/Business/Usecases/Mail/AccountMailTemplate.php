<?php
namespace User\Business\Usecases\Mail;

/**
 * Shared HTML fragments for account mail bodies.
 */
class AccountMailTemplate
{
    public function e(string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public function greeting(string $name): string
    {
        return '<p style="margin:0 0 16px 0;font-size:18px;font-weight:bold;color:#0f172a;">Hello '
            . $this->e($name) . ',</p>';
    }

    public function intro(string $html): string
    {
        return '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">'
            . $html . '</p>';
    }

    public function signOff(): string
    {
        return '<p style="margin:24px 0 0 0;font-size:15px;line-height:1.65;color:#475569;">'
            . 'Thank you for using our service.<br><br>'
            . 'Best regards,<br><strong style="color:#0f172a;">The Team</strong>'
            . '</p>';
    }

    public function otpBox(string $otp, string $label): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px 0;">'
            . '<tr><td align="center" style="background-color:#0f766e;border-radius:10px;padding:20px 16px;">'
            . '<span style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#ccfbf1;margin-bottom:8px;">'
            . $this->e($label) . '</span>'
            . '<span style="display:block;font-family:Georgia,\'Times New Roman\',serif;font-size:32px;font-weight:bold;letter-spacing:6px;color:#ffffff;">'
            . $this->e($otp) . '</span>'
            . '</td></tr></table>';
    }

    public function amountBox(string $label, float $amount, bool $credit): string
    {
        $bg = $credit ? '#f0fdfa' : '#fffbeb';
        $border = $credit ? '#99f6e4' : '#fde68a';
        $labelColor = $credit ? '#0f766e' : '#b45309';
        $prefix = $credit ? '+' : '-';

        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px 0;">'
            . '<tr><td style="background-color:' . $bg . ';border:1px solid ' . $border . ';border-radius:10px;padding:16px;">'
            . '<span style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.06em;color:' . $labelColor . ';font-weight:bold;margin-bottom:6px;">'
            . $this->e($label) . '</span>'
            . '<span style="display:block;font-size:24px;font-weight:bold;color:#0f172a;">'
            . $prefix . ' ₦ ' . $this->e(number_format($amount, 2)) . '</span>'
            . '</td></tr></table>';
    }
}
