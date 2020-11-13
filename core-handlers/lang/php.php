<?php
namespace aw2\php;


\aw2_library::add_service('php','PHP Library',['namespace'=>__NAMESPACE__]);

function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	$pieces=$shortcode['tags'];
	if(count($pieces)!=2)return 'error:You must have exactly two parts to the php shortcode';
	$fname=$pieces[1];
	
	$parameters = array();
	$i=1;
	$found=true;
	while ($found==true) {
		$pname='p' . strval($i);
		if(isset($atts[$pname])){
			array_push($parameters,$atts[$pname]);
			unset($atts[$pname]);
			$i++;
		}
		else{
			$found=false;
		}
	}	

	$return_value=call_user_func_array($fname, $parameters);	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;

}

\aw2_library::add_service('php.site_url','Return the site url constant',['namespace'=>__NAMESPACE__]);

function site_url($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	$return_value=SITE_URL;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
