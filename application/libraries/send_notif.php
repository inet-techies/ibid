<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Send_notif {

	function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->library('session');
		$this->ci->load->library('email');
		$this->ci->load->model("tasks/tasks_model");
		$this->ci->load->model("tasks/users_model");
		$config=array(
				'protocol'=>'mail',
				'charset'=>'utf-8',
				'wordwrap'=> FALSE,
				'mailtype' => 'html'
			);
			
			$this->ci->email->initialize($config);
	}

	public function send_mail_notif($type,$data) {
	
		$task_data = $this->ci->tasks_model->get_task_by_id($data["id"]);
		$user_data_ass_to = $this->ci->users_model->get_user_by_id($task_data[0]["assigned_to"]);
		$user_data_ass_by = $this->ci->users_model->get_user_by_id($task_data[0]["assigned_by"]);

		$this->ci->email->from($this->ci->config->item('default_email'),$this->ci->config->item('default_email_name'));
		
		
		if($type == "add_task") {
			$this->ci->email->to($user_data_ass_to[0]["email"]); 
			$this->ci->email->subject("Task Manager- A New task is assigned to you");
			$this->ci->email->message("Hi ".$user_data_ass_to[0]["username"].",<br/><br/>A new task <b>".$task_data[0]["task_name"]."</b> is assigned to you<br/><br/><a href = '".$this->ci->config->item('base_url')."'>Click here</a> to visit Task Manager for more details<br/><br/>Note :'This is a System generated e-mail and Don't Reply in the same. To Change status or for more details login to Task Manager using your credentials'.");	
			$this->ci->email->send();
			
			$this->ci->email->to($user_data_ass_by[0]["email"]); 
			$this->ci->email->subject("Task Manager- You have assigned a new Task to ".$user_data_ass_to[0]["username"]);
			$this->ci->email->message("Hi ".$user_data_ass_by[0]["username"].",<br/><br/>A new task <b>".$task_data[0]["task_name"]."</b> has been assigned by <b>you</b> to <b>".$user_data_ass_to[0]["username"]."</b><br/><br/><a href = '".$this->ci->config->item('base_url')."'>Click here</a> to visit Task Manager for more details<br/><br/>Note :'This is a System generated e-mail and Don't Reply in the same. To Change status or for more details login to Task Manager using your credentials'.");	
			
			$this->ci->email->send();
			
			
		} elseif($type == "task_comment") {
		
			$this->ci->email->to($user_data_ass_to[0]["email"]); 
			$this->ci->email->subject("Task Manager- ".$task_data[0]["task_name"]." received a new comment");
			$this->ci->email->message("Hi ".$user_data_ass_to[0]["username"].",<br/><br/>The task <b>".$task_data[0]["task_name"]."</b> assigned to <b>you</b> by <b>".$user_data_ass_by[0]["username"]."</b> received a new comment:<br/><b>".$data["comment"]."</b><br/><br/><a href = '".$this->ci->config->item('base_url')."'>Click here</a> to visit Task Manager for more details<br/><br/>Note :'This is a System generated e-mail and Don't Reply in the same. To Change status or for more details login to Task Manager using your credentials'.");	
			$this->ci->email->send();
			
			$this->ci->email->to($user_data_ass_by[0]["email"]); 
			$this->ci->email->subject("Task Manager- ".$task_data[0]["task_name"]." received a new comment");
			$this->ci->email->message("Hi ".$user_data_ass_by[0]["username"].",<br/><br/>The task <b>".$task_data[0]["task_name"]."</b> assigned to <b>".$user_data_ass_to[0]["username"]."</b> by <b>you</b> received a new comment:<br/><b>".$data["comment"]."</b><br/><br/><a href = '".$this->ci->config->item('base_url')."'>Click here</a> to visit Task Manager for more details<br/><br/>Note :'This is a System generated e-mail and Don't Reply in the same. To Change status or for more details login to Task Manager using your credentials'.");		
			$this->ci->email->send();
		
		} elseif($type == "change_progress") {
		
			$this->ci->email->to($user_data_ass_to[0]["email"]); 
			$this->ci->email->subject("Task Manager- ".$task_data[0]["task_name"]." 's status/progress changed");
			$this->ci->email->message("Hi ".$user_data_ass_to[0]["username"].",<br/><br/>The task <b>".$task_data[0]["task_name"]."</b> assigned to <b>you</b> by <b>".$user_data_ass_by[0]["username"]."</b> has a status/progress change.<br/><b>Status &rArr; ".$task_data[0]["status_name"]."</b><br/><b>Progress &rArr; ".$task_data[0]["progress"]."%</b><br/><br/><a href = '".$this->ci->config->item('base_url')."'>Click here</a> to visit Task Manager for more details<br/><br/>Note :'This is a System generated e-mail and Don't Reply in the same. To Change status or for more details login to Task Manager using your credentials'.");	
			$this->ci->email->send();
			
			$this->ci->email->to($user_data_ass_by[0]["email"]); 
			$this->ci->email->subject("Task Manager- ".$task_data[0]["task_name"]." 's status/progress changed");
			$this->ci->email->message("Hi ".$user_data_ass_by[0]["username"].",<br/><br/>The task <b>".$task_data[0]["task_name"]."</b> assigned to <b>".$user_data_ass_to[0]["username"]."</b> by <b>you</b> has a status/progress change.<br/><b>Status &rArr; ".$task_data[0]["status_name"]."</b><br/><b>Progress &rArr; ".$task_data[0]["progress"]."%</b><br/><br/><a href = '".$this->ci->config->item('base_url')."'>Click here</a> to visit Task Manager for more details<br/><br/Note :'This is a System generated e-mail and Don't Reply in the same. To Change status or for more details login to Task Manager using your credentials'.");	
			$this->ci->email->send();
		
		}
		 
		
		

		
//		echo $this->ci->email->print_debugger();

	}


	public function hive_notif($message) {

		$user_id = $this->ci->user_auth->get_user_id();
		$user_data = $this->ci->users_model->get_user_by_id($user_id);
		$other_users = $this->ci->users_model->get_all_users_by_category("CAUSA USA");
		$flag = 0;
		$this->ci->email->from($this->ci->config->item('default_email'),$this->ci->config->item('default_email_name'));
		foreach($other_users as $email_vals) {

			$this->ci->email->to($email_vals["email"]);
			$this->ci->email->subject("Hive- ".$message["subject"]);
			$this->ci->email->message("Hi ".$email_vals["username"].",<br/><br/>".$message["content"]);	
			$this->ci->email->send();
			
			if($user_data[0]["email"] == $email_vals["email"]) {

				$flag = 1;
			}
		}
		
		if($flag != 1) {
		
			$this->ci->email->to($user_data[0]["email"]);
			$this->ci->email->subject("Hive- ".$message["subject"]);
			$this->ci->email->message("Hi ".$user_data[0]["username"].",<br/><br/>".$message["content"]);	
			$this->ci->email->send();
		}
	}
}


/* End of file Send_notif.php */

