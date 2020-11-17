<?php

namespace aw2\encode;

\aw2_library::add_service('encode.src','URL encode the input and return',['namespace'=>__NAMESPACE__]);
function src($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );
	
	//**resolve the dynamic variables**//
	$main = \aw2_library::resolve_chain($main);
	
	//**encode the url**//
	$return_value=urlencode($main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('encode.class','Encode the input and return class',['func'=>'_class','namespace'=>__NAMESPACE__]);
function _class($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	//**resolve the dynamic variables**//
	$main = \aw2_library::resolve_chain($main);
	
	//**encode the html**//
	$return_value='class="'.htmlentities($main).'"';
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('encode.id','Encode the input and return id',['namespace'=>__NAMESPACE__]);
function id($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );
		
	//**resolve the dynamic variables**//
	$main = \aw2_library::resolve_chain($main);
	
	//**replace all chars except allowed**//
	$main=preg_replace('/[^A-Za-z0-9_-]/','',$main);
	
	//**encode the html**//
	$return_value='id="'.htmlentities($main).'"';
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('encode.atts','Loop through the atts and return appended string',['namespace'=>__NAMESPACE__]);
function atts($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );
		
	$return_value="";
	
	if(is_array($atts)){
		foreach($atts as $attr => $value){
			$return_value .= $attr.'="'.htmlentities($value).'" ';
		}
	}
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('encode.value','Encode Value',['namespace'=>__NAMESPACE__]);
function value($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );
	
	$main = \aw2_library::resolve_chain($main);	
	$return_value =' value="' . htmlentities($main) . '"';
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('encode.str','Purify the HTML and return',['namespace'=>__NAMESPACE__]);
function str($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );
	
	//**resolve the dynamic variables**//
	$main = \aw2_library::resolve_chain($main);
	
	/** Include htmlpurifier **/
	//$path = \aw2_library::$plugin_path . "libraries";
	//require_once $path . '/htmlpurifier/library/HTMLPurifier.auto.php';
	
	//**Purify the HTML**//
	$config = \HTMLPurifier_Config::createDefault();
	$purifier = new \HTMLPurifier($config);
	$return_value = $purifier->purify($main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('encode.options','',['namespace'=>__NAMESPACE__]);
function options($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );
	
	//**resolve the dynamic variables**//
	$main = \aw2_library::resolve_chain($main);
	
	/** Include htmlpurifier **/
	//$path = \aw2_library::$plugin_path . "libraries";
	//require_once $path . '/htmlpurifier/library/HTMLPurifier.auto.php';
	
	$config = \HTMLPurifier_Config::createDefault();
	$purifier = new \HTMLPurifier($config);
	$return_value = $purifier->purify($main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}