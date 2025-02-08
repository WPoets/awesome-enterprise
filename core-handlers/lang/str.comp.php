<?php
namespace aw2\str;

\aw2_library::add_service('str.comp.eq', 'Check if string equals another', ['func'=>'comp_eq', 'namespace'=>__NAMESPACE__]);
function comp_eq($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_eq'));
    
    if(!is_string($main) || !is_string($with))
        throw new \InvalidArgumentException('str.comp.eq: both values must be strings. Use str: prefix for typecasting.');
    
    return $main === $with;
}

\aw2_library::add_service('str.comp.neq', 'Check if string not equals another', ['func'=>'comp_neq', 'namespace'=>__NAMESPACE__]);
function comp_neq($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_neq'));
    
    if(!is_string($main) || !is_string($with))
        throw new \InvalidArgumentException('str.comp.neq: both values must be strings. Use str: prefix for typecasting.');
    
    return $main !== $with;
}

\aw2_library::add_service('str.comp.lte', 'Check if string is less than or equal to another', ['func'=>'comp_lte', 'namespace'=>__NAMESPACE__]);
function comp_lte($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_lte'));
    
    if(!is_string($main) || !is_string($with))
        throw new \InvalidArgumentException('str.comp.lte: both values must be strings. Use str: prefix for typecasting.');
    
    return strcmp($main, $with) <= 0;
}

\aw2_library::add_service('str.comp.gte', 'Check if string is greater than or equal to another', ['func'=>'comp_gte', 'namespace'=>__NAMESPACE__]);
function comp_gte($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_gte'));
    
    if(!is_string($main) || !is_string($with))
        throw new \InvalidArgumentException('str.comp.gte: both values must be strings. Use str: prefix for typecasting.');
    
    return strcmp($main, $with) >= 0;
}

\aw2_library::add_service('str.comp.lt', 'Check if string is less than another', ['func'=>'comp_lt', 'namespace'=>__NAMESPACE__]);
function comp_lt($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_lt'));
    
    if(!is_string($main) || !is_string($with))
        throw new \InvalidArgumentException('str.comp.lt: both values must be strings. Use str: prefix for typecasting.');
    
    return strcmp($main, $with) < 0;
}

\aw2_library::add_service('str.comp.gt', 'Check if string is greater than another', ['func'=>'comp_gt', 'namespace'=>__NAMESPACE__]);
function comp_gt($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_gt'));
    
    if(!is_string($main) || !is_string($with))
        throw new \InvalidArgumentException('str.comp.gt: both values must be strings. Use str: prefix for typecasting.');
    
    return strcmp($main, $with) > 0;
}