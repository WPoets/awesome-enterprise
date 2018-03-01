<?php


aw2_library::add_library('content','Content Library');


function aw2_content_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$return_value=$content;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_content_run($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	$return_value=aw2_library::parse_shortcode($content) ;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

