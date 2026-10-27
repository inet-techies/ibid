<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $this->config->item('site_title'); ?> | <?= $this->config->item('site_powered'); ?> </title>
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');  ?>
<link rel="shortcut icon" href="<?= $theme_path; ?>/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?= $theme_path; ?>/css/style.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/jquery-ui-1.9.1.custom.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/jquery.multiselect.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/jquery.multiselect.filter.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" media="print" href="<?= $theme_path; ?>/css/bootstrap-print.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/bootstrap-responsive.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/demo_table.css"></link>
<script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-1.8.2.js"></script>
<script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-ui-1.9.1.custom.min.js"></script>
<script type="text/javascript" src="<?= $theme_path; ?>/js/jquery.multiselect.min.js"></script>
<!-- <script type="text/javascript" src="<?= $theme_path; ?>/js/jquery.multiselect.filter.js"></script>
<!--<script type="text/javascript" src="<?= $theme_path; ?>/js/jQuery.Validate.min.js"></script>-->
<script type="text/javascript" src="<?= $theme_path; ?>/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?= $theme_path; ?>/js/common_validation.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/datepicker.css"></link>
<?php $check_validate = array('admin'); ?>
<?php if(in_array($this->uri->segment(1),$check_validate)): ?>
<script type="text/javascript" src="<?= $theme_path; ?>/js/jQuery.Validate.min.js"></script>
<?php endif; ?>

<script type="text/javascript">

var BASE_URL = '<?php echo $this->config->item('base_url');  ?>';
 $(function(){
	$(".multi-select, #color-select, #size-select").multiselect();
	
if($('form input.required').length > 0 ) {
	$('form').submit(function(){
	
	var validator = $(this).validate({
	
	errorPlacement : function(error,element){  
								
								element.css('border-color','#f00');
								
								$(element).attr('title',$(error).html());
								
								$(element).tooltip('fixTitle').tooltip('show');
	},
		
	 onkeyup: function(element) {  if($(element).valid()) {  $(element).tooltip('hide'); $(element).css('border-color' ,'');  $(element).focus();     } else {    }    }
	
	});
	
	if(validator.form()) { return true; }
	
	else return false;
	
	});
	
	}
});


$(document).ready(function(){

$('input:text').keyup(function(){
    this.value = this.value.toUpperCase();
});

$('input:text').change(function(){
    this.value = this.value.toUpperCase();
});

var print_val = window.location.pathname.split("/");

var print_ele = print_val[print_val.length-1];

if(print_ele == 'print'){

window.print();

}

//console.log($("table#data-table-pagiante").length);

if($("table#data-table-pagiante").length > 0 ) {
$("table#data-table-pagiante").dataTable();
}
$("input:text").tooltip({ "placement" : "right", "trigger" : "manual" });

});

</script>

