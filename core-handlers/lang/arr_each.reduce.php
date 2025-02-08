<?php

namespace aw2\arr;

\aw2_library::add_service('arr_each.reduce', 'Reduces array to a single value using the provided context and content', ['func'=>'arr_each_reduce', 'namespace'=>__NAMESPACE__]);

function arr_each_reduce($atts, $content=null, $shortcode) {
    
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('arr_each.reduce: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('arr_each.reduce: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('arr_reduce',$context,$context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);

    $info=&$call_stack['info'];
    $info['index']=0;
    $info['result']=null;

    // Get initial value if provided
    if(isset($atts['initial'])) {
        $info['result'] = $atts['initial'];
    }
    
    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Add temporary handler for this context
    \aw2_library::add_service($context, 'arr_each.reduce this object', ['func'=>'arr_reduce_context_handler', 'namespace'=>'aw2\arr_reduce_context']);

    // Get items array using build_array
    $items = \aw2\common\build_array($atts, $content);
    $info['source']=$items;
    $info['count']=0;
    if(is_countable($items))
    $info['count']=count($items);

    $index=0;
    foreach ($items as $key =>&$item) {
        \aw2\common\updateInfo($info,$index);

        $info['value']=$item;
        $info['key']=$key;
        
        \aw2_library::parse_shortcode($content);
        $index++;
    }
    \aw2\call_stack\pop_context($stack_id);

    // Restore existing handler if any
    \aw2_library::set('handlers.' . $context,$backup_service);

    return $info['result'];
}

namespace aw2\arr_reduce_context;

function arr_reduce_context_handler($atts, $content=null, $shortcode){
    $service='arr_reduce_context.' . implode('.',$shortcode['tags_left']);
    $atts['@context']=$shortcode['tags'][0];
    \aw2_library::service_run($service,$atts,null);
}

\aw2_library::add_service('arr_reduce_context._modify', 'Modify array item during array reducing', ['namespace'=>__NAMESPACE__]);

function modify($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    $is_within_context = \aw2\call_stack\is_within_context('arr_reduce:' . $context);
    if($is_within_context === false) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }
    
    // Get reference to the info array from the stack
    $info = &\aw2_library::get_array_ref($context, 'info');
    
    
    if(isset($atts['main'])) {
        $info['result'] = $atts['main'];
    }
    
    return;
}

\aw2_library::add_service('arr_reduce_context.reduce', 'Reduce and accumulate during array reduction', ['namespace'=>__NAMESPACE__]);

function reduce($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    $context = $atts['@context'];

    // Get reference to the info array from the stack
    $info = &\aw2_library::get_array_ref($context, 'info');

    if(isset($atts['main'])) {
        $info['result'] = $atts['main'];
        return;
    }
    
    $service = \aw2_library::get_service_params($atts);
    if(!empty($service['name'])) {
        $info['result']=\aw2_library::service_run($service['name'],$service['atts'],null);
    }    
    
    return;
}