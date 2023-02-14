<?php
namespace aw2\services;

\aw2_library::add_service('services.add','Add a New Service',['namespace'=>__NAMESPACE__]);

function add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'desc'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['desc']);

	$atts['service_id']=$main;

	if(!isset($atts['connection']))$atts['connection']='#default';

	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='services';
		$live_debug_event['action']='services.add';
		$live_debug_event['service_id']=$atts['service_id'];
		$live_debug_event['atts']=$atts;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event]);
	}	

	\aw2_library::add_service($main,$desc,$atts);
	
}


