<?php

/*
	if(array_key_exists('modify_output',$atts)){
			$arr=self::get($atts['modify_output']);
			$value=self::modify_output($value,$arr);
		}

		//last
		if(array_key_exists('last',$atts)){
			if(is_array($value)){
				$value=end($value);
				reset($arr);
			}
		}
*/

namespace aw2\m;

//run
\aw2_library::add_service('m.run','Run the code. Use m.run',['namespace'=>__NAMESPACE__]);
function run($value, $atts){
	$value = \aw2_library::parse_shortcode($value);
	return $value;
}

//the_content
\aw2_library::add_service('m.the_content','Apply the_content filter . Use m.the_content',['namespace'=>__NAMESPACE__]);
function the_content($value, $atts){
	$value = \aw2_library::the_content_filter($value);
	$value = do_shortcode($value);
	return $value;
}

//do_shortcode
\aw2_library::add_service('m.do_shortcode','Apply do_shortcode filter . Use m.do_shortcode',['func'=>'_do_shortcode','namespace'=>__NAMESPACE__]);
function _do_shortcode($value, $atts){
	$value= wpautop($value);
	$value= shortcode_unautop($value);
	$value = do_shortcode($value);
	return $value;
}

//strtolower
\aw2_library::add_service('m.strtolower','Return the value as lowercase. Use m.strtolower',['func'=>'lower','namespace'=>__NAMESPACE__]);
\aw2_library::add_service('m.lower','Return the value as lowercase. Use m.lower',['namespace'=>__NAMESPACE__]);
function lower($value, $atts){
	$value = strtolower($value);
	return $value;
}

//strtoupper
\aw2_library::add_service('m.strtoupper','Return the value as lowercase. Use m.strtoupper',['func'=>'upper','namespace'=>__NAMESPACE__]);
\aw2_library::add_service('m.upper','Return the value as lowercase. Use m.upper',['namespace'=>__NAMESPACE__]);
function upper($value, $atts){
	$value = strtoupper($value);
	return $value;
}

//trim
\aw2_library::add_service('m.trim','Trim the value of any whitespaces and return. Use m.trim',['func'=>'_trim','namespace'=>__NAMESPACE__]);
function _trim($value, $atts){
	$value = trim($value);
	return $value;
}

//length
\aw2_library::add_service('m.strlen','Return the length of the value. Use m.strlen',['func'=>'_strlen','namespace'=>__NAMESPACE__]);
function _strlen($value, $atts){
	$value = strlen($value);
	return $value;
}

//10 digit number
\aw2_library::add_service('m.ten_digit','Slice the value to 10 digit and return. Use m.ten_digit',['namespace'=>__NAMESPACE__]);
function ten_digit($value, $atts){
	$value = str_replace(' ','',$value);
	if(strlen($value)>10)
		$value =substr ( $value , -10 ,10);
	return $value;
}

//json_decode
\aw2_library::add_service('m.json_decode','Decode the JSON value and return. Use m.json_decode',['func'=>'_json_decode','namespace'=>__NAMESPACE__]);
function _json_decode($value, $atts){
	$value = json_decode($value,true);
	return $value;
}

//json_encode
\aw2_library::add_service('m.json_encode','Encode the value into JSON and return. Use m.json_encode',['func'=>'_json_encode','namespace'=>__NAMESPACE__]);
function _json_encode($value, $atts){
	if(is_array($value))
		$value = json_encode($value,true);
	return $value;
}

//dump
\aw2_library::add_service('m.dump','var_dump the value. Use m.dump',['namespace'=>__NAMESPACE__]);
function dump($value, $atts){
	$value =\util::var_dump($value,true);
	return $value;
}

//stripslashes_deep
\aw2_library::add_service('m.stripslashes_deep','Use m.stripslashes_deep',['func'=>'_stripslashes_deep','namespace'=>__NAMESPACE__]);
function _stripslashes_deep($value, $atts){
	$value = stripslashes_deep($value);
	return $value;
}

//encrypt
\aw2_library::add_service('m.encrypt','Encrypt the value and return. Use m.encrypt',['namespace'=>__NAMESPACE__]);
function encrypt($value, $atts){
	$value = \aw2_library::simple_encrypt($value);
	return $value;
}

//decrypt
\aw2_library::add_service('m.decrypt','Decrypt the value and return. Use m.decrypt',['namespace'=>__NAMESPACE__]);
function decrypt($value, $atts){
	$value = \aw2_library::simple_decrypt($value);
	return $value;
}

//explode
\aw2_library::add_service('m.explode','Explode the value and return. Use m.explode="<delimiter>"',['func'=>'_explode','namespace'=>__NAMESPACE__]);
function _explode($value, $atts){
	$value = explode($atts['explode'],$value);
	return $value;
}

