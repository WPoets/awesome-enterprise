<?php

namespace aw2\arr_items;

\aw2_library::add_service('arr_items.reverse', 'Reverse the order of array items', ['func'=>'arr_items_reverse', 'namespace'=>__NAMESPACE__]);

function arr_items_reverse($atts, $content=null, $shortcode=null) {

    $array = \aw2\common\build_array($atts, $content);
    if(!is_array($array)) {
        throw new \InvalidArgumentException('main must be an array');
    }
    
    return array_reverse($array, true); // true preserves keys
}

\aw2_library::add_service('arr_items.insert', 'Insert items into array at specified position', ['func'=>'arr_items_insert', 'namespace'=>__NAMESPACE__]);

function arr_items_insert($atts, $content=null, $shortcode=null) {
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('arr_items.insert: position must be specified (beginning|before_pos|before_key|after_pos|after_key|end)');
    }
    
    if(!isset($atts['target'])) {
        throw new \InvalidArgumentException('arr_items.insert target must be provided');
    }
    $target_arr=\aw2_library::get($atts['target']);        
    if(!is_array($target_arr)) {
        throw new \InvalidArgumentException('target must be an array');
    }
    
    $items_to_insert = \aw2\common\build_array($atts, $content);
    
    $position = $shortcode['tags_left'][0];
    
    switch($position) {
        case 'beginning':
            $offset = 0;
            break;
            
        case 'before_pos':
            if(!isset($atts['pos'])) {
                throw new \InvalidArgumentException('arr_items.insert.before_pos: pos attribute must be provided');
            }
            $pos = (int)$atts['pos'];
            if($pos < 1) {
                throw new \InvalidArgumentException('arr_items.insert.before_pos: pos must be greater than 0');
            }
            $offset = $pos - 1;
            break;
            
        case 'before_key':
            if(!isset($atts['key'])) {
                throw new \InvalidArgumentException('arr_items.insert.before_key: key attribute must be provided');
            }
            $offset = get_offset($target_arr, $atts['key']);
            break;
            
        case 'after_pos':
            if(!isset($atts['pos'])) {
                throw new \InvalidArgumentException('arr_items.insert.after_pos: pos attribute must be provided');
            }
            $pos = (int)$atts['pos'];
            if($pos < 1) {
                throw new \InvalidArgumentException('arr_items.insert.after_pos: pos must be greater than 0');
            }
            $offset = $pos;
            break;
            
        case 'after_key':
            if(!isset($atts['key'])) {
                throw new \InvalidArgumentException('arr_items.insert.after_key: key attribute must be provided');
            }
            $offset = get_offset($target_arr, $atts['key']) + 1;
            break; 
            
        case 'end':
            $offset = count($target_arr);
            break;
            
        default:
            throw new \InvalidArgumentException('arr_items.insert: invalid position specified');
    }
    
    // Insert the items at the calculated offset
    array_splice($target_arr, $offset, 0, $items_to_insert);
    return $target_arr;
}



\aw2_library::add_service('arr_items.remove', 'Remove elements from array at specified position', ['func'=>'arr_items_remove', 'namespace'=>__NAMESPACE__]);

function arr_items_remove($atts, $content=null, $shortcode=null) {
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('arr_items.remove: position must be specified (first|last|key|keys|slice)');
    }
    
    $main = \aw2\common\build_array($atts, $content);
    
    $position = $shortcode['tags_left'][0];
    
    switch($position) {
        case 'first':
            $slice = get_slice_details(['from_beginning' => 'yes', 'length' => '1'], $main);
            break;
            
        case 'last':
            $slice = get_slice_details(['from_pos' => (count($main) - 1), 'length' => '1'], $main);
            break;
            
        case 'key':
            if(!isset($atts['key'])) {
                throw new \InvalidArgumentException('arr_items.remove.key: key attribute must be provided');
            }
            $key = $atts['key'];
            unset($main[$key]);
            return $main;
            
        case 'keys':
            if(!isset($atts['keys'])) {
                throw new \InvalidArgumentException('arr_items.remove.keys: keys attribute must be provided');
            }
            $keys = explode(',', $atts['keys']);
            foreach($keys as $key) {
                $key = trim($key);
                if(isset($main[$key])) {
                    unset($main[$key]);
                }
            }
            return $main;
            
        case 'slice':
            $slice = get_slice_details($atts, $main);
            break;
            
        default:
            throw new \InvalidArgumentException('arr_items.remove: invalid position specified');
    }
    
    array_splice($main, $slice['offset'], $slice['length']);
    return $main;
}

