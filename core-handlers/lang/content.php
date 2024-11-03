<?php
namespace aw2\content;

\aw2_library::add_service('content','Content Library',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('content.get','Get the raw content',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$return_value=$content;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('content.run','Run the content',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	$return_value=\aw2_library::parse_shortcode($content) ;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}