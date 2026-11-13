<!DOCTYPE html>
<html lang="en">
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BID Management || Login OTP Verification</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?=$theme_path?>/assets/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/css/theme.min.css">
    <style>
        .footer {
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            border-top: 1px solid #e5e7eb;
        }
        .otp-input {
            width: 44px;
            height: 48px;
            font-size: 20px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="auth-cover-wrapper">
        <div class="auth-cover-content-inner">
            <div class="auth-cover-content-wrapper">
                <div class="auth-img">
                    <img src="<?=$theme_path?>/assets/images/auth/auth-cover-verify-bg.svg" alt="" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="auth-cover-sidebar-inner">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5">
                    <div class="wd-50 mb-5">
                        <img src="<?=$theme_path?>/assets/images/logo-inet.png" alt="" class="img-fluid">
                    </div>
                    <h2 class="fs-20 fw-bolder mb-2">Two-Step Login Verification</h2>
                    <h4 class="fs-13 fw-bold text-muted mb-3">Enter the 6-digit One-Time Password sent to complete login.</h4>

                    <?php if ($this->session->flashdata('yes')): ?>
                        <div class="alert alert-success fs-12"><?php echo htmlspecialchars((string)$this->session->flashdata('yes'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php elseif ($this->session->flashdata('err')): ?>
                        <div class="alert alert-danger fs-12"><?php echo htmlspecialchars((string)$this->session->flashdata('err'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <p class="fs-12 fw-medium text-muted">
                        <span>OTP sent to</span>
                        <strong><?php echo htmlspecialchars((string)($maskedEmail ?? 'your registered email'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>

                    <div id="otp-msg" class="mt-3 text-center fs-12 fw-semibold"></div>

                    <form id="loginOtpForm" method="post" onsubmit="verifyLoginOtp(); return false;" class="w-100 mt-3 pt-2">
                        <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                            <input class="m-1 text-center form-control rounded otp-input" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" autofocus required>
                            <input class="m-1 text-center form-control rounded otp-input" type="text" inputmode="numeric" maxlength="1" required>
                            <input class="m-1 text-center form-control rounded otp-input" type="text" inputmode="numeric" maxlength="1" required>
                            <input class="m-1 text-center form-control rounded otp-input" type="text" inputmode="numeric" maxlength="1" required>
                            <input class="m-1 text-center form-control rounded otp-input" type="text" inputmode="numeric" maxlength="1" required>
                            <input class="m-1 text-center form-control rounded otp-input" type="text" inputmode="numeric" maxlength="1" required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" id="verifyOtpBtn" class="btn btn-lg btn-primary w-100">
                                Verify &amp; Login
                            </button>
                        </div>

                        <div class="mt-4 d-flex align-items-center justify-content-between text-muted fs-12">
                            <div>
                                <span>Didn't receive code?</span>
                                <a href="javascript:void(0);" id="resendOtp" class="fw-bold ms-1">Resend</a>
                            </div>
                            <a href="<?php echo base_url('bid/index'); ?>" class="text-primary fw-semibold">Back to Login</a>
                        </div>
                    </form>
                </div>
                <footer class="footer">
                    <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright">
                        <span>Copyright &copy;</span>
                        <script>document.write(new Date().getFullYear());</script>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.i-net.in/" target="_blank">I-Net Secure Labs Pvt Ltd</a>
                    </p>
                </footer>
            </div>
        </div>
    </main>

    <script src="<?=$theme_path?>/assets/vendors/js/vendors.min.js"></script>
    <script src="<?=$theme_path?>/assets/js/common-init.min.js"></script>
    <script src="<?=$theme_path?>/assets/js/theme-customizer-init.min.js"></script>
    <script>
        var BASE_URL = '<?php echo $this->config->item('base_url'); ?>';
        var initialCooldown = <?php echo (int)($resendWait ?? 0); ?>;

        $('.otp-input').on('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && $(this).next('.otp-input').length) {
                $(this).next('.otp-input').focus();
            }
        });

        $('.otp-input').on('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value) {
                $(this).prev('.otp-input').focus();
            }
        });

        $('.otp-input').on('paste', function (e) {
            var clip = (e.originalEvent.clipboardData || window.clipboardData).getData('text') || '';
            var digits = clip.replace(/\D/g, '').slice(0, 6);
            if (!digits) return;
            e.preventDefault();
            var $inputs = $('.otp-input');
            for (var i = 0; i < 6; i++) {
                $inputs.eq(i).val(digits[i] || '');
            }
            $inputs.eq(Math.min(digits.length, 5)).focus();
        });

        function verifyLoginOtp() {
            var otp = '';
            $('.otp-input').each(function () {
                otp += $(this).val();
            });

            if (otp.length !== 6) {
                $('#otp-msg').html('<span class="text-danger">Please enter all 6 digits of the OTP.</span>');
                return;
            }

            var $btn = $('#verifyOtpBtn');
            $btn.prop('disabled', true).text('Verifying...');

            $.ajax({
                url: BASE_URL + 'bid/verifyLoginOtpAjax',
                type: 'POST',
                dataType: 'json',
                data: { otp: otp },
                success: function (res) {
                    $('#otp-msg').html(
                        '<span class="' + (res.status ? 'text-success' : 'text-danger') + '">' + $('<div>').text(res.message || '').html() + '</span>'
                    );
                    if (res.status === true && res.redirect) {
                        setTimeout(function () {
                            window.location.href = res.redirect;
                        }, 800);
                    } else {
                        $btn.prop('disabled', false).text('Verify & Login');
                        if (res.redirect && res.expiredSession) {
                            setTimeout(function () { window.location.href = res.redirect; }, 1400);
                        }
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).text('Verify & Login');
                    $('#otp-msg').html('<span class="text-danger">Server error occurred while verifying OTP.</span>');
                }
            });
        }

        var resendTimer = null;
        function startCooldown(seconds) {
            var remaining = parseInt(seconds, 10) || 60;
            if (resendTimer) clearInterval(resendTimer);
            $('#resendOtp').addClass('disabled text-muted').css('pointer-events', 'none').text('Resend (' + remaining + 's)');
            resendTimer = setInterval(function () {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(resendTimer);
                    $('#resendOtp').removeClass('disabled text-muted').css('pointer-events', 'auto').text('Resend');
                } else {
                    $('#resendOtp').text('Resend (' + remaining + 's)');
                }
            }, 1000);
        }

        if (initialCooldown > 0) {
            startCooldown(initialCooldown);
        }

        $('#resendOtp').on('click', function () {
            if ($(this).hasClass('disabled')) return;
            $(this).addClass('disabled').css('pointer-events', 'none').text('Sending...');
            $.ajax({
                url: BASE_URL + 'bid/resendLoginOtp',
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    $('#otp-msg').html(
                        '<span class="' + (res.status ? 'text-success' : 'text-danger') + '">' + $('<div>').text(res.msg || '').html() + '</span>'
                    );
                    if (res.status) {
                        startCooldown(60);
                    } else if (res.wait && res.wait > 0) {
                        startCooldown(res.wait);
                    } else {
                        $('#resendOtp').removeClass('disabled').css('pointer-events', 'auto').text('Resend');
                    }
                },
                error: function () {
                    $('#resendOtp').removeClass('disabled').css('pointer-events', 'auto').text('Resend');
                    $('#otp-msg').html('<span class="text-danger">Failed to resend OTP. Please try again.</span>');
                }
            });
        });
    </script>
</body>
</html>

