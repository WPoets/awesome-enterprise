<?php
namespace aw2\func;

// Register the service
\aw2_library::add_service('func.add', 'Builds an array using the provided context and content', ['func'=>'add', 'namespace'=>__NAMESPACE__]);

/**
 * Builds an array using the provided context and content by adding a function to a namespace
 * 
 * @param array $atts Attributes including 'namespace' and function path
 * @param string $content Function content/code
 * @param object $shortcode Shortcode object
 * @return array Status array indicating success/failure
 * @throws \Exception If namespace or content is missing
 */

function add($atts, $content=null, $shortcode=null) {
    // Check if content is provided
    if(empty($content)) {
        throw new \Exception('content is required in func.add');
    }
    
    // Get reference to function stack
    $fstack = &\aw2_library::$funcstack;
    
    // Determine function name and namespace
    $func_name = null;
    $namespace = null;
    
	if(!empty(\aw2_library::get('namespace_build.info.namespace'))) {
		$namespace = \aw2_library::get('namespace_build.info.namespace');
	}

    // Case 1: main attribute contains namespace:func format
    if(!empty($atts['main']) && strpos($atts['main'], ':') !== false) {
        $parts = explode(':', $atts['main'], 2);
        $parts = explode(':', $atts['main'], 2);
        $namespace = $parts[0];
        $func_name = $parts[1];
    }
    // Case 2: main attribute contains just func name, namespace as separate attribute
    else if(!empty($atts['main'])) {
        $func_name = $atts['main'];
        
		if(empty($namespace)) {
            if(empty($atts['namespace'])) {
                throw new \Exception('namespace is required when main does not include namespace:func format');
            }
            
            $namespace = $atts['namespace'];
        }
    }
    // Handle missing main attribute
    else {
        throw new \Exception('main attribute is required in func.add');
    }
    
    // Final validation
    if(empty($namespace) || empty($func_name)) {
        throw new \Exception('Both namespace and function name are required in func.add');
    }
    
    // Ensure namespace exists
    if(!isset($fstack[$namespace])) {
        $fstack[$namespace] = [];
    }
    
    // Store the function in the fstack
    $fstack[$namespace][$func_name] = [
        'code' => $content
    ];
    
    // Add optional title if provided
    if(!empty($atts['title'])) {
        $fstack[$namespace][$func_name]['title'] = $atts['title'];
    }
    
    // Add optional description if provided
    if(!empty($atts['desc'])) {
        $fstack[$namespace][$func_name]['desc'] = $atts['desc'];
    }
    
    // Return success
    return '';
}
 




// Register the service
\aw2_library::add_service('func.service.create', 'Registers a function as a service in Awesome Enterprise', ['func'=>'create', 'namespace'=>__NAMESPACE__]);
/**
 * Registers a function as a service in Awesome Enterprise
 * 
 * @param array $atts Attributes including 'main' (service name), 'namespace', 'func', and optional 'desc'
 * @param string|null $content Not used in this service
 * @param object|null $shortcode Shortcode object
 * @return array Status array indicating success/failure
 * @throws \Exception If required parameters are missing
 */

function create($atts, $content=null, $shortcode=null) {
    // Check required parameters
    if(empty($atts['main'])) {
        throw new \Exception('main (service name) is required in func.service.create');
    }
    
    $service_name = $atts['main'];
    $func_name = null;
    $namespace = null;
    
    // Option 1: Using func and namespace separate attributes
    if(!empty($atts['func']) && !empty($atts['namespace'])) {
        $func_name = $atts['func'];
        $namespace = $atts['namespace'];
    }
    // Option 2: Using source attribute with namespace:func format
    else if(!empty($atts['source']) && strpos($atts['source'], ':') !== false) {
        $parts = explode(':', $atts['source'], 2);
        $namespace = $parts[0];
        $func_name = $parts[1];
    }
    // Final validation
    else {
        throw new \Exception('Both namespace and function name are required in func.service.create. Use either func/namespace attributes or source with namespace:func format.');
    }
    
    // Get reference to function stack to verify function exists
    $fstack = \aw2_library::$funcstack;
    
    if(!isset($fstack[$namespace]) || !isset($fstack[$namespace][$func_name])) {
        throw new \Exception("Function '$func_name' in namespace '$namespace' not found");
    }
    
    // Set up defaults array
    $defaults = array(
        'namespace' => $namespace,
        'func' => $func_name
    );
    
    // Get description if provided
    $description = isset($atts['desc']) ? $atts['desc'] : '';
    
    // Register the service
    \aw2_library::add_service(
        $service_name,
        $description,
        [
            'func' => 'run',
            'namespace' => __NAMESPACE__,
            '#defaults' => $defaults
        ]
    );
    
    return '';
}

