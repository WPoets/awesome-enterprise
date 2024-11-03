<?php

namespace aw2\obj;

\aw2_library::add_service('obj','Object Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('obj.is.obj', 'Check if the value is an object', ['func'=>'is_obj', 'namespace'=>__NAMESPACE__]);
function is_obj($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_obj'));
    return is_object($main);
}

\aw2_library::add_service('obj.is.not_obj', 'Check if the value is not an object', ['func'=>'is_not_obj', 'namespace'=>__NAMESPACE__]);
function is_not_obj($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_not_obj'));
    return !is_object($main);
}


\aw2_library::add_service('obj.set','Set a value in an array',['namespace'=>__NAMESPACE__]);
function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	unset($atts['main']);
	
	$obj = new \stdClass();

	foreach ($atts as $loopkey => $loopvalue) {
		
		$obj->$loopkey=$loopvalue;
	}	
	\aw2_library::set($main,$obj);
	return;
}

\aw2_library::add_service('obj.create','Build an Object',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'path'=>null,
	'raw_content'=>null
	), $atts) );
	
	if(!is_null($path))$content=\aw2_library::get($path);

	if(!is_null($raw_content))$content=$raw_content;
	
	$ab=new \array_builder();
	$return_value=$ab->parse($content);
	$return_value = (object) $return_value;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('obj.empty','Empty array',['func'=>'_empty','namespace'=>__NAMESPACE__]);
function _empty($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	$return_value=new \stdClass();
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('arr.search_deep','Allows you to search for a value in an array of arrays or an array of objects, and return the key of the value that matches the search criteria.',['namespace'=>__NAMESPACE__]);
function search_deep($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'search'=>null,
	'field'=>FALSE
	), $atts, 'aw2_get' ) );
	
	if(is_null($main) || is_null($search)) return ;
	
	$arr_to_search = \aw2_library::get($main);
	
	$return_value=null;
	searchObject($arr_to_search, $search,$return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}






function searchObject($object, $searchKey, &$result = null) {
    // Traverse through the object
    foreach ($object as $key => $value) {
        // If key matches, save the result and break out of the loop
        if ($key === $searchKey) {
            $result = $value;
            return true;
        }
        
        // If the value is an object, call the function recursively
        if (is_object($value)) {
            if (searchObject($value, $searchKey, $result)) {
                return true; // Return if the key was found in the nested object
            }
        }

        // If the value is an array, traverse the array
        if (is_array($value)) {
            foreach ($value as $subValue) {
                if (is_object($subValue) && searchObject($subValue, $searchKey, $result)) {
                    return true;
                }
            }
        }
    }
    
    return false; // Return false if the key is not found
}

