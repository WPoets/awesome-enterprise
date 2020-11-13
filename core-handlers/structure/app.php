<?php
namespace aw2\app;

\aw2_library::add_service('app','Handles the active app',['env_key'=>'app']);


\aw2_library::add_service('app.run','Run the active module of the current app',['namespace'=>__NAMESPACE__]);

function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'module'=>null,
		'template'=>null
		), $atts) );
	if(!$main)return 'app not defined';
	unset($atts['main']);
	
	if($main==='active_module'){
		$ref=\aw2_library::get_array_ref('app','active');
		$return_value=\aw2_library::module_run($ref['collection'],$ref['module'],$ref['template'],$content,$atts);
	}	

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

//not sure how to use it
function aw2_app_return($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value=\aw2_library::get($main,$atts,$content);
	\aw2_library::set('_return',true);	
	\aw2_library::set('app._return',$return_value);
	return;
}

