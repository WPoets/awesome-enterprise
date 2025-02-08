<?php
namespace aw2\_while;

// Main handler for while.arr
\aw2_library::add_service('while.arr', 'While array implementation', ['func'=>'while_arr', 'namespace'=>__NAMESPACE__]);

function while_arr($atts, $content=null, $shortcode) {
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('while.arr: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('while.arr: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('while_arr', $context, $context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
    $info = &$call_stack['info'];
    
    // Initialize info object with required properties
    $info['status'] = true;
    $info['items'] = array();
    $info['index'] = 0;
    $info['count'] = count($info['items']);
	
    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Add temporary handler for this context
    \aw2_library::add_service($context, 'while_arr context object', ['func'=>'while_arr_context_handler', 'namespace'=>'aw2\while_arr_context']);
    
    // Infinite loop until broken
    while(true) {
        \aw2\common\updateInfo($info, $info['index']);
        \aw2_library::parse_shortcode($content);
		$info['count'] = count($info['items']);

        if($info['status'] === false)
            break;
        $info['index']++;
    }
    
    \aw2\call_stack\pop_context($stack_id);
    // Restore existing handler
    \aw2\common\env\set_handler($context, $backup_service);
    
    return $info['items'];
}

namespace aw2\while_arr_context;

// Context handler
function while_arr_context_handler($atts, $content=null, $shortcode) {
    $service = 'while_arr_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    return \aw2_library::service_run($service, $atts, $content);
}

// Then handler - executes if condition is true
\aw2_library::add_service('while_arr_context.then', 'Execute if condition is true', ['namespace'=>__NAMESPACE__]);

function then($atts, $content=null, $shortcode) {
    \aw2\common\env\validate_context($atts, 'while_arr:');
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    
    if($info['status'] === true) {
        \aw2_library::parse_shortcode($content);
    }
}

// Condition handler - sets whether loop continues or breaks
\aw2_library::add_service('while_arr_context.cond', 'Sets whether loop continues', ['func'=>'cond', 'namespace'=>__NAMESPACE__]);

function cond($atts, $content=null, $shortcode) {
    \aw2\common\env\validate_context($atts, 'while_arr:');
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    $info['status'] = \aw2\common\cond_check($atts);
    return '';
}

// Array handler - adds items to array
\aw2_library::add_service('while_arr_context.arr', 'Add item to array', ['namespace'=>__NAMESPACE__]);

function arr($atts, $content=null, $shortcode) {
    \aw2\common\env\validate_context($atts, 'while_arr:');
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    
    // Handle indexed array mode
    if(isset($atts['main'])) {
        $info['items'][] = $atts['main'];
        return '';
    }
    
    // Handle associative array mode
    if(!isset($atts['key']) || !isset($atts['value'])) {
        throw new \InvalidArgumentException('while.arr: When not using main, both key and value must be specified');
    }
    
    $info['items'][$atts['key']] = $atts['value'];
    return '';
}