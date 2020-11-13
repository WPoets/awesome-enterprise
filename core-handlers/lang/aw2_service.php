<?php

// aw2_register_service

function aw2_service_unhandled($atts,$content,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'template'=>null
	), $atts) );
	$pieces=$shortcode['tags'];
	if(!count($pieces)>=1)return 'Module not defined';
	array_shift($pieces);
	$module=array_shift($pieces);
	
	$t=implode('.',$pieces);

	if($template)
		$return_value=aw2_library::module_forced_run($shortcode['handler'],$module,$template,$content,$atts);	
	else
		$return_value=aw2_library::module_run($shortcode['handler'],$module,$t,$content,$atts);		
	
	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}


function aw2_service_run($atts,$content,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'template'=>null,
		'module'=>null
	), $atts) );
	if($main==null)return 'Module/Template must be provided';	
	echo 'here';
	if(!$module && $template)$return_value=aw2_library::module_forced_run($shortcode['handler'],$main,$template,$content,$atts);	
	if(!$module && !$template)$return_value=aw2_library::module_run($shortcode['handler'],$main,null,$content,$atts);
	if($module && !$template)$return_value=aw2_library::module_run($shortcode['handler'],$module,$main,$content,$atts);	
		
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}

function aw2_service_include($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	$return_value=aw2_library::module_include($shortcode['handler'],$main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

// aw2_register_service

function aw2_service_call($atts,$content=null){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'service'=>null,
		'main'=>null,
		'template'=>null,
		'module'=>null
	), $atts) );
	if($main==null)return 'Module/Template must be provided';	

	$handlers=&aw2_library::get_array_ref('handlers');
	$handler=$handlers[$service];

				
	if(!$module && $template)$return_value=aw2_library::module_forced_run($handler,$main,$template,$content,$atts);	
	if(!$module && !$template)$return_value=aw2_library::module_run($handler,$main,null,$content,$atts);
	if($module && !$template)$return_value=aw2_library::module_run($handler,$module,$main,$content,$atts);	
		
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}