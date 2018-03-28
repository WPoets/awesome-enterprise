<?php

aw2_library::add_library('session_ticket','Virtual Session Handler');


function aw2_session_ticket_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'time' =>60,
	'nonce'=>'no',
	'otp_value'=>null,
	'user'=>null,
	'app'=>null
	), $atts) );

	$validation=array();	
	
	//generate the ticket
	if($otp_value){
		$ticket=mt_rand(100000,999999);
		$validation['otp_value']=$otp_value;
	}
	else{
		$ticket=uniqid();
	}
	
	$redis = aw2_library::redis_connect(12);

	if($redis->exists($ticket))die ('Ticket already Exists');
	
	
	//do i need a nonce
	if($nonce==='yes'){
		$validation['nonce']=wp_create_nonce($ticket);
	}
	// if user validation
	if(!is_null($user)){
		if($user==='current_user'){
			$current_user = wp_get_current_user();
			$validation['user']=$current_user->user_login;
		}	
		else{
			$validation['user']=$user;
		}
	}	

	// if app validation
	if(!is_null($app)){
		if($app==='current_app')
			$validation['app']=aw2_library::get('app.slug');
		else{
			$validation['app']=$app;
		}
	}	
	$json=json_encode($validation);
	$redis->hSet($ticket,'validation',$json);
	$redis->setTimeout($ticket, $time*60);
	$return_value=$ticket;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


function aw2_session_ticket_check($ticket,$otp_value,$validation){
	//otp validation
	if(isset($validation['otp_value'])){
		if($otp_value!==$validation['otp_value'])
			return false;
	}
	
	// nonce validation
	if(isset($validation['nonce'])){
		$nonce=wp_create_nonce($ticket);
		if($nonce!== $validation['nonce'])return false;
	}
	
	// user validation
	if(isset($validation['user'])){
		$current_user = wp_get_current_user();
		if($current_user->user_login!== $validation['user'])return false;
	}
	
	// app validation
	if(isset($validation['app'])){
		$app = aw2_library::get('app.slug');
		if($app!== $validation['app'])return false;
	}

	return true;	
	
}

function aw2_session_ticket_validate($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	'otp_value'=>null
	), $atts) );
	$ticket=$main;
	$redis = aw2_library::redis_connect(12);
	
	$return_value=true;
	
	if(!$redis->exists($ticket))$return_value=false;

	// validate
	if($return_value===true and !$redis->hExists($ticket,'validation'))$return_value=false;	

	$json=$redis->hGet($ticket,'validation');
	$validation=json_decode($json,true);

	if($return_value===true)
		$return_value=aw2_session_ticket_check($ticket,$otp_value,$validation);	

	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


/*
	app =
	collection =
	module=
*/

function aw2_session_ticket_set_activity($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'app'=>null,
	'collection'=>null,
	'module'=>null,
	), $atts) );

	if(!$main)return 'Main must be set';

	$ticket=$main;
	$redis = aw2_library::redis_connect(12);
	
	$ticket_activity=array();
	if($app)$ticket_activity['app']=$app;
	if($collection)$ticket_activity['collection']=$collection;
	if($module)$ticket_activity['module']=$module;
	
	$json=json_encode($ticket_activity);
	$redis->hSet($ticket,'ticket_activity',$json);
	return;
}


function aw2_session_ticket_set($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'field'=>'_error',
	'value'=>null,
	), $atts) );

	if(!$main)return 'Main must be set';

	$ticket=$main;
	$redis = aw2_library::redis_connect(12);
	
	$redis->hSet($ticket,$field,$value);
	return;
}

function aw2_session_ticket_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'otp_value'=>null
	), $atts) );

	if(!$main)return 'Main must be set';		
	
	$ticket=$main;
	$redis = aw2_library::redis_connect(12);

	$return_value='';	
	
	if($redis->hExists($ticket,'validation')){
		$json=$redis->hGet($ticket,'validation');
		$validation=json_decode($json,true);
		$check=aw2_session_ticket_check($ticket,$otp_value,$validation);	
		if($check){
			if($field)
				$return_value=$redis->hGet($ticket,$field);
			else	
				$return_value=$redis->hGetAll($ticket);
		}
	}


	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

