<?php
namespace aw2\url_conn;

function get_results($config,$post_type,$read_file=true){
     $modules = array();
	
	if(!isset($config['url'])) return $modules;
	
	$url=$config['url'] . '/' . $post_type;
	
	$modules_list =  @file_get_contents($url.'/modules.json');
	$modules_list= json_decode($modules_list,true);
	
	foreach($modules_list['modules'] as $module){

		$file_url = $url.'/'.$module.'.module.html';
		$p=array();
		$p['post_name']=$module;
		$p['post_title']=$module;
		$p['path'] = $file_url;
		$p['post_type'] = $post_type;
		if($read_file) $p['post_content'] = @file_get_contents($file_url);
		$modules[]=$p;
	}
	
	return $modules;
}

function convert_to_module($post_type,$module,$code,$path,$hash){
	$arr=array();
	$arr['module']=$module;
	$arr['post_type']=$post_type;
	$arr['title']=$module;
	$arr['id']=$path;
	$arr['code']=$code;
	$arr['hash']=$hash;
	return $arr;

}


namespace aw2\url_conn\module;

\aw2_library::add_service('url_conn.module.get','Get a Module',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'connection'=>'#default',
	'post_type'=>null,
	'module'=>null,
	), $atts) );

	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='connection';
		$live_debug_event['action']='connection.called';
		$live_debug_event['stream']='module_get';
		$live_debug_event['hash']=$post_type . ':' . $module;
		$live_debug_event['module']=$module;
		$live_debug_event['connection']=$connection;
		$live_debug_event['post_type']=$post_type;

		$debug_format=array();
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}
	
	//check the location
	$connection_arr=\aw2_library::$stack['code_connections'];
	if(!isset($connection_arr[$connection])) 
		throw new Exception($connection.' connection is not defined');
	
	$config = $connection_arr[$connection];
	
	$use_env_cache=USE_ENV_CACHE;
	$set_env_cache=SET_ENV_CACHE;
	$readonly= isset($config['read_only'])?$config['read_only']:false;
	 
	if($readonly){
		$use_env_cache=true;
		$set_env_cache=true;
	}


	$hash='modules:' . $post_type . ':' . $module;

	if(\aw2_library::is_live_debug()){
		$live_debug_event['action']='connection.getting';
		$live_debug_event['cache_key']=$hash;
		$live_debug_event['use_env_cache']=$use_env_cache;
		$live_debug_event['config']=$config;
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}
	
	$return_value=null;
	
	if($use_env_cache){
		$return_value=\aw2\global_cache\get(["main"=>$hash ,"db"=>$config['redis_db']],null,null);
		$return_value=json_decode($return_value,true);

		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.cache.used';
			$live_debug_event['cache_used']='yes';
			$live_debug_event['result']=$return_value;
			$debug_format['bgcolor']='#DEB6AB';

			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}
		
		
	}

	if(is_null($return_value)){
		$url=$config['url'] . '/' . $post_type . '/' . $module . '.module.html?rnd='.rand();
		
		$code = false;
		if(\aw2\url_conn\collection\url_exists($url))
			$code = @file_get_contents($url);

		if($code===false)
			$return_value=array();
		else	
			$return_value=\aw2\url_conn\convert_to_module($post_type,$module,$code,$url,$hash);


		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.cache.not_used';
			$live_debug_event['url']=$url;
			$live_debug_event['cache_used']='no';
			$live_debug_event['SET_ENV_CACHE']=$set_env_cache;
			$live_debug_event['result']=$return_value;
			$debug_format['bgcolor']='#DEB6AB';

			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}

			
		if($set_env_cache){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'600';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($return_value),null);
		}			
	}
	
	if(defined('SET_DEBUG_CACHE') && SET_DEBUG_CACHE){
		$fields = array('last_accessed'=>date('Y-m-d H:i:s'),'connection'=>$connection);
				
		\aw2\debug_cache\set_access_post_type(["post_type"=>$return_value['post_type'],"fields"=>$fields],'',null);
		\aw2\debug_cache\set_access_module(["post_type"=>$return_value['post_type'],"module"=>$return_value['module'],"fields"=>$fields],'',null);
				
		if(isset(\aw2_library::$stack['app'])){	
			$app_slug = \aw2_library::$stack['app']['slug'];
			$fields['app_name']= \aw2_library::$stack['app']['name'];
			\aw2\debug_cache\set_access_app(["app"=>$app_slug,"fields"=>$fields],'',null);
			unset($fields);
		}
				
	}

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.done';
			$debug_format['bgcolor']='#DEB6AB';

			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}
	
	
	return $return_value;	
}


