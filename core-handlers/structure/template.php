<?php
namespace aw2\template;

\aw2_library::add_service('template','Handles the active template',['env_key'=>'template']);

\aw2_library::add_service('template.anon.run','Run an arbitrary template',['func'=>'anon_run','namespace'=>__NAMESPACE__]);
function anon_run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
	), $atts) );
	if(!$main)return 'Template Path not defined';
	unset($atts['main']);
	
	$template_content=\aw2_library::get($main);
	$return_value=\aw2_library::template_anon_run($template_content,$content,$atts);

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

\aw2_library::add_service('template.run','Run an arbitrary template',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'module'=>null
	), $atts) );
	if(!$main)return 'Template not defined';
	unset($atts['main']);
	unset($atts['module']);
	
	$return_value=\aw2_library::module_run($atts,$module,$main,$content,$atts);

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

\aw2_library::add_service('template.return','End the active template',['func'=>'_return' , 'namespace'=>__NAMESPACE__]);

function _return($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'service'=>null
	), $atts) );
	
	if($service){
		$return_value=\aw2_library::service_run($service,$atts,$content);		
	}
	else
	$return_value=\aw2_library::get($main,$atts,$content);

	\aw2_library::set('_return',true);	
	\aw2_library::set('template._return',$return_value);
	return;
}

/*
//////// Template Library ///////////////////
\aw2_library::add_library('template','Template Functions');

function aw2_template_get($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_template_unhandled($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	$atts['main']=implode(".",$pieces);		
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_template_set($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_set($atts,$content,$shortcode);
}


function aw2_template_set_raw($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_set_raw($atts,$content,$shortcode);
}

function aw2_template_set_array($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_set_array($atts,$content,$shortcode);
}


function aw2_template_dump($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_dump($atts,$content,$shortcode);

}

function aw2_template_echo($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_echo($atts,$content,$shortcode);
}


*/
