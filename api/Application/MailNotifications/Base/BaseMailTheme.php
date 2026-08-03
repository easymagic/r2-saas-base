<?php
namespace Application\MailNotifications\Base;

class BaseMailTheme implements BaseMailThemeInterface
{
    public function wrapTemplate(string $template)
    {
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Notification</title>
  <!--[if mso]>
  <style type="text/css">
    body, table, td { font-family: Arial, Helvetica, sans-serif !important; }
  </style>
  <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#eef2f6;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#eef2f6;margin:0;padding:0;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.08);">

          <!-- Header band -->
          <tr>
            <td style="background:linear-gradient(135deg,#0f766e 0%,#134e4a 100%);background-color:#0f766e;padding:28px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-family:Georgia,'Times New Roman',serif;font-size:22px;line-height:1.3;color:#ffffff;font-weight:bold;letter-spacing:0.3px;">
                    Your Platform
                  </td>
                </tr>
                <tr>
                  <td style="padding-top:6px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.4;color:#ccfbf1;">
                    Transactional notification
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Accent strip -->
          <tr>
            <td style="height:4px;line-height:4px;font-size:0;background-color:#f59e0b;">&nbsp;</td>
          </tr>

          <!-- Body content slot -->
          <tr>
            <td style="padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.65;color:#334155;">
              {$template}
            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td style="padding:0 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="border-top:1px solid #e2e8f0;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:24px 32px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#64748b;background-color:#f8fafc;">
              <p style="margin:0 0 8px 0;">
                This is an automated message. Please do not reply directly to this email.
              </p>
              <p style="margin:0;color:#94a3b8;">
                &copy; {$year} Your Platform. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
