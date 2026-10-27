
<?php 
if(!empty($RegMail)) {
    $Buid = urlencode(base64_encode(base64_encode(base64_encode(base64_encode($RegMail[0]['Buid'])))));
} else {
    redirect($this->config->item('base_url').'bid/index');
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OTP Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f8;
            font-family: system-ui, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
        }
        .header {
            background-color: #3F51B5;
            color: #ffffff;
            text-align: center;
            padding: 22px;
            font-size: 22px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
            color: #555;
            font-size: 14px;
            line-height: 24px;
        }
        .otp-box {
            margin: 25px auto;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 6px;
            color: #4CAF50;
            background: #C8E6C9;
            padding: 15px;
            border-radius: 6px;
            width: fit-content;
        }
        .footer {
            padding: 20px 30px;
            background: #fafafa;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" style="padding: 30px 10px;">
<div class="container">

    <!-- Header -->
    <div class="header">
        BID Management
    </div>

    <!-- Content -->
    <div class="content">
        <p>Hello <strong><?php echo $name; ?></strong>,</p>

        <p>
            To proceed with your verification, please use the One-Time Password (OTP) below:
        </p>

        <div class="otp-box">
            <?php echo $otp; ?>
        </div>

        <p>
            This OTP is valid for <strong>10 minutes</strong>.  
            Please do not share this OTP with anyone.
        </p>

        <p>
            If you did not request this verification, please ignore this email or
            contact our support team immediately.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            Need help? Contact us at <strong>info@inetcsc.com</strong><br>
            <?php echo date('Y'); ?> BID Management. All rights reserved.
        </p>
        <img src="cid:I-NET" alt="Company Logo">
    </div>

</div>
</td>
</tr>
</table>

</body>
</html>