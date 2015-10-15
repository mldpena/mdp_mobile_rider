<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Waybill extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('waybill_model');
		$this->load->library('sqlfunction');
	}

	public function index()
	{	
		$this->sqlfunction->checkUserExist();

		if (isset($_POST['data'])) 
		{
			$this->ajaxRequest();
			exit();
		}

		$data['page'] 	= 'dispatch_waybill_list';
		$data['script'] = 'dispatch_waybill_list_js.php';

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
		$fnc 		= $postData['request_action'];

		switch ($fnc) {
			
			case 'get_dispatch_waybill_list':
				$response = $this->waybill_model->getDispatchWaybillList();
				break;
			
			case 'encode_waybill_status':
				$response = $this->waybill_model->saveWaybillNewStatus($postData);
				break;

			default:
				$response['error'] = 'Invalid waybill status!';
				break;
		}

		echo json_encode($response);
	}
}
