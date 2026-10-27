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
        <title> BID Management </title>		
        <!-- <link type="text/css" href="<?= $theme_path; ?>/css/style.default_login.css" rel="stylesheet"> -->
        <script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-1.11.1.min.js"></script>
        <link href="<?=$theme_path?>/css/jquery-ui.css" rel="stylesheet" />
        <script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-ui.js"></script>

  
    </head>

    <body class="signin" style="background-image:<?=$theme_path?>/landing_page/img/bg.jpg" >
        <section>
            <div class="contentpanel">
            	<?php echo $content; ?>
            </div>
        </section>

        
        <script>
		$(function()
		{
			$(".date").datepicker
			({
				changeMonth: true,
				changeYear: true,
				dateFormat: "dd-mm-yy",
				yearRange: "c-90:c+5"
			});
		});
		</script>
    </body>
</html>
