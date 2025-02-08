<?php
namespace aw2\arr;

\aw2_library::add_service('arr.join.comma', 'Join array elements with commas', ['func'=>'join_comma', 'namespace'=>__NAMESPACE__]);
function join_comma($atts, $content=null, $shortcode=null) {
    $main = \aw2\common\build_array($atts, $content);
    return implode(',', $main);
}

\aw2_library::add_service('arr.join.dot', 'Join array elements with dots', ['func'=>'join_dot', 'namespace'=>__NAMESPACE__]);
function join_dot($atts, $content=null, $shortcode=null) {
    $main = \aw2\common\build_array($atts, $content);
    return implode('.', $main);
}

\aw2_library::add_service('arr.join.space', 'Join array elements with single space', ['func'=>'join_space', 'namespace'=>__NAMESPACE__]);
function join_space($atts, $content=null, $shortcode=null) {
    $main = \aw2\common\build_array($atts, $content);
    return implode(' ', $main);
}

\aw2_library::add_service('arr.join.separator', 'Join array elements with custom separator', ['func'=>'join_separator', 'namespace'=>__NAMESPACE__]);
function join_separator($atts, $content=null, $shortcode=null) {
    if(!isset($atts['separator'])) {
        throw new \InvalidArgumentException('arr.join.separator: separator attribute is required.');
    }
    
    if(!is_string($atts['separator'])) {
        throw new \InvalidArgumentException('arr.join.separator: separator must be a string value.');
    }
    
    $main = \aw2\common\build_array($atts, $content);
    return implode($atts['separator'], $main);
}