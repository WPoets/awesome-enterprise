<?php
namespace aw2\common\env;


// Base validation function for context handlers
// Base validation function for context handlers
function validate_context($atts,$type) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    $context=$atts['@context'];
    $is_within_context = \aw2\call_stack\is_within_context($type . $context);
    if($is_within_context === false) {
        throw new \OutOfBoundsException('You are accessing context outside of ' . $type);
    }
}



function set_handler($path, $value){
    return set($path, $value, 'handlers'); 
}

/*
 $path is a dot separated path 
eg::
user.profile.education

Special keywords

@arr:first -> shifts the current context to the first element of the array
@arr:last -> shifts the current context to the last element of the array
@arr:append -> adds a node at the bottom of the array and shifts the current context to the last element of the array
@arr:prepend -> adds a node at the top of the array and shifts the current context to the first element of the array
@arr:pos:<n> -> where n is an integer. Shifts the array to the nth position , 1 based

@obj:create -> Makes the current context an object
 */

 function set(string $path, $value, $start = null){
    try {
        // Input validation
        if (empty($path)) {
            throw new \Exception("Path cannot be empty");
        }
        if (strlen($path) > 1024) {
            throw new \Exception("Path exceeds maximum length");
        }

        // Get the reference to the environment array
        if ($start !== null) {
            $arr = &\aw2_library::get_array_ref($start);
        } else {
            $arr = &\aw2_library::get_array_ref();
        }

        // Split the path into parts
        $parts = explode('.', $path);
         // Check maximum nesting level
         if (count($parts) > 20) { // reasonable limit for nesting
            throw new \Exception("Path nesting level exceeds maximum allowed depth");
        }

        $current = &$arr;

        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
/*
            if (!preg_match('/^[@a-zA-Z0-9_\-:]+$/', $part)) {
                throw new \Exception("Invalid characters in path part: " . $part);
            }
*/
            if ($part === '' || is_null($part)) {
                throw new \Exception("Part cannot be empty");
            }

            // Handle array first element
            if ($part === '@arr:first') {
                if (!is_array($current)) {
                    throw new \Exception("Node is not an array when trying to access first element");
                }
                if (empty($current)) {
                    throw new \Exception("Cannot access first element of empty array");
                }
                reset($current);
                $current = &$current[key($current)];
                continue;
            }

            // Handle array last element
            if ($part === '@arr:last') {
                if (!is_array($current)) {
                    throw new \Exception("Node is not an array when trying to access last element");
                }
                if (empty($current)) {
                    throw new \Exception("Cannot access last element of empty array");
                }
                end($current);
                $current = &$current[key($current)];
                continue;
            }

            // Handle array append
            if ($part === '@arr:append') {
                if (!is_array($current)) {
                    $current = array();
                }
                $current[] = '#_not_set_#';
                end($current);
                $current = &$current[key($current)];
                continue;
            }

            // Handle array prepend
            if ($part === '@arr:prepend') {
                if (!is_array($current)) {
                    $current = array();
                }
                array_unshift($current, '#_not_set_#');
                reset($current);
                $current = &$current[key($current)];
                continue;
            }

            // Handle object creation
            if ($part === '@obj:create') {
                if (!is_object($current)) {
                    $current = new \stdClass();
                }
                continue;
            }

			// Handle position-based array access
            if (strpos($part, '@arr:pos:') === 0) {
                $position = (int)substr($part, 9);
                if ($position < 1) {
                    throw new \Exception("Array position must be greater than 0");
                }
                if (!is_array($current)) {
                    throw new \Exception("Node is not an array when trying to access position");
                }
                if ($position > count($current)) {
                    throw new \Exception("Array position {$position} is out of bounds");
                }
                $current = &$current[$position - 1];
                continue;
            }

            // Handle regular path parts
            if (is_object($current)) {
                if (!isset($current->{$part})) {
                    $current->{$part} = '#_not_set_#';
                }
                $current = &$current->{$part};
            } else {
                if (!is_array($current)) {
                    $current = array();
                }
                if (!isset($current[$part])) {
                    $current[$part] = '#_not_set_#';
                }
                $current = &$current[$part];
            }
        }

        $current = $value;

        return;

    } catch (\Exception $e) {
        \util::var_dump('exception');
        \util::var_dump($e->getMessage());
        return false;
    }
}


