<?php
namespace aw2\num;

\aw2_library::add_service('num.comp.eq', 'Check if float equals another', ['func'=>'comp_eq', 'namespace'=>__NAMESPACE__]);
function comp_eq($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_eq'));

    if(!is_float($main) || !is_float($with))
        throw new \InvalidArgumentException('num.comp.eq: both values must be floats. Use num: prefix for typecasting.');
    
    return abs($main - $with) < PHP_FLOAT_EPSILON;
}

\aw2_library::add_service('num.comp.neq', 'Check if float not equals another', ['func'=>'comp_neq', 'namespace'=>__NAMESPACE__]);
function comp_neq($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_neq'));
    
    if(!is_float($main) || !is_float($with))
        throw new \InvalidArgumentException('num.comp.neq: both values must be floats. Use num: prefix for typecasting.');
    
    return abs($main - $with) >= PHP_FLOAT_EPSILON;
}

\aw2_library::add_service('num.comp.lte', 'Check if float is less than or equal to another', ['func'=>'comp_lte', 'namespace'=>__NAMESPACE__]);
function comp_lte($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_lte'));
    
    if(!is_float($main) || !is_float($with))
        throw new \InvalidArgumentException('num.comp.lte: both values must be floats. Use num: prefix for typecasting.');
    
    return $main <= $with;
}

\aw2_library::add_service('num.comp.gte', 'Check if float is greater than or equal to another', ['func'=>'comp_gte', 'namespace'=>__NAMESPACE__]);
function comp_gte($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_gte'));
    
    if(!is_float($main) || !is_float($with))
        throw new \InvalidArgumentException('num.comp.gte: both values must be floats. Use num: prefix for typecasting.');
    
    return $main >= $with;
}

\aw2_library::add_service('num.comp.lt', 'Check if float is less than another', ['func'=>'comp_lt', 'namespace'=>__NAMESPACE__]);
function comp_lt($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_lt'));
    
    if(!is_float($main) || !is_float($with))
        throw new \InvalidArgumentException('num.comp.lt: both values must be floats. Use num: prefix for typecasting.');
    
    return $main < $with;
}

\aw2_library::add_service('num.comp.gt', 'Check if float is greater than another', ['func'=>'comp_gt', 'namespace'=>__NAMESPACE__]);
function comp_gt($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null, 'with' => null), $atts, 'comp_gt'));
    
    if(!is_float($main) || !is_float($with))
        throw new \InvalidArgumentException('num.comp.gt: both values must be floats. Use num: prefix for typecasting.');
    
    return $main > $with;
}