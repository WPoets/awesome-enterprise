<?php

namespace aw2\arr\is;

// arr.is.empty - Check if array is empty
\aw2_library::add_service('arr.is.empty', 'Check if array is empty', ['func'=>'_empty','namespace'=>__NAMESPACE__]);
function _empty($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);
   return empty($main);
}

// arr.is.not_empty - Check if array is not empty
\aw2_library::add_service('arr.is.not_empty', 'Check if array is not empty', ['namespace'=>__NAMESPACE__]);
function not_empty($atts, $content=null, $shortcode=null) {
    $main = \aw2\common\build_array($atts, $content);
    return !empty($main);
}


// arr.is.associative - Check if array is associative 
\aw2_library::add_service('arr.is.associative', 'Check if array is associative', ['namespace'=>__NAMESPACE__]);
function associative($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);
   return array_keys($main) !== range(0, count($main) - 1);
}

// arr.is.sequential - Check if array is sequential
\aw2_library::add_service('arr.is.sequential', 'Check if array is sequential', ['namespace'=>__NAMESPACE__]);
function sequential($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);
   return array_keys($main) === range(0, count($main) - 1);
}

// arr.is.arr - Check if value is an array
\aw2_library::add_service('arr.is.arr', 'Check if value is an array', ['namespace'=>__NAMESPACE__]);
function arr($atts, $content=null, $shortcode=null) {
   if(!isset($atts['main'])) {
       throw new \Exception('arr.is.arr requires main attribute');
   }
   return is_array($atts['main']);
}

// arr.is.not_arr - Check if value is not an array
\aw2_library::add_service('arr.is.not_arr', 'Check if value is not an array', ['namespace'=>__NAMESPACE__]);
function not_arr($atts, $content=null, $shortcode=null) {
   if(!isset($atts['main'])) {
       throw new \Exception('arr.is.not_arr requires main attribute');
   }
   return !is_array($atts['main']);
}