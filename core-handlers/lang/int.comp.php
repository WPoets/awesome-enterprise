<?php
namespace aw2\int;



\aw2_library::add_service('int.comp.eq', 'Check if two integers are equal', ['func'=>'comp_eq', 'namespace'=>__NAMESPACE__]);
function comp_eq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_eq'));
    
    if($lhs === null || $rhs === null || !is_int($lhs) || !is_int($rhs)) {
        throw new \InvalidArgumentException('int.comp.eq: both lhs and rhs must be integer values. Use int: prefix for typecasting.');
    }
    
    return $lhs === $rhs;
}

\aw2_library::add_service('int.comp.neq', 'Check if two integers are not equal', ['func'=>'comp_neq', 'namespace'=>__NAMESPACE__]);
function comp_neq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_neq'));
    
    if($lhs === null || $rhs === null || !is_int($lhs) || !is_int($rhs)) {
        throw new \InvalidArgumentException('int.comp.neq: both lhs and rhs must be integer values. Use int: prefix for typecasting.');
    }
    
    return $lhs !== $rhs;
}

\aw2_library::add_service('int.comp.lte', 'Check if one integer is less than or equal to another', ['func'=>'comp_lte', 'namespace'=>__NAMESPACE__]);
function comp_lte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lte'));
    
    if($lhs === null || $rhs === null || !is_int($lhs) || !is_int($rhs)) {
        throw new \InvalidArgumentException('int.comp.lte: both lhs and rhs must be integer values. Use int: prefix for typecasting.');
    }
    
    return $lhs <= $rhs;
}

\aw2_library::add_service('int.comp.gte', 'Check if one integer is greater than or equal to another', ['func'=>'comp_gte', 'namespace'=>__NAMESPACE__]);
function comp_gte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gte'));
    
    if($lhs === null || $rhs === null || !is_int($lhs) || !is_int($rhs)) {
        throw new \InvalidArgumentException('int.comp.gte: both lhs and rhs must be integer values. Use int: prefix for typecasting.');
    }
    
    return $lhs >= $rhs;
}

\aw2_library::add_service('int.comp.lt', 'Check if one integer is less than another', ['func'=>'comp_lt', 'namespace'=>__NAMESPACE__]);
function comp_lt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lt'));
    
    if($lhs === null || $rhs === null || !is_int($lhs) || !is_int($rhs)) {
        throw new \InvalidArgumentException('int.comp.lt: both lhs and rhs must be integer values. Use int: prefix for typecasting.');
    }
    
    return $lhs < $rhs;
}

\aw2_library::add_service('int.comp.gt', 'Check if one integer is greater than another', ['func'=>'comp_gt', 'namespace'=>__NAMESPACE__]);
function comp_gt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gt'));
    
    if($lhs === null || $rhs === null || !is_int($lhs) || !is_int($rhs)) {
        throw new \InvalidArgumentException('int.comp.gt: both lhs and rhs must be integer values. Use int: prefix for typecasting.');
    }
    
    return $lhs > $rhs;
}