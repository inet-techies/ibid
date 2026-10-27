<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bid extends MX_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->library('email');
    	$this->load->database();
		$this->load->library('form_validation');
		$this->load->library('pagination');
		// $this->load->library('pdf');
		$check_session = $this->session->userdata('logged_in');
		$this->load->helper('cookie');
		$this->load->helper('string');
		$this->load->library('encrypt');
		$this->load->library('user_agent');
		date_default_timezone_set("Asia/Kolkata");
	 	$this->load->model('bid/bid_model');

	 	$roleId = $this->session->userdata('RoleId'); // make sure role_id exists in session
	 	//echo "<pre>"; print_r($check_session); exit;
		$menus = $this->bid_model->getMenusByRole($check_session['RoleId']);
		// $currentUrl = uri_string();
		// 	//echo "<pre>currentUrl"; print_r($currentUrl); exit;
		// $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
		// // echo "<pre>currentUrl"; print_r($data['breadcrumb']); exit;
		$menuTree = [];

			foreach ($menus as $menu) {
			    if ($menu['ParentId'] === NULL) {
			        $menuTree[$menu['Bmid']] = $menu;
			        $menuTree[$menu['Bmid']]['children'] = [];
			    }
			}

			foreach ($menus as $menu) {
			    if ($menu['ParentId'] !== NULL && isset($menuTree[$menu['ParentId']])) {
			        $menuTree[$menu['ParentId']]['children'][] = $menu;
			    }
			}
		$this->load->vars('menuTree', $menuTree);


	} //Construct


	public function index()
	{
	 	$this->load->model('bid/bid_model');
		
		$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
		$this->template->write_view('content', 'bid/index');
		$this->template->render();
	}


	public function CheckLogin()
	{

		   $inps = $this->input->post();
		   $username = $inps['EmailId'];
		   $yourpassword = $inps['EmailPass'];
		     //echo "<pre>HELLO"; print_r($yourpassword);  exit;
		   // echo "<pre>HELLO"; print_r(md5($yourpassword)); 
		  if($username!="")
			{ 

			$this->db->select('bu.*');
			$this->db->where('bu.Email',$username);
			$this->db->where('bu.Passwd',md5($yourpassword));
			$this->db->where('bu.Status',1); 

			$Buquery = $this->db->get('BidUsers as bu')->result_array();
			
			// echo"<pre>";print_r($userquery);
			 	 // echo $this->db->last_query(); exit;
				
				if(!empty($Buquery)){
 

					$sess_array = array('Bid'=>$Buquery[0]['Buid'],'BName'=>$Buquery[0]['Name'],'BEmail'=>$Buquery[0]['Email'],'RoleId'=>$Buquery[0]['Brid']);
                    //echo "<pre>HELLO"; print_r($sess_array);
                    $this->session->set_userdata('logged_in',$sess_array);
                    $Bid_Data = $this->session->userdata('logged_in');
                    //echo"<pre>sess";print_r($employee_det);
                    $data['Logdescription'] = "User ".$Bid_Data['Bid']." Logged in Successfully on ".date("Y M d H i s");
                   $ip_address = $this->input->ip_address();
                    $data['Buid'] = $Bid_Data['Bid'];
                    $data['UserRole'] = $Bid_Data['RoleId'];
                    $data['Ipaddress'] = $ip_address;
                    // $data['location'] = $Bid_Data['work_loc'];
                    $curr_time=time();
                    $login_time = date("Y-m-d H:i:s",$curr_time);
                    //echo"<pre> Login Time";print_r($login_time);
                    $data['LogInTime'] = $login_time;
                    $data['LogOutTime'] ='';
                     // exit;
                     $data['Status'] ='1';
                     $this->db->insert('BidLogin_Log',$data);
                   
                     redirect($this->config->item('base_url').'bid/dashboard');
 
                 } else {

                 	$this->session->set_flashdata('err', 'Invalid Login Credentials..!');
	   			    redirect($this->config->item('base_url').'bid/index');


                 }
				
			}
		  	$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
			$this->template->write_view('content', 'bid/index');
			$this->template->render();
		  
		   }
	
