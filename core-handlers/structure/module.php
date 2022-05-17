<?php
namespace aw2\module;

\aw2_library::add_service('module','Handles the active module',['env_key'=>'module']);


\aw2_library::add_service('module.register','Register an arbitrary module',['namespace'=>__NAMESPACE__]);

function register($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;


	$atts=\aw2_library::split_array_on($atts,'collection');	

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'desc'=>null
	), $atts) );

	unset($atts['main']);
	unset($atts['desc']);
	
	\aw2_library::add_service($main,$desc,$atts);

}




\aw2_library::add_service('module.run','Run an arbitrary module',['namespace'=>__NAMESPACE__]);

function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'template'=>null
	), $atts) );
	unset($atts['main']);
	unset($atts['template']);
	
	$return_value=\aw2_library::module_forced_run($atts,$main,$template,$content,$atts);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('module.include','Include an arbitrary module',['func'=>'_include','namespace'=>__NAMESPACE__]);

function _include($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	unset($atts['main']);
	$return_value=\aw2_library::module_include($atts,$main);
	return $return_value;
}

\aw2_library::add_service('module.return','Return an active module',['func'=>'_return','namespace'=>__NAMESPACE__]);

function _return($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	\aw2_library::set('_return',true);	
	\aw2_library::set('module._return',$return_value);
	return;
}




