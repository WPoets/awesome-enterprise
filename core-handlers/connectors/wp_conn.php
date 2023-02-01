<?php
namespace aw2\wp_conn;

function get_results($sql,$connection,$config){
	//php8OK
	$conn=code_conn($connection,$config);
	$obj = $conn->query($sql);
	
	$results = $obj->fetchAll("assoc");
	return $results;
}

function code_conn($connection,$config){
	//php8OK
	$conn=\aw2_library::get('#'.$connection.'.conn');
	if(is_object($conn))return $conn; 

	$conn = new \SimpleMySQLi($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name'], "utf8mb4", "assoc");
	\aw2_library::set('#'.$connection.'.conn',$conn);
	$conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
	return $conn;
}

function convert_to_module($raw,$hash){
	$arr=array();
	$arr['module']=$raw['post_name'];
	$arr['post_type']=$raw['post_type'];
	$arr['title']=$raw['post_title'];
	$arr['id']=$raw['ID'];
	$arr['code']=$raw['post_content'];
	$arr['hash']=$hash;
	$arr['wp']='yes';
	return $arr;

}


namespace aw2\wp_conn\module;

\aw2_library::add_service('wp_conn.module.get','Get a Module',['namespace'=>__NAMESPACE__]);

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
	$return_value='';
	if($use_env_cache){
		$return_value=\aw2\global_cache\get(["main"=>$hash ,"db"=>$config['redis_db']],null,null);
		$return_value=json_decode($return_value,true);
	}

	if(!$return_value){
		$sql="select post_content,post_type,ID,post_name,post_title from wp_posts where post_type='" . $post_type . "' and post_name='" . $module . "'";
		
		$results =\aw2\wp_conn\get_results($sql,$connection,$config);				

		if(count($results)!==1)
			$return_value=array();
		else	
			$return_value=\aw2\wp_conn\convert_to_module($results[0],$hash);
		
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='connection.cache.not_used';
			$live_debug_event['cache_used']='no';
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


\aw2_library::add_service('wp_conn.module.meta','Get a Module Meta',['namespace'=>__NAMESPACE__]);

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
	
	if(!$metas){
		$post_table= 'wp_posts';
		$post_meta_table= 'wp_postmeta';
		
		if(IS_WP){
			global $wpdb;	
			$post_table = $wpdb->posts;
			$post_meta_table = $wpdb->postmeta;
		}
	
		$sql="
		with q0 as(
		   select ID from ".$post_table." where post_name='".$module."' and post_type='".$post_type."'
		),
		q1 as (
		   select post_id,meta_key,meta_value from ".$post_meta_table." join q0 on post_id=q0.ID
		)
		select *from q1;
		";
		$results =\aw2\wp_conn\get_results($sql,$connection,$config);				

		$metas=array();		
		foreach ($results as $result) {
			$metas[$result['meta_key']]=$result['meta_value'];
		}
		
		if($set_env_cache){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'300';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($metas),null);
		}
		
	}
	
	$return_value=\aw2_library::post_actions('all',$metas,$atts);
	return $return_value;	
}

\aw2_library::add_service('wp_conn.module.exists','Get a Module',['namespace'=>__NAMESPACE__]);

function exists($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'connection'=>null,
	'post_type'=>null,
	'module'=>null,
	), $atts) );

	if(empty($connection)) 
		throw new Exception('connection is not provided');;
	//make module slug in lower case
	$module=strtolower($module);
	
	$results=\aw2\wp_conn\collection\_list($atts);
	$module_names = array_column($results, 'post_title', 'post_name');
		
	if(isset($module_names[$module]))
		$return_value= true;
	else	
		$return_value= false;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

namespace aw2\wp_conn\collection;

\aw2_library::add_service('wp_conn.collection.get','Get a Collection',['namespace'=>__NAMESPACE__]);

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
	
	$results='';
	if($use_env_cache){
		$data=\aw2\global_cache\get(["main"=>$hash,"db"=>$config['redis_db']],null,null);
		$results=json_decode($data,true);
	}
	
	if(!$results){
		
		$post_table= 'wp_posts';
		if(IS_WP){
			global $wpdb;	
			$post_table = $wpdb->posts;
			
		}
		
		$sql="select post_content,post_type,ID,post_name,post_title from ".$post_table." where post_status='publish' and post_type='" . $post_type . "'";
		$results =\aw2\wp_conn\get_results($sql,$connection,$config);				

		if($set_env_cache){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'600';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($results),null);
		}
		
	}
	$return_value=array();
	foreach ($results as $result) {
		$post=\aw2\wp_conn\convert_to_module($result,'modules:' . $post_type . ':' . $result['post_name']);
		$return_value[$post['module']]=$post;
	}
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}




\aw2_library::add_service('wp_conn.collection.list','Get List of ',['func'=>'_list' ,'namespace'=>__NAMESPACE__]);

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
	
	if(!$results){
		$post_table= 'wp_posts';
		if(IS_WP){
			global $wpdb;	
			$post_table = $wpdb->posts;
			
		}
		
		$sql="select post_type,ID,post_name,post_title from ".$post_table." where post_status='publish' and post_type='" . $post_type . "'";
		$results =\aw2\wp_conn\get_results($sql,$connection,$config);				
		
		if($set_env_cache){
			$ttl = isset($config['cache_expiry'])?$config['cache_expiry']:'600';
			\aw2\global_cache\set(["key"=>$hash,"db"=>$config['redis_db'],'ttl'=>$ttl],json_encode($results),null);
		}
	}
	$return_value=$results;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

