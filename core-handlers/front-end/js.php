<?php
namespace aw2\js;

\aw2_library::add_service('js','JS Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('js.minify','Minify JS',['namespace'=>__NAMESPACE__]);

function minify($atts,$content=null,$shortcode){
	
	$string=\aw2_library::parse_shortcode($content);
	

	$path = \aw2_library::$plugin_path . "/libraries";
	require_once $path . '/minify2/Minify.php';
	require_once $path . '/minify2/CSS.php';
	require_once $path . '/minify2/JS.php';
	require_once $path . '/minify2/Exception.php';
	require_once $path . '/minify2/Exceptions/BasicException.php';
	require_once $path . '/minify2/Exceptions/FileImportException.php';
	require_once $path . '/minify2/Exceptions/IOException.php';
	require_once $path . '/minify2/path-converter/ConverterInterface.php';
	require_once $path . '/minify2/path-converter/Converter.php';
	
	$minifier = new \MatthiasMullie\Minify\JS();
	$minifier->add($string);
	
	$return_value = $minifier->minify();
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
