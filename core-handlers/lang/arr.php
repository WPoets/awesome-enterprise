<?php

namespace aw2\arr;

\aw2_library::add_service('arr','Array Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('arr.is.arr', 'Check if the value is an array', ['func'=>'is_arr', 'namespace'=>__NAMESPACE__]);
function is_arr($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_arr'));
    return is_array($main);
}

\aw2_library::add_service('arr.is.not_arr', 'Check if the value is not an array', ['func'=>'is_not_arr', 'namespace'=>__NAMESPACE__]);
function is_not_arr($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_arr'));
    return !is_array($main);
}


\aw2_library::add_service('arr.set','Set a value in an array',['namespace'=>__NAMESPACE__]);
function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	unset($atts['main']);
	
	foreach ($atts as $loopkey => $loopvalue) {
		$arr[$loopkey]=$loopvalue;
	}	
	\aw2_library::set($main,$arr);
	return;
}

\aw2_library::add_service('arr.create','Build an array',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'path'=>null,
	'raw_content'=>null
	), $atts) );
	
	if(!is_null($path))$content=\aw2_library::get($path);

	if(!is_null($raw_content))$content=$raw_content;
	
	$ab=new \array_builder();
	$return_value=$ab->parse($content);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('arr.empty','Empty array',['func'=>'_empty','namespace'=>__NAMESPACE__]);
function _empty($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	$return_value=array();
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('arr.search_deep','Allows you to search for a value in an array of arrays or an array of objects, and return the key of the value that matches the search criteria.',['namespace'=>__NAMESPACE__]);
function search_deep($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'search'=>null,
	'field'=>FALSE
	), $atts, 'aw2_get' ) );
	
	if(is_null($main) || is_null($search)) return ;
	
	$arr_to_search = \aw2_library::get($main);
	
	$return_value =\util::array_search_deep($arr_to_search, $search,$field);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('arr.unshift','Empty array',['namespace'=>__NAMESPACE__]);
function unshift($atts,$content=null,$shortcode){
	
    if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'array_path' => null,
        'values' => array(),
        ), $atts) );

    $arr=\aw2_library::get($array_path);  
    array_unshift($arr,$values);     
    $return_value=$arr;
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
    return $return_value;
}
