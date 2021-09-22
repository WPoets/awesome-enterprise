<?php 
namespace aw2\register;

\aw2_library::add_service('register.less_variables','Handles the registration of ctp, less variables etc.',['namespace'=>__NAMESPACE__]);

function less_variables($atts,$content=null,$shortcode){
		
		$less_variables=\aw2_library::get('css.less_variables');
		
		$args = \aw2_library::parse_shortcode(trim($content));
		$less_variables = $less_variables .' '.$args;
		
		\aw2_library::set('css.less_variables',$less_variables );		
		
}