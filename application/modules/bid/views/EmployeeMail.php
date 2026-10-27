
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
    <title>Account Activation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
            margin: 0 auto;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
        }
        .header {
            background-color: #3F51B5;
            padding: 25px;
            text-align: center;
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .content {
            padding: 30px;
            color: #555555;
            font-size: 14px;
            line-height: 24px;
        }
        .content p {
            margin: 0 0 15px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            background-color: #4caf50;
            color: #ffffff;
            padding: 12px 28px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 4px;
            display: inline-block;
        }
        .footer {
            padding: 20px 30px;
            background-color: #fafafa;
            color: #777777;
            font-size: 12px;
            line-height: 20px;
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
                    Bid Management
                </div>

                <!-- Content -->
                <div class="content">
                    <p>Hello <strong><?php echo $RegMail[0]['Name']; ?></strong>,</p>

                    <p>
                        We are pleased to inform you that your account has been 
                        <strong>successfully registered</strong> with Bid Management.
                    </p>

                    <p>
                        To complete your registration, please activate your account and create
                        a password by clicking the button below.
                    </p>

                    <div class="btn-container">
                        <a href="<?php echo $this->config->item('base_url'); ?>bid/SendOtpVerify/<?php echo $Buid; ?>" 
                           class="btn" target="_blank">
                            Activate Account & Create Password
                        </a>
                    </div>

                    <p>
                        If you did not initiate this registration, please ignore this email or
                        contact our support team immediately.
                    </p>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <p>
                        For any assistance, feel free to reach us at  
                        <strong>info@inetcsc.com</strong> or call us at <strong>+91 44 4400 666</strong>.
                    </p>

                    <p>
                        Thank you,<br>
                        <strong>Support Team</strong>
                    </p>

                    <img src="cid:I-NET" alt="Company Logo">
                </div>

            </div>
        </td>
    </tr>
</table>

</body>
</html>