<?php


aw2_library::add_library('code','Code Library');


function aw2_code_run($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=aw2_library::get($main,$atts,$content);
	$return_value=aw2_library::parse_shortcode($return_value);	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}