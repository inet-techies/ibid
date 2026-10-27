<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Session_messages {

	function __construct()
	{
	
		$this->ci =& get_instance();
		
		$this->ci->load->library('session');
		
		date_default_timezone_set("Asia/Calcutta");
		
	
	}
	function add_message($type,$message)
	{
		$message_count = 0;
		
		if($this->ci->session->userdata("message"))
		{
			$message_count = count($this->ci->session->userdata("message"));
			
			$messages = $this->ci->session->userdata("message");
			
			$messages[$message_count] =  array("type" => $type , "message" => $message,"created_time" => date('Y-m-d H:i:s')) ;
			
			$this->ci->session->set_userdata("message", $messages);
			
		}
		else
		{
			$this->ci->session->set_userdata("message", 
			
				array($message_count  =>	array("type" => $type , "message" => $message,"created_time" => date('Y-m-d H:i:s')))
			);
		}
		
	}
	function view_all_messages()
	{
	
		$data = $this->ci->session->userdata("message");
		
		return $data;
	
	}
	function delete_session_message($id)
	{
	
		if($this->ci->session->userdata("message"))
		{
		//	echo "enter";
			$messages = $this->ci->session->userdata("message");
			
			if(isset($messages[$id]) && !empty($messages[$id]))
			
				$messages[$id] =  array("type" => "" , "message" => "" ,"created_time" => "") ;
			
			//print_r($messages[$id]);
			
			$this->ci->session->set_userdata("message", $messages);
			
			$this->is_empty_session();
		}
	}
	function is_empty_session()
	{
		
			$messages = $this->ci->session->userdata("message");
			
			$clear_all = 0;
			
			if(isset($messages) && !empty($messages))
			{
				for($i=0; $i <count($messages);$i++)
				{
					//echo strlen($messages[$i]["message"]);
					
					if(strlen($messages[$i]["message"])!=0)
					{
						
							$clear_all = 1;
						
					}
				}
			
			}
			if($clear_all == 0)
			{
				$this->ci->session->unset_userdata("message");
			
			}
			
	
	}
}
	
	
	



?>