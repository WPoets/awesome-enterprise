<?php
namespace aw2\str;

\aw2_library::add_service('str','String Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('str.get','Returns value as a String',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(string)$return_value;	
	
	if($return_value==='')$return_value=(string)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('str.create','Create & return value as a String',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(string)$main;	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}





\aw2_library::add_service('str.is.str', 'Check if the value is a string', ['func'=>'is_str', 'namespace'=>__NAMESPACE__]);
function is_str($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_str'));
    
    return is_string($main);
}

\aw2_library::add_service('str.is.not_str', 'Check if the value is not a string', ['func'=>'is_not_str', 'namespace'=>__NAMESPACE__]);
function is_not_str($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_str'));
    
    return !is_string($main);
}

\aw2_library::add_service('str.is.yes', 'Check if the string is literally "yes"', ['func'=>'is_yes', 'namespace'=>__NAMESPACE__]);
function is_yes($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_yes'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.yes: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return $main === 'yes';
}

\aw2_library::add_service('str.is.no', 'Check if the string is literally "no"', ['func'=>'is_no', 'namespace'=>__NAMESPACE__]);
function is_no($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_no'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.no: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return $main === 'no';
}

\aw2_library::add_service('str.is.empty', 'Check if the string is empty', ['func'=>'is_empty', 'namespace'=>__NAMESPACE__]);
function is_empty($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_empty'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.empty: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return $main === '';
}

\aw2_library::add_service('str.is.whitespace', 'Check if the string is empty or contains only whitespace', ['func'=>'is_whitespace', 'namespace'=>__NAMESPACE__]);
function is_whitespace($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_whitespace'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.whitespace: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return trim($main) === '';
}


\aw2_library::add_service('str.is.not_whitespace', 'Check if the string is not empty and contains non-whitespace characters', ['func'=>'is_not_whitespace', 'namespace'=>__NAMESPACE__]);
function is_not_whitespace($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_whitespace'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.not_whitespace: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return trim($main) !== '';
}

\aw2_library::add_service('str.is.not_empty', 'Check if the string is not empty', ['func'=>'is_not_empty', 'namespace'=>__NAMESPACE__]);
function is_not_empty($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_empty'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.not_empty: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return $main !== '';
}

\aw2_library::add_service('str.is.in', 'Check if a string is in another string or content', ['func'=>'is_in', 'namespace'=>__NAMESPACE__]);
function is_in($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'haystack' => null
    ), $atts, 'is_in'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.in: main (needle) must be a string value. Use str: prefix for typecasting.');
    }
    
    if($haystack === null && $content === null) {
        throw new \InvalidArgumentException('str.is.in: Either haystack attribute or content must be provided.');
    }
    
    if($haystack !== null) {
        if(!is_string($haystack)) {
            throw new \InvalidArgumentException('str.is.in: haystack must be a string value. Use str: prefix for typecasting.');
        }
        return strpos($haystack, $main) !== false;
    }
    
    // If haystack is not provided, use parsed content
    $parsed_content = \aw2_library::parse_shortcode($content);
    return strpos($parsed_content, $main) !== false;
}

\aw2_library::add_service('str.is.not_in', 'Check if a string is not in another string or content', ['func'=>'is_not_in', 'namespace'=>__NAMESPACE__]);
function is_not_in($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'haystack' => null
    ), $atts, 'is_not_in'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.is.not_in: main (needle) must be a string value. Use str: prefix for typecasting.');
    }
    
    if($haystack === null && $content === null) {
        throw new \InvalidArgumentException('str.is.not_in: Either haystack attribute or content must be provided.');
    }
    
    if($haystack !== null) {
        if(!is_string($haystack)) {
            throw new \InvalidArgumentException('str.is.not_in: haystack must be a string value. Use str: prefix for typecasting.');
        }
        return strpos($haystack, $main) === false;
    }
    
    // If haystack is not provided, use parsed content
    $parsed_content = \aw2_library::parse_shortcode($content);
    return strpos($parsed_content, $main) === false;
}