\aw2_library::add_service('env2.set', 'Set an Environment Value', ['func'=>'_set', 'namespace'=>__NAMESPACE__]);

/**
 * Sets environment values with optional prefix
 * 
 * @param array $atts Attributes containing key-value pairs to set in environment
 * @param string $content Optional content, defaults to '#*not_set_#'
 * @param mixed $shortcode Shortcode information
 * @return void
 */
function _set(array $atts, string $content = '#*not_set_#', $shortcode): void {
    // Extract prefix if exists
    $prefix = null;
    if (isset($atts['@prefix'])) {
        $prefix = $atts['@prefix'];
        unset($atts['@prefix']);
    }

    // Set each attribute in the environment
    foreach ($atts as $key => $value) {
        // Build the full key with prefix if it exists
        $full_key = $prefix ? $prefix . '.' . $key : $key;
        
        // Set the value in environment
        \aw2\common\env\set($full_key, $value);
    }
    
    return;
}




function get(string $path, $start = null) {
    // Input validation
        if ($path === '' || is_null($path)) {
            throw new \Exception("Path cannot be empty");
        }
        if (strlen($path) > 1024) {
            throw new \Exception("Path exceeds maximum length");
        }

        // Get the environment array
        if ($start !== null) {
            $arr = \aw2_library::get_array_ref($start);
        } else {
            $arr = \aw2_library::get_array_ref();
        }

        // Split the path into parts
        $parts = explode('.', $path);
        $current = $arr;
        
        // Check maximum nesting level
        if (count($parts) > 64) {
            throw new \Exception("Path nesting level exceeds maximum allowed depth");
        }

        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            
            // Check for empty part
            if ($part === '') {
                throw new \Exception("Path part cannot be empty");
            }
            
			/*
            // Validate path part characters
            if (!preg_match('/^[@a-zA-Z0-9_\-:]+$/', $part)) {
                throw new \Exception("Invalid characters in path part: " . $part);
            }
			*/
            // Handle array first element
            if ($part === '@arr:first') {
                if (!is_array($current)) {
                    return '#_does_not_exist_#';
                }
                if (empty($current)) {
                    return '#_does_not_exist_#';
                }
                reset($current);
                $current = $current[key($current)];
                continue;
            }

            // Handle array last element
            if ($part === '@arr:last') {
                if (!is_array($current)) {
                    return '#_does_not_exist_#';
                }
                if (empty($current)) {
                    return '#_does_not_exist_#';
                }
                end($current);
                $current = $current[key($current)];
                continue;
            }

            // Handle position-based array access
            if (strpos($part, '@arr:pos:') === 0) {
                $position = (int)substr($part, 9);
                if ($position < 1) {
                    return '#_does_not_exist_#';
                }
                if (!is_array($current)) {
                    return '#_does_not_exist_#';
                }
                if ($position > count($current)) {
                    return '#_does_not_exist_#';
                }
                $current = $current[$position - 1];
                continue;
            }

            // Handle regular path parts
            if (is_object($current)) {
                if (!isset($current->{$part})) {
                    return '#_does_not_exist_#';
                }
                $current = $current->{$part};
            } 
            elseif (is_array($current)) {
                if (!isset($current[$part])) {
                    return '#_does_not_exist_#';
                }
                $current = $current[$part];
            }
            else {
                return '#_does_not_exist_#';
            }
        }

        return $current;

}


\aw2_library::add_service('env2.get', 'Get an Environment Value', ['func'=>'_get', 'namespace'=>__NAMESPACE__]);

function _get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>'#_not_set_#'
	), $atts, 'aw2_get' ) );
	
    $prefix = null;
    if(isset($atts['@prefix'])){
        $prefix = $atts['@prefix'];
        unset($atts['@prefix']);
    }

	$return_value=\aw2\common\env\get($main,$prefix);
	
	if($return_value==='' && $default!=='#_not_set_#')$return_value=$default;
	return $return_value;
}
