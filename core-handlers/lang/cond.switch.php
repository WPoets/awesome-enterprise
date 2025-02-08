<?php
namespace aw2\cond;

// Main switch handler
\aw2_library::add_service('cond.switch', 'Switch between multiple cases based on value or conditions', ['func'=>'cond_switch', 'namespace'=>__NAMESPACE__]);
function cond_switch($atts, $content=null, $shortcode) {
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('cond.switch: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('cond.switch: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('cond_switch', $context, $context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
    $info = &$call_stack['info'];
    
    // Store switch value in context info - use '#_not_set_#' if not provided
    $info['value'] = isset($atts['value']) ? $atts['value'] : '#_not_set_#';
    $info['matched'] = false;
    
    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Add temporary handler for this context
    \aw2_library::add_service($context, 'cond_switch context object', ['func'=>'cond_switch_context_handler', 'namespace'=>'aw2\cond_switch_context']);
    
    $return = \aw2_library::parse_shortcode($content);
    
    \aw2\call_stack\pop_context($stack_id);
    // Restore existing handler if any
    \aw2\common\env\set_handler($context, $backup_service);
    
    return $return;
}

namespace aw2\cond_switch_context;

// Context handler
function cond_switch_context_handler($atts, $content=null, $shortcode) {
    $service = 'cond_switch_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    return \aw2_library::service_run($service, $atts, $content);
}

// Base validation function for context handlers
function validate_context($context) {
    if(!isset($context)) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $is_within_context = \aw2\call_stack\is_within_context('cond_switch:' . $context);
    if($is_within_context === false) {
        throw new \OutOfBoundsException('You are accessing context outside of cond.switch');
    }
    
    return \aw2_library::get_array_ref($context, 'info');
}

// Case handler
\aw2_library::add_service('cond_switch_context.case', 'Execute if case matches', ['func'=>'case_handler','namespace'=>__NAMESPACE__]);
function case_handler($atts, $content=null, $shortcode) {
    validate_context($atts['@context']);
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    
    // Skip if we already found a match
    if($info['matched'] === true) {
        return '';
    }

    // Condition based matching
    if(isset($atts['cond.@'])) {
        $result = \aw2\common\cond_check($atts);
        if($result === true) {
            $info['matched'] = true;
            return \aw2_library::parse_shortcode($content);
        }
		else{
			return;
		}
    }

    // Value based matching
	
	if(!isset($atts['match']))	
        throw new \InvalidArgumentException('match or a cond has to be there');

	if($info['value'] === '#_not_set_#')	
        throw new \InvalidArgumentException('match parameter is set. But switch does not have a value parameter');
	
	if($info['value'] === $atts['match']) {
		$info['matched'] = true;
		return \aw2_library::parse_shortcode($content);
	}

    return;
}

// Default handler
\aw2_library::add_service('cond_switch_context.default', 'Execute if no case matches', ['func'=>'default_handler','namespace'=>__NAMESPACE__]);
function default_handler($atts, $content=null, $shortcode) {
    validate_context($atts['@context']);
    $info = &\aw2_library::get_array_ref($atts['@context'], 'info');
    
    if($info['matched'] === false) {
        return \aw2_library::parse_shortcode($content);
    }
    
    return;
}