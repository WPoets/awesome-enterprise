<?php

aw2_library::add_library('global_cache','Session Cache Handler');

function aw2_global_cache_set($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'key'=>null,
	'prefix'=>'',
	'ttl' => 300
	), $atts) );
	
	if(!isset($atts['value']))$value=$content;
	else
	$value=$atts['value'];	
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 11;
	$redis->select($database_number);
	
	if(!$key)return 'Invalid Key';		
	if($prefix)$key=$prefix . $key;
	
	$redis->set($key, $value);
	$redis->setTimeout($key, $ttl*60);
	return;
}

function aw2_global_cache_get($atts,$content=null,$shortcode=null){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 11;
	$redis->select($database_number);
	$return_value='';
	if($redis->exists($main))
		$return_value = $redis->get($main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_global_cache_flush($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	//Connect to Redis and store the data
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 11;
	$redis->select($database_number);
	$redis->flushdb() ;
}