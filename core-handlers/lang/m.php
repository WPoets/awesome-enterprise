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
	$value = \awesome_wp_utils::the_content_filter($value);
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

	$value = (float) $value;

	$format = $atts['money_format'];
	if(empty($format)){
		$format = 'en_IN';
	}
	if($format=="yes"){
		$format = 'en_IN';
	}

	if(isset($atts['currency'])){
		$currency = $atts['currency'];
	}else{
		$currency = 'INR';
	}
	if(empty($currency)){
		$currency = 'INR';
	}

	$fmt = new \NumberFormatter( $format, \NumberFormatter::CURRENCY );
	$value = $fmt->formatCurrency($value, $currency);
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
	$value = \aw2_library::esc_sql((string)$value);
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


//return empty array
\aw2_library::add_service('m.empty_array','given empty array in return. Use m.empty_array',['namespace'=>__NAMESPACE__]);
function empty_array($value, $atts){
	$value = array();
	return $value;
}


//number to word
\aw2_library::add_service('m.number_to_word','Return the value as capitalized. Use m.number_to_word',['namespace'=>__NAMESPACE__]);
function number_to_word($value, $atts){
	$value = \util::number_to_word($value);
	return $value;
}


\aw2_library::add_service('m.sanitize_title','Return the value as text with sanitze. Use m.santize_title',['namespace'=>__NAMESPACE__]);
function sanitize_title( $value, $atts){
	$title = $value;
	
	$fallback_title = isset($atts['fallback_title'] )? $atts['fallback_title'] :'';
	$context = 'save';
    $raw_title = $title;
    if ( 'save' === $context ) {
        $title = custom_remove_accents( $title );
    }

    $title = custom_sanitize_title_with_dashes( $title );

    if ( '' === $title || false === $title ) {
        $title = $fallback_title;
    }

    return $title;
}


\aw2_library::add_service('m.sort','Returns array sorted based on paramters specifed. Use m.sort',['namespace'=>__NAMESPACE__]);
function sort( $value, $atts){
	
    $func = $atts['sort'];
    $supported_arr = array('asort','arsort','krsort','ksort','rsort','sort','array_multisort');
    
    if(!in_array($func,$supported_arr))
        return $value;

    $func($value);
    
    return $value;
}

function custom_sanitize_title_with_dashes( $title, $raw_title = '', $context = 'display' ) {
    $title = strip_tags( $title );
    // Preserve escaped octets.
    $title = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title );
    // Remove percent signs that are not part of an octet.
    $title = str_replace( '%', '', $title );
    // Restore octets.
    $title = preg_replace( '|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title );

    if ( custom_seems_utf8( $title ) ) {
        if ( function_exists( 'mb_strtolower' ) ) {
            $title = mb_strtolower( $title, 'UTF-8' );
        }
        $title = custom_utf8_uri_encode( $title, 200 );
    }

    $title = strtolower( $title );

    if ( 'save' === $context ) {
        // Convert &nbsp, &ndash, and &mdash to hyphens.
        $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
        // Convert &nbsp, &ndash, and &mdash HTML entities to hyphens.
        $title = str_replace( array( '&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;' ), '-', $title );
        // Convert forward slash to hyphen.
        $title = str_replace( '/', '-', $title );

        // Strip these characters entirely.
        $title = str_replace(
            array(
                // Soft hyphens.
                '%c2%ad',
                // &iexcl and &iquest.
                '%c2%a1',
                '%c2%bf',
                // Angle quotes.
                '%c2%ab',
                '%c2%bb',
                '%e2%80%b9',
                '%e2%80%ba',
                // Curly quotes.
                '%e2%80%98',
                '%e2%80%99',
                '%e2%80%9c',
                '%e2%80%9d',
                '%e2%80%9a',
                '%e2%80%9b',
                '%e2%80%9e',
                '%e2%80%9f',
                // Bullet.
                '%e2%80%a2',
                // &copy, &reg, &deg, &hellip, and &trade.
                '%c2%a9',
                '%c2%ae',
                '%c2%b0',
                '%e2%80%a6',
                '%e2%84%a2',
                // Acute accents.
                '%c2%b4',
                '%cb%8a',
                '%cc%81',
                '%cd%81',
                // Grave accent, macron, caron.
                '%cc%80',
                '%cc%84',
                '%cc%8c',
            ),
            '',
            $title
        );

        // Convert &times to 'x'.
        $title = str_replace( '%c3%97', 'x', $title );
    }

    // Kill entities.
    $title = preg_replace( '/&.+?;/', '', $title );
    $title = str_replace( '.', '-', $title );

    $title = preg_replace( '/[^%a-z0-9 _-]/', '', $title );
    $title = preg_replace( '/\s+/', '-', $title );
    $title = preg_replace( '|-+|', '-', $title );
    $title = trim( $title, '-' );

    return $title;
}

