<?php
namespace aw2\package;

\aw2_library::add_service('package.require','Require a Library',['func'=>'_require' ,'namespace'=>__NAMESPACE__]);


function _require($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'local_path' => null,
		), $atts) );
	
	if(!empty($local_path)){

		foreach (glob($local_path . "/handlers/*.php") as $filename)
		{
			require_once $filename;
		}
		
		
		foreach (glob($local_path . "/modules/*.module.html") as $filename)
		{
			$collection=array();
			$collection['source']=$local_path."/modules";
			$module=basename($filename);
			$module=str_replace(".module.html","",$module);
			$template=null;

			$return_value=\aw2_library::module_run($collection,$module,$template,null,[]);
		}
	}	
}	 


\aw2_library::add_service('package.run','Run a Library',['namespace'=>__NAMESPACE__]);


function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'local_path' => null,
		), $atts) );
	
	foreach (glob($local_path . "/modules/*.module.html") as $filename)
	{
		$collection=array();
		$collection['source']=$local_path."/modules";
		$module=basename($filename);
		$module=str_replace(".module.html","",$module);
		$template=null;

		$return_value=\aw2_library::module_run($collection,$module,$template,null,[]);
	}
}	