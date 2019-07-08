<?php
namespace aw2\exotel;
define('CALLURL',"https://loantap4:611b65c731aab866d823260cd56074029deff5f2@twilix.exotel.in/v1/Accounts/loantap4/Calls");
define('CALLID',"08030456096");

\aw2_library::add_service('exotel','Exotel Calls',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('exotel.agent_customer_call','Call Number',['func'=>'call','namespace'=>__NAMESPACE__]);

function call($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'from'  	  => "",
		'to'		  => ""
        ), $atts) );
    
	//**Prepend 0 to the number. Required by Exotel**//
    $from = '0'.$from;
	$to = '0'.$to;

    //**Build the exotel api url to be called**//
	$user = \aw2_library::get('settings.exotel-user');
	$token = \aw2_library::get('settings.exotel-token');
	$call_url = "https://$user:$token@twilix.exotel.in/v1/Accounts/$user/Calls/connect.json";
	
	//**Build the data array**//
	$data = [
        'From' =>  $from, 
        'To' => $to, 
        'CallerId' => \aw2_library::get('settings.exotel-callerid'),
        'CallType' => 'trans'
    ];
	
    //**Build query sting from array**//
	$data = http_build_query($data);	
	
	//**POST the data**//
	$result = wp_remote_post( $call_url, array(
		'headers'     => array(
			'content-type' => 'application/x-www-form-urlencoded',
			'cache-control' => 'no-cache'
		),
		'body'        => $data
		)
	);

	if($result['response']['code']==200 && $result['response']['message']=="OK"){
		$response = $result['body'];	
	}
	
	$return_value=\aw2_library::post_actions('all',$response,$atts);
	return $return_value;
}

\aw2_library::add_service('exotel.get','Get communication details',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
			'sid'    =>""
        ), $atts) );
    
	//**Build the exotel api url to be called**//
	$user = \aw2_library::get('settings.exotel-user');
	$token = \aw2_library::get('settings.exotel-token');
	$call_url = "https://$user:$token@twilix.exotel.in/v1/Accounts/$user/Calls/$sid.json?details=true";
	
	//**GET the data**//
	$result = wp_remote_get($call_url);
	
	$response = '';
	
	if($result['response']['code']==200 && $result['response']['message']=="OK"){
		$response = $result['body'];
	}
	
	$return_value=\aw2_library::post_actions('all',$response,$atts);
	return $return_value;
}