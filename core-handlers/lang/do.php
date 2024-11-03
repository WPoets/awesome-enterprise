<?php
namespace aw2\_do;


\aw2_library::add_service('do','Do Library',['namespace'=>__NAMESPACE__]);

function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$pieces=$shortcode['tags'];
	if(count($pieces)!=2)die('error:You must have exactly two parts to the do shortcode');
	$ctr=$pieces[1];
	$stack_id=\aw2_library::push_child($ctr,$ctr);
	$call_stack=&\aw2_library::get_array_ref('call_stack',$stack_id);

	$return_value = \aw2_library::parse_shortcode($content);

	\aw2_library::pop_child($stack_id);	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


/*
function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$pieces=$shortcode['tags'];
	if(count($pieces)!=2)return 'error:You must have exactly two parts to the do shortcode';
	$ctr=$pieces[1];
	$stack_id=\aw2_library::push_child($ctr,$ctr);
	$call_stack=&\aw2_library::get_array_ref('call_stack',$stack_id);
	\aw2_library::add_service($ctr,'Added by do',['env_key'=>$ctr]);

	$return_value = \aw2_library::parse_shortcode($content);

	\aw2_library::pop_child($stack_id);	
	$ref=&\aw2_library::get_array_ref();
	if(!isset($ref[$ctr]))
		\aw2_library::remove_service($ctr);
		
		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}
*/
