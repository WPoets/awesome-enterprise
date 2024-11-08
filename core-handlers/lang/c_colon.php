<?php
namespace aw2\c_colon;


function c_check($c = array()) {
    if (!isset($c['@']))return true;
    $atts=c_build($c);

    if (isset($atts['and']['@']) and  isset($atts['or']['@']) )
        {
            throw new \InvalidArgumentException("Error: and or both cannot be there in c:");
        }	

    if (isset($atts['c']['@'])) {
        $c_reply = \aw2_library::service_run($atts['c']['@'], $atts['c']['atts'], null, 'service');
        
        if (!is_bool($c_reply)) {
            throw new \InvalidArgumentException("Error: 'c' condition must return a boolean value.");
        }
        
        if ($c_reply === true && !isset($atts['and']['@'])) {
            return true;
        }
        
        if ($c_reply === false && isset($atts['and']['@'])) {
            return false;
        }
    }
    
    if (isset($atts['and']['@'])) {
        $and_reply = \aw2_library::service_run($atts['and']['@'], $atts['and']['atts'], null, 'service');
        
        if (!is_bool($and_reply)) {
            throw new \InvalidArgumentException("Error: 'and' condition must return a boolean value.");
        }
        
        if ($and_reply === true) {
            return true;
        }
    }
    
    if (isset($atts['or']['@'])) {
        $or_reply = \aw2_library::service_run($atts['or']['@'], $atts['or']['atts'], null, 'service');
        
        if (!is_bool($or_reply)) {
            throw new \InvalidArgumentException("Error: 'or' condition must return a boolean value.");
        }
        
        if ($or_reply === true) {
            return true;
        }
    }
    
    return false;
}

function c_build($atts = array()) {
    $cond = array();

    // Check for 'and.@' key
    if (isset($atts['and.@'])) {
        $cond['and']['@'] = $atts['and.@'];
        unset($atts['and.@']);
    }

    // Check for other 'and.' keys
    foreach ($atts as $key => $value) {
        if (strpos($key, 'and.') === 0) {
            $cond['and']['atts'][substr($key, 4)] = $value;
            unset($atts[$key]);
        }
    }

    // Check for 'or.@' key
    if (isset($atts['or.@'])) {
        $cond['or']['@'] = $atts['or.@'];
        unset($atts['or.@']);
    }

    // Check for other 'or.' keys
    foreach ($atts as $key => $value) {
        if (strpos($key, 'or.') === 0) {
            $cond['or']['atts'][substr($key, 3)] = $value;
            unset($atts[$key]);
        }
    }

    // Check for '@' key
    if (isset($atts['@'])) {
        $cond['c']['@'] = $atts['@'];
        unset($atts['@']);
    }

    // All remaining attributes go into 'c.atts'
    if (!empty($atts)) {
        $cond['c']['atts'] = $atts;
    }

    return $cond;
}