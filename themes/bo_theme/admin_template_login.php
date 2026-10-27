<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta http-equiv="Cache-control" content="public"/>
        <meta http-equiv="expires" content="Tue, 29 Sep 2015 1:00:00 GMT" />
        <?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>
		<link href="<?=$theme_path?>/images/favicon.png" rel="shortcut icon">
        <title> BID Management | I-NET 2026</title>     	
        <link type="text/css" href="<?= $theme_path; ?>/css/style.default_login.css" rel="stylesheet">
		<?php /*?><link href="<?=$theme_path?>/css/morris.css" rel="stylesheet">
        <link href="<?=$theme_path?>/css/select2.css" rel="stylesheet" />
        <link href="<?=$theme_path?>/css/jquery-ui.css" rel="stylesheet" /><?php */?>
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="js/html5shiv.js"></script>
        <script src="js/respond.min.js"></script>
        <![endif]-->
         
    </head>

    <body class="signin"><!--onLoad="randomString()"-->
        
        
        <section>
            <div class="contentpanel">
            
            <?php echo $content;?>
            </div>
        </section>


        <script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-1.11.1.min.js"></script>        
      <?php /*?>  <script src="<?= $theme_path; ?>/js/jquery-migrate-1.2.1.min.js"></script>
        <script src="<?= $theme_path; ?>/js/bootstrap.min.js"></script>
        <script src="<?= $theme_path; ?>/js/modernizr.min.js"></script>
        <script src="<?= $theme_path; ?>/js/pace.min.js"></script>
        <script src="<?= $theme_path; ?>/js/retina.min.js"></script>
        <script src="<?= $theme_path; ?>/js/jquery.cookies.js"></script>
        <script src="<?= $theme_path; ?>/js/custom.js"></script><?php */?>
        
        <!-- Datepicker -->
       <?php /*?> <script src="<?= $theme_path; ?>/js/jquery-ui.js"></script>
		<script>
			$(function() {
			$( ".date" ).datepicker();
			});
        </script><?php */?>

    </body>
</html>
