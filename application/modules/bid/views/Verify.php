<!DOCTYPE html>
<html lang="zxx">
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="theme_ocean">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>BID Management || Login 2025</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="<?=$theme_path?>/assets/images/favicon.ico">
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/css/bootstrap.min.css">
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/vendors.min.css">
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/css/theme.min.css">
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
      <script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<style>
    .footer {
    padding: 20px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #fff;
    border-top: 1px solid #e5e7eb;
}

</style>
<body>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
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
                    <!-- <h2 class="fs-20 fw-bolder mb-4">Verify <a href="javascript:void(0);" class="float-end fs-12 text-primary">Change Methord</a></h2> -->
                    <h4 class="fs-13 fw-bold mb-2">Verify your account</h4>
                       <?php 
                     if($this->session->flashdata('yes')){
                   ?>
                     <div class="alert alert-success"> 
                       <?php  echo $this->session->flashdata('yes'); ?>
                      </div>
                  <?php    
                  } else if($this->session->flashdata('err')){
                  ?>
                   <div class = "alert alert-danger">
                     <?php echo $this->session->flashdata('err'); ?>
                   </div>
                  <?php } ?>

                   <h4 class="fs-13 fw-bold mb-2">Please enter th e code generated one time password to verify your account.</h4>
                    <p class="fs-12 fw-medium text-muted"><span>A code has been sent to</span> <strong>Registered Email</strong></p>
                    <div id="otp-msg" class="mt-3 text-center"></div> 
                    <form method="post" class="w-100 mt-4 pt-2">
                        <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                        	<input type="hidden" id="Buid" value="<?= $Buid ?>">
                        	
                           <!--  <input class="m-2 text-center form-control rounded otp-input" type="text" id="first" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" id="second" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" id="third" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" id="fourth" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" id="fifth" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" id="sixth" maxlength="1" required> -->
                           <input class="m-2 text-center form-control rounded otp-input" type="text" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" maxlength="1" required>
                            <input class="m-2 text-center form-control rounded otp-input" type="text" maxlength="1" required>
                        </div>
                        <div class="mt-5">
                           <button type="button" onclick="verifyOtp()" class="btn btn-lg btn-primary w-100">
							    Validate
							</button>
                        </div>
                        <div class="mt-5 text-muted">
						    <span>Didn't get the code</span>
						    <a href="javascript:void(0);" id="resendOtp">Resend</a>
						</div>
                    </form> 
                </div>
                 <!-- [ Footer ] start -->
                    <footer class="footer">
                        <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright">
                            <span>Copyright ©</span>
                            <script>
                                document.write(new Date().getFullYear()); 
                            </script>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.i-net.in/" target="_blank">I-Net Secure Labs Pvt Ltd</a>
                        </p> 
                    </footer>
                    <!-- [ Footer ] end -->
            </div> 
        </div> 
    </main> 
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->

    <!--! BEGIN: Vendors JS !-->
    <script src="<?=$theme_path?>/assets/vendors/js/vendors.min.js"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="<?=$theme_path?>/assets/js/common-init.min.js"></script>
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    <script src="<?=$theme_path?>/assets/js/theme-customizer-init.min.js"></script>
    <!--! END: Theme Customizer !-->
</body>
<script>
	var BASE_URL = '<?php echo $this->config->item('base_url');?>';
/* ================= OTP INPUT UX ================= */
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

/* ================= VERIFY OTP ================= */
function verifyOtp() {

    let otp = '';
    $('.otp-input').each(function () {
        otp += $(this).val();
    });

    if (otp.length !== 6) {
        $('#otp-msg').html('<span class="text-danger">Enter 6-digit OTP</span>');
        return;
    }

    $.ajax({
        url: BASE_URL + "bid/VerifyOtpAjax", 
        type: "POST",
        dataType: "json",
        data: {
            otp: otp,
            Buid: $('#Buid').val()
        },
        success: function (res) {

            console.log(res);

            $('#otp-msg').html(
                `<span class="${res.status ? 'text-success' : 'text-danger'}">${res.message}</span>`
            );

            if (res.status === true) {
                setTimeout(function () {
                    window.location.href = res.redirect;
                }, 1200);
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            $('#otp-msg').html('<span class="text-danger">Server error occurred</span>');
        }
    });
}

/* ================= RESEND OTP ================= */
let resendCooldown = 60;
$('#resendOtp').click(function () {

    if ($(this).hasClass('disabled')) return;

    $.ajax({
        url: "<?= base_url('bid/ResendOtp/'.$Buid) ?>",
        type: "GET",
        dataType: "json",
        success: function (res) {
            $('#otp-msg').html(
                `<span class="${res.status ? 'text-success' : 'text-danger'}">${res.msg}</span>`
            );

            if (res.status) startCooldown();
        }
    });
});

function startCooldown() {
    $('#resendOtp').addClass('disabled').css('pointer-events','none');
    let timer = setInterval(() => {
        resendCooldown--;
        $('#resendOtp').text(`Resend (${resendCooldown}s)`);

        if (resendCooldown <= 0) {
            clearInterval(timer);
            resendCooldown = 60;
            $('#resendOtp').removeClass('disabled')
                           .css('pointer-events','auto')
                           .text('Resend');
        }
    }, 1000);
}
</script>
</html>