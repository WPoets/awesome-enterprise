<?php
namespace aw2\int;

\aw2_library::add_service('int','Integer Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('int.is.odd', 'Check if an integer is odd', ['func'=>'is_odd', 'namespace'=>__NAMESPACE__]);
function is_odd($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_odd'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.is.odd: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return $main % 2 !== 0;
}

\aw2_library::add_service('int.is.even', 'Check if an integer is even', ['func'=>'is_even', 'namespace'=>__NAMESPACE__]);
function is_even($atts, $content=null, $shortcode=null) {
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_even'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.is.even: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return $main % 2 === 0;
}


\aw2_library::add_service('int.is.int', 'Check if the value is an integer', ['func'=>'_is_int', 'namespace'=>__NAMESPACE__]);
function _is_int($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_int'));
    return is_int($main);
}

\aw2_library::add_service('int.is.not_int', 'Check if the value is not an integer', ['func'=>'is_not_int', 'namespace'=>__NAMESPACE__]);
function is_not_int($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_int'));
    return !is_int($main);
}



\aw2_library::add_service('int.get','Returns value as an Integer',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>0
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(int)$return_value;	
	
	if($return_value===0)$return_value=(int)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('int.create','Create & Return value as an Integer',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(int)$main;	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


// Absolute value service
\aw2_library::add_service('int.abs', 'Return the absolute value of an integer', ['func'=>'_abs', 'namespace'=>__NAMESPACE__]);
function _abs($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'abs'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.abs: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return abs($main);
}

// Maximum value service
\aw2_library::add_service('int.max', 'Return the maximum of two or more integers', ['func'=>'_max', 'namespace'=>__NAMESPACE__]);
function _max($atts, $content=null, $shortcode=null) {
    $values = array();
    foreach ($atts as $key => $value) {
        if (strpos($key, 'var.') === 0) {
            if (!is_int($value)) {
                throw new \InvalidArgumentException("int.max: All values must be integers. Use int: prefix for typecasting. Invalid value for $key");
            }
            $values[] = $value;
        }
    }
    
    if (empty($values)) {
        throw new \InvalidArgumentException('int.max: At least one integer value must be provided.');
    }
    
    return max($values);
}

// Minimum value service
\aw2_library::add_service('int.min', 'Return the minimum of two or more integers', ['func'=>'_min', 'namespace'=>__NAMESPACE__]);
function _min($atts, $content=null, $shortcode=null) {
    $values = array();
    foreach ($atts as $key => $value) {
        if (strpos($key, 'var.') === 0) {
            if (!is_int($value)) {
                throw new \InvalidArgumentException("int.min: All values must be integers. Use int: prefix for typecasting. Invalid value for $key");
            }
            $values[] = $value;
        }
    }
    
    if (empty($values)) {
        throw new \InvalidArgumentException('int.min: At least one integer value must be provided.');
    }
    
    return min($values);
}

// Check if an integer is positive
\aw2_library::add_service('int.is.positive', 'Check if an integer is positive', ['func'=>'is_positive', 'namespace'=>__NAMESPACE__]);
function is_positive($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_positive'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.is.positive: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return $main > 0;
}

// Check if an integer is negative
\aw2_library::add_service('int.is.negative', 'Check if an integer is negative', ['func'=>'is_negative', 'namespace'=>__NAMESPACE__]);
function is_negative($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_negative'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.is.negative: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return $main < 0;
}

// Check if an integer is zero
\aw2_library::add_service('int.is.zero', 'Check if an integer is zero', ['func'=>'is_zero', 'namespace'=>__NAMESPACE__]);
function is_zero($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_zero'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.is.zero: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return $main === 0;
}


\aw2_library::add_service('int.is.between', 'Check if an integer is within a specified range (inclusive)', ['func'=>'is_between', 'namespace'=>__NAMESPACE__]);
function is_between($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'min' => null,
        'max' => null
    ), $atts, 'is_between'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.is.between: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    if($min === null || !is_int($min)) {
        throw new \InvalidArgumentException('int.is.between: min must be an integer value. Use int: prefix for typecasting.');
    }
    
    if($max === null || !is_int($max)) {
        throw new \InvalidArgumentException('int.is.between: max must be an integer value. Use int: prefix for typecasting.');
    }
    
    if($min > $max) {
        throw new \InvalidArgumentException('int.is.between: min value must be less than or equal to max value.');
    }
    
    return ($main >= $min && $main <= $max);
}


// Generate random integer
\aw2_library::add_service('int.random', 'Generate a random integer within a specified range', ['func'=>'random', 'namespace'=>__NAMESPACE__]);
function random($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'min' => 0,
        'max' => PHP_INT_MAX
    ), $atts, 'random'));
    
    if(!is_int($min)) {
        throw new \InvalidArgumentException('int.random: min must be an integer value. Use int: prefix for typecasting.');
    }
    
    if(!is_int($max)) {
        throw new \InvalidArgumentException('int.random: max must be an integer value. Use int: prefix for typecasting.');
    }
    
    if($min > $max) {
        throw new \InvalidArgumentException('int.random: min value must be less than or equal to max value.');
    }
    
    return rand($min, $max);
}

// Sum of integers
\aw2_library::add_service('int.sum', 'Calculate the sum of multiple integers', ['func'=>'sum', 'namespace'=>__NAMESPACE__]);
function sum($atts, $content=null, $shortcode=null) {
    $values = array();
    foreach ($atts as $key => $value) {
        if (strpos($key, 'var.') === 0) {
            if (!is_int($value)) {
                throw new \InvalidArgumentException("int.sum: All values must be integers. Use int: prefix for typecasting. Invalid value for $key");
            }
            $values[] = $value;
        }
    }
    
    if (empty($values)) {
        throw new \InvalidArgumentException('int.sum: At least one integer value must be provided.');
    }
    
    return array_sum($values);
}


// Convert integer to number (float)
\aw2_library::add_service('int.to.num', 'Convert an integer to a number (float)', ['func'=>'to_num', 'namespace'=>__NAMESPACE__]);
function to_num($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'to_num'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.to.num: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return (float)$main;
}

// Convert integer to string
\aw2_library::add_service('int.to.str', 'Convert an integer to a string', ['func'=>'to_str', 'namespace'=>__NAMESPACE__]);
function to_str($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'to_str'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.to.str: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return (string)$main;
}

// Increment integer by one
\aw2_library::add_service('int.plus_one', 'Increment an integer by one', ['func'=>'plus_one', 'namespace'=>__NAMESPACE__]);
function plus_one($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'plus_one'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.plus_one: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return $main + 1;
}

\aw2_library::add_service('int.minus_one', 'Decrement an integer by one', ['func'=>'minus_one', 'namespace'=>__NAMESPACE__]);
function minus_one($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'minus_one'));
    
    if($main === null || !is_int($main)) {
        throw new \InvalidArgumentException('int.minus_one: main must be an integer value. Use int: prefix for typecasting.');
    }
    
    return $main - 1;
}