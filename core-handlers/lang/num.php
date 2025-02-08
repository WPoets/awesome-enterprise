<?php
namespace aw2\num;

\aw2_library::add_service('num','Numeric Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('num.is.num', 'Check if the value is a number', ['func'=>'is_num', 'namespace'=>__NAMESPACE__]);
function is_num($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_num'));
    return is_numeric($main);
}

\aw2_library::add_service('num.is.not_num', 'Check if the value is not a number', ['func'=>'is_not_num', 'namespace'=>__NAMESPACE__]);
function is_not_num($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_num'));
    return !is_numeric($main);
}


\aw2_library::add_service('num.get','Returns value as a Float',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>0.00
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(float)$return_value;	
	
	if($return_value===0.00)$return_value=(float)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('num.create','Create & return value as a Float',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(float)$main;	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('num.is.positive', 'Check if a number is positive', ['func'=>'is_positive', 'namespace'=>__NAMESPACE__]);
function is_positive($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_positive'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.is.positive: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return $main > 0;
}

\aw2_library::add_service('num.is.negative', 'Check if a number is negative', ['func'=>'is_negative', 'namespace'=>__NAMESPACE__]);
function is_negative($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_negative'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.is.negative: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return $main < 0;
}

\aw2_library::add_service('num.is.zero', 'Check if a number is zero', ['func'=>'is_zero', 'namespace'=>__NAMESPACE__]);
function is_zero($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_zero'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.is.zero: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return $main == 0;
}


\aw2_library::add_service('num.is.between', 'Check if a number is within a specified range (inclusive)', ['func'=>'is_between', 'namespace'=>__NAMESPACE__]);
function is_between($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'min' => null,
        'max' => null
    ), $atts, 'is_between'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.is.between: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    if($min === null || (!is_numeric($min) && !is_int($min))) {
        throw new \InvalidArgumentException('num.is.between: min must be a numeric or integer value. Use num: or int: prefix for typecasting.');
    }
    
    if($max === null || (!is_numeric($max) && !is_int($max))) {
        throw new \InvalidArgumentException('num.is.between: max must be a numeric or integer value. Use num: or int: prefix for typecasting.');
    }
    
    // Convert min and max to float to ensure correct comparison
    $min = (float)$min;
    $max = (float)$max;
    
    if($min > $max) {
        throw new \InvalidArgumentException('num.is.between: min value must be less than or equal to max value.');
    }
    
    return ($main >= $min && $main <= $max);
}

\aw2_library::add_service('num.ceiling', 'Round up to the nearest integer', ['func'=>'_ceiling', 'namespace'=>__NAMESPACE__]);
function _ceiling($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'ceiling'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.ceiling: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return ceil($main);
}

\aw2_library::add_service('num.floor', 'Round down to the nearest integer', ['func'=>'_floor', 'namespace'=>__NAMESPACE__]);
function _floor($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'floor'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.floor: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return floor($main);
}

\aw2_library::add_service('num.round', 'Round a number to a specified precision', ['func'=>'_round', 'namespace'=>__NAMESPACE__]);
function _round($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'precision' => 0
    ), $atts, 'round'));

    if(!is_numeric($main)) {
        throw new \InvalidArgumentException('num.round: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    if(!is_int($precision)) {
        throw new \InvalidArgumentException('num.round: precision must be a integer value.');
    }
    
    return round($main, $precision);
}

\aw2_library::add_service('num.truncate', 'Truncate a number to a specified number of decimal places', ['func'=>'truncate', 'namespace'=>__NAMESPACE__]);
function truncate($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'decimals' => 0
    ), $atts, 'truncate'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.truncate: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    if(!is_numeric($decimals)) {
        throw new \InvalidArgumentException('num.truncate: decimals must be a numeric value.');
    }
    
    $pow = pow(10, $decimals);
    return floor($main * $pow) / $pow;
}

\aw2_library::add_service('num.abs', 'Get the absolute value of a number', ['func'=>'_abs', 'namespace'=>__NAMESPACE__]);
function _abs($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'abs'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.abs: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return abs($main);
}

\aw2_library::add_service('num.min', 'Return the minimum of two or more numbers', ['func'=>'_min', 'namespace'=>__NAMESPACE__]);
function _min($atts, $content=null, $shortcode=null) {
    $values = array();
    foreach ($atts as $key => $value) {
        if (strpos($key, 'var.') === 0) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException("num.min: All values must be numeric. Use num: prefix for typecasting. Invalid value for $key");
            }
            $values[] = $value;
        }
    }
    
    if (empty($values)) {
        throw new \InvalidArgumentException('num.min: At least one numeric value must be provided.');
    }
    
    return min($values);
}

\aw2_library::add_service('num.max', 'Return the maximum of two or more numbers', ['func'=>'_max', 'namespace'=>__NAMESPACE__]);
function _max($atts, $content=null, $shortcode=null) {
    $values = array();
    foreach ($atts as $key => $value) {
        if (strpos($key, 'var.') === 0) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException("num.max: All values must be numeric. Use num: prefix for typecasting. Invalid value for $key");
            }
            $values[] = $value;
        }
    }
    
    if (empty($values)) {
        throw new \InvalidArgumentException('num.max: At least one numeric value must be provided.');
    }
    
    return max($values);
}

\aw2_library::add_service('num.to.str', 'Convert a number to a string', ['func'=>'to_str', 'namespace'=>__NAMESPACE__]);
function to_str($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'to_str'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.to.str: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return (string)$main;
}

\aw2_library::add_service('num.rand', 'Generate a random number within a specified range', ['func'=>'_rand', 'namespace'=>__NAMESPACE__]);
function _rand($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'min' => 0,
        'max' => 1
    ), $atts, 'rand'));
    
    if(!is_numeric($min) || !is_numeric($max)) {
        throw new \InvalidArgumentException('num.rand: min and max must be numeric values. Use num: prefix for typecasting.');
    }
    
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

\aw2_library::add_service('num.sum', 'Calculate the sum of multiple numbers', ['func'=>'_sum', 'namespace'=>__NAMESPACE__]);
function _sum($atts, $content=null, $shortcode=null) {
    $values = array();
    foreach ($atts as $key => $value) {
        if (strpos($key, 'var.') === 0) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException("num.sum: All values must be numeric. Use num: prefix for typecasting. Invalid value for $key");
            }
            $values[] = $value;
        }
    }
    
    if (empty($values)) {
        throw new \InvalidArgumentException('num.sum: At least one numeric value must be provided.');
    }
    
    return array_sum($values);
}

\aw2_library::add_service('num.to.int', 'Convert a number to an integer', ['func'=>'to_int', 'namespace'=>__NAMESPACE__]);
function to_int($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'to_int'));
    
    if($main === null || !is_numeric($main)) {
        throw new \InvalidArgumentException('num.to.int: main must be a numeric value. Use num: prefix for typecasting.');
    }
    
    return (int)$main;
}

