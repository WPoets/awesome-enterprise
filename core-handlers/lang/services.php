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
	

	\aw2_library::add_service($main,$desc,$atts);
}


