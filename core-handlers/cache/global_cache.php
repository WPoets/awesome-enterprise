<?php
namespace aw2\global_cache;

\aw2_library::add_service('global_cache','Global Cache Library',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('global_cache.set','Set the Global Cache',['namespace'=>__NAMESPACE__]);

function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'key'=>null,
	'prefix'=>'',
	'ttl' => 300,
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );
	
	if(!isset($atts['value']))$value=$content;
	else
	$value=$atts['value'];	
	$redis = \aw2_library::redis_connect($db);
	
	if(!$key)return 'Invalid Key';		
	if($prefix)$key=$prefix . $key;
	
	$redis->set($key, $value);
	$redis->expire($key, $ttl*60);
	return;
}

\aw2_library::add_service('global_cache.hset','Set the Global Cache',['namespace'=>__NAMESPACE__]);
function hset($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'prefix'=>'',
	'ttl' => 300,
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );
	
	if(!isset($atts['value']))$value=$content;
	else
	$value=$atts['value'];	
	$redis = \aw2_library::redis_connect($db);
	
	$key=$main;
	if(!$key)return 'Invalid key';		
	if($prefix)$key=$prefix . $key;
	
	if(!$field)return 'Invalid field';		
	
	$redis->hset($key, $field,$value);
	$redis->expire($key, $ttl*60);
	return;
}


\aw2_library::add_service('global_cache.get','Get the Global Cache',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
		
	$return_value='';
	if($redis->exists($main))$return_value = $redis->get($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('global_cache.hget','Get the Global Cache',['namespace'=>__NAMESPACE__]);
function hget($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if(!$field)return 'Invalid field';
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
		
	$return_value='';
	if($redis->exists($main))$return_value = $redis->hget($main,$field);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('global_cache.exists','if exists in the global cache',['namespace'=>__NAMESPACE__]);
function exists($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
		
	$return_value=false;
	if($redis->exists($main))$return_value = true;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('global_cache.flush','Flush the Global Cache',['namespace'=>__NAMESPACE__]);

function flush($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );	
		$redis = \aw2_library::redis_connect($db);
	$redis->flushdb() ;
}


\aw2_library::add_service('global_cache.del','Delete a Key',['namespace'=>__NAMESPACE__]);

function del($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
	if($redis->exists($main))$redis->del($main);
	return;	
}


\aw2_library::add_service('global_cache.run','Set the Global Cache',['namespace'=>__NAMESPACE__]);

function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'ttl' => 30,
	'db'=>REDIS_DATABASE_GLOBAL_CACHE
	), $atts) );

	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
		
	if($main && $redis->exists($main)){
		$return_value = $redis->get($main);
	}
	else{
		$return_value=\aw2_library::parse_shortcode($content) ;
	}
		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}
