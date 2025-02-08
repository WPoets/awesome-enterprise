<?php

namespace aw2\arr;

\aw2_library::add_service('arr_each.build', 'Builds an array using the provided context and content', ['func'=>'arr_each_build', 'namespace'=>__NAMESPACE__]);

function arr_each_build($atts, $content=null, $shortcode) {
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('arr_each.build: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('arr_each.build: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('arr_build', $context, $context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
    $info = &$call_stack['info'];
    $info['index'] = 0;
    $info['result'] = '';    
    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Add temporary handler for this context
    \aw2_library::add_service($context, 'arr_each.build this object', ['func'=>'arr_build_context_handler', 'namespace'=>'aw2\arr_build_context']);
    
    // Get items array using build_array
    $items = \aw2\common\build_array($atts, $content);
    $info['source'] = $items;
    $info['count'] = 0;
    if(is_countable($items))
        $info['count'] = count($items);
    
    $index = 0;
    foreach ($items as $key => &$item) {
        \aw2\common\updateInfo($info, $index);
        $info['value'] = $item;
        $info['key'] = $key;
        
        $reply = \aw2_library::parse_shortcode($content);
        $index++;
        $info['result'] = $info['result'] . $reply;
    }
    
    \aw2\call_stack\pop_context($stack_id);
    
    // Restore existing handler if any
    \aw2_library::set('handlers.' . $context, $backup_service);
    
    return $info['result'];
}

// Create namespace for context handlers
namespace aw2\arr_build_context;

function arr_build_context_handler($atts, $content=null, $shortcode) {
    $service = 'arr_build_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    \aw2_library::service_run($service, $atts, null);
}