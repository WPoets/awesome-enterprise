<?php
namespace aw2\vsession;

\aw2_library::add_service('vsession','vsession Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('vsession.create','Create the Vsession',['namespace'=>__NAMESPACE__]);

function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'id'=>null,
	'setcookie'=>'yes'
	), $atts) );
	
	if(!$id)$id='aw2_vsession';

	$ticket=uniqid();
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$redis->hSet($ticket,'ticket_id',$ticket);
	$redis->expire($ticket, 60*60);
	unset($_COOKIE[$id]);
	if($setcookie==='yes')setcookie($id, $ticket, -1, '/');
	
	$return_value=$ticket;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('vsession.exists','Check if VSession Exists',['namespace'=>__NAMESPACE__]);
function exists($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'id'=>null
	), $atts) );
	
	if(!$id)$id='aw2_vsession';

	$return_value = false;	
	if(isset($_COOKIE[$id])){
		$ticket=$_COOKIE[$id];
		$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		if($redis->exists($ticket))$return_value = true;
	}
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('vsession.set','set VSession',['namespace'=>__NAMESPACE__]);
function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'key'=>null,
	'value'=>null,
	'id'=>null,
	'ticket'=>null
	), $atts) );

	if(!$id)$id='aw2_vsession';
	//get ticketid from cookie or main

	if($ticket){}
	else
	{
		if(isset($_COOKIE[$id]))
			$ticket=$_COOKIE[$id];
	}
	
	if($ticket!==null){
		$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		if($redis->exists($ticket)){
				$redis->hSet($ticket,$key,$value);
		}
	}
	return;
}

\aw2_library::add_service('vsession.get','Get VSession',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'id'=>null,
	'ticket'=>null
	), $atts) );

	if(!$id)$id='aw2_vsession'	;

	//get ticketid from cookie or main
	if(!$ticket && isset($_COOKIE[$id])){
			$ticket=$_COOKIE[$id];
	}

	$return_value='';
	
	if($ticket!==null){
		$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		if(!$main){
			$return_value=$redis->hGetAll($ticket);
		}			
		else{
			if(!$redis->hExists($ticket,$main))
				$return_value='';
			else
				$return_value=$redis->hGet($ticket, $main);
		}
	}

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

