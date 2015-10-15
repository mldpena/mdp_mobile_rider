<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_SMS{

	protected $outboundEndpoint = '';
	protected $smsKey 			= '';
	protected $smsFrom 			= '';
	protected $sms 				= '';
	protected $smsNumber 		= '';
	protected $consigneeName 	= '';

	public function __construct()
	{
		$xml = simplexml_load_file("application/config/sms_config.xml") or die("Error: Cannot create object");
		$this->smsFrom 			= (string)$xml->sms_from;
		$this->sms 				= (string)$xml->sms_message;
		$this->outboundEndpoint = (string)$xml->sms_api_url;
		$this->smsKey 			= (string)$xml->sms_api_key;
	}

	public function setNumber($number)
	{
		$this->smsNumber = $number;
	}

	public function setConsignee($name)
	{
		$this->consigneeName = $name;
	}

	private function getNumber()
	{
		return $this->smsNumber;
	}

	private function getConsignee()
	{
		return $this->consigneeName;
	}

	public function sendMessage()
	{
		
		$okFlag = false;
		$this->sms = str_replace('<consigneename>',$this->getConsignee(),$this->sms);
		//while ($okFlag == false) {
			$smsData = [
			    'api' 	=> $this->smsKey, 
			    'number' => $this->smsNumber, 
			    'message' => $this->sms, 
			    'from' => $this->smsFrom, 
			];

			$smsParameter = http_build_query($smsData);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->outboundEndpoint);
			curl_setopt($ch,CURLOPT_POST, count($smsData));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $smsParameter);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = json_decode(curl_exec($ch), true);
			curl_close($ch);

			if(strtolower($output['status']) === "success") { 
				$okFlag = true;
			}
		//}
	}
	
}
