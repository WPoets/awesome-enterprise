<?php
namespace aw2\str;




\aw2_library::add_service('str.slice.left', 'Slice string from left', ['func'=>'slice_left', 'namespace'=>__NAMESPACE__]);
function slice_left($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'length' => null
    ), $atts, 'slice_left'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.slice.left: main must be a string value. Use str: prefix for typecasting.');
    }
    
    if(!is_int($length) || $length < 0) {
        throw new \InvalidArgumentException('str.slice.left: length must be a non-negative integer.');
    }
    
    return substr($main, 0, $length);
}

\aw2_library::add_service('str.slice.right', 'Slice string from right', ['func'=>'slice_right', 'namespace'=>__NAMESPACE__]);
function slice_right($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'length' => null
    ), $atts, 'slice_right'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.slice.right: main must be a string value. Use str: prefix for typecasting.');
    }
    
    if(!is_int($length) || $length < 0) {
        throw new \InvalidArgumentException('str.slice.right: length must be a non-negative integer.');
    }
    
    return substr($main, -$length);
}

\aw2_library::add_service('str.slice.mid', 'Slice string from middle', ['func'=>'slice_mid', 'namespace'=>__NAMESPACE__]);
function slice_mid($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'start' => null,
        'length' => null,
        'end' => null
    ), $atts, 'slice_mid'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.slice.mid: main must be a string value. Use str: prefix for typecasting.');
    }
    
    if(!is_int($start) || $start < 0) {
        throw new \InvalidArgumentException('str.slice.mid: start must be a non-negative integer.');
    }
    
    if($length !== null) {
        if(!is_int($length) || $length < 0) {
            throw new \InvalidArgumentException('str.slice.mid: length must be a non-negative integer.');
        }
        return substr($main, $start, $length);
    } elseif($end !== null) {
        if(!is_int($end) || $end < $start) {
            throw new \InvalidArgumentException('str.slice.mid: end must be an integer greater than or equal to start.');
        }
        return substr($main, $start, $end - $start + 1);
    } else {
        throw new \InvalidArgumentException('str.slice.mid: Either length or end must be provided.');
    }
}

