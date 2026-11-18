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
		$this->session->unset_userdata('pending_otp_login');
		
		$this->template->set_master_template('../../themes/'.$this->config->item("active_template").'/landing_template_login.php');
		$this->template->write_view('content', 'bid/index');
		$this->template->render();
	}


	public function CheckLogin()
	{
		$inps = $this->input->post();
		$username = isset($inps['EmailId']) ? trim((string)$inps['EmailId']) : '';
		$yourpassword = isset($inps['EmailPass']) ? (string)$inps['EmailPass'] : '';

		if ($username === '' || $yourpassword === '') {
			$this->session->set_flashdata('err', 'Please enter both Email and Password.');
			redirect($this->config->item('base_url').'bid/index');
			return;
		}

		$Buquery = $this->bid_model->getLoginUser($username, $yourpassword);

		if (!empty($Buquery)) {
			$userRow = $Buquery[0];

			/*
			// =========================================================================
			// [TEMPORARILY COMMENTED: 2-Step OTP Verification for Login]
			// Uncomment this block once SMTP / mail delivery is configured.
			// =========================================================================
			$this->session->unset_userdata('logged_in');

			$otpResult = $this->_issueAndSendOtp($userRow, 'login');

			$this->session->set_userdata('pending_otp_login', [
				'Buid'    => (int)$userRow['Buid'],
				'Name'    => $userRow['Name'],
				'Email'   => $userRow['Email'],
				'Brid'    => $userRow['Brid'],
				'sent_at' => time(),
			]);

			$masked = $this->_maskEmail($userRow['Email']);
			if (!empty($otpResult['mail_sent'])) {
				$this->session->set_flashdata('yes', 'A 6-digit login OTP has been sent to ' . $masked . '.');
			} else {
				$this->session->set_flashdata('err', 'Could not deliver OTP email right now. Please click Resend or contact support.');
			}

			redirect($this->config->item('base_url').'bid/verifyLoginOtp');
			return;
			// =========================================================================
			*/

			// Direct Login (Bypassing OTP challenge)
			$sess_array = array(
				'Bid'    => $userRow['Buid'],
				'BName'  => $userRow['Name'],
				'BEmail' => $userRow['Email'],
				'RoleId' => $userRow['Brid']
			);
			$this->session->set_userdata('logged_in', $sess_array);

			$ip_address = $this->input->ip_address();
			$login_time = date("Y-m-d H:i:s");
			$logData = array(
				'Logdescription' => "User " . $userRow['Buid'] . " Logged in Successfully on " . date("Y M d H i s"),
				'Buid'           => $userRow['Buid'],
				'UserRole'       => $userRow['Brid'],
				'Ipaddress'      => $ip_address,
				'LogInTime'      => $login_time,
				'LogOutTime'     => '',
				'Status'         => '1'
			);
			$this->bid_model->insertLoginLog($logData);

			redirect($this->config->item('base_url').'bid/dashboard');
			return;
		} else {
			$this->session->set_flashdata('err', 'Invalid Login Credentials..!');
			redirect($this->config->item('base_url').'bid/index');
			return;
		}
	}

	/**
	 * Render 2FA OTP verification screen for pending login session
	 */
	public function verifyLoginOtp()
	{
		if (!empty($this->session->userdata('logged_in'))) {
			redirect($this->config->item('base_url').'bid/dashboard');
			return;
		}

		$pending = $this->session->userdata('pending_otp_login');
		if (empty($pending) || empty($pending['Buid'])) {
			$this->session->set_flashdata('err', 'Your login session has expired. Please sign in again.');
			redirect($this->config->item('base_url').'bid/index');
			return;
		}

		$elapsed = time() - (int)($pending['sent_at'] ?? 0);
		$data['maskedEmail'] = $this->_maskEmail($pending['Email'] ?? '');
		$data['resendWait']  = ($elapsed < 60) ? (60 - $elapsed) : 0;

		$this->template->set_master_template('../../themes/'.$this->config->item('active_template').'/landing_template_login.php');
		$this->template->write_view('content', 'bid/VerifyLoginOtp', $data);
		$this->template->render();
	}

	/**
	 * AJAX endpoint to verify Login OTP and establish authenticated session
	 */
	public function verifyLoginOtpAjax()
	{
		if (!headers_sent()) {
			header('Content-Type: application/json');
		}

		$pending = $this->session->userdata('pending_otp_login');
		if (empty($pending) || empty($pending['Buid'])) {
			echo json_encode([
				'status'         => false,
				'expiredSession' => true,
				'message'        => 'Login session expired. Redirecting to login...',
				'redirect'       => base_url('bid/index')
			]);
			return;
		}

		$buid = (int)$pending['Buid'];
		$enteredOtp = preg_replace('/\D/', '', (string)$this->input->post('otp'));
		$user = $this->bid_model->getBidUserRow($buid);

		if (!$user || (int)$user->Status !== 1) {
			$this->session->unset_userdata('pending_otp_login');
			echo json_encode([
				'status'         => false,
				'expiredSession' => true,
				'message'        => 'Account unavailable. Please sign in again.',
				'redirect'       => base_url('bid/index')
			]);
			return;
		}

		if ((int)$user->OTPAttempts >= 5) {
			echo json_encode([
				'status'  => false,
				'locked'  => true,
				'message' => 'Maximum OTP attempts reached. Please click Resend to receive a new code.'
			]);
			return;
		}

		if (empty($user->OTPExpiry) || strtotime($user->OTPExpiry) < time()) {
			echo json_encode([
				'status'  => false,
				'message' => 'OTP has expired. Please click Resend to receive a new code.'
			]);
			return;
		}

		if (strlen($enteredOtp) !== 6 || empty($user->OTPHash) || !password_verify($enteredOtp, $user->OTPHash)) {
			$this->bid_model->incrementOtpAttempts($buid);
			$remaining = max(0, 5 - ((int)$user->OTPAttempts + 1));
			echo json_encode([
				'status'  => false,
				'message' => $remaining > 0
					? "Invalid OTP. {$remaining} attempt(s) remaining."
					: 'Invalid OTP. Maximum attempts reached; please request a new code.'
			]);
			return;
		}

		// Clear OTP fields in DB once verified
		$this->bid_model->updateBidUser($buid, [
			'OTPHash'     => NULL,
			'OTPExpiry'   => NULL,
			'OTPAttempts' => 0
		]);

		// Promote pending session to authenticated session
		$this->session->unset_userdata('pending_otp_login');
		if (method_exists($this->session, 'sess_regenerate')) {
			@$this->session->sess_regenerate(FALSE);
		}

		$sess_array = [
			'Bid'    => $user->Buid,
			'BName'  => $user->Name,
			'BEmail' => $user->Email,
			'RoleId' => $user->Brid,
		];
		$this->session->set_userdata('logged_in', $sess_array);

		$loginTime = date('Y-m-d H:i:s');
		$logData = [
			'Logdescription' => 'User ' . $user->Buid . ' Logged in with OTP on ' . date('Y M d H i s'),
			'Buid'           => $user->Buid,
			'UserRole'       => $user->Brid,
			'Ipaddress'      => $this->input->ip_address(),
			'LogInTime'      => $loginTime,
			'LogOutTime'     => '',
			'Status'         => '1',
		];
		$this->bid_model->insertLoginLog($logData);

		echo json_encode([
			'status'   => true,
			'message'  => 'OTP verified successfully! Redirecting...',
			'redirect' => base_url('bid/dashboard')
		]);
	}

	/**
	 * AJAX endpoint to resend Login OTP with 60-second cooldown
	 */
	public function resendLoginOtp()
	{
		if (!headers_sent()) {
			header('Content-Type: application/json');
		}

		$pending = $this->session->userdata('pending_otp_login');
		if (empty($pending) || empty($pending['Buid'])) {
			echo json_encode(['status' => false, 'msg' => 'Login session expired. Please sign in again.']);
			return;
		}

		$elapsed = time() - (int)($pending['sent_at'] ?? 0);
		if ($elapsed < 60) {
			$wait = 60 - $elapsed;
			echo json_encode(['status' => false, 'wait' => $wait, 'msg' => "Please wait {$wait}s before requesting another OTP."]);
			return;
		}

		$userRows = $this->bid_model->getUserById((int)$pending['Buid']);
		if (empty($userRows)) {
			echo json_encode(['status' => false, 'msg' => 'User account not found.']);
			return;
		}

		$otpResult = $this->_issueAndSendOtp($userRows[0], 'login');
		$pending['sent_at'] = time();
		$this->session->set_userdata('pending_otp_login', $pending);

		if (!empty($otpResult['mail_sent'])) {
			echo json_encode(['status' => true, 'msg' => 'A fresh 6-digit OTP has been sent to ' . $this->_maskEmail($userRows[0]['Email']) . '.']);
		} else {
			echo json_encode(['status' => false, 'msg' => 'Failed to send OTP email. Please check mail server settings.']);
		}
	}

	/**
	 * Generate, store, and email a 6-digit OTP for login or account verification
	 */
	private function _issueAndSendOtp(array $userRow, $purpose = 'login')
	{
		$buid = (int)$userRow['Buid'];
		$otp  = random_int(100000, 999999);

		$this->bid_model->updateBidUser($buid, [
			'OTPHash'     => password_hash((string)$otp, PASSWORD_DEFAULT),
			'OTPExpiry'   => date('Y-m-d H:i:s', strtotime('+10 minutes')),
			'OTPAttempts' => 0
		]);

		$mailData = [
			'otp'           => $otp,
			'name'          => $userRow['Name'] ?? 'User',
			'expiryMinutes' => 10,
			'ipAddress'     => $this->input->ip_address(),
			'RegMail'       => [$userRow],
		];

		$mailSent = false;
		$mailError = null;
		try {
			require_once(APPPATH . 'libraries/InetMailer.php');
			$objs = new InetMailer();
			$mail = $objs->load();

			$mail->setFrom('info@inetcsc.com', 'I-NET CSC');
			$mail->addAddress(trim((string)$userRow['Email']));
			$mail->isHTML(true);

			if ($purpose === 'login') {
				$mail->Subject = 'Login Verification OTP - Bid Management';
				$mail->Body    = $this->load->view('bid/LoginOTPMail', $mailData, TRUE);
			} else {
				$mail->addCC('prasannakumari@inetcsc.com');
				$mail->Subject = 'Employee Verification OTP - Bid Management';
				if (file_exists(FCPATH . 'images/inet_logo.png')) {
					$mail->AddEmbeddedImage(FCPATH . 'images/inet_logo.png', 'I-NET');
				}
				$mail->Body = $this->load->view('bid/EmployeeOTPMail', $mailData, TRUE);
			}

			$mailSent = (bool)$mail->send();
		} catch (\Exception $e) {
			$mailError = $e->getMessage();
			log_message('error', 'OTP mail send failed for Buid ' . $buid . ': ' . $mailError);
		}

		return ['mail_sent' => $mailSent, 'mail_error' => $mailError];
	}

	/**
	 * Mask email address for display on OTP screen (e.g. pr***@inetcsc.com)
	 */
	private function _maskEmail($email)
	{
		$email = trim((string)$email);
		if (strpos($email, '@') === false) return '***';
		list($local, $domain) = explode('@', $email, 2);
		$visible = substr($local, 0, min(2, strlen($local)));
		return $visible . str_repeat('*', max(3, strlen($local) - strlen($visible))) . '@' . $domain;
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
			$data['statuses']  = $this->bid_model->getStatuses();
			$data['categories']= $this->bid_model->getDistinctCategories();
			$data['states']    = $this->bid_model->getDistinctStates();
			$data['departments'] = $this->bid_model->getDistinctDepartments();
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

	    			$BidUsersDetails = $this->bid_model->checkUserExists($inps['phoneInput'], $inps['mailInput']);
					//echo"<pre>"; print_r($emp_details); exit;	
				   	if($BidUsersDetails>0){

				   			$this->session->set_flashdata('err', 'Employee Already Exists..!');
				   			redirect($this->config->item('base_url').'bid/AddUser'); 
				   			
				   	} else {

				   			$BidUsersdata=array('Name'=>$inps['fullnameInput'],'Mobile'=>$inps['phoneInput'],'Email' =>$inps['mailInput'],'Designation' =>$inps['designationInput'],'Brid' =>$inps['usertypeInput'],'Status' =>0);

							   	 $id = $this->bid_model->insertBidUser($BidUsersdata);
							   	  // echo"<pre>id";print_r($id);exit;
							   	if($id>0){

								 $data['RegMail'] = $this->bid_model->getUserById($id);
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
 
    $data['Buid'] = base64_decode(base64_decode(base64_decode(base64_decode(urldecode($Buid)))));

  
    $data['RegMail'] = $this->bid_model->getUserById($data['Buid']);

    if (empty($data['RegMail'])) {
      $this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
      redirect($this->config->item('base_url')."bid/index");
    }

  

    // Secure 6-digit OTP
    $otp = random_int(100000, 999999);

    // Save OTP securely
    $updateData = [
        'OTPHash'     => password_hash($otp, PASSWORD_DEFAULT),
        'OTPExpiry'   => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        'OTPAttempts' => 0
    ];

    $this->bid_model->updateBidUser($data['Buid'], $updateData);

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

    $user = $this->bid_model->getBidUserRow($Buid);

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
        $this->bid_model->incrementOtpAttempts($Buid);

        echo "Invalid OTP";
        return;
    }

    // OTP success → clear OTP
    $this->bid_model->updateBidUser($Buid, [
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

    $data['RegMail'] = $this->bid_model->getUserById($Buid);
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
	 
		 
		$UpdateId = $this->bid_model->checkUserExistsById($Bid);
	 	// echo"<pre>NPassInput"; print_r($ups['NPassInput']); 
	 	if($UpdateId>0)
	 	{
          $data=array('Passwd' =>md5($ups['NPassInput']),'ActivateTEmail' =>1,'Status' =>1);
	 	  $UpdateQuery = $this->bid_model->updateBidUser($Bid, $data); 
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

			    $update = $this->bid_model->updateBidUser($id, [
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

	    $user = $this->bid_model->getUserRowArray($id);

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

public function profile()
{
	redirect($this->config->item('base_url') . "bid/dashboard");
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

			$bidState = trim($inputs['bidStateInput'] ?? '');
			if (empty($bidState)) {
				$validationErrors[] = 'State is required.';
			}

			$bidCustomerName = trim($inputs['bidCustomerNameInput'] ?? '');
			if (empty($bidCustomerName)) {
				$validationErrors[] = 'Customer Name is required.';
			}

			$bidCustomerPhone = trim($inputs['bidCustomerPhoneInput'] ?? '');
			if (empty($bidCustomerPhone)) {
				$validationErrors[] = 'Customer Phone is required.';
			} elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $bidCustomerPhone)) {
				$validationErrors[] = 'Customer Phone must be a valid phone number (7-20 digits).';
			}

			$bidCustomerAddress = trim($inputs['bidCustomerAddressInput'] ?? '');
			if (empty($bidCustomerAddress)) {
				$validationErrors[] = 'Customer Address is required.';
			}

			$bidCategory = trim($inputs['bidCategoryInput'] ?? '');
			if (empty($bidCategory)) {
				$validationErrors[] = 'Category is required.';
			}

			if (!empty($validationErrors)) {
				$this->session->set_flashdata('err', implode('<br>', $validationErrors));
				redirect($this->config->item('base_url').'bid/CreateBids');
				return;
			}
						$docTenderId = $this->bid_model->generateBidId(
				$bidType,
				$bidDepartment,
				date('Y-m-d', strtotime($publishedDate))
			);

			if (!$docTenderId) {
				$this->session->set_flashdata('err', 'Unable to generate Bid ID. Please try again.');
				redirect($this->config->item('base_url').'bid/CreateBids');
				return;
			}
			$Biddata = [
	            'BidType'          => $bidType,
	            'BidTitle'         => $bidTitle,
				'DocTenderId' => $docTenderId,
	            'BidDepartment'    => $bidDepartment,
	            'State'            => $bidState,
	            'CustomerName'     => $bidCustomerName,
	            'CustomerPhone'    => $bidCustomerPhone,
	            'CustomerAddress'  => $bidCustomerAddress,
	            'Category'         => $bidCategory,
	            'EstimatedValue'   => (float)$rawEstimated,
	            'EmdValue'         => (float)$rawEmd,
	            'PublishedDate'    => date('Y-m-d', strtotime($publishedDate)),
	            'SubmissionDate'   => date('Y-m-d', strtotime($submissionDate)),
	            'OpeningDate'      => date('Y-m-d', strtotime($openingDate)),
	            'tenderPublicationTime'   => !empty($inputs['tenderPublicationTime'])   ? trim($inputs['tenderPublicationTime'])   : (!empty($publishedDate) && strpos($publishedDate, 'T') !== false ? date('H:i:s', strtotime($publishedDate)) : null),
	            'documentSaleStartDate'   => !empty($inputs['documentSaleStartDate'])   ? date('Y-m-d', strtotime($inputs['documentSaleStartDate'])) : null,
	            'documentSaleStartTime'   => !empty($inputs['documentSaleStartTime'])   ? trim($inputs['documentSaleStartTime'])   : (!empty($inputs['documentSaleStartDate']) && strpos($inputs['documentSaleStartDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['documentSaleStartDate'])) : null),
	            'documentSaleEndDate'     => !empty($inputs['documentSaleEndDate'])     ? date('Y-m-d', strtotime($inputs['documentSaleEndDate'])) : null,
	            'documentSaleEndTime'     => !empty($inputs['documentSaleEndTime'])     ? trim($inputs['documentSaleEndTime'])     : (!empty($inputs['documentSaleEndDate']) && strpos($inputs['documentSaleEndDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['documentSaleEndDate'])) : null),
	            'prebidQueryStartDate'    => !empty($inputs['prebidQueryStartDate'])    ? date('Y-m-d', strtotime($inputs['prebidQueryStartDate'])) : null,
	            'prebidQueryStartTime'    => !empty($inputs['prebidQueryStartTime'])    ? trim($inputs['prebidQueryStartTime'])    : (!empty($inputs['prebidQueryStartDate']) && strpos($inputs['prebidQueryStartDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['prebidQueryStartDate'])) : null),
	            'prebidQueryEndDate'      => !empty($inputs['prebidQueryEndDate'])      ? date('Y-m-d', strtotime($inputs['prebidQueryEndDate'])) : null,
	            'prebidQueryEndTime'      => !empty($inputs['prebidQueryEndTime'])      ? trim($inputs['prebidQueryEndTime'])      : (!empty($inputs['prebidQueryEndDate']) && strpos($inputs['prebidQueryEndDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['prebidQueryEndDate'])) : null),
	            'prebidMeetingDate'       => !empty($inputs['prebidMeetingDate'])       ? date('Y-m-d', strtotime($inputs['prebidMeetingDate'])) : null,
	            'prebidMeetingTime'       => !empty($inputs['prebidMeetingTime'])       ? trim($inputs['prebidMeetingTime'])       : (!empty($inputs['prebidMeetingDate']) && strpos($inputs['prebidMeetingDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['prebidMeetingDate'])) : null),
	            'prebidResponseDate'      => !empty($inputs['prebidResponseDate'])      ? date('Y-m-d', strtotime($inputs['prebidResponseDate'])) : null,
	            'prebidResponseTime'      => !empty($inputs['prebidResponseTime'])      ? trim($inputs['prebidResponseTime'])      : (!empty($inputs['prebidResponseDate']) && strpos($inputs['prebidResponseDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['prebidResponseDate'])) : null),
	            'bidSubmissionStartDate'  => !empty($inputs['bidSubmissionStartDate'])  ? date('Y-m-d', strtotime($inputs['bidSubmissionStartDate'])) : null,
	            'bidSubmissionStartTime'  => !empty($inputs['bidSubmissionStartTime'])  ? trim($inputs['bidSubmissionStartTime'])  : (!empty($inputs['bidSubmissionStartDate']) && strpos($inputs['bidSubmissionStartDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['bidSubmissionStartDate'])) : null),
	            'bidSubmissionEndTime'    => !empty($inputs['bidSubmissionEndTime'])    ? trim($inputs['bidSubmissionEndTime'])    : (!empty($submissionDate) && strpos($submissionDate, 'T') !== false ? date('H:i:s', strtotime($submissionDate)) : null),
	            'pqBidOpeningDate'        => !empty($inputs['pqBidOpeningDate'])        ? date('Y-m-d', strtotime($inputs['pqBidOpeningDate'])) : null,
	            'pqBidOpeningTime'        => !empty($inputs['pqBidOpeningTime'])        ? trim($inputs['pqBidOpeningTime'])        : (!empty($inputs['pqBidOpeningDate']) && strpos($inputs['pqBidOpeningDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['pqBidOpeningDate'])) : null),
	            'technicalBidOpeningTime' => !empty($inputs['technicalBidOpeningTime']) ? trim($inputs['technicalBidOpeningTime']) : (!empty($openingDate) && strpos($openingDate, 'T') !== false ? date('H:i:s', strtotime($openingDate)) : null),
	            'financialBidOpeningDate' => !empty($inputs['financialBidOpeningDate']) ? date('Y-m-d', strtotime($inputs['financialBidOpeningDate'])) : null,
	            'financialBidOpeningTime' => !empty($inputs['financialBidOpeningTime']) ? trim($inputs['financialBidOpeningTime']) : (!empty($inputs['financialBidOpeningDate']) && strpos($inputs['financialBidOpeningDate'], 'T') !== false ? date('H:i:s', strtotime($inputs['financialBidOpeningDate'])) : null),
	            'SummaryKeywords'  => $bidSummary,
	            'TenderPortalUrl'  => $bidTenderPortal,
	            'TenderId'         => $bidTenderId,
	            'NASPath'          => $nasPath,
	            'Remarks'          => $bidRemarks,
	            'BidStatus'        => 1
	        ];
			$editBcid = !empty($inputs['Bcid']) ? (int)$inputs['Bcid'] : 0;
			if ($editBcid > 0) {
				$this->db->where('Bcid', $editBcid)->update('BidCreationDetails', $Biddata);
				$id = $editBcid;
			} else {
				$id = $this->bid_model->insertBidCreationDetails($Biddata);
			}
			if($id>0){
				$status = $this->bid_model->getBidCreationRow($id);

				$tracking = [
					'Bcid'             => $id,
					'OldStatusId'      => $status->BidStatus,
					'CurrentStatusId'  => $status->BidStatus,
					'UpdatedBy'        => $Bid_Session['Bid'],
					'Ipaddress'        => $ip,
					'UserAgent'        => $userAgent,
					'IsSystemGenerated'=> 0
				];

				$this->bid_model->insertBidStatusTracking($tracking);

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
					// $mail->addAddress('prasannakumari@inetcsc.com', 'Prasanna Kumari');
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
			}
			else{
				$this->session->set_flashdata('err','Failed to Create Bid.Please try Again..!!');
				redirect($this->config->item('base_url').'bid/CreateBids');
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
	 
        $currentUrl = strtolower(uri_string());

$data['currentUrlArray'] =
    $this->bid_model->getBreadcrumb($currentUrl);

        	$data['BidData'] = $this->bid_model->getBidDetailsData();
        	$this->template->write_view('content', 'bid/BidDetails', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
}
    /**
     * AJAX endpoint to fetch bid modal details, categories, last modified info, and edit history
     */
    public function getBidModalDetails()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (empty($Bid_Session)) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized session. Please log in again.']);
            return;
        }

        $bcid = (int)$this->input->get_post('bcid');
        if ($bcid <= 0) {
            echo json_encode(['status' => false, 'message' => 'Invalid Bid ID.']);
            return;
        }

        $bid = $this->bid_model->getBidDetailsRow($bcid);
        if (empty($bid)) {
            echo json_encode(['status' => false, 'message' => 'Bid record not found.']);
            return;
        }

        $lastModified = $this->bid_model->getBidLastModifiedInfo($bcid);
        $history      = $this->bid_model->getBidTrackingHistory($bcid);
        $categories   = $this->bid_model->getActiveCategories();

        echo json_encode([
            'status'       => true,
            'bid'          => $bid,
            'lastModified' => $lastModified,
            'history'      => $history,
            'categories'   => $categories
        ]);
    }

    /**
     * AJAX endpoint to save edited bid details, record tracking in existing schema, and synchronize Fact Sheet
     */
    public function updateBidDetails()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (empty($Bid_Session)) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized action. Please log in again.']);
            return;
        }

        $bcid = (int)$this->input->post('Bcid');
        if ($bcid <= 0) {
            echo json_encode(['status' => false, 'message' => 'Invalid Bid ID.']);
            return;
        }

        $existing = $this->db->get_where('BidCreationDetails', ['Bcid' => $bcid])->row_array();
        if (empty($existing)) {
            echo json_encode(['status' => false, 'message' => 'Bid record not found.']);
            return;
        }

        $title = trim((string)$this->input->post('BidTitle'));
        if (empty($title)) {
            echo json_encode(['status' => false, 'message' => 'Bid Title is required.']);
            return;
        }

        $estValRaw = trim((string)$this->input->post('EstimatedValue'));
        $estVal = null;
        if ($estValRaw !== '') {
            $cleanEst = str_replace([',', ' '], '', $estValRaw);
            if (!is_numeric($cleanEst) || (float)$cleanEst < 0) {
                echo json_encode(['status' => false, 'message' => 'Estimated Value must be a valid positive number.']);
                return;
            }
            $estVal = number_format((float)$cleanEst, 2, '.', '');
        }

        $emdValRaw = trim((string)$this->input->post('EmdValue'));
        $emdVal = null;
        if ($emdValRaw !== '') {
            $cleanEmd = str_replace([',', ' '], '', $emdValRaw);
            if (!is_numeric($cleanEmd) || (float)$cleanEmd < 0) {
                echo json_encode(['status' => false, 'message' => 'EMD Value must be a valid positive number.']);
                return;
            }
            $emdVal = number_format((float)$cleanEmd, 2, '.', '');
        }

        $normalizeDatePost = function ($key) {
            $val = trim((string)$this->input->post($key));
            if ($val === '' || $val === '0000-00-00') return null;
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $val, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            $ts = strtotime($val);
            return $ts ? date('Y-m-d', $ts) : null;
        };

        $normalizeTimePost = function ($key, $linkedDateKey = null) {
            $val = trim((string)$this->input->post($key));
            if ($val === '') {
                if ($linkedDateKey) {
                    $dVal = trim((string)$this->input->post($linkedDateKey));
                    if ($dVal !== '' && strpos($dVal, 'T') !== false && strtotime($dVal)) {
                        return date('H:i', strtotime($dVal));
                    }
                }
                return null;
            }
            if (preg_match('/^(\d{1,2}):(\d{2})/', $val, $m)) {
                return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
            }
            $ts = strtotime($val);
            return $ts ? date('H:i', $ts) : $val;
        };

        $updateData = [
            'BidTitle'                => $title,
            'BidDepartment'           => trim((string)$this->input->post('BidDepartment')),
            'State'                   => trim((string)$this->input->post('State')),
            'CustomerName'            => trim((string)$this->input->post('CustomerName')),
            'CustomerPhone'           => trim((string)$this->input->post('CustomerPhone')),
            'CustomerAddress'         => trim((string)$this->input->post('CustomerAddress')),
            'Category'                => trim((string)$this->input->post('Category')),
            'BidType'                 => trim((string)$this->input->post('BidType')),
            'EstimatedValue'          => $estVal !== null ? $estVal : $existing['EstimatedValue'],
            'EmdValue'                => $emdVal !== null ? $emdVal : $existing['EmdValue'],
            'PublishedDate'           => $normalizeDatePost('PublishedDate'),
            'tenderPublicationTime'   => $normalizeTimePost('tenderPublicationTime', 'PublishedDate'),
            'documentSaleStartDate'   => $normalizeDatePost('documentSaleStartDate'),
            'documentSaleStartTime'   => $normalizeTimePost('documentSaleStartTime', 'documentSaleStartDate'),
            'documentSaleEndDate'     => $normalizeDatePost('documentSaleEndDate'),
            'documentSaleEndTime'     => $normalizeTimePost('documentSaleEndTime', 'documentSaleEndDate'),
            'prebidQueryStartDate'    => $normalizeDatePost('prebidQueryStartDate'),
            'prebidQueryStartTime'    => $normalizeTimePost('prebidQueryStartTime', 'prebidQueryStartDate'),
            'prebidQueryEndDate'      => $normalizeDatePost('prebidQueryEndDate'),
            'prebidQueryEndTime'      => $normalizeTimePost('prebidQueryEndTime', 'prebidQueryEndDate'),
            'prebidMeetingDate'       => $normalizeDatePost('prebidMeetingDate'),
            'prebidMeetingTime'       => $normalizeTimePost('prebidMeetingTime', 'prebidMeetingDate'),
            'prebidResponseDate'      => $normalizeDatePost('prebidResponseDate'),
            'prebidResponseTime'      => $normalizeTimePost('prebidResponseTime', 'prebidResponseDate'),
            'bidSubmissionStartDate'  => $normalizeDatePost('bidSubmissionStartDate'),
            'bidSubmissionStartTime'  => $normalizeTimePost('bidSubmissionStartTime', 'bidSubmissionStartDate'),
            'SubmissionDate'          => $normalizeDatePost('SubmissionDate'),
            'bidSubmissionEndTime'    => $normalizeTimePost('bidSubmissionEndTime', 'SubmissionDate'),
            'pqBidOpeningDate'        => $normalizeDatePost('pqBidOpeningDate'),
            'pqBidOpeningTime'        => $normalizeTimePost('pqBidOpeningTime', 'pqBidOpeningDate'),
            'OpeningDate'             => $normalizeDatePost('OpeningDate'),
            'technicalBidOpeningTime' => $normalizeTimePost('technicalBidOpeningTime', 'OpeningDate'),
            'financialBidOpeningDate' => $normalizeDatePost('financialBidOpeningDate'),
            'financialBidOpeningTime' => $normalizeTimePost('financialBidOpeningTime', 'financialBidOpeningDate'),
            'TenderPortalUrl'         => trim((string)$this->input->post('TenderPortalUrl')),
            'TenderId'                => trim((string)$this->input->post('TenderId')),
            'Remarks'                 => trim((string)$this->input->post('Remarks')),
            'SummaryKeywords'         => trim((string)$this->input->post('SummaryKeywords')),
            'LastChangeTs'            => date('Y-m-d H:i:s')
        ];

        // Track human-readable change log for audit
        $fieldLabels = [
            'BidTitle'                => 'Bid Title',
            'BidDepartment'           => 'Department',
            'State'                   => 'State',
            'CustomerName'            => 'Customer Name',
            'CustomerPhone'           => 'Customer Phone',
            'CustomerAddress'         => 'Customer Address',
            'Category'                => 'Category',
            'BidType'                 => 'Bid Type',
            'EstimatedValue'          => 'Estimated Value',
            'EmdValue'                => 'EMD Value',
            'PublishedDate'           => 'Published Date',
            'tenderPublicationTime'   => 'Published Time',
            'documentSaleStartDate'   => 'Document Sale Start Date',
            'documentSaleStartTime'   => 'Document Sale Start Time',
            'documentSaleEndDate'     => 'Document Sale End Date',
            'documentSaleEndTime'     => 'Document Sale End Time',
            'prebidQueryStartDate'    => 'Pre-Bid Query Start Date',
            'prebidQueryStartTime'    => 'Pre-Bid Query Start Time',
            'prebidQueryEndDate'      => 'Pre-Bid Query End Date',
            'prebidQueryEndTime'      => 'Pre-Bid Query End Time',
            'prebidMeetingDate'       => 'Pre-Bid Meeting Date',
            'prebidMeetingTime'       => 'Pre-Bid Meeting Time',
            'prebidResponseDate'      => 'Pre-Bid Response Date',
            'prebidResponseTime'      => 'Pre-Bid Response Time',
            'bidSubmissionStartDate'  => 'Bid Submission Start Date',
            'bidSubmissionStartTime'  => 'Bid Submission Start Time',
            'SubmissionDate'          => 'Submission Date',
            'bidSubmissionEndTime'    => 'Submission End Time',
            'pqBidOpeningDate'        => 'PQ Bid Opening Date',
            'pqBidOpeningTime'        => 'PQ Bid Opening Time',
            'OpeningDate'             => 'Opening Date',
            'technicalBidOpeningTime' => 'Opening Time',
            'financialBidOpeningDate' => 'Financial Bid Opening Date',
            'financialBidOpeningTime' => 'Financial Bid Opening Time',
            'TenderPortalUrl'         => 'Tender Portal',
            'TenderId'                => 'Tender ID',
            'Remarks'                 => 'Remarks',
            'SummaryKeywords'         => 'Keywords'
        ];

        $changes = [];
        foreach ($fieldLabels as $col => $lbl) {
            $oldVal = trim((string)($existing[$col] ?? ''));
            $newVal = trim((string)($updateData[$col] ?? ''));
            if ($col === 'EstimatedValue' || $col === 'EmdValue') {
                $oldNum = (float)str_replace([',', ' '], '', $oldVal);
                $newNum = (float)str_replace([',', ' '], '', $newVal);
                if (abs($oldNum - $newNum) > 0.001) {
                    $changes[] = "{$lbl}: " . number_format($oldNum, 2) . " -> " . number_format($newNum, 2);
                }
            } else {
                if ($oldVal !== $newVal) {
                    $changes[] = "{$lbl} updated";
                }
            }
        }

        $logRemarks = !empty($changes) ? implode('; ', $changes) : 'Bid details reviewed and saved';

        // Log tracking entry using existing BidStatusTracking schema
        $userId = (int)($Bid_Session['Bid'] ?? 1);
        $trackingData = [
            'Bcid'              => $bcid,
            'ActualDate'        => date('Y-m-d'),
            'OldStatusId'       => $existing['BidStatus'],
            'CurrentStatusId'   => $existing['BidStatus'],
            'UpdatedBy'         => $userId,
            'Remarks'           => substr($logRemarks, 0, 1000),
            'Ipaddress'         => $this->input->ip_address(),
            'UserAgent'         => substr($this->input->user_agent(), 0, 500),
            'IsSystemGenerated' => 0,
            'CreationTs'        => date('Y-m-d H:i:s')
        ];

        $success = $this->bid_model->updateBidDetailsAndTrack($bcid, $updateData, $trackingData);

        if (!$success) {
            echo json_encode(['status' => false, 'message' => 'Failed to save bid changes in database.']);
            return;
        }

        // ── CRITICAL: Synchronize with Fact Sheet ──
        $this->load->model('document_parser/Factsheet_model');
        $this->Factsheet_model->generateOrSyncFactsheetForBid($bcid, $updateData);

        // Fetch refreshed record
        $updatedBid   = $this->bid_model->getBidDetailsRow($bcid);
        $lastModified = $this->bid_model->getBidLastModifiedInfo($bcid);
        $history      = $this->bid_model->getBidTrackingHistory($bcid);

        echo json_encode([
            'status'       => true,
            'message'      => 'Bid Details saved successfully and synchronized with Fact Sheet!',
            'bid'          => $updatedBid,
            'lastModified' => $lastModified,
            'history'      => $history
        ]);
    }

public function LBids()
{
    return $this->status('L1Bids');
}

public function L1Bids()
{
    return $this->status('L1Bids');
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
        
        $status = $this->bid_model->getStatusByName($statusKey);

        if(!$status){
            show_404();
        }

      
        $currentUrl = strtolower(uri_string());
        $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
        $data['BidData'] = $this->bid_model->getBidsByStatusId($status->Sid);
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

		  
		        $current = $this->bid_model->getStatusSortOrder($currentStatus);

		        if(!$current){
		            echo json_encode([]);
		            return;
		        }

		        $currentSortOrder = $current->SortOrder;

		       
				
		        $statuses = $this->bid_model->getForwardStatuses($currentSortOrder);

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

			$users = $this->bid_model->getManagementUsers();

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
		            echo json_encode(['status'=>false,'message'=>'Please select a status.']);
		            return;
		        }

		        if(empty($actualDate)){
		            echo json_encode(['status'=>false,'message'=>'Please select a date.']);
		            return;
		        }

		        if($remarks === null || trim($remarks) === ''){
		            echo json_encode(['status'=>false,'message'=>'Please enter remarks.']);
		            return;
		        }

		        // Fetch target status details for contextual validation
		        $targetStatusRow = $this->bid_model->getStatusById($newStatus);
		        $targetStatusName = $targetStatusRow ? $targetStatusRow->StatusName : '';

		        if ($targetStatusName === 'TechnicalEvaluated' && $techSelectedVal === null) {
		            echo json_encode(['status'=>false,'message'=>'Please select whether Technical Selected (Yes / No).']);
		            return;
		        }

		        if (($targetStatusName === 'LBids' || $targetStatusName === 'L1Bids') && ($priceDetails === null || trim($priceDetails) === '')) {
		            echo json_encode(['status'=>false,'message'=>'Please enter price details.']);
		            return;
		        }

		        if ($targetStatusName === 'PQEvaluation') {
		            if (empty($approvedMgmt) || $approvedMgmt == 0) {
		                echo json_encode(['status'=>false,'message'=>'Please select whether Approved By Management (Yes / No).']);
		                return;
		            }
		            if ($approvedMgmt == 1 && empty($approvedWhom)) {
		                echo json_encode(['status'=>false,'message'=>'Please select who approved this bid.']);
		                return;
		            }
		        }

		        if ($targetStatusName === 'LoaReceived' && $loaReceivedVal === null) {
		            echo json_encode(['status'=>false,'message'=>'Please select whether LOA was received (Yes / No).']);
		            return;
		        }

		        if ($targetStatusName === 'BgSubmitted' && $bgSubmittedVal === null) {
		            echo json_encode(['status'=>false,'message'=>'Please select whether BG was submitted (Yes / No).']);
		            return;
		        }

		        if ($targetStatusName === 'WoReceived' && $woReceivedVal === null) {
		            echo json_encode(['status'=>false,'message'=>'Please select whether WO was received (Yes / No).']);
		            return;
		        }

		        if ($targetStatusName === 'CompletionCertificate' && $ccReceivedVal === null) {
		            echo json_encode(['status'=>false,'message'=>'Please select whether Completion Certificate was received (Yes / No).']);
		            return;
		        }

		        // Get Current Status
		        $current = $this->bid_model->getBidStatusInfo($bcid);

		        if(!$current){
		            echo json_encode(['status'=>false,'message'=>'Invalid Bid']);
		            return;
		        }

		        $oldStatus = $current->BidStatus;

		        // CASE 0: Technical Selected is explicitly selected as "No" (0)
		        // User requirement: Bid remains in its current status / table / menu, with date & remarks saved to DB.
		        if ($techSelectedVal === 0) {
		            $updateMain = [
		                'TechnicalSelected' => 0,
		                'Remarks'           => $remarks !== null ? trim($remarks) : '',
		            ];

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

		            $this->bid_model->updateBidStatusAndTracking($bcid, $updateMain, $trackingData);

		            echo json_encode([
		                'status'  => true,
		                'message' => 'Technical evaluation marked as Not Selected. Bid remains in current status.'
		            ]);
		            return;
		        }

		        // CASE 1: LOA Received is explicitly selected as "No" (0)
		        // User requirement: Bid remains in its current status / table / menu, with date & remarks saved to DB.
		        if ($loaReceivedVal === 0) {
		            $updateMain = [
		                'LoaReceived' => 0,
		                'Remarks'     => $remarks !== null ? trim($remarks) : '',
		            ];

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

		            $this->bid_model->updateBidStatusAndTracking($bcid, $updateMain, $trackingData);

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
		            $updateMain = [
		                'BgSubmitted' => 0,
		                'Remarks'     => $remarks !== null ? trim($remarks) : '',
		            ];

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

		            $this->bid_model->updateBidStatusAndTracking($bcid, $updateMain, $trackingData);

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
		            $updateMain = [
		                'WoReceived' => 0,
		                'Remarks'    => $remarks !== null ? trim($remarks) : '',
		            ];

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

		            $this->bid_model->updateBidStatusAndTracking($bcid, $updateMain, $trackingData);

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
		            $updateMain = [
		                'CompletionCertificateReceived' => 0,
		                'Remarks'                       => $remarks !== null ? trim($remarks) : '',
		            ];

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

		            $this->bid_model->updateBidStatusAndTracking($bcid, $updateMain, $trackingData);

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

		    
		        if($oldStatus == $newStatus){
		            echo json_encode(['status'=>false,'message'=>'Status already same']);
		            return;
		        }

		        
		        $updateMain = [
		            'BidStatus' => $newStatus,
		            'Remarks'   => $remarks !== null ? trim($remarks) : '',
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

		        
		        $trackingData = [
		            'Bcid' => $bcid,
		            'OldStatusId' => $oldStatus,
		            'CurrentStatusId' => $newStatus,
		            'ActualDate' => !empty($actualDate) ? $actualDate : NULL,
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

		        $updateResult = $this->bid_model->updateBidStatusAndTracking($bcid, $updateMain, $trackingData);

		        if ($updateResult) {
		            $this->sendStatusChangeNotification($bcid, $newStatus);
		        }

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

        $menus = $this->bid_model->getMenusWithAssignmentByRole($roleId);

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

        $this->bid_model->updateRoleMenuPermission($roleId, $menuId, $action);

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
				$this->bid_model->insertLoginLog($data);

				$this->session->unset_userdata('logged_in');
				$this->session->unset_userdata('pending_otp_login');
				redirect($this->config->item('base_url').'bid/index');
			} else {
				redirect($this->config->item('base_url').'bid/index');
			}
	}

    
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
                    $this->bid_model->updateTenderFactsheet($factsheet['Tfsid'], $updatedFields);
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

    public function view_tender_document()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (!isset($Bid_Session) || empty($Bid_Session)) {
            show_error('Unauthorized access. Please log in.', 401);
            return;
        }

        $tfsid = (int)$this->input->get('tfsid');
        $bcid  = (int)$this->input->get('bcid');

        $docPath = '';
        $docName = 'tender_document.pdf';

        $this->load->model('document_parser/Factsheet_model');
        if ($tfsid > 0) {
            $fs = $this->Factsheet_model->getFactSheetById($tfsid);
            if ($fs && !empty($fs['DocumentPath'])) {
                $docPath = $fs['DocumentPath'];
                $docName = !empty($fs['DocumentName']) ? $fs['DocumentName'] : basename($docPath);
            }
        } elseif ($bcid > 0) {
            $fs = $this->Factsheet_model->getFactSheetByBcid($bcid);
            if ($fs && !empty($fs['DocumentPath'])) {
                $docPath = $fs['DocumentPath'];
                $docName = !empty($fs['DocumentName']) ? $fs['DocumentName'] : basename($docPath);
            }
        }

        if (empty($docPath)) {
            show_404();
            return;
        }

        // Canonical path & Path traversal protection
        $fullPath = realpath(FCPATH . ltrim($docPath, '/\\'));
        $allowedBase = realpath(FCPATH . 'uploads');

        if (!$fullPath || !$allowedBase || strpos($fullPath, $allowedBase) !== 0 || !file_exists($fullPath)) {
            show_404();
            return;
        }

        $mime = 'application/pdf';
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $mime = 'application/pdf';
        }

        
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($docName) . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: private, max-age=3600, must-revalidate');
        header('Pragma: public');
        readfile($fullPath);
        exit;
    }

   
    public function get_field_evidence()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (!isset($Bid_Session) || empty($Bid_Session)) {
            $this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $tfsid = (int)$this->input->get('tfsid');
        $field = trim($this->input->get('field') ?? '');

        if ($tfsid <= 0) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['error' => 'Invalid Fact Sheet ID']));
            return;
        }

        $this->load->model('document_parser/Factsheet_model');
        $evidence = $this->Factsheet_model->getEvidenceByTfsid($tfsid);

        if (!empty($field)) {
            $matched = isset($evidence['by_field'][$field]) ? $evidence['by_field'][$field] : null;
            $allPassages = isset($evidence['all'][$field]) ? $evidence['all'][$field] : [];
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => true,
                'field'   => $field,
                'primary' => $matched,
                'all'     => $allPassages
            ]));
            return;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'  => true,
            'evidence' => $evidence
        ]));
    }

    /* =========================================================================
     * NEW BID NOTIFICATION SYSTEM METHODS
     * ========================================================================= */

    /**
     * Send professional email notification immediately when an existing Bid's status changes
     * Triggered by updateBidStatus() only after database update successfully completes.
     *
     * @param int $bcid Bid ID
     * @param int $newStatus Target status ID
     * @return bool
     */
    private function sendStatusChangeNotification($bcid, $newStatus)
    {
        try {
            $bid = $this->bid_model->getBidDetailsForNotification($bcid);
            if (!$bid) {
                log_message('error', "Status Change Notification: Bid #{$bcid} details not found.");
                return false;
            }

            require_once(APPPATH . 'libraries/InetMailer.php');
            $objs = new InetMailer();
            $mail = $objs->load();

            $mail->setFrom('info@inetcsc.com', 'I-NET Bid Management');
            $mail->addAddress('prasannakumari@inetcsc.com', 'Prasanna Kumari');
            $mail->addCC('reka@inetcsc.com', 'Reka');
            $mail->addCC('Mohamedfazil@inetcsc.com', 'Fazil');

            $mail->isHTML(true);
            $subject = 'Bid Status Update - ' . (!empty($bid['BidTitle']) ? $bid['BidTitle'] : 'Bid #' . $bcid);
            $mail->Subject = $subject;

            if (file_exists(FCPATH . 'images/inet_logo.png')) {
                $mail->AddEmbeddedImage(FCPATH . 'images/inet_logo.png', 'I-NET');
            }

            $userSession = $this->session->userdata('logged_in');
            $senderName = !empty($userSession['Name']) ? $userSession['Name'] : (!empty($userSession['BName']) ? $userSession['BName'] : 'Reka R.');

            $emailData = [
                'Bcid'          => $bcid,
                'bid'           => $bid,
                'currentStatus' => $bid['CurrentStatus'] ?? 'Updated',
                'senderName'    => $senderName,
                'portalUrl'     => $this->config->item('base_url') . 'bid/BidDetails'
            ];

            $mail->Body = $this->load->view('bid/BidStatusUpdateMail', $emailData, TRUE);
            $mail->send();

            // Record successful notification in log
            $this->bid_model->insertNotificationLog([
                'Bcid'             => (int)$bcid,
                'NotificationType' => 'STATUS_CHANGE',
                'RecipientEmail'   => 'prasannakumari@inetcsc.com (CC: reka@inetcsc.com, Mohamedfazil@inetcsc.com)',
                'Status'           => 'SENT',
                'SentAt'           => date('Y-m-d H:i:s'),
                'CurrentStatusId'  => (int)$newStatus,
                'OpeningDate'      => !empty($bid['OpeningDate']) ? $bid['OpeningDate'] : null,
                'Subject'          => $subject,
                'ErrorMessage'     => null
            ]);

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Bid Status Change Notification Failed: ' . $e->getMessage());

            // Record failure (do NOT mark as SENT)
            try {
                $this->bid_model->insertNotificationLog([
                    'Bcid'             => (int)$bcid,
                    'NotificationType' => 'STATUS_CHANGE',
                    'RecipientEmail'   => 'prasannakumari@inetcsc.com (CC: reka@inetcsc.com, Mohamedfazil@inetcsc.com)',
                    'Status'           => 'FAILED',
                    'SentAt'           => null,
                    'CurrentStatusId'  => (int)$newStatus,
                    'OpeningDate'      => isset($bid['OpeningDate']) ? $bid['OpeningDate'] : null,
                    'Subject'          => isset($subject) ? $subject : 'Bid Status Update',
                    'ErrorMessage'     => $e->getMessage()
                ]);
            } catch (\Throwable $logEx) {
                log_message('error', 'Failed to log notification error: ' . $logEx->getMessage());
            }

            return false;
        }
    }

  
    public function checkOpeningReminders($testBcid = null)
    {
        $isCli = (function_exists('is_cli') && is_cli()) || (PHP_SAPI === 'cli');
        $targetBcid = $testBcid ? (int)$testBcid : (int)$this->input->get('bcid');
        $force = (bool)$this->input->get('force'); 

        $results = [
            'status'         => true,
            'timestamp'      => date('Y-m-d H:i:s'),
            'mode'           => $isCli ? 'CLI' : 'HTTP',
            'interval_hours' => 24,
            'eligible_count' => 0,
            'sent_count'     => 0,
            'skipped_count'  => 0,
            'failed_count'   => 0,
            'details'        => []
        ];

        // Retrieve eligible bids
        if ($targetBcid > 0) {
            $bid = $this->bid_model->getBidDetailsForNotification($targetBcid);
            $eligibleBids = $bid ? [$bid] : [];
        } else {
            $eligibleBids = $this->bid_model->getBidsEligibleForOpeningReminder(24);
        }

        $results['eligible_count'] = count($eligibleBids);

        if (empty($eligibleBids)) {
            $results['message'] = 'No bids currently due for 24-hour opening date reminder.';
            $this->outputCheckerResponse($results, $isCli);
            return;
        }

        require_once(APPPATH . 'libraries/InetMailer.php');

        foreach ($eligibleBids as $bid) {
            $bcid = (int)$bid['Bcid'];
            $bidTitle = $bid['BidTitle'] ?? "Bid #{$bcid}";

           
            $wasSentIn24h = $this->bid_model->wasNotificationSentWithinInterval($bcid, 'OPENING_DATE_REMINDER', 24);

            if ($wasSentIn24h && !$force) {
                $results['skipped_count']++;
                $results['details'][] = [
                    'bcid'   => $bcid,
                    'title'  => $bidTitle,
                    'action' => 'SKIPPED',
                    'reason' => 'Notification already sent within the last 24 hours. Duplicate prevented.'
                ];
                continue;
            }

         
            $subject = 'Bid Opening Reminder - ' . $bidTitle;
            try {
                $objs = new InetMailer();
                $mail = $objs->load();

                $mail->setFrom('info@inetcsc.com', 'I-NET Bid Management');
                $mail->addAddress('prasannakumari@inetcsc.com', 'Prasanna Kumari');
                $mail->addCC('reka@inetcsc.com', 'Reka');
                $mail->addCC('Mohamedfazil@inetcsc.com', 'Fazil');

                $mail->isHTML(true);
                $mail->Subject = $subject;

                if (file_exists(FCPATH . 'images/inet_logo.png')) {
                    $mail->AddEmbeddedImage(FCPATH . 'images/inet_logo.png', 'I-NET');
                }

                $emailData = [
                    'Bcid'          => $bcid,
                    'bid'           => $bid,
                    'currentStatus' => $bid['CurrentStatus'] ?? 'Active',
                    'senderName'    => 'Reka R.',
                    'portalUrl'     => $this->config->item('base_url') . 'bid/BidDetails'
                ];

                $mail->Body = $this->load->view('bid/BidOpeningReminderMail', $emailData, TRUE);
                $mail->send();

                // Record successful notification in log
                $this->bid_model->insertNotificationLog([
                    'Bcid'             => $bcid,
                    'NotificationType' => 'OPENING_DATE_REMINDER',
                    'RecipientEmail'   => 'prasannakumari@inetcsc.com (CC: reka@inetcsc.com, Mohamedfazil@inetcsc.com)',
                    'Status'           => 'SENT',
                    'SentAt'           => date('Y-m-d H:i:s'),
                    'CurrentStatusId'  => (int)$bid['BidStatus'],
                    'OpeningDate'      => !empty($bid['OpeningDate']) ? $bid['OpeningDate'] : null,
                    'Subject'          => $subject,
                    'ErrorMessage'     => null
                ]);

                $results['sent_count']++;
                $results['details'][] = [
                    'bcid'        => $bcid,
                    'title'       => $bidTitle,
                    'action'      => 'SENT',
                    'openingDate' => $bid['OpeningDate'] ?? null
                ];

            } catch (\Throwable $e) {
                log_message('error', "Opening Reminder Failed for Bid #{$bcid}: " . $e->getMessage());

                // Record failure (do NOT mark as SENT)
                $this->bid_model->insertNotificationLog([
                    'Bcid'             => $bcid,
                    'NotificationType' => 'OPENING_DATE_REMINDER',
                    'RecipientEmail'   => 'prasannakumari@inetcsc.com (CC: reka@inetcsc.com, Mohamedfazil@inetcsc.com)',
                    'Status'           => 'FAILED',
                    'SentAt'           => null,
                    'CurrentStatusId'  => (int)$bid['BidStatus'],
                    'OpeningDate'      => !empty($bid['OpeningDate']) ? $bid['OpeningDate'] : null,
                    'Subject'          => $subject,
                    'ErrorMessage'     => $e->getMessage()
                ]);

                $results['failed_count']++;
                $results['details'][] = [
                    'bcid'   => $bcid,
                    'title'  => $bidTitle,
                    'action' => 'FAILED',
                    'error'  => $e->getMessage()
                ];
            }
        }

        $this->outputCheckerResponse($results, $isCli);
    }

    /**
     * Helper to render response for CLI and HTTP checker execution
     */
    private function outputCheckerResponse($results, $isCli)
    {
        if ($isCli) {
            echo "========================================================\n";
            echo "I-NET BID MANAGEMENT - 24-HOUR OPENING REMINDER CHECKER\n";
            echo "========================================================\n";
            echo "Timestamp: {$results['timestamp']}\n";
            echo "Eligible Bids: {$results['eligible_count']}\n";
            echo "Sent: {$results['sent_count']} | Skipped: {$results['skipped_count']} | Failed: {$results['failed_count']}\n";
            if (!empty($results['message'])) {
                echo "Notice: {$results['message']}\n";
            }
            if (!empty($results['details'])) {
                echo "--------------------------------------------------------\n";
                foreach ($results['details'] as $d) {
                    echo "[{$d['action']}] Bid #{$d['bcid']}: {$d['title']}";
                    if (!empty($d['reason'])) echo " - {$d['reason']}";
                    if (!empty($d['error'])) echo " - Error: {$d['error']}";
                    echo "\n";
                }
            }
            echo "========================================================\n";
        } else {
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($results, JSON_PRETTY_PRINT));
        }
    }

    public function notificationLogs($bcid = null)
    {
        $targetBcid = $bcid ? (int)$bcid : ($this->input->get('bcid') ? (int)$this->input->get('bcid') : null);
        $logs = $this->bid_model->getNotificationLogs($targetBcid, 100);

        $isCli = (function_exists('is_cli') && is_cli()) || (PHP_SAPI === 'cli');
        if ($isCli) {
            echo json_encode($logs, JSON_PRETTY_PRINT) . PHP_EOL;
        } else {
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['status' => true, 'total' => count($logs), 'logs' => $logs], JSON_PRETTY_PRINT));
        }
    }


    public function testStatusNotification($bcid = null)
    {
        $targetBcid = $bcid ? (int)$bcid : ($this->input->get('bcid') ? (int)$this->input->get('bcid') : 8);
        $res = $this->sendStatusChangeNotification($targetBcid, 3); // 3: PQ Evaluated

        $isCli = (function_exists('is_cli') && is_cli()) || (PHP_SAPI === 'cli');
        if ($isCli) {
            echo "Status Notification Test for Bid #{$targetBcid}: " . ($res ? "SENT SUCCESSFULLY" : "FAILED") . PHP_EOL;
        } else {
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['status' => $res, 'bcid' => $targetBcid, 'action' => 'STATUS_CHANGE_TEST']));
        }
    }

    
    public function timeline()
    {
        $Bid_Session = $this->session->userdata('logged_in');

        if (isset($Bid_Session) && !empty($Bid_Session))
        {
            $this->load->model('document_parser/Factsheet_model');
            $this->load->model('document_parser/Timeline_model');

            $fsid = $this->input->get_post('id') ? (int)$this->input->get_post('id') : (int)$this->input->get_post('fsid');
            $bcid = (int)$this->input->get_post('bcid');

            $factsheet = null;
            if ($fsid > 0) {
                $factsheet = $this->Factsheet_model->getFactSheetById($fsid);
                if (!empty($factsheet['Bcid'])) {
                    $factsheet = $this->Factsheet_model->generateOrSyncFactsheetForBid($factsheet['Bcid']);
                }
            } elseif ($bcid > 0) {
                $factsheet = $this->Factsheet_model->getFactSheetByBcid($bcid);
            }

            if (!$factsheet) {
                if ($this->input->get('ajax') || $this->input->is_ajax_request()) {
                    echo '<div class="alert alert-warning m-4 border-0 shadow-sm" style="border-radius:8px;">' .
                         '<div class="fw-bold"><i class="feather-alert-circle me-1"></i> No Fact Sheet Found</div>' .
                         '<div class="small mt-1">Please ensure a tender document is uploaded to analyze timeline milestones.</div>' .
                         '</div>';
                    return;
                }
                $this->session->set_flashdata('err', 'No Fact Sheet found for this tender.');
                redirect($this->config->item('base_url') . 'bid/masterdashboard');
                return;
            }

            $tfsId = (int)$factsheet['Tfsid'];
            $timelineData = $this->Timeline_model->getTimeline($tfsId);
            $corrigendaList = $this->Timeline_model->getCorrigenda($tfsId);

            $data = [
                'factsheet'      => $factsheet,
                'timelineData'   => $timelineData,
                'corrigendaList' => $corrigendaList,
                'tfs_id'         => $tfsId,
                'bcid'           => $factsheet['Bcid'] ?? $bcid,
                'isAjax'         => $this->input->get('ajax') || $this->input->is_ajax_request()
            ];

            if ($this->input->get('ajax') || $this->input->is_ajax_request()) {
                $this->load->view('document_parser/TimelineIntelligenceView', $data);
                return;
            }

            $data['page_title'] = 'Timeline Intelligence - ' . (!empty($factsheet['TenderRefNo']) ? $factsheet['TenderRefNo'] : 'Tender');
            $currentUrl = strtolower(uri_string());
            $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
            $this->template->write_view('content', 'document_parser/TimelineIntelligenceView', $data);
            $this->template->render();
        } else {
            if ($this->input->get('ajax') || $this->input->is_ajax_request()) {
                echo '<div class="alert alert-danger m-4">Session expired. Please log in again.</div>';
                return;
            }
            $this->session->set_flashdata('err', 'Invalid Session. Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

    /**
     * Fetch timeline data as JSON for Fact Sheet or dynamic widgets
     */
    public function ajaxGetTimeline()
    {
        $tfsId = (int)$this->input->get_post('tfs_id');
        $bcid  = (int)$this->input->get_post('bcid');

        $this->load->model('document_parser/Timeline_model');
        $this->load->model('document_parser/Factsheet_model');

        if (!$tfsId && $bcid > 0) {
            $fs = $this->Factsheet_model->getFactSheetByBcid($bcid);
            if (!empty($fs['Tfsid'])) {
                $tfsId = (int)$fs['Tfsid'];
            }
        }

        if (!$tfsId) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Missing Factsheet ID']));
        }

        $timeline = $this->Timeline_model->getTimeline($tfsId);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => !empty($timeline), 'timeline' => $timeline, 'tfs_id' => $tfsId]));
    }



    /**
     * Schedule a deadline reminder for an authorized user
     */
    public function saveMilestoneReminder()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (empty($Bid_Session)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Session expired. Please log in again.']));
        }

        $tfsId        = (int)$this->input->post('tfs_id');
        $type         = trim($this->input->post('milestone_type'));
        $remType      = trim($this->input->post('reminder_type') ?: '3_days');
        $scheduledAt  = trim($this->input->post('scheduled_at'));
        $notes        = trim($this->input->post('notes'));
        $emailAddress = trim($this->input->post('email_address') ?: ($Bid_Session['EmailId'] ?? ''));

        if (!$tfsId || empty($type)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Missing tender or milestone parameters.']));
        }

        $this->load->model('document_parser/Timeline_model');
        $remId = $this->Timeline_model->saveReminder([
            'tfs_id'          => $tfsId,
            'milestone_type'  => $type,
            'user_id'         => $Bid_Session['UserId'] ?? 1,
            'reminder_type'   => $remType,
            'scheduled_at'    => $scheduledAt,
            'recipient_email' => $emailAddress,
            'notes'           => $notes,
            'delivery_status' => 'pending'
        ]);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'status'      => true,
                'reminder_id' => $remId,
                'message'     => 'Reminder scheduled successfully.'
            ]));
    }

    /**
     * Cancel an existing reminder
     */
    public function cancelMilestoneReminder()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (empty($Bid_Session)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Session expired.']));
        }

        $reminderId = (int)$this->input->post('reminder_id');
        $this->load->model('document_parser/Timeline_model');
        $res = $this->Timeline_model->cancelReminder($reminderId, $Bid_Session['UserId'] ?? null);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => (bool)$res, 'message' => 'Reminder cancelled.']));
    }

    /**
     * Cron endpoint to process and dispatch due timeline reminders
     */
    public function processTimelineRemindersCron()
    {
        $this->load->model('document_parser/Timeline_model');
        $this->load->model('bid/Bid_model');
        $due = $this->Timeline_model->getPendingRemindersDue();
        $sentCount = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($due as $rem) {
            $remId = (int)($rem['Ttrid'] ?? $rem['id']);
            $emailSent = false;
            $subject = 'Tender Milestone Reminder: ' . ucfirst(str_replace('_', ' ', $rem['milestone_type'])) . ' for ' . ($rem['TenderNo'] ?: 'Tender #' . $rem['tfs_id']);

            // Send email if recipient email exists
            if (!empty($rem['recipient_email'])) {
                @$this->email->clear();
                @$this->email->from('noreply@ibid-system.local', 'iBid Tender Timeline Intelligence');
                @$this->email->to($rem['recipient_email']);
                @$this->email->subject($subject);
                @$this->email->message("This is a reminder for {$rem['milestone_type']}.\nScheduled at: {$rem['scheduled_at']}\nNotes: {$rem['notes']}");
                $emailSent = (bool)@$this->email->send();
            }

            $deliveryStatus = $emailSent ? 'Sent' : (!empty($rem['recipient_email']) ? 'Failed' : 'Sent');

            // Update exact table TenderTimelineReminders
            if ($this->Bid_model->tableExists('TenderTimelineReminders')) {
                $this->db->where('Ttrid', $remId)->update('TenderTimelineReminders', [
                    'DeliveryStatus' => $deliveryStatus,
                    'DeliveredAt'    => $now
                ]);
            }

            // Record notification dispatch into exact table BidNotificationLog
            if (!empty($rem['bcid']) && $this->Bid_model->tableExists('BidNotificationLog')) {
                $this->bid_model->insertNotificationLog([
                    'Bcid'             => (int)$rem['bcid'],
                    'NotificationType' => 'TIMELINE_REMINDER',
                    'RecipientEmail'   => $rem['recipient_email'] ?? '',
                    'Subject'          => $subject,
                    'Status'           => ($deliveryStatus === 'Sent') ? 'SENT' : 'FAILED',
                    'SentAt'           => ($deliveryStatus === 'Sent') ? $now : null,
                    'CurrentStatusId'  => 0,
                    'OpeningDate'      => null,
                    'ErrorMessage'     => ($deliveryStatus === 'Failed') ? 'Email transport delivery failed' : null
                ]);
            }

            if ($deliveryStatus === 'Sent') {
                $sentCount++;
            }
        }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'status'     => true,
                'dispatched' => $sentCount,
                'timestamp'  => $now
            ]));
    }
} //MAIN
