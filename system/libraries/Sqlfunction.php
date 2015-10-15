<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_Sqlfunction{
	private $_currentUserId = 0;
	private $_currentBranchId = 0;
	private $_currentDate = '';

	public function __construct() {
		$ci =& get_instance();
		$ci->load->library('myfunction');
		$ci->load->library('encrypt');

		$ci->_currentBranchId 	= isset($_COOKIE['branch']) ? $ci->encrypt->decode($ci->myfunction->getCookie('branch')) : 0;
		$ci->_currentUserId 	= isset($_COOKIE['temp']) ? $ci->encrypt->decode($ci->myfunction->getCookie('temp')) : 0;
		$ci->_currentDate 		= date('Y-m-d H:i:s');
	}

	public function exeQuery($query,$dataArray)
	{
		$ci =& get_instance();
		$data = array();
		$data['error'] = false;
		$data['id'] = 0;
		$data['errmsg'] = '';

		if (is_array($query)) {
			$temp = "";
			for ($i=0; $i < count($query); $i++) { 
				$temp .= $query.";";
			}
			$query = $temp;	
		}

		for ($i=0; $i < 5; $i++) { 
			$ci->db->trans_start();
			$ci->db->query($query,$dataArray);
			$data['id'] = $ci->db->insert_id();
			$ci->db->trans_complete();
			if ($ci->db->trans_status() === FALSE){
				$data['id'] = 0;
				continue;
			}
			else
			{
				break;
			}
		}

		$err = $ci->db->_error_message();
		if (!empty($err)) {
			$data['error'] = true;
			$data['id'] = 0;
			$data['errmsg'] = $err;
		}

		return $data;		
	}

	function execTransaction($queryArray, $dataArray = array())
	{
		$ci =& get_instance();
		$data = array();
		$data['error'] = false;
		$data['id'] = 0;
		$data['errmsg'] = '';

		$ci->db->trans_start();

		for ($i=0; $i < count($queryArray); $i++) { 
			for ($x=0; $x < 5; $x++) { 
				if (count($dataArray) == 0) {
					$ci->db->query($queryArray[$i]);
				}
				else{
					$ci->db->query($queryArray[$i],$dataArray[$i]);
				}

				if ($ci->db->_error_number() == 0){
					break;
				}
			}

			$data['id'] = $ci->encrypt->encode($ci->db->insert_id());
		}

		$ci->db->trans_complete();

		$err = $ci->db->_error_message();

		if (!empty($err)) {
			$data['error'] = true;
			$data['id'] = 0;
			$data['errmsg'] = $err;
		}

		return $data;
	}

	public function getBranchList($option = "")
	{
		$ci =& get_instance();
		$query = "SELECT `id`, CONCAT(`code`,' - ',`name`) AS 'name' FROM branch WHERE `show` = 1";

		$result = $ci->db->query($query);

		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->id;
			$data[$i]['name'] = $row->name;
			$i++;
		}

		$result->free_result();

		return $data;
	}

	public function getShipperList($branchid)
	{
		$ci =& get_instance();
		$branchid = $ci->encrypt->decode($branchid);

		$query = "SELECT `id`, CONCAT(`code`,' - ',`fullname`) AS 'name' FROM shipper WHERE `show` = 1 AND `branchid` = ?";

		$result = $ci->db->query($query,$branchid);

		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->id;
			$data[$i]['name'] = $row->name;
			$i++;
		}

		$result->free_result();

		return $data;
	}

	public function getRegionList()
	{
		$ci =& get_instance();
		$query = "SELECT CONCAT(`id`,',',`shortname`,',',`island`) AS 'id', `name` FROM loc_region";

		$result = $ci->db->query($query);

		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->id;
			$data[$i]['name'] = $row->name;
			$i++;
		}

		$result->free_result();

		return $data;
	}

	public function getCityList($param)
	{
		$ci =& get_instance();
		$query = "SELECT `id`, `name` FROM loc_city WHERE `region_id` = ?";

		$result = $ci->db->query($query,$param['id']);

		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->id;
			$data[$i]['name'] = $row->name;
			$i++;
		}

		$result->free_result();

		return $data;
	}

	public function getMunicipalList($param)
	{
		$ci =& get_instance();
		$query = "SELECT `id`, `name` FROM loc_municipal WHERE `cityid` = ?";

		$result = $ci->db->query($query,$param['id']);

		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->id;
			$data[$i]['name'] = $row->name;
			$i++;
		}

		$result->free_result();

		return $data;
	}

	public function checkUserExist()
	{
		$ci =& get_instance();

		if (!isset($_COOKIE['rider_username']) || !isset($_COOKIE['rider_fullname']) || !isset($_COOKIE['rider_temp']) || !isset($_COOKIE['rider_branch'])) {
			$ci->myfunction->deleteCookie('rider_username');
			$ci->myfunction->deleteCookie('rider_branch');
			$ci->myfunction->deleteCookie('rider_fullname');
			$ci->myfunction->deleteCookie('rider_temp');
			$ci->myfunction->relocate('login');
			exit();
		}
		
		$username = $ci->encrypt->decode($_COOKIE['rider_username']);
		$fullname = $ci->encrypt->decode($_COOKIE['rider_fullname']);
		$temp = $ci->encrypt->decode($_COOKIE['rider_temp']);

		$query = "SELECT `id`, `username`, `password`, `fullname`
					FROM `user` AS U 
					WHERE U.`show` = 1 AND U.`username` = ? AND U.`fullname` = ? AND U.`id` = ?";

		$result = $ci->db->query($query, array($username,$fullname,$temp));

		if ($result->num_rows() != 1) {
			$ci->myfunction->deleteCookie('rider_username');
			$ci->myfunction->deleteCookie('rider_fullname');
			$ci->myfunction->deleteCookie('rider_temp');
			$ci->myfunction->deleteCookie('rider_branch');
			$ci->myfunction->relocate('login');
		}


		$result->free_result();

		return 'success';
	}

	public function logWayBillAction($id,$message,$status,$datetime = "",$ref = 0)
	{
		$ci =& get_instance();
		$date = date("Y-m-d h:i:s");
		$datetime = empty($datetime) ? $date : $datetime;
		$query = "INSERT INTO `waybillautolog`
					(`waybillid`,
					`message`,
					`datetime`,
					`status`,
					`dispatchrefno`,
					`datecreated`)
					VALUES
					(?,?,?,?,?,'$date')";

		$returnData = $this->exeQuery($query,array($id,$message,$datetime,$status,$ref));

		if (!$returnData['error']) {
			$data['error'] = 'Unable to save data!';
		}

	}

	public function getUserBranchList($userid)
	{
		$ci =& get_instance();
		$data = array();
		$id = $ci->encrypt->decode($userid);

		$query = "SELECT DISTINCT(UB.`branchid`) AS 'branchid', 
					CONCAT(B.`code`,' - ',B.`name`) AS 'branchname'
					FROM userbranchpermission AS UB 
					LEFT JOIN branch AS B ON B.`id` = UB.`branchid` 
					WHERE UB.`userid` = ?";

		$result = $ci->db->query($query,$id);
		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->branchid;
			$data[$i]['name'] = $row->branchname;
			$i++;
		}

		$result->free_result();

		return $data;
	}

	public function getBranchRider($branchid)
	{
		$data = array();
		$ci =& get_instance();
		$branchid = $ci->encrypt->decode($branchid);

		$query = "SELECT U.`id`, CONCAT(U.`code`,' - ',U.`fullname`) AS 'name'
					FROM user AS U
					LEFT JOIN userbranchpermission AS UB ON U.`id` = UB.`userid` 
					WHERE UB.`branchid` = ? AND U.`user_type` = 2
					GROUP BY U.`id`";

		$result = $ci->db->query($query,$branchid);
		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->id;
			$data[$i]['name'] = $row->name;
			$i++;
		}

		$result->free_result();

		return $data;

	}

	public function getSegregationAreaByBranch($branchid)
	{
		$ci =& get_instance();
		$branchid = $ci->encrypt->decode($branchid);

		$query = "SELECT `id`, 
					CONCAT(`code`,'-',`name`) AS 'name'
					FROM segregationarea 
					WHERE `branchid` = ? AND `show` = 1";

		$result = $ci->db->query($query,$branchid);

		$i = 0;

		foreach ($result->result() as $row) {
			$data[$i]['id'] = $row->id;
			$data[$i]['name'] = $row->name;
			$i++;
		}

		$result->free_result();

		return $data;

	}

	public function setNextReferenceNo($tablename, $field)
	{
		$query = array();

		array_push($query,"SET @invoiceno_d = 0;");
		array_push($query,"SELECT coalesce(MAX(`$field`+0),10000000) into @invoiceno_d FROM `$tablename` 
                             WHERE `show` = 1 FOR UPDATE;");
		$invoiceno_variable = "IF(@invoiceno_d = 0,'10000001',@invoiceno_d+1)";
		array_push($query,"INSERT INTO `$tablename`($field,`show`,`datetimedeliver`) VALUES($invoiceno_variable,1,NOW())");

		$returnData = $this->execTransaction($query);

		return $returnData;
	}

	public function getBranchName($branchid)
	{
		$ci =& get_instance();
		$branchid = $ci->encrypt->decode($branchid);
		$query = "SELECT CONCAT(`code`,' - ',`name`) AS 'name' FROM `branch` WHERE `id` = ?";
		
		$result = $ci->db->query($query,$branchid);
		$row = $result->row();
		$name = $row->name;

		$result->free_result();

		return $name;
	}

	public function getSegregationName($segid)
	{
		$ci =& get_instance();
		$query = "SELECT CONCAT(`code`,' - ',`name`) AS 'name' FROM `segregationarea` WHERE `id` = ?";
		
		$result = $ci->db->query($query,$segid);
		$row = $result->row();
		$name = $row->name;

		$result->free_result();

		return $name;
	}

	public function getShipperEmail($shipperid)
	{
		$ci =& get_instance();
		$query 	= "SELECT COALESCE(`email`,'') AS 'email' FROM shipper WHERE `id` = ? AND `show` = 1";
		
		$result = $ci->db->query($query,$shipperid);
		$row 	= $result->row();
		$email 	= $row->email;
		
		$result->free_result();
		return $email;
	}

	public function logUserAction($waybillId, $actionDescription)
	{
		$ci =& get_instance();

		$logUserActionQuery = "INSERT INTO `audit_trail`
								(`userid`,
								`waybillid`,
								`branchid`,
								`message`,
								`actiondate`)
								VALUES
								(?,?,?,?,?);";

		$returnData = $this->exeQuery($logUserActionQuery,	[
																$ci->_currentUserId, 
																$waybillId,
																$ci->_currentBranchId, 
																$actionDescription, 
																$ci->_currentDate
															]);

		if (!$returnData['error']) 
			$data['error'] = 'Unable to save data!';
	}
}
