<?php
namespace aw2;

\aw2_library::add_service('aw2.wp_mail','Send a Mail using Wordpress',['namespace'=>__NAMESPACE__]);


function wp_mail($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract(\aw2_library::shortcode_atts( array(
		'part' => 'start'
	), $atts, 'aw_wp_mail' ) );

	$ref=&\aw2_library::get_array_ref('mail_builder');
	if($part=='run'){
		
		// Log messages in table
		$log_messages=\aw2_library::get("site_settings.log_messages");
		if($log_messages=="on"){
			global $wpdb;
			$message=esc_sql($ref['message']);
			if(!isset($ref['object_type']))$ref['object_type']='';
			if(!isset($ref['object_id']))$ref['object_id']='';
			$wpdb->query("INSERT INTO `message_log` (`message_type`, `message_provider`, `message_to`, `message_cc`, `message_bcc`, `message_from`, `message_reply_to`, `subject`, `message`, `object_type`, `object_id`) VALUES ('Mail', 'wp_mail', '".$ref['to']."', '".$ref['cc']."', '".$ref['bcc']."', '".$ref['from']."', '".$ref['reply_to']."', '".$ref['subject']."', '".$message ."', '".$ref['object_type']."', '".$ref['object_id']."')");			
		}
		//$ref['to']="hardcoded email id";	
		wp_mail( $ref['to'], $ref['subject'], $ref['message'], $ref['headers'], $ref['attachment'] );
		$ref=&\aw2_library::get_array_ref();
		unset($ref['mail_builder']);
		return;
	}


	if($part=='start'){
		$args=\aw2_library::get_clean_args($content,$atts);
		$ref=array();
		$ref['to']=$args['to'];	
		$ref['to']="gopi@amiworks.com";
		$ref['subject']=$args['subject'];	
		
		@$ref['cc']=$args['cc'];
		@$ref['bcc']=$args['bcc'];
		@$ref['from']=$args['from'];
		@$ref['reply_to']=$args['reply_to'];
		
		$ref['headers']=array();
		$ref['headers'][] = 'Content-Type: text/html'; 
		if(array_key_exists('cc',$args))$ref['headers'][] ='Cc: ' . $args['cc'] ;
		if(array_key_exists('bcc',$args))$ref['headers'][] ='Bcc: ' . $args['bcc'] ;
		if(array_key_exists('from',$args))$ref['headers'][] ='From: ' . $args['from'] ;
		if(array_key_exists('reply_to',$args))$ref['headers'][] ='Reply-To: ' . $args['reply_to'] ;
		
		$ref['attachment']=array();
		if(array_key_exists('attachment',$args)){
			
			if(!is_array($args['attachment'])){
				$args['attachment'] = explode(',',$args['attachment']);
			}
			$ref['attachment']	=$args['attachment'] ;
		}
	}	

	if($part=='message'){
		$ref['message']=\aw2_library::parse_shortcode($content);	
	}
	return ;
}


\aw2_library::add_service('aw2.send_grid_mail','Send a Mail using Send Grid',['namespace'=>__NAMESPACE__]);

function send_grid_mail($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract(\aw2_library::shortcode_atts( array(
		'array' => null
	), $atts, 'aw2_send_grid_mail' ) );

	require_once(ABSPATH . 'wp-content/plugins/awesome-studio/monoframe/sendgrid/sendgrid-php.php');

	$ref=\aw2_library::get($array);	
	
	
	if(!isset($ref['cc']))$ref['cc']='';
	if(!isset($ref['bcc']))$ref['bcc']='';
	if(!isset($ref['reply_to']))$ref['reply_to']='';
	$ref['to']="gopi@amiworks.com";
	$log_messages=\aw2_library::get("site_settings.log_messages");
		if($log_messages=="on"){
			global $wpdb;
			$message=esc_sql($ref['message']);
			if(!isset($ref['object_type']))$ref['object_type']='';
			if(!isset($ref['object_id']))$ref['object_id']='';			
			$wpdb->query("INSERT INTO `message_log` (`message_type`, `message_provider`, `message_to`, `message_cc`, `message_bcc`, `message_from`, `message_reply_to`, `subject`, `message`, `object_type`, `object_id`) VALUES ('Mail', 'SendGrid', '".$ref['to']."', '".$ref['cc']."', '".$ref['bcc']."', '".$ref['from']."', '".$ref['reply_to']."', '".$ref['subject']."', '".$message."', '".$ref['object_type']."', '".$ref['object_id']."')");			
		}
	
	
	$from = new \SendGrid\Email($ref['from_name'], $ref['from']);
	$to = new \SendGrid\Email(null, $ref['to']);
	$content = new \SendGrid\Content("text/html", $ref['message']);
	$mail = new \SendGrid\Mail($from, $ref['subject'], $to, $content);

	$apiKey = $ref['key'];
	$sg = new \SendGrid($apiKey);

	
	$response = $sg->client->mail()->send()->post($mail);
	$return_value= $response->statusCode();

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}
