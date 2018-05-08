<?php

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

