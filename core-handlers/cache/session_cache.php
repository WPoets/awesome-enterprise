<?php
namespace aw2\session_cache;

\aw2_library::add_service('session_cache','Session Cache Library',['namespace'=>__NAMESPACE__]);

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
	//define( 'CACHE_CONNECTOR', 'mysql/redis' );
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
                $result = $function($atts, $content, $shortcode);
                // Ensure we're not returning errors as strings
                if (is_string($result) && strpos($result, 'MySQL Cache Error:') === 0) {
                    error_log($result); // Log the error
                    return ($operation == 'exists') ? false : '';
                }
                return $result;
            }
        } else {
            // Default to Redis connector
            $function = "\\aw2\\cache\\redis\\$operation";
            if (function_exists($function)) {
                $result = $function($atts, $content, $shortcode);
                // Ensure we're not returning errors as strings
                if (is_string($result) && strpos($result, 'Redis Error:') === 0) {
                    error_log($result); // Log the error
                    return ($operation == 'exists') ? false : '';
                }
                return $result;
            }
        }
        
        // If we reach here, the function doesn't exist in the selected connector
        error_log("Error: Operation '$operation' not supported by backend '$backend'");
        return ($operation == 'exists') ? false : '';
    } catch (\Exception $e) {
        error_log("Cache Error: " . $e->getMessage());
        return ($operation == 'exists') ? false : '';
    }
}

\aw2_library::add_service('session_cache.set','Set Session Cache',['namespace'=>__NAMESPACE__]);
function set($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'ttl' => 60,
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1
    ], $atts);
    
    // Check for required key
    if (!isset($atts['key'])) {
        return 'Invalid Key';
    }
    
    // Apply prefix to key if specified
    if (!empty($atts['prefix'])) {
        $atts['key'] = $atts['prefix'] . $atts['key'];
        unset($atts['prefix']);
    }
    
    // Ensure value is properly set
    if (!isset($atts['value']) && $content !== null) {
        $atts['value'] = $content;
    }
    
    $result = route_cache_operation('set', $atts, $content, $shortcode);
	return $result;
}

\aw2_library::add_service('session_cache.get','Get Session Cache',['namespace'=>__NAMESPACE__]);
function get($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1,
        'backend' => 'redis' // Default to redis for backward compatibility
    ], $atts);
    
    // Check for required main parameter
    if (!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (key) if specified
    if (!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('get', $atts, $content, $shortcode);
	return $result;
}

\aw2_library::add_service('session_cache.hset','Set Hash Field in Session Cache',['namespace'=>__NAMESPACE__]);
function hset($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'ttl' => 60,
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1
    ], $atts);
    
    // Ensure required parameters
    if (!isset($atts['main'])) {
        return 'Invalid key';
    }
    
    if (!isset($atts['field'])) {
        return 'Invalid field';
    }
    
    // Apply prefix to main (hash name) if specified
    if (!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    // Ensure value is properly extracted from content if not provided in atts
    if (!isset($atts['value'])) {
        $atts['value'] = $content;
    }
    
    $result = route_cache_operation('hset', $atts, $content, $shortcode);
	return $result;
}

\aw2_library::add_service('session_cache.hget','Get Hash Field from Session Cache',['namespace'=>__NAMESPACE__]);
function hget($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1
    ], $atts);
    
    // Check for required main parameter
    if (!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (hash name) if specified
    if (!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    // If no field is specified, use hgetall operation
    if (!isset($atts['field'])) {
        $result = route_cache_operation('hgetall', $atts, $content, $shortcode);
		return $result;
    }
    
    // Otherwise use standard hget
    $result = route_cache_operation('hget', $atts, $content, $shortcode);
	return $result;
}

\aw2_library::add_service('session_cache.exists','Check if key exists in Session Cache',['namespace'=>__NAMESPACE__]);
function exists($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1
    ], $atts);
    
    // Check for required main parameter
    if (!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (key) if specified
    if (!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('exists', $atts, $content, $shortcode);
	return $result;
}

\aw2_library::add_service('session_cache.del','Delete key from Session Cache',['namespace'=>__NAMESPACE__]);
function del($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1
    ], $atts);
    
    // Check for required main parameter
    if (!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (key) if specified
    if (!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    $result = route_cache_operation('del', $atts, $content, $shortcode);
    return \aw2_library::post_actions('all', $result, $atts);
}

\aw2_library::add_service('session_cache.hlen','Get hash count of a key',['namespace'=>__NAMESPACE__]);
function hlen($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'prefix' => '',
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1
    ], $atts);
    
    // Check for required main parameter
    if (!isset($atts['main'])) {
        return 'Main must be set';
    }
    
    // Apply prefix to main (key) if specified
    if (!empty($atts['prefix'])) {
        $atts['main'] = $atts['prefix'] . $atts['main'];
        unset($atts['prefix']);
    }
    
    // Use the router to call the appropriate hlen function
    $result = route_cache_operation('hlen', $atts, $content, $shortcode);
    return $result;
}

\aw2_library::add_service('session_cache.flush','Flush Session Cache',['namespace'=>__NAMESPACE__]);
function flush($atts, $content = null, $shortcode = null) {
    if(\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) return;
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Set default attributes if not provided
    $atts = array_merge([
        'db' => defined('REDIS_DATABASE_SESSION_CACHE') ? REDIS_DATABASE_SESSION_CACHE : 1
    ], $atts);
    
    $result = route_cache_operation('flush', $atts, $content, $shortcode);
	return $result;
}

// Add MySQL-specific clean function to purge expired cache entries
\aw2_library::add_service('session_cache.clean','Clean expired cache entries (MySQL only)',['namespace'=>__NAMESPACE__]);
function clean($atts, $content = null, $shortcode = null) {
  
    
    // Ensure $atts is an array
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Force MySQL backend for this operation as it's MySQL-specific
    $atts['backend'] = 'mysql';
    
    $result = route_cache_operation('clean', $atts, $content, $shortcode);
    return $result;
}