<?php
aw2_library::add_shortcode('aw2','push', 'awesome2_push','Send a Push notification from your site.');

function awesome2_push($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
		'main' => null,
		'pem_path' => aw2_library::get("site_settings.apple-pem-path"),
		'passphrase' => aw2_library::get("site_settings.apple-pem-passphrase"),
		'google_api_key' => aw2_library::get("site_settings.google-push-api-key"),
		'device_tokens' => null,
		'topic' => null
	), $atts, 'aw2.push' ) );
	
	if(is_null($main) || (is_null($device_tokens)&& is_null($topic))){
		return;
	}

	$apn=new awesome2_push_notification();
	$apn->push_message = aw2_library::parse_shortcode($content);
	$apn->push_device_tokens = $device_tokens;
	$apn->topic = $topic;

	switch($main){
		case 'android':
			if(empty($google_api_key))
				return;
				
			$apn->google_api_key = $google_api_key;
			//$result = $apn->android();
			$return_value= $apn->android();
		break;
		case 'ios':
			if(!file_exists($pem_path))
				return;
				$apn->pem_path = $pem_path;
				$apn->passphrase = $passphrase;
			//$result = $apn->ios();
			$return_value= $apn->ios();
			
		break;
		case 'windows':
			
		break;
		case 'web':
			
		break;
	}
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

class awesome2_push_notification{
	
	public $push_message;
	public $push_device_tokens;
	public $topic;
	public $google_api_key;
	public $pem_path;
	public $passphrase;
	
	public function android(){

		$url = 'https://fcm.googleapis.com/fcm/send'; //FCM URL
		if(empty($this->topic)){
			$device_ids=explode(',',$this->push_device_tokens);

			$fields = array(
						'registration_ids' => $device_ids,
						'notification' => array("body"=> $this->push_message),
						'data'=>array("data"=> $this->push_message)
					);
		}
		else{
			$fields = array(
						'to' => '/topics/'.$this->topic,
						'notification' => array("body"=> $this->push_message),
						'data'=>array("data"=> $this->push_message)
					);
		}
		
		util::var_dump($fields);
		
		$headers = array(
					'Authorization: key=' . $this->google_api_key,
					'Content-Type: application/json'
				);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch); 
		$return_value= 'success';
		
		if ($result === FALSE) {
			aw2_library::set_error('Android Curl failed: ' . curl_error($ch));
			$return_value= 'fail';
		}
		curl_close($ch);
		
		return $return_value;
	}

	public function ios(){
	
		$device_ids=explode(',',$this->push_device_tokens);
		
		$arrContextOptions=array(
		    "ssl"=>array(
		        "verify_peer"=>false,
		        "verify_peer_name"=>false,
		    ),
		);
		$ctx = stream_context_create($arrContextOptions);
		stream_context_set_option($ctx, 'ssl', 'local_cert', $this->pem_path);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
		
		$fp = stream_socket_client(
			'ssl://gateway.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		
		if (!$fp){
			
			aw2_library::set_error("Failed to connect: $err $errstr");
			return 'fail';
		}
			
		
			
		$body['aps'] = array(
					'alert' => $this->push_message,
					'sound' => 'default'
					);
		
		$payload = json_encode($body);
		$result ='';
		
		$device_count= count($device_ids);
		
		for($i=0; $i<$device_count; $i++){
			
			
			$msg = chr(0) . pack('n', 32) . pack('H*',$device_ids[$i]) . pack('n', strlen($payload)) . $payload;
		
			$a=$msg;
			$fpp=$fp;
			$result = fwrite($fpp, $a, strlen($a));
		}
		fclose($fp);
		$return_value= 'success';
		if (!$result){
			aw2_library::set_error('ios notification failed: Message not delivered ');
			$return_value= 'fail';
			
		}
		return $return_value;
		
	}
}