<?php

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

//lower
\aw2_library::add_service('m.lower','Return the value as lowercase. Use m.lower',['namespace'=>__NAMESPACE__]);
function lower($value, $atts){
	$value = strtolower((string)$value);
	return $value;
}

//upper
\aw2_library::add_service('m.upper','Return the value as uppercase. Use m.upper',['namespace'=>__NAMESPACE__]);
function upper($value, $atts){
	$value = strtoupper((string)$value);
	return $value;
}

//capitalize
\aw2_library::add_service('m.capitalize','Return the value as capitalized. Use m.capitalize',['namespace'=>__NAMESPACE__]);
function capitalize($value, $atts){
	$value = ucfirst((string)$value);
	return $value;
}

//sentenceCase
\aw2_library::add_service('m.sentence','Return the value as sentenceCase. Use m.sentence',['namespace'=>__NAMESPACE__]);
function sentence($value, $atts){
	$value = \aw2_library::sentenceCase((string)$value);
	return $value;
}

//trim
\aw2_library::add_service('m.trim','Trim the value of any whitespaces and return. Use m.trim',['func'=>'_trim','namespace'=>__NAMESPACE__]);
function _trim($value, $atts){
	$value = trim((string)$value);
	return $value;
}

//ltrim
\aw2_library::add_service('m.ltrim','Trim the value of any whitespaces from left and return. Use m.ltrim',['func'=>'_ltrim','namespace'=>__NAMESPACE__]);
function _ltrim($value, $atts){
	$value = ltrim((string)$value);
	return $value;
}

//rtrim
\aw2_library::add_service('m.rtrim','Trim the value of any whitespaces from right and return. Use m.rtrim',['func'=>'_rtrim','namespace'=>__NAMESPACE__]);
function _rtrim($value, $atts){
	$value = rtrim((string)$value);
	return $value;
}

//left
\aw2_library::add_service('m.left','Truncate the characters from left of the value and return. Use m.left=n:<char length>',['namespace'=>__NAMESPACE__]);
function left($value, $atts){
	$length = substr( $atts['left'], 2, strlen($atts['left']) );
	$value = substr((string)$value ,0, $length);
	return $value;
}

//right
\aw2_library::add_service('m.right','Truncate the characters from right of the value and return. Use m.right=n:<char length>',['namespace'=>__NAMESPACE__]);
function right($value, $atts){
	$length = substr( $atts['right'], 2, strlen($atts['right']) );
	$value = substr((string)$value , -$length);
	return $value;
}

//substr
\aw2_library::add_service('m.substr','Truncate the value and return. Use m.substr=yes m.start=n:<char length> m.chars=n:<char length>',['func'=>'_substr','namespace'=>__NAMESPACE__]);
function _substr($value, $atts){
	$start = substr( $atts['start'], 2, strlen($atts['start']) );
	$chars = substr( $atts['chars'], 2, strlen($atts['chars']) );
	$value = substr((string)$value ,$start, $chars);
	return $value;
}

//length
\aw2_library::add_service('m.length','Return the length of the value. Use m.length',['namespace'=>__NAMESPACE__]);
function length($value, $atts){
	if(is_array($value))
		$value=count($value);
	else
		$value = strlen((string)$value);
	
	return $value;
}

//json_encode
\aw2_library::add_service('m.json_encode','Encode the value into JSON and return. Use m.json_encode',['func'=>'_json_encode','namespace'=>__NAMESPACE__]);
function _json_encode($value, $atts){
	if(is_array($value))
		$value = json_encode($value,true);
	return $value;
}

