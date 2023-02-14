<?php
 
namespace aw2\service;

use function aw2\c\not_null;

\aw2_library::add_service('service.run','Used to run a service',['namespace'=>__NAMESPACE__]);

function run($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'service'=>null,
		'template'=>null,
		'module'=>null,
		'_atts_arr'=>array(),
		'_default'=>'service'
	), $atts) );
	
	$return_value = '';

	if(is_array($_atts_arr)){
		$atts = array_merge($atts,$_atts_arr);	
	}

	if($service){
		$return_value=\aw2_library::service_run($service,$atts,$content,$_default);		
	}
	else{
		if($main){
			$arr=explode('.',$main);
			$service=$arr[0];
			$collection=\aw2_library::get_array_ref('handlers',$service);

			$module=$arr[1];

			$template=null;
			if(isset($arr[2]))$template=$arr[2];

			$return_value=\aw2_library::module_run($collection,$module,$template,$content,$atts);
		}
	}	

	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
 	
	return $return_value;
}

 
\aw2_library::add_service('service.template.add','Add a New Template',['func'=>'template_add','namespace'=>__NAMESPACE__]);
 
function template_add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'desc'=>null
	), $atts) );
	unset($atts['main']);
	unset($atts['desc']);
	$atts['name']=$main;	
	$atts['namespace']=__NAMESPACE__;	
	$atts['func']='template_run';	
	$atts['code']=$content;	
	$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
	if(isset($sc_exec['content_pos']))$atts['content_pos']=$sc_exec['content_pos'];
	if(isset($sc_exec['module']))$atts['module']=$sc_exec['module'];
	if(isset($sc_exec['collection']))$atts['collection']=$sc_exec['collection'];


	\aw2_library::add_service($main,$desc,$atts);
}

\aw2_library::add_service('service.template.run','Used to run a service',['func'=>'template_run','namespace'=>__NAMESPACE__]);

function template_run($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'service'=>null,
		'template'=>null,
		'module'=>null,
		'_default'=>'service'
	), $atts) );
	
	$return_value=\aw2_library::service_template_run($shortcode['handler'],$atts);		

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
 	
	return $return_value;
}

function runbackup($atts,$content,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
	), $atts) );
	
	if(!$main)
		$return_value='Service not Found';
	else
		$return_value=\aw2_library::service_run($main,$atts,$content);		

	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
 	
	return $return_value;
}



\aw2_library::add_service('service.modules.list','get a list of modules of collection',['func'=>'modules_list','namespace'=>__NAMESPACE__]);


//[service.modules.list db_service /]	
function modules_list($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null
	), $atts) );
	
	$collection=\aw2_library::get('handlers.' . $main);

	if(!isset($collection['connection']))$collection['connection']='#default';
	$connection_arr=\aw2_library::$stack['code_connections'][$collection['connection']];
	
	$connection_service = '\\aw2\\'.$connection_arr['connection_service'].'\\collection\\_list';
		
	$atts['connection']=$collection['connection'];
	$atts['post_type']=$collection['post_type'];
	$modules = call_user_func($connection_service,$atts);
	return $modules;
}


\aw2_library::add_service('service.module.get','get a modules',['func'=>'module_get','namespace'=>__NAMESPACE__]);
//[service.module.get db_service module=m1 /]	

//[service.modules.list db_service /]	
function module_get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'service'=>null
	), $atts) );
	
	$collection=\aw2_library::get('handlers.' . $service);
	$module=\aw2_library::get_module($collection,$main);
 	
	return $module;
}



