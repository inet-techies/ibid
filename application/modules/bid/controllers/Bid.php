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

		$Bid_Session = $this->session->userdata('logged_in');  
 		
 		if(isset($Bid_Session) && !empty($Bid_Session))
		{	
			// Get current URL (bid/dashboard)
			$currentUrl = strtolower(uri_string());

			// Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
				
			// Optional date filtering from query string
			$startDate = $this->input->get('start_date');
			$endDate   = $this->input->get('end_date');

			// Load Current and Overview datasets from model
			$data['current']   = $this->bid_model->getCurrentDashboardData($startDate, $endDate);
			$data['overview']  = $this->bid_model->getOverviewDashboardData($startDate, $endDate);
			$data['allBids']   = $this->bid_model->getAllBidsWithStatus();
			$data['statuses']  = $this->db->order_by('SortOrder', 'ASC')->get('BidStatusMaster')->result_array();
			$data['startDate'] = $startDate;
			$data['endDate']   = $endDate;

			// Theme path for asset references
			$data['theme_path'] = $this->config->item('theme_locations') . $this->config->item('active_template');

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
	$Bid_Session=$this->session->userdata('logged_in');  
 		
 	if(isset($Bid_Session) && !empty($Bid_Session))
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
	} else
	{
		$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
		redirect($this->config->item('base_url')."bid/index");
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
 		 		$inputs = $this->input->post();
 		if(!$inputs){
		    $this->session->set_flashdata('err', 'No Inputs Received');
		    redirect($this->config->item('base_url').'bid/CreateBids');
		    return;
		}
		else{
			// Server-side validation for all required fields
			$validationErrors = [];

			$bidType = trim($inputs['bidTypeInput'] ?? '');
			if (empty($bidType) || $bidType === '0') {
				$validationErrors[] = 'Bid Type is required (OEM or Service).';
			}

			$bidTitle = trim($inputs['bidTitleInput'] ?? '');
			if (empty($bidTitle)) {
				$validationErrors[] = 'Bid Title is required.';
			}

			$bidDepartment = trim($inputs['bidDepartmentInput'] ?? '');
			if (empty($bidDepartment)) {
				$validationErrors[] = 'Department/Organization is required.';
			}

			$rawEstimated = str_replace(',', '', trim($inputs['bidEstimatedInput'] ?? ''));
			if ($rawEstimated === '' || !is_numeric($rawEstimated) || (float)$rawEstimated <= 0) {
				$validationErrors[] = 'Estimated Tender Value must be a valid positive amount.';
			}

			$rawEmd = str_replace(',', '', trim($inputs['bidEmdInput'] ?? ''));
			if ($rawEmd === '' || !is_numeric($rawEmd) || (float)$rawEmd < 0) {
				$validationErrors[] = 'Bid EMD Value must be a valid amount (enter 0 if exempt).';
			}

			$publishedDate = trim($inputs['PublishedDate'] ?? '');
			if (empty($publishedDate) || !strtotime($publishedDate)) {
				$validationErrors[] = 'Valid Bid Published Date is required.';
			}

			$submissionDate = trim($inputs['SubmissionDate'] ?? '');
			if (empty($submissionDate) || !strtotime($submissionDate)) {
				$validationErrors[] = 'Valid Bid Submission End Date is required.';
			}

			$openingDate = trim($inputs['OpeningDate'] ?? '');
			if (empty($openingDate) || !strtotime($openingDate)) {
				$validationErrors[] = 'Valid Bid Opening Date is required.';
			}

			if (!empty($publishedDate) && !empty($submissionDate) && strtotime($submissionDate) < strtotime($publishedDate)) {
				$validationErrors[] = 'Submission End Date cannot be earlier than Published Date.';
			}

			if (!empty($submissionDate) && !empty($openingDate) && strtotime($openingDate) < strtotime($submissionDate)) {
				$validationErrors[] = 'Opening Date cannot be earlier than Submission End Date.';
			}

			$bidSummary = trim($inputs['bidSummaryInput'] ?? '');
			if (empty($bidSummary)) {
				$validationErrors[] = 'Keywords for Summary is required.';
			}

			$bidTenderPortal = trim($inputs['bidTenderPortalInput'] ?? '');
			if (empty($bidTenderPortal)) {
				$validationErrors[] = 'Tender Portal URL is required.';
			}

			$bidTenderId = trim($inputs['bidTenderIDInput'] ?? '');
			if (empty($bidTenderId)) {
				$validationErrors[] = 'Tender ID is required.';
			}

			$nasPath = !empty($inputs['bidNASIDInput']) ? trim($inputs['bidNASIDInput']) : (!empty($inputs['attachedDocumentPath']) ? trim($inputs['attachedDocumentPath']) : '');
			if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
				$uploadDir = FCPATH . 'uploads/tender/';
				if (!is_dir($uploadDir)) {
					@mkdir($uploadDir, 0777, true);
				}
				$safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['file']['name']);
				if (@move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $safeName)) {
					$nasPath = 'uploads/tender/' . $safeName;
				}
			}
			if (empty($nasPath)) {
				$validationErrors[] = 'NAS Path or uploaded tender document is required.';
			}

			$bidRemarks = trim($inputs['bidRemarksInput'] ?? '');
			if (empty($bidRemarks)) {
				$validationErrors[] = 'Remarks is required.';
			}

			if (!empty($validationErrors)) {
				$this->session->set_flashdata('err', implode('<br>', $validationErrors));
				redirect($this->config->item('base_url').'bid/CreateBids');
				return;
			}

			$Biddata = [
	            'BidType'        => $bidType,
	            'BidTitle'       => $bidTitle,
	            'BidDepartment'  => $bidDepartment,
	            'EstimatedValue' => (float)$rawEstimated,
	            'EmdValue'       => (float)$rawEmd,
	            'PublishedDate'  => date('Y-m-d', strtotime($publishedDate)),
	            'SubmissionDate' => date('Y-m-d', strtotime($submissionDate)),
	            'OpeningDate'    => date('Y-m-d', strtotime($openingDate)),
	            'SummaryKeywords'=> $bidSummary,
	            'TenderPortalUrl'=> $bidTenderPortal,
	            'TenderId'       => $bidTenderId,
	            'NASPath'        => $nasPath,
	            'Remarks'        => $bidRemarks,
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

					        $factSheetId = !empty($inputs['factSheetId']) ? (int)$inputs['factSheetId'] : 0;
					        $attachedDoc = !empty($inputs['attachedDocumentPath']) ? $inputs['attachedDocumentPath'] : $nasPath;
					        $this->load->model('document_parser/Factsheet_model');
					        if ($factSheetId > 0) {
					            $this->Factsheet_model->linkFactSheetToBid($factSheetId, $id);
					        } elseif (!empty($attachedDoc)) {
					            $fs = $this->Factsheet_model->getFactSheetByDocPath($attachedDoc);
					            if ($fs) {
					                $this->Factsheet_model->linkFactSheetToBid($fs['Tfsid'], $id);
					            }
					        }
					        // Immediately generate/sync Fact Sheet so it resembles the saved Bid details 1:1
					        $this->Factsheet_model->generateOrSyncFactsheetForBid($id, $Biddata);

					        // Send Email Notification to Prasanna Kumari (TO), with Reka and Fazil in CC
					        try {
					            require_once(APPPATH . 'libraries/InetMailer.php');
					            $objs = new InetMailer();
					            $mail = $objs->load();

					            $mail->setFrom('info@inetcsc.com', 'I-NET Bid Management');
					            $mail->addAddress('prasannakumari@inetcsc.com', 'Prasanna Kumari');
					            $mail->addCC('reka@inetcsc.com', 'Reka');
					            $mail->addCC('Mohamedfazil@inetcsc.com', 'Fazil');

					            $mail->isHTML(true);
					            $mail->Subject = "New Bid Created: " . $Biddata['BidTitle'];

					            if (file_exists(FCPATH . 'images/inet_logo.png')) {
					                $mail->AddEmbeddedImage(FCPATH . 'images/inet_logo.png', 'I-NET');
					            }

					            $emailData = [
					                'Bcid'      => $id,
					                'bid'       => $Biddata,
					                'createdBy' => $Bid_Session['BName'] ?? ($Bid_Session['Name'] ?? 'Administrator'),
					                'createdOn' => date('d M Y, h:i A'),
					                'portalUrl' => $this->config->item('base_url') . 'bid/CreateBids'
					            ];

					            $mail->Body = $this->load->view('bid/NewBidCreatedMail', $emailData, TRUE);
					            $mail->send();
					        } catch (\Throwable $e) {
					            log_message('error', 'Bid Creation Email Notification Failed: ' . $e->getMessage());
					        }

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

    if(isset($Bid_Session) && !empty($Bid_Session))
    {
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
    } else
    {
        $this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."bid/index");
    }
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
		        $techSelected = $this->input->post('TechnicalSelected');
		        $techSelectedVal = ($techSelected !== '' && $techSelected !== null) ? intval($techSelected) : null;
		        $priceDetails = $this->input->post('PriceDetails');
		        $loaReceived = $this->input->post('LoaReceived');
		        $loaReceivedVal = ($loaReceived !== '' && $loaReceived !== null) ? intval($loaReceived) : null;
		        $bgSubmitted = $this->input->post('BgSubmitted');
		        $bgSubmittedVal = ($bgSubmitted !== '' && $bgSubmitted !== null) ? intval($bgSubmitted) : null;
		        $woReceived = $this->input->post('WoReceived');
		        $woReceivedVal = ($woReceived !== '' && $woReceived !== null) ? intval($woReceived) : null;
		        $ccReceived = $this->input->post('CompletionCertificateReceived');
		        $ccReceivedVal = ($ccReceived !== '' && $ccReceived !== null) ? intval($ccReceived) : null;

		        $user = $this->session->userdata('logged_in');

		        if(empty($bcid) || empty($newStatus)){
		            echo json_encode(['status'=>false,'message'=>'Required fields missing']);
		            return;
		        }

		        // Get Current Status
		        $current = $this->db->select('BidStatus, LoaReceived, LoaDocumentPath, BgSubmitted, BgDocumentPath, WoReceived, WoDocumentPath, CompletionCertificateReceived, CompletionCertificateDocumentPath')
		                            ->where('Bcid', $bcid)
		                            ->get('BidCreationDetails')
		                            ->row();

		        if(!$current){
		            echo json_encode(['status'=>false,'message'=>'Invalid Bid']);
		            return;
		        }

		        $oldStatus = $current->BidStatus;

		        // CASE 0: Technical Selected is explicitly selected as "No" (0)
		        // User requirement: Bid remains in its current status / table / menu, with date & remarks saved to DB.
		        if ($techSelectedVal === 0) {
		            $this->db->trans_start();

		            $updateMain = [
		                'TechnicalSelected' => 0,
		                'Remarks'           => $remarks !== null ? trim($remarks) : '',
		                'ActualDate'        => !empty($actualDate) ? $actualDate : NULL,
		            ];

		            $this->db->where('Bcid', $bcid)->update('BidCreationDetails', $updateMain);

		            $trackingData = [
		                'Bcid'              => $bcid,
		                'OldStatusId'       => $oldStatus,
		                'CurrentStatusId'   => $oldStatus,
		                'TechnicalSelected' => 0,
		                'ActualDate'        => !empty($actualDate) ? $actualDate : NULL,
		                'Remarks'           => $remarks !== null ? trim($remarks) : 'Technical Evaluated: Not Selected',
		                'UpdatedBy'         => $Bid_Session['Bid'],
		                'Ipaddress'         => $ipAddress,
		                'UserAgent'         => $userAgent
		            ];

		            $this->db->insert('BidStatusTracking', $trackingData);
		            $this->db->trans_complete();

		            echo json_encode([
		                'status'  => true,
		                'message' => 'Technical evaluation marked as Not Selected. Bid remains in current status.'
		            ]);
		            return;
		        }

		        // CASE 1: LOA Received is explicitly selected as "No" (0)
		        // User requirement: Bid remains in its current status / table / menu, with date & remarks saved to DB.
		        if ($loaReceivedVal === 0) {
		            $this->db->trans_start();

		            $updateMain = [
		                'LoaReceived' => 0,
		                'Remarks'     => $remarks !== null ? trim($remarks) : '',
		                'ActualDate'  => !empty($actualDate) ? $actualDate : NULL,
		            ];

		            $this->db->where('Bcid', $bcid)->update('BidCreationDetails', $updateMain);

		            $trackingData = [
		                'Bcid'            => $bcid,
		                'OldStatusId'     => $oldStatus,
		                'CurrentStatusId' => $oldStatus,
		                'LoaReceived'     => 0,
		                'ActualDate'      => !empty($actualDate) ? $actualDate : NULL,
		                'Remarks'         => $remarks !== null ? trim($remarks) : 'LOA Not Received',
		                'UpdatedBy'       => $Bid_Session['Bid'],
		                'Ipaddress'       => $ipAddress,
		                'UserAgent'       => $userAgent
		            ];

		            $this->db->insert('BidStatusTracking', $trackingData);
		            $this->db->trans_complete();

		            echo json_encode([
		                'status'  => true,
		                'message' => 'LOA marked as Not Received. Bid remains in current status.'
		            ]);
		            return;
		        }

		        // CASE 2: LOA Received is selected as "Yes" (1) -> Handle document upload
		        $loaDocPath = $current->LoaDocumentPath ?? null;
		        if (isset($_FILES['loa_document']) && $_FILES['loa_document']['error'] === UPLOAD_ERR_OK) {
		            $uploadDir = FCPATH . 'uploads/loa/';
		            if (!is_dir($uploadDir)) {
		                @mkdir($uploadDir, 0777, true);
		            }
		            $safeName = 'loa_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['loa_document']['name']);
		            if (@move_uploaded_file($_FILES['loa_document']['tmp_name'], $uploadDir . $safeName)) {
		                $loaDocPath = 'uploads/loa/' . $safeName;
		            } else {
		                echo json_encode(['status' => false, 'message' => 'Failed to upload LOA document']);
		                return;
		            }
		        }

		        if ($loaReceivedVal === 1 && empty($loaDocPath)) {
		            echo json_encode(['status' => false, 'message' => 'Please upload the LOA Document']);
		            return;
		        }

		        // CASE 3: BG Submitted is explicitly selected as "No" (0)
		        // User requirement: Bid remains in its current status / table / menu, with date & remarks saved to DB.
		        if ($bgSubmittedVal === 0) {
		            $this->db->trans_start();

		            $updateMain = [
		                'BgSubmitted' => 0,
		                'Remarks'     => $remarks !== null ? trim($remarks) : '',
		                'ActualDate'  => !empty($actualDate) ? $actualDate : NULL,
		            ];

		            $this->db->where('Bcid', $bcid)->update('BidCreationDetails', $updateMain);

		            $trackingData = [
		                'Bcid'            => $bcid,
		                'OldStatusId'     => $oldStatus,
		                'CurrentStatusId' => $oldStatus,
		                'BgSubmitted'     => 0,
		                'ActualDate'      => !empty($actualDate) ? $actualDate : NULL,
		                'Remarks'         => $remarks !== null ? trim($remarks) : 'BG Not Submitted',
		                'UpdatedBy'       => $Bid_Session['Bid'],
		                'Ipaddress'       => $ipAddress,
		                'UserAgent'       => $userAgent
		            ];

		            $this->db->insert('BidStatusTracking', $trackingData);
		            $this->db->trans_complete();

		            echo json_encode([
		                'status'  => true,
		                'message' => 'BG marked as Not Submitted. Bid remains in current status.'
		            ]);
		            return;
		        }

		        // CASE 4: BG Submitted is selected as "Yes" (1) -> Handle document upload
		        $bgDocPath = $current->BgDocumentPath ?? null;
		        if (isset($_FILES['bg_document']) && $_FILES['bg_document']['error'] === UPLOAD_ERR_OK) {
		            $uploadDir = FCPATH . 'uploads/bg/';
		            if (!is_dir($uploadDir)) {
		                @mkdir($uploadDir, 0777, true);
		            }
		            $safeName = 'bg_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['bg_document']['name']);
		            if (@move_uploaded_file($_FILES['bg_document']['tmp_name'], $uploadDir . $safeName)) {
		                $bgDocPath = 'uploads/bg/' . $safeName;
		            } else {
		                echo json_encode(['status' => false, 'message' => 'Failed to upload BG document']);
		                return;
		            }
		        }

		        if ($bgSubmittedVal === 1 && empty($bgDocPath)) {
		            echo json_encode(['status' => false, 'message' => 'Please upload the BG Document']);
		            return;
		        }

		        // CASE 5: WO Received is explicitly selected as "No" (0)
		        // User requirement: Bid remains in its current status / table / menu, with date & remarks saved to DB.
		        if ($woReceivedVal === 0) {
		            $this->db->trans_start();

		            $updateMain = [
		                'WoReceived' => 0,
		                'Remarks'    => $remarks !== null ? trim($remarks) : '',
		                'ActualDate' => !empty($actualDate) ? $actualDate : NULL,
		            ];

		            $this->db->where('Bcid', $bcid)->update('BidCreationDetails', $updateMain);

		            $trackingData = [
		                'Bcid'            => $bcid,
		                'OldStatusId'     => $oldStatus,
		                'CurrentStatusId' => $oldStatus,
		                'WoReceived'      => 0,
		                'ActualDate'      => !empty($actualDate) ? $actualDate : NULL,
		                'Remarks'         => $remarks !== null ? trim($remarks) : 'WO Not Received',
		                'UpdatedBy'       => $Bid_Session['Bid'],
		                'Ipaddress'       => $ipAddress,
		                'UserAgent'       => $userAgent
		            ];

		            $this->db->insert('BidStatusTracking', $trackingData);
		            $this->db->trans_complete();

		            echo json_encode([
		                'status'  => true,
		                'message' => 'WO marked as Not Received. Bid remains in current status.'
		            ]);
		            return;
		        }

		        // CASE 6: WO Received is selected as "Yes" (1) -> Handle document upload
		        $woDocPath = $current->WoDocumentPath ?? null;
		        if (isset($_FILES['wo_document']) && $_FILES['wo_document']['error'] === UPLOAD_ERR_OK) {
		            $uploadDir = FCPATH . 'uploads/wo/';
		            if (!is_dir($uploadDir)) {
		                @mkdir($uploadDir, 0777, true);
		            }
		            $safeName = 'wo_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['wo_document']['name']);
		            if (@move_uploaded_file($_FILES['wo_document']['tmp_name'], $uploadDir . $safeName)) {
		                $woDocPath = 'uploads/wo/' . $safeName;
		            } else {
		                echo json_encode(['status' => false, 'message' => 'Failed to upload WO document']);
		                return;
		            }
		        }

		        if ($woReceivedVal === 1 && empty($woDocPath)) {
		            echo json_encode(['status' => false, 'message' => 'Please upload the WO Document']);
		            return;
		        }

		        // CASE 7: Completion Certificate is explicitly selected as "No" (0)
		        // User requirement: Bid remains in its current status / table / menu, with date & remarks saved to DB.
		        if ($ccReceivedVal === 0) {
		            $this->db->trans_start();

		            $updateMain = [
		                'CompletionCertificateReceived' => 0,
		                'Remarks'                       => $remarks !== null ? trim($remarks) : '',
		                'ActualDate'                    => !empty($actualDate) ? $actualDate : NULL,
		            ];

		            $this->db->where('Bcid', $bcid)->update('BidCreationDetails', $updateMain);

		            $trackingData = [
		                'Bcid'                          => $bcid,
		                'OldStatusId'                   => $oldStatus,
		                'CurrentStatusId'               => $oldStatus,
		                'CompletionCertificateReceived' => 0,
		                'ActualDate'                    => !empty($actualDate) ? $actualDate : NULL,
		                'Remarks'                       => $remarks !== null ? trim($remarks) : 'Completion Certificate Not Received',
		                'UpdatedBy'                     => $Bid_Session['Bid'],
		                'Ipaddress'                     => $ipAddress,
		                'UserAgent'                     => $userAgent
		            ];

		            $this->db->insert('BidStatusTracking', $trackingData);
		            $this->db->trans_complete();

		            echo json_encode([
		                'status'  => true,
		                'message' => 'Completion Certificate marked as Not Received. Bid remains in current status.'
		            ]);
		            return;
		        }

		        // CASE 8: Completion Certificate is selected as "Yes" (1) -> Handle document upload
		        $ccDocPath = $current->CompletionCertificateDocumentPath ?? null;
		        if (isset($_FILES['completion_certificate_document']) && $_FILES['completion_certificate_document']['error'] === UPLOAD_ERR_OK) {
		            $uploadDir = FCPATH . 'uploads/completion_certificate/';
		            if (!is_dir($uploadDir)) {
		                @mkdir($uploadDir, 0777, true);
		            }
		            $safeName = 'cc_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['completion_certificate_document']['name']);
		            if (@move_uploaded_file($_FILES['completion_certificate_document']['tmp_name'], $uploadDir . $safeName)) {
		                $ccDocPath = 'uploads/completion_certificate/' . $safeName;
		            } else {
		                echo json_encode(['status' => false, 'message' => 'Failed to upload Completion Certificate document']);
		                return;
		            }
		        }

		        if ($ccReceivedVal === 1 && empty($ccDocPath)) {
		            echo json_encode(['status' => false, 'message' => 'Please upload the Completion Certificate Document']);
		            return;
		        }

		        // Prevent same status update for standard transitions
		        if($oldStatus == $newStatus){
		            echo json_encode(['status'=>false,'message'=>'Status already same']);
		            return;
		        }

		        $this->db->trans_start();

		        // 1️⃣ Update Main Table
		        $updateMain = [
		            'BidStatus' => $newStatus,
		            'Remarks'   => $remarks !== null ? trim($remarks) : '',
		            'ActualDate'=> !empty($actualDate) ? $actualDate : NULL,
		            'ApprovedByManagement' => !empty($approvedMgmt) ? (int)$approvedMgmt : 0,
		            'ApprovedByWhom'       => !empty($approvedWhom) ? (int)$approvedWhom : NULL,
		        ];
		        if ($techSelectedVal !== null) {
		            $updateMain['TechnicalSelected'] = $techSelectedVal;
		        }
		        if ($priceDetails !== null && $priceDetails !== '') {
		            $updateMain['PriceDetails'] = trim($priceDetails);
		        }
		        if ($loaReceivedVal !== null) {
		            $updateMain['LoaReceived'] = $loaReceivedVal;
		        }
		        if (!empty($loaDocPath)) {
		            $updateMain['LoaDocumentPath'] = $loaDocPath;
		        }
		        if ($bgSubmittedVal !== null) {
		            $updateMain['BgSubmitted'] = $bgSubmittedVal;
		        }
		        if (!empty($bgDocPath)) {
		            $updateMain['BgDocumentPath'] = $bgDocPath;
		        }
		        if ($woReceivedVal !== null) {
		            $updateMain['WoReceived'] = $woReceivedVal;
		        }
		        if (!empty($woDocPath)) {
		            $updateMain['WoDocumentPath'] = $woDocPath;
		        }
		        if ($ccReceivedVal !== null) {
		            $updateMain['CompletionCertificateReceived'] = $ccReceivedVal;
		        }
		        if (!empty($ccDocPath)) {
		            $updateMain['CompletionCertificateDocumentPath'] = $ccDocPath;
		        }

		        $this->db->where('Bcid', $bcid)
		                 ->update('BidCreationDetails', $updateMain);

		        // 2️⃣ Insert Tracking Record
		        $trackingData = [
		            'Bcid' => $bcid,
		            'OldStatusId' => $oldStatus,
		            'CurrentStatusId' => $newStatus,
		            'ActualDate' => !empty($actualDate) ? $actualDate : NULL,
		            'ApprovedByManagement' => !empty($approvedMgmt) ? (int)$approvedMgmt : 0,
		            'ApprovedByWhom' => !empty($approvedWhom) ? (int)$approvedWhom : NULL,
		            'Remarks' => $remarks !== null ? trim($remarks) : '',
		            'UpdatedBy' => $Bid_Session['Bid'],
		            'Ipaddress' => $ipAddress,
		            'UserAgent' => $userAgent
		        ];
		        if ($techSelectedVal !== null) {
		            $trackingData['TechnicalSelected'] = $techSelectedVal;
		        }
		        if ($priceDetails !== null && $priceDetails !== '') {
		            $trackingData['PriceDetails'] = trim($priceDetails);
		        }
		        if ($loaReceivedVal !== null) {
		            $trackingData['LoaReceived'] = $loaReceivedVal;
		        }
		        if (!empty($loaDocPath)) {
		            $trackingData['AttachmentPath'] = $loaDocPath;
		        }
		        if ($bgSubmittedVal !== null) {
		            $trackingData['BgSubmitted'] = $bgSubmittedVal;
		        }
		        if (!empty($bgDocPath)) {
		            $trackingData['AttachmentPath'] = $bgDocPath;
		        }
		        if ($woReceivedVal !== null) {
		            $trackingData['WoReceived'] = $woReceivedVal;
		        }
		        if (!empty($woDocPath)) {
		            $trackingData['AttachmentPath'] = $woDocPath;
		        }
		        if ($ccReceivedVal !== null) {
		            $trackingData['CompletionCertificateReceived'] = $ccReceivedVal;
		        }
		        if (!empty($ccDocPath)) {
		            $trackingData['AttachmentPath'] = $ccDocPath;
		        }

		        $this->db->insert('BidStatusTracking', $trackingData);

		        $this->db->trans_complete();

		        echo json_encode(['status'=>true, 'message'=>'Status Updated Successfully']);
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
    $Bid_Session = $this->session->userdata('logged_in');

    if(isset($Bid_Session) && !empty($Bid_Session))
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
    } else
    {
        $this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."bid/index");
    }
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
    $Bid_Session = $this->session->userdata('logged_in');

    if(isset($Bid_Session) && !empty($Bid_Session))
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
    } else
    {
        $this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
        redirect($this->config->item('base_url')."bid/index");
    }
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

    /**
     * Render the 12-Section Tender Fact Sheet
     * Route: GET/POST /bid/factsheet?id=X or ?bcid=Y or ?doc=Z
     */
    public function factsheet()
    {
        $Bid_Session = $this->session->userdata('logged_in');

        if (isset($Bid_Session) && !empty($Bid_Session))
        {
            $this->load->model('document_parser/Factsheet_model');

            $fsid = $this->input->get_post('id') ? (int)$this->input->get_post('id') : (int)$this->input->get_post('fsid');
            $bcid = (int)$this->input->get_post('bcid');
            $doc  = $this->input->get_post('doc');

            $factsheet = null;
            if ($fsid > 0) {
                $factsheet = $this->Factsheet_model->getFactSheetById($fsid);
                if (!empty($factsheet['Bcid'])) {
                    $factsheet = $this->Factsheet_model->generateOrSyncFactsheetForBid($factsheet['Bcid']);
                }
            } elseif ($bcid > 0) {
                $factsheet = $this->Factsheet_model->getFactSheetByBcid($bcid);
            } elseif (!empty($doc)) {
                $factsheet = $this->Factsheet_model->getFactSheetByDocPath($doc);
            }

            // Allow live overlay/sync from current CreateBids form fields passed via POST
            $formSync = $this->input->post();
            if (!empty($formSync) && is_array($formSync)) {
                $updatedFields = [];
                if (!empty($formSync['bidTitle'])) {
                    $factsheet['TenderTitle'] = trim($formSync['bidTitle']);
                    $updatedFields['TenderTitle'] = $factsheet['TenderTitle'];
                }
                if (!empty($formSync['bidDepartment'])) {
                    $factsheet['Department'] = trim($formSync['bidDepartment']);
                    $updatedFields['Department'] = $factsheet['Department'];
                }
                if (!empty($formSync['bidType']) && $formSync['bidType'] !== '0') {
                    $factsheet['TenderType'] = trim($formSync['bidType']);
                    $updatedFields['TenderType'] = $factsheet['TenderType'];
                }
                if (!empty($formSync['bidEstimated'])) {
                    $factsheet['ProjectValue'] = trim($formSync['bidEstimated']);
                    $factsheet['EstimatedValue'] = trim($formSync['bidEstimated']);
                    $updatedFields['ProjectValue'] = $factsheet['ProjectValue'];
                    $updatedFields['EstimatedValue'] = $factsheet['EstimatedValue'];
                }
                if (!empty($formSync['bidEmd'])) {
                    $factsheet['EmdAmount'] = trim($formSync['bidEmd']);
                    $updatedFields['EmdAmount'] = $factsheet['EmdAmount'];
                }
                if (!empty($formSync['bidTenderID'])) {
                    $factsheet['TenderRefNo'] = trim($formSync['bidTenderID']);
                    $updatedFields['TenderRefNo'] = $factsheet['TenderRefNo'];
                }
                if (!empty($formSync['startDate'])) {
                    $factsheet['TenderPublishedDate'] = trim($formSync['startDate']);
                    $factsheet['TenderDate'] = trim($formSync['startDate']);
                    $updatedFields['TenderPublishedDate'] = $factsheet['TenderPublishedDate'];
                    $updatedFields['TenderDate'] = $factsheet['TenderDate'];
                }
                if (!empty($formSync['dueDate'])) {
                    $factsheet['BidSubmissionDeadline'] = trim($formSync['dueDate']);
                    $updatedFields['BidSubmissionDeadline'] = $factsheet['BidSubmissionDeadline'];
                }
                if (!empty($formSync['dateofBirth'])) {
                    $factsheet['TechnicalBidOpening'] = trim($formSync['dateofBirth']);
                    $updatedFields['TechnicalBidOpening'] = $factsheet['TechnicalBidOpening'];
                }
                if (!empty($formSync['bidSummary']) && empty($factsheet['ScopeOfWork'])) {
                    $factsheet['ScopeOfWork'] = trim($formSync['bidSummary']);
                    $updatedFields['ScopeOfWork'] = $factsheet['ScopeOfWork'];
                }

                // Keep the 12 section columns in perfect sync with the updated form fields
                $factsheet = $this->Factsheet_model->buildSectionPayload($factsheet);
                foreach ([
                    'TenderOverview', 'ImportantDates', 'TenderValue', 'ScopeOfWork',
                    'AreasCovered', 'ContractPeriod', 'AwardCriteria', 'PerformanceGuarantee',
                    'BidValidity', 'Warranty', 'RiskPoints', 'OpportunityAssessment'
                ] as $secCol) {
                    if (isset($factsheet[$secCol])) {
                        $updatedFields[$secCol] = $factsheet[$secCol];
                    }
                }

                if (!empty($updatedFields) && !empty($factsheet['Tfsid'])) {
                    $this->db->where('Tfsid', (int)$factsheet['Tfsid'])->update('tenderfactsheet', $updatedFields);
                }
            }

            if (!$factsheet) {
                $this->session->set_flashdata('err', 'No Fact Sheet found. Please upload a tender document first.');
                redirect($this->config->item('base_url') . 'bid/CreateBids');
                return;
            }

            $data = [
                'page_title' => 'Tender Fact Sheet - ' . (!empty($factsheet['TenderRefNo']) ? $factsheet['TenderRefNo'] : (!empty($factsheet['DocumentName']) ? $factsheet['DocumentName'] : 'Document')),
                'factsheet'  => $factsheet
            ];

            if ($this->input->get('ajax') || $this->input->is_ajax_request()) {
                $data['is_ajax'] = true;
                $this->load->view('document_parser/FactSheet', $data);
                return;
            }

            if ($this->input->get('print') || $this->input->get('standalone')) {
                $this->load->view('document_parser/FactSheet', $data);
            } else {
                $currentUrl = strtolower(uri_string());
                $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
                $this->template->write_view('content', 'document_parser/FactSheet', $data);
                $this->template->render();
            }
        } else
        {
            $this->session->set_flashdata('err', 'Invalid Session.Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

} //MAIN