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
                    <img src="<?=$theme_path?>/assets/images/auth/auth-cover-login-bg.svg" alt="" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="auth-cover-sidebar-inner">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5">
                    <div class="wd-50 mb-5">
                        <img src="<?=$theme_path?>/assets/images/logo-inet.png" alt="" class="img-fluid">
                    </div>
                    <h2 class="fs-20 fw-bolder mb-4">Login</h2>
                    <h4 class="fs-13 fw-bold mb-2">Login to your account</h4>
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
                    <form  method="post" action="<?php echo $this->config->item('base_url'); ?>bid/CheckLogin" onsubmit="return validate();" autocomplete="off" class="w-100 mt-4 pt-2">
                        <div class="mb-4">
                            <input type="email" class="form-control" placeholder="Email" id="EmailId" name="EmailId" value="" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" id="EmailPass" name="EmailPass" class="form-control" placeholder="Password" value="" required>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="rememberMe">
                                    <label class="custom-control-label c-pointer" for="rememberMe">Remember Me</label>
                                </div>
                            </div>
                            <div>
                                <a href="auth-reset-cover.html" class="fs-11 text-primary">Forget password?</a>
                            </div>
                        </div>
                        <div class="mt-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100">Login</button>
                        </div>
                    </form>
                 
                   <!--  <div class="mt-5 text-muted">
                        <span> Don't have an account?</span>
                        <a href="auth-register-cover.html" class="fw-bold">Create an Account</a>
                    </div> -->
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

</html>