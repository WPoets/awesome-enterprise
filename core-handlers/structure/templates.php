<?php
namespace aw2\templates;

\aw2_library::add_service('templates','Manage Templates of the Active Module',['namespace'=>__NAMESPACE__]);

function unhandled($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );	
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	if(!count($pieces)>=1)return 'Template not defined';
	$template=implode('.',$pieces);	
	
	$return_value=\aw2_library::template_run($template,$content,$atts);
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}



//////// Templates Library ///////////////////
\aw2_library::add_service('templates.add','Add a Template to the Active Module',['namespace'=>__NAMESPACE__]);

function add($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'template_type'=>null
	), $atts) );
	
	$ref=&\aw2_library::get_array_ref('module','templates');
	
	
	$ref[$main]['code']=$content;
	$ref[$main]['name']=$main;
	$ref[$main]['template_type']=$template_type;
	
	$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
	if(isset($sc_exec['content_pos']))$ref[$main]['content_pos']=$sc_exec['content_pos'];
	
}

\aw2_library::add_service('templates.run','Run a Template of the Active Module',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );	
	unset($atts['main']);	
	$return_value=\aw2_library::template_run($main,$content,$atts);
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}
