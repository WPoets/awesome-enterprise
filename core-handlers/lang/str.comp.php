<?php
namespace aw2\str;


\aw2_library::add_service('str.comp.eq', 'Check if two strings are equal', ['func'=>'comp_eq', 'namespace'=>__NAMESPACE__]);
function comp_eq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_eq'));
    
    if($lhs === null || $rhs === null || !is_string($lhs) || !is_string($rhs)) {
        throw new \InvalidArgumentException('both the values must be string values.');
    }
    
    return $lhs === $rhs;
}

\aw2_library::add_service('str.comp.neq', 'Check if two strings are not equal', ['func'=>'comp_neq', 'namespace'=>__NAMESPACE__]);
function comp_neq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_neq'));
    
    if($lhs === null || $rhs === null || !is_string($lhs) || !is_string($rhs)) {
        throw new \InvalidArgumentException('str.comp.neq: both lhs and rhs must be string values. Use str: prefix for typecasting.');
    }
    
    return $lhs !== $rhs;
}

\aw2_library::add_service('str.comp.lte', 'Check if one string is less than or equal to another', ['func'=>'comp_lte', 'namespace'=>__NAMESPACE__]);
function comp_lte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lte'));
    
    if($lhs === null || $rhs === null || !is_string($lhs) || !is_string($rhs)) {
        throw new \InvalidArgumentException('str.comp.lte: both lhs and rhs must be string values. Use str: prefix for typecasting.');
    }
    
    return strcmp($lhs, $rhs) <= 0;
}

\aw2_library::add_service('str.comp.gte', 'Check if one string is greater than or equal to another', ['func'=>'comp_gte', 'namespace'=>__NAMESPACE__]);
function comp_gte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gte'));
    
    if($lhs === null || $rhs === null || !is_string($lhs) || !is_string($rhs)) {
        throw new \InvalidArgumentException('str.comp.gte: both lhs and rhs must be string values. Use str: prefix for typecasting.');
    }
    
    return strcmp($lhs, $rhs) >= 0;
}

\aw2_library::add_service('str.comp.lt', 'Check if one string is less than another', ['func'=>'comp_lt', 'namespace'=>__NAMESPACE__]);
function comp_lt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lt'));
    
    if($lhs === null || $rhs === null || !is_string($lhs) || !is_string($rhs)) {
        throw new \InvalidArgumentException('str.comp.lt: both lhs and rhs must be string values. Use str: prefix for typecasting.');
    }
    
    return strcmp($lhs, $rhs) < 0;
}

\aw2_library::add_service('str.comp.gt', 'Check if one string is greater than another', ['func'=>'comp_gt', 'namespace'=>__NAMESPACE__]);
function comp_gt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gt'));
    
    if($lhs === null || $rhs === null || !is_string($lhs) || !is_string($rhs)) {
        throw new \InvalidArgumentException('str.comp.gt: both lhs and rhs must be string values. Use str: prefix for typecasting.');
    }
    
    return strcmp($lhs, $rhs) > 0;
}