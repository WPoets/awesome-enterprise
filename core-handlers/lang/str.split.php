<?php
namespace aw2\str;

\aw2_library::add_service('str.split.comma', 'Split string on commas', ['func'=>'split_comma', 'namespace'=>__NAMESPACE__]);
function split_comma($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'split_comma'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.split.comma: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return explode(',', $main);
}

\aw2_library::add_service('str.split.dot', 'Split string on dots', ['func'=>'split_dot', 'namespace'=>__NAMESPACE__]);
function split_dot($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'split_dot'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.split.dot: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return explode('.', $main);
}

\aw2_library::add_service('str.split.ws', 'Split string on whitespace', ['func'=>'split_ws', 'namespace'=>__NAMESPACE__]);
function split_ws($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'split_ws'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.split.ws: main must be a string value. Use str: prefix for typecasting.');
    }
    
    // Split on any number of whitespace characters
    return preg_split('/\s+/', trim($main));
}

\aw2_library::add_service('str.split.separator', 'Split string on custom separator', ['func'=>'split_separator', 'namespace'=>__NAMESPACE__]);
function split_separator($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'separator' => null
    ), $atts, 'split_separator'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.split.separator: main must be a string value. Use str: prefix for typecasting.');
    }
    
    if(!is_string($separator)) {
        throw new \InvalidArgumentException('str.split.separator: separator must be a string value.');
    }
    
    if(empty($separator)) {
        throw new \InvalidArgumentException('str.split.separator: separator cannot be empty.');
    }
    
    return explode($separator, $main);
}