<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Waybill_Model extends CI_Model {
	private $_currentRiderId = 0;
	private $_currentBranchId = 0;
	private $_currentDate = '';
	private $_currentDateTime = '';

	public function __construct() {
		$this->load->library('encrypt');
		$this->load->library('myfunction');

		$this->_currentBranchId = $this->encrypt->decode($this->myfunction->getCookie('rider_branch'));
		$this->_currentRiderId = (int)$this->encrypt->decode($this->myfunction->getCookie('rider_temp'));
		$this->_currentDate = date('Y-m-d');
		$this->_currentDateTime = date('Y-m-d H:i:s');

		parent::__construct();
	}

	public function getDispatchWaybillList()
	{
		$response['data'] = [];

		$getDispatchWaybillTodayQuery = "SELECT
											W.`waybillnumber`,
											W.`status`,
											W.`consigneename` AS 'consignee',
											W.`contactnumber` AS 'contact',
											W.`consigneeaddress` AS 'address',
											W.`id` AS 'waybill_id',
											W.`description`,
											COALESCE(D.`remark`,'') AS 'remarks'
										FROM 
											dispatchhead AS H
										LEFT JOIN 
											dispatchdetail AS D ON D.`headid` = H.`id`
										LEFT JOIN
											waybill AS W ON W.`id` = D.`waybillid`
										WHERE 
											H.`show` = 1 AND 
											H.`frombranchid` = ? AND
											H.`riderid` = ? AND
											H.`tobranchid` = 0 AND
											DATE(H.`datetimedeliver`) = ?
										ORDER BY W.`status` ASC";

		$resultSet = $this->db->query($getDispatchWaybillTodayQuery, [$this->_currentBranchId, $this->_currentRiderId, $this->_currentDate]);

		if ($resultSet->num_rows() > 0) 
		{	
			$i = 0;
			foreach ($resultSet->result() as $row) 
			{
				$response['data'][$i]['waybill'] 	= $row->waybillnumber;
				$response['data'][$i]['status'] 	= $this->myfunction->getColorStatus($row->status);
				$response['data'][$i]['remarks'] 	= $row->remarks;
				$response['data'][$i]['consignee']	= $row->consignee;
				$response['data'][$i]['contact']	= $row->contact;
				$response['data'][$i]['address']	= $row->address;
				$response['data'][$i]['waybill_id']	= $row->waybill_id;
				$response['data'][$i]['description'] = $row->description;
				$i++;
			}
		}

		$resultSet->free_result();

		return $response;
	}

	public function saveWaybillNewStatus($param)
	{
		extract($param);

		$response['error'] = '';

		$addWaybillLogsQuery = "INSERT INTO `waybillmanuallog`
								(`waybillid`,
								`status`,
								`remark`,
								`show`,
								`datecreated`,
								`lastmodifieddate`,
								`userid`,
								`lastmodifiedby`,
								`datetime`)
								VALUES
								(?,?,?,?,?,?,?,?,?)";	

		$returnResult = $this->sqlfunction->exeQuery($addWaybillLogsQuery, [
																				$waybill_id, 
																				$status, 
																				$remarks, 1, 
																				$this->_currentDateTime, 
																				$this->_currentDateTime, 
																				$this->_currentRiderId, 
																				$this->_currentRiderId, 
																				$this->_currentDateTime
																			]);

		$this->sqlfunction->logUserAction($waybill_id, 'Updated waybill status using mobile system.');

		if ($returnResult['error'])
			$response['error'] = 'Unable to save data!';

		return $response;
	}
}
