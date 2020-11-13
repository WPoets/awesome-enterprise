<?php
namespace aw2\content_types;

\aw2_library::add_service('content_types.add','Add a New Content Type',['namespace'=>'aw2\content_types']);
function add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'desc'=>null,
	'module'=>null,
	'post_type'=>null,
	'template'=>null,
	), $atts) );
	
	unset($atts['main']);
	unset($atts['desc']);
	
	$content_types=&\aw2_library::get_array_ref('content_types');
	$content_types[$main]=array();
	$atts['content_type_def']=true;
	$atts['content_type']=$main;
	\aw2_library::add_service($main,$desc,$atts);

	if($module && $post_type)
		\aw2_library::module_run(["post_type"=>$post_type],$module,$template);
	
	return;
}

