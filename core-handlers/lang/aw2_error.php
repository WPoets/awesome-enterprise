<?php

namespace aw2\aw2_error;

\aw2_library::add_service('aw2_error.create','Create a new error',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'message'=>'',
	'error_code'=>''
	), $atts) );

		$obj=new \aw2_error();
		$obj->message=$message;
		$obj->error_code=$error_code;
		return $obj;
		
}