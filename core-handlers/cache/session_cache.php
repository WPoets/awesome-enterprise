<?php
namespace aw2\session_cache;

\aw2_library::add_service('session_cache','Session Cache Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('session_cache.set','Set Session Cache',['namespace'=>__NAMESPACE__]);

function set($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'key'=>null,
	'value'=>null,
	'prefix'=>'',
	'ttl' => 60
	), $atts) );
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	
	if(!$key)return 'Invalid Key';		
	if($prefix)$key=$prefix . $key;
	
	$redis->set($key, $value);
	$redis->expire($key, $ttl*60);
	return;
}


\aw2_library::add_service('session_cache.get','Set Session Cache',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_SESSION_CACHE
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
	$return_value='';
	if($redis->exists($main))
		$return_value = $redis->get($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('session_cache.hset','Set Session Cache',['namespace'=>__NAMESPACE__]);
function hset($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'prefix'=>'',
	'ttl' => 60,
	'db'=>REDIS_DATABASE_SESSION_CACHE
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



\aw2_library::add_service('session_cache.hget','Set Session Cache',['namespace'=>__NAMESPACE__]);
function hget($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_SESSION_CACHE
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
		
	$return_value='';
	
	if($redis->exists($main)){
		$return_value = is_null($field)?$redis->hGetAll($main):$redis->hget($main,$field);
	}	
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('session_cache.del','Delete key from session cache',['namespace'=>__NAMESPACE__]);
function del($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_SESSION_CACHE
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
		
	$return_value='';
	
	if($redis->exists($main)){
		$return_value = $redis->del($main);
	}	
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('session_cache.hlen','Get hash count of a key',['namespace'=>__NAMESPACE__]);
function hlen($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'prefix'=>'',
	'db'=>REDIS_DATABASE_SESSION_CACHE
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect($db);
		
	$return_value='';
	
	if($redis->exists($main)){
		$return_value = $redis->hLen($main);
	}	
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('session_cache.flush','Flush Session Cache',['namespace'=>__NAMESPACE__]);
function flush($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$redis->flushdb() ;
}