function custom_remove_accents( $string ) {
    if ( ! preg_match( '/[\x80-\xff]/', $string ) ) {
        return $string;
    }

    if ( custom_seems_utf8( $string ) ) {
        $chars = array(
            // Decompositions for Latin-1 Supplement.
            'ª' => 'a',
            'º' => 'o',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'D',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'TH',
            'ß' => 's',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'd',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'þ' => 'th',
            'ÿ' => 'y',
            'Ø' => 'O',
            // Decompositions for Latin Extended-A.
            'Ā' => 'A',
            'ā' => 'a',
            'Ă' => 'A',
            'ă' => 'a',
            'Ą' => 'A',
            'ą' => 'a',
            'Ć' => 'C',
            'ć' => 'c',
            'Ĉ' => 'C',
            'ĉ' => 'c',
            'Ċ' => 'C',
            'ċ' => 'c',
            'Č' => 'C',
            'č' => 'c',
            'Ď' => 'D',
            'ď' => 'd',
            'Đ' => 'D',
            'đ' => 'd',
            'Ē' => 'E',
            'ē' => 'e',
            'Ĕ' => 'E',
            'ĕ' => 'e',
            'Ė' => 'E',
            'ė' => 'e',
            'Ę' => 'E',
            'ę' => 'e',
            'Ě' => 'E',
            'ě' => 'e',
            'Ĝ' => 'G',
            'ĝ' => 'g',
            'Ğ' => 'G',
            'ğ' => 'g',
            'Ġ' => 'G',
            'ġ' => 'g',
            'Ģ' => 'G',
            'ģ' => 'g',
            'Ĥ' => 'H',
            'ĥ' => 'h',
            'Ħ' => 'H',
            'ħ' => 'h',
            'Ĩ' => 'I',
            'ĩ' => 'i',
            'Ī' => 'I',
            'ī' => 'i',
            'Ĭ' => 'I',
            'ĭ' => 'i',
            'Į' => 'I',
            'į' => 'i',
            'İ' => 'I',
            'ı' => 'i',
            'Ĳ' => 'IJ',
            'ĳ' => 'ij',
            'Ĵ' => 'J',
            'ĵ' => 'j',
            'Ķ' => 'K',
            'ķ' => 'k',
            'ĸ' => 'k',
            'Ĺ' => 'L',
            'ĺ' => 'l',
            'Ļ' => 'L',
            'ļ' => 'l',
            'Ľ' => 'L',
            'ľ' => 'l',
            'Ŀ' => 'L',
            'ŀ' => 'l',
            'Ł' => 'L',
            'ł' => 'l',
            'Ń' => 'N',
            'ń' => 'n',
            'Ņ' => 'N',
            'ņ' => 'n',
            'Ň' => 'N',
            'ň' => 'n',
            'ŉ' => 'n',
            'Ŋ' => 'N',
            'ŋ' => 'n',
            'Ō' => 'O',
            'ō' => 'o',
            'Ŏ' => 'O',
            'ŏ' => 'o',
            'Ő' => 'O',
            'ő' => 'o',
            'Œ' => 'OE',
            'œ' => 'oe',
            'Ŕ' => 'R',
            'ŕ' => 'r',
            'Ŗ' => 'R',
            'ŗ' => 'r',
            'Ř' => 'R',
            'ř' => 'r',
            'Ś' => 'S',
            'ś' => 's',
            'Ŝ' => 'S',
            'ŝ' => 's',
            'Ş' => 'S',
            'ş' => 's',
            'Š' => 'S',
            'š' => 's',
            'Ţ' => 'T',
            'ţ' => 't',
            'Ť' => 'T',
            'ť' => 't',
            'Ŧ' => 'T',
            'ŧ' => 't',
            'Ũ' => 'U',
            'ũ' => 'u',
            'Ū' => 'U',
            'ū' => 'u',
            'Ŭ' => 'U',
            'ŭ' => 'u',
            'Ů' => 'U',
            'ů' => 'u',
            'Ű' => 'U',
            'ű' => 'u',
            'Ų' => 'U',
            'ų' => 'u',
            'Ŵ' => 'W',
            'ŵ' => 'w',
            'Ŷ' => 'Y',
            'ŷ' => 'y',
            'Ÿ' => 'Y',
            'Ź' => 'Z',
            'ź' => 'z',
            'Ż' => 'Z',
            'ż' => 'z',
            'Ž' => 'Z',
            'ž' => 'z',
            'ſ' => 's',
            // Decompositions for Latin Extended-B.
            'Ș' => 'S',
            'ș' => 's',
            'Ț' => 'T',
            'ț' => 't',
            // Euro sign.
            '€' => 'E',
            // GBP (Pound) sign.
            '£' => '',
            // Vowels with diacritic (Vietnamese).
            // Unmarked.
            'Ơ' => 'O',
            'ơ' => 'o',
            'Ư' => 'U',
            'ư' => 'u',
            // Grave accent.
            'Ầ' => 'A',
            'ầ' => 'a',
            'Ằ' => 'A',
            'ằ' => 'a',
            'Ề' => 'E',
            'ề' => 'e',
            'Ồ' => 'O',
            'ồ' => 'o',
            'Ờ' => 'O',
            'ờ' => 'o',
            'Ừ' => 'U',
            'ừ' => 'u',
            'Ỳ' => 'Y',
            'ỳ' => 'y',
            // Hook.
            'Ả' => 'A',
            'ả' => 'a',
            'Ẩ' => 'A',
            'ẩ' => 'a',
            'Ẳ' => 'A',
            'ẳ' => 'a',
            'Ẻ' => 'E',
            'ẻ' => 'e',
            'Ể' => 'E',
            'ể' => 'e',
            'Ỉ' => 'I',
            'ỉ' => 'i',
            'Ỏ' => 'O',
            'ỏ' => 'o',
            'Ổ' => 'O',
            'ổ' => 'o',
            'Ở' => 'O',
            'ở' => 'o',
            'Ủ' => 'U',
            'ủ' => 'u',
            'Ử' => 'U',
            'ử' => 'u',
            'Ỷ' => 'Y',
            'ỷ' => 'y',
            // Tilde.
            'Ẫ' => 'A',
            'ẫ' => 'a',
            'Ẵ' => 'A',
            'ẵ' => 'a',
            'Ẽ' => 'E',
            'ẽ' => 'e',
            'Ễ' => 'E',
            'ễ' => 'e',
            'Ỗ' => 'O',
            'ỗ' => 'o',
            'Ỡ' => 'O',
            'ỡ' => 'o',
            'Ữ' => 'U',
            'ữ' => 'u',
            'Ỹ' => 'Y',
            'ỹ' => 'y',
            // Acute accent.
            'Ấ' => 'A',
            'ấ' => 'a',
            'Ắ' => 'A',
            'ắ' => 'a',
            'Ế' => 'E',
            'ế' => 'e',
            'Ố' => 'O',
            'ố' => 'o',
            'Ớ' => 'O',
            'ớ' => 'o',
            'Ứ' => 'U',
            'ứ' => 'u',
            // Dot below.
            'Ạ' => 'A',
            'ạ' => 'a',
            'Ậ' => 'A',
            'ậ' => 'a',
            'Ặ' => 'A',
            'ặ' => 'a',
            'Ẹ' => 'E',
            'ẹ' => 'e',
            'Ệ' => 'E',
            'ệ' => 'e',
            'Ị' => 'I',
            'ị' => 'i',
            'Ọ' => 'O',
            'ọ' => 'o',
            'Ộ' => 'O',
            'ộ' => 'o',
            'Ợ' => 'O',
            'ợ' => 'o',
            'Ụ' => 'U',
            'ụ' => 'u',
            'Ự' => 'U',
            'ự' => 'u',
            'Ỵ' => 'Y',
            'ỵ' => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin).
            'ɑ' => 'a',
            // Macron.
            'Ǖ' => 'U',
            'ǖ' => 'u',
            // Acute accent.
            'Ǘ' => 'U',
            'ǘ' => 'u',
            // Caron.
            'Ǎ' => 'A',
            'ǎ' => 'a',
            'Ǐ' => 'I',
            'ǐ' => 'i',
            'Ǒ' => 'O',
            'ǒ' => 'o',
            'Ǔ' => 'U',
            'ǔ' => 'u',
            'Ǚ' => 'U',
            'ǚ' => 'u',
            // Grave accent.
            'Ǜ' => 'U',
            'ǜ' => 'u',
        );

        $string = strtr( $string, $chars );
    } else {
        $chars = array();
        // Assume ISO-8859-1 if not UTF-8.
        $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
            . "\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
            . "\xc3\xc4\xc5\xc7\xc8\xc9\xca"
            . "\xcb\xcc\xcd\xce\xcf\xd1\xd2"
            . "\xd3\xd4\xd5\xd6\xd8\xd9\xda"
            . "\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
            . "\xe4\xe5\xe7\xe8\xe9\xea\xeb"
            . "\xec\xed\xee\xef\xf1\xf2\xf3"
            . "\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
            . "\xfc\xfd\xff";

        $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';

        $string              = strtr( $string, $chars['in'], $chars['out'] );
        $double_chars        = array();
        $double_chars['in']  = array( "\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe" );
        $double_chars['out'] = array( 'OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th' );
        $string              = str_replace( $double_chars['in'], $double_chars['out'], $string );
    }

    return $string;
}


