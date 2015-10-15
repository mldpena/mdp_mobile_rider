<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('login_model');
		$this->load->library('sqlfunction');
	}

	public function index()
	{	
		$isLogout = $this->uri->segment(2);

		if ($isLogout == "logout")
			$this->myfunction->deleteSessionCookies();

		if (isset($_COOKIE['rider_username']) && isset($_COOKIE['rider_fullname']) && isset($_COOKIE['rider_temp'])) 
		{
			$check = $this->sqlfunction->checkUserExist();
			if ($check == 'success')
				$this->myfunction->relocate('waybill');
		}

		if (isset($_POST['data'])) {
			$this->ajaxRequest();
			exit();
		}

		$data['page'] 	= 'login';
		$data['script'] = 'login_js.php';

		$this->load->view('master', $data);
	}

	public function _remap()
	{
        $param_offset = 1;
        $method = 'index';
	    $params = array_slice($this->uri->rsegment_array(), $param_offset);

	    call_user_func_array(array($this, $method), $params);
	} 

	private function ajaxRequest()
	{
		$response 	= array();
		$postData 	= array();
		$fnc 		= '';

		$postData 	= json_decode($_POST['data'],true);
		$fnc 		= $postData['fnc'];

		switch ($fnc) 
		{
			case 'check_login':
				$response = $this->login_model->checkUserCredential($postData);
				break;

			case 'final_verification':
				$response = $this->login_model->finalVerifyUser($postData);
				break;

			case 'get_user_branch_list':
				$response = $this->sqlfunction->getUserBranchList($postData['userid']);
				break;
		}

		echo json_encode($response);
	}
}
