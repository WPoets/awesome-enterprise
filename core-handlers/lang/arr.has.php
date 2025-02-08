<?php

namespace aw2\arr\has;

// arr.has.key - Check if specific key exists with type matching
\aw2_library::add_service('arr.has.key', 'Check if specific key exists', ['namespace'=>__NAMESPACE__]);
function key($atts, $content=null, $shortcode=null) {
    if(!isset($atts['key'])) {
        throw new \Exception('arr.has.key requires key attribute');
    }
    
    $main = \aw2\common\build_array($atts, $content);
    if(!is_array($main)) {
        throw new \Exception('main must be an array');
    }
    
    $key = $atts['key'];
    
    // Check if key exists
    if(!array_key_exists($key, $main)) {
        return false;
    }
    
    return true;
}


// arr.has.keys - Check for multiple keys
\aw2_library::add_service('arr.has.keys', 'Check if multiple keys exist', ['namespace'=>__NAMESPACE__]);
function keys($atts, $content=null, $shortcode=null) {
    if(!isset($atts['keys'])) {
        throw new \Exception('arr.has.keys requires keys attribute');
    }
    
    $main = \aw2\common\build_array($atts, $content);
    if(!is_array($main)) {
        throw new \Exception('main must be an array');
    }
    
    $keys = array_map('trim', explode(',', $atts['keys']));
    
    foreach($keys as $key) {
        // Check key existence
        if(!array_key_exists($key, $main)) {
            return false;
        }
    }
    
    return true;
}

// arr.has.value - Check if specific value exists with type matching
\aw2_library::add_service('arr.has.value', 'Check if specific value exists with type matching', ['namespace'=>__NAMESPACE__]);
function value($atts, $content=null, $shortcode=null) {
   if(!isset($atts['value'])) {
       throw new \Exception('arr.has.value requires value attribute');
   }
   
   $main = \aw2\common\build_array($atts, $content);
   
   $search_value = $atts['value']; // This will already be typecasted by build_array
   
   foreach($main as $value) {
       if($value === $search_value) { // === will check both value and type
           return true;
       }
   }
   
   return false;
}



// arr.has.values - Check if multiple string values exist with type matching
\aw2_library::add_service('arr.has.values', 'Check if multiple string values exist with type matching', ['namespace'=>__NAMESPACE__]);
function values($atts, $content=null, $shortcode=null) {
   if(!isset($atts['values'])) {
       throw new \Exception('arr.has.values requires values attribute');
   }
   
   $main = \aw2\common\build_array($atts, $content);
   if(!is_array($main)) {
       throw new \Exception('main must be an array');
   }
   
   $search_values = array_map('trim', explode(',', $atts['values']));
   
   foreach($search_values as $search_value) {
       if(!in_array($search_value, $main, true)) {
           return false;
       }
   }
   
   return true;
}


// arr.has.empty - Check if array has any empty values
\aw2_library::add_service('arr.has.empty', 'Check if array has any empty values', ['func'=>'_empty', 'namespace'=>__NAMESPACE__]);
function _empty($atts, $content=null, $shortcode=null) {
    $main = \aw2\common\build_array($atts, $content);
    if(!is_array($main)) {
        throw new \Exception('main must be an array');
    }
    
    foreach($main as $value) {
        // Check only for whitespace, null, and empty string
        if($value === null || (is_string($value) && trim($value) === '')) {
            return true;
        }
    }
    
    return false;
}

// arr.has.null - Check if array has any null values
\aw2_library::add_service('arr.has.null', 'Check if array has any null values', ['func'=>'_null', 'namespace'=>__NAMESPACE__]);
function _null($atts, $content=null, $shortcode=null) {
    $main = \aw2\common\build_array($atts, $content);
    if(!is_array($main)) {
        throw new \Exception('main must be an array');
    }
    
    return in_array(null, array_values($main), true);
}


