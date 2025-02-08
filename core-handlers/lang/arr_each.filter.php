<?php

namespace aw2\arr;

\aw2_library::add_service('arr_each.filter', 'Filters an array using the provided context and content', ['func'=>'arr_each_filter', 'namespace'=>__NAMESPACE__]);

function arr_each_filter($atts, $content=null, $shortcode) {
    
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('arr_each.filter: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('arr_each.filter: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('arr_filter',$context,$context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);

    $info=&$call_stack['info'];
    $info['index']=0;
    $info['updated_array']=array();
    
    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Add temporary handler for this context
    \aw2_library::add_service($context, 'arr_each.filter this object', ['func'=>'arr_filter_context_handler', 'namespace'=>'aw2\arr_filter_context']);

    // Get items array using build_array
    $items = \aw2\common\build_array($atts, $content);
    $info['source']=$items;
    $info['count']=0;
    if(is_countable($items))
    $info['count']=count($items);

    $index=0;
    foreach ($items as $key =>&$item) {
        \aw2\common\updateInfo($info,$index);

        $info['#select']=false;
        $info['value']=$item;
        $info['key']=$key;
        
        \aw2_library::parse_shortcode($content);
        $index++;
        if($info['#select']===true){
            $info['updated_array'][$info['key']]=$info['value'];
        }
    }
    \aw2\call_stack\pop_context($stack_id);

    // Restore existing handler if any
    \aw2_library::set('handlers.' . $context,$backup_service);

    return $info['updated_array'];
}

namespace aw2\arr_filter_context;

function arr_filter_context_handler($atts, $content=null, $shortcode){
    $service='arr_filter_context.' . implode('.',$shortcode['tags_left']);
    $atts['@context']=$shortcode['tags'][0];
    \aw2_library::service_run($service,$atts,null);
}

\aw2_library::add_service('arr_filter_context._modify', 'Modify array item during array filtering', ['namespace'=>__NAMESPACE__]);

function modify($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    $is_within_context = \aw2\call_stack\is_within_context('arr_filter:' . $context);
    if($is_within_context === false) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }
    
    // Get reference to the info array from the stack
    $info = &\aw2_library::get_array_ref($context, 'info');
    
    // Handle key and value changes
    if(isset($atts['key'])) {
        $info['key'] = $atts['key'];
    }
    
    if(isset($atts['value'])) {
        $info['value'] = $atts['value'];
    }
    
    return;
}

\aw2_library::add_service('arr_filter_context._select', 'Select item during array filtering', ['namespace'=>__NAMESPACE__]);

function select($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    $is_within_context = \aw2\call_stack\is_within_context('arr_filter:' . $context);
    if($is_within_context === false) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }
    
    // Get reference to the info array from the stack
    $info = &\aw2_library::get_array_ref($context, 'info');
    $info['#select'] = true;
    
    return;
}

\aw2_library::add_service('arr_filter_context.filter', 'Filter Item during array filtering', ['namespace'=>__NAMESPACE__]);

function filter($atts, $content=null, $shortcode) {
    // Extract attributes with defaults
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
	$context=$atts['@context'];

    // Get reference to the info array from the stack
    $info = &\aw2_library::get_array_ref($context, 'info');

    // Check primary condition
    $info['#select'] = \aw2\common\cond_check($atts);

    if($info['#select']===true){    
        //Handle main
        if(isset($atts['main'])) {
            $info['value'] = $atts['main'];
        }


        // Handle key and value changes
        if(isset($atts['key'])) {
            $info['key'] = $atts['key'];
        }
        
        if(isset($atts['value'])) {
            $info['value'] = $atts['value'];
        }



        $service=\aw2_library::get_service_params($atts);

        if(!empty($service['name'])) {
            $info['value']=\aw2_library::service_run($service['name'],$service['atts'],null);
        }

        
    }
    // Return empty string as no output is needed
    return ;
}