\aw2_library::add_service('arr_items.replace', 'Replace elements in array at specified position', ['func'=>'arr_items_replace', 'namespace'=>__NAMESPACE__]);

function arr_items_replace($atts, $content=null, $shortcode=null) {
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('arr_items.replace: position must be specified (first|last|key|slice)');
    }
    
    if(!isset($atts['target'])) {
        throw new \InvalidArgumentException('arr_items.replace: target must be provided');
    }
    
    $target_arr = \aw2_library::get($atts['target']);        
    if(!is_array($target_arr)) {
        throw new \InvalidArgumentException('target must be an array');
    }
    
    $position = $shortcode['tags_left'][0];
    $replacement_items = \aw2\common\build_array($atts, $content);
    
    switch($position) {
        case 'first':
            $slice = get_slice_details(['from_beginning' => 'yes', 'length' => '1'], $target_arr);
            break;
            
        case 'last':
            $slice = get_slice_details(['from_pos' => (count($target_arr) - 1), 'length' => '1'], $target_arr);
            break;
            
        case 'key':
            if(!isset($atts['key'])) {
                throw new \InvalidArgumentException('arr_items.replace.key: key attribute must be provided');
            }
            $slice = get_slice_details(['from_key' => $atts['key'], 'to_key' => $atts['key']], $target_arr);
            break;
            
        case 'slice':
            $slice = get_slice_details($atts, $target_arr);
            break;
            
        default:
            throw new \InvalidArgumentException('arr_items.replace: invalid position specified');
    }
    
    array_splice($target_arr, $slice['offset'], $slice['length'], $replacement_items);
    return $target_arr;
}

\aw2_library::add_service('arr_items.get', 'Get elements from array at specified position', ['func'=>'arr_items_get', 'namespace'=>__NAMESPACE__]);

function arr_items_get($atts, $content=null, $shortcode=null) {
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('arr_items.get: position must be specified (first|last|key|keys|slice)');
    }
    
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'arr_items_get'));
    
    if(!is_array($main)) {
        throw new \InvalidArgumentException('main must be an array');
    }
    
    if(empty($main)) {
        return array();
    }
    
    $position = $shortcode['tags_left'][0];
    
    switch($position) {
        case 'first':
            $slice = get_slice_details(['from_beginning' => 'yes', 'length' => '1'], $main);
            break;
            
        case 'last':
            $slice = get_slice_details(['from_pos' => (count($main) - 1), 'length' => '1'], $main);
            break;
            
        case 'key':
            if(!isset($atts['key'])) {
                throw new \InvalidArgumentException('arr_items.get.key: key attribute must be provided');
            }
            if(!isset($main[$atts['key']])) {
                throw new \InvalidArgumentException("Key '{$atts['key']}' does not exist in array");
            }
            return array($atts['key'] => $main[$atts['key']]);
            
        case 'keys':
            if(!isset($atts['keys'])) {
                throw new \InvalidArgumentException('arr_items.get.keys: keys attribute must be provided');
            }
            $keys = array_map('trim', explode(',', $atts['keys']));
            $result = array();
            foreach($keys as $key) {
                if(isset($main[$key])) {
                    $result[$key] = $main[$key];
                }
            }
            return $result;
            
        case 'slice':
            $slice = get_slice_details($atts, $main);
            break;
            
        default:
            throw new \InvalidArgumentException('arr_items.get: invalid position specified');
    }
    
    return array_slice($main, $slice['offset'], $slice['length'], true);
}


// arr_items.keys - Extract all keys from array
\aw2_library::add_service('arr_items.keys', 'Extract all keys from array', ['namespace'=>__NAMESPACE__]);
function keys($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);    
   return array_keys($main);
}

// arr_items.values - Extract all values from array
\aw2_library::add_service('arr_items.values', 'Extract all values from array', ['namespace'=>__NAMESPACE__]);
function values($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);    
   return array_values($main);
}