\aw2_library::add_service('url_conn.module.meta','Get a Module Meta',['namespace'=>__NAMESPACE__]);

function meta($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'connection'=>'#default',
	'post_type'=>null,
	'module'=>null,
	), $atts) );
	
	//check the location
	$connection_arr=\aw2_library::$stack['code_connections'];
	if(!isset($connection_arr[$connection])) 
		throw new Exception($connection.' connection is not defined');
	
	$config = $connection_arr[$connection];
	
	$use_env_cache=USE_ENV_CACHE;
	$set_env_cache=SET_ENV_CACHE;
	$readonly= isset($config['read_only'])?$config['read_only']:false;
	 
	if($readonly){
		$use_env_cache=true;
		$set_env_cache=true;
	}

	$hash='modules_meta:' . $post_type . ':' . $module;
	
	$metas=null;
	
	if($use_env_cache){
		$data=\aw2\global_cache\get(["main"=>$hash,"db"=>$config['redis_db']],null,null);
		$metas=json_decode($data,true);
	}
	
	if(is_null($metas)){
		// read the settings.json for the app which is key value folder
		$url=$config['url'] . '/' . $post_type;
		
		$metas='{}';
		if(\aw2\url_conn\collection\url_exists($url.'/settings.json'))
			$metas =  @file_get_contents($url.'/settings.json');
		
		$metas= json_decode($metas,true);
				
		if($set_env_cache){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'600';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($metas),null);
		}
		
	}

	$return_value=\aw2_library::post_actions('all',$metas,$atts);
	return $return_value;	
}

\aw2_library::add_service('url_conn.module.exists','Get a Module',['namespace'=>__NAMESPACE__]);

function exists($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'connection'=>null,
	'post_type'=>null,
	'module'=>null,
	), $atts) );

	if(empty($connection)) 
		throw new Exception('connection is not provided');;

	$results=\aw2\url_conn\collection\_list($atts);
	$module_names = array_column($results, 'post_title', 'post_name');

	if(isset($module_names[$module]))
		$return_value= true;
	else	
		$return_value= false;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

namespace aw2\url_conn\collection;

\aw2_library::add_service('url_conn.collection.get','Get a Collection',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'connection'=>null,
	'post_type'=>null,
	), $atts) );
	
	$connection_arr=\aw2_library::$stack['code_connections'];
	if(!isset($connection_arr[$connection])) 
		throw new Exception($connection.' connection is not defined');
	
	$config = $connection_arr[$connection];

	$use_env_cache=USE_ENV_CACHE;
	$set_env_cache=SET_ENV_CACHE;
	$readonly= isset($config['read_only'])?$config['read_only']:false;
	 
	if($readonly){
		$use_env_cache=true;
		$set_env_cache=true;
	}

	
	$hash='collection:' . $post_type;
	$results = null;
	
	if($use_env_cache){
		$data=\aw2\global_cache\get(["main"=>$hash,"db"=>$config['redis_db']],null,null);
		$results=json_decode($data,true);
	}
	
	if(is_null($results)){
		
		$results = \aw2\url_conn\get_results($config,$post_type);
		
		if($set_env_cache){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'600';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($results),null);
		}
		
	}
	$return_value=array();
	foreach ($results as $result) {
		
		$post=\aw2\url_conn\convert_to_module($result['post_type'],$result['post_name'],$result['post_content'],$result['path'],'modules:' . $result['post_type'] . ':' . $result['post_name']);
		$return_value[$post['module']]=$post;
	}
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}


\aw2_library::add_service('url_conn.collection.list','Get List of ',['func'=>'_list' ,'namespace'=>__NAMESPACE__]);

