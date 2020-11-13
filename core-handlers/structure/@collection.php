<?php
namespace aw2\active_collection;


\aw2_library::add_service('@collection','Pointer to the active collection',['namespace'=>__NAMESPACE__]);


function unhandled($atts,$content,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;	


	$ref=\aw2_library::get_array_ref('module');
	$sc['collection']=$ref['collection'];	
	$sc['handler']=\aw2_library::get_array_ref('handlers','collection');
	$service='collection';
	$next_tag=reset($shortcode['tags_left']);
	$sc['tags_left']=$shortcode['tags_left'];
	if(isset($sc['handler'][$next_tag])){
		$service=$next_tag;
		$sc['handler']=$sc['handler'][$next_tag];
		$next_tag=null;
	}	
	$handler = $sc['handler'];		

	if(isset($handler['func']))
		$fn_name=$handler['namespace'] . '\\' . $handler['func'];
	else{
			$fn_name=$handler['namespace'] . '\\' . $service;					
	}
	if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
	if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
	if (!is_callable($fn_name))$fn_name=null;
	$return_value ='';

	if($fn_name)$return_value = call_user_func($fn_name, $atts, $content, $sc );

	return $return_value;
}

/*
function aw2_this_collection_run($atts,$content,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'template'=>null,
		'module'=>null
	), $atts) );
	$ref=\aw2_library::get_array_ref('module');
	$collection=$ref['collection'];

	if($main==null)return 'Module/Template must be provided';	

	if(!$module && $template)$return_value=\aw2_library::module_forced_run($collection,$main,$template,$content,$atts);	
	if(!$module && !$template)$return_value=\aw2_library::module_run($collection,$main,null,$content,$atts);
	if($module && !$template)$return_value=\aw2_library::module_run($collection,$module,$main,$content,$atts);	
		
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}
  

function aw2_this_collection_include($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	
	$ref=\aw2_library::get_array_ref('module');
	$collection=$ref['collection'];

	$return_value=\aw2_library::module_include($collection,$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


function aw2_this_collection_include_raw($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	
	$ref=\aw2_library::get_array_ref('module');
	$collection=$ref['collection'];

	$return_value=\aw2_library::module_include_raw($collection,$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}
*/