function custom_seems_utf8( $str ) {
    custom_mbstring_binary_safe_encoding();
    $length = strlen( $str );
    custom_reset_mbstring_encoding();
    for ( $i = 0; $i < $length; $i++ ) {
        $c = ord( $str[ $i ] );
        if ( $c < 0x80 ) {
            $n = 0; // 0bbbbbbb
        } elseif ( ( $c & 0xE0 ) == 0xC0 ) {
            $n = 1; // 110bbbbb
        } elseif ( ( $c & 0xF0 ) == 0xE0 ) {
            $n = 2; // 1110bbbb
        } elseif ( ( $c & 0xF8 ) == 0xF0 ) {
            $n = 3; // 11110bbb
        } elseif ( ( $c & 0xFC ) == 0xF8 ) {
            $n = 4; // 111110bb
        } elseif ( ( $c & 0xFE ) == 0xFC ) {
            $n = 5; // 1111110b
        } else {
            return false; // Does not match any model.
        }
        for ( $j = 0; $j < $n; $j++ ) { // n bytes matching 10bbbbbb follow ?
            if ( ( ++$i == $length ) || ( ( ord( $str[ $i ] ) & 0xC0 ) != 0x80 ) ) {
                return false;
            }
        }
    }
    return true;
}

function custom_mbstring_binary_safe_encoding( $reset = false ) {
    static $encodings  = array();
    static $overloaded = null;

    if ( is_null( $overloaded ) ) {
        $overloaded = function_exists( 'mb_internal_encoding' ); // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.mbstring_func_overloadDeprecated
    }

    if ( false === $overloaded ) {
        return;
    }

    if ( ! $reset ) {
        $encoding = mb_internal_encoding();
        array_push( $encodings, $encoding );
        mb_internal_encoding( 'ISO-8859-1' );
    }

    if ( $reset && $encodings ) {
        $encoding = array_pop( $encodings );
        mb_internal_encoding( $encoding );
    }
}

