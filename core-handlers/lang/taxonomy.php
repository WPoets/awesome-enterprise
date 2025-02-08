<?php

namespace aw2\taxonomy;

// Main taxonomy.define service
\aw2_library::add_service('taxonomy.define', 'Define a taxonomy with hierarchical terms and metadata', ['func'=>'define_taxonomy', 'namespace'=>__NAMESPACE__]);

function define_taxonomy($atts, $content=null, $shortcode) {
    if(empty($atts['main']) || !is_string($atts['main']))
        throw new \Exception('Taxonomy name is required and must be a string');
    
    $taxonomy_name = $atts['main'];
    unset($atts['main']);
    
    //build the hierarchical array
    $items = \aw2\common\build_array($atts, $content);
    
    // Store taxonomy definition
    \aw2_library::set($taxonomy_name, $items);
    
    // Add permanent handler for this context
    \aw2_library::add_service($taxonomy_name, 'Services for taxonomy', ['func'=>'taxonomy_context_handler', 'namespace'=>'aw2\taxonomy_context']);
    
    return '';
}


namespace aw2\taxonomy_context;

function taxonomy_context_handler($atts, $content=null, $shortcode) {
    $service = 'taxonomy_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    return \aw2_library::service_run($service, $atts, $content);
}

// Helper function to find a term in the taxonomy
function find_term($taxonomy, $term_path) {
    // If no path specified, return top level taxonomy
    if(empty($term_path)) {
        return $taxonomy;
    }
    
    // Split path into parts
    $parts = explode('.', $term_path);
    $current = $taxonomy;
    
    // Traverse through the path
    foreach($parts as $part) {
        if(!isset($current[$part])) {
            return null;
        }
        $current = $current[$part];
        
        // If this is not the last part, move to children
        if(end($parts) !== $part && isset($current['children'])) {
            $current = $current['children'];
        }
    }
    
    return $current;
}
// Get complete list
\aw2_library::add_service('taxonomy_context.list', 'Get full taxonomy definition', ['func'=>'get_list', 'namespace'=>__NAMESPACE__]);
function get_list($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    $taxonomy_def = \aw2_library::get($context);
    
    if(!is_array($taxonomy_def))
        throw new \InvalidArgumentException("The taxonomy $context is missing");
        
    return $taxonomy_def;
}



// Get terms under a specific path 
\aw2_library::add_service('taxonomy_context.get_terms', 'Get terms under specified path', ['namespace'=>__NAMESPACE__]);
function get_terms($atts, $content=null, $shortcode) {
    if(empty($atts['@context'])) {
        throw new \InvalidArgumentException('@context is empty or missing');
    }
    
    if(empty($atts['main'])) {
        throw new \InvalidArgumentException('Parent path is missing');
    }
    
    $context = $atts['@context'];
    $parent = $atts['main'];
    
    $taxonomy = \aw2_library::get($context);
    if(!is_array($taxonomy)) {
        throw new \InvalidArgumentException("The taxonomy $context is missing");
    }
    
    $term = find_term($taxonomy, $parent);
    if($term === null) {
        throw new \InvalidArgumentException("Term $parent not found in taxonomy");
    }
    
    if(!isset($term['children']) || !is_array($term['children'])) {
        throw new \InvalidArgumentException("Term $parent has no terms");
    }
    
    return $term['children'];
}

// Get a specific term with its meta and children
\aw2_library::add_service('taxonomy_context.get_term', 'Get specific term with meta and children', ['namespace'=>__NAMESPACE__]);
function get_term($atts, $content=null, $shortcode) {
    if(empty($atts['@context'])) {
        throw new \InvalidArgumentException('@context is empty or missing');
    }
    
    if(empty($atts['main'])) {
        throw new \InvalidArgumentException('Term path is missing');
    }
    
    $context = $atts['@context'];
    $term_path = $atts['main'];
    
    $taxonomy = \aw2_library::get($context);
    if(!is_array($taxonomy)) {
        throw new \InvalidArgumentException("The taxonomy $context is missing");
    }
    
    $term = find_term($taxonomy, $term_path);
    if($term === null) {
        throw new \InvalidArgumentException("Term $term_path not found in taxonomy");
    }
    
    return $term;
}

// Get term metadata
\aw2_library::add_service('taxonomy_context.get_meta', 'Get term metadata', ['namespace'=>__NAMESPACE__]);
function get_meta($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    if(empty($atts['main']))
        throw new \Exception('Term is required');
    
    $context = $atts['@context'];
    $term = $atts['main'];
    
    $taxonomy_def = \aw2_library::get($context);
    if(!is_array($taxonomy_def))
        throw new \InvalidArgumentException("The taxonomy $context is missing");
    
    // Find the term and return its metadata
    $term_data = find_term($taxonomy_def, $term);
    if($term_data === null) {
        throw new \InvalidArgumentException("Term $term not found in taxonomy");
    }
   
    return isset($term_data['meta']) ? $term_data['meta'] : array();
}

// Check if a term exists
\aw2_library::add_service('taxonomy_context.exists', 'Check if term exists', ['namespace'=>__NAMESPACE__]);
function exists($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    if(empty($atts['main']))
        throw new \Exception('Term to check is required');
    
    $context = $atts['@context'];
    $term = $atts['main'];
    
    $taxonomy_def = \aw2_library::get($context);
    if(!is_array($taxonomy_def))
        throw new \InvalidArgumentException("The taxonomy $context is missing");
    
    return find_term($taxonomy_def, $term) !== null;
}


// Add multiple terms at once
\aw2_library::add_service('taxonomy_context.add_terms', 'Add multiple terms at once', ['namespace'=>__NAMESPACE__]);
function add_terms($atts, $content=null, $shortcode) {
    if(empty($atts['@context'])) {
        throw new \InvalidArgumentException('@context is empty or missing');
    }
    
    if(empty($atts['main'])) {
        throw new \InvalidArgumentException('Base path is missing');
    }
    
    $context = $atts['@context'];
    $base_path = $atts['main'];
    unset($atts['main']);
	
    $taxonomy = \aw2_library::get($context);
    if(!is_array($taxonomy)) {
        throw new \InvalidArgumentException("The taxonomy $context is missing");
    }

    // Get terms to add from content
    $terms_to_add = \aw2\common\build_array($atts, $content);
    // Find the parent term
    if($base_path!=='@new') {
        $parent = find_term($taxonomy, $base_path);
        if($parent === null) {
            throw new \InvalidArgumentException("Parent path $base_path not found");
        }
        
        // Traverse to the correct point in taxonomy
        $parts = explode('.', $base_path);
        $current = &$taxonomy;
        foreach($parts as $part) {
            if(!isset($current[$part])) {
                $current[$part] = ['meta' => [], 'children' => []];
            }
            if(!isset($current[$part]['children'])) {
                $current[$part]['children'] = [];
            }
            $current = &$current[$part]['children'];
        }
        
        // Add new terms at this location
        foreach($terms_to_add as $term_name => $term_data) {
            if(!isset($term_data['meta'])) {
                $term_data['meta'] = [];
            }
            if(!isset($term_data['children'])) {
                $term_data['children'] = [];
            }
            $current[$term_name] = $term_data;
        }
    } else {
        // Add at root level
        foreach($terms_to_add as $term_name => $term_data) {
            if(!isset($term_data['meta'])) {
                $term_data['meta'] = [];
            }
            if(!isset($term_data['children'])) {
                $term_data['children'] = [];
            }
            $taxonomy[$term_name] = $term_data;
        }
    }
    
    // Save updated taxonomy
    \aw2_library::set($context, $taxonomy);
    return true;
}