// arr_items.find - Find items in array
\aw2_library::add_service('arr_items.find', 'Find items in array', ['func'=>'arr_items_find', 'namespace'=>__NAMESPACE__]);
function arr_items_find($atts, $content=null, $shortcode=null) {
   if(!isset($shortcode['tags_left'][0])) {
       throw new \InvalidArgumentException('arr_items.find: type must be specified (all|first|last)');
   }
   
   $main = \aw2\common\build_array($atts, $content);
   
   if(!isset($atts['value'])) {
       throw new \InvalidArgumentException('arr_items.find requires value attribute');
   }
   
   $search_value = $atts['value'];
   $position = $shortcode['tags_left'][0];
   
   switch($position) {
       case 'all':
           $result = [];
           foreach($main as $key => $value) {
               if($value === $search_value) {
                   $result[$key] = $value;
               }
           }
           return $result;
           
       case 'first':
           foreach($main as $key => $value) {
               if($value === $search_value) {
                   return [$key => $value];
               }
           }
           return [];
           
       case 'last':
           $result = [];
           foreach($main as $key => $value) {
               if($value === $search_value) {
                   $result = [$key => $value];
               }
           }
           return $result;
           
       default:
           throw new \InvalidArgumentException('arr_items.find: invalid type specified (must be all|first|last)');
   }
}

function get_slice_details($atts, $arr) {
    if (!is_array($arr)) {
        throw new InvalidArgumentException("Target must be an array");
    }

    $slice = array();
    
    // Handle offset (start position)
    if (isset($atts['from_beginning'])) {
        if ($atts['from_beginning'] !== 'yes') {
            throw new InvalidArgumentException("from_beginning must be 'yes'");
        }
        $slice['offset'] = 0;
    } elseif (isset($atts['from_key'])) {
        $slice['offset'] = get_offset($arr, $atts['from_key']);
    } elseif (isset($atts['from_pos'])) {
        if (!is_numeric($atts['from_pos'])) {
            throw new InvalidArgumentException("from_pos must be numeric");
        }
        if ($atts['from_pos'] < 1) {
            throw new InvalidArgumentException("from_pos must be greater than 0");
        }
        $slice['offset'] = $atts['from_pos'] - 1;
    } else {
        throw new InvalidArgumentException("Must specify one of: from_beginning=yes, from_key, or from_pos");
    }

    // Handle length
    $array_length = count($arr);
    
    if (isset($atts['to_end'])) {
        if ($atts['to_end'] !== 'yes') {
            throw new InvalidArgumentException("to_end must be 'yes'");
        }
        $slice['length'] = $array_length - $slice['offset'];
    } elseif (isset($atts['to_key'])) {
        $end_offset = get_offset($arr, $atts['to_key']);
        if ($end_offset < $slice['offset']) {
            throw new InvalidArgumentException("to_key results in negative length");
        }
        $slice['length'] = $end_offset - $slice['offset'] + 1;
    } elseif (isset($atts['to_pos'])) {
        if (!is_numeric($atts['to_pos'])) {
            throw new InvalidArgumentException("to_pos must be numeric");
        }
        if ($atts['to_pos'] < 1) {
            throw new InvalidArgumentException("to_pos must be greater than 0");
        }
        if ($atts['to_pos'] - 1 < $slice['offset']) {
            throw new InvalidArgumentException("to_pos results in negative length");
        }
        $slice['length'] = ($atts['to_pos'] - 1) - $slice['offset'] + 1;
    } elseif (isset($atts['length'])) {
        if (!is_numeric($atts['length'])) {
            throw new InvalidArgumentException("length must be numeric");
        }
        if ($atts['length'] < 1) {
            throw new InvalidArgumentException("length must be greater than 0");
        }
        $slice['length'] = $atts['length'];
    } else {
        throw new InvalidArgumentException("Must specify one of: to_end=yes, to_key, to_pos, or length");
    }

    // Validate final slice details
    if ($slice['offset'] >= $array_length) {
        throw new InvalidArgumentException("Offset exceeds array length");
    }
    
    if ($slice['offset'] + $slice['length'] > $array_length) {
        $slice['length'] = $array_length - $slice['offset'];
    }

    return $slice;
}




function get_offset($array, $key_to_find) {
    $keys = array_keys($array);
    $offset = array_search($key_to_find, $keys, true);
    if($offset === false) {
        throw new \InvalidArgumentException("Key '$key_to_find' does not exist in array");
    }
    return $offset;
}


function get_slice($array, $start=0, $length = null) {
    $slice = array_slice($array, $start, $length);
    return $slice;
}












 