// Register the service
\aw2_library::add_service('call', 'Calls a function within the same namespace', ['func'=>'func_call', 'namespace'=>__NAMESPACE__]);

/**
 * Calls a function within the same namespace
 * 
 * @param array $atts Attributes for the function call
 * @param string|null $content Content passed to the function
 * @param object $shortcode Shortcode object containing tags_left
 * @return mixed Result of the function call
 * @throws \Exception If function path or namespace is invalid
 */
function func_call($atts, $content=null, $shortcode=null) {
    // Validate function path is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \Exception('you must specify the function to call');
    }

    // Get current namespace
    $namespace = \aw2_library::get('namespace.info.namespace');
    if(empty($namespace)) {
        throw new \Exception('current namespace not found');
    }

    // Get function parts
    $func_name =implode('.', $shortcode['tags_left']);

    // Get reference to function stack
    $fstack = &\aw2_library::$funcstack;
    
    // Check if namespace exists
    if(!isset($fstack[$namespace][$func_name])) {
        throw new \Exception("$func_name or namespace $namespace not found in function stack");
    }

	$code=$fstack[$namespace][$func_name]['code'];


    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('func', 'func', 'func');
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);

    $call_stack['atts']=$atts;
    $call_stack['content']=$content;
    $info = &$call_stack['info'];
    $info['namespace'] = $namespace;
    $info['func'] = implode('.', $shortcode['tags_left']);

    // Parse and execute the function code
    $reply = \aw2_library::parse_shortcode($code);
    
    // Clean up stack
    \aw2\call_stack\pop_context($stack_id);
    
    return $reply;
}


/**
 * Runs a registered function with the provided settings
 * 
 * @param array $atts Attributes for the function
 * @param string|null $content Content passed to the function
 * @param object $shortcode Shortcode object containing handler settings
 * @return mixed Result of the function execution
 * @throws \Exception If namespace or function is not found
 */
function run($atts, $content=null, $shortcode=null) {
	// Get settings from handler
	if(isset($shortcode['handler']['$defaults']))
		$settings = $shortcode['handler']['$defaults'];

	if(isset($shortcode['handler']['#defaults']))
		$settings = $shortcode['handler']['#defaults'];
	
	//throw exception if not found	
	$namespace = $settings['namespace'];
	$func = $settings['func'];

	$def=null;
	//find the func
	//step 1 check the stack
	// Get reference to function stack
	$fstack = &\aw2_library::$funcstack;
	// Check if namespace exists
	if(isset($fstack[$namespace][$func])) {
		$def=$fstack[$namespace][$func];
	}
	
	//if not found then get from connection
	if(is_null($def)){
		// Create parameters for folder_conn.service.get
		$params = array('namespace' => $namespace,'func' => $func, 'connection' => $settings['connection']);
		
		// Get the service definition
		$def = \aw2\url_conn\func\get($params);
		$fstack = &\aw2_library::$funcstack;
		$fstack[$namespace][$func]=$def;
	}

	// Set up namespace context
	$stack_id = \aw2\call_stack\push_context('namespace', 'namespace', 'namespace');
	$call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
	$info = &$call_stack['info'];
	$info['namespace'] = $namespace;

	// Set up function context
	$func_stack_id = \aw2\call_stack\push_context('func', 'func', 'func');
	$func_call_stack = &\aw2_library::get_array_ref('call_stack', $func_stack_id);
	$func_call_stack['atts'] = $atts;
	$func_call_stack['content'] = $content;
	$func_info = &$func_call_stack['info'];
	$func_info['namespace'] = $namespace;
	$func_info['func'] = $func;

	// Parse and execute the function code
	$reply = \aw2_library::parse_shortcode($def['code']);

	$ref = &\aw2_library::get_array_ref('func');
	if(isset($ref['_return'])){
		$r=&\aw2_library::get_array_ref();
		unset($r['_return']);
		$reply=$ref['_return'];
	}
	
    \aw2\call_stack\pop_context($stack_id);
	
	return $reply;

}

