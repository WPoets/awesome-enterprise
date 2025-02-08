<?php

namespace aw2\_bool;

\aw2_library::add_service('bool','Boolean Functions',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('bool.is.bool', 'Check if the value is a boolean', ['func'=>'_is_bool', 'namespace'=>__NAMESPACE__]);
function _is_bool($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_bool'));
    return is_bool($main);
}

\aw2_library::add_service('bool.is.not_bool', 'Check if the value is not a boolean', ['func'=>'is_not_bool', 'namespace'=>__NAMESPACE__]);
function is_not_bool($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_bool'));
    return !is_bool($main);
}

\aw2_library::add_service('bool.get','Returns value as a Boolean',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>false
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(bool)$return_value;	
	if($return_value===false)$return_value=(bool)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('bool.create','Create & return value as a Boolean',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	if($main==='true')
		$return_value=true;
	else{
		if($main==='false'){
			$return_value=false;
		}		
		else{
			$return_value=(bool)$main;
		} 
	}
		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('bool.is.true', 'Check if a boolean value is true', ['func'=>'is_true', 'namespace'=>__NAMESPACE__]);
function is_true($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_true'));
    
    if($main === null || !is_bool($main)) {
        return \aw2_library::set_error('bool.is.true: main must be a boolean value. Use bool: prefix for typecasting.');
    }
    
    return $main === true;
}

\aw2_library::add_service('bool.is.false', 'Check if a boolean value is false', ['func'=>'is_false', 'namespace'=>__NAMESPACE__]);
function is_false($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_false'));
    
    if($main === null || !is_bool($main)) {
        return \aw2_library::set_error('bool.is.false: main must be a boolean value. Use bool: prefix for typecasting.');
    }
    
    return $main === false;
}

\aw2_library::add_service('bool.to.str', 'Convert boolean value to string representation', ['func'=>'bool_to_str', 'namespace'=>__NAMESPACE__]);

function bool_to_str($atts, $content=null, $shortcode=null) {
    if(\aw2_library::pre_actions('all', $atts, $content)==false) return;
    
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'bool_to_str'));
    
    if($main === null || !is_bool($main)) {
        throw new \Exception('bool.to.str: main must be a boolean value. Use bool: prefix for typecasting.');
    }
    
    return $main ? 'true' : 'false';
}