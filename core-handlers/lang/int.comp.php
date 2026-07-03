<?php
namespace aw2\int;

\aw2_library::add_service('int.comp.eq', 'Check if integer equals another', ['func'=>'comp_eq', 'namespace'=>__NAMESPACE__]);
function comp_eq($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_eq'));
    
    if(!is_int($main) || !is_int($with))
        throw new \InvalidArgumentException('int.comp.eq: both values must be integers. Use int: prefix for typecasting.');
    
    return $main === $with;
}

\aw2_library::add_service('int.comp.neq', 'Check if integer not equals another', ['func'=>'comp_neq', 'namespace'=>__NAMESPACE__]);
function comp_neq($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_neq'));
    
    if(!is_int($main) || !is_int($with))
        throw new \InvalidArgumentException('int.comp.neq: both values must be integers. Use int: prefix for typecasting.');
    
    return $main !== $with;
}

\aw2_library::add_service('int.comp.lte', 'Check if integer is less than or equal to another', ['func'=>'comp_lte', 'namespace'=>__NAMESPACE__]);
function comp_lte($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_lte'));
    
    if(!is_int($main) || !is_int($with))
        throw new \InvalidArgumentException('int.comp.lte: both values must be integers. Use int: prefix for typecasting.');
    
    return $main <= $with;
}

\aw2_library::add_service('int.comp.gte', 'Check if integer is greater than or equal to another', ['func'=>'comp_gte', 'namespace'=>__NAMESPACE__]);
function comp_gte($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_gte'));
    
    if(!is_int($main) || !is_int($with))
        throw new \InvalidArgumentException('int.comp.gte: both values must be integers. Use int: prefix for typecasting.');
    
    return $main >= $with;
}

\aw2_library::add_service('int.comp.lt', 'Check if integer is less than another', ['func'=>'comp_lt', 'namespace'=>__NAMESPACE__]);
function comp_lt($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_lt'));
    
    if(!is_int($main) || !is_int($with))
        throw new \InvalidArgumentException('int.comp.lt: both values must be integers. Use int: prefix for typecasting.');
    
    return $main < $with;
}

\aw2_library::add_service('int.comp.gt', 'Check if integer is greater than another', ['func'=>'comp_gt', 'namespace'=>__NAMESPACE__]);
function comp_gt($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_gt'));
    
    if(!is_int($main) || !is_int($with))
        throw new \InvalidArgumentException('int.comp.gt: both values must be integers. Use int: prefix for typecasting.');
    
    return $main > $with;
} 