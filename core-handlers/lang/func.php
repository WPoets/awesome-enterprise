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
	// Check required parameters
	if(empty($atts['namespace'])) {
		throw new \Exception('namespace is required in func.add');
	}
	
	if(empty($content)) {
		throw new \Exception('content is required in func.add');
	}

	// Get reference to function stack
	$fstack = &\aw2_library::$funcstack;

	// Ensure namespace exists
	if(!isset($fstack[$atts['namespace']])) {
		$fstack[$atts['namespace']] = [];
	}

	// Get main attribute (function path)
	if(empty($atts['main'])) {
		throw new \Exception('function path is required in func.add');
	}

	// Split function path into parts
	$parts = explode('.', $atts['main']);
	
	// Reference to current level in fstack
	$current = &$fstack[$atts['namespace']];
	
	// Navigate through parts to build nested structure
	$last_part = array_pop($parts); // Remove and store last part
	
	foreach($parts as $part) {
		if(!isset($current[$part])) {
			$current[$part] = [];
		}
		$current = &$current[$part];
	}
	
	// Set the function content at final level
	$current[$last_part]['code'] = $content;

	// Return success
	return;
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
            throw new \Exception('main (service name) is required in func.create');
        }
        
        if(empty($atts['namespace'])) {
            throw new \Exception('namespace is required in func.create');
        }
        
        if(empty($atts['func'])) {
            throw new \Exception('func is required in func.register');
        }

        // Set up settings array
        $defaults = array(
            'namespace' => $atts['namespace'],
            'func' => $atts['func']
        );

        // Register the service with aw2_library
        \aw2_library::add_service(
            $atts['main'],
            isset($atts['desc']) ? $atts['desc'] : '',
            [
                'func' => 'run',
                'namespace' => __NAMESPACE__,
                '$defaults' => $defaults
            ]
        );

        // Return success
        return;
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

    // Get function parts
    $func = $shortcode['tags_left'];
 
    // Get current namespace
    $namespace = \aw2_library::get('namespace.info.namespace');
    if(empty($namespace)) {
        throw new \Exception('current namespace not found');
    }

    // Get reference to function stack
    $fstack = &\aw2_library::$funcstack;
    
    // Check if namespace exists
    if(!isset($fstack[$namespace])) {
        throw new \Exception("namespace $namespace not found in function stack");
    }

    // Navigate through function path to find code
    $current = $fstack[$namespace];
    foreach($func as $part) {
        if(!isset($current[$part])) {
            throw new \Exception("function part $part not found in namespace $namespace");
        }
        $current = $current[$part];
    }


    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('func', 'func', 'func');
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);

    $call_stack['atts']=$atts;
    $call_stack['content']=$content;
    $info = &$call_stack['info'];
    $info['namespace'] = $namespace;
    $info['func'] = implode('.', $shortcode['tags_left']);

    // Parse and execute the function code
    $reply = \aw2_library::parse_shortcode($current['code']);
    
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
	$settings = $shortcode['handler']['$defaults'];
	$namespace = $settings['namespace'];
	$func = $settings['func'];

	// Get reference to function stack
	$fstack = &\aw2_library::$funcstack;
	
	// Check if namespace exists
	if(!isset($fstack[$namespace])) {
		throw new \Exception("namespace $namespace not found in function stack");
	}

	// Navigate through function path to find code
	$current = $fstack[$namespace];
	
	// Split function path on dots
	$func_parts = explode('.', $func);
	
	foreach($func_parts as $part) {
		if(!isset($current[$part])) {
			throw new \Exception("function part $part not found in namespace $namespace");
		}
		$current = $current[$part];
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

   //\util::var_dump($current);
	// Parse and execute the function code
	$reply = \aw2_library::parse_shortcode($current['code']);

	$ref = &\aw2_library::get_array_ref('func');
	if(isset($ref['_return'])){
		$r=&\aw2_library::get_array_ref();
		unset($r['_return']);
		$reply=$ref['_return'];
	}
	//\util::var_dump($reply);	
    \aw2\call_stack\pop_context($stack_id);
	
	return $reply;

}


\aw2_library::add_service('func.get','Get a func Value',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode){
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	$main='func.' . $main;
	$return_value=\aw2_library::get($main);
	if(($return_value==='' || is_null($return_value)) && $default!=='##not_set##')$return_value=$default;
	return $return_value;
}

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
function set($atts, $content=null, $shortcode=null) {
	// Case 1: Simple key-value pair
	// [func.set key=value /]
	$initial='func';
	if(isset($atts['key']) && isset($atts['value'])) {
		\aw2_library::set($initial . '.' . $atts['key'], $atts['value']);
	}

	// Case 2: Path based content
	// [func.set path='func path']content[/func.set]
	if(isset($atts['path']) && $content !== null) {
		\aw2_library::set($initial . '.' .$atts['path'], $content);
		return;
	}

	// Case 3: Main value with path
	// [func.set main=value path='func path' /]
	if(isset($atts['main']) && isset($atts['path'])) {
		\aw2_library::set($initial . '.' .$atts['path'], $atts['main']);
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


\aw2_library::add_service('func.dump','Dump func Value',['namespace'=>__NAMESPACE__]);

function dump($atts,$content=null,$shortcode){

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'dump' ) );

	$c='func';
	if($main)$c.='.' . $main ;

	$return_value=\aw2_library::get($c);
	$return_value=\util::var_dump($return_value,true);
	return $return_value;
}

\aw2_library::add_service('func.echo','Echo func Value',['func'=>'_echo','namespace'=>__NAMESPACE__]);

function _echo($atts,$content=null,$shortcode){

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'dump' ) );

	$c='func';
	if($main)$c.='.' . $main ;

	$return_value=\aw2_library::get($c);
	\util::var_dump($return_value);
}


\aw2_library::add_service('func.return','Return an active func',['func'=>'_return','namespace'=>__NAMESPACE__]);

function _return($atts,$content=null,$shortcode){
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=\aw2_library::get($main);
	\aw2_library::set('_return',true);	
	\aw2_library::set('func._return',$return_value);
	return;
}