<?php
namespace aw2\cond;


// Main condition block handler
\aw2_library::add_service('cond.block', 'Conditional block with context handling', ['func'=>'cond_block', 'namespace'=>__NAMESPACE__]);
function cond_block($atts, $content=null, $shortcode) {
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('cond.block: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('cond.block: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('cond_block', $context, $context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
    $info = &$call_stack['info'];
    
    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Add temporary handler for this context
    \aw2_library::add_service($context, 'cond_block context object', ['func'=>'cond_block_context_handler', 'namespace'=>'aw2\cond_block_context']);
    
    // Check primary condition
    $result = \aw2\common\cond_check($atts);
    $info['status'] = $result;
    
    $return = \aw2_library::parse_shortcode($content);
    
    \aw2\call_stack\pop_context($stack_id);
    // Restore existing handler if any
    //\aw2_library::set('handlers.' . $context, $backup_service);
    \aw2\common\env\set_handler($context, $backup_service);
    
    return $return;
}

namespace aw2\cond_block_context;

// Context handler
function cond_block_context_handler($atts, $content=null, $shortcode){
    $service = 'cond_block_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    return \aw2_library::service_run($service, $atts, $content);
}

// Base validation function for context handlers
function validate_context($context) {
    if(!isset($context)) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $is_within_context = \aw2\call_stack\is_within_context('cond_block:' . $context);
    if($is_within_context === false) {
        throw new \OutOfBoundsException('You are accessing context outside of cond.block');
    }
    
    return \aw2_library::get_array_ref($context, 'info');
}

// Then handler
\aw2_library::add_service('cond_block_context.then', 'Execute if primary condition is true', ['namespace'=>__NAMESPACE__]);
function then($atts, $content=null, $shortcode) {
    $info = validate_context($atts['@context']);
    
    if($info['status'] === true) {
        return \aw2_library::parse_shortcode($content);
    }
    
    return '';
}

// Then.and handler
\aw2_library::add_service('cond_block_context.then.and', 'Execute if primary is true and secondary is true', ['func'=>'then_and', 'namespace'=>__NAMESPACE__]);
function then_and($atts, $content=null, $shortcode) {
    $info = validate_context($atts['@context']);
    
    if($info['status'] === true) {
        $result = \aw2\common\cond_check($atts);
        if($result === true) {
            return \aw2_library::parse_shortcode($content);
        }
    }
    
    return '';
}

// Then.not handler
\aw2_library::add_service('cond_block_context.then.not', 'Execute if primary is true and secondary is false', ['func'=>'then_not', 'namespace'=>__NAMESPACE__]);
function then_not($atts, $content=null, $shortcode) {
    $info = validate_context($atts['@context']);
    
    if($info['status'] === true) {
        $result = \aw2\common\cond_check($atts);
        if($result === false) {
            return \aw2_library::parse_shortcode($content);
        }
    }
    
    return '';
}

// Else handler
\aw2_library::add_service('cond_block_context.else', 'Execute if primary condition is false', ['func'=>'else_handler', 'namespace'=>__NAMESPACE__]);
function else_handler($atts, $content=null, $shortcode) {
    $info = validate_context($atts['@context']);
    
    if($info['status'] === false) {
        return \aw2_library::parse_shortcode($content);
    }
    
    return '';
}

// Else.and handler
\aw2_library::add_service('cond_block_context.else.and', 'Execute if primary is false and secondary is true', ['func'=>'else_and', 'namespace'=>__NAMESPACE__]);
function else_and($atts, $content=null, $shortcode) {
    $info = validate_context($atts['@context']);
    
    if($info['status'] === false) {
        $result = \aw2\common\cond_check($atts);
        if($result === true) {
            return \aw2_library::parse_shortcode($content);
        }
    }
    
    return '';
}

// Else.not handler
\aw2_library::add_service('cond_block_context.else.not', 'Execute if primary is false and secondary is false', ['func'=>'else_not', 'namespace'=>__NAMESPACE__]);
function else_not($atts, $content=null, $shortcode) {
    $info = validate_context($atts['@context']);
    
    if($info['status'] === false) {
        $result = \aw2\common\cond_check($atts);
        if($result === false) {
            return \aw2_library::parse_shortcode($content);
        }
    }
    
    return '';
}

// Or handler
\aw2_library::add_service('cond_block_context.or', 'Execute if either primary or secondary is true', ['func'=>'or_handler', 'namespace'=>__NAMESPACE__]);
function or_handler($atts, $content=null, $shortcode) {
    $info = validate_context($atts['@context']);
    
    if($info['status'] === true) {
        return \aw2_library::parse_shortcode($content);
    }
    
    $result = \aw2\common\cond_check($atts);
    if($result === true) {
        return \aw2_library::parse_shortcode($content);
    }
    
    return '';
}