</head>
<body>
	<div id="wrap">
        <!-- Begin page content -->
        <div class="container">
        
        	<div class="page-head" id="header-container">
            
               <div class="logo">
                <a href="<?= $this->config->item('base_url') ?>"><img src="<?= $theme_path; ?>/images/logo.png"/> </a>
              </div>
              <div align="right" class="right_header">
                <p class="text-success"><a href="<?= $this->config->item('base_url') ?>users/profile" class="label label-success">Welcome <?= $this->user_auth->get_username(); ?></a> | <a href="<?= $this->config->item('base_url') ?>users/logout" class="label label-important logout">Logout</a>
                </p>
              </div>
              
			  <!-- navbar -->
              <div class="navbar">
                <div class="navbar-inner">
                    <div class="container">
                        <ul class="nav">
                        	<li><a href="<?= $this->config->item('base_url') ?>"><i class="icon-home icon-white"></i> Home</a></li>
                            <li class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="">Masters</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/firms" title="">Firms</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/vendors" title="">Vendor</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/consignees" title="">Consignee</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/customers" title="">Customer</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/shipto" title="">Ship to</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/forwarder" title="">Forwarder</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/employee" title="">Employee</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/couriers" title="">Couriers</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>admin/masters" title="">Masters</a></li>
                                </ul>
                            
                            </li>
							
							<li class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="">Styles</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= $this->config->item('base_url') ?>merchandiser/style_list" title="">Styles List</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>merchandiser/style_creation" title="">Style Creation</a></li>
								</ul>
							</li>

                            <li class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="">Orders</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= $this->config->item('base_url') ?>merchandiser/orders_list" title="">Orders List</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>merchandiser/order_creation" title="">Order Creation</a></li>
                                 </ul>
                             </li>
                             
                            <li class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="">Shipping</a>
                                <ul class="dropdown-menu">

                                    <li><a href="<?= $this->config->item('base_url') ?>merchandiser/package_list/" title="">View Packing List</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>merchandiser/package_creation/" title="">Create Packing List</a></li>
								   <li><a href="<?= $this->config->item('base_url') ?>shipping/view_invoice" title="">Create Shipping Advice</a></li>
								   <li><a href="<?= $this->config->item('base_url') ?>shipping/shipadvice" title="">View Shipping Advice</a></li>
								    <li><a href="<?= $this->config->item('base_url') ?>shipping/createshipdoc" title="">Create Shipping Document</a></li>
								     <li><a href="<?= $this->config->item('base_url') ?>shipping/viewshipdoc" title="">View Shipping Document</a></li>


							 	</ul>
							</li>
							 
							<li class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="">Billing</a>
                                <ul class="dropdown-menu">
                                
									<li><a href="<?= $this->config->item('base_url') ?>manager/invoice_list/commercial" title="">View Commercial Invoice</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>manager/create_invoice/commercial" title="">Create Commercial Invoice</a></li>
                                    
									<li><a href="<?= $this->config->item('base_url') ?>manager/invoice_list/commission" title="">View Commission Invoice</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>manager/commission_invoice" title="">Create Commission Invoice</a></li>

                                    <li><a href="<?= $this->config->item('base_url') ?>manager/invoice_list/air_freight/" title="">View Freight Invoice</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>manager/air_freight_invoice" title="">Create Freight Invoice</a></li>
                                    
									<li><a href="<?= $this->config->item('base_url') ?>manager/invoice_list/ldp" title="">View LDP Invoice</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>manager/ldp_invoice" title="">Create LDP Invoice</a></li>

							 	</ul>
							</li>
                            
                            <li  class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="">Reports</a>
                            	<ul class="dropdown-menu">
								   <li><a href="<?= $this->config->item('base_url') ?>reportsview/booked" title="">Booked</a></li>
                                    <li><a href="<?= $this->config->item('base_url') ?>reportsview/shipped" title="">Shipped</a></li>
                                     <li><a href="<?= $this->config->item('base_url') ?>reportsview/billing" title="">Billing</a></li>
                                      <li><a href="<?= $this->config->item('base_url') ?>reportsview/receipts" title="">Receipts</a></li>
							 	</ul>
                            </li>
                            
                              <li  class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="">Entry</a>
                            	<ul class="dropdown-menu">
								   <li><a href="<?= $this->config->item('base_url') ?>adminentry/payment_invoice" title="">Payment Receipt Entry</a></li>
                                   
							 	</ul>
                            </li>
                            

							
<!--                            <li><a href="<?= $this->config->item('base_url') ?>tasks/add">Quote</a></li>
                            <li><a href="<?= $this->config->item('base_url') ?>tasks/add">Order Status</a></li>
                            <li><a href="<?= $this->config->item('base_url') ?>tasks/add">Reports</a></li>
                            <li><a href="<?= $this->config->item('base_url') ?>tasks/add">Shipment Tracking</a></li>
                            <li><a href="<?= $this->config->item('base_url') ?>tasks/add">Couriers</a></li>-->
                            <?php //if ($this->user_auth->is_admin()): ?>
            
                            <?php //endif; ?>
                        </ul>
                    </div>
                </div>
              </div> <!-- navbar -->
        	</div>
        
            <div class="center_content">
                <?php echo $content; ?>
            </div>   <!--end of center content -->   
        </div>
		<div id="push"></div>
	</div>   
    
    <div id="footer">
      <div class="container">
       <p class="muted credit">&copy; 2012-2013 <?= $this->config->item('site_title'); ?> | <a href="<?= $this->config->item('powered_url'); ?>"><?= $this->config->item('site_powered'); ?></a> | <a href="<?= $this->config->item('powered_url'); ?>"><?= $this->config->item('powered_raw_url'); ?></a> </p>
      </div>
    </div>
  
    

<script type="text/javascript" src="<?= $theme_path; ?>/js/bootstrap.js"></script>
<script type="text/javascript" src="<?= $theme_path; ?>/js/bootstrap-typeahead.js"></script>
<script type="text/javascript" src="<?= $theme_path; ?>/js/bootstrap-datepicker.js"></script>

</body>
</html>

