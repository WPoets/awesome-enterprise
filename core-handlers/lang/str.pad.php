<?php
namespace aw2\str;

\aw2_library::add_service('str.pad.left', 'Pad string from left', ['func'=>'pad_left', 'namespace'=>__NAMESPACE__]);
function pad_left($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'length' => null,
        'pad_string' => ' ' // Default to space if not specified
    ), $atts, 'pad_left'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.pad.left: main must be a string value. Use str: prefix for typecasting.');
    }
    
    if(!is_int($length) || $length < 0) {
        throw new \InvalidArgumentException('str.pad.left: length must be a non-negative integer.');
    }

    if(!is_string($pad_string) || empty($pad_string)) {
        throw new \InvalidArgumentException('str.pad.left: pad_string must be a non-empty string.');
    }
    
    return str_pad($main, $length, $pad_string, STR_PAD_LEFT);
}

\aw2_library::add_service('str.pad.right', 'Pad string from right', ['func'=>'pad_right', 'namespace'=>__NAMESPACE__]);
function pad_right($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'length' => null,
        'pad_string' => ' ' // Default to space if not specified
    ), $atts, 'pad_right'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.pad.right: main must be a string value. Use str: prefix for typecasting.');
    }
    
    if(!is_int($length) || $length < 0) {
        throw new \InvalidArgumentException('str.pad.right: length must be a non-negative integer.');
    }

    if(!is_string($pad_string) || empty($pad_string)) {
        throw new \InvalidArgumentException('str.pad.right: pad_string must be a non-empty string.');
    }
    
    return str_pad($main, $length, $pad_string, STR_PAD_RIGHT);
}

\aw2_library::add_service('str.pad.both', 'Pad string from both sides', ['func'=>'pad_both', 'namespace'=>__NAMESPACE__]);
function pad_both($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'length' => null,
        'pad_string' => ' ' // Default to space if not specified
    ), $atts, 'pad_both'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.pad.both: main must be a string value. Use str: prefix for typecasting.');
    }
    
    if(!is_int($length) || $length < 0) {
        throw new \InvalidArgumentException('str.pad.both: length must be a non-negative integer.');
    }

    if(!is_string($pad_string) || empty($pad_string)) {
        throw new \InvalidArgumentException('str.pad.both: pad_string must be a non-empty string.');
    }
    
    return str_pad($main, $length, $pad_string, STR_PAD_BOTH);
}