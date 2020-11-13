<?php

namespace aw2\elastic_email;

//elastic_email

\aw2_library::add_service('elastic_email','Elastic Mail Library',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('elastic_email.send','Send a Mail using Elastic Mail. Use elastic_email.send',['namespace'=>__NAMESPACE__]);
function send($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract(\aw2_library::shortcode_atts( array(
		'array' => null
	), $atts, 'elastic_email_send' ) );


	$ref=aw2_library::get($array);
	$apiKey = $ref['key'];
		
	$log_messages=aw2_library::get("site_settings.log_messages");
		if($log_messages=="on"){
			global $wpdb;
			// check if log already created or not
			$subject=esc_sql($ref['subject']);
			$message=esc_sql($ref['message']);

			
			if (isset($ref['id'])) {
						$wpdb->query("UPDATE `message_log` SET `message_type`='Mail',`message_provider`='Elastic',`message_to`='".$ref['to']."',`message_cc`='".@$ref['cc']."', `message_bcc`='".@$ref['bcc']."', `message_from`='".@$ref['from']."', `message_reply_to`='".@$ref['reply_to']."', `subject`='".$subject."', `message`='".$message."' where ID='".$ref['id']."'");
			}else{	
				
				$wpdb->query("INSERT INTO `message_log` (`message_type`, `message_provider`, `message_to`, `message_cc`, `message_bcc`, `message_from`, `message_reply_to`, `subject`, `message`) VALUES ('Mail', 'Elastic EMail', '".$ref['to']."', '".@$ref['cc']."', '".@$ref['bcc']."', '".@$ref['from']."', '".@$ref['reply_to']."', '".$subject."', '".$message."')");	
			}		
		}
	
	$url = 'https://api.elasticemail.com/v2/email/send';

	try{
			$post = array('from' => $ref['from'],
			'fromName' => $ref['from_name'],
			'apikey' => $apiKey,
			'subject' => $ref['subject'],
			'to' => $ref['to'],
			'bodyHtml' => $ref['message'],
			'bodyText' => $ref['message'],
			'isTransactional' => false);
			
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $post,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLOPT_SSL_VERIFYPEER => false
			));
			
			$result=curl_exec ($ch);
			curl_close ($ch);
			
			$return_value= $result;	
	}
	catch(Exception $ex){
		$return_value= $ex->getMessage();
	}
	

	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}