//format number
\aw2_library::add_service('m.comma_separator','Format the value as number and return. Use m.comma_separator',['namespace'=>__NAMESPACE__]);
function comma_separator($value, $atts){
	$value = number_format($value,0, '.', ',');
	return $value;
}

//format date
\aw2_library::add_service('m.date_format','Format the value as Date and return. Use m.date_format="<format>"',['func'=>'_date_format','namespace'=>__NAMESPACE__]);
function _date_format($value, $atts){
	$format = $atts['date_format'];
	if($format==''){
		$format = 'M d, Y';
	}
	
	if(is_object($value) && get_class($value)==='DateTime'){
		$value = date_format($value,$format);
	}
	else{
		try {
			$new_date = new \DateTime($value);
		} 
		catch (Exception $e) {
			$new_date = false;
		} 
		if($new_date===false)
			$value='';
		else
			$value = date_format(new \DateTime($value),$format);
	}
	return $value;
}

//words
\aw2_library::add_service('m.words','Break the value as words and return. Use m.words',['namespace'=>__NAMESPACE__]);
function words($value, $atts){
	$value = \aw2_library::break_words($value, $atts['words']);
	return $value;
}

//separator
\aw2_library::add_service('m.separator','Explode the value using the seperator, if value is array, else implode, and return. Use m.separator',['namespace'=>__NAMESPACE__]);
function separator($value, $atts){
	if(is_array($value))
		$value=implode ( $atts['separator'] , $value );
	else
		$value=explode ($atts['separator'] , $value );
	return $value;
}

//comma
\aw2_library::add_service('m.comma','Implode the value using comma, if value is array, else explode, and return. Use m.comma',['namespace'=>__NAMESPACE__]);
function comma($value, $atts){
	if(is_array($value)) 
		$value=implode ( ',' , $value );
	else
		$value=explode ( ',' , $value );
	return $value;
}

//space
\aw2_library::add_service('m.space','Implode the value using space, if value is array, else explode, and return. Use m.space',['namespace'=>__NAMESPACE__]);
function space($value, $atts){
	if(is_array($value)) 
		$value=implode ( ' ' , $value );
	else
		$value=explode ( ' ' , $value );
	return $value;
}

//url_encode
\aw2_library::add_service('m.url_encode','Encode the URL and return. Use m.url_encode',['namespace'=>__NAMESPACE__]);
function url_encode($value, $atts){
	$value = urlencode($value);
	return $value;
}

//url_decode
\aw2_library::add_service('m.url_decode','Decode the URL and return. Use m.url_decode',['namespace'=>__NAMESPACE__]);
function url_decode($value, $atts){
	$value = urldecode($value);
	return $value;
}

//count
\aw2_library::add_service('m.count','Return the count of the value, if value is an array. Use m.count',['func'=>'_count','namespace'=>__NAMESPACE__]);
function _count($value, $atts){
	if(is_array($value)){
		$value=count($value);
	}
	return $value;
}

//first
\aw2_library::add_service('m.first','If the value is an array, set the value as first element. Use m.first',['namespace'=>__NAMESPACE__]);
function first($value, $atts){
	if(is_array($value)){
		reset($value);
		$value= current($value);
	}
	return $value;
}

//last
\aw2_library::add_service('m.last','If the value is an array, set the value as last element. Use m.last',['namespace'=>__NAMESPACE__]);
function last($value, $atts){
	if(is_array($value)){
		$value=end($value);
		reset($value);
	}
	return $value;
}

//shuffle
\aw2_library::add_service('m.shuffle','If the value is an array, shuffle the elements and return. Use m.shuffle',['namespace'=>__NAMESPACE__]);
function shuffle($value, $atts){
	if(is_array($value)){
		 // Initialize
		$shuffled_array = array();
		// Get array's keys and shuffle them.
		$shuffled_keys = array_keys($value);
		shuffle($shuffled_keys);
		// Create same array, but in shuffled order.
		foreach ( $shuffled_keys AS $shuffled_key ) {
			$shuffled_array[  $shuffled_key  ] = $value[  $shuffled_key  ];
		} // foreach
		$value = $shuffled_array;
	}
	return $value;
}

//entities_encode
\aw2_library::add_service('m.entities_encode','Encode the HTML Entities in the value and return. Use m.entities_encode',['namespace'=>__NAMESPACE__]);
function entities_encode($value, $atts){
	$value = htmlentities($value, ENT_QUOTES, "UTF-8",false);
	return $value;
}

//entities_decode
\aw2_library::add_service('m.entities_decode','Decode the HTML Entities in the value and return. Use m.entities_decode',['namespace'=>__NAMESPACE__]);
function entities_decode($value, $atts){
	$value = html_entity_decode($value, ENT_QUOTES);
	return $value;
}