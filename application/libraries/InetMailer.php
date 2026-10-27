<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Customer file for sending the email
 *
 * Generate PDF's in your CodeIgniter applications.
 *
 * @package			Custome
 * @subpackage		Libraries
 * @category		Libraries
 * @author			Karunakaran
 * @license			-----------
 * @link			-----------
 */

//require_once(dirname(__FILE__) . '/dompdf/dompdf_config.inc.php');


require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class InetMailer
{

	public function __construct()
    {
        log_message('Debug', 'PHPMailer class is loaded.');
    }

    public function load()
    {
		$mail = new PHPMailer(true);

		//Server settings
		$mail->SMTPDebug = 0;                                 // Enable verbose debug output 0 1 2
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.office365.com';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'inet@inetcsc.com';  //karunakaran@inetcsc.com                // SMTP username
		$mail->Password = 'Acb@786@123'; //Empty@123 /***before2021-05-20****/   //Wun59371                        // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to
		return $mail;


    }


}
