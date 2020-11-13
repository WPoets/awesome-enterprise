<?php
namespace aw2\active_template;

\aw2_library::add_service('@template','Handles the active template',['env_key'=>'@template']);




\aw2_library::add_service('@template.return','End the active template',['func'=>'_return' , 'namespace'=>__NAMESPACE__]);
function _return($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'service'=>null
	), $atts) );
	
	if($service){
		$return_value=\aw2_library::service_run($service,$atts,$content);		
	}
	else
	$return_value=\aw2_library::get($main,$atts,$content);

	\aw2_library::set('_return',true);	
	\aw2_library::set('@template._return',$return_value);
	return;
}
