<?php
namespace aw2\str;

\aw2_library::add_service('str.trim.left', 'Trim whitespace from left of string', ['func'=>'trim_left', 'namespace'=>__NAMESPACE__]);
function trim_left($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'trim_left'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.trim.left: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return ltrim($main);
}

\aw2_library::add_service('str.trim.right', 'Trim whitespace from right of string', ['func'=>'trim_right', 'namespace'=>__NAMESPACE__]);
function trim_right($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'trim_right'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.trim.right: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return rtrim($main);
}

\aw2_library::add_service('str.trim.both', 'Trim whitespace from both sides of string', ['func'=>'trim_both', 'namespace'=>__NAMESPACE__]);
function trim_both($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'trim_both'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.trim.both: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return trim($main);
}