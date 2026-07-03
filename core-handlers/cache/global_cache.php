<?php
namespace aw2\global_cache;

\aw2_library::add_service('global_cache','Global Cache Library',['namespace'=>__NAMESPACE__]);

/**
 * Route cache operations to the appropriate backend (Redis or MySQL)
 * @param string $operation Operation name (set, get, hset, etc.)
 * @param array $atts Attributes for the operation
 * @param string|null $content Content to use as value if applicable
 * @param string|null $shortcode The shortcode that was used
 * @return mixed Result of the operation
 */
function route_cache_operation($operation, $atts, $content = null, $shortcode = null) {
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
	// Extract backend from attributes, default to redis 
    $backend = 'redis';

	//define( 'CACHE_CONNECTOR', 'mysqli/redis' );
	if(defined('CACHE_CONNECTOR')){
		$backend = CACHE_CONNECTOR;
	}

    if (isset($atts['backend'])) {
        $backend = $atts['backend'];
        // Remove backend from attributes to avoid passing it to connectors
        unset($atts['backend']);
    }
    
    // Route to the appropriate backend
    try {
        if ($backend === 'mysql') {
            // Call the MySQL cache connector
            $function = "\\aw2\\cache\\mysql\\$operation";
            if (function_exists($function)) {
                return $function($atts, $content, $shortcode);
            }
        } else {
            // Default to Redis connector
            $function = "\\aw2\\cache\\redis\\$operation";
            if (function_exists($function)) {
                return $function($atts, $content, $shortcode);
            }
        }
        
        // If we reach here, the function doesn't exist in the selected connector
        return "Error: Operation '$operation' not supported by backend '$backend'";
    } catch (\Exception $e) {
        return "Cache Error: " . $e->getMessage();
    }
}

\aw2_library::add_service('global_cache.set','Set the Global Cache',['namespace'=>__NAMESPACE__]);
function set($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false) return;
     // Ensure $atts is an array
	 if (!is_array($atts)) {
        $atts = [];
    }

    // Ensure value is properly extracted from content if not provided in atts
    if(!isset($atts['value'])) {
        $atts['value'] = $content;
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'ttl' => 300,
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0
    ], $atts);
    
    // Apply prefix to key if specified
    if(!empty($atts['prefix']) && isset($atts['key'])) {
        $atts['key'] = $atts['prefix'] . $atts['key'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('set', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('global_cache.hset','Set field in the Global Cache hash',['namespace'=>__NAMESPACE__]);
function hset($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false) return;
     // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }

    // Ensure value is properly extracted from content if not provided in atts
    if(!isset($atts['value'])) {
        $atts['value'] = $content;
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'ttl' => 300,
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0
    ], $atts);
    
    // Apply prefix to main (hash key) if specified
    if(!empty($atts['prefix']) && isset($atts['main'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('hset', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('global_cache.get','Get from the Global Cache',['namespace'=>__NAMESPACE__]);
function get($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false) return;
     // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }

    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0
    ], $atts);
    
    // Check for required main attribute
    if(!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (key) if specified
    if(!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('get', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('global_cache.hget','Get field from the Global Cache hash',['namespace'=>__NAMESPACE__]);
function hget($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false) return;
     // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }

    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0
    ], $atts);
    
    // Check for required attributes
    if(!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    if(!isset($atts['field'])) {
        return 'Invalid field';
    }
    
    // Apply prefix to main (hash key) if specified
    if(!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('hget', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('global_cache.exists','Check if key exists in the Global Cache',['namespace'=>__NAMESPACE__]);
function exists($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false) return;
     // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }

    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0
    ], $atts);
    
    // Check for required main attribute
    if(!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (key) if specified
    if(!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('exists', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('global_cache.flush','Flush the Global Cache',['namespace'=>__NAMESPACE__]);
function flush($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
	
	 // Ensure $atts is an array
	 if (!is_array($atts)) {
        $atts = [];
    }

    // Set default attributes if not provided
    $atts = array_merge([
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0
    ], $atts);
    
    // Validate db parameter
    if(empty($atts['db'])) {
        throw new \InvalidArgumentException('global_cache.flush: db is empty must be an integer.');
    }
    
    $result = route_cache_operation('flush', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('global_cache.del','Delete a Key from the Global Cache',['namespace'=>__NAMESPACE__]);
function del($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
     // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }

    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0
    ], $atts);
    
    // Check for required main attribute
    if(!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (key) if specified
    if(!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('del', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('global_cache.run','Run with caching',['namespace'=>__NAMESPACE__]);
function run($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false) return;
 	// Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }    
    // Set default attributes if not provided
    $atts = array_merge([
        'ttl' => 30,
        'db' => defined('REDIS_DATABASE_GLOBAL_CACHE') ? REDIS_DATABASE_GLOBAL_CACHE : 0,
        'backend' => 'redis' // Default to redis for backward compatibility
    ], $atts);
    
    // Check for required main attribute
    if(!isset($atts['main'])) {
        return 'Main key must be set';
    }
    
    // First try to get cached value
    $cached = route_cache_operation('get', $atts, null, $shortcode);
    
    if (!empty($cached)) {
        return \aw2_library::post_actions('all', $cached, $atts);
    }
    
    // If no cached value, execute content
    $result = \aw2_library::parse_shortcode($content);
    
    // Cache the result
    $set_atts = [
        'key' => $atts['main'],
        'value' => $result,
        'ttl' => $atts['ttl'],
        'db' => $atts['db'],
        'backend' => $atts['backend']
    ];
    
    route_cache_operation('set', $set_atts, null, $shortcode);
    
    return \aw2_library::post_actions('all', $result, $atts);
}
