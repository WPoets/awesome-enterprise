<?php
namespace aw2\session_ticket;

function check($ticket,$otp_value,$validation){
	//otp validation
	if(isset($validation['otp_value'])){
		if($otp_value!==$validation['otp_value'])
			return false;
	}
	
	// nonce validation
	if(isset($validation['nonce'])){
		$nonce=\wp_create_nonce($ticket);
		if($nonce!== $validation['nonce'])return false;
	}
	
	// user validation
	if(isset($validation['user'])){
		$current_user = \aw2_library::get('app.user.login');
		if($current_user!== $validation['user'])return false;
	}
	
	// app validation
	if(isset($validation['app'])){
		$app = \aw2_library::get('app.slug');
		if($app!== $validation['app'])return false;
	}

	return true;	
	
}

\aw2_library::add_service('session_ticket','Session Ticket',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('session_ticket.create','Create a ticket',['namespace'=>__NAMESPACE__]);


function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'time' =>60,
	'nonce'=>'no',
	'otp_value'=>null,
	'user'=>null,
	'app'=>null,
	'main'=>null
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
	
	if($main)$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);

	if($redis->exists($ticket)){
		$return_value='_error';
		$return_value=\aw2_library::post_actions('all',$return_value,$atts);
		return $return_value;
	}
	
	
	//do i need a nonce
	if($nonce==='yes'){
		$validation['nonce']=\wp_create_nonce($ticket);
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
			$validation['app']=\aw2_library::get('app.slug');
		else{
			$validation['app']=$app;
		}
	}	
	$json=json_encode($validation);
	$redis->hSet($ticket,'validation',$json);
	$redis->hSet($ticket,'ticket_id',$ticket);
	
	if((float)$time >=0)
		$redis->expire($ticket, $time*60);
	$return_value=$ticket;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('session_ticket.validate','Validate a ticket',['namespace'=>__NAMESPACE__]);

function validate($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'otp_value'=>null
	), $atts) );
	$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	
	$return_value=true;
	
	if(!$redis->exists($ticket))$return_value=false;

	// validate
	if($return_value===true and !$redis->hExists($ticket,'validation'))$return_value=false;	

	$json=$redis->hGet($ticket,'validation');
	$validation=json_decode($json,true);

	if($return_value===true)
		$return_value=check($ticket,$otp_value,$validation);	

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


/*
	app =
	collection =
	module=
*/
\aw2_library::add_service('session_ticket.set_activity','Set activity of a  ticket',['namespace'=>__NAMESPACE__]);

function set_activity($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'app'=>null,
	'collection'=>null,
	'module'=>null,
	'service'=>null,
	'template'=>null
	
	), $atts) );

	if(!$main)return 'Main must be set';

	$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	
	$ticket_activity=array();
	if($app)$ticket_activity['app']=$app;
	if($collection)$ticket_activity['collection']=$collection;
	if($module)$ticket_activity['module']=$module;
	if($service)$ticket_activity['service']=$service;
	if($template)$ticket_activity['template']=$template;
	
	$json=json_encode($ticket_activity);
	$redis->hSet($ticket,'ticket_activity',$json);
	return;
}


\aw2_library::add_service('session_ticket.set','Set values of a ticket',['namespace'=>__NAMESPACE__]);

function set($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>'_error',
	'value'=>null,
	), $atts) );

	if(!$main)return 'Main must be set';

	$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$redis->hSet($ticket,$field,$value);
	return;
}

\aw2_library::add_service('session_ticket.set_timeout','Set values of a ticket',['namespace'=>__NAMESPACE__]);

function set_timeout($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'time' => 60,
	'main'=>null,
	'field'=>'_error',
	'value'=>null,
	), $atts) );

	if(!$main)return 'Main must be set';

	$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$redis->expire($ticket, $time*60);
	return;
}

\aw2_library::add_service('session_ticket.get','Get values of a ticket',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode=null){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'otp_value'=>null
	), $atts) );

	if(!$main)return 'Main must be set';		
	
	$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);

	$return_value='';	
	
	if($redis->hExists($ticket,'validation')){
		$json=$redis->hGet($ticket,'validation');
		$validation=json_decode($json,true);
		$check=check($ticket,$otp_value,$validation);	
		if($check){
			if($field)
				$return_value=$redis->hGet($ticket,$field);
			else	
				$return_value=$redis->hGetAll($ticket); 
				
			if($field==='ticket_expiry'){
				$ttl=$redis->ttl($ticket);
				$return_value=round($ttl/60,2);
			}		
		}
	}


	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

\aw2_library::add_service('session_ticket.query','Get all values of a query',['namespace'=>__NAMESPACE__]);

function query($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );

	if(!$main)return 'Main must be set';
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$return_value= $redis->keys($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('session_ticket.generate_token','Generate action token against ticket.',['namespace'=>__NAMESPACE__]);

function generate_token($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'value'=>null,
	), $atts) );

	if(!$main)return 'Main must be set';

	$field = uniqid();
	
	$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$redis->hSet($ticket,$field,$value);
	return $field;
}

\aw2_library::add_service('session_ticket.destroy','Destroy a ticket',['namespace'=>__NAMESPACE__]);

function destroy($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );

	if(!$main)return 'Main must be set';		
	
	$ticket=$main;
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);

	$return_value=$redis->unlink($ticket);
	
}
