<?php
namespace aw2\code;

\aw2_library::add_service('code','Code Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('code.run','Run the Code Library',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=\aw2_library::parse_shortcode($return_value);	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('code.dump','Run the Code Library',['namespace'=>__NAMESPACE__]);
function dump($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$return_value='<code>' . $content . '</code>';	
	return $return_value;
}