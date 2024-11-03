<?php
namespace aw2\ref;

\aw2_library::add_service('ref.add','Add a New Reference',['namespace'=>__NAMESPACE__]);

function add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'ref_to'=>null
	), $atts) );
	

	\aw2_library::add_ref($main,$ref_to);
}


