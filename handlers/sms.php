<?php
aw2_library::add_shortcode('aw2','sms', 'awesome2_sms','Send a SMS from your site.');

function awesome2_sms($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
		'main' => null,
		'api_key' => null,
		'phone_no' => null,
		'senderid' => null,
		'object_type' => null,
		'object_id' => null,
	), $atts, 'aw2_sms' ) );
	
	$phone_no="9890827666";
	if(is_null($main) || is_null($phone_no)){
		return;
	}

	$log_messages=aw2_library::get("site_settings.log_messages");
	switch($main){
		case 'kookoo':
			$url = 'http://www.kookoo.in/outbound/outbound_sms.php';
			$param = array('api_key' => aw2_library::get("site_settings.sms-api-key"),
				'phone_no' => '0'.$phone_no, 
				'message' => aw2_library::parse_shortcode($content)
			);
		// log messages		
		$message=aw2_library::parse_shortcode($content);
		if($log_messages=="on"){
			global $wpdb;
			$wpdb->query("INSERT INTO `message_log` (`message_type`, `message_provider`, `message_to`, `message_cc`, `message_bcc`, `message_from`, `message_reply_to`, `subject`, `message`, `object_type`, `object_id`) VALUES ('SMS', 'kookoo', '".$phone_no."', '', '', '', '', '', '".$message."', '".$object_type."', '".$object_id."')");			
		}			
			
			$url = $url . "?" . http_build_query($param, '&');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$result = curl_exec($ch);
			curl_close($ch);
			$result = simplexml_load_string($result);
			$return_value= $result->status;
		break;
		case 'ozonetel':
			$url = "http://smscloud.ozonetel.com/GatewayAPI/rest";
	
				$param = array(
					'send_to' =>'0'.$phone_no,  
					'msg' => aw2_library::parse_shortcode($content), 
					'msg_type' => 'text',
					'loginid' => aw2_library::get("site_settings.ozontel-loginid"),
					'auth_scheme' => 'plain',
					'password' => aw2_library::get("site_settings.ozontel-password"),
					'v' => '1.1',
					'format' => 'text',
					'method' => 'sendMessage',
					'mask' => aw2_library::get("site_settings.ozontel-sender-mask"),
				);
		// log messages		 
		$message=aw2_library::parse_shortcode($content);
		if($log_messages=="on"){
			global $wpdb;
			$wpdb->query("INSERT INTO `message_log` (`message_type`, `message_provider`, `message_to`, `message_cc`, `message_bcc`, `message_from`, `message_reply_to`, `subject`, `message`, `object_type`, `object_id`) VALUES ('SMS', 'ozonetel', '".$phone_no."', '', '', '', '', '', '".$message."', '".$object_type."', '".$object_id."')");			
		}	
				 
				$url = $url . "?" . http_build_query($param, '&');
				 
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				$result = curl_exec($ch);
				curl_close($ch);
				$return_value= $result;
		break;
		case 'exotel':
			
		break;
	}
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}