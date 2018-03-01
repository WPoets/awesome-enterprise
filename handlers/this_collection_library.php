<?php

aw2_library::add_library('this_collection','This Collection Library');


function aw2_this_collection_unhandled($atts,$content,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'template'=>null
	), $atts) );
	$ref=aw2_library::get_array_ref('module');
	$collection=$ref['collection'];

	$pieces=$shortcode['tags'];
	if(!count($pieces)>=1)return 'Module not defined';
	array_shift($pieces);
	$module=array_shift($pieces);
	
	$t=implode('.',$pieces);

	if($template)
		$return_value=aw2_library::module_forced_run($collection,$module,$template,$content,$atts);	
	else
		$return_value=aw2_library::module_run($collection,$module,$t,$content,$atts);		
	
	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	return $return_value;
}

function aw2_this_collection_run($atts,$content,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'main'=>null,
		'template'=>null,
		'module'=>null
	), $atts) );
	$ref=aw2_library::get_array_ref('module');
	$collection=$ref['collection'];

	if($main==null)return 'Module/Template must be provided';	

	if(!$module && $template)$return_value=aw2_library::module_forced_run($collection,$main,$template,$content,$atts);	
	if(!$module && !$template)$return_value=aw2_library::module_run($collection,$main,null,$content,$atts);
	if($module && !$template)$return_value=aw2_library::module_run($collection,$module,$main,$content,$atts);	
		
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}
  

function aw2_this_collection_include($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	
	$ref=aw2_library::get_array_ref('module');
	$collection=$ref['collection'];

	$return_value=aw2_library::module_include($collection,$main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


function aw2_this_collection_include_raw($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	
	$ref=aw2_library::get_array_ref('module');
	$collection=$ref['collection'];

	$return_value=aw2_library::module_include_raw($collection,$main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}