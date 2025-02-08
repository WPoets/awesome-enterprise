<?php

namespace aw2\enum;

// Main enum.define service
\aw2_library::add_service('enum.define', 'Define an enum with key-value pairs', ['func'=>'define_enum', 'namespace'=>__NAMESPACE__]);

function define_enum($atts, $content=null, $shortcode) {
    if(empty($atts['main']) || !is_string($atts['main']))
        throw new \Exception('Enum name is required and must be a string');
    
    $enum_name = $atts['main'];
    unset($atts['main']);

    //build the array
    $items=\aw2\common\build_array($atts, $content);
    
    // Store enum definition
    \aw2_library::set($enum_name, $items);
    
    // Add permanent handler for this context
    \aw2_library::add_service($enum_name, 'Services for enum', ['func'=>'enum_context_handler', 'namespace'=>'aw2\enum_context']);
    
    return '';
}

namespace aw2\enum_context;

function enum_context_handler($atts, $content=null, $shortcode) {
    $service = 'enum_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    return \aw2_library::service_run($service, $atts, null);
}

\aw2_library::add_service('enum_context.keys', 'Get enum keys', ['namespace'=>__NAMESPACE__]);
function keys($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    $enum_def = \aw2_library::get($context);

    if(!is_array($enum_def))
        throw new \InvalidArgumentException("The Array for the Enum $context is missing");
        
    return array_keys($enum_def);
}

\aw2_library::add_service('enum_context.list', 'Get full enum definition', ['func'=>'get_list', 'namespace'=>__NAMESPACE__]);
function get_list($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    $context = $atts['@context'];
    
    $enum_def = \aw2_library::get($context);

    if(!is_array($enum_def))
        throw new \InvalidArgumentException("The Array for the Enum $context is missing");

    return $enum_def;
}

\aw2_library::add_service('enum_context.validate', 'Validate enum value', ['namespace'=>__NAMESPACE__]);
function validate($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    
    if(empty($atts['main']) || !is_string($atts['main']))
        throw new \Exception('Enum name is required and must be a string');
        
    $enum_def = \aw2_library::get($context);
    
    if(!is_array($enum_def))
        throw new \InvalidArgumentException("The Array for the Enum $context is missing");
        
    return array_key_exists($atts['main'], $enum_def);
}