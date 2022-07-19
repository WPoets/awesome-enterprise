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
	
	//check the location
	$connection_arr=\aw2_library::$stack['code_connections'];
	if(!isset($connection_arr[$connection])) 
		throw new Exception($connection.' connection is not defined');
	
	$config = $connection_arr[$connection];

	$hash='modules:' . $post_type . ':' . $module;
	
	if(USE_ENV_CACHE){
		$return_value=\aw2\global_cache\get(["main"=>$hash ,"db"=>$config['redis_db']],null,null);
		$return_value=json_decode($return_value,true);
	}

	if(!$return_value){
		$url=$config['url'] . '/' . $post_type . '/' . $module . '.module.html';
		$code = @file_get_contents($url);

		if($code===false)
			$return_value=array();
		else	
			$return_value=\aw2\url_conn\convert_to_module($post_type,$module,$code,$url,$hash);
			
		if(SET_ENV_CACHE){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'300';
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

	$hash='modules:' . $post_type . ':' . $module;
	
	$metas=array();
	
	if(USE_ENV_CACHE){
		$data=\aw2\global_cache\get(["main"=>$hash,"db"=>$config['redis_db']],null,null);
		$metas=json_decode($data,true);
	}
	
	if(!$metas){
		// read the settings.json for the app which is key value folder
		$url=$config['url'] . '/' . $post_type;
		$metas =  @file_get_contents($url.'/settings.json');
		$metas= json_decode($metas,true);
				
		if(SET_ENV_CACHE){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'300';
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
	
	
	$hash='collection:' . $post_type;
	
	if(USE_ENV_CACHE){
		$data=\aw2\global_cache\get(["main"=>$hash,"db"=>$config['redis_db']],null,null);
		$results=json_decode($data,true);
	}
	
	if(!$results){
		
		$results = \aw2\url_conn\get_results($config['path'],$post_type);
		
		if(SET_ENV_CACHE){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'300';
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
	
	$hash='collection_list:' . $post_type;

	if(USE_ENV_CACHE){
		$data=\aw2\global_cache\get(["main"=>$hash,"db"=>$config['redis_db']],null,null);
		$results=json_decode($data,true);
	}
	
	if(!$results){
		$results = \aw2\url_conn\get_results($config,$post_type, false);			
		if(SET_ENV_CACHE){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'300';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($results),null);
		}
	}
	$return_value=$results;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

