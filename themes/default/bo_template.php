<!DOCTYPE html>
<html lang="en">
    <head>
    
 <style type="text/css"> 
  .mobileShow {display: none;} 
 #mob_shw { top:5px; }
  /* Smartphone Portrait and Landscape */ 
  @media only screen 
    and (min-device-width : 320px) 
    and (max-device-width : 480px){ 
      .mobileShow {display: inline;}
	  #mob_shw { top:65px; }
  }
  
</style>

<style>
.rounded-image {
  border-radius: 160px;
  overflow: hidden;
  
}
 
  
#myMenu {  list-style-type: none; padding: 0; margin: 0; } 
#myMenu li a { backgrxound-color: #f6f6f6; padding: 14px; text-decoration: none; color:#333; display: block } 
.children a:hover { background-color: #eee;}
#myMenu li a select { color: #fff; }

.pckge_styl { font-weight:bold; color:#600;  margin-top:6px; }
</style>

<?php 
	$user_det = $this->session->userdata('logged_in'); 
	if(empty($user_det)) { redirect($this->config->item('base_url').'admin/index'); } 
?>
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">                  
        <link href="<?=$theme_path?>/images/favicon.png" rel="shortcut icon">
        <title> Election 2019 | Election Surveillance </title>
		<link type="text/css" href="<?=$theme_path?>/css/style.default.css" rel="stylesheet">
       
        <link type="text/css" href="<?=$theme_path?>/css/select2.css" rel="stylesheet" />
        <link type="text/css" href="<?=$theme_path?>/css/style.datatables.css" rel="stylesheet">
        <link type="text/css" href="<?=$theme_path?>/css/dataTables.bootstrap.css" rel="stylesheet">
       
        <link href="<?=$theme_path?>/css/jquery-ui.css" rel="stylesheet" />
        <link type="text/css" href="<?=$theme_path?>/css/menu.css" rel="stylesheet" />
        <link href="<?=$theme_path?>/css/media_print.css" rel="stylesheet" />
        <script src="<?= $theme_path; ?>/js/jquery-1.8.2.js" type="text/javascript"></script>
		<script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-ui.js"></script>
		<script type="text/javascript">
		var BASE_URL = '<?php echo $this->config->item('base_url');  ?>';
		var Them_path = '<?= $theme_path; ?>';
		</script>
      
        <!-- Datepicker -->
        
		<script>
			$(function() {
			$( ".date" ).datepicker({
				  changeMonth: true,
				  changeYear: true,
				  dateFormat: "dd-mm-yy",
				  yearRange: "c-90:c+5"
				});
			});
        </script>
    </head>

    <body>
  <!-- Checking My profile-->
  
 	
           <div   class="headerwrapper mobileShow">
                <div class="header-left">
                   
                    <div class="pull-right">
                        <a href="#" class="menu-collapse">
                            <i class="fa fa-bars"></i>
                        </a>
                    </div>
                </div><!-- header-left -->
                
                <div class="header-right">
                	<div class="pull-left">
                     
					</div> 
                    
                </div><!-- header-right -->
 			 
            </div><!-- headerwrapper -->
           
     
 		   
          
          <section>
          
            <div class="mainwrapper" id="mob_shw" style="" >
                <div class="leftpanel">
     
                    <div class="media profile-left">
                 <a class="pull-left  profile-thumb ">
				<img  class="rounded-image" src="<?=$this->config->item('base_url')?>tep_img/logo.png"  height="45" width="50"  />
                    </a> 
                  
					<div class="pckge_styl">   <span style="font-size:18px"> Welcome !!! </span>
                    </div> 
                    <div class="media-body">
                       <h4 class="media-heading">					   
                        <a class="pull-left"><small class="text-muted "> <span style="color:#000; font-weight:700; font-size:16px"> <?=$user_det['op_id']?> </span> </small> </a>
                       </h4>
                         
                    
                  </div>          	
                         
                 </div><!-- media -->

                   <!-- Active Class and Children Class-->
                   <?php
				   // Config Screen
				   $user_det = $this->session->userdata('logged_in');
				   
				   $ftch_class = $this->router->fetch_class(); //this is for health insurance
				   $ftch_method = $this->router->fetch_method();
				   $config=0;
				   $config_display=0;
				   if($ftch_class=='category' || $ftch_class=='sub_category' || $ftch_class=='branch' || $ftch_class=='bo_comission' || $ftch_class=='master' || $ftch_class=='flash_msg' || $ftch_class=='inet_notice' || $ftch_class=='inet_offer' || $ftch_class=='product')
					{
						$config='active';
						$config_display='block';
					}
					else
					{
						$config='';
						$config_display='none';
					}
					
					// All Users
					if($ftch_method=='user_history_list' || $ftch_method=='allot_history_list'|| $ftch_method=='pc_oper_user_history_list' )
					{
						$opr_id='active';
					}
					else
					{
						$opr_id='';
					}
				  
					if($ftch_method=='pc_allot_operator_list'|| $ftch_method=='st_oper_new_list'|| $ftch_method=='oper_st_view')
					{
						$pc_at_id='active';
					}
					else
					{
						$pc_at_id='';
					}
					
					if($ftch_method=='zn_pc_allot_operator_list'|| $ftch_method=='zn_st_oper_new_list' || $ftch_method=='oper_zn_st_oper_new_list')
					{
						$zn_pc_at_id='active';
					}
					else
					{
						$zn_pc_at_id='';
					}
					
					if($ftch_method=='pc_alloted_history_list' )
					{
						$pc_id='active';
					}
					else
					{
						$pc_id='';
					}
					
					if($ftch_method=='vacant_history_list' )
					{
						$vac_id='active';
					}
					else
					{
						$vac_id='';
					}
				 
					if($ftch_method=='all_allot_history_list' )
					{
						$al_at_id='active';
					}
					else
					{
						$al_at_id='';
					}
						
					if($ftch_method=='pc_sup_history_list' || $ftch_method=='zn_sup_st_history_list'  || $ftch_method=='sup_zn_sup_st_history_list')
					{
						$pc_sup_id='active';
					}
					else
					{
						$pc_sup_id='';
					}
					
					if($ftch_method=='sta_sup_st_history_list' || $ftch_method=='sta_pc_sup_history_list' || $ftch_method=='sup_st_view')
					{
						$st_pc_sup_id='active';
					}
					else
					{
						$st_pc_sup_id='';
					}
					
					if($ftch_method=='sup_history_list' || $ftch_method=='sup_allot_list' || $ftch_method=='pc_new_sup_history_list')
					{
						$sup_id='active';
					}
					else
					{
						$sup_id='';
					}
			 
					if($ftch_method=='user_reg')
					{
						$ureg_id='active';
					}
					else
					{
						$ureg_id='';
					}
					
					if($ftch_method=='pc_user_reg')
					{
						$preg_id='active';
					}
					else
					{
						$preg_id='';
					}
					
					if($ftch_method=='attend_list' || $ftch_method=='attend_sup_view_list')
					{
						$att_id='active';
					}
					else
					{
						$att_id='';
					}
					
					if($ftch_method=='upload_det')
					{
						$mat_map='active';
					}
					else
					{
						$mat_map='';
					}
 
					if($ftch_method=='zonal_alloted_history_list' || $ftch_method=='zonal_history_list')
					{
						$zc_id='active';
					}
					else
					{
						$zc_id='';
					}
					
					if($ftch_method=='user_ps_view_list' || $ftch_method=='user_get_ps_all_list')
					{
						$st_ps='active';
					}
					else
					{
						$st_ps='';
					}
					
					if($ftch_method=='zonal_ps_view_list' || $ftch_method=='zonal_get_ps_all_list')
					{
						$zn_ps='active';
					}
					else
					{
						$zn_ps='';
					}
					
					if($ftch_method=='pc_ps_view_list' || $ftch_method=='get_ps_all_list')
					{
						$pc_ps='active';
					}
					else
					{
						$pc_ps='';
					} 
					
					if($ftch_method=='sup_ps_view_list' )
					{
						$sp_ps='active';
					}
					else
					{
						$sp_ps='';
					} 
					
					if($ftch_method=='dashboard' || $ftch_method=='allot_oper_list')
					{
						$dashboard='active';
					}
					else
					{
						$dashboard='';
					}
						
					$all_users=0;
					$all_users_display=0;
					if($ftch_class=='users' || $ftch_method=='bo_list')
					{
						$all_users='active';
						$all_users_display='block';
					}
					else
					{
						$all_users='';
						$all_users_display='none';
					}
				 ?>
                 
         <!-- END Active Class and Children Class-->
           <ul id="myMenu" class="nav nav-pills nav-stacked" > <!-- clsroll id="style-4"-->
          
                <!--NEW MENU-->
                 <?php if($user_det['user_type_id'] == '2' ) {  
				      $reg_id = urlencode(base64_encode(base64_encode(base64_encode(base64_encode(1)))));  
                   }  ?>
                 <?php  if($user_det['user_type_id'] == '3' ) { 
				      $reg_id = urlencode(base64_encode(base64_encode(base64_encode(base64_encode(2)))));  
				      }  ?>
                   <?php if($user_det['user_type_id'] == '4' ) { 
                  $reg_id = urlencode(base64_encode(base64_encode(base64_encode(base64_encode(4)))));  
                  }  ?>      
				  <?php if($user_det['user_type_id'] == '5' ) { 
                  $reg_id = urlencode(base64_encode(base64_encode(base64_encode(base64_encode(5)))));  
                  }  ?> 
                         
            <li class="<?=$dashboard?>"><a href="<?php echo $this->config->item('base_url');?>admin/dashboard/"> <span style="color:#000"> &nbsp; Dashboard</span></a></li>
                       
            <?php if($user_det['user_type_id'] == '2' || $user_det['user_type_id'] == '3') {  
               ?>    
                <li class="<?=$ureg_id?>"><a href="<?php echo $this->config->item('base_url');?>admin/user_reg/<?=$reg_id?>"> <span style="color:#000"> &nbsp; Registration</span></a></li>
                
                 <?php } else if($user_det['user_type_id'] == '4' || $user_det['user_type_id'] == '5') { ?>
                <li class="<?=$preg_id?>"><a href="<?php echo $this->config->item('base_url');?>admin/pc_user_reg/<?=$reg_id?>"> <span style="color:#000"> &nbsp; Registration</span></a></li>
               
                 <?php } ?>
                
                 <?php if($user_det['user_type_id'] == '2' || $user_det['user_type_id'] == '3') {  ?>
                   
                <li class="<?=$opr_id?>"><a href="<?=$this->config->item('base_url'),'admin/user_history_list'?>"> <span style="color:#000"> &nbsp; Operators</span></a></li>
               
                <?php } ?> 
                  <?php if($user_det['user_type_id']== '2'){ ?>
                  <li class="<?=$sp_ps?>"><a href="<?=$this->config->item('base_url'),'admin/sup_ps_view_list'?>"> <span style="color:#000"> &nbsp; Polling Station List </span></a></li>
                   <?php } ?> 
                   
                <?php if($user_det['user_type_id'] == '3') { ?>
  
                  <li class="<?=$sup_id?>"><a href="<?=$this->config->item('base_url'),'admin/sup_history_list'?>"> <span style="color:#000"> &nbsp; Supervisors</span></a></li>
                    <li class="<?=$pc_ps?>"><a href="<?=$this->config->item('base_url'),'admin/pc_ps_view_list'?>"> <span style="color:#000"> &nbsp; Polling Station List </span></a></li>
                 
                <?php }  ?>  
                   
               <?php if($user_det['user_type_id'] == '4' || $user_det['user_type_id'] == '5') {  ?>
               
  				 <?php if($user_det['user_type_id'] == '5' ) {  ?>
    				<li class="<?=$pc_at_id?>"><a href="<?=$this->config->item('base_url'),'admin/st_oper_new_list'?>"> <span style="color:#000"> &nbsp; Operators</span></a></li>
                <?php } else { ?> 
                     <li class="<?=$zn_pc_at_id?>"><a href="<?=$this->config->item('base_url'),'admin/zn_st_oper_new_list'?>"> <span style="color:#000"> &nbsp; Operators</span></a></li>
                <?php }  ?> 
                
                <?php if($user_det['user_type_id'] == '5' ) {  ?>
       				<li class="<?=$st_pc_sup_id?>"><a href="<?=$this->config->item('base_url'),'admin/sta_sup_st_history_list'?>"> <span style="color:#000"> &nbsp; Supervisors</span></a></li>
                <?php } else { ?> 
                     <li class="<?=$pc_sup_id?>"><a href="<?=$this->config->item('base_url'),'admin/zn_sup_st_history_list'?>"> <span style="color:#000"> &nbsp; Supervisors</span></a></li>
                <?php }  ?>  
                    <li class="<?=$pc_id?>"><a href="<?=$this->config->item('base_url'),'admin/pc_alloted_history_list'?>"> <span style="color:#000"> &nbsp; Parliament Constitution</span></a></li>
                    
                <?php }  ?>  
                   
              <?php if($user_det['user_type_id'] == '5') {  ?>
            	<li class="<?=$zc_id?>"><a href="<?=$this->config->item('base_url'),'admin/zonal_history_list'?>"> <span style="color:#000"> &nbsp; Zonal </span></a></li>
                <li class="<?=$st_ps?>"><a href="<?=$this->config->item('base_url'),'admin/user_ps_view_list'?>"> <span style="color:#000"> &nbsp; Polling Station List </span></a></li>
              <?php }  ?>  
                <?php if($user_det['user_type_id'] == '4') {  ?>
               <li class="<?=$zn_ps?>"><a href="<?=$this->config->item('base_url'),'admin/zonal_ps_view_list'?>"> <span style="color:#000"> &nbsp; Polling Station List </span></a></li>
                 <?php }  ?>  
                <li class="<?=$att_id?>"><a href="<?php echo $this->config->item('base_url');?>admin/attend_list"><span style="color:#000"> &nbsp; Attendance</span></a></li>
                
                <?php if($user_det['user_type_id'] == '5') {  ?>
                
                <li class="<?=$mat_map?>"><a href="<?php echo $this->config->item('base_url');?>admin/upload_det"><span style="color:#000"> &nbsp; Material Mapping </span></a></li>
                
                <?php } ?>
                
                <li class=""><a href="<?=$this->config->item('base_url'),'admin/logout'?>"> <span style="color:#000"> &nbsp; Log Out</span></a></li>
       </ul>
   </div><!-- leftpanel -->
                
        <div class="mainpanel">
        <div class="pageheader"> <!--style="height:100px"-->
                <div class="media">
                    
                  <?php /*?><div style="width:100%; padding-top:15px; margin-left:15%;">
                     <table align="center" width="100%" class="pulse-shrink">
                       <tr style="color:green; font-size:22px; font-weight:bold; margin-left:100px;">
                         <td class="fls_txt">
                          <a target="_blank" href="http://www.avslive.in/ieba"> Click to Watch, </a> IeBA 2k19 Award Ceremony Function. Live Streaming starts Today @2:00PM
                           
                           <a href="<?php echo $this->config->item('base_url');?>admin/state_topper"> Click to View, </a> IeBA 2k19 Award Winners (I-NET CSC 2018 Best Performers)
                         </td>
                        </tr>
                      </table>     
                   </div> <?php */?>
                                    
                   
<div style="width:100%; padding-top:1px;">
                        <table align="center" width="100%">
                             <center>
                              <span style="color:green; font-size:25px; font-weight:bold;">   LOK SABHA ELECTION 2019 - TAMIL NADU </span> 
                              </center>
                         </table>
                    </div>
                                                     
<style>
@-webkit-keyframes blinker
{
  from {opacity: 1.0;}
  to {opacity: 0.0;}
 
}
.blink{
	text-decoration: blink;
	-webkit-animation-name: blinker;
	-webkit-animation-duration: 2.5s;
	-webkit-animation-iteration-count:infinite;
	-webkit-animation-timing-function:ease-in-out;
	-webkit-animation-direction: alternate;
}


@keyframes blink {  
    0% { color: red; }
    100% { color: green; }
}
@-webkit-keyframes blink {
    0% { color: red; }
    100% { color: green; }
}
.fls_txt {
    -webkit-animation: blink 2s linear infinite;
    -moz-animation: blink 2s linear infinite;
    -ms-animation: blink 2s linear infinite;
    -o-animation: blink 2s linear infinite;
    animation: blink 2s linear infinite;
} 
</style>
            
                            <div class="media-body">
                                <h4></h4>
                            </div>
              </div><!-- media -->
          </div><!-- pageheader -->
          <div id="list_service"></div>
             <div class="contentpanel">
                       
        <script type="text/javascript">
		function for_loading(txt)
		{
			//THIS IS FOR NOTIFICATION WHEN AJAX LOAD STARTS, CODE STARTS HERE 
			$('#dyna_div').addClass('my_alert-info').removeClass('my_alert-success');
			$('#tick_img_spn').css('display','none');
			$('#load_img_div').css('display','block');
			$('#main_load_div').css('display','block');
			$('#cls_inf_bt').css('display','none');
			$('#info_txt').html(txt);
			//THIS IS FOR NOTIFICATION WHEN AJAX LOAD STARTS, CODE ENDS HERE
		}
		function for_response(txt) 
		{
			//THIS IS FOR NOTIFICATION WHEN AJAX LOAD RESPONSE CAME CODE STARTS HERE
			$('#dyna_div').addClass('alert alert-success').removeClass('alert alert-success');
			$('#main_load_div').css('display','block');			
			$('#cls_inf_bt').css('display','block');
			$('#load_img_div').css('display','none');
			$('#tick_img_spn').css('display','block');
			$('#info_txt').html(txt);
			setTimeout(function(){
			$('#main_load_div').css('display','none');
			}, $('#aja_notf_time').val());
			//THIS IS FOR NOTIFICATION WHEN AJAX LOAD RESPONSE CAME CODE ENDS HERE	
		}
		
		 $(document).ready(function()
 		 {
			$('#cls_inf_bt').click(function(){ $('#main_load_div').css('display','none');}); 
		 });
		</script>
        
       <input name="" type="hidden" value="1000" id="aja_notf_time">
        <!--AJAX LOADING AND NOTIFICATIONS ENDS HERE-->
        
                    <?php echo $content;?>
                    <!-- contentpanel -->
                    </div>
              </div><!-- mainpanel -->
            </div><!-- mainwrapper -->
        </section>
		
        <script type="text/javascript" src="<?=$theme_path?>/js/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="<?=$theme_path?>/js/jquery-ui-1.10.3.min.js"></script>
        <script type="text/javascript" src="<?=$theme_path?>/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?=$theme_path?>/js/bootstrap-wizard.min.js"></script>
       
        <!--  dataTables  -->
        <script type="text/javascript" src="<?=$theme_path?>/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?=$theme_path?>/js/dataTables.bootstrap.js"></script>
        <script type="text/javascript" src="<?=$theme_path?>/js/dataTables.responsive.js"></script>
        <script type="text/javascript" src="<?=$theme_path?>/js/select2.min.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                
                jQuery('#basicTable,#basicTable1,#basicTable2,#basicTable3').DataTable({
                    responsive: true
                });
                
                var shTable = jQuery('#shTable').DataTable({
                    "fnDrawCallback": function(oSettings) {
                        jQuery('#shTable_paginate ul').addClass('pagination-active-dark');
                    },
                    responsive: true
                });
                
                // Show/Hide Columns Dropdown
                jQuery('#shCol').click(function(event){
                    event.stopPropagation();
                });
                
                jQuery('#shCol input').on('click', function() {

		<!--Notification JSON coding-->

	

                    // Get the column API object
                    var column = shTable.column($(this).val());
 
                    // Toggle the visibility
                    if ($(this).is(':checked'))
                        column.visible(true);
                    else
                        column.visible(false);
                });
                
                var exRowTable = jQuery('#exRowTable').DataTable({
                    responsive: true,
                    "fnDrawCallback": function(oSettings) {
                        jQuery('#exRowTable_paginate ul').addClass('pagination-active-success');
                    },
                    "ajax": "ajax/objects.txt",
                    "columns": [
                        {
                            "class":          'details-control',
                            "orderable":      false,
                            "data":           null,
                            "defaultContent": ''
                        },
                        { "data": "name" },
                        { "data": "position" },
                        { "data": "office" },
                        { "data": "salary" }
                    ],
                    "order": [[1, 'asc']] 
                });
                
                // Add event listener for opening and closing details
                jQuery('#exRowTable tbody').on('click', 'td.details-control', function () {
                    var tr = $(this).closest('tr');
                    var row = exRowTable.row( tr );
             
                    if ( row.child.isShown() ) {
                        // This row is already open - close it
                        row.child.hide();
                        tr.removeClass('shown');
                    }
                    else {
                        // Open this row
                        row.child( format(row.data()) ).show();
                        tr.addClass('shown');
                    }
                });
               
                
                // DataTables Length to Select2
                jQuery('div.dataTables_length select').removeClass('form-control input-sm');
                jQuery('div.dataTables_length select').css({width: '60px'});
                jQuery('div.dataTables_length select').select2({
                    minimumResultsForSearch: -1
                });
    
            });
            
            function format (d) {
                // `d` is the original data object for the row
                return '<table class="table table-bordered nomargin">'+
                    '<tr>'+
                        '<td>Full name:</td>'+
                        '<td>'+d.name+'</td>'+
                    '</tr>'+
                    '<tr>'+
                        '<td>Extension number:</td>'+
                        '<td>'+d.extn+'</td>'+
                    '</tr>'+
                    '<tr>'+
                        '<td>Extra info:</td>'+
                        '<td>And any further details here (images etc)...</td>'+
                    '</tr>'+
                '</table>';
            }
        </script>

        
        <!-- Notification-->
		<?php /*?><script src="<?=$theme_path?>/js/notification/jquery.easing.min.js"></script><?php */?>
        <script type="text/javascript" src="<?=$theme_path?>/js/notification/jquery.easy-ticker.js"></script>
        <script type="text/javascript">
        $(document).ready(function(){  
		
		 
            var dd = $('.vticker').easyTicker({
                direction: 'up',
                easing: 'easeInOutBack',
                speed: 'slow',
                interval: 4000,
                height: 'auto',
                visible: 4,
                mousePause: 0,
                controls: {
                    up: '.up',
                    down: '.down',
                    toggle: '.toggle',
                    stopText: 'Stop !!!'
                }
            }).data('easyTicker');            
            cc = 1;
            $('.aa').click(function(){
                $('.vticker ul').append('<li>' + cc + ' Triangles can be made easily using CSS also without any images. This trick requires only div tags and some</li>');
                cc++;
            });            
            $('.vis').click(function(){
                dd.options['visible'] = 3;                
            });
            
            $('.visall').click(function(){
                dd.stop();
                dd.options['visible'] = 0 ;
                dd.start();
            });            
        });
        </script>
        
        <script type="text/javascript" src="<?=$theme_path?>/js/jquery.gritter.min.js"></script>
        <script type="text/javascript" src="<?=$theme_path?>/js/custom.js"></script>
    </body>
</html>