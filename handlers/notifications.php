<?php

/**
 * Shortcodes definitions for current library
 */
aw2_library::add_shortcode('wpmail','send', 'awesome2_wpmail','Send a Mail using wordpress mail');
aw2_library::add_shortcode('sendgrid','send', 'awesome2_sendgrid','Send a Mail using Send Grid');
aw2_library::add_shortcode('kookoo','send', 'awesome2_kookoo','Send a SMS using kookoo');

/**
 * Function to send mail using wordpress mail
 */
function awesome2_wpmail($atts,$content=null,$shortcode){
    if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract( shortcode_atts( array(
        'email' => null,
        'log' => null,
        'notification_object_type' => null,    
        'notification_object_id' => null
    ), $atts, 'aw2_wpmail' ) );
    
    // if email is null, return
    if(is_null($email)) return;

    if(!isset($email['to']['email_id']))$email['to']['email_id']='';
    if(!isset($email['subject']))$email['subject']='';
    if(!isset($email['message']))$email['message']='';
	if(!isset($email['headers']))$email['headers']='';
    if(!isset($email['attachment']))$email['attachment']='';

    // Log data in db
    notification_log('mail', 'wpmail', $email, $log, $notification_object_type, $notification_object_id);

	wp_mail( 
        $email['to']['email_id'], 
        $email['subject'], 
        $email['message'], 
        $email['headers'], 
        $email['attachment'] 
    );

    $return_value = "success";
    $return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

/**
 * Function to send mail using sendgrid
 */
function awesome2_sendgrid($atts,$content=null,$shortcode){
    if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    //including SENDGRID library
	require_once(ABSPATH . 'wp-content/plugins/awesome-studio/libraries/sendgrid/sendgrid-php.php');
        
    extract( shortcode_atts( array(
		'email' => null,
        'log' => null,
        'notification_object_type' => null,    
        'notification_object_id' => null
    ), $atts, 'aw2_sendgrid' ) );
    
    // if email is null, return
    if(is_null($email)) return;
    
    // Checking for values and setting them if not present.
	if(!isset($email['from']['email_id']))$email['from']['email_id']='';
	if(!isset($email['to']['email_id']))$email['to']['email_id']='';
    if(!isset($email['message']))$email['message']='';
    if(!isset($email['subject']))$email['subject']='';
		
		//provider.apiKey or settings.sendgrid_apiKey
    $apiKey = $email['provider']['key'];

    if(empty($apiKey) || strlen($apiKey) === 0){
        $return_value=aw2_library::post_actions('all','No api key is not provided, check you settings for default api key!',$atts);
        return $return_value;
    }

    $sendgrid = new \SendGrid($apiKey);
    
	$from = new SendGrid\Email(null, $email['from']['email_id']);
	$to = new SendGrid\Email(null, $email['to']['email_id']);
	$content = new SendGrid\Content("text/html", $email['message']);
	$mail = new SendGrid\Mail($from, $email['subject'], $to, $content);

    $response = $sendgrid->client->mail()->send()->post($mail);

    //get headers from the response->headers();
    $header = $response->headers();

    //getting the message id from the header response
    $messageId = getBetween($header,"X-Message-Id:","Access-Control-Allow-Origin");

    //setting up tracking array
    $tracking['tracking_id'] = trim($messageId) ;
    $tracking['tracking_status'] = 'sent_to_provider';
    $tracking['tracking_stage'] = 'sent_to_provider';

    // Log data in db
    notification_log('mail', 'sendgrid', $email, $log, $notification_object_type, $notification_object_id, $tracking);

    $return_value = $response->statusCode();

    if($return_value == 202){
        $return_value = "success";
    }

	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

/**
 * Function to send sms using, KOOKOO
 */
function awesome2_kookoo($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract( shortcode_atts( array(
		'sms' => null,
        'log' => null,
        'notification_object_type' => null,    
        'notification_object_id' => null
    ), $atts, 'aw2_kookoo' ) );

    // if sms is null, return
    if(is_null($sms)) return;

    if(!isset($sms['to']['mobile_number']))$sms['to']['mobile_number']='';
    if(!isset($sms['message']))$sms['message']='';
    if(!isset($sms['provider']['key']))$sms['provider']['key']='';

    // Log data in db
    notification_log('sms', 'kookoo', $sms, $log, $notification_object_type, $notification_object_id);

    // api base url
    $url = 'http://www.kookoo.in/outbound/outbound_sms.php';

    $apiKey = $sms['provider']['key'];

    if(empty($apiKey) || strlen($apiKey) === 0){
        $return_value=aw2_library::post_actions('all','No api key is not provided, check you settings for default api key!',$atts);
        return $return_value;
    }

    // parameter to send in sms
    $param = array(
        'api_key' => $apiKey,
        'phone_no' => '0'.$sms['to']['mobile_number'], 
        'message' => $sms['message']
    );

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

    $return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function notification_log($type, $provider, $data, $log, $notification_object_type, $notification_object_id, $tracking = null){

    // when $log is empty or the value is not equals to 'yes', return.
    if(is_null($log) || $log != 'yes') return;

    if(!isset($data['to']['email_id']))$data['to']['email_id']='';

    $to = $data['to']['email_id'];

    if(isset($data['to']['mobile_number'])){
        $to = $data['to']['mobile_number'];
    }

    //data for emails
	if(!isset($data['from']['email_id']))$data['from']['email_id']='';
    if(!isset($data['cc']['email_id']))$data['cc']['email_id']='';
    if(!isset($data['bcc']['email_id']))$data['bcc']['email_id']='';
    if(!isset($data['reply_to']['email_id']))$data['reply_to']['email_id']='';
    if(!isset($data['subject']))$data['subject']='';
    if(!isset($data['message']))$data['message']='';
    if(!isset($data['object_type']))$data['object_type']='';
    if(!isset($data['object_id']))$data['object_id']='';

    //tracking array
    if(!isset($tracking['tracking_id']))$tracking['tracking_id']='';
    if(!isset($tracking['tracking_status']))$tracking['tracking_status']='';
    if(!isset($tracking['tracking_stage']))$tracking['tracking_stage']='';

    $subject = str_replace("'","\"",$data['subject']);
    $message = str_replace("'","\"",$data['message']);

    global $wpdb;
    $wpdb->query("INSERT INTO `notification_log` (`notification_type`, `notification_provider`, `notification_to`, `notification_from`, `cc`, `bcc`, `reply_to`, `subject`, `message`, `object_type`, `object_id`, `tracking_id`, `tracking_status`, `tracking_stage`) VALUES ('".$type."', '".$provider."', '".$to."', '".$data['from']['email_id']."', '".$data['cc']['email_id']."', '".$data['bcc']['email_id']."', '".$data['reply_to']['email_id']."', '".$subject."', '".$message."', '".$notification_object_type."', '".$notification_object_id."', '".$tracking['tracking_id']."', '".$tracking['tracking_status']."', '".$tracking['tracking_stage']."')");			
}

function getBetween($content,$start,$end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}
