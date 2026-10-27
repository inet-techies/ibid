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
                    <img src="<?=$theme_path?>/assets/images/auth/auth-cover-resetting-bg.svg" alt="" class="img-fluid">
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
                    <h4 class="fs-13 fw-bold mb-2">Update Your Password</h4>
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

                   <h4 class="fs-13 fw-bold mb-2">Password should be at least 8 characters long and include a mix of letters and numbers.</h4> 
                      <form method="post"  action="<?php echo $this->config->item('base_url'); ?>bid/UpdatePasswordDetails/<?php echo $Bid = urlencode(base64_encode(base64_encode(base64_encode(base64_encode($RegMail[0]['Buid'])))));;?>" onsubmit="return changevalidate()" class="w-100 mt-4 pt-2">
                       <div class="col-xl-12">
                
                                <div class="mb-4">
                                    <label class="form-label">New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="NPassInput" name="NPassInput" placeholder="Enter New Password">
                                      <span id="NPassInput_err" style="color:#F00; font-style:system-ui;"></span>
                                </div> 
                                <div class="mb-4">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="NCPassInput" name="NCPassInput"  placeholder="Enter Confirm Password">
                                     <span id="NCPassInput_err" style="color:#F00; font-style:system-ui ;"></span>
                                </div> 
                    </div>
                       
                        <div class="mt-5">
                           <button type="submit" class="btn btn-lg btn-primary w-100">
							    Submit
							</button>
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
	
	 function changevalidate() {
  
  var i=0;
  
    var NPassInput=$("#NPassInput").val();
     //alert("pass");
     var ffilter=/^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
    if(NPassInput=="" || NPassInput==null || NPassInput.trim().NPassInput==0)
    {
      //alert("avg");
      $("#NPassInput_err").html("Enter Password");
      i=1;
    }
    else if(!ffilter.test(NPassInput))
    {
     // alert("fail");
      $("#NPassInput_err").html("Minimum 5 and upto 15 characters, at least one uppercase letter, one lowercase letter, one number and one special character:");
      i=1;
    }
    else
    {
      $("#NPassInput_err").html("");
    }


     var NPassInput = $('#NPassInput').val();
      var NCPassInput = $('#NCPassInput').val();
      if (NCPassInput == "") {
          $('#NCPassInput_err').html('Re-Enter password cannot be empty');
          i=1;
      } else if (NPassInput !== NCPassInput) {
          $('#NCPassInput_err').html('Re-Entered password did not match');
          i=1;
      } else {
          $('#NCPassInput_err').html('');
          
      }

   if(i==1)
    {
      return false;
    }
    else
    {
      
      return true;
    }

   }

  $("#NPassInput").on('blur',function()
    {
      var NPassInput=$("#NPassInput").val();
      //alert(" hlo");
        //var lnfilter=/^[0-9]/;
        var ffilter=/^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
        if(NPassInput=="" || NPassInput==null || NPassInput.trim().NPassInput==0)
        {
          //alert("hi");
          $("#NPassInput_err").html("Enter Password");
          i=1;
        }
        else if(!ffilter.test(NPassInput))
        {
          $("#NPassInput_err").html("Minimum 5 and upto 15 characters, at least one uppercase letter, one lowercase letter, one number and one special character:");
          i=1;
        }
        else
        {
          //alert(3);
          $("#NPassInput_err").html("");
        }

    });

  $("#NCPassInput").on('blur',function()
    {
       var NPassInput = $('#NPassInput').val();
      var NCPassInput = $('#NCPassInput').val();
      if (NCPassInput == "") {
          $('#NCPassInput_err').html('Confirm password cannot be empty');
          i=1;
      } else if (NPassInput !== NCPassInput) {
          $('#NCPassInput_err').html('Password and Confirm Password did not Match..!');
          i=1;
      } else {
          $('#NCPassInput_err').html('');
          
      }
    });

</script>
</html>