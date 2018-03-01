<?php

//////// Script Collection ///////////////////
aw2_library::add_library('script','Script Collection');

function aw2_script_minify($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;

	require_once (aw2_library::$plugin_path . "/libraries/minify/minify.php");
	$return_value=minify_js(aw2_library::parse_shortcode($content));
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;		
}