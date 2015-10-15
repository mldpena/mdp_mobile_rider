<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_Myfunction{

	public function __construct()
	{

	}

	public function encryptDataMD5($string)
	{
		$string = base64_encode($string);
		$string.= SALT;
		return md5(trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SALT, $string, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))));
	}

	public function relocate($url)
	{
		header("location:".base_url().$url);
	}

	public function setCookie($cookie_name,$cookie_value)
	{
		setcookie($cookie_name, $cookie_value, time() + (86400),'/');
	}

	public function getCookie($cookie_name)
	{
		return $_COOKIE[$cookie_name];
	}

	public function deleteCookie($cookie_name)
	{
		setcookie($cookie_name,'',time() - 3600,'/');
	}

	public function deleteSessionCookies()
	{
		$this->deleteCookie('rider_username');
		$this->deleteCookie('rider_fullname');
		$this->deleteCookie('rider_temp');
		$this->deleteCookie('rider_branch');
	}

	public function getColorStatus($stat)
	{
		$status = "";
		switch ($stat) {
			case 1:
				$status ='<div class="stat stat-received">Received</div>';
				break;
			
			case 2:
				$status ='<div class="stat stat-segregated">Segregated</div>';
				break;

			case 3:
				$status ='<div class="stat stat-ondelivery">On Transit</div>';
				break;

			case 4:
				$status ='<div class="stat stat-receivedbyconsignee">Received By Representative</div>';
				break;

			case 5:
				$status ='<div class="stat stat-withproblem">Problematic</div>';
				break;

			case 6:
				$status ='<div class="stat stat-onhold">On Hold</div>';
				break;

			case 7:
				$status ='<div class="stat stat-pulloutbyshipper">Cancelled</div>';
				break;

			case 8:
				$status ='<div class="stat stat-pullout">Pulled Out</div>';
				break;

			case 9:
				$status ='<div class="stat stat-rts">Return to Shipper</div>';
				break;

			case 10:
				$status ='<div class="stat stat-ondelivery">Forwarding</div>';		
				break;

			case 11:
				$status ='<div class="stat stat-receivedbyconsignee">Forwarded</div>';		
				break;
				
			case 12:
				$status ='<div class="stat stat-withproblem">Consignee Unknown</div>';
				break;

			case 13:
				$status ='<div class="stat stat-withproblem">No one to Receive</div>';
				break;

			case 14:
				$status ='<div class="stat stat-withproblem">Incorrect / Incomplete Details</div>';
				break;
		}

		return $status;
	}

	public function fillOption($data,$isempty = '', $id = 0)
	{
		if ($isempty == 1) {
			echo "<option value='0' selected>&nbsp;</option>";
		}

		for ($i=0; $i < count($data); $i++) { 
			$selected = ($id != 0 && $id == $data[$i]['id']) ? 'selected' : '';
			echo "<option value='".$data[$i]['id']."' $selected>".$data[$i]['name']."</option>";
		}
	}

	public function checkPermission($permissionNeeded,$curretPermission)
	{
		$isExist = false;

		for ($i=0; $i < count($permissionNeeded); $i++) { 
			if (in_array($permissionNeeded[$i],$curretPermission)) {
				$isExist = true;
				break;
			}
		}

		return $isExist;
	}

	public function getPagePermission($page,$curretPermission)
	{
		$data = array();
		switch ($page) {
			case 'branchlist':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_BRANCH),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_BRANCH),$curretPermission);
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_BRANCH),$curretPermission);
				break;
			
			case 'userlist':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_USER),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_USER),$curretPermission);
				break;

			case 'userdetail':
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_USER),$curretPermission);
				break;

			case 'shipperlist':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_SHIPPER),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_SHIPPER),$curretPermission);
				break;

			case 'shipperdetail':
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_SHIPPER),$curretPermission);
				break;


			case 'segregationlist':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_SEGREGATION_SETTINGS),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_SEGREGATION_SETTINGS),$curretPermission);
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_SEGREGATION_SETTINGS),$curretPermission);
				$data['allow_to_view'] 		= $this->checkPermission(array(ADMIN,VIEW_SEGREGATION_DETAIL_SETTINGS),$curretPermission);
				break;

			case 'segregationdetail':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_SEGREGATION_DETAIL_SETTINGS),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_SEGREGATION_DETAIL_SETTINGS),$curretPermission);
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_SEGREGATION_DETAIL_SETTINGS),$curretPermission);
				break;

			case 'emailsetting':
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_EMAIL_SETTINGS),$curretPermission);
				break;

			case 'smssetting':
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_SMS_SETTINGS),$curretPermission);
				break;

			case 'rateadjust':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_RATE_ADJUST),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_RATE_ADJUST),$curretPermission);
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_RATE_ADJUST),$curretPermission);
				break;

			case 'municipallist':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_MUNICIPAL_SETTINGS),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_MUNICIPAL_SETTINGS),$curretPermission);
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_MUNICIPAL_SETTINGS),$curretPermission);
				break;

			case 'newwaybill':
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_WAYBILL),$curretPermission);
				$data['allow_to_pullout'] 	= $this->checkPermission(array(ADMIN,PULLOUT_WAYBILL),$curretPermission);
				break;

			case 'waybilllist':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_WAYBILL),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_WAYBILL),$curretPermission);
				$data['allow_to_import'] 	= $this->checkPermission(array(ADMIN,IMPORT_EXCEL),$curretPermission);
				$data['allow_to_export'] 	= $this->checkPermission(array(ADMIN,EXPORT_WAYBILL_EXCEL),$curretPermission);
				$data['allow_to_pullout'] 	= $this->checkPermission(array(ADMIN,PULLOUT_WAYBILL),$curretPermission);
				break;

			case 'waybilllogs':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_WAYBILL_LOGS),$curretPermission);
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_WAYBILL_LOGS),$curretPermission);
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_WAYBILL_LOGS),$curretPermission);
				break;

			case 'waybillscan':
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_SEGREGATION),$curretPermission);
				$data['allow_to_auto_segregate'] = $this->checkPermission(array(ADMIN,AUTO_SEGREGATE),$curretPermission);
				break;

			case 'segregationcountlist':
				$data['allow_to_add'] 		= $this->checkPermission(array(ADMIN,ADD_SEGREGATION),$curretPermission);
				break;

			case 'segregationcountdetail':
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_SEGREGATION),$curretPermission);
				$data['allow_to_auto_dispatch'] = $this->checkPermission(array(ADMIN,AUTO_DISPATCH),$curretPermission);
				break;

			case 'dispatchlist':
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_DISPATCH),$curretPermission);
				$data['allow_to_print_track'] 	= $this->checkPermission(array(ADMIN,PRINT_TRACKING_SHEET),$curretPermission);
				$data['allow_to_print_manifest'] 	= $this->checkPermission(array(ADMIN,PRINT_MANIFEST),$curretPermission);
				$data['allow_to_print_run'] 	= $this->checkPermission(array(ADMIN,PRINT_RUN_SHEET),$curretPermission);
				break;

			case 'dispatch':
				$data['allow_to_delete'] 	= $this->checkPermission(array(ADMIN,DELETE_DISPATCH),$curretPermission);
				$data['allow_to_edit'] 		= $this->checkPermission(array(ADMIN,EDIT_DISPATCH),$curretPermission);
				$data['allow_to_print_track'] 	= $this->checkPermission(array(ADMIN,PRINT_TRACKING_SHEET),$curretPermission);
				$data['allow_to_print_manifest'] 	= $this->checkPermission(array(ADMIN,PRINT_MANIFEST),$curretPermission);
				$data['allow_to_print_run'] = $this->checkPermission(array(ADMIN,PRINT_RUN_SHEET),$curretPermission);
				$data['allow_to_send'] 		= $this->checkPermission(array(ADMIN,SEND_SMS),$curretPermission);
				break;

			case 'billinglist':
				$data['allow_to_export_excel'] 	= $this->checkPermission(array(ADMIN,EXPORT_BILLING_EXCEL),$curretPermission);
				$data['allow_to_export_pdf'] 	= $this->checkPermission(array(ADMIN,EXPORT_BILLING_PDF),$curretPermission);
				$data['allow_to_mail'] 		= $this->checkPermission(array(ADMIN,MAIL_BILLING),$curretPermission);
				break;

			case 'saclist':
				$data['allow_to_export_pdf'] 	= $this->checkPermission(array(ADMIN,EXPORT_BILLING_PDF),$curretPermission);
				$data['allow_to_mail'] 		= $this->checkPermission(array(ADMIN,MAIL_BILLING),$curretPermission);
				$data['allow_to_addon'] 	= $this->checkPermission(array(ADMIN,SOC_ADD_ON),$curretPermission);
				break;

			case 'receive':
				$data['allow_to_receive'] = $this->checkPermission(array(ADMIN,EDIT_RECEIVE),$curretPermission);
				break;

			default:
				# code...
				break;
		}

		return $data;
	}

}
