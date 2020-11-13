<?php

namespace aw2\request;

\aw2_library::add_service('request','Request Library',['namespace'=>__NAMESPACE__]);
function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	$main=implode(".",$pieces);	
	$return_value=\aw2_library::get_request($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('get','Get the request from URL',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts) );
	
	$return_value=\aw2_library::get_request($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('dump','Get the request from URL and dump',['namespace'=>__NAMESPACE__]);
function dump($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts) );

	$return_value=\aw2_library::get_request($main);
	$return_value=\util::var_dump($return_value,true);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

\aw2_library::add_service('echo','Echo the request from URL',['func'=>'_echo','namespace'=>__NAMESPACE__]);
function _echo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts) );

	$return_value=\aw2_library::get_request($main);
	\util::var_dump($return_value);
	return;
}
