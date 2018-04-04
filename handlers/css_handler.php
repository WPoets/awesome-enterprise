<?php

//////// CSS Library ///////////////////
aw2_library::add_library('css','CSS Handler');

function aw2_css_less($atts,$content=null,$shortcode){
	require_once (aw2_library::$plugin_path . "/libraries/wp-less/wp-less.php");
	$string=aw2_library::parse_shortcode($content);
	$less = new lessc;
	$less_variables=aw2_library::get('less_variables');
	
	$return_value = $less->compile($less_variables .' '.$string);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
function aw2_css_minify($atts,$content=null,$shortcode){
	//require_once (aw2_library::$plugin_path . "/libraries/minify2/Minify.php");
	//require_once (aw2_library::$plugin_path . "/libraries/minify2/CSS.php");
	
	$path = aw2_library::$plugin_path . "/libraries";
	require_once $path . '/minify2/Minify.php';
	require_once $path . '/minify2/CSS.php';
	require_once $path . '/minify2/JS.php';
	require_once $path . '/minify2/Exception.php';
	require_once $path . '/minify2/Exceptions/BasicException.php';
	require_once $path . '/minify2/Exceptions/FileImportException.php';
	require_once $path . '/minify2/Exceptions/IOException.php';
	require_once $path . '/minify2/path-converter/ConverterInterface.php';
	require_once $path . '/minify2/path-converter/Converter.php';
	
	
	$string=aw2_library::parse_shortcode($content);
	
	$minifier = new \MatthiasMullie\Minify\CSS();
	$minifier->add($string);
	
	$return_value = $minifier->minify();
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

function aw2_css_style($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$return_value = aw2_css_less($atts,$content,$shortcode);
	$return_value = '<style>'.$return_value.'</style>';
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}