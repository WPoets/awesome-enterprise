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
        'log' => null
    ), $atts, 'aw2_wpmail' ) );
    
    // if email is null, return
    if(is_null($email)) return;

    if(!isset($email['to']['value']))$email['to']['value']='';
    if(!isset($email['subject']['value']))$email['subject']['value']='';
    if(!isset($email['message']['value']))$email['message']['value']='';
	if(!isset($email['headers']['value']))$email['headers']['value']='';
    if(!isset($email['attachment']['value']))$email['attachment']['value']='';

    // Log data in db
    notification_log('mail', 'wpmail', $email, $log);

	wp_mail( 
        $email['to']['value'], 
        $email['subject']['value'], 
        $email['message']['value'], 
        $email['headers']['value'], 
        $email['attachment']['value'] 
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
        'log' => null
    ), $atts, 'aw2_sendgrid' ) );
    
    // if email is null, return
    if(is_null($email)) return;
    
    // Checking for values and setting them if not present.
	if(!isset($email['from']['value']))$email['from']['value']='';
	if(!isset($email['to']['value']))$email['to']['value']='';
    if(!isset($email['message']['value']))$email['message']['value']='';
    if(!isset($email['subject']['value']))$email['subject']['value']='';

    $apiKey = $email['apikey']['value'];

    if(empty($apiKey) || strlen($apiKey) === 0){
        $return_value=aw2_library::post_actions('all','No api key is not provided, check you settings for default api key!',$atts);
        return $return_value;
    }


    // Log data in db
    notification_log('mail', 'sendgrid', $email, $log);

    $sendgrid = new \SendGrid($apiKey);
    
	$from = new SendGrid\Email(null, $email['from']['value']);
	$to = new SendGrid\Email(null, $email['to']['value']);
	$content = new SendGrid\Content("text/html", $email['message']['value']);
	$mail = new SendGrid\Mail($from, $email['subject']['value'], $to, $content);

    $response = $sendgrid->client->mail()->send()->post($mail);
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
        'log' => null
    ), $atts, 'aw2_kookoo' ) );

    // if sms is null, return
    if(is_null($sms)) return;

    if(!isset($sms['to']['value']))$sms['to']['value']='';
    if(!isset($sms['message']['value']))$sms['message']['value']='';
    if(!isset($sms['apikey']['value']))$sms['apikey']['value']='';

    // Log data in db
    notification_log('sms', 'kookoo', $sms, $log);

    // api base url
    $url = 'http://www.kookoo.in/outbound/outbound_sms.php';

    $apiKey = $sms['apikey']['value'];

    if(empty($apiKey) || strlen($apiKey) === 0){
        $return_value=aw2_library::post_actions('all','No api key is not provided, check you settings for default api key!',$atts);
        return $return_value;
    }

    // parameter to send in sms
    $param = array(
        'api_key' => $apiKey,
        'phone_no' => '0'.$sms['to']['value'], 
        'message' => $sms['message']['value']
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

function notification_log($type, $provider, $data, $log){

    // when $log is empty or the value is not equals to 'yes', return.
    if(is_null($log) || $log != 'yes') return;

    if(!isset($data['to']['value']))$data['to']['value']='';
	if(!isset($data['from']['value']))$data['from']['value']='';
    if(!isset($data['cc']['value']))$data['cc']['value']='';
    if(!isset($data['bcc']['value']))$data['bcc']['value']='';
    if(!isset($data['reply_to']['value']))$data['reply_to']['value']='';
    if(!isset($data['subject']['value']))$data['subject']['value']='';
    if(!isset($data['message']['value']))$data['message']['value']='';
    if(!isset($data['object_type']['value']))$data['object_type']['value']='';
    if(!isset($data['object_id']['value']))$data['object_id']['value']='';

    global $wpdb;
    $wpdb->query("INSERT INTO `notification_log` (`notification_type`, `notification_provider`, `notification_to`, `notification_from`, `cc`, `bcc`, `reply_to`, `subject`, `message`, `object_type`, `object_id`) VALUES ('".$type."', '".$provider."', '".$data['to']['value']."', '".$data['from']['value']."', '".$data['cc']['value']."', '".$data['bcc']['value']."', '".$data['reply_to']['value']."', '".$data['subject']['value']."', '".$data['message']['value']."', '".$data['object_type']['value']."', '".$data['object_id']['value']."')");			
}