public function dashboard(){

		$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{	
			//Get current URL (bid/CreateBids)
        $currentUrl = strtolower(uri_string());

        //Fetch breadcrumb data
        $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
				
				  // Get grouped counts
					    $this->db->select('BidStatus, COUNT(*) as total');
					    $this->db->group_by('BidStatus');
					    $counts = $this->db->get('BidCreationDetails')->result_array();

					    $countArray = [];
					    foreach($counts as $row){
					        $countArray[$row['BidStatus']] = $row['total'];
					    }

					    // Get all statuses
					    $statuses = $this->db->get('BidStatusMaster')
					                         ->result_array();

					    $statusMap = [];
					    foreach($statuses as $status){
					        $statusMap[$status['StatusName']] = [
					            'DisplayName' => $status['DisplayName'],
					            'BadgeClass' => $status['BadgeClass'],
					            'Sid' => $status['Sid'],
					            'Count' => isset($countArray[$status['Sid']]) ? $countArray[$status['Sid']] : 0,
					            'IconClass'   => $status['IconClass']
					        ];
					    }

					    // $data['statusName'] = $statuses['DisplayName'];
					    $data['statusCounts'] = $statusMap;
					    $data['totalBids'] = array_sum($countArray);

					     // Bootstrap color mapping
						    $data['colorMap'] = [
						        'bg-primary'   => '#0d6efd',
						        'bg-success'   => '#198754',
						        'bg-danger'    => '#dc3545',
						        'bg-warning'   => '#ffc107',
						        'bg-info'      => '#0dcaf0',
						        'bg-dark'      => '#212529',
						        'bg-secondary' => '#6c757d'
						    ];

				// Get Last 10 Bids (latest first)
				$lastBids = $this->db
				    ->select('b.Bcid, b.BidTitle, b.BidType, b.BidDepartment, b.PublishedDate,b.OpeningDate,b.SubmissionDate,b.CreationTs,s.DisplayName as StatusName, s.BadgeClass')
				    ->from('BidCreationDetails b')
				    ->join('BidStatusMaster s', 's.Sid = b.BidStatus', 'left')
				    ->order_by('b.CreationTs', 'DESC')
				    ->limit(10)
				    ->get()
				    ->result_array();

				$data['lastBids'] = $lastBids;
		    
				$this->template->write_view('content', 'bid/masterdashboard', $data);
				$this->template->render();

		} else
		{
		$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}

public function CreateBids(){

		$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
				
		//Get current URL (bid/CreateBids)
        $currentUrl = strtolower(uri_string());

        //Fetch breadcrumb data
        $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

				$this->template->write_view('content', 'bid/CreateBids', $data);
				$this->template->render();

		} else
		{
		$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}

public function AddUser(){

		$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
				
		//Get current URL (bid/AddUser)
        $currentUrl = strtolower(uri_string());

        //Fetch breadcrumb data
        $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

				$this->template->write_view('content', 'bid/AddUser', $data);
				$this->template->render();

		} else
		{
		$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}
public function saveUsers(){

		$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
				
		  	$inps = $this->input->post();
		  
		    if($this->input->post())
	    	{

	    		// echo"<pre>"; print_r($inps);exit;

	    			$this->db->select('bu.*');
					$this->db->where('bu.Mobile',$inps['phoneInput']); 
					$this->db->where('bu.Email',$inps['mailInput']); 
					$BidUsersDetails = $this->db->get('BidUsers as bu')->num_rows();
					//echo"<pre>"; print_r($emp_details); exit;	
				   	if($BidUsersDetails>0){

				   			$this->session->set_flashdata('err', 'Employee Already Exists..!');
				   			redirect($this->config->item('base_url').'bid/AddUser'); 
				   			
				   	} else {

				   			$BidUsersdata=array('Name'=>$inps['fullnameInput'],'Mobile'=>$inps['phoneInput'],'Email' =>$inps['mailInput'],'Designation' =>$inps['designationInput'],'Brid' =>$inps['usertypeInput'],'Status' =>0);
					   	   		//$this->db->last_query();

							   	 $sql_query=$this->db->insert('BidUsers',$BidUsersdata);
							   	 $id = $this->db->insert_id();
							   	  // echo"<pre>id";print_r($id);exit;
							   	if($sql_query>0){

							   	 $this->db->select('*');
				 		    	 $this->db->where('bu.Buid', $id); 
								 $data['RegMail']=$this->db->get('BidUsers as bu')->result_array();
								 // echo $this->db->last_query();
								 // echo "<pre>"; print_r( $data['email']); exit;
								        try {
								            // otp to mail
								          		//$to = $inps['email'];
												$to = $data['RegMail'][0]['Email'];
						    	   
									            $subject="Employee Registration Successful - ".$inps['fullnameInput']." - Bid Management.";
									             
									            require(APPPATH.'libraries/InetMailer.php');
									            $objs = new InetMailer();
									            $mail = $objs->load(); 
									            $mail->setFrom('info@inetcsc.com', 'I-NET CSC');
									            $mail->addAddress(trim($to)); // Add a recipient FCPATH.'sign_board/' 
									 				 
									            $mail->addCC('prasannakumari@inetcsc.com');

									            $mail->isHTML(true); // Set email format to HTML
									            $mail->Subject = $subject; 
									            $mail->AddEmbeddedImage('./images/inet_logo.png', 'I-NET');
									            $mail->Body = $this->load->view('bid/EmployeeMail',$data,TRUE);
									            $mail->send();            
							           
								           				
								          } catch (\Exception $e) {
								            //Simply leave, if nay email errors
								          }
							   		 $this->session->set_flashdata('true', 'Employee Added Successfully...!');
							   		 redirect($this->config->item('base_url').'bid/AddUser');
							   		 //echo"success";exit;
							   		}
							   		else{
							   			
							   			$this->session->set_flashdata('err','something went wrong.Error!!');
							   			redirect($this->config->item('base_url').'bid/AddUser');
							   			//echo"Failed";exit;
							   		}

				   	}


			} else {


	   		 $this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/admin_template_login.php');
			$this->template->write_view('content', 'bid/AddUser');
			$this->template->render();

		   	}


		} else
		{

			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}


public function SendOtpVerify($Buid)
{
    // Decode BUID
    $data['Buid'] = base64_decode(base64_decode(base64_decode(base64_decode(urldecode($Buid)))));

    // Fetch user
    $this->db->where('bu.Buid', $data['Buid']);
    $data['RegMail'] = $this->db->get('BidUsers as bu')->result_array();

    if (empty($data['RegMail'])) {
      $this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
      redirect($this->config->item('base_url')."bid/index");
    }

    /* ================= OTP GENERATION ================= */

    // Secure 6-digit OTP
    $otp = random_int(100000, 999999);

    // Save OTP securely
    $updateData = [
        'OTPHash'     => password_hash($otp, PASSWORD_DEFAULT),
        'OTPExpiry'   => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        'OTPAttempts' => 0
    ];

    $this->db->where('Buid', $data['Buid']);
    $this->db->update('BidUsers', $updateData);

    // Pass OTP data to email template
    $data['otp']  = $otp;
    $data['name'] = $data['RegMail'][0]['Name'];

    /* ================= SEND OTP EMAIL ================= */

    try {

        $to = $data['RegMail'][0]['Email'];
        $subject = "Employee Verification OTP - Bid Management";

        require(APPPATH.'libraries/InetMailer.php');
        $objs = new InetMailer();
        $mail = $objs->load();

        $mail->setFrom('info@inetcsc.com', 'I-NET CSC');
        $mail->addAddress(trim($to));
        $mail->addCC('prasannakumari@inetcsc.com');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->AddEmbeddedImage('./images/inet_logo.png', 'I-NET');

        // OTP Email Template
        $mail->Body = $this->load->view('bid/EmployeeOTPMail', $data, TRUE);

        $mail->send();

    } catch (\Exception $e) {
        // Log if needed, but do not break flow
    }

    /* ================= LOAD OTP VERIFY PAGE ================= */

    $this->template->set_master_template(
        '../../themes/'.$this->config->item("active_template").'/landing_template_login.php'
    ); 
    $this->template->write_view('content', 'bid/Verify', $data);
    $this->template->render();
}

public function VerifyOtpAjax()
{
    $enteredOtp = $this->input->post('otp');
    $Buid = $this->input->post('Buid');

    $user = $this->db->get_where('BidUsers', ['Buid' => $Buid])->row();

    if (!$user) {
        echo "Invalid user";
        return;
    }

    // Check expiry
    if (strtotime($user->OTPExpiry) < time()) {
        echo "OTP expired";
        return;
    }

    // Verify OTP
    if (!password_verify($enteredOtp, $user->OTPHash)) {

        // Increment attempts
        $this->db->set('OTPAttempts', 'OTPAttempts+1', FALSE)
                 ->where('Buid', $Buid)
                 ->update('BidUsers');

        echo "Invalid OTP";
        return;
    }

    // OTP success → clear OTP
    $this->db->where('Buid', $Buid)->update('BidUsers', [
        'OTPHash' => NULL,
        'OTPExpiry' => NULL,
        'OTPAttempts' => 0
    ]);

      echo json_encode([
        'status'   => true,
        'message'  => 'OTP verified successfully',
        'redirect' =>  base_url('bid/ChangePass/'.$Buid)
    ]);

}

public function ChangePass($Buid){

	$this->db->select('bu.Buid,bu.Name,bu.Mobile,bu.Email,bu.Status');
	$this->db->where('bu.Buid', $Buid);
    $data['RegMail'] = $this->db->get('BidUsers as bu')->result_array();
     //echo "<pre>"; print_r( $data['RegMail']); exit;
    if (empty($data['RegMail'])) {

      $this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
      redirect($this->config->item('base_url')."bid/index");

    } else {

    	$this->template->set_master_template(
        '../../themes/'.$this->config->item("active_template").'/landing_template_login.php'
	    ); 
	    $this->template->write_view('content', 'bid/ChangePass', $data);
	    $this->template->render();
    }
 

}

public function UpdatePasswordDetails($Bid){

	$Bid = base64_decode(base64_decode(base64_decode(base64_decode(urldecode($Bid)))));
    	//echo"<pre>"; print_r($emp_id);//exit;
		$ups = $this->input->post();
	 
		 
		$this->db->select('bu.Buid,bu.Name,bu.Mobile,bu.Email,bu.Status');
		$this->db->where('bu.Buid', $Bid);
	 	$UpdateId=$this->db->get('BidUsers as bu')->num_rows();
	 	// echo"<pre>NPassInput"; print_r($ups['NPassInput']); 
	 	// echo $this->db->last_query(); exit;
	 	if($UpdateId>0)
	 	{
          $data=array('Passwd' =>md5($ups['NPassInput']),'ActivateTEmail' =>1,'Status' =>1);
          $this->db->where('Buid',$Bid);
	 	  $UpdateQuery=$this->db->update('BidUsers',$data); 
	 		 if($UpdateQuery>0){
 
		   		 $this->session->set_flashdata('yes', 'Successfully Updated Your Password');
		   		 redirect($this->config->item('base_url').'bid/index');
		   		 //echo"success";exit;
		   		}
		   		else{
		   			
		   			$this->session->set_flashdata('err','Failed to Updated Password.Please try Again..!!');
		   			redirect($this->config->item('base_url').'bid/index');
		   			//echo"Failed";exit;
		   		}
	 	}
	 	else{
   			
   			$this->session->set_flashdata('err','No Details Found. Please Try Again..!!');
   			redirect($this->config->item('base_url').'bid/index');
   			//echo"Failed";exit;
   		}

}

public function UserUpdate(){

	$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
				
		//Get current URL (bid/AddUser)
        $currentUrl = strtolower(uri_string());

        //Fetch breadcrumb data
        $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
        		$data['UsersData'] = $this->bid_model->getUsersData();
				$this->template->write_view('content', 'bid/UserUpdate', $data);
				$this->template->render();

		} else
		{
		$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}

}
public function updateUserStatus(){

	$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
				 
 			 	$id     = $this->input->post('id');
			    $status = $this->input->post('status');


			    if (!$id) {
					echo json_encode([
			            'status'  => false,
			            'message' => 'Invalid User ID'
			        ]);
			        return;
			    }

			    $this->db->where('Buid', $id);
			    $update = $this->db->update('BidUsers', [
			        'Status' => $status
			    ]);

			    if ($update) {
			        echo json_encode([
			            'status'  => true,
			            'message' => 'User status updated successfully'
			        ]);
			    } else {
			        echo json_encode([
			            'status'  => false,
			            'message' => 'Something went wrong'
			        ]);
			    }
         		 

		} else
		{
		$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}

}

