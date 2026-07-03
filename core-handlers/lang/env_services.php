<?php
namespace aw2\common\env_services;

/**
 * Get a value from the environment
 * 
 * @param array $atts The function attributes
 * @return mixed The value from the environment or default if provided
 * @throws \Exception If required parameters are missing
 */
function get($atts,$content = null, $shortcode=null) {
    // Extract attributes with proper defaults
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'start' => null,
        'default' => '#_not_set_#'
    ), $atts, 'aw2_get'));
    
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }
    
    // Get value from environment
    $return_value = \aw2\common\env\get($main, $start);
    
	if($return_value === '#_does_not_exist_#')
		$return_value ='';
	
    // Return default if value is empty and default is provided
    if (($return_value === '' || $return_value === null) && $default !== '#_not_set_#') {
        $return_value = $default;
    }
    
    return $return_value;
}

/**
 * Check if a path exists in the environment
 * 
 * @param array $atts The function attributes
 * @return bool True if the path exists, false otherwise
 * @throws \Exception If required parameters are missing
 */
function exists($atts,$content = null, $shortcode=null) {
    // Extract attributes with proper defaults
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'start' => null
    ), $atts, 'aw2_exists'));
    
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }
    
    // Construct full path
    $path = $main ? $start . '.' . $main : $start;
    
    // Check if path exists
    $value = \aw2\common\env\get($path, null);
    return ($value !== '#_does_not_exist_#');
}

/**
 * Dump a value from the environment with formatting
 * 
 * @param array $atts The function attributes
 * @return string A formatted dump of the value
 * @throws \Exception If required parameters are missing
 */
function dump($atts,$content = null, $shortcode=null) {
    // Extract attributes with proper defaults
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'start' => null
    ), $atts, 'dump'));
    
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }
    
    // Construct path based on provided attributes
    $path = $main !== null ? $start . '.' . $main : $start;
    
    // Get value from environment
    $return_value = \aw2\common\env\get($path, null);
    
    // Format and return value
    return \util::var_dump($return_value, true);
}

/**
 * Echo a value from the environment
 * 
 * @param array $atts The function attributes
 * @param string|null $content The content between tags
 * @param string $shortcode The shortcode name
 * @throws \Exception If required parameters are missing
 */
function _echo($atts, $content = null, $shortcode=null) {
    // Extract attributes with proper defaults
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'start' => null
    ), $atts, 'echo'));
    
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }
    
    // Construct path based on provided attributes
    $path = $main !== null ? $start . '.' . $main : $start;
    
    // Get value from environment
    $return_value = \aw2\common\env\get($path, null);
    
    // Output value directly
    \util::var_dump($return_value);
    
    // Return empty as this is an output function
    return '';
}

/*
[func.set.path main=<value> path=<path> /]
*/

function set_path($atts, $content=null, $shortcode=null) {
	extract(\aw2_library::shortcode_atts( array(
	'start'=>null,
	'main' => '#_not_set_#',
	'path' => null
	), $atts, 'aw2_set' ) );
	
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }
	if(!$path) throw new \Exception('Missing required parameter: path');
	if($main==='#_not_set_#') throw new \Exception('Missing required parameter: main');
	
	\aw2\common\env\set($path, $main, $start);
}

/*
[func.set.value main=<path> value=<value> /]
*/

function set_value($atts, $content=null, $shortcode=null) {
	extract(\aw2_library::shortcode_atts( array(
	'start'=>null,
	'value' => '#_not_set_#',
	'main' => null
	), $atts, 'aw2_set' ) );
	
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }

	if(!$main) throw new \Exception('Missing required parameter: path');
	if($value==='#_not_set_#') throw new \Exception('Missing required parameter: main');
	
	\aw2\common\env\set($main, $value, $start);
}


/*
[func.set.paths path.<path>=value path.<path>=value /]
*/

function set_paths($atts, $content=null, $shortcode=null) {
	extract(\aw2_library::shortcode_atts( array(
	'start'=>null
	), $atts, 'aw2_set' ) );
	
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }


	$has_keypaths = false;
	foreach($atts as $keypath => $value) {
		if(strpos($keypath, 'path.') === 0) {
			$has_keypaths = true;
			$actual_path = substr($keypath, 5); // Remove 'path.' prefix
			\aw2\common\env\set($actual_path, $value, $start);
		}
	}
	
	if($has_keypaths) {
		return;
	}
	throw new \Exception('Invalid parameters for set');
}


/*
[func.set.content main=<path>]
value
[/func.set]
*/

function set_content($atts, $content=null, $shortcode=null) {
	extract(\aw2_library::shortcode_atts( array(
	'start'=>null,
	'main' => null
	), $atts, 'aw2_set' ) );
	
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }

	if(!$main) throw new \Exception('Missing required parameter: path');
	
	$reply=\aw2_library::parse_shortcode($content);
	\aw2\common\env\set($main, $reply, $start);
}


/*
[func.set.raw main=<path>]
value
[/func.set]
*/

function set_raw($atts, $content=null, $shortcode=null) {
	extract(\aw2_library::shortcode_atts( array(
	'start'=>null,
	'main' => null
	), $atts, 'aw2_set' ) );
	
    // Validate required parameters
    if ($start === null) {
        throw new \Exception('Missing required parameter: start');
    }

	if(!$main) throw new \Exception('Missing required parameter: path');
	
	\aw2\common\env\set($main, $content, $start);
}


namespace aw2\common\env;


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
        if (!empty($start)) {
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

function get(string $path, $start = null) {
    // Input validation
        if ($path === '' || is_null($path)) {
            throw new \Exception("Path cannot be empty");
        }
        if (strlen($path) > 1024) {
            throw new \Exception("Path exceeds maximum length");
        }

        // Get the environment array
        if (!empty($start)) {
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

            // Handle position-based array access
            if (strpos($part, '@arr:index:') === 0) {
                $index = (int)substr($part, 11);
                if ($index < 0) {
                    return '#_does_not_exist_#';
                }
                if (!is_array($current)) {
                    return '#_does_not_exist_#';
                }
                if ($index > count($current) - 1) {
                    return '#_does_not_exist_#';
                }
                $current = $current[$index];
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