function custom_reset_mbstring_encoding() {
    custom_mbstring_binary_safe_encoding( true );
}

function custom_utf8_uri_encode( $utf8_string, $length = 0 ) {
    $unicode        = '';
    $values         = array();
    $num_octets     = 1;
    $unicode_length = 0;

    custom_mbstring_binary_safe_encoding();
    $string_length = strlen( $utf8_string );
    custom_reset_mbstring_encoding();

    for ( $i = 0; $i < $string_length; $i++ ) {

        $value = ord( $utf8_string[ $i ] );

        if ( $value < 128 ) {
            if ( $length && ( $unicode_length >= $length ) ) {
                break;
            }
            $unicode .= chr( $value );
            $unicode_length++;
        } else {
            if ( count( $values ) == 0 ) {
                if ( $value < 224 ) {
                    $num_octets = 2;
                } elseif ( $value < 240 ) {
                    $num_octets = 3;
                } else {
                    $num_octets = 4;
                }
            }

            $values[] = $value;

            if ( $length && ( $unicode_length + ( $num_octets * 3 ) ) > $length ) {
                break;
            }
            if ( count( $values ) == $num_octets ) {
                for ( $j = 0; $j < $num_octets; $j++ ) {
                    $unicode .= '%' . dechex( $values[ $j ] );
                }

                $unicode_length += $num_octets * 3;

                $values     = array();
                $num_octets = 1;
            }
        }
    }

    return $unicode;
}