public function getUserData()
{
    $id = $this->input->post('id');

    $user = $this->db->where('Buid', $id)
                     ->get('BidUsers')
                     ->row_array();

    if($user){
        // echo "
        // <p><strong>Name:</strong> {$user['Name']}</p>
        // <p><strong>Mobile:</strong> {$user['Mobile']}</p>
        // <p><strong>Email:</strong> {$user['Email']}</p>
        // <p><strong>Designation:</strong> {$user['Designation']}</p>
        // <p><strong>Status:</strong> ".($user['Status']==1?'Active':'Inactive')."</p>
        // <p><strong>Created On:</strong> {$user['CreationTs']}</p>
        // ";
    echo '

<!-- Name -->
<div class="mb-4 pb-1 d-flex">
    <div class="d-flex w-50 align-items-center me-3">
         <div>
            <a href="javascript:void(0);" class="text-truncate-1-line">'.$user['Name'].'</a>
            <div class="fs-11 text-muted">Name</div>
        </div>
    </div>
     
</div>
<!-- Mobile -->
<div class="mb-4 pb-1 d-flex">
    <div class="d-flex w-50 align-items-center me-3">
         <div>
            <a href="javascript:void(0);" class="text-truncate-1-line">'.$user['Mobile'].'</a>
            <div class="fs-11 text-muted">Mobile No</div>
        </div>
    </div>
     
</div>
<!-- Email -->
<div class="mb-4 pb-1 d-flex">
    <div class="d-flex w-50 align-items-center me-3">
         <div>
            <a href="javascript:void(0);" class="text-truncate-1-line">'.$user['Email'].'</a>
            <div class="fs-11 text-muted">Email</div>
        </div>
    </div> 
</div>

<!-- Designation -->
<div class="mb-4 pb-1 d-flex">
    <div class="d-flex w-50 align-items-center me-3">
         <div>
            <a href="javascript:void(0);" class="text-truncate-1-line">'.$user['Designation'].'</a>
            <div class="fs-11 text-muted">Designation</div>
        </div>
    </div> 
</div>

<!-- Status -->
<div class="mb-4 pb-1 d-flex">
    <div class="d-flex w-50 align-items-center me-3">
         <div>
            <a href="javascript:void(0);" class="text-truncate-1-line">Account Status</a>
            <div class="fs-11 text-muted">Current State</div>
        </div>
    </div>
    <div class="d-flex flex-grow-1 align-items-center justify-content-end">'.
        ($user["Status"] == 1 
            ? '<span class="badge bg-success">Active</span>' 
            : '<span class="badge bg-danger">Inactive</span>')
    .'
    </div>
</div>

<!-- Created On -->
<div class="mb-0 d-flex">
    <div class="d-flex w-50 align-items-center me-3">
         <div>
            <a href="javascript:void(0);" class="text-truncate-1-line">'.date("d M Y", strtotime($user['CreationTs'])).'</a>
            <div class="fs-11 text-muted">Created On</div>
        </div>
    </div>
    <div class="d-flex flex-grow-1 align-items-center justify-content-end">
        <span class="text-muted">'.date("h:i A", strtotime($user['CreationTs'])).'</span>
    </div>
</div>

';

    }
}
// public function PQEvaluation(){

