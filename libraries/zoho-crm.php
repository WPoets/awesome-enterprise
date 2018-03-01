<?php

class awesome_zoho_crm{
	private static $api_url = 'https://crm.zoho.com/crm/private/xml/';
	private static $api_toke_url = 'https://accounts.zoho.com/apiauthtoken/nb/create';
	private $auth_token;
	
	function __construct($auth_token) {
		$this->auth_token = $auth_token;
	}
	
	//function generate_authcode( $object_id, $cmb_id, $updated_fields, $cmb){
	static function generate_authcode($cmb, $object_id){

		if(	empty($cmb->data_to_save['zoho-crm-authcode']) && 
			(!empty($cmb->data_to_save['zoho-crm-email']) && !empty($cmb->data_to_save['zoho-crm-password']))){
				$authcode=self::get_token($cmb->data_to_save['zoho-crm-email'], $cmb->data_to_save['zoho-crm-password']);
				if($authcode !== false){
					$cmb->data_to_save['zoho-crm-authcode'] = $authcode;
					$cmb->data_to_save['zoho-crm-password'] = '';
				}
			}		
	}
	
	static function get_token($email, $password) {
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'content' => http_build_query(array(
					'SCOPE' => 'ZohoCRM/crmapi',
					'EMAIL_ID' => $email,
					'PASSWORD' => $password,
					'DISPLAY_NAME' => 'Awesome Studio - '.substr($_SERVER['HTTP_HOST'], 0, 25)
				)),
				'header' => 'Content-Type: application/x-www-form-urlencoded'
			)
		));
		$result = file_get_contents(self::$api_toke_url, false, $context);

		if ($result === false){
			aw2_library::set_error($result);
			return false;
		}
			
		foreach(explode("\n", $result) as $line) {
			$line = trim($line);
			if (strlen($line) > 10 && substr($line, 0, 10) == 'AUTHTOKEN=')
				return substr($line, 10);
		}
		return false;
	}

	public function request($module, $method, $params=array()) {
		$params['authtoken'] = $this->auth_token;
		$params['scope'] = 'crmapi';
		$url = self::$api_url.$module.'/'.$method;
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'content' => http_build_query($params),
				'header' => 'Content-Type: application/x-www-form-urlencoded'
			)
		));
		
		$result = file_get_contents($url, false, $context);
		if ($result === false){
			aw2_library::set_error($result);
			return false;
		}
			
		$result = simplexml_load_string($result);
		if ($result === false)
			return false;
		return $result;
	}
	
	public function fields_to_xml($module, $rows) {
		$xml = new SimpleXMLElement("<$module />");
		foreach ($rows as $i => $fields) {
			$row = $xml->addChild('row');
			$row->addAttribute('no', $i + 1);
			
			foreach ($fields as $fieldName => $fieldValue) {
				$field = $row->addChild('FL', str_replace('&', '&amp;', $fieldValue));
				$field->addAttribute('val', $fieldName);
			}
		}
		return $xml->asXML();
	}
	
}



//add_action('cmb2_save_options-page_fields','awesome_zoho::generate_authcode',10,4);
add_action('cmb2_options-page_process_fields_zoho-crm-settings','awesome_zoho_crm::generate_authcode',10,2);