<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= $this->config->item('site_title'); ?> | <?= $this->config->item('site_powered'); ?> </title>
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');  ?>
<link rel="shortcut icon" href="<?= $theme_path; ?>/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?= $theme_path; ?>/css/style.css" />
<!--<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/niceforms-default.css" />-->
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/jquery-ui-1.9.1.custom.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/jquery-ui-timepicker-addon.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/jquery.multiselect.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/jquery.multiselect.filter.css" />
<link class="include" rel="stylesheet" type="text/css" href="<?= $theme_path; ?>/css/jquery.jqplot.min.css" />
<link type="text/css" rel="stylesheet" href="<?= $theme_path; ?>/css/shCoreDefault.min.css" />
<link type="text/css" rel="stylesheet" href="<?= $theme_path; ?>/css/shThemejqPlot.min.css" />
<link href="<?= $theme_path; ?>/css/uni-form.css" media="screen" rel="stylesheet"/>
<link href="<?= $theme_path; ?>/css/default.uni-form.css" title="Default Style" media="screen" rel="stylesheet"/>
    
    <!--[if lte ie 7]>
      <style type="text/css" media="screen">
        /* Move these to your IE6/7 specific stylesheet if possible */
        .uniForm, .uniForm fieldset, .uniForm .ctrlHolder, .uniForm .formHint, .uniForm .buttonHolder, .uniForm .ctrlHolder ul{ zoom:1; }
      </style>
    <![endif]-->
	
<script type="text/javascript" src="<?= $theme_path; ?>/js/all_library.js"></script>

<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?= $theme_path; ?>/js/excanvas.js"></script><![endif]-->
<script type="text/javascript" src="<?= $theme_path; ?>/js/user_js.js"></script>

</head>
<body>
<div id="main_container">
	<div id="header-container">
		<div class="header">
			<div class="logo"><a href="<?= $this->config->item('base_url') ?>"><img src="<?= $theme_path; ?>/images/logo.png"/> </a></div>
			<div class="right_header"><a href="<?= $this->config->item('base_url') ?>users/profile" class="">Welcome <?= $this->user_auth->get_username(); ?></a> | <a href="<?= $this->config->item('base_url') ?>users/logout" class="logout">Logout</a></div>
			<div class="jclock"></div>
			<?php 
				$enter_class = ""; 
				$cur_class = $this->router->class;
				$cur_method = $this->router->method;
			?>
			<div class="menu">
				 <ul>
                 	<?php 
					if($cur_class == "tasks" && $cur_method == "index") { $enter_class = "current"; }
					else {$enter_class = "";}  
					?>
					<li><a class="<?= $enter_class; ?>" href="<?= $this->config->item('base_url') ?>">Home</a></li>
					<?php 
					if($cur_class == "tasks" && $cur_method == "view") { $enter_class = "current"; }
					else {$enter_class = "";}  
					?>            
					<li><a class="<?= $enter_class; ?>" href="<?= $this->config->item('base_url') ?>tasks/view">View Tasks</a></li>
                    <?php 
					if($cur_class == "tasks" && $cur_method == "add") { $enter_class = "current"; }
					else {$enter_class = "";}  
					?>
					<li><a class="<?= $enter_class; ?>" href="<?= $this->config->item('base_url') ?>tasks/add">Add Task</a></li>
					<?php if ($this->user_auth->is_admin()): ?>
                    <?php 
					if($cur_class == "categories" && $cur_method == "index") { $enter_class = "current"; }
					else {$enter_class = "";}  
					?>
					<li><a class="<?= $enter_class; ?>" href="<?= $this->config->item('base_url') ?>tasks/categories">Categories</a></li>
                    <?php 
					if($cur_class == "users" || $cur_class == "roles") { $enter_class = "current"; }
					else {$enter_class = "";}  
					?>
					<li><a class="<?= $enter_class; ?>" href="<?= $this->config->item('base_url') ?>users">Users<!--[if IE 7]><!--></a><!--<![endif]-->
						<!--[if lte IE 6]><table><tr><td><![endif]-->
							<ul>
                            	<?php 
								if($cur_class == "users" && $cur_method == "index") { $enter_class = "current"; }
								else {$enter_class = "";}  
								?>
								<li><a class="<?= $enter_class; ?>" href="<?= $this->config->item('base_url') ?>users" title="">Manage Users</a></li>
                                <?php 
								if($cur_class == "roles" && $cur_method == "index") { $enter_class = "current"; }
								else {$enter_class = "";}  
								?>
								<li><a class="<?= $enter_class; ?>" href="<?= $this->config->item('base_url') ?>users/roles" title="">Manage Roles</a></li>
							</ul>
						<!--[if lte IE 6]></td></tr></table></a><![endif]--></a>
					
					
					</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
    </div>
    <div class="main_content">
    	
         <br><br>
    <div class="center_content">  
       <!-- <a class="bt_green addbtn" href="#"><span class="bt_green_lft"></span><strong>Add new Task</strong><span class="bt_green_r"></span></a>-->
       <!-- <div class="left_content">
            <div class="sidebar_search">
                <form>
                    <input type="text" name="" class="search_input" value="search keyword" onclick="this.value=''" />
                    <input type="image" class="search_submit" src="<?= $theme_path; ?>/images/search.png" />
                </form>            
            </div>
    
            <div class="sidebarmenu">
                <a class="menuitem_green" href="">Green button</a>
                <a class="menuitem" href="">Blue button</a>
            </div>
         </div>  -->
        
        <?php echo $content; ?>
                           
  </div>   <!--end of center content -->               
                    
                    
    
    
    <div class="clear"></div>
    </div> <!--end of main content-->
	
    
    <div class="footer">
    
    	<div class="left_footer">&copy; 2012-2013 <?= $this->config->item('site_title'); ?> | <a href="http://www.tecnovasolutions.com"><?= $this->config->item('site_powered'); ?></a> </div>
    	<div class="right_footer"><a href="http://www.tecnovasolutions.com">www.tecnovasolutions.com</a></div>
    
    </div>

</div>		
</body>
</html>