// 		$Bid_Session=$this->session->userdata('logged_in');  
 		
//  		if(isset($Bid_Session) && !empty($Bid_Session))
// 		{
				
// 		//Get current URL (bid/CreateBids)
//         $currentUrl = strtolower(uri_string());

//         //Fetch breadcrumb data
//         $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

// 				$this->template->write_view('content', 'bid/PQEvaluation', $data);
// 				$this->template->render();

// 		} else
// 		{
// 			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
// 			redirect($this->config->item('base_url')."bid/index");
// 		}
// }

public function AddBidData(){

		$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			
		$ip = $_SERVER['REMOTE_ADDR'];
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
 		//Get current URL (bid/CreateBids)
        $currentUrl = strtolower(uri_string());

        //Fetch breadcrumb data
        $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
 		
 		$inputs = $this->input->post();;
 		// echo "<ptr>inps"; print_r($inputs); exit;
 		 if(!$inputs){
 
		   		 $this->session->set_flashdata('err', 'No Inputs Received');
		   		 redirect($this->config->item('base_url').'bid/CreateBids');
		   		 //echo"success";exit;
		   		}
		   		else{
		   			
		   			 $Biddata = [
			            'BidType'        => $inputs['bidTypeInput'],
			            'BidTitle'       => $inputs['bidTitleInput'],
			            'BidDepartment'  => $inputs['bidDepartmentInput'],
			            'EstimatedValue' => $inputs['bidEstimatedInput'],
			            'EmdValue'       => $inputs['bidEmdInput'],
			            'PublishedDate'  => date('Y-m-d', strtotime($inputs['PublishedDate'])),
			            'SubmissionDate' => date('Y-m-d', strtotime($inputs['SubmissionDate'])),
			            'OpeningDate'    => date('Y-m-d', strtotime($inputs['OpeningDate'])),
			            'SummaryKeywords'=> $inputs['bidSummaryInput'],
			            'TenderPortalUrl'=> $inputs['bidTenderPortalInput'],
			            'TenderId'       => $inputs['bidTenderIDInput'],
			            'NASPath'        => $inputs['bidNASIDInput'],
			            'Remarks'        => $inputs['bidRemarksInput'],
			            'BidStatus'      => 1
			        ];
					$sql_query=$this->db->insert('BidCreationDetails',$Biddata);
					$id = $this->db->insert_id();
			         if($id>0){
 							
 						$status = $this->db->get_where('BidCreationDetails', ['Bcid' => $id])->row();

			         	  $tracking = [
					            'Bcid'             => $id,
					            'OldStatusId'      => $status->BidStatus,
					            'CurrentStatusId'  => $status->BidStatus,
					            'UpdatedBy'        => $Bid_Session['Bid'],
					            'Ipaddress'        => $ip,
					            'UserAgent'        => $userAgent,
					            'IsSystemGenerated'=> 0
					        ];

					        $this->db->insert('BidStatusTracking', $tracking);


					   		 $this->session->set_flashdata('true', 'Successfully Added Bid Details');
					   		 redirect($this->config->item('base_url').'bid/CreateBids');
				   		 //echo"success";exit;
				   		}
				   		else{
				   			
				   			$this->session->set_flashdata('err','Failed to Create Bid.Please try Again..!!');
				   			 redirect($this->config->item('base_url').'bid/CreateBids');
				   			//echo"Failed";exit;
				   		}

		   		}



		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}

