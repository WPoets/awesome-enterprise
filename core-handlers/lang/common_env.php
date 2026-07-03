<?php
namespace aw2\common\env;


// Base validation function for context handlers
// Base validation function for context handlers
function validate_context($atts,$type) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    $context=$atts['@context'];
    $is_within_context = \aw2\call_stack\is_within_context($type . $context);
    if($is_within_context === false) {
        throw new \OutOfBoundsException('You are accessing context outside of ' . $type);
    }
}



function set_handler($path, $value){
    return set($path, $value, 'handlers'); 
}



 


\aw2_library::add_service('env2.set', 'Set an Environment Value', ['func'=>'_set', 'namespace'=>__NAMESPACE__]);

/**
 * Sets environment values with optional prefix
 * 
 * @param array $atts Attributes containing key-value pairs to set in environment
 * @param string $content Optional content, defaults to '#*not_set_#'
 * @param mixed $shortcode Shortcode information
 * @return void
 */
function _set(array $atts, string $content = '#*not_set_#', $shortcode = array()): void {
    // Extract prefix if exists
    $prefix = null;
    if (isset($atts['@prefix'])) {
        $prefix = $atts['@prefix'];
        unset($atts['@prefix']);
    }

    // Set each attribute in the environment
    foreach ($atts as $key => $value) {
        // Build the full key with prefix if it exists
        $full_key = $prefix ? $prefix . '.' . $key : $key;
        
        // Set the value in environment
        \aw2\common\env\set($full_key, $value);
    }
    
    return;
}



\aw2_library::add_service('env2.get', 'Get an Environment Value', ['func'=>'_get', 'namespace'=>__NAMESPACE__]);

function _get($atts,$content=null,$shortcode = array()){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>'#_not_set_#'
	), $atts, 'aw2_get' ) );
	
    $prefix = null;
    if(isset($atts['@prefix'])){
        $prefix = $atts['@prefix'];
        unset($atts['@prefix']);
    }

	$return_value=\aw2\common\env\get($main,$prefix);
	
	if($return_value==='' && $default!=='#_not_set_#')$return_value=$default;
	return $return_value;
}
