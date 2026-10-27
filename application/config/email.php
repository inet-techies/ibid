<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*$config['protocol'] = 'smtp';
$config['smtp_host']= 'mail.inetcsc.com';
$config['smtp_port']= '587';
$config['smtp_user']= 'info@inetcsc.com';
$config['smtp_pass']= 'Info$321';*/
$config['smtp_crypto'] = 'tls'; 
$config['protocol'] = 'smtp';
$config['smtp_host']= 'smtp.office365.com';
$config['smtp_port']= '587';
$config['smtp_timeout'] = '60';
$config['smtp_user']= 'inet@inetcsc.com';
$config['smtp_pass']= 'Empty@123';

$config['mailpath'] = '/usr/sbin/sendmail';
$config['charset'] = 'iso-8859-1';
$config['wordwrap'] = TRUE;
$config['mailtype'] = 'html';
$config['newline']	= '\r\n';