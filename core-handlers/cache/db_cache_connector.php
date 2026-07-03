<?php
/**
 * MySQL Cache Connector
 * Provides services to interact with MySQL for caching, mimicking Redis functionality
 * 
 * Required database table structure:
 * CREATE TABLE `awesome_code_cache` (
 *   `cache_db` INT NOT NULL,
 *   `cache_key` VARCHAR(255) NOT NULL,
 *   `cache_field` VARCHAR(255) NOT NULL DEFAULT '',
 *   `cache_value` LONGTEXT NOT NULL,
 *   `expiry` TIMESTAMP NULL DEFAULT NULL,
 *   PRIMARY KEY (`cache_db`, `cache_key`, `cache_field`),
 *   INDEX `idx_expiry` (`expiry`)
 * )
 */
namespace aw2\cache\mysql;

/**
 * Set value in MySQL cache
 * @param array $atts Attributes including key, ttl, db, value
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.set', 'Set value in MySQL cache', ['namespace' => __NAMESPACE__]);
function set($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'key' => null,
        'ttl' => 300, // Time to live in minutes
        'db' => null,
        'value' => '#_notset_#',
        'connection' => 'default'
    ], $atts));
    
   // Validate required parameters
   if (!isset($db)) {
    throw new \InvalidArgumentException('mysql.cache.set: db parameter is required');
}

if (!$key) {
    throw new \InvalidArgumentException('mysql.cache.set: key parameter is required');
}

if ($value === '#_notset_#') {
    throw new \InvalidArgumentException('mysql.cache.set: value parameter is required');
}

try {
    // Calculate expiry timestamp using MySQL's DATE_ADD function
    $minutes = (int)$ttl;
    $sql = "INSERT INTO awesome_code_cache (cache_db, cache_key, cache_field, cache_value, expiry) 
            VALUES (" . $db . ", '" . \esc_sql($key) . "', '', '" 
            . \esc_sql($value) . "', DATE_ADD(NOW(), INTERVAL " . $minutes . " MINUTE))
            ON DUPLICATE KEY UPDATE 
            cache_value = '" . \esc_sql($value) . "',
            expiry = DATE_ADD(NOW(), INTERVAL " . $minutes . " MINUTE)";
       
  //$result = \aw2_library::get_results($sql);	
   $r=\aw2\mysqli\cud([],$sql,array());

    return '';
} catch (\Exception $e) {
    return 'MySQL Cache Error: ' . $e->getMessage();
}
}

/**
 * Set field value in MySQL cache (hash equivalent)
 * @param array $atts Attributes including main (hash name), field, ttl, db, value
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.hset', 'Set field value in MySQL cache', ['namespace' => __NAMESPACE__]);
function hset($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Hash name
        'field' => null,   // Field name within hash
        'value' => '#_notset_#',
        'ttl' => 300,      // Time to live in minutes
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.hset: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('mysql.cache.hset: main parameter is required');
    }
    
    if (!$field) {
        throw new \InvalidArgumentException('mysql.cache.hset: field parameter is required');
    }
    
    if ($value === '#_notset_#') {
        throw new \InvalidArgumentException('mysql.cache.hset: value parameter is required');
    }
    
    try {
        // Calculate expiry timestamp in a timezone-aware way
        // Use MySQL's DATE_ADD function to handle timezone properly
        $minutes = (int)$ttl;
        $sql = "INSERT INTO awesome_code_cache (cache_db, cache_key, cache_field, cache_value, expiry) 
                VALUES (" . $db . ", '" . \esc_sql($main) . "', '" . \esc_sql($field) . "', '" 
                . \esc_sql($value) . "', DATE_ADD(NOW(), INTERVAL " . $minutes . " MINUTE))
                ON DUPLICATE KEY UPDATE 
                cache_value = '" . \esc_sql($value) . "',
                expiry = DATE_ADD(NOW(), INTERVAL " . $minutes . " MINUTE)";
        
        // Execute query
        $r=\aw2\mysqli\cud([],$sql,array());
         
        
        return '';
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Get value from MySQL cache
 * @param array $atts Attributes including main (key), db
 * @return mixed Value from cache or error message
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.get', 'Get value from MySQL cache', ['namespace' => __NAMESPACE__]);
function get($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Key name
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.get: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('mysql.cache.get: main parameter is required');
    }
    
    try {
       
        // Prepare MySQL query
        $sql = "SELECT cache_value FROM awesome_code_cache 
                WHERE cache_db = " . $db . "
                AND cache_key = '" .  \esc_sql($main) . "'
                AND cache_field = '' 
                AND (expiry > NOW() OR expiry IS NULL)";
        // Execute query
        $result = \aw2_library::get_results($sql);
        // Check if result exists and return the value
        if (is_array($result) && !empty($result) && isset($result[0]->cache_value)) {
            return $result[0]->cache_value;
        }
        
        // Return empty string if no value found
        return '';
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Get field value from MySQL cache (hash equivalent)
 * @param array $atts Attributes including main (hash name), field, db
 * @return mixed Field value from hash or error message
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.hget', 'Get field value from MySQL cache', ['namespace' => __NAMESPACE__]);
function hget($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Hash name
        'field' => null,   // Field name within hash
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.hget: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('mysql.cache.hget: main parameter is required');
    }
    
    if (!$field) {
        throw new \InvalidArgumentException('mysql.cache.hget: field parameter is required');
    }
    
    try {
         // Prepare MySQL query
         $sql = "SELECT cache_value FROM awesome_code_cache 
         WHERE cache_db = " . $db . "
         AND cache_key = '" . \esc_sql($main) . "'
         AND cache_field = '" . \esc_sql($field) . "'
         AND (expiry > NOW() OR expiry IS NULL)";

       //  \util::var_dump($sql );
        // Execute query
        $result = \aw2_library::get_results($sql);
       // \util::var_dump( $result);
        // Check if result exists and return the value
        if (is_array($result) && !empty($result) && isset($result[0]['cache_value'])) {
           // \util::var_dump( $result[0]['cache_value']);
            return $result[0]['cache_value'];
        }
        
        // Return empty string if no value found
        return '';
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Check if key exists in MySQL cache
 * @param array $atts Attributes including main (key), db
 * @return boolean True if key exists, false otherwise
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.exists', 'Check if key exists in MySQL cache', ['namespace' => __NAMESPACE__]);
function exists($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Key name
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.exists: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('mysql.cache.exists: main parameter is required');
    }
    
    try {
        // Prepare MySQL query - we need to modify this to look for any field with this key
        $sql = "SELECT 1 FROM awesome_code_cache 
                WHERE cache_db = " . $db . "
                AND cache_key = '" . \esc_sql($main) . "'
                AND (expiry > NOW() OR expiry IS NULL)
                LIMIT 1";
        
        // Execute query
        $result = \aw2_library::get_results($sql);
        
        // For debugging, log the SQL and result
        // error_log("Cache exists check SQL: " . $sql);
        // error_log("Cache exists result: " . print_r($result, true));
        // Return boolean based on result
        return (!empty($result)) ? true : false;
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Flush entire MySQL cache db
 * @param array $atts Attributes including db
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.flush', 'Flush MySQL cache database', ['namespace' => __NAMESPACE__]);
function flush($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.flush: db parameter is required');
    }
    
    if (empty($db) || !is_numeric($db)) {
        throw new \InvalidArgumentException('mysql.cache.flush: db must be a valid integer.');
    }
    
    try {
        // Prepare MySQL query - delete only entries for specific db
        $sql = "DELETE FROM awesome_code_cache WHERE cache_db = " . $db;
        
        // Execute query
        $r=\aw2\mysqli\cud([],$sql,array());

        return '';
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Delete key from MySQL cache
 * @param array $atts Attributes including main (key), db
 * @return mixed Empty string on success, error message on failure
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.del', 'Delete key from MySQL cache', ['namespace' => __NAMESPACE__]);
function del($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Key name
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.del: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('mysql.cache.del: main parameter is required');
    }
    
    try {
        // Prepare MySQL query
        $sql = "DELETE FROM awesome_code_cache 
                WHERE cache_db = " . $db . "
                AND cache_key = '" . \esc_sql($main) . "'";
        
        // Execute query
        $r=\aw2\mysqli\cud([],$sql,array());

        return '';
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Clean expired items from MySQL cache
 * @param array $atts Attributes including connection
 * @return mixed Empty string on success, error message on failure
 */
\aw2_library::add_service('mysql.cache.clean', 'Remove expired items from MySQL cache', ['namespace' => __NAMESPACE__]);
function clean($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'connection' => 'default'
    ], $atts));
    
    try {
        // Prepare MySQL query
        $sql = "DELETE FROM awesome_code_cache WHERE expiry <= NOW()";
        
        // Execute query
       $r=\aw2\mysqli\cud([],$sql,array());
    

        return '';
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Get all keys matching a pattern
 * @param array $atts Attributes including pattern, db
 * @return array Array of matching keys
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.keys', 'Get all keys matching a pattern', ['namespace' => __NAMESPACE__]);
function keys($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'pattern' => '*',
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.keys: db parameter is required');
    }
    
    try {
        // Convert Redis-style pattern to SQL LIKE pattern
        $sql_pattern = str_replace('*', '%', $pattern);
        
        // Prepare MySQL query
        $sql = "SELECT DISTINCT cache_key FROM awesome_code_cache 
                WHERE cache_db = " . $db . "
                AND cache_key LIKE '" . \esc_sql($sql_pattern) . "'
                AND (expiry > NOW() OR expiry IS NULL)
                ORDER BY cache_key";
        
        $keys = \aw2_library::get_results($sql);
    
        return $keys ?: [];
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Get all fields in a hash
 * @param array $atts Attributes including main (hash name), db
 * @return array Array of field-value pairs
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.hgetall', 'Get all hash fields from MySQL cache', ['namespace' => __NAMESPACE__]);
function hgetall($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Hash name
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.hgetall: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('mysql.cache.hgetall: main parameter is required');
    }
    
    try {
        // Prepare MySQL query to get all fields for the key
        $sql = "SELECT cache_field, cache_value FROM awesome_code_cache 
                WHERE cache_db = " . $db . "
                AND cache_key = '" . \esc_sql($main) . "'
                AND cache_field != ''
                AND (expiry > NOW() OR expiry IS NULL)";
        
        // Execute query
        $results = \aw2_library::get_results($sql);
        
        // Convert result to hash format
        $hash = [];
        if (is_array($results) && !empty($results)) {
            foreach ($results as $row) {
                $hash[$row->cache_field] = $row->cache_value;
            }
        }
        
        return $hash;
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}

/**
 * Count fields in a hash
 * @param array $atts Attributes including main (hash name), db
 * @return int Number of fields in the hash
 * @throws \InvalidArgumentException If required parameters are missing
 */
\aw2_library::add_service('mysql.cache.hlen', 'Count hash fields in MySQL cache', ['namespace' => __NAMESPACE__]);
function hlen($atts, $content = null, $shortcode = null) {
    extract(\aw2_library::shortcode_atts([
        'main' => null,    // Hash name
        'db' => null,
        'connection' => 'default'
    ], $atts));
    
    // Validate required parameters
    if (!isset($db)) {
        throw new \InvalidArgumentException('mysql.cache.hlen: db parameter is required');
    }
    
    if (!$main) {
        throw new \InvalidArgumentException('mysql.cache.hlen: main parameter is required');
    }
    
    try {
        // Prepare MySQL query to count fields
        $sql = "SELECT COUNT(*) as field_count FROM awesome_code_cache 
                WHERE cache_db = " . $db . "
                AND cache_key = '" . \esc_sql($main) . "'
                AND cache_field != ''
                AND (expiry > NOW() OR expiry IS NULL)";
        
        // Execute query
        $result = \aw2_library::get_results($sql);
        
        // Return count
        if (is_array($result) && !empty($result) && isset($result[0]->field_count)) {
            return intval($result[0]->field_count);
        }
        
        return 0;
    } catch (\Exception $e) {
        return 'MySQL Cache Error: ' . $e->getMessage();
    }
}