function _list($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'connection'=>null,
	'post_type'=>null,
	), $atts) );
	
	$connection_arr=\aw2_library::$stack['code_connections'];
	if(!isset($connection_arr[$connection])) 
		throw new Exception($connection.' connection is not defined');
	
	$config = $connection_arr[$connection];
	$use_env_cache=USE_ENV_CACHE;
	$set_env_cache=SET_ENV_CACHE;
	$readonly= isset($config['read_only'])?$config['read_only']:false;
	 
	if($readonly){
		$use_env_cache=true;
		$set_env_cache=true;
	}
		
	$hash='collection_list:' . $post_type;

	$results=null; 
	if($use_env_cache){
		$data=\aw2\global_cache\get(["main"=>$hash,"db"=>$config['redis_db']],null,null);
		$results=json_decode($data,true);
	}
	
	if(is_null($results)){
		$results = \aw2\url_conn\get_results($config,$post_type, false);			
		if($set_env_cache){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'600';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($results),null);
		}
	}
	$return_value=$results;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

function url_exists(string $url): bool
{
    return str_contains(get_headers($url)[0], "200 OK");
}


namespace aw2\url_conn\func;
\aw2_library::add_service('url_conn.func.get','Get a Func',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode=null){

	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'connection'=>null,
		'namespace'=>null,
		'func'=>null
	), $atts) );

	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='connection';
		$live_debug_event['action']='connection.called';
		$live_debug_event['stream']='func.get';
		$live_debug_event['connection']=$connection;
		$live_debug_event['post_type']=$namespace;

		$debug_format=array();
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}

	if(is_null($connection)) 
		throw new \Exception('connection is not defined');
	
	$func_folder = null;
	$func_filename = null;

	//check the location
	$connection_arr=\aw2_library::$stack['code_connections'];
	if(!isset($connection_arr[$connection])) 
		throw new \Exception($connection.' connection is not defined');
	
	$config = $connection_arr[$connection];
		
		
	// Process main parameter if provided
	if($main !== null) {
		$parts = explode(':', $main);
		if(count($parts) === 2) {
			$func_folder = $parts[0];
			$func_filename = $parts[1];
		}
	}
	// If namespace and func are provided, use them
	if($namespace !== null && $func !== null) {
		$func_folder = $namespace;
		$func_filename = $func;
	}

	// Validate we have the required information
	if(!$func_folder || !$func_filename)
		throw new \Exception('Function folder and filename are required. Provide either main=folder:filename or both namespace and func parameters');
	
	$hash='func:' . $func_folder . ':' . $func_filename;

	if(\aw2_library::is_live_debug()){
		$live_debug_event['action']='connection.getting';
		$live_debug_event['cache_key']=$hash;
		$live_debug_event['use_env_cache']=USE_ENV_CACHE;		
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}
	
	$return_value=null;
	if(USE_ENV_CACHE && 1===2){
		$return_value=\aw2\global_cache\get(["main"=>$hash ,"db"=>$config['redis_db']],null,null);
		$return_value=json_decode($return_value,true);

		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.cache.used';
			$live_debug_event['cache_used']='yes';
			$live_debug_event['result']=$return_value;
			$debug_format['bgcolor']='#DEB6AB';

			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}
		
		
	}

	if(is_null($return_value)){
		$url=$config['url'] . '/' . $func_folder  . '/' . $func_filename . '.func.html?rnd='.rand();

		$code = false;
		if(\aw2\url_conn\collection\url_exists($url))
			$code = @file_get_contents($url);

		if($code===false)
			$return_value=array('#exists' => false);
		else{
			$ab = new \array_builder();
			$return_value = $ab->parse($code);
			$return_value['#exists'] = true;
		}	

		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.cache.not_used';
			$live_debug_event['url']=$url;
			$live_debug_event['cache_used']='no';
			$live_debug_event['config']=$config;
			$live_debug_event['result']=$return_value;
			$debug_format['bgcolor']='#DEB6AB';

			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}

			
		if(SET_ENV_CACHE){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'300';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($return_value),null);
		}			
	}
	
	if(defined('SET_DEBUG_CACHE') && SET_DEBUG_CACHE){
		$fields = array('last_accessed'=>date('Y-m-d H:i:s'),'connection'=>$connection);
	
		if(isset(\aw2_library::$stack['app'])){	
			$app_slug = \aw2_library::$stack['app']['slug'];
			$fields['app_name']= \aw2_library::$stack['app']['name'];
			\aw2\debug_cache\set_access_app(["app"=>$app_slug,"fields"=>$fields],'',null);
			unset($fields);
		}	
	}
	
	if(\aw2_library::is_live_debug()){
		$live_debug_event['action']='connection.done';
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}

	return $return_value;	
}

