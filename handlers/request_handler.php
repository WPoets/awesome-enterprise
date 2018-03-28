<?php
aw2_library::add_library('request','Request Library');
//aw2_library::register_service('request',['prefix'=>'aw2'],'Request Handler');


function aw2_request_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts) );
	
	$return_value=aw2_library::get_request($main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_request_unhandled($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	$main=implode(".",$pieces);	
	$return_value=aw2_library::get_request($main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


function aw2_request_dump($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts) );

	$return_value=aw2_library::get_request($main);
	$return_value=util::var_dump($return_value,true);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

function aw2_request_echo($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract( shortcode_atts( array(
	'main'=>null,
	), $atts) );

	$return_value=aw2_library::get_request($main);
	util::var_dump($return_value);
	return;
}
