<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add History
 *
 * Authentication library
 *
 * @package		Tasker
 * @author		dckap 
 * @version		1.0
 */
 
class Add_history
{
	private $error = array();

	function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->library('session');
		$this->ci->load->database();
		$this->ci->load->model("tasks/history_model");
		
	}


	function log_history($task_id, $history) {
	

	
		$data = array("history" => $history, "logged_by" => $this->ci->session->userdata('user_id_for_history'), "task_id" => $task_id);
	
		$this->ci->history_model->insert_history($data);
	
	}

}

/* End of file userauth.php */
/* Location: ./application/libraries/userauth.php */