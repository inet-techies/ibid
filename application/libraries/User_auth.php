<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
define('STATUS_ACTIVATED', '1');
define('STATUS_NOT_ACTIVATED', '0');
/**
 * Userauth
 *
 * Authentication library
 *
 * @package		Userauth
 * @author		dckap 
 * @version		1.0
 */

class User_auth
{
	private $error = array();
	function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->library('session');
		$this->ci->load->database();
	}
	
	function get_user_id()
	{
		$user_det = $this->ci->session->userdata('logged_in');
		 return $user_det['user_id'];
	}

	/**
	 * Get username
	 *
	 * @return	string
	 */
	function get_username()
	{
		$user_det = $this->ci->session->userdata('logged_in');
		
		 return $user_det['name'];
		
	}
	/**
	 * Get username
	 *
	 * @return	string
	 */
	function get_user_email()
	{
		$user_det = $this->ci->session->userdata('logged_in');
		
		 return $user_det['email'];
		
	}
	/** user permission
	*/
	function get_user_permission()
	{
		$user_permission = $this->ci->session->userdata('admin_permission');
		
		 return $user_permission;
	}
	function get_curdate()
	{
		$timezone = new DateTimeZone("Asia/Kolkata" );
		$date = new DateTime();
		$date->setTimezone($timezone );
		$cur_date_time = $date->format( 'H:i:s A  /  D, M jS, Y' );
		$cur_date = $date->format( 'd-m-Y' );
		 return $cur_date;
	}
	function get_curdate_time()
	{
		$timezone = new DateTimeZone("Asia/Kolkata" );
		$date = new DateTime();
		$date->setTimezone($timezone );
		
		$cur_date_time = $date->format( 'Y-d-m h:i:s');
		//$cur_date = $date->format( 'd-m-Y' );
		 return $cur_date_time;
	}

	
	
}

/* End of file userauth.php */
/* Location: ./application/libraries/userauth.php */