namespace aw2\url_conn\service;
\aw2_library::add_service('url_conn.service.get','Get a service',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode=null){
	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'connection'=>null
	), $atts) );

	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='connection';
		$live_debug_event['action']='connection.called';
		$live_debug_event['stream']='service.get';
		$live_debug_event['connection']=$connection;
		$live_debug_event['main']=$main;

		$debug_format=array();
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}
	
	if(is_null($connection)) 
		throw new \Exception('connection is not defined');


	// If main is not defined, throw exception
	if(!$main)
		throw new \Exception('Main parameter is required when calling url_conn\service\get');
	
	$hash = 'service:' . $main;
	if(\aw2_library::is_live_debug()){
		$live_debug_event['action']='connection.getting';
		$live_debug_event['cache_key']=$hash;
		$live_debug_event['use_env_cache']=USE_ENV_CACHE;
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}
	
	$return_value=null;
	if(USE_ENV_CACHE && 1===2){
		//check key exists
		$return_value=\aw2\global_cache\get(["main"=>$hash ,"db"=>$config['redis_db']],null,null);
		$return_value=json_decode($return_value,true);

		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.cache.used';
			$live_debug_event['cache_used']='yes';
			$live_debug_event['result']=$return_value;
			$debug_format['bgcolor']='#DEB6AB';

			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}
		
		
	}

	if(is_null($return_value)){

		//check the location
		$connection_arr=\aw2_library::$stack['code_connections'];
		if(!isset($connection_arr[$connection])) 
			throw new \Exception($connection.' connection is not defined');
		
		$config = $connection_arr[$connection];


		$parts = explode('.', $main);
	
		$current_folder = $config['url'];
		$service_filename = null;
		$url=null;	
		// Loop through the parts to find the service file
		foreach($parts as $part) {
			// Check if this part with .service.html exists in current folder
			$potential_service_file_url = $current_folder . '/' . $part . '.service.html';

			if(\aw2\url_conn\collection\url_exists($potential_service_file_url)) {
				$service_filename = $part . '.service.html';
				// Set the full path for the service file
				$url = $current_folder . '/' . $service_filename;
				break;
			}
			
			// Check if this part is a folder
			$current_folder = $current_folder . '/' . $part;

			//\util::var_dump($potential_service_file_url);
		}
		$url=$url.'?rnd='.rand();
		
		$code = false;
		if(\aw2\url_conn\collection\url_exists($url))
			$code = @file_get_contents($url);


		if($code===false)
			$return_value=array('#exists' => false);
		else{
			$ab = new \array_builder();
			$return_value = $ab->parse($code);
			$return_value['#exists'] = true;
		}	

		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.cache.not_used';
			$live_debug_event['url']=$url;
			$live_debug_event['config']=$config;
			$live_debug_event['cache_used']='no';
			$live_debug_event['result']=$return_value;
			$debug_format['bgcolor']='#DEB6AB';

			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}
	
		if(SET_ENV_CACHE){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'300';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($return_value),null);
		}			
	}
	
	if(defined('SET_DEBUG_CACHE') && SET_DEBUG_CACHE){
		$fields = array('last_accessed'=>date('Y-m-d H:i:s'),'connection'=>$connection);
	
		if(isset(\aw2_library::$stack['app'])){	
			$app_slug = \aw2_library::$stack['app']['slug'];
			$fields['app_name']= \aw2_library::$stack['app']['name'];
			\aw2\debug_cache\set_access_app(["app"=>$app_slug,"fields"=>$fields],'',null);
			unset($fields);
		}
				
	}
	
	if(\aw2_library::is_live_debug()){
		$live_debug_event['action']='connection.done';
		$debug_format['bgcolor']='#DEB6AB';

		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
	}
	
	return $return_value;
}