/*
\aw2_library::add_service('func.get','Get a func Value',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode = array()){
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>'#_not_set_#'
	), $atts, 'aw2_get' ) );
	
	$main='func.' . $main;
	$return_value=\aw2_library::get($main);
	if(($return_value==='' || is_null($return_value)) && $default!=='#_not_set_#')$return_value=$default;
	return $return_value;
}
*/


// Register the service
\aw2_library::add_service('func.set', 'Set a func Value', ['namespace'=>__NAMESPACE__]);

/**
 * Sets values using aw2_library::set with support for multiple formats
 * [func.set key=value /]
 * [func.set key.path=value key.path=value /]
 * [func.set main=value path='func path' /]
 * [func.set path='func path']content[/func.set]
 * 
 * @param array $atts Attributes including key/value pairs or path
 * @param string|null $content Optional content for path based setting
 * @param object $shortcode Shortcode object
 * @return array Empty array as service always returns void
 * @throws \Exception If required parameters are missing or invalid
 */

//deprecated
function set($atts, $content=null, $shortcode=null) {
	// Case 1: Simple key-value pair
	// [func.set key=value /]
	$initial='func';
	if(isset($atts['key']) && isset($atts['value'])) {
		\aw2_library::set($initial . '.' . $atts['key'], $atts['value']);
		return;
	}

	// Case 3: Main value with path
	// [func.set main=value path='func path' /]
	if(isset($atts['main']) && isset($atts['path'])) {
		\aw2_library::set($initial . '.' .$atts['path'], $atts['main']);
		return;
	}
	
	// Case 2: Path based content
	// [func.set path='func path']content[/func.set]
	if(isset($atts['path'])) {
		\aw2_library::set($initial . '.' .$atts['path'], $content);
		return;
	}

	// Case 4: Multiple key.path=value pairs
	// [func.set key.path=value key.path=value /]
	$has_keypaths = false;
	foreach($atts as $keypath => $value) {
		if(strpos($keypath, 'key.') === 0) {
			$has_keypaths = true;
			$actual_path = substr($keypath, 4); // Remove 'key.' prefix
			\aw2_library::set($initial . '.' . $actual_path, $value);
		}
	}
	
	if($has_keypaths) {
		return;
	}

	throw new \Exception('Invalid parameters for func.set');

}


\aw2_library::add_service('func.return','Return an active func',['func'=>'_return','namespace'=>__NAMESPACE__]);

function _return($atts,$content=null,$shortcode = array()){
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=\aw2_library::get($main);
	\aw2_library::set('_return',true);	
	\aw2_library::set('func._return',$return_value);
	return;
}


// Register basic func services
\aw2_library::add_service('func.get', 'Get a func Value', ['namespace' => __NAMESPACE__]);
function get($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\get($atts);
}

// Register basic func services
\aw2_library::add_service('func.exists', 'Check existence of a path', ['namespace' => __NAMESPACE__]);
function exists($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\exists($atts);
}


\aw2_library::add_service('func.dump', 'Dump func Value', ['namespace' => __NAMESPACE__]);
function dump($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\dump($atts);
}

\aw2_library::add_service('func.echo', 'Echo func Value', ['func' => '_echo', 'namespace' => __NAMESPACE__]);
function _echo($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    \aw2\common\env_services\_echo($atts);
}

// Additional set services
\aw2_library::add_service('func.set.path', 'Set func Value with Path', ['func' => 'set_path','namespace' => __NAMESPACE__]);
function set_path($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\set_path($atts);
}

\aw2_library::add_service('func.set.paths', 'Set multiple func Values with Paths', ['func' => 'set_paths','namespace' => __NAMESPACE__]);
function set_paths($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\set_paths($atts);
}

\aw2_library::add_service('func.set.value', 'Set func Value directly', ['func' => 'set_value','namespace' => __NAMESPACE__]);
function set_value($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\set_value($atts);
}

\aw2_library::add_service('func.set.content', 'Set func Value from Content', ['func' => 'set_content','namespace' => __NAMESPACE__]);
function set_content($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\set_content($atts, $content);
}

\aw2_library::add_service('func.set.raw', 'Set Raw unparsed Content to func', ['func' => 'set_raw','namespace' => __NAMESPACE__]);
function set_raw($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'func';
    return \aw2\common\env_services\set_raw($atts, $content);
}