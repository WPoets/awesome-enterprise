<?php
namespace aw2\vsession;

\aw2_library::add_service('vsession','vsession Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('vsession.create','Create the Vsession',['namespace'=>__NAMESPACE__]);

function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'id'=>null
	), $atts) );
	
	if(!$id)$id='aw2_vsesssion';
	unset($_COOKIE[$id]);
	$length=15;
	$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	$token=substr( str_shuffle($chars ), 0, $length );
	$nonce=wp_create_nonce($token);
	$ticket=$token . '.' . $nonce;
	setcookie($id, $ticket, -1, '/');
	
	
	return ;
}

\aw2_library::add_service('vsession.exists','Check if VSession Exists',['namespace'=>__NAMESPACE__]);
function exists($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'id'=>null
	), $atts) );
	
	if(!$id)$id='aw2_vsesssion';

	$return_value = false;	
	if(isset($_COOKIE[$id])){
		$ticket=$_COOKIE[$id];
		$pieces=explode('.',$ticket);
		$token=$pieces[0];
		$nonce=$pieces[1];
		
		//verify that nonce is valid
		if(wp_create_nonce($token) !==$nonce)$return_value = true;
	}
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('vsession.set','set VSession',['namespace'=>__NAMESPACE__]);
function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'key'=>null,
	'value'=>null,
	'prefix'=>'',
	'ttl' => 60,
	'id'=>null
	), $atts) );

	if(!$id)$id='aw2_vsesssion';
	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);

	
	if(isset($_COOKIE[$id])){
		$ticket=$_COOKIE[$id];
		$pieces=explode('.',$ticket);
		$token=$pieces[0];
		$nonce=$pieces[1];
		
		//verify that nonce is valid
		if(wp_create_nonce($token)!=$nonce){
			unset($_COOKIE[$id]);
			$length=15;
			$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			
			$token=substr( str_shuffle( $chars ), 0, $length );
			$nonce=wp_create_nonce($token);
			$ticket=$token . '.' . $nonce;
			setcookie($id, $ticket, -1, '/');
		}
	}
	
	if(!$key)return 'Invalid Key';		
	if($prefix)$key=$prefix . $key;
	$redis->hMSet($ticket, array($key => $value));
	
	$redis->setTimeout($ticket, $ttl*60);
	return;
}

\aw2_library::add_service('vsession.get','Get VSession',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'id'=>null,
	'prefix'=>'',
	), $atts) );

	if(!$id)$id='aw2_vsesssion'	;
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);

	$return_value='_error';
	
	if(isset($_COOKIE[$id])){
		$ticket=$_COOKIE[$id];
		$pieces=explode('.',$ticket);
		$token=$pieces[0];
		$nonce=$pieces[1];
		
		//verify that nonce is valid
		if(wp_create_nonce($token)==$nonce){
			
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
	}
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