//json_decode
\aw2_library::add_service('m.json_decode','Decode the JSON value and return. Use m.json_decode',['func'=>'_json_decode','namespace'=>__NAMESPACE__]);
function _json_decode($value, $atts){
	$value = json_decode((string)$value,true);
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

//strip_tags
\aw2_library::add_service('m.strip_tags','Use m.strip_tags',['func'=>'_strip_tags','namespace'=>__NAMESPACE__]);
function _strip_tags($value, $atts){
	$value = strip_tags($value);
	return $value;
}

//encrypt
\aw2_library::add_service('m.encrypt','Encrypt the value and return. Use m.encrypt',['namespace'=>__NAMESPACE__]);
function encrypt($value, $atts){
	$value = \aw2_library::simple_encrypt((string)$value);
	return $value;
}

//decrypt
\aw2_library::add_service('m.decrypt','Decrypt the value and return. Use m.decrypt',['namespace'=>__NAMESPACE__]);
function decrypt($value, $atts){
	$value = \aw2_library::simple_decrypt((string)$value);
	return $value;
}

//explode_on
\aw2_library::add_service('m.explode_on','Explode the value and return. Use m.explode_on="s:<delimiter>"',['namespace'=>__NAMESPACE__]);
function explode_on($value, $atts){
	if($atts['explode_on'] === 'yes' ){
		$value = explode(',',(string)$value);
		return $value;
	}

	$delimiter = substr( $atts['explode_on'], 2, strlen($atts['explode_on']) );
	
	switch($delimiter){
		case 'comma':
			$delimiter = ",";
		break;
		case 'space':
			$delimiter = " ";
		break;
		case 'dot':
			$delimiter = ".";
		break;
	}
	
	$value = explode($delimiter,(string)$value);
	return $value;
	
}

//implode_on
\aw2_library::add_service('m.implode_on','Explode the value and return. Use m.implode_on="<glue>"',['namespace'=>__NAMESPACE__]);
function implode_on($value, $atts){
	
	if (!is_array($value)) return array();
	
	if($atts['implode_on'] === 'yes' ){
		$value = implode(',',$value);
		return $value;
	}

	$glue = substr( $atts['implode_on'], 2, strlen($atts['implode_on']) );
	
	switch($glue){
		case 'comma':
			$value = implode(',',$value);
			break;
		case 'space':
			$value = implode(' ',$value);
			break;
		case 'quote_comma':			
			if(count($value)<1)
					$value='';
				
			if(count($value)>0)
				$value="'" . implode ( "','" , $value ) . "'";				
			break;
		case 'dot':
			$value = implode('.',$value);
			break;
		default:
			$value = implode($glue,$value);
		break;
	}
	
	return $value;
	
}

//format number
\aw2_library::add_service('m.number_format','Format the value as number and return. Use m.number_format=yes m.decimals=n:<integer>',['func'=>'_number_format','namespace'=>__NAMESPACE__]);
function _number_format($value, $atts){
	$decimals = substr( $atts['decimals'], 2, strlen($atts['decimals']) );
	$value = number_format($value,$decimals, '.', ',');
	return $value;
}

//money_format
\aw2_library::add_service('m.money_format','Format the value as Money and return. Use m.money_format="<format>"',['func'=>'_money_format','namespace'=>__NAMESPACE__]);
function _money_format($value, $atts){
	$format = $atts['money_format'];
	if(empty($format)){
		$format = 'en_IN';
	}
	if($format=="yes"){
		$format = 'en_IN';
	}
	setlocale(LC_MONETARY, $format);
    $value = money_format('%!i', (float)$value);
	$value =str_replace('.00','',$value);
	return $value;
}

//format date
\aw2_library::add_service('m.date_format','Format the value as Date and return. Use m.date_format="<format>"',['func'=>'_date_format','namespace'=>__NAMESPACE__]);
function _date_format($value, $atts){
	$format = $atts['date_format'];
	if($format==='yes'){
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


//format date
\aw2_library::add_service('m.hhmm_format','Format the value as HH:MM:SS and return. Use m.date_format="<format>"',['func'=>'_date_format','namespace'=>__NAMESPACE__]);
function hhmm_format($value, $atts){

	do{
        $c[] = str_pad(isset($c) && sizeof($c) > 1 ? $value : $value % 60, isset($c) && $value < 60 ? 1 : 2, 0, STR_PAD_LEFT);
    }while(($value = floor($value / 60)) > 0 && sizeof($c) < 3);
    
    $return_value = implode(":", array_reverse($c));

	return $return_value;
}



//words
\aw2_library::add_service('m.words','Break the value as words and return. Use m.words=yes or m.words="n:<no of words>"',['namespace'=>__NAMESPACE__]);
function words($value, $atts){
	if($atts['words'] === 'yes')
		$words = -1;
	else
		$words = substr( $atts['words'], 2, strlen($atts['words']) );
	
	$value = \aw2_library::break_words((string)$value, $words);
	return $value;
}

//url_encode
\aw2_library::add_service('m.url_encode','Encode the URL and return. Use m.url_encode',['namespace'=>__NAMESPACE__]);
function url_encode($value, $atts){
	$value = urlencode((string)$value);
	return $value;
}

//url_decode
\aw2_library::add_service('m.url_decode','Decode the URL and return. Use m.url_decode',['namespace'=>__NAMESPACE__]);
function url_decode($value, $atts){
	$value = urldecode((string)$value);
	return $value;
}

//arr_item
\aw2_library::add_service('m.arr_item','If the value is an array, return the nth element. Use m.arr_item=<s:first|s:last|n:<nth element>|s:<key>>',['namespace'=>__NAMESPACE__]);
function arr_item($value, $atts){
	if(is_array($value)){
		
		$type = substr( $atts['arr_item'], 0, 2 );	
		$element = substr( $atts['arr_item'], 2, strlen($atts['arr_item']) );
		
		if($type === 's:'){
			switch ($element) {
				case 'first':
					reset($value);
					$value= current($value);
					break;
				case 'last':
					$arr=$value;
					$value=end($value);
					reset($arr);
					break;
				default:
					$value=$value[$element];
					break;
			}
		}
		if($type === 'n:'){
			$keys = array_keys($value);
			$value= $value[$keys[$element-1]]; //We do this to get the nth element of the array.
		}
	}
	return $value;
}


//unset_item
\aw2_library::add_service('m.unset_item','If the value is an array, unset element from array and return array. Use m.unset_item=<first|last| element_key>',['namespace'=>__NAMESPACE__]);
function unset_item($value, $atts){
	
	if(is_array($value)){
		
		
		$element = $atts['unset_item'];
		
		if($element){
			switch ($element) {
				case 'first':
					array_shift($value);
					break;
				case 'last':
					array_pop($value);
					break;
				default:
					//$value = $element;
					unset($value[$element]);
					break;
			}
		}
		
	}
	return $value;
}


//shuffle
\aw2_library::add_service('m.shuffle','If the value is an array, shuffle the elements and return. Use m.shuffle',['func'=>'_shuffle','namespace'=>__NAMESPACE__]);
function _shuffle($value, $atts){
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

//esc_sql
\aw2_library::add_service('m.esc_sql','Use m.esc_sql',['func'=>'_esc_sql','namespace'=>__NAMESPACE__]);
function _esc_sql($value, $atts){
	$value = esc_sql((string)$value);
	return $value;
}

//math solve
\aw2_library::add_service('m.solve','Solve a math string. Use m.solve=""',['namespace'=>__NAMESPACE__]);
function solve($value, $atts){
	$pattern = '/([^-\d.\(\)\+\*\/ \^%])/';
	$replacement = '';
	$result= preg_replace($pattern, $replacement, $value);
	$value=eval('return ' . $result .  ' ;');
	return $value;
}

//to string
\aw2_library::add_service('m.to_str','Typecast the value to string and return. Use m.to_str',['namespace'=>__NAMESPACE__]);
function to_str($value, $atts){
	$value = (string) $value;
	return $value;
}

//to number
\aw2_library::add_service('m.to_num','Typecast the value to float and return. Use m.to_num',['namespace'=>__NAMESPACE__]);
function to_num($value, $atts){
	$value = (float) $value;
	return $value;
}

//to interger
\aw2_library::add_service('m.to_int','Typecast the value to integer and return. Use m.to_int',['namespace'=>__NAMESPACE__]);
function to_int($value, $atts){
	$value = (int) $value;
	return $value;
}

//to boolean
\aw2_library::add_service('m.to_bool','Typecast the value to boolean and return. Use m.to_bool',['namespace'=>__NAMESPACE__]);
function to_bool($value, $atts){
	$value = (bool) $value;
	return $value;
}

//append to value
\aw2_library::add_service('m.append','Append the given string and return. Use m.append',['namespace'=>__NAMESPACE__]);
function append($value, $atts){
	$append = $atts['append'];
	$value = $value.$append;
	
	return $value;
}

//prepend to value
\aw2_library::add_service('m.prepend','Prepend the given string and return. Use m.prepend',['namespace'=>__NAMESPACE__]);
function prepend($value, $atts){
	$prepend = $atts['prepend'];
	$value = $prepend.$value;
	
	return $value;
}

//round to value
\aw2_library::add_service('m.num_round','round the given value and return. Use m.num_round',['namespace'=>__NAMESPACE__]);
function num_round($value, $atts){
	$value = round($value);
	return $value;
}