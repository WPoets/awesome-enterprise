<?php
namespace aw2\json;

// json.to.arr - Convert JSON string to array with exception handling
\aw2_library::add_service('json.to.arr', 'Convert JSON string to array', ['func'=>'_to_arr', 'namespace'=>__NAMESPACE__]);

function _to_arr($atts, $content=null, $shortcode=null) {
    if(!isset($atts['main']) && !$content)
        throw new \Exception('No JSON input provided');
        
    // Get JSON string from main attribute or content
    $json_str = isset($atts['main']) ? $atts['main'] : $content;
    
    // Handle associative flag (default true)
    $assoc = true;
    if(isset($atts['assoc']) && $atts['assoc'] === 'no') {
        $assoc = false;
    }
    
    // Decode JSON with error handling
    $result = json_decode($json_str, $assoc);
    
    // Check for JSON errors
    if(json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    return $result;
}