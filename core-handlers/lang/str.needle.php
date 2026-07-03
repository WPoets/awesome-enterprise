<?php
namespace aw2\str;

\aw2_library::add_service('str.needle.count', 'Count case-insensitive occurrences of a string in another string or content', ['func'=>'needle_count', 'namespace'=>__NAMESPACE__]);
function needle_count($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'haystack' => null
    ), $atts, 'needle_count'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.needle.count: main (needle) must be a string value. Use str: prefix for typecasting.');
    }
    
    if($haystack === null && $content === null) {
        throw new \InvalidArgumentException('str.needle.count: Either haystack attribute or content must be provided.');
    }
    
    $search_in = $haystack !== null ? $haystack : \aw2_library::parse_shortcode($content);
    
    if(!is_string($search_in)) {
        throw new \InvalidArgumentException('str.needle.count: haystack or parsed content must be a string.');
    }
    
    // Convert both strings to lowercase for case-insensitive comparison
    return substr_count(strtolower($search_in), strtolower($main));
}

\aw2_library::add_service('str.needle.pos_first', 'Find index of first case-insensitive occurrence of a string', ['func'=>'needle_pos_first', 'namespace'=>__NAMESPACE__]);
function needle_pos_first($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'haystack' => null
    ), $atts, 'needle_index_first'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.needle.pos_first: main (needle) must be a string value. Use str: prefix for typecasting.');
    }
    
    if($haystack === null && $content === null) {
        throw new \InvalidArgumentException('str.needle.pos_first: Either haystack attribute or content must be provided.');
    }
    
    $search_in = $haystack !== null ? $haystack : \aw2_library::parse_shortcode($content);
    
    if(!is_string($search_in)) {
        throw new \InvalidArgumentException('str.needle.pos_first: haystack or parsed content must be a string.');
    }
    
    // Convert both strings to lowercase for case-insensitive comparison
    $index = stripos($search_in, $main);
    return $index === false ? -1 : $index;
}

\aw2_library::add_service('str.needle.pos_last', 'Find index of last case-insensitive occurrence of a string', ['func'=>'needle_pos_last', 'namespace'=>__NAMESPACE__]);
function needle_pos_last($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'haystack' => null
    ), $atts, 'needle_index_last'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.needle.pos_last: main (needle) must be a string value. Use str: prefix for typecasting.');
    }
    
    if($haystack === null && $content === null) {
        throw new \InvalidArgumentException('str.needle.pos_last: Either haystack attribute or content must be provided.');
    }
    
    $search_in = $haystack !== null ? $haystack : \aw2_library::parse_shortcode($content);
    
    if(!is_string($search_in)) {
        throw new \InvalidArgumentException('str.needle.pos_last: haystack or parsed content must be a string.');
    }
    
    // Convert both strings to lowercase for case-insensitive comparison
    $index = strripos($search_in, $main);
    return $index === false ? -1 : $index;
}

\aw2_library::add_service('str.needle.pos_n', 'Find index of nth case-insensitive occurrence of a string', ['func'=>'needle_pos_n', 'namespace'=>__NAMESPACE__]);
function needle_pos_n($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'haystack' => null,
        'n' => null
    ), $atts, 'needle_index_n'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.needle.pos_n: main (needle) must be a string value. Use str: prefix for typecasting.');
    }
    
    if($haystack === null && $content === null) {
        throw new \InvalidArgumentException('str.needle.pos_n: Either haystack attribute or content must be provided.');
    }
    
    if(!is_int($n) || $n < 1) {
        throw new \InvalidArgumentException('str.needle.pos_n: n must be a positive integer.');
    }
    
    $search_in = $haystack !== null ? $haystack : \aw2_library::parse_shortcode($content);
    
    if(!is_string($search_in)) {
        throw new \InvalidArgumentException('str.needle.pos_n: haystack or parsed content must be a string.');
    }
    
    $index = -1;
    $offset = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $pos = stripos($search_in, $main, $offset);
        if ($pos === false) {
            return -1;
        }
        $index = $pos;
        $offset = $pos + 1;
    }
    
    return $index;
}

\aw2_library::add_service('str.needle.replace', 'Replace case-insensitive occurrences of a string', ['func'=>'needle_replace', 'namespace'=>__NAMESPACE__]);
function needle_replace($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'haystack' => null,
        'replacement' => ''
    ), $atts, 'needle_replace'));
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('str.needle.replace: main (needle) must be a string value. Use str: prefix for typecasting.');
    }
    
    if($haystack === null && $content === null) {
        throw new \InvalidArgumentException('str.needle.replace: Either haystack attribute or content must be provided.');
    }

    if(is_null($replacement) || !is_string($replacement)) {
        throw new \InvalidArgumentException('str.needle.replace: replacement must be provided');
    }

    
    $search_in = $haystack !== null ? $haystack : \aw2_library::parse_shortcode($content);
    
    if(!is_string($search_in)) {
        throw new \InvalidArgumentException('str.needle.replace: haystack or parsed content must be a string.');
    }
    
    // Use str_ireplace for case-insensitive replacement
    return str_ireplace($main, $replacement, $search_in);
}