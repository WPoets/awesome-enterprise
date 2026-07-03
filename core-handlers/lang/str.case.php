<?php
namespace aw2\str;

\aw2_library::add_service('str.case.upper', 'Convert string to uppercase', ['func'=>'case_upper', 'namespace'=>__NAMESPACE__]);
function case_upper($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'case_upper'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.case.upper: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return strtoupper($main);
}

\aw2_library::add_service('str.case.lower', 'Convert string to lowercase', ['func'=>'case_lower', 'namespace'=>__NAMESPACE__]);
function case_lower($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'case_lower'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.case.lower: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return strtolower($main);
}

\aw2_library::add_service('str.case.sentence', 'Convert string to sentence case', ['func'=>'case_sentence', 'namespace'=>__NAMESPACE__]);
function case_sentence($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'case_sentence'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.case.sentence: main must be a string value. Use str: prefix for typecasting.');
    }
    
    // First convert all to lowercase, then uppercase first character
    return ucfirst(strtolower($main));
}

\aw2_library::add_service('str.case.title', 'Convert string to title case', ['func'=>'case_title', 'namespace'=>__NAMESPACE__]);
function case_title($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'case_title'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.case.title: main must be a string value. Use str: prefix for typecasting.');
    }
    
    return ucwords(strtolower($main));
}

\aw2_library::add_service('str.case.invert', 'Invert case of string', ['func'=>'case_invert', 'namespace'=>__NAMESPACE__]);
function case_invert($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'case_invert'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.case.invert: main must be a string value. Use str: prefix for typecasting.');
    }
    
    $inverted = '';
    $length = strlen($main);
    
    for($i = 0; $i < $length; $i++) {
        $char = $main[$i];
        if(ctype_upper($char)) {
            $inverted .= strtolower($char);
        } else if(ctype_lower($char)) {
            $inverted .= strtoupper($char);
        } else {
            $inverted .= $char;
        }
    }
    
    return $inverted;
}