<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_Model extends CI_Model {

	public function __construct() {
		$this->load->library('encrypt');
		$this->load->library('myfunction');
		parent::__construct();
	}

	public function checkUserCredential($param)
	{
		extract($param);

		$data['error'] = '';
		
		$pass = $this->myfunction->encryptDataMD5($pass);

		$query = " SELECT 
						`id`, `username`, `password`, `fullname`
						FROM `user` 
						WHERE 
							`show` = 1 AND 
							`username` = ? AND 
							`password` = ? AND 
							`user_type` = 2";

		$result = $this->db->query($query, array($user, $pass));

		if ($result->num_rows() != 1)
			$data['error'] = "Invalid Username / Password!";
		else
		{
			$row = $result->row();
			$data['userid'] = $this->encrypt->encode($row->id);
		}

		$result->free_result();

		return $data;
	}

	public function finalVerifyUser($param)
	{
		extract($param);
		$data['error'] = '';
		$pass = $this->myfunction->encryptDataMD5($pass);

		$query = " SELECT 
						`id`, `username`, `password`, `fullname`
						FROM `user` 
						WHERE 
							`show` = 1 AND 
							`username` = ? AND 
							`password` = ? AND 
							`user_type` = 2";

		$result = $this->db->query($query, array($user, $pass));

		if ($result->num_rows() != 1)
			$data['error'] = "Invalid Username / Password!";
		else
		{
			$row = $result->row();

			$this->myfunction->setCookie('rider_username',$this->encrypt->encode($row->username));
			$this->myfunction->setCookie('rider_fullname',$this->encrypt->encode($row->fullname));
			$this->myfunction->setCookie('rider_temp',$this->encrypt->encode($row->id));
			$this->myfunction->setCookie('rider_branch',$this->encrypt->encode($branchid));
			
		}

		$result->free_result();

		return $data;
	}
}
