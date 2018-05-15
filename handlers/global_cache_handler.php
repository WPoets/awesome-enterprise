<?php
namespace aw2\global_cache;

\aw2_library::add_service('global_cache','Global Cache Library',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('global_cache.set','Set the Global Cache',['namespace'=>__NAMESPACE__]);

function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'key'=>null,
	'prefix'=>'',
	'ttl' => 300
	), $atts) );
	
	if(!isset($atts['value']))$value=$content;
	else
	$value=$atts['value'];	
	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_GLOBAL_CACHE);
	
	if(!$key)return 'Invalid Key';		
	if($prefix)$key=$prefix . $key;
	
	$redis->set($key, $value);
	$redis->setTimeout($key, $ttl*60);
	return;
}


\aw2_library::add_service('global_cache.get','Get the Global Cache',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data		
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_GLOBAL_CACHE);

	$return_value='';
	if($redis->exists($main))
		$return_value = $redis->get($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('global_cache.flush','Flush the Global Cache',['namespace'=>__NAMESPACE__]);

function flush($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	//Connect to Redis and store the data
		
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_GLOBAL_CACHE);

	$redis->flushdb() ;
}