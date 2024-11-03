<?php
namespace aw2\num;

\aw2_library::add_service('num.comp.eq', 'Check if two floats are equal', ['func'=>'comp_eq', 'namespace'=>__NAMESPACE__]);
function comp_eq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_eq'));
    
    if($lhs === null || $rhs === null || !is_float($lhs) || !is_float($rhs)) {
        throw new \InvalidArgumentException('num.comp.eq: both lhs and rhs must be float values. Use num: prefix for typecasting.');
    }
	
/*
Note that for floating-point equality comparisons (num.comp.eq and num.comp.neq), we use PHP_FLOAT_EPSILON to account for potential floating-point precision issues. This approach is more reliable than direct equality comparison for floats.
*/	
    
    return abs($lhs - $rhs) < PHP_FLOAT_EPSILON;
}

\aw2_library::add_service('num.comp.neq', 'Check if two floats are not equal', ['func'=>'comp_neq', 'namespace'=>__NAMESPACE__]);
function comp_neq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_neq'));
    
    if($lhs === null || $rhs === null || !is_float($lhs) || !is_float($rhs)) {
        throw new \InvalidArgumentException('num.comp.neq: both lhs and rhs must be float values. Use num: prefix for typecasting.');
    }

/*
Note that for floating-point equality comparisons (num.comp.eq and num.comp.neq), we use PHP_FLOAT_EPSILON to account for potential floating-point precision issues. This approach is more reliable than direct equality comparison for floats.
*/	
    
    return abs($lhs - $rhs) >= PHP_FLOAT_EPSILON;
}

\aw2_library::add_service('num.comp.lte', 'Check if one float is less than or equal to another', ['func'=>'comp_lte', 'namespace'=>__NAMESPACE__]);
function comp_lte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lte'));
    
    if($lhs === null || $rhs === null || !is_float($lhs) || !is_float($rhs)) {
        throw new \InvalidArgumentException('num.comp.lte: both lhs and rhs must be float values. Use num: prefix for typecasting.');
    }
    
    return $lhs <= $rhs;
}

\aw2_library::add_service('num.comp.gte', 'Check if one float is greater than or equal to another', ['func'=>'comp_gte', 'namespace'=>__NAMESPACE__]);
function comp_gte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gte'));
    
    if($lhs === null || $rhs === null || !is_float($lhs) || !is_float($rhs)) {
        throw new \InvalidArgumentException('num.comp.gte: both lhs and rhs must be float values. Use num: prefix for typecasting.');
    }
    
    return $lhs >= $rhs;
}

\aw2_library::add_service('num.comp.lt', 'Check if one float is less than another', ['func'=>'comp_lt', 'namespace'=>__NAMESPACE__]);
function comp_lt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lt'));
    
    if($lhs === null || $rhs === null || !is_float($lhs) || !is_float($rhs)) {
        throw new \InvalidArgumentException('num.comp.lt: both lhs and rhs must be float values. Use num: prefix for typecasting.');
    }
    
    return $lhs < $rhs;
}

\aw2_library::add_service('num.comp.gt', 'Check if one float is greater than another', ['func'=>'comp_gt', 'namespace'=>__NAMESPACE__]);
function comp_gt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gt'));
    
    if($lhs === null || $rhs === null || !is_float($lhs) || !is_float($rhs)) {
        throw new \InvalidArgumentException('num.comp.gt: both lhs and rhs must be float values. Use num: prefix for typecasting.');
    }
    
    return $lhs > $rhs;
}