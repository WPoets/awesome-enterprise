<?php
/**
 * Redis Cache Connector
 * Provides services to interact with Redis for caching
 */
namespace aw2\cache\redis;

/**
 * Set value in Redis cache
 * @param array $atts Attributes including key, prefix, ttl, db
 * @param string $content Content to store if value not provided
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('redis.set', 'Set value in Redis cache', ['namespace' => __NAMESPACE__]);
function set($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'key' => null,
        'ttl' => 300, // Time to live in minutes
        'db' => null,
		'value'=>'#_notset_#'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('redis.set: db parameter is required');
    }
    
    if (!$key) {
        throw new \InvalidArgumentException('redis.set: key parameter is required');
    }
	
    if ($value==='#_notset_#') {
        throw new \InvalidArgumentException('redis.set: value parameter is required');
    }
    
    try {
        // Connect to Redis
        $redis = \aw2_library::redis_connect($db);
        
        // Set value and expiration
        $redis->set($key, $value);
        $redis->expire($key, $ttl * 60); // Convert minutes to seconds
        
        return '';
    } catch (\Exception $e) {
        return 'Redis Error: ' . $e->getMessage();
    }
}

/**
 * Set value in Redis hash
 * @param array $atts Attributes including main (hash name), field, prefix, ttl, db
 * @param string $content Content to store if value not provided
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('redis.hset', 'Set field value in Redis hash', ['namespace' => __NAMESPACE__]);
function hset($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Hash name
        'field' => null,   // Field name within hash
		'value'=>'#_notset_#',
        'ttl' => 300,      // Time to live in minutes
        'db' => null
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('redis.hset: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('redis.hset: main parameter is required');
    }
    
    if (!$field) {
        throw new \InvalidArgumentException('redis.hset: field parameter is required');
    }
    
    if ($value==='#_notset_#') {
        throw new \InvalidArgumentException('redis.set: value parameter is required');
    }    
    
    try {
        // Connect to Redis
        $redis = \aw2_library::redis_connect($db);
        
        // Set hash field value and expiration
        $redis->hset($main, $field, $value);
        $redis->expire($main, $ttl * 60); // Convert minutes to seconds
        
        return '';
    } catch (\Exception $e) {
        return 'Redis Error: ' . $e->getMessage();
    }
}

/**
 * Get value from Redis cache
 * @param array $atts Attributes including main (key), prefix, db
 * @return mixed Value from cache or error message
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('redis.get', 'Get value from Redis cache', ['namespace' => __NAMESPACE__]);
function get($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Key name
        'db' => null
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('redis.get: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('redis.get: main parameter is required');
    }
    
    try {
        // Connect to Redis
        $redis = \aw2_library::redis_connect($db);
        
        // Get value if key exists
        $return_value = '';
        if ($redis->exists($main)) {
            $return_value = $redis->get($main);
        }
        
        return $return_value;
    } catch (\Exception $e) {
        return 'Redis Error: ' . $e->getMessage();
    }
}

/**
 * Get field value from Redis hash
 * @param array $atts Attributes including main (hash name), field, prefix, db
 * @return mixed Field value from hash or error message
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('redis.hget', 'Get field value from Redis hash', ['namespace' => __NAMESPACE__]);
function hget($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Hash name
        'field' => null,   // Field name within hash
        'db' => null
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('redis.hget: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('redis.hget: main parameter is required');
    }
    
    if (!$field) {
        throw new \InvalidArgumentException('redis.hget: field parameter is required');
    }
    
    try {
        // Connect to Redis
        $redis = \aw2_library::redis_connect($db);
        
        // Get hash field value if hash exists
        $return_value = '';
        if ($redis->exists($main)) {
            $return_value = $redis->hget($main, $field);
        }
        
        return $return_value;
    } catch (\Exception $e) {
        return 'Redis Error: ' . $e->getMessage();
    }
}

/**
 * Check if key exists in Redis cache
 * @param array $atts Attributes including main (key), prefix, db
 * @return boolean True if key exists, false otherwise
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('redis.exists', 'Check if key exists in Redis cache', ['namespace' => __NAMESPACE__]);
function exists($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Key name
        'db' => null
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('redis.exists: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('redis.exists: main parameter is required');
    }
    
    
    try {
        // Connect to Redis
        $redis = \aw2_library::redis_connect($db);
        
        // Check if key exists
        $return_value = false;
        if ($redis->exists($main)) {
            $return_value = true;
        }
        
        return $return_value;
    } catch (\Exception $e) {
        return 'Redis Error: ' . $e->getMessage();
    }
}

/**
 * Flush entire Redis db
 * @param array $atts Attributes including db
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('redis.flush', 'Flush Redis database', ['namespace' => __NAMESPACE__]);
function flush($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'db' => null
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('redis.flush: db parameter is required');
    }
    
    if (empty($db) || !is_numeric($db)) {
        throw new \InvalidArgumentException('redis.flush: db must be a valid integer.');
    }
    
    try {
        // Connect to Redis
        $db = intval($db);
        $redis = \aw2_library::redis_connect($db);
        
        // Flush database
        $redis->flushdb();
        
        return '';
    } catch (\Exception $e) {
        return 'Redis Error: ' . $e->getMessage();
    }
}

/**
 * Delete key from Redis cache
 * @param array $atts Attributes including main (key), prefix, db
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('redis.del', 'Delete key from Redis cache', ['namespace' => __NAMESPACE__]);

function del($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Key name
        'prefix' => '',
        'db' => null
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('redis.del: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('redis.del: main parameter is required');
    }
    
    try {
        // Connect to Redis
        $redis = \aw2_library::redis_connect($db);
        
        // Delete key if exists
        if ($redis->exists($main)) {
            $redis->del($main);
        }
        
        return '';
    } catch (\Exception $e) {
        return 'Redis Error: ' . $e->getMessage();
    }
}