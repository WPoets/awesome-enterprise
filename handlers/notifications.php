<?php
namespace aw2\notify;

\aw2_library::add_service('notify.wpmail','Send wp mail',['namespace'=>__NAMESPACE__]);

function wpmail($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

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
    if(!isset($email['attachments']))$email['attachments']=array();

    // Log data in db
    \notification_log('mail', 'wpmail', $email, $log, $notification_object_type, $notification_object_id);

	wp_mail( 
        $email['to']['email_id'], 
        $email['subject'], 
        $email['message'], 
        $email['headers'], 
        $email['attachments'] 
    );

    $return_value = "success";
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('notify.sendgrid','Send Sendgrid mail',['namespace'=>__NAMESPACE__]);

function sendgrid($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

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
        $return_value=\aw2_library::post_actions('all','No api key is not provided, check your settings for default api key!',$atts);
        return $return_value;
    }


    $sendgrid = new \SendGrid($apiKey);
    
	$from = new \SendGrid\Email(null, $email['from']['email_id']);
	$to = new \SendGrid\Email(null, $email['to']['email_id']);
    $content = new \SendGrid\Content("text/html", $email['message']);
	$mail = new \SendGrid\Mail($from, $email['subject'], $to, $content);
    
    // Works on only when the attachments are present
    if(isset($email['attachments']['file'])){
        
        //storing file array in variable
        $file = $email['attachments']['file'];

        //looping through the file content
        for($i=0; $i<sizeof($file); $i++){
            $name = $file[$i]['name'];
            $path = $file[$i]['path'];
            if(!empty($path)){
                // new instance of attachment
                $attachment = new \SendGrid\Attachment();
                $file_encoded = base64_encode(file_get_contents($path));
                $attachment->setContent($file_encoded);
                $attachment->setDisposition("attachment");
                $attachment->setFilename($name);
                $mail->addAttachment($attachment);
            }
        }
    }

    $response = $sendgrid->client->mail()->send()->post($mail);

    //get headers from the response->headers();
    $header = $response->headers();

    //getting the message id from the header response
    $messageId = \getBetween($header,"X-Message-Id:","Access-Control-Allow-Origin");

    //setting up tracking array
    $tracking['tracking_id'] = trim($messageId) ;
    $tracking['tracking_status'] = 'sent_to_provider';
    $tracking['tracking_stage'] = 'sent_to_provider';

    // Log data in db
    \notification_log('mail', 'sendgrid', $email, $log, $notification_object_type, $notification_object_id, $tracking);

    $return_value = $response->statusCode();

    if($return_value == 202){
        $return_value = "success";
    }

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('notify.kookoo','Send Kookoo SMS',['namespace'=>__NAMESPACE__]);

function kookoo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

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
    \notification_log('sms', 'kookoo', $sms, $log, $notification_object_type, $notification_object_id);

    // api base url
    $url = 'http://www.kookoo.in/outbound/outbound_sms.php';

    $apiKey = $sms['provider']['key'];

    if(empty($apiKey) || strlen($apiKey) === 0){
        $return_value=\aw2_library::post_actions('all','No api key is not provided, check you settings for default api key!',$atts);
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

    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



