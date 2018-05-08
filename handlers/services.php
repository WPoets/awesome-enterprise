<?php
namespace aw2\services;

\aw2_library::add_service('services.add','Add a New Service',['namespace'=>'aw2\services']);

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



namespace aw2\s1;

function test1($atts,$content=null,$shortcode){
	return 'hello';
}

