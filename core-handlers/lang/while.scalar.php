<?php
namespace aw2\_while;

// Main handler for while.scalar
\aw2_library::add_service('while.scalar', 'While scalar implementation', ['func'=>'while_scalar', 'namespace'=>__NAMESPACE__]);

function while_scalar($atts, $content=null, $shortcode) {
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('while.scalar: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('while.scalar: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('while_scalar', $context, $context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
    $info = &$call_stack['info'];
    
    // Initialize info object with required properties
    $info['status'] = true;
    $info['index'] = 0;
    $info['result'] = null;  // Accumulated result
    
    if(isset($atts['initial'])) {
        $info['result'] = $atts['initial'];
    }

    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Add temporary handler for this context
    \aw2_library::add_service($context, 'while_scalar context object', ['func'=>'while_scalar_context_handler', 'namespace'=>'aw2\while_scalar_context']);
    
    // Infinite loop until broken
    while(true) {
        \aw2\common\updateInfo($info, $info['index']);
        \aw2_library::parse_shortcode($content);
        if($info['status'] === false)
            break;
        $info['index']++;
    }
    
    \aw2\call_stack\pop_context($stack_id);
    // Restore existing handler
    \aw2\common\env\set_handler($context, $backup_service);
    
    return $info['result'];
}

namespace aw2\while_scalar_context;

// Context handler
function while_scalar_context_handler($atts, $content=null, $shortcode) {
    $service = 'while_scalar_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    return \aw2_library::service_run($service, $atts, $content);
}

// Then handler - executes if condition is true
\aw2_library::add_service('while_scalar_context.then', 'Execute if condition is true', ['namespace'=>__NAMESPACE__]);

function then($atts, $content=null, $shortcode) {
    \aw2\common\env\validate_context($atts, 'while_scalar:');
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    
    if($info['status'] === true) {
        \aw2_library::parse_shortcode($content);
    }
}

// Condition handler - sets whether loop continues or breaks
\aw2_library::add_service('while_scalar_context.cond', 'Sets whether loop continues', ['func'=>'cond', 'namespace'=>__NAMESPACE__]);

function cond($atts, $content=null, $shortcode) {
    \aw2\common\env\validate_context($atts, 'while_scalar:');
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    $info['status'] = \aw2\common\cond_check($atts);
    return '';
}

// Scalar handler - updates the scalar result
\aw2_library::add_service('while_scalar_context.scalar', 'Update scalar result', ['namespace'=>__NAMESPACE__]);

function scalar($atts, $content=null, $shortcode) {
    \aw2\common\env\validate_context($atts, 'while_scalar:');
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    
    // Handle direct value assignment
    if(isset($atts['main'])) {
        $info['result'] = $atts['main'];
        return '';
    }
    
    // Handle service-based value assignment
    $service = \aw2_library::get_service_params($atts);
    $info['result'] = \aw2_library::service_run($service['name'], $service['atts'], null);
    return '';
}