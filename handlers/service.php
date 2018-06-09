<?php
 
namespace aw2\service;

\aw2_library::add_service('service.run','Used to run a service',['namespace'=>__NAMESPACE__]);

function run($atts,$content,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'main'=>null,
		'service'=>null,
		'template'=>null,
		'module'=>null
	), $atts) );
	
	
	if($main){
		$arr=explode('.',$main);
		$service=$arr[0];
		$collection=\aw2_library::get_array_ref('handlers',$service);

		$module=$arr[1];

		$template=null;
		if(isset($arr[2]))$template=$arr[2];

		$return_value=\aw2_library::module_run($collection,$module,$template,$content,$atts);
	}

	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
 	
	return $return_value;
}






