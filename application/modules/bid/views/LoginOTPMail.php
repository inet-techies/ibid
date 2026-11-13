<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login OTP Verification - BID Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { margin: 0; padding: 0; background-color: #f4f6f8; font-family: system-ui, -apple-system, sans-serif; }
        .container { max-width: 600px; margin: auto; background: #ffffff; border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb; }
        .header { background-color: #3454d1; color: #ffffff; text-align: center; padding: 22px; font-size: 20px; font-weight: 700; }
        .content { padding: 28px 30px; color: #374151; font-size: 14px; line-height: 1.6; }
        .otp-box { margin: 24px auto; text-align: center; font-size: 30px; font-weight: 700; letter-spacing: 8px; color: #1e40af; background: #eff6ff; border: 1px dashed #93c5fd; padding: 14px 24px; border-radius: 8px; width: fit-content; }
        .meta { background: #f9fafb; padding: 12px 16px; border-radius: 6px; font-size: 12px; color: #6b7280; margin-top: 16px; }
        .footer { padding: 18px 30px; background: #fafafa; font-size: 12px; color: #777; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" style="padding: 30px 10px;">
<div class="container">
    <div class="header">BID Management — Login Verification</div>
    <div class="content">
        <p>Hello <strong><?php echo htmlspecialchars((string)($name ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></strong>,</p>
        <p>Use the One-Time Password (OTP) below to complete your sign-in to the BID Management portal:</p>
        <div class="otp-box"><?php echo htmlspecialchars((string)($otp ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
        <p>This OTP is valid for <strong><?php echo (int)($expiryMinutes ?? 10); ?> minutes</strong>. Do not share this code with anyone.</p>
        <div class="meta">
            <strong>Request IP:</strong> <?php echo htmlspecialchars((string)($ipAddress ?? 'Unknown'), ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Requested At:</strong> <?php echo date('d-m-Y h:i A'); ?>
        </div>
        <p style="margin-top: 16px;">If you did not attempt to log in, please change your password immediately and notify your administrator.</p>
    </div>
    <div class="footer">
        <p style="margin:0;">Need help? Contact <strong>info@inetcsc.com</strong><br>&copy; <?php echo date('Y'); ?> I-Net Secure Labs Pvt Ltd. All rights reserved.</p>
    </div>
</div>
</td>
</tr>
</table>
</body>
</html>

