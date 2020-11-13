<?php
namespace aw2\library;

\aw2_library::add_service('library.require','Require a Library',['func'=>'_require' ,'namespace'=>__NAMESPACE__]);


function _require($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'local_path' => null,
		), $atts) );
	

	foreach (glob($local_path . "/*.php") as $filename)
	{
		require_once $filename;
	}
	
	
	foreach (glob($local_path . "/*.module.html") as $filename)
	{
		$collection=array();
		$collection['source']=$local_path;
		$module=basename($filename);
		$template=null;

		$return_value=\aw2_library::module_run($collection,$module,$template,null,[]);
	}
}	 


\aw2_library::add_service('library.run','Run a Library',['namespace'=>__NAMESPACE__]);


function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'local_path' => null,
		), $atts) );
	
	foreach (glob($local_path . "/*.module.html") as $filename)
	{
		$collection=array();
		$collection['source']=$local_path;
		$module=basename($filename);
		$template=null;

		$return_value=\aw2_library::module_run($collection,$module,$template,null,[]);
	}
}	