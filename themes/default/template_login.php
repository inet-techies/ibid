<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?= $this->config->item('site_title'); ?> | <?= $this->config->item('site_powered'); ?></title>
		<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');  ?>
		<link rel="shortcut icon" href="<?= $theme_path; ?>/images/favicon.png" />
		<link rel="stylesheet" type="text/css" href="<?= $theme_path; ?>/css/style.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?= $theme_path; ?>/css/bootstrap-responsive.css" />
        <script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-1.8.2.js"></script>
        <script type="text/javascript" src="<?= $theme_path; ?>/js/common_validation.js"></script>
		<script type="text/javascript" src="<?= $theme_path; ?>/js/bootstrap.js"></script>
        <style type="text/css">
			.footer_login {
				text-align:center;
			}
		</style>	
	</head>
	<body>
		<div id="main_container">
			<div class="login_form">
				<?php echo $content; ?>         
			</div>  
			<div class="footer_login">
				<div class="left_footer_login"><?= $this->config->item('site_title'); ?> | <a href="<?= $this->config->item('powered_url'); ?>"><?= $this->config->item('site_powered'); ?></a></div>
				<div class="right_footer_login"></div>
			</div>
		</div>		
	</body>
</html>
