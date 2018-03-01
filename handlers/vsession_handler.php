<?php

aw2_library::add_library('vsession','Virtual Session Handler');

function aw2_vsession_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'id'=>null
	), $atts) );
	
	if(!$id)$id='aw2_vsesssion';
	unset($_COOKIE[$id]);
	$length=15;
	$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	$token=substr( str_shuffle( $chars ), 0, $length );
	$nonce=wp_create_nonce($token);
	$ticket=$token . '.' . $nonce;
	setcookie($id, $ticket, -1, '/');
	return ;
}

aw2_library::add_library('vsession','Virtual Session Handler');

function aw2_vsession_exists($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
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
		if(wp_create_nonce($token)!=$nonce)$return_value = true;
	}
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


function aw2_vsession_set($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'key'=>null,
	'value'=>null,
	'prefix'=>'',
	'ttl' => 60,
	'id'=>null
	), $atts) );

	if(!$id)$id='aw2_vsesssion';
	
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 12;
	$redis->select($database_number);
	
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
	
	$redis->set($key, $value);
	$redis->setTimeout($key, $ttl*60);
	return;
}

function aw2_vsession_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'id'=>null,
	'prefix'=>'',
	), $atts) );

	if(!$id)$id='aw2_vsesssion'	;
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 12;
	$redis->select($database_number);
	$return_value='_error';
	
	if(isset($_COOKIE[$id])){
		$ticket=$_COOKIE[$id];
		$pieces=explode('.',$ticket);
		$token=$pieces[0];
		$nonce=$pieces[1];
		
		//verify that nonce is valid
		if(wp_create_nonce($token)==$nonce){
			$return_value=$redis->hMGet($ticket, array($main));
		}
	}
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

