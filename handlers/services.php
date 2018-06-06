<?php
namespace aw2\services;

\aw2_library::add_service('services.add','Add a New Service',['namespace'=>__NAMESPACE__]);

function add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	'desc'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['desc']);
	

	\aw2_library::add_service($main,$desc,$atts);
}


\aw2_library::add_service('services.remove','Remove a Service',['namespace'=>__NAMESPACE__]);

function remove($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	'desc'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['desc']);
	

	\aw2_library::remove_service($main,$desc,$atts);
}