public function BidDetails(){

	$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
	 
        //Fetch breadcrumb data
        $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

        	$data['BidData'] = $this->bid_model->getBidDetailsData();
        	$this->template->write_view('content', 'bid/BidDetails', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}
public function _remap($method, $params = [])
{
    // Allow real methods like index, add, edit etc.
    if (method_exists($this, $method)) {
        return call_user_func_array([$this, $method], $params);
    }

    // Otherwise treat method name as status
    return $this->status($method);
}

private function status($statusKey)
{
    $Bid_Session = $this->session->userdata('logged_in');

    if(empty($Bid_Session)){
        redirect(base_url('bid/index'));
    }

    // Get status from master table
    $status = $this->db->where('StatusName', $statusKey)
                       ->where('IsActive',1)
                       ->get('BidStatusMaster')
                       ->row();

    if(!$status){
        show_404();
    }

    // Fetch bids of that status
    $this->db->select('b.*, s.DisplayName, s.BadgeClass');
    $this->db->from('BidCreationDetails b');
    $this->db->join('BidStatusMaster s','s.Sid = b.BidStatus','left');
    $this->db->where('b.BidStatus', $status->Sid);
    $this->db->order_by('b.CreationTs','DESC');

    $data['BidData'] = $this->db->get()->result_array();
    $data['PageTitle'] = $status->DisplayName;

    $this->template->write_view('content', 'bid/BidDetails', $data);
	$this->template->render();
}


public function getStatuses()
{
	$Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
		    $currentStatus = $this->input->post('current_status');
		    // echo "<ptr>currentStatus"; print_r($currentStatus);// exit;
		    // $this->db->where('IsActive', 1);
		    // $this->db->where('Sid !=', $currentStatus);
		    // $this->db->order_by('SortOrder', 'ASC');

		    // $statuses = $this->db->get('BidStatusMaster')->result();
		     // echo "<ptr>query"; print_r($statuses);// exit;

		    // 1️⃣ Get current status SortOrder
		        $current = $this->db->select('SortOrder')
		                            ->where('Sid', $currentStatus)
		                            ->get('BidStatusMaster')
		                            ->row();

		        if(!$current){
		            echo json_encode([]);
		            return;
		        }

		        $currentSortOrder = $current->SortOrder;

		        // 2️⃣ Get only forward stages
		        $this->db->where('IsActive', 1);
		        $this->db->where('SortOrder >', $currentSortOrder);
		        $this->db->order_by('SortOrder', 'ASC');

		        $statuses = $this->db->get('BidStatusMaster')->result();

		    echo json_encode($statuses);
		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}

public function getManagementUsers()
{
    $Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{

			$users = $this->db->where('Status', 0)
                      ->get('BidApproverdetails')
                      ->result();

  			  echo json_encode($users);
  		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}

public function updateBidStatus()
{
     $Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
				//$this->db->trans_begin();
 				// print_r($_POST);
				$ipAddress = $_SERVER['REMOTE_ADDR'];

				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				    $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}

				$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
				
				$bcid = $this->input->post('Bcid');
		        $newStatus = $this->input->post('bid_status');
		        $actualDate = $this->input->post('ActualDate');
		        $approvedMgmt = $this->input->post('ApprovedByManagement');
		        $approvedWhom = $this->input->post('ApprovedByWhom');
		        $remarks = $this->input->post('Remarks');

		        $user = $this->session->userdata('logged_in');

		        if(empty($bcid) || empty($newStatus)){
		            echo json_encode(['status'=>false,'message'=>'Required fields missing']);
		            return;
		        }

		        // Get Current Status
		        $current = $this->db->select('BidStatus')
		                            ->where('Bcid', $bcid)
		                            ->get('BidCreationDetails')
		                            ->row();

		        if(!$current){
		            echo json_encode(['status'=>false,'message'=>'Invalid Bid']);
		            return;
		        }

		        $oldStatus = $current->BidStatus;

		        // Prevent same status update
		        if($oldStatus == $newStatus){
		            echo json_encode(['status'=>false,'message'=>'Status already same']);
		            return;
		        }

		        $this->db->trans_start();

		        // 1️⃣ Update Main Table
		        $this->db->where('Bcid', $bcid)
		                 ->update('BidCreationDetails', [
		                     'BidStatus' => $newStatus
		                 ]);

		        // 2️⃣ Insert Tracking Record
		        $this->db->insert('BidStatusTracking', [
		            'Bcid' => $bcid,
		            'OldStatusId' => $oldStatus,
		            'CurrentStatusId' => $newStatus,
		            'ActualDate' => $actualDate,
		            'ApprovedByManagement' => $approvedMgmt ?: 0,
		            'ApprovedByWhom' => !empty($approvedWhom) ? $approvedWhom : NULL,
		            'Remarks' => $remarks,
		            'UpdatedBy' => $Bid_Session['Bid']
		        ]);

		        $this->db->trans_complete();

		        echo json_encode(['status'=>true]);
		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}

public function BidRoleUsers(){
 $Bid_Session=$this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			 $data['UsersData'] = $this->bid_model->getUsersData();
			 // echo "<pre>currentStatus"; print_r($data['UsersData']); exit;
			 $this->template->write_view('content', 'bid/BidRoleUsersMenus', $data);
  			 $this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}


}
public function manageRoleMenus()
{
    $roleId = $this->input->post('roleId');

    if(empty($roleId)){
        show_error("Role ID missing");
    }

    $this->db->select('m.*, 
        IF(r.MenuId IS NOT NULL, 1, 0) as IsAssigned')
        ->from('BidMenus m')
        ->join(
            'RoleMenuPermissions r',
            'r.MenuId = m.Bmid AND r.RoleId = '.$this->db->escape($roleId),
            'left'
        )
        ->where('m.IsActive', 1)
        ->order_by('m.ParentId, m.SortOrder');

    $menus = $this->db->get()->result_array();

    $data['menus'] = $this->buildTree($menus);
    $data['roleId'] = $roleId;

    // IMPORTANT: For AJAX, load only partial view
    $this->load->view('bid/BidMenus', $data);
}
private function buildTree($menus)
{
    $tree = [];
    $refs = [];

    foreach ($menus as $menu) {
        $menu['children'] = [];
        $refs[$menu['Bmid']] = $menu;
    }

    foreach ($refs as $id => &$menu) {
        if ($menu['ParentId'] == 0) {
            $tree[$id] = &$menu;
        } else {
            $refs[$menu['ParentId']]['children'][] = &$menu;
        }
    }

    return $tree;
}

 public function updatePermission()
{
    $roleId = $this->input->post('roleId');
    $menuId = $this->input->post('menuId');
    $action = $this->input->post('action');

    if($action == 'add'){

        $exists = $this->db->where('RoleId',$roleId)
                           ->where('MenuId',$menuId)
                           ->get('RoleMenuPermissions')
                           ->row();

        if(!$exists){
            $this->db->insert('RoleMenuPermissions',[
                'RoleId' => $roleId,
                'MenuId' => $menuId
            ]);
        }

    } else {

        $this->db->where('RoleId',$roleId)
                 ->where('MenuId',$menuId)
                 ->delete('RoleMenuPermissions');
    }

    echo "Updated";
}

public  function logout()
    {
			if($this->session->userdata('logged_in'))
			{
				 $employee_det = $this->session->userdata('logged_in');
				$data['Buid'] = $employee_det['Bid'];
				$data['UserRole'] = $employee_det['RoleId'];
				 $data['Logdescription'] = "User ".$Bid_Data['Bid']." Logged Out Successfully on ".date("Y M d H i s");
				 $ip_address = $this->input->ip_address();
				 $data['Ipaddress'] = $ip_address;
 				 $data['LogInTime'] ='0';
				 $curr_time=time();
                 $data['LogOutTime'] = date("Y-m-d H:i:s",$curr_time);
				 $data['Status'] ='0';
				$this->db->insert('BidLogin_Log', $data);

				$this->session->unset_userdata('logged_in');
				redirect($this->config->item('base_url').'bid/index');
			} else {
				redirect($this->config->item('base_url').'bid/index');
			}
	} 

} //MAIN