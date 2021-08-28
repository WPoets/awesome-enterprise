<?php

//////// CSS Library ///////////////////

namespace aw2\css;

\aw2_library::add_service('css','CSS Handler',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('less','LESS Compiler',['namespace'=>__NAMESPACE__]);
function less($atts,$content=null,$shortcode){
	//require_once (AWESOME_PATH . "/libraries/wp-less/wp-less.php");
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$string=\aw2_library::parse_shortcode($content);
	$less = new \lessc;
	$less_variables=\aw2_library::get('css.less_variables');
	
	$return_value = $less->compile($less_variables .' '.$string);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('minify','Minify CSS and return',['namespace'=>__NAMESPACE__]);
function minify($atts,$content=null,$shortcode){
	/* $path =AWESOME_PATH . "/libraries";
	require_once $path . '/minify2/Minify.php';
	require_once $path . '/minify2/CSS.php';
	require_once $path . '/minify2/JS.php';
	require_once $path . '/minify2/Exception.php';
	require_once $path . '/minify2/Exceptions/BasicException.php';
	require_once $path . '/minify2/Exceptions/FileImportException.php';
	require_once $path . '/minify2/Exceptions/IOException.php';
	require_once $path . '/minify2/path-converter/ConverterInterface.php';
	require_once $path . '/minify2/path-converter/Converter.php'; */
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$string=\aw2_library::parse_shortcode($content);
	
	$minifier = new \MatthiasMullie\Minify\CSS();
	$minifier->add($string);
	
	$return_value = $minifier->minify();
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('style','Output CSS Style',['namespace'=>__NAMESPACE__]);
function style($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$return_value = less($atts,$content,$shortcode);
	$return_value = minify($atts,$return_value,$shortcode);
	$return_value = '<style>'.$return_value.'</style>';
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}