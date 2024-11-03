<?php
namespace aw2\date;

// Helper function to validate date string
function validate_date($date_str) {
    $dt = \DateTime::createFromFormat('Ymd', $date_str);
    if(!$dt || $dt->format('Ymd') !== $date_str) {
        throw new \InvalidArgumentException('Invalid date: ' . $date_str);
    }
    return (int)$date_str;
}

\aw2_library::add_service('date.comp.eq', 'Check if two 8-digit dates are equal', ['func'=>'comp_eq', 'namespace'=>__NAMESPACE__]);
function comp_eq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_eq'));
    
    if($lhs === null || $rhs === null) {
        throw new \InvalidArgumentException('date.comp.eq: both lhs and rhs must be provided.');
    }
    
    $lhs_val = validate_date($lhs);
    $rhs_val = validate_date($rhs);
    
    return $lhs_val === $rhs_val;
}

\aw2_library::add_service('date.comp.neq', 'Check if two 8-digit dates are not equal', ['func'=>'comp_neq', 'namespace'=>__NAMESPACE__]);
function comp_neq($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_neq'));
    
    if($lhs === null || $rhs === null) {
        throw new \InvalidArgumentException('date.comp.neq: both lhs and rhs must be provided.');
    }
    
    $lhs_val = validate_date($lhs);
    $rhs_val = validate_date($rhs);
    
    return $lhs_val !== $rhs_val;
}

\aw2_library::add_service('date.comp.lte', 'Check if one 8-digit date is less than or equal to another', ['func'=>'comp_lte', 'namespace'=>__NAMESPACE__]);
function comp_lte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lte'));
    
    if($lhs === null || $rhs === null) {
        throw new \InvalidArgumentException('date.comp.lte: both lhs and rhs must be provided.');
    }
    
    $lhs_val = validate_date($lhs);
    $rhs_val = validate_date($rhs);
    
    return $lhs_val <= $rhs_val;
}

\aw2_library::add_service('date.comp.gte', 'Check if one 8-digit date is greater than or equal to another', ['func'=>'comp_gte', 'namespace'=>__NAMESPACE__]);
function comp_gte($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gte'));
    
    if($lhs === null || $rhs === null) {
        throw new \InvalidArgumentException('date.comp.gte: both lhs and rhs must be provided.');
    }
    
    $lhs_val = validate_date($lhs);
    $rhs_val = validate_date($rhs);
    
    return $lhs_val >= $rhs_val;
}

\aw2_library::add_service('date.comp.lt', 'Check if one 8-digit date is less than another', ['func'=>'comp_lt', 'namespace'=>__NAMESPACE__]);
function comp_lt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_lt'));
    
    if($lhs === null || $rhs === null) {
        throw new \InvalidArgumentException('date.comp.lt: both lhs and rhs must be provided.');
    }
    
    $lhs_val = validate_date($lhs);
    $rhs_val = validate_date($rhs);
    
    return $lhs_val < $rhs_val;
}

\aw2_library::add_service('date.comp.gt', 'Check if one 8-digit date is greater than another', ['func'=>'comp_gt', 'namespace'=>__NAMESPACE__]);
function comp_gt($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('lhs' => null, 'rhs' => null), $atts, 'comp_gt'));
    
    if($lhs === null || $rhs === null) {
        throw new \InvalidArgumentException('date.comp.gt: both lhs and rhs must be provided.');
    }
    
    $lhs_val = validate_date($lhs);
    $rhs_val = validate_date($rhs);
    
    return $lhs_val > $rhs_val;
}