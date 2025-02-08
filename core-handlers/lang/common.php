<?php

namespace aw2\common;

function atts_to_service($atts, $prefix) {
    //php8OK
    $service = array('atts' => array());
    $prefix_len = strlen($prefix) + 1; // +1 for the dot
    
    foreach ($atts as $key => $value) {
        if (strpos($key, $prefix . '.') === 0) {
            $actual_key = substr($key, $prefix_len);
            if ($actual_key === '@')
                $service['name'] = $value;
            else
                $service['atts'][$actual_key] = $value;
        }
    }
    return $service;
}

function updateInfo(array &$info,$index): array {

    $info['index']=$index;
    $info['position']=$index+1;
    
    $info['first']=false;
    $info['last']=false;
    $info['between']=false;
    $info['odd']=false;
    $info['even']=false;
    
    if ($index % 2 != 0)
        $info['odd']= true;
    else
        $info['even']= true;
    if($index==1)$info['first']=true;

    if(isset($info['count'])) {
        if($index==$info['count'])$info['last']=true;
        if($index!=$info['count'])$info['between']=true;
    }



// Return the updated array
return $info;
}

function handle_range($atts) {
    // Validate required attributes
    if(!isset($atts['start']) || !isset($atts['stop'])) {
        throw new Exception("Both start and stop are required for range");
    }

    $start = $atts['start'];
    $stop = $atts['stop'];
    $step = isset($atts['step']) ? $atts['step'] : 1;

    // Validate numeric types
    if(!is_numeric($start) || !is_numeric($stop) || !is_numeric($step)) {
        throw new Exception("start, stop and step must be numeric");
    }

    // Validate step is not zero
    if($step == 0) {
        throw new Exception("step cannot be zero");
    }

    // Validate range direction based on step
    if($step > 0 && $start > $stop) {
        throw new Exception("When step is positive, start must be less than or equal to stop");
    }
    if($step < 0 && $start < $stop) {
        throw new Exception("When step is negative, start must be greater than or equal to stop");
    }

    // Generate range array
    $result = array();
    if($step > 0) {
        for($i = $start; $i <= $stop; $i += $step) {
            $result[] = $i;
        }
    } else {
        for($i = $start; $i >= $stop; $i += $step) {
            $result[] = $i;
        }
    }

    return $result;
}

function build_array($atts, $content){
    //range(string|int|float $start, string|int|float $end, int|float $step = 1): array
    // Check for range parameters
    if(isset($atts['range.start'])) {
        // Map range.* parameters to handle_range format
        $range_atts = array(
            'start' => $atts['range.start'],
            'stop' => isset($atts['range.stop']) ? $atts['range.stop'] : null,
            'step' => isset($atts['range.step']) ? $atts['range.step'] : null
        );
        return handle_range($range_atts);
    }

    // Check for arr.@ service
    if(isset($atts['arr.@'])) {
        $service = array('name'=>null, 'atts'=>array());
        foreach ($atts as $key => $value) {
            if ($key === 'arr.@') {
                $service['name'] = $value;
            }
            elseif (strpos($key, 'arr.') === 0) {
                $service['atts'][substr($key, 4)] = $value;
            }
        }
        $items = \aw2_library::service_run($service['name'], $service['atts'], null);
        if(!is_array($items)) {
            throw new \InvalidArgumentException('Service must return an array');
        }
        return $items;
    }
    
    // If main is provided, use it as items
    if(isset($atts['main'])) {
        $items = $atts['main'];

		// Check items is array
		if(!is_array($items)) {
			throw new \InvalidArgumentException('main must be an array');
		}
        return $items;

    }
    
    
    // If content is provided, parse it
    if($content) {
        $ab = new \array_builder();
        $items = $ab->parse($content);
        return $items;
    }
    
    // Add key-value pairs
    foreach($atts as $key => $value) {
        $flag=false;
        if(strpos($key, 'key.') === 0) {
            $flag=true;
            $real_key = substr($key, 4);
            $items[$real_key] = $value;
        }
        if($flag===true) return $items;
    }
    
    // Merge from_path items if provided
    if(isset($atts['from_path'])) {
        $path_items = \aw2_library::get($atts['from_path']);
        if(!is_array($path_items)) {
            throw new \InvalidArgumentException('from_path must resolve to an array');
        }
		
        return $items;
    }
    
	if(!is_array($items)) {
		throw new \InvalidArgumentException('Array not found');
	}
}


function get_value($atts, $content){
    
    if(isset($atts['main'])) {
        $value = $atts['main'];
        return $value;
    }
    
    // If content is provided, parse it
    if($content) {
        $value = \aw2_library::parse_shortcode($content);
        return $value;
    }
    
    foreach($atts as $key => $value) {
        if(strpos($key, 'key.') === 0) {
            $real_key = substr($key, 4);
            $items[$real_key] = $value;
        }
        return $items;
    }
    
    // Merge from_path items if provided
    if(isset($atts['from_path'])) {
        $path_items = \aw2_library::get($atts['from_path']);
        return $items;
    }
    return '#_error_#';    
}


function cond_check($atts) {
    // Check if there's a condition to evaluate
    if (!isset($atts['cond.@'])) {
        return true;
    }

    $cond = array();

    // Check for 'and.@' key
    if (isset($atts['cond.and.@'])) {
        $cond['and']['@'] = $atts['cond.and.@'];
        unset($atts['cond.and.@']);
    }

    // Check for other 'and.' keys
    foreach ($atts as $key => $value) {
        if (strpos($key, 'cond.and.') === 0) {
            $cond['and']['atts'][substr($key, 9)] = $value;
            unset($atts[$key]);
        }
    }

    // Check for 'or.@' key
    if (isset($atts['cond.or.@'])) {
        $cond['or']['@'] = $atts['cond.or.@'];
        unset($atts['cond.or.@']);
    }

    // Check for other 'or.' keys
    foreach ($atts as $key => $value) {
        if (strpos($key, 'cond.or.') === 0) {
            $cond['or']['atts'][substr($key, 8)] = $value;
            unset($atts[$key]);
        }
    }

    // Check for '@' key
    if (isset($atts['cond.@'])) {
        $cond['when']['@'] = $atts['cond.@'];
        unset($atts['cond.@']);
    }

    // All remaining attributes go into 'when.atts'
    foreach ($atts as $key => $value) {
        if (strpos($key, 'cond.') === 0) {
            $cond['when']['atts'][substr($key, 5)] = $value;
            unset($atts[$key]);
        }
    }


  
    // Evaluate the main condition
    $main_result = evaluate_condition($cond['when']);


    // If there's no 'and' or 'or' condition, return the main result
    if (!isset($cond['and']['@']) && !isset($cond['or']['@'])) {
        return $main_result;
    }
 
    // Evaluate 'and' condition if present
    $and_result = isset($cond['and']['@']) ? evaluate_condition($cond['and']) : true;

    // Evaluate 'or' condition if present
    $or_result = isset($cond['or']['@']) ? evaluate_condition($cond['or']) : false;

    // Combine results: (main AND and) OR or
    return ($main_result && $and_result) || $or_result;
 }

function evaluate_condition($cond) {
    // Validate service name exists

    if (!isset($cond['@'])) {
        throw new \UnexpectedValueException("Service for condition not found");
    }

    if (!isset($cond['atts'])) {
        $cond['atts']=array();
    }

    // Run the service
    $result = \aw2_library::service_run($cond['@'], $cond['atts'], null);

    // Strictly check for boolean type
    if (!is_bool($result)) {
        throw new \UnexpectedValueException("Service '{$cond['@']}' must return a boolean value.");
    }

    return $result;
}