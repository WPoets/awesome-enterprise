<?php
 
namespace aw2\service;

\aw2_library::add_service('service.run','Used to run a service',['namespace'=>__NAMESPACE__]);

function run($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'service'=>null,
		'template'=>null,
		'module'=>null,
		'_default'=>'service'
	), $atts) );
	
	$return_value = '';
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




