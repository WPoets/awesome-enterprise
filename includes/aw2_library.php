<?php
/*
//Concatenation is the slowest operation
$time_start= microtime(true); 

	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 11;
	$redis->select($database_number);

for ($x = 0; $x <= 10000; $x++) {
	$str='';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . 'hello';
	$str=$str . $x;
	
	//$redis->rpush('mylist',$str);
	$arr[]=$str;
} 
//$arr=$redis->lrange(mylist,0,10000);
$z=implode($arr);
$time_end= microtime(true); 
$diff=$time_end - $time_start;
echo '<br>PHP:' . $diff;
//echo $z;
*/

class aw2_library{

static $conn=null;
static $stack=array();
static $plugin_path=null;

static function setup(){
	self::$plugin_path=plugin_dir_path( __DIR__ );
	$files = self::$plugin_path . '/handlers';
	foreach (glob($files . "/*.php") as $filename)
	{
		include $filename;
	}
}

static function redis_connect($database_number){
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$redis->select($database_number);
	return $redis;	
}
// takes a json and returns back an array
static function get_clean_args($content,&$atts=null){
	if($content==null || $content=='')return '';
	$json=self::clean_specialchars($content);
	$json=self::checkshortcode(self::parse_shortcode($json));
	$args=json_decode($json, true);
	if($json && is_null($args)){
		self::set_error('Invalid JSON' . $json);
	}
	return $args;
}


static function checkshortcode($string ) {
	$pos = strpos($string, "{{");
	if ($pos === false) {
		return $string;
	} else {
		$string=str_replace ( "{{", "[" , $string);
		$string=str_replace ( "}}", "]" , $string);
		return self::parse_shortcode($string);
	}
}

static function clean_specialchars($content){
	$content=str_replace ( "&#8216;" , "'" ,$content );
	$content=str_replace ( "&#8217;" , "'" ,$content );
	$content=str_replace ( "&#8220;" , '"' ,$content );
	$content=str_replace ( "&#8221;" , '"' ,$content );
	$content=str_replace ( "&#8243;" , '"' ,$content );
	$content=str_replace ( "&#039;" , "'" ,$content );
	return $content;
}

static function clean_html($content){
	$content=preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $content);
	return $content;
}

static function break_words($content, $length , $tags = '<a><em><strong>',$extra = '') {

	$output = strip_shortcodes(strip_tags($content), $tags);
	$output = preg_split('/\b/', $output, $length * 2 + 1);
	$excerpt_waste = array_pop($output);
	$output = implode($output);
	$output .= $extra;

	return $output ;
}	


static function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

static function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

//----------------------------------------------------------------------------------------------
//Shortcode Functions
static function reg_shortcode($shortcode_name, $func_name){
		add_shortcode($shortcode_name, $func_name);
	}

static function parse_shortcode( $content, $ignore_html = false ) {
	$content = preg_replace("/\/\/\*.*\*\/\//sU", "", $content);
	if ( false === strpos( $content, '[' ) )return $content;

	
	$pattern = self::get_shortcode_regex();
	$pattern =str_replace("_handler","(?:[a-zA-Z0-9\-._@])+",$pattern);	
	$content = preg_replace_callback( "/$pattern/s", 'self::shortcode_tag', $content );

	if(isset(self::$stack['_return']))return '';
	
	// Always restore square braces so we don't break things like <!--[if IE ]>
	$content = unescape_invalid_shortcodes( $content );
	$content=str_replace ( "&osb;", "[" , $content);
	$content=str_replace ( "&csb;", "]" , $content);

	return trim($content);
}

static function shortcode_tag( $m ) {
	global $shortcode_tags;
	if(isset(self::$stack['_return']))return '';

	// allow [[foo]] syntax for escaping a tag
	if ( $m[1] == '[' && $m[6] == ']' ) {
		return substr($m[0], 1, -1);
	}

	$tag = $m[2];
	$attr = self::shortcode_parse_atts( $m[3] );
	

	if ( isset( $m[5] ) )
		$content=$m[5];	
	else
		$content=null;

	$pieces=explode('.',$tag);

	if(count($pieces)>=2){
		$sc_handler=$pieces[0];
		$sc_tag=$pieces[1];
		
		//check whether i have the library
		$handlers=&self::get_array_ref('handlers');
		$sc=array();
		$sc['tags']=$pieces;
		if(isset($handlers[$sc_handler])){
			if(isset($handlers[$sc_handler][$sc_tag]['func'])){
				$sc['handler']=$handlers[$sc_handler][$sc_tag];

				return $m[1] . call_user_func($handlers[$sc_handler][$sc_tag]['func'], $attr, $content, $tag ) . $m[6];
			}		
			
			if(isset($handlers[$sc_handler]['alias'])){
				$sc['handler']=$handlers[$sc_handler];

				if(is_callable('aw2_' . $handlers[$sc_handler]['alias'] . '_' . $sc_tag))
					return $m[1] . call_user_func('aw2_' . $handlers[$sc_handler]['alias'] . '_' . $sc_tag, $attr, $content, $sc ) . $m[6];
			
				if(is_callable('aw2_' . $handlers[$sc_handler]['alias'] . '_unhandled'))
					return $m[1] . call_user_func('aw2_' . $handlers[$sc_handler]['alias'] . '_unhandled', $attr, $content, $sc ) . $m[6];
			}		
		}
	}
	
	if(isset($shortcode_tags[$tag])){
		return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, $content, $tag ) . $m[6];
	}

	return $m[0];
	
}


static function shortcode_parse_atts($text) {
	$atts = array();
	$pattern = '/([-a-zA-Z0-9_.@]+)\s*=\s*"([^"]*)"(?:\s|$)|([-a-zA-Z0-9_.@]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([-a-zA-Z0-9_.@]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
	$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
	if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
		foreach ($match as $m) {
			if (!empty($m[1]))
				$atts[strtolower($m[1])] = stripcslashes($m[2]);
			elseif (!empty($m[3]))
				$atts[strtolower($m[3])] = stripcslashes($m[4]);
			elseif (!empty($m[5]))
				$atts[strtolower($m[5])] = stripcslashes($m[6]);
			elseif (isset($m[7]) && strlen($m[7]))
				$atts[] = stripcslashes($m[7]);
			elseif (isset($m[8]))
				$atts[] = stripcslashes($m[8]);
		}

		// Reject any unclosed HTML elements
		foreach( $atts as &$value ) {
			if ( false !== strpos( $value, '<' ) ) {
				if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
					$value = '';
				}
			}
		}
	} else {
		$atts = ltrim($text);
	}
	return $atts;
}

static function add_shortcode($library,$tag, $func,$desc=null) {
	$handler=&self::get_array_ref('handlers',$library);
	$handler[$tag]=array();
	$handler[$tag]['name']=$tag;
	$handler[$tag]['desc']=$desc;
	$handler[$tag]['func']=$func;
}

static $libraries=array();

static function add_library($library,$desc=null,$alias=null) {
	$handler=&self::get_array_ref('handlers',$library);
	if(!$alias)$alias=$library;
	$handler['alias']=$alias;
	$handler['desc']=$desc;
}


static function add_collection($name,$atts,$desc=null) {
	$arr=$atts;
	$arr['alias']='collection';
	$arr['desc']=$desc;
	$handler=&self::get_array_ref('handlers',$name);
	$handler=array_merge($handler,$arr);
}



static function register_service($name,$atts,$desc=null) {
	$arr=$atts;
	$arr['alias']='service';
	$arr['desc']=$desc;
	$handler=&self::get_array_ref('handlers',$name);
	$handler=array_merge($handler,$arr);
}


static function collection_define($collection,$atts){
	if (!is_array(self::$stack['collections']))self::$stack['collections']=array();
	self::$stack['collections'][$collection]=$atts;
	self::add_library($collection,'Collection','collection'); 
}

static function get_shortcode_regex() {
	global $shortcode_tags;
	$tagnames = array_keys($shortcode_tags);
	$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
	$tagregexp = $tagregexp . '|(?:[a-zA-Z0-9\-_@])+\.(?:[a-zA-Z0-9\-._@])+';
	// WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
	// Also, see shortcode_unautop() and shortcode.js.
	return
		  '\\['                              // Opening bracket
		. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
		. "($tagregexp)"                     // 2: Shortcode name
		. '(?![\\w-])'                       // Not followed by word character or hyphen
		. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
		.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
		.     '(?:'
		.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
		.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
		.     ')*?'
		. ')'
		. '(?:'
		.     '(\\/)'                        // 4: Self closing tag ...
		.     '\\]'                          // ... and closing bracket
		. '|'
		.     '\\]'                          // Closing bracket
		.     '(?:'
		.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
		.             '[^\\[]*+'             // Not an opening bracket
		.             '(?:'
		.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
		.                 '[^\\[]*+'         // Not an opening bracket
		.             ')*+'
		.         ')'
		.         '\\[\\/\\2\\]'             // Closing shortcode tag
		.     ')?'
		. ')'
		. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
}


// ------------------------------------------------------------------------------------
//stack functions
static function &get_array_ref($context1=null,$context2=null,$context3=null){
	if(!$context1)
		return self::$stack;
	if(!array_key_exists($context1,self::$stack))
		self::$stack[$context1]=array();

	if($context2 && !array_key_exists($context2,self::$stack[$context1]))
		self::$stack[$context1][$context2]=array();
	
	if($context3 && !array_key_exists($context3,self::$stack[$context1][$context2]))
		self::$stack[$context1][$context2][$context3]=array();

	if(!$context2)
		return self::$stack[$context1];
	
	if(!$context3)
		return self::$stack[$context1][$context2];
	
	return self::$stack[$context1][$context2][$context3];
}

static function push_obj($type,$create=true){
	//Create Local Data
	if($create==true){
		$array_name=self::rand_gen();
		$new_data=&self::get_array_ref('data','history');
		$new_data[$array_name]=array();
		self::$stack['data']['module']=&self::$stack['data']['history'][$array_name];
		
	}
	else{
		$stack=&self::get_array_ref('call_stack',$type);
		end($stack);
		$array_name=$stack;
		reset($stack);
	}
	
	
	//Register in stack
	$stack=&self::get_array_ref('call_stack',$type);
	$stack[]=$array_name;

	
}

static function set_error($msg){
	self::set('errors.new',$msg);
}



//pre actions	
static function pre_actions($actions,&$atts=null,$content,$shortcode=null){
	$return_value=true;
	if(!$atts) return $return_value;
	if($actions==='all'){
		$return_value=self::pre_action_parse($atts);
		$return_value=self::checkcondition($atts);
		return $return_value;
	}
	switch ($actions) {
		case 'parse_attributes':
			$return_value=self::pre_action_parse($atts);
			break;
		case 'check_if':
			$return_value=self::checkcondition($atts);
			break;
	}
	return $return_value;
	
}

static function pre_action_parse(&$atts) {
	foreach ($atts as $key => $value) {
		if (is_int($key)) {
			$atts['main']=$value;	
			unset($atts[$key]);
		}
	}
	
	
	
	foreach ($atts as $key =>$value) {
		if (is_string($value) && strpos($value, '{') !== false) {

			$startpos = strrpos($value, "{");
			$stoppos = strpos($value, "}");
			if ($startpos === 0 && $stoppos===strlen($value)-1 and strpos($value, " ")===false) {
				$value=str_replace("{","",$value);		
				$value=str_replace("}","",$value);		
				$atts[$key]=self::get($value);
			}
			else{
				$patterns = array();
				$patterns[0] = '/{{(.+?)}}/';
				$patterns[1] = '/{(.+?)}/';

				$replacements = array();
				$replacements[0] = '[$1]';
				$replacements[1] = '[aw2.get $1]';
				$value=preg_replace($patterns, $replacements, $value);
				$atts[$key]=self::parse_shortcode($value);
			}

		}
		
	}
	return;
}
	
static function checkcondition(&$atts){
	if(!$atts)return true;


		if(array_key_exists('ignore',$atts)){
			return false;
		}

		if(array_key_exists('odd',$atts)){
			if((int)$atts['odd'] % 2 == 0)
		return false;
	else
		unset($atts['odd']);  
		}
		
		if(array_key_exists('even',$atts)){
			if((int)$atts['even'] % 2 != 0)
		return false;
	else
		unset($atts['even']);  
		}

		if(array_key_exists('true',$atts)){
			if($atts['true']!=true)
		return false;
	else
		unset($atts['true']); 
	}

	if(array_key_exists('false',$atts)){
			if($atts['false']==true)
		return false;
	else
		unset($atts['false']); 
	}

	if(array_key_exists('yes',$atts)){
		if($atts['yes']!=='yes')
			return false;
		else
			unset($atts['yes']); 
	}

	if(array_key_exists('no',$atts)){
		if($atts['no']!=='no')
			return false;
		else
			unset($atts['no']); 
	}
	
	if(array_key_exists('arr',$atts)){
		if(!is_array($atts['arr']))
			return false;
		else
			unset($atts['arr']); 
	}
	
	if(array_key_exists('not_arr',$atts)){
		if(is_array($atts['not_arr']))
			return false;
		else
			unset($atts['not_arr']); 
	}
	
	if(array_key_exists('str',$atts)){
		if(!is_string($atts['str']))
			return false;
		else
			unset($atts['str']); 
	}
	
	if(array_key_exists('not_str',$atts)){
		if(is_string($atts['not_str']))
			return false;
		else
			unset($atts['not_str']); 
	}
	
	if(array_key_exists('bool',$atts)){
		if(!is_bool($atts['bool']))
			return false;
		else
			unset($atts['bool']); 
	}
	
	if(array_key_exists('not_bool',$atts)){
		if(is_bool($atts['not_bool']))
			return false;
		else
			unset($atts['not_bool']); 
	}
	
	if(array_key_exists('num',$atts)){
		if(!is_numeric($atts['num']))
			return false;
		else
			unset($atts['num']); 
	}
	
	if(array_key_exists('greater_than_zero',$atts)){
		if(!is_numeric($atts['greater_than_zero']) || $atts['greater_than_zero']<=0 )
			return false;
		else
			unset($atts['greater_than_zero']); 
	}

	
	if(array_key_exists('is_num',$atts)){
		if(!is_numeric($atts['is_num']))
			return false;
		else
			unset($atts['is_num']); 
	}
	
	if(array_key_exists('not_num',$atts)){
		if(is_numeric($atts['not_num']))
			return false;
		else
			unset($atts['not_num']); 
	}

	if(array_key_exists('int',$atts)){
		if(!is_int($atts['int']))
			return false;
		else
			unset($atts['int']); 
	}
	
	if(array_key_exists('not_int',$atts)){
		if(is_int($atts['not_int']))
			return false;
		else
			unset($atts['not_int']); 
	}
	
	if(array_key_exists('date_obj',$atts)){
		if(!get_class($atts['date_obj'])=='DateTime')
			return false;
		else
			unset($atts['date_obj']); 
	}
	
	if(array_key_exists('not_date_obj',$atts)){
		if(get_class($atts['date_obj']))
			return false;
		else
			unset($atts['not_date_obj']); 
	}

	if(array_key_exists('obj',$atts)){
		if(!is_object($atts['obj']))
			return false;
		else
			unset($atts['obj']); 
	}
	
	if(array_key_exists('not_obj',$atts)){
		if(is_object($atts['not_obj']))
			return false;
		else
			unset($atts['not_obj']); 
	}
	
	
		if(array_key_exists('empty',$atts)){
			if(!empty($atts['empty']))
		return false;
	else
		unset($atts['empty']); 
	}

	if(array_key_exists('not_empty',$atts)){
		if(empty($atts['not_empty']))
			return false;
		else
			unset($atts['not_empty']); 
	}

	if(array_key_exists('whitespace',$atts)){
		if($atts['whitespace'] === '' || !(ctype_space($atts['whitespace'])))return false;
	else
		unset($atts['whitespace']); 
	}

	if(array_key_exists('not_whitespace',$atts)){
		if(ctype_space($atts['not_whitespace']) || $atts['not_whitespace'] === '')return false;
	else
		unset($atts['not_whitespace']); 
	}

	
		if(array_key_exists('user_can',$atts)){
	if(current_user_can($atts['user_can'])===false)
		return false;
	else
		unset($atts['user_can']); 
		}
	
		if(array_key_exists('user_cannot',$atts)){
	if(current_user_can($atts['user_cannot']))
		return false;
	else
		unset($atts['user_cannot']); 
		}
		
		if(array_key_exists('logged_in',$atts)){
	if(!is_user_logged_in())
		return false;
	else
		unset($atts['logged_in']); 
		}

		if(array_key_exists('not_logged_in',$atts)){
	if(is_user_logged_in())
		return false;
	else
		unset($atts['not_logged_in']); 
		}

		if(array_key_exists('request_exists',$atts)){
	if(self::get_request($atts['request_exists'])==null)
		return false;
	else
		unset($atts['request_exists']); 		  
		}	  
	
		if(array_key_exists('request_not_exists',$atts)){
	if(self::get_request($atts['request_not_exists'])!=null)
		return false;
	else
		unset($atts['request_not_exists']); 		  
		}

		if(array_key_exists('ajax',$atts)){
	if(self::get_request('ajax')!='true')
		return false;
	else
		unset($atts['ajax']); 
		}

		if(array_key_exists('not_ajax',$atts)){
	if(self::get_request('ajax')=='true')
		return false;
	else
		unset($atts['not_ajax']); 
		}
	
		if(array_key_exists('request_part',$atts)){
	if((self::get_request('part') ==$atts['request_part']) || (self::get_request('part')==null && $atts['request_part']=='default') )
		unset($atts['request_part']); 
	else
		return false;	
		}	  

		if(array_key_exists('list',$atts) && array_key_exists('contains',$atts) ){
			if(!is_array($atts['list']))
				$arr= explode( ',' ,$atts['list'] );
			else
				$arr=$atts['list']; 
			if(!in_array($atts['contains'],$arr))
		return false;
			else 
	{unset($atts['list']);unset($atts['contains']); }		
		}

		if(array_key_exists('list',$atts) && array_key_exists('not_contains',$atts) ){
			if(!is_array($atts['list']))
				$arr= explode( ',' ,$atts['list'] );
			else
				$arr=$atts['list']; 
			if(in_array($atts['not_contains'],$arr))
		return false;
			else 
	{unset($atts['list']);unset($atts['not_contains']); }		
		}

		if(array_key_exists('cond',$atts) && array_key_exists('not_equal',$atts) ){
			if($atts['cond']!=$atts['not_equal'])
		{unset($atts['cond']);unset($atts['not_equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('equal',$atts) ){
			if($atts['cond']==$atts['equal'])
		{unset($atts['cond']);unset($atts['equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('greater_than',$atts) ){
			if($atts['cond']>$atts['greater_than'])
		{unset($atts['cond']);unset($atts['greater_than']); }		
			else 
		return false;
		}
	
		if(array_key_exists('cond',$atts) && array_key_exists('less_than',$atts) ){
			if($atts['cond']<$atts['less_than'])
		{unset($atts['cond']);unset($atts['less_than']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('greater_equal',$atts) ){
			if($atts['cond']>=$atts['greater_equal'])
		{unset($atts['cond']);unset($atts['greater_equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('less_equal',$atts) ){
			if($atts['cond']<=$atts['less_equal'])
		{unset($atts['cond']);unset($atts['less_equal']); }		
			else 
		return false;
		}
	
	
		if(array_key_exists('require_once',$atts)){
	$stack=&self::get_array_ref('require_once_stack');
	if(array_key_exists($atts['require_once'],$stack))
		return false;
	else
	{
		self::set('require_once_stack.' . $atts['require_once'] ,true);
		unset($atts['require_once']);	
	}
		}


		if(array_key_exists('device',$atts)){
	$detect = new Mobile_Detect;
	$device_status=false;
			$arr= explode( ',' ,$atts['device'] );
			if($detect->isMobile() && !$detect->isTablet() && in_array('mobile',$arr) )
		$device_status=true;
	
			if($detect->isTablet() && in_array('tablet',$arr) )
		$device_status=true;
	
			if(!$detect->isMobile() && !$detect->isTablet() && in_array('desktop',$arr) )
		$device_status=true;

	if($device_status==false)
		return false;		
			else 
		unset($atts['device']);	
		}
	
	if(array_key_exists('in_array',$atts) && array_key_exists('contains',$atts) ){
			
			if(!self::in_array_r($atts['contains'],self::get($atts['in_array'])))
		return false;
			else 
	{unset($atts['in_array']);unset($atts['contains']); }		
		}
	
	if(array_key_exists('in_array',$atts) && array_key_exists('not_contains',$atts) ){
			if(self::in_array_r($atts['not_contains'],self::get($atts['in_array'])))
		return false;
			else 
	{unset($atts['in_array']);unset($atts['not_contains']); }		
		}
	
	return true;
}

// post actions	
static function post_actions($actions,$value,&$atts=null){
	$return_value=$value;
	if(!$atts) return $return_value;
	if($actions='all'){
		$return_value=self::modify_output($return_value,$atts);
		$return_value=self::redirect_output($return_value,$atts);
		return $return_value;
	}
	foreach ($actions as $action) {
		switch ($action) {
			case 'modify':
				$return_value=self::modify_output($return_value,$atts);
				break;
			case 'redirect':
				$return_value=self::redirect_output($return_value,$atts);
				break;
		}
	}
	return $return_value;
}

static function modify_output($value,&$atts){
	
		if($atts==null)return $value;

		if(array_key_exists('modify_output',$atts)){
			$arr=self::get($atts['modify_output']);
			$value=self::modify_output($value,$arr);
		}
			
		//run	
		if(array_key_exists('run',$atts)){
			$value = self::parse_shortcode($value);
		}	

		//the_content
		if(array_key_exists('the_content',$atts)){
			
			$value = self::the_content_filter($value);
			$value = do_shortcode($value);
		}		
		
		//self::do_shortcode	
		if(array_key_exists('do_shortcode',$atts)){
			$value= wpautop($value);
			$value= shortcode_unautop($value);
			$value = do_shortcode($value);
		}	
		
		//strtolower
		if(array_key_exists('strtolower',$atts) ){
			$value = strtolower($value);
		}
		if(array_key_exists('lower',$atts) ){
			$value = strtolower($value);
		}
		
		//strtoupper
		if(array_key_exists('upper',$atts) ){
			$value = strtoupper($value);
		}
		
		//trim
		if(array_key_exists('trim',$atts) ){
			$value = trim($value);
		}
		
		//length
		if(array_key_exists('strlen',$atts) ){
			$value = strlen($value);
		}		

		//10 digit number
		if(array_key_exists('ten_digit',$atts) ){
			$value = str_replace(' ','',$value);
			if(strlen($value)>10)
				$value =substr ( $value , -10 ,10);
		}
		
		// json_decode
		if(array_key_exists('json_decode',$atts)){
			$value = json_decode($value,true);
		}

		// json_encode
		if(array_key_exists('json_encode',$atts) && is_array($value) ){
			$value = json_encode($value);
		}
		
		//dump
		if(array_key_exists('dump',$atts) ){
			$value =util::var_dump($value,true);
		}
		
		//stripslashes_deep
		if(array_key_exists('stripslashes_deep',$atts) ){
			$value = stripslashes_deep($value);
		}

		//encrypt
		if(array_key_exists('encrypt',$atts)){
			$value = self::simple_encrypt($value);
		}		
		
		//decrypt
		if(array_key_exists('decrypt',$atts)){
			$value = self::simple_decrypt($value);
		}		
		
		//explode	
		if(array_key_exists('explode',$atts)){
			$value = explode($atts['explode'],$value);
		}

		//format number
		if(array_key_exists('comma_separator',$atts) && $value!='' ){
				$value = number_format($value,0, '.', ',');
		}

		
		//format date
		if(array_key_exists('date_format',$atts) && $value!='' ){
			$format = $atts['date_format'];
			if($format==''){
				$format = 'M d, Y';
			}
			
			if(is_object($value) && get_class($value)==='DateTime'){
				$value = date_format($value,$format);
			}
			else{
				try {
					$new_date = new DateTime($value);
				} 
				catch (Exception $e) {
					$new_date = false;
				} 
				if($new_date===false)
					$value='';
				else
					$value = date_format(new DateTime($value),$format);
			}
		}
		
		//words
		if(array_key_exists('words',$atts) && $value!='' ){
			$value = self::break_words($value, $atts['words']);
		}
		
		//separator
		if(array_key_exists('separator',$atts)){
			if(is_array($value)){
				$value=implode ( $atts['separator'] , $value );
			}
			else
				$value=explode ($atts['separator'] , $value );
			
		}

		//comma
		if(array_key_exists('comma',$atts)){
			if(is_array($value)){
				$value=implode ( ',' , $value );
			}
			else
				$value=explode ( ',' , $value );
		}

		//space
		if(array_key_exists('space',$atts)){
			if(is_array($value)){
				$value=implode ( ' ' , $value );
			}
			else
				$value=explode ( ' ' , $value );
		}
		
		// url_encode
		if(array_key_exists('url_encode',$atts) ){
			$value = urlencode($value);
		}

		// url_decode
		if(array_key_exists('url_decode',$atts) ){
			$value = urldecode($value);
		}	

		//count
		if(array_key_exists('count',$atts)){
			if(is_array($value)){
				$value=count($value);
			}
		}

		//first
		if(array_key_exists('first',$atts)){
			if(is_array($value)){
				reset($value);
				$value= current($value);
			}
		}

		//last
		if(array_key_exists('last',$atts)){
			if(is_array($value)){
				$value=end($value);
				reset($arr);
			}
		}
		
		//shuffle
		if(array_key_exists('shuffle',$atts)){
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
		}
		
			//entities_decode
		if(array_key_exists('entities_decode',$atts)){
			$value = html_entity_decode($value, ENT_QUOTES);
		}

		//entities_encode - htmlentities
		if(array_key_exists('entities_encode',$atts) && is_array($value) ){
			$value = htmlentities($value, ENT_QUOTES, "UTF-8",false);
		}
		
		return $value;
	}	


static function redirect_output($value,&$atts){
		if($atts==null)return $value;

		if(array_key_exists('exit',$atts)){
			exit(util::var_dump($value,true));
		}
		
		if(array_key_exists('console',$atts)){
			echo('<script type="text/spa" spa_activity="core:console_log">Memory Usage ' . util::var_dump($value,true) .'</script>');
		}

		if(array_key_exists('log',$atts)){
			$upload_dir = wp_upload_dir();
			$path= $upload_dir['path'] . '/log.html';
			$fp = fopen($path, 'a');
			fwrite($fp, util::var_dump($value,true));
		}

		if(array_key_exists('no_output',$atts)){
			$value='';	
		}
		
		if(array_key_exists('set',$atts)){
			self::set($atts['set'],$value,null,$atts);
			$value='';	
		}

		if(array_key_exists('merge_with',$atts)){
			if(is_array($value)){
				/*
				foreach ($value as $key => $item) {
					self::set($atts['merge_with'] . '.' . $key,$item,null,$atts);
				}*/
				$merge_with_array=self::get($atts['merge_with']);
				if(!is_array($merge_with_array))$merge_with_array=array();
				$final_array=array_merge($merge_with_array,$value);
				self::set($atts['merge_with'],$final_array,null,$atts);
				$value='';	
			}
		}	
		
	return $value;
	
	
}
/// -------------------------------------------------------------------------------------------------------------------------
//common functions

static function get_post_from_slug($slug,$posttype,&$post,$site_id=null){
		
		if(!is_null($site_id))
			switch_to_blog($site_id); 
		
		$args=array(
			'name' => $slug,
			'post_type' => $posttype,
			'post_status'=>'any'
		);
		$my_posts = get_posts( $args );
		
		if(!is_null($site_id))
			restore_current_blog();
		
		if( $my_posts ){
			$post=$my_posts[0];
			return true;
		}
		else
		return false;
	}

 
//------------------------------------------------------------------------------------------------------------------------------
//Stack implementation

static function push_child($obj_type,$name){
	$call_id=self::get_rand(6);
	$stack_id=$obj_type . ':' .  $name . ':' . $call_id;
	$obj=array();
	
	$obj['obj_type']=$obj_type;
	$obj['name']=$name;
	$obj['obj_id']=$stack_id;

	$stack=&self::get_array_ref('call_stack');
	$stack[$stack_id]=$obj;
	
	self::$stack[$obj_type]=&$stack[$stack_id];	
	return $stack_id;
}

static function pop_child($stack_id){

	$stack=&self::get_array_ref('call_stack');
	$reverse=array_reverse ($stack);
	foreach ($reverse as $key => $value) {
		unset(self::$stack['call_stack'][$key]);
		if(isset($value['obj_type'])){
			unset(self::$stack[$value['obj_type']]);
		}
		if($key==$stack_id)
				break;
	}
	
	reset($stack);	
	foreach ($stack as $key => $value) {
		if(isset($value['obj_type'])){
			self::$stack[$value['obj_type']]=&$stack[$key];	
		}
	}
}
	
static function last_child($obj_type){
	$stack=&self::get_array_ref('call_stack');
	$new_obj=null;
	foreach ($stack as $key => $value) {
		if($stack[$key]['obj_type']==$obj_type)
			$new_obj=$key;
	}
	return $new_obj;	
}

static function push_atts($stack_id,$atts=null){
	if(!$atts) return;
		$stack_ref=&self::get_array_ref('call_stack',$stack_id);
		foreach ($atts as $key => $value) {
				$stack_ref[$key]=$value;
		}
}

static function push_this($stack_id){
	$stack=&aw2_library::get_array_ref();
	if(array_key_exists('this',$stack)){
		$ref=aw2_library::get_array_ref('this');
		$stack_ref=&self::get_array_ref('call_stack',$stack_id);
		foreach ($ref as $key => $value) {
			$stack_ref[$key]=$value;

		}
		unset($stack['this']);	
	}
	
	if(array_key_exists('_args',$stack)){
		$ref=aw2_library::get_array_ref('_args');
		$stack_ref=&self::get_array_ref('call_stack',$stack_id);
		$stack_ref['args']=array();
		foreach ($ref as $key => $value) {
			$stack_ref['args'][$key]=$value;
		}
		unset($stack['_args']);	
	}
	
}




//-------------------------------------------------------------------------------------------------------------------------------
static function get_rand($length=8,$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	return substr( str_shuffle( $chars ), 0, $length );
}

static function set($key,$value,$content=null,$atts=null){
	extract( shortcode_atts( array(
	'overwrite'=>'yes'
	), $atts) );
	if($key==null || $key=='')return;
	$return_value=null;
	if($value===null)$value=trim(self::parse_shortcode($content));
	
	$pieces=explode('.',$key);
	switch ($pieces[0]) {
		case 'session':
			self::set_session($pieces[1],$value,$overwrite);
			array_shift($pieces);
			array_shift($pieces);
			break;
		case 'option':
			self::set_option($pieces[1],$value,$overwrite);
			array_shift($pieces);
			array_shift($pieces);
			break;
		case 'cookie':
			self::set_cookie($pieces[1],$value,$overwrite);
			array_shift($pieces);
			array_shift($pieces);
			break;
			
		default:
			$arr=&self::get_array_ref();
			break;
	}

	while(count($pieces)!=0) {
	$flag=true;
	$key=$pieces[0];
	switch ($key) {
		case 'new':
			if(!is_array($arr))
				$arr=array();
			
			$arr[] = null;
			end($arr);
			$pieces[0]= key($arr); 
			reset($arr);
		break;
		case 'first':
			reset($arr);
			$pieces[0]= key($arr); 
		break;
		case 'last':
			end($arr);
			$pieces[0]= key($arr); 
			reset($arr);
		break;
	}
		
		if(count($pieces)==1){
			
			if(!is_object($arr) && !is_array($arr)){
				$arr=array();
			}
			
			if(is_object($arr)){
				if (property_exists($arr,$pieces[0]) && $overwrite=='no')$flag=false;
				if (property_exists($arr,$pieces[0]) && $arr->$pieces[0]!='' && $arr->$pieces[0]!=null & $overwrite=='empty')$flag=false;
				if($flag)$arr->$pieces[0]=$value;
				array_shift($pieces);
			}	
			if(is_array($arr)){
				if (array_key_exists($pieces[0],$arr) && $overwrite=='no')$flag=false;
				if (array_key_exists($pieces[0], $arr) && $arr[$pieces[0]]!='' && $arr[$pieces[0]]!=null & $overwrite=='empty')$flag=false;
				if($flag)$arr[$pieces[0]]=$value;
				array_shift($pieces);
			}	
		}	
		if(count($pieces)>1){
			if(!is_object($arr) && !is_array($arr)){
				$arr=array();
			}
			if(is_array($arr)){
				
				if (!array_key_exists($pieces[0],$arr)){
					$arr[$pieces[0]]=null;
					
				}
				$arr=&$arr[$pieces[0]];

			}	
			elseif(is_object($arr)){
				if (!property_exists($arr,$pieces[0])){
					$arr->$pieces[0]=null;
					
				}
				
				$arr=&$arr->{$pieces[0]};
			}				
			//$arr=&$arr[$pieces[0]];
			array_shift($pieces);
		}
		}
	
	return;
}	



static function set_cookie($key,$value,$overwrite='yes'){
	$flag=true;
	if (array_key_exists($key, $_COOKIE) && $overwrite=='no')$flag=false;
	if (array_key_exists($key, $_COOKIE) && $_COOKIE[$key]!='' & $_COOKIE[$key]!=null & $overwrite=='empty')$flag=false;	
	
		if($flag){
			$_COOKIE[$key]=$value;
			setcookie($key, $value,time()+60*60*24*30,'/');
			echo('<script type=text/spa spa_activity=core:create_cookie days=30 key="' . $key . '" value="' . $value .  '"></script>');
		}	
}

		
static function set_session($key,$value,$overwrite='yes'){
	$flag=true;
	if (!isset($_SESSION)) return;
	if (array_key_exists($key, $_SESSION) && $overwrite=='no')$flag=false;
	if (array_key_exists($key, $_SESSION) && $_SESSION[$key]!='' & $_SESSION[$key]!=null & $overwrite=='empty')$flag=false;
	if($flag)$_SESSION[$key]=$value;	
	return $_SESSION[$key];
}

static function set_option($key,$value,$overwrite='yes'){
	add_option( $key, $value, '', 'no' );
}


// -------------------------------------------------------------------------------------------------------------------------------
// implementation of get

static function get($main,&$atts=null,$content=null){
	$o=new stdClass();
	$o->main=$main;
	$o->atts=$atts;
	$o->content=$content;
	if(is_array($main))return 'array was passed to get';
	if(is_object($main))return 'object was passed to get';
	
	
	$o->pieces=explode('.',$main);
	$o->value='';
	
	self::get_start($o);

	while(count($o->pieces)>0) {
		if ($o->value=='_error' && $o->pieces['0']!='exists'){
			//$o->value='';
			$o->pieces=array();
		}
		elseif(is_object($o->value)){

			self::resolve_object($o);
		}	
		elseif(is_array($o->value) ){
			self::resolve_array($o);
		}
		elseif(is_string($o->value) || is_bool($o->value) || is_numeric($o->value)){
			$return_value=self::resolve_string($o);
		}
		else{
			$o->value='_error';
			$o->pieces=array();
		}
		
	}
	if($o->value==='_error') 
		$o->value='';

	return $o->value;
}

// Individual get functions

static function get_start($o){
	$key=$o->pieces[0];

	switch ($key) {
		case 'offset':
			if(array_key_exists('posts_per_page',$o->atts))$pagesize=$o->atts['posts_per_page'];
			if(array_key_exists('pagesize',$o->atts) )$pagesize=$o->atts['pagesize'];
			array_shift($o->pieces);
			$o->value=intval(($o->atts['pageno']-1)* $pagesize);	
		break;

		case 'dataset_values':
			array_shift($o->pieces);
			self::dataset_values($o);	
		break;

		
		case 'device':
			array_shift($o->pieces);
			$detect = new Mobile_Detect;
			$o->value='desktop';	
			if($detect->isMobile()) 
				$o->value='mobile';
			if($detect->isTablet()) 
				$o->value='tablet';
			break;
		case 'organic':
			array_shift($o->pieces);
			$o->value=self::get_organic();
			break;	
		case 'stack':
			array_shift($o->pieces);
			$o->value=self::$stack; 
			break;
		case 'trace':
			array_shift($o->pieces);
			$o->value=self::generateCallTrace(); 
			break;
		case 'option':
			array_shift($o->pieces);
			self::get_option($o); 
			break;
		case 'url':
			array_shift($o->pieces);
			self::get_url($o); 
			break;
		case 'realpath':
			array_shift($o->pieces);
			self::get_realpath($o); 
			break;
		case 'ip':
			array_shift($o->pieces);
			$o->value=$_SERVER['REMOTE_ADDR']; 
			break;
		case 'lipsum':
			array_shift($o->pieces);
			self::get_lipsum($o); 
			break;
		case 'now':
			array_shift($o->pieces);
			self::get_now($o); 
			break;
			
		case 'server_variables':
			array_shift($o->pieces);
			$o->value=$_SERVER;
			break;
		case 'wpdb':
			array_shift($o->pieces);
			global $wpdb;
			$o->value=$wpdb;
			break;
		case 'post':
			array_shift($o->pieces);
			global $post;
			$o->value=$post;
			break;
		case 'wp_query':
			array_shift($o->pieces);
			global $wp_query;
			$o->value=$wp_query;
			break;
		case 'token':
			array_shift($o->pieces);
			self::get_token($o); 
			break;
		case 'aw2_secret':
			array_shift($o->pieces);
			self::get_aw2_secret($o); 
			break;
			
		case 'unique_number':
			array_shift($o->pieces);
			self::get_unique_number($o); 
			break;			
		case 'function':
			array_shift($o->pieces);
			self::get_function($o);
			break;
		case 'shortcode':
			array_shift($o->pieces);
			self::get_shortcode($o);
			break;
		case 'ajax':
			array_shift($o->pieces);
			if(self::get_request('ajax')=='true')
				$o->value=true;
			else
				$o->value=false;
			break;
		case 'current_user':
			array_shift($o->pieces);
			$o->value=wp_get_current_user();
			break;
		case 'logged_in':
			array_shift($o->pieces);
			if(is_user_logged_in())
				$o->value=true;
			else
				$o->value=false;
			break;
		case 'cookie':
			array_shift($o->pieces);
			$o->value=$_COOKIE;
		break;
		case 'session':
			array_shift($o->pieces);
			if (session_status() == PHP_SESSION_NONE) {
				$o->pieces=[];
				$o->value='_error';
			}else{
				if(empty($o->pieces))
					$o->value=$_SESSION;
				else{
					if(array_key_exists($o->pieces[0],$_SESSION)) 
						$o->value=$_SESSION[$o->pieces[0]];
					else
						$o->value='_error';
					
					array_shift($o->pieces);
				}
			}
		break;
		case 'request':
			array_shift($o->pieces);
			if(empty($o->pieces))
				$o->value=$_REQUEST;
			else{
				$o->value=self::get_request($o->pieces[0]);
				if($o->value==null)
					$o->value='_error';
				
				array_shift($o->pieces);
			}
		break;
		case 'client':
			array_shift($o->pieces);
			self::get_client($o); 
			break;
		case 'content':
			array_shift($o->pieces);
			$o->value=self::parse_shortcode($o->content);
		break;
		case 'raw':
			array_shift($o->pieces);
			$o->value=$o->content;
		break;
		case 'data':
			array_shift($o->pieces);
			$o->value=self::$stack;
			$o->array_type='data';
			break;
		case 'env':
			array_shift($o->pieces);
			$o->value=self::$stack;
			$o->array_type='data';
			break;
		case 'term_link':
			array_shift($o->pieces);
			$o->value=get_term_link($o->atts['slug'], $o->atts['taxonomy'] );
			unset($o->atts['slug']);
			unset($o->atts['taxonomy']);
			break;
		case 'term_meta':
			array_shift($o->pieces);
			$o->value=get_term_meta($o->atts['term_id'], $o->atts['key'], true);
			unset($o->atts['term_id']);
			unset($o->atts['key']);
			unset($o->atts['single']);
			break;
		case 'menu':
			array_shift($o->pieces);
			$o->value=self::get_menu($o);
			break;
		case 'image_alt':
			array_shift($o->pieces);
			$o->value=self::get_image_alt($o);
			unset($o->atts['post_id']);
			break;
		case 'attachment':
			array_shift($o->pieces);
			$o->value=self::get_attachment_details($o);
			unset($o->atts['attachment_id']);
			break;
		case 'breadcrumb':
			array_shift($o->pieces);
			if(isset($o->atts['seperator']))
				$sep = $o->atts['seperator'];
			else
				$sep = '&raquo;';			
			$o->value = "<div class='breadcrumb'>".self::get_breadcrumb($o->atts['main_menu_slug'], $sep, $o->atts['show_home'])."</div>";
			unset($o->atts['main_menu_slug']);
			unset($o->atts['seperator']);
			break;
		case 'attachment_url':
			array_shift($o->pieces);
			$size=isset($o->atts['size'])?$o->atts['size']:'thumbnail';
			
			$img=wp_get_attachment_image_src( $o->atts['attachment_id'], $size );
			$o->value = $img[0]; 
			unset($o->atts['size']);
			unset($o->atts['attachment_id']);
			break;
		case 'next_post':
			array_shift($o->pieces);				
			$o->value =  self::get_next_post( $o );
			break;
		case 'prev_post':
			array_shift($o->pieces);
					
			$o->value =  self::get_prev_post( $o );
			break;	
		case 'country':			
			array_shift($o->pieces);
			if(!class_exists('iptocountry')){
				$o->value='_error';
				return;
			}
			if(isset($o->atts['ip']))
				$IP=$o->atts['ip'];
			else
				$IP=$_SERVER['REMOTE_ADDR']; 
			
			if(isset($atts['name']) && $atts['name']=='full')
				$o->value=iptocountry::get_full_name($return_value);
			else
				$o->value=iptocountry::get_short_name($IP);
			
			unset($o->atts['ip']);
			unset($o->atts['name']);
			break;		
		case 'nonce':
			array_shift($o->pieces);
			$o->value=wp_create_nonce($o->pieces[0]) . '::' . $o->pieces[0];
			array_shift($o->pieces);
			break;		
		case 'denonce':
			array_shift($o->pieces);
			$a=split('::',$o->pieces[0]);
			if(count($a)==2){
				$returnvalue=wp_verify_nonce( $a[0], $a[1]);
				if($returnvalue==false)
					$o->value='error';
				else
					$o->value=$a[1];
			}
			else{
				$o->value='no';
			}
			
			array_shift($o->pieces);
			break;		
		case 'const':
			array_shift($o->pieces);
			$o->value=$o->pieces[0];
			if($o->value=='empty')
				$o->value='';

			if($o->value=='null')
				$o->value=null;
			
			array_shift($o->pieces);
			break;			
		case 'newdate':
			array_shift($o->pieces);
			self::get_newdate($o);
			break;
		
		case 'sidebar':
			array_shift($o->pieces);
			self::get_sidebar($o);
			break;
		case 'sideload_media':
			array_shift($o->pieces);
			self::get_sideload_media($o);
			break;
			
		case 'device_tokens':
			array_shift($o->pieces);
			self::get_device_tokens($o);
			break;	
			
		case 'taxonomy_term_list':
			array_shift($o->pieces);
			self::get_taxonomy_term_list($o);
			break;
		default:
			$o->value=self::$stack;
			$o->array_type='data';
			break;
	}
}

static function get_device_tokens($o){
	if(empty($o->pieces)&& !isset($o->atts['user_id'])){
		$o->value='_error';
		return;
	}
	
	if(empty($o->pieces)&& isset($o->atts['user_id'])){
		$o->value=awesome_notifications::get_user_device_token($o->atts['user_id']);
		return;
	}
		
	$device_type=$o->pieces[0];
	array_shift($o->pieces);

	$o->value=awesome_notifications::get_all_device_tokens($device_type);

	return;
}

static function dataset_values($o){
	$arr=array();

	$arr['pagesize']=$o->atts['pagesize'];
	
	if(!empty($o->atts['pageno']) && $arr['pagesize']!=-1){
		$arr['pageno']=$o->atts['pageno'];
		$arr['offset']=(string)(($o->atts['pageno']-1)* $o->atts['pagesize']);
	}
	
	if(!empty($o->atts['offset']) && $arr['pagesize']!=-1){
			$arr['offset']=$o->atts['offset'];
			$arr['pageno']=floor ( $o->atts['offset'] / $o->atts['pagesize'] ) + 1 ;
	}

	if(!array_key_exists('offset',$arr))$arr['offset']='0';
	if(!array_key_exists('pageno',$arr))$arr['pageno']=1;
	

	if(! empty($o->atts['found_rows']) && $arr['pagesize']!=-1 ){
		$arr['found_pages']=ceil($o->atts['found_rows'] / $o->atts['pagesize']);
	}
		
	if(empty($o->atts['found_rows'])){
		$arr['found_pages']='0';
	}

	if($arr['pagesize']=='-1' && ! empty($o->atts['found_rows'])){
		$arr['found_pages']='1';
	} 
	
	$o->value=$arr;
}


static function get_organic(){

$organic_sources = array(
		'google'=>'www.google',
		'yahoo.com'=>'yahoo.com/',
		'bing.com' => 'bing.com/'
	);
	
	if(!isset($_SERVER['HTTP_REFERER']))
		return '';
		
	$referrer=$_SERVER['HTTP_REFERER'];
	foreach($organic_sources as $name => $path) {
			if (strpos($referrer, $path) !== false && aw2_library::get_request('utm_source')=='') {
				return $name;
			}
	}
	return '';
}

static function get_taxonomy_term_list($o){
	if(!isset($o->atts['post_id'])){
		$o->value='_error';
		return;
	}
	
	//get all taxonomies attached to the post
	$result=array();
	$all_tax=get_post_taxonomies($o->atts['post_id']);	
	//for each taxonomy get the applied terms
	if(isset($o->atts['fields'])){
		$field=$o->atts['fields'];
	} else {
		$field='all';
	}
	
	foreach($all_tax as $tax){
		
		$result[$tax]= wp_get_post_terms( $o->atts['post_id'], $tax,  array("fields" => $field) );
	}

	$o->value=$result;

	return;
}

static function get_option($o){
	if(empty($o->pieces)){
		$o->value='_error';
	}
	$option=$o->pieces[0];
	array_shift($o->pieces);
	if (isset($o->atts['default']))
		$o->value=get_option( $option,$o->atts['default']);
	else	
		$o->value=get_option( $option);
	
	if($o->value===false)
		$o->value='_error';
	return;
	
}
static function get_url($o) {
	if(empty($o->pieces)){
		 $pageURL = 'http';
		 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 $o->value=$pageURL;
		 return;
	}

	$url=$o->pieces[0];
	array_shift($o->pieces);

	switch ($url) {
		case 'cdn':
			$o->value=self::$cdn;
			break;		
		case 'uploads':
			$o->value=wp_upload_dir()['baseurl'] . '/';
			break;
		case 'site':
			$o->value=site_url() . '/';
			break;
		case 'home':
			$o->value=home_url() . '/';
			break;
		case 'page':
			$pageURL = 'http';
			if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			$o->value=$pageURL . '/';
			break;			
	}
}


static function get_realpath($o) {
	if(empty($o->pieces)){
		$o->value='_error';
		return;
	}			
	
	$url=$o->pieces[0];
	array_shift($o->pieces);
	
	switch ($url) {
		case 'app_folder':
			$folder=dirname(getcwd(), 1) . '/' . self::get('app.slug') . '-docs';
			if (!file_exists($folder)) {
				mkdir($folder, 0777, true);
			}
			$o->value=$folder . '/';
			break;		
		case 'template_folder':
			$folder=dirname(getcwd(), 1) . '/' . 'templates';
			$o->value=$folder . '/';
			break;		
			
		case 'home':
			$o->value=getcwd() . '/';
			break;
	}
}

static function get_lipsum($o) {
	//$amount is� how much of $what you want. 
	//$what is either paras, words, bytes or lists. 
	//$start is whether or not to start the result with Lorem ipsum dolor sit amet�
	extract( shortcode_atts( array(
	'amount'=>30,
	'what'=>'words',
	'start'=>0
	), $o->atts) );
		
	$o->value=simplexml_load_file("http://www.lipsum.com/feed/xml?amount=$amount&what=$what&start=$start")->lipsum;
	return;	
}

static function get_now($o){
	$date_format=isset($o->atts['format'])?$o->atts['format']:'M d, Y';
	unset($o->atts['format']);
	$o->value=date($date_format);
	return ;
}

static function get_function($o){
	if(empty($o->pieces)){
		$o->value='_error';
		return;
	}
	$fname=$o->pieces[0];
	array_shift($o->pieces);
	
	$parameters = array();
	$i=1;
	$found=true;
	while ($found==true) {
		$pname='p' . strval($i);
		if(isset($o->atts[$pname])){
			array_push($parameters,$o->atts[$pname]);
			unset($o->atts[$pname]);
			$i++;
		}
		else{
			$found=false;
		}
	}	
	$o->value=call_user_func_array($fname, $parameters);	
	return;
}

static function get_shortcode($o){
	if(empty($o->pieces)){
		$o->value='_error';
		return;
	}
	$args=self::get_clean_args($o->$content);
	$sc=$o->pieces[0];
	array_shift($o->pieces);


	$buildstring="[" . $sc;
	foreach ($args as $key => $value) {
			$buildstring .=" " . $key ."=" . '"' . $value . '"';
	}
	$buildstring .="]";
	
	$o->value=do_shortcode($buildstring);	
	return ;
}

static function get_token( $o) {
	$length=isset($o->atts['length'])?$o->atts['length']:12;
	$chars=isset($o->atts['chars'])?$o->atts['chars']:'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ;
	$o->value=substr( str_shuffle( $chars ), 0, $length );
	return;
}

static function get_aw2_secret( $o) {
	$length=isset($o->atts['length'])?$o->atts['length']:12;
	$chars=isset($o->atts['chars'])?$o->atts['chars']:'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ;
	$a=array();
	$a['token']='aw2temp_' . substr( str_shuffle( $chars ), 0, $length );
	$a['nonce']=wp_create_nonce($a['token']);
	$a['secret']=$a['token'] . '.' . $a['nonce'];
	$o->value=$a;
	return;
}


static function get_unique_number( $o) {
	$m=microtime(true);
	$s=substr ( $m , 0, 10);
	$t=mt_rand(0, 9999);
	$o->value=$s . $t;	
	return;
}


static function get_client($o){
	if(empty($o->pieces)){
		$o->value='_error';
		return;
	}	
	$content=self::clean_html(self::clean_specialchars($o->content));
	$content=str_replace ( "&#038;" , "&" ,$content );
	$content=str_replace("<script>", "", $content);
	$content=str_replace("</script>", "", $content);
	$content=str_replace("<style>", "", $content);
	$content=str_replace("</style>", "", $content);
	$key=$o->pieces[0];
	$content=$o->content;
	$count=count($o->pieces);
	switch ($key) {
		case 'script':
			array_shift($o->pieces);
			$o->value="<script>" . aw2_library::parse_shortcode($content) . "</script>";
			break;
		case 'ready':
			array_shift($o->pieces);
			$string="<script type='spa/axn'  axn='core.run_script' " . (isset($o->atts['cdnjs']) ? 'data-cdnjs='.$o->atts['cdnjs'] : '')  .  ">" . aw2_library::parse_shortcode($content) . "</script>";
			$ref=&self::get_array_ref('footer_output','ready');
			$ref[]=$string;
			$o->value='';
			if(array_key_exists('cdncss',$o->atts)){
				//$ref=&self::get_array_ref('footer_output','stylesheet');
				$arr= explode( ',' ,$o->atts['cdncss'] );
				foreach ($arr as $value) {
					echo "<link rel='stylesheet' href='" . aw2_library::$cdn . $value . "' type='text/css' media='all' />";				
				}

			}
			
			break;
		case 'minify_less':
			array_shift($o->pieces);
			$string=aw2_library::parse_shortcode($content);
			$less = new lessc;
			$css = $less->compile($string);
			$css=minify_css($css);
/* 			
			$ref=&self::get_array_ref('footer_output','less');
			$ref[]=$string; */
			$o->value=$css;
			break;			
		case 'less':
			array_shift($o->pieces);
			$string=aw2_library::parse_shortcode($content);
			$less = new lessc;
			$css = $less->compile($string);
/* 			
			$ref=&self::get_array_ref('footer_output','less');
			$ref[]=$string; */
			$o->value='<style>' . $css . '</style>';
			break;
		case 'less_combine':
			array_shift($o->pieces);
			$string=aw2_library::parse_shortcode($content);
			
			$ref=&self::get_array_ref('footer_output','less');
			$ref[]=$string;
			$o->value='';
			break;
		case 'style':
			array_shift($o->pieces);
			/* $string="<style>" . aw2_library::parse_shortcode($content) . "</style>";
			$ref=&self::get_array_ref('footer_output','style');
			$ref[]=$string; */
			$string=str_replace('; ',';',str_replace(' }','}',str_replace('{ ','{',str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',aw2_library::parse_shortcode($content))))));
			$o->value="<style>" . $string . "</style>";
			break;
	}
	if($count==count($o->pieces))
		$o->value='_error';
	return;
}


static function get_menu($o){
	$args=self::get_clean_args($o->content);
	//if(isset($o->atts['no_cache'])){
		return wp_nav_menu( $args );
	//}
	
	//return aw2_optimised::cached_nav_menu( $args );
	
}

static function get_image_alt($o){
	if(isset($o->atts['attachment_id'])){
		$attachment_id=$o->atts['attachment_id']; 
	}
	else if(isset($o->atts['post_id'])) {
		$attachment_id=get_post_thumbnail_id( $o->atts['post_id'] ); 
	}
	
	return trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ));;
}

static function get_attachment_details($o){
	if(!isset($o->atts['attachment_id'])){
		return '';
	}
	
	$attachment_id=$o->atts['attachment_id']; 
	
	$return_value=array();
	$return_value['name']=get_the_title($attachment_id);
	$return_value['url']=wp_get_attachment_url($attachment_id);
	$return_value['alt'] = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true);
	$return_value['path']  = get_attached_file( $attachment_id);
	$return_value['meta']  = wp_get_attachment_metadata( $attachment_id);
	
	return $return_value;
}

static function get_adjacent_post( $all=false, $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ) {
	global $wpdb;

	if ( ( ! $post = get_post() ) || ! taxonomy_exists( $taxonomy ) )
		return null;

	$current_post_date = $post->post_date;

	$join = '';
	$where = '';
	$adjacent = $previous ? 'previous' : 'next';

	if ( $in_same_term || ! empty( $excluded_terms ) ) {
		if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) {
			// back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
			if ( false !== strpos( $excluded_terms, ' and ' ) ) {
				_deprecated_argument( __FUNCTION__, '3.3.0', sprintf( __( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
				$excluded_terms = explode( ' and ', $excluded_terms );
			} else {
				$excluded_terms = explode( ',', $excluded_terms );
			}

			$excluded_terms = array_map( 'intval', $excluded_terms );
		}

		if ( $in_same_term ) {
			$join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
			$where .= $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

			if ( ! is_object_in_taxonomy( $post->post_type, $taxonomy ) )
				return '';
			$term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

			// Remove any exclusions from the term array to include.
			$term_array = array_diff( $term_array, (array) $excluded_terms );
			$term_array = array_map( 'intval', $term_array );

			if ( ! $term_array || is_wp_error( $term_array ) )
				return '';

			$where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
		}

		/**
		 * Filters the IDs of terms excluded from adjacent post queries.
		 *
		 * The dynamic portion of the hook name, `$adjacent`, refers to the type
		 * of adjacency, 'next' or 'previous'.
		 *
		 * @since 4.4.0
		 *
		 * @param string $excluded_terms Array of excluded term IDs.
		 */
		$excluded_terms = apply_filters( "get_{$adjacent}_post_excluded_terms", $excluded_terms );

		if ( ! empty( $excluded_terms ) ) {
			$where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', array_map( 'intval', $excluded_terms ) ) . ') )';
		}
	}

	// 'post_status' clause depends on the current user.
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();

		$post_type_object = get_post_type_object( $post->post_type );
		if ( empty( $post_type_object ) ) {
			$post_type_cap    = $post->post_type;
			$read_private_cap = 'read_private_' . $post_type_cap . 's';
		} else {
			$read_private_cap = $post_type_object->cap->read_private_posts;
		}

		/*
		 * Results should include private posts belonging to the current user, or private posts where the
		 * current user has the 'read_private_posts' cap.
		 */
		$private_states = get_post_stati( array( 'private' => true ) );
		$where .= " AND ( p.post_status = 'publish'";
		foreach ( (array) $private_states as $state ) {
			if ( current_user_can( $read_private_cap ) ) {
				$where .= $wpdb->prepare( " OR p.post_status = %s", $state );
			} else {
				$where .= $wpdb->prepare( " OR (p.post_author = %d AND p.post_status = %s)", $user_id, $state );
			}
		}
		if($all){
			$where .= " OR p.post_status = 'pending'";
			$where .= " OR p.post_status = 'draft'";
		}
		$where .= " )";
	} else {
		$where .= " AND p.post_status = 'publish'";
	}

	$op = $previous ? '<' : '>';
	$order = $previous ? 'DESC' : 'ASC';

	/**
	 * Filters the JOIN clause in the SQL for an adjacent post query.
	 *
	 * The dynamic portion of the hook name, `$adjacent`, refers to the type
	 * of adjacency, 'next' or 'previous'.
	 *
	 * @since 2.5.0
	 * @since 4.4.0 Added the `$taxonomy` and `$post` parameters.
	 *
	 * @param string  $join           The JOIN clause in the SQL.
	 * @param bool    $in_same_term   Whether post should be in a same taxonomy term.
	 * @param array   $excluded_terms Array of excluded term IDs.
	 * @param string  $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
	 * @param WP_Post $post           WP_Post object.
	 */
	$join = apply_filters( "get_{$adjacent}_post_join", $join, $in_same_term, $excluded_terms, $taxonomy, $post );

	/**
	 * Filters the WHERE clause in the SQL for an adjacent post query.
	 *
	 * The dynamic portion of the hook name, `$adjacent`, refers to the type
	 * of adjacency, 'next' or 'previous'.
	 *
	 * @since 2.5.0
	 * @since 4.4.0 Added the `$taxonomy` and `$post` parameters.
	 *
	 * @param string $where          The `WHERE` clause in the SQL.
	 * @param bool   $in_same_term   Whether post should be in a same taxonomy term.
	 * @param array  $excluded_terms Array of excluded term IDs.
	 * @param string $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
	 * @param WP_Post $post           WP_Post object.
	 */
	$where = apply_filters( "get_{$adjacent}_post_where", $wpdb->prepare( "WHERE p.post_date $op %s AND p.post_type = %s $where", $current_post_date, $post->post_type ), $in_same_term, $excluded_terms, $taxonomy, $post );

	/**
	 * Filters the ORDER BY clause in the SQL for an adjacent post query.
	 *
	 * The dynamic portion of the hook name, `$adjacent`, refers to the type
	 * of adjacency, 'next' or 'previous'.
	 *
	 * @since 2.5.0
	 * @since 4.4.0 Added the `$post` parameter.
	 *
	 * @param string $order_by The `ORDER BY` clause in the SQL.
	 * @param WP_Post $post    WP_Post object.
	 */
	$sort  = apply_filters( "get_{$adjacent}_post_sort", "ORDER BY p.post_date $order LIMIT 1", $post );

	$query = "SELECT p.ID FROM $wpdb->posts AS p $join $where $sort";
	$query_key = 'adjacent_post_' . md5( $query );
	$result = wp_cache_get( $query_key, 'counts' );
	if ( false !== $result ) {
		if ( $result )
			$result = get_post( $result );
		return $result;
	}

	$result = $wpdb->get_var( $query );
	if ( null === $result )
		$result = '';

	wp_cache_set( $query_key, $result, 'counts' );

	if ( $result )
		$result = get_post( $result );

	return $result;
}
static function get_next_post($o){
	if(!isset($o->atts['post_id'])){
		return '';
	}
	$in_same_cat=false;
	
	if(isset($o->atts['in_same_cat']) && strtolower($o->atts['in_same_cat'])=='true'){
		$in_same_cat=true;
	}
	
	$out="id";
	if(!empty($o->pieces))
		$out=$o->pieces[0];
		array_shift($o->pieces);
	
	//get_{$adjacent}_post_where
	$all=false;
	if(isset($o->atts['take_all_post'])){
	/* 	//add_filter( 'get_previous_post_where', array( $this, 'filter_adjacent' ) );
		echo 'AMIT2 ';
		add_filter( 'get_next_post_where','aw2_library::filter_adjacent',99,1  );
		 */
		 $all=true;
	}
	
	$post_id=$o->atts['post_id']; 
	
	 // Get a global post reference since get_adjacent_post() references it
    global $post,$wpdb;

    // Store the existing post object for later so we don't lose it
    $oldGlobal = $post;

    // Get the post object for the specified post and place it in the global variable
    $post = get_post( $post_id );

    // Get the post object for the previous post
    $next_post = self::get_adjacent_post($all,$in_same_cat,'',false);

    // Reset our global object
    $post = $oldGlobal;

    if ( '' == $next_post ) {
        $next_post_id = 0;
    }

    $return_value = $next_post->ID;
	
	if($out == 'url'){
		$return_value = get_permalink($next_post);
	}
	
	if($out == 'slug'){
		$return_value = $next_post->post_name;
	}
	
	return $return_value;
}

static function get_prev_post($o){
	if(!isset($o->atts['post_id'])){
		return '';
	}
	$in_same_cat=false;
	
	if(isset($o->atts['in_same_cat']) && strtolower($o->atts['in_same_cat'])=='true'){
		$in_same_cat=true;
	}
	
	$out="id";
	if(!empty($o->pieces))
		$out=$o->pieces[0];
		array_shift($o->pieces);
	
	$post_id=$o->atts['post_id']; 
	
	 // Get a global post reference since get_adjacent_post() references it
    global $post;

    // Store the existing post object for later so we don't lose it
    $oldGlobal = $post;

    // Get the post object for the specified post and place it in the global variable
    $post = get_post( $post_id );
	
	$all=false;
	if(isset($o->atts['take_all_post'])){
		 $all=true;
	}
	
    // Get the post object for the previous post
    $prev_post = self::get_adjacent_post($all,$in_same_cat,'',true);

    // Reset our global object
    $post = $oldGlobal;

    if ( '' == $prev_post ) {
        $next_post_id = 0;
    }

    $return_value = $prev_post->ID;
	
	if($out == 'url'){
		$return_value = get_permalink($prev_post);
	}
	
	if($out == 'slug'){
		$return_value = $prev_post->post_name;
	}
	
	return $return_value;
}


static function get_breadcrumb($theme_location = 'main', $separator = ' &raquo; ', $show_home = 'yes') {

    $items = wp_get_nav_menu_items($theme_location);
    _wp_menu_item_classes_by_context( $items ); // Set up the class variables, including current-classes
    $crumbs = array();
	
	if($show_home == 'yes')
		$crumbs[] = '<a href="'.get_option('home').'">Home</a> ';
	
	$i=0;
    foreach($items as $item) {
        if ($item->current === true) {
            $crumbs[] = "$item->title";
        }elseif (($item->current_item_ancestor === true || $item->current_item_parent === true) && $item->current === false){
			$crumbs[] = "<a href=\"{$item->url}\" title=\"{$item->title}\">{$item->title}</a>";
		}
		$i++;
    }
	$separator="<span class='separator'>".$separator."</span>";
	if($i==0){
		
		$crumbstxt='<a href="'.get_option('home').'">Home</a> '.$separator;
		if (is_author())
		{
			
			$crumbstxt.="<a href='".get_author_posts_url( get_the_author_meta( 'ID' ) )."'>".get_the_author_meta('display_name')."</a>";
			
		}else{
			
			if($post->post_parent) {
				$parent_id = $post->post_parent;
				$crumbs = array();
				$e=0;
				
				while ($parent_id) 
				{
					$page = get_page($parent_id);
					$crumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
					$parent_id = $page->post_parent;
					$e++;
				}
				if($e!=0){
					return implode($separator, $crumbs);
				}
			}
			
			if (is_category() || is_single()) 
			{
				$the_cat = get_the_category();
				$d=0;
				$catlinkarr="";
				foreach($the_cat as $k => $v)
				{
					$category_link = get_category_link( $v->cat_ID );
					if($d==0){
						$catlinkarr.= '<a href="'.$category_link.'">'.$v->name.'</a>';
					}else{
						$catlinkarr.= ' & <a href="'.$category_link.'">'.$v->name.'</a>';
					}
					
					
					$d++;
				}
				
				
				$crumbstxt.=$catlinkarr;
				if (is_single()) {
					$crumbstxt.=" ".$separator." ".the_title('', '', false);
				}
			} elseif (is_page()) {
				$crumbstxt.=the_title('', '', false);
			}
			
			
		}
			

			return $crumbstxt;
		
		
	}else{
		return implode($separator, $crumbs);
	}
    
}

static function get_newdate($o){
	
	$format		= 	(isset($o->atts['format'])) ? $o->atts['format'] : "Y-m-d H:i:s";
	$from		=	(isset($o->atts['from'])) ? $o->atts['from'] : "";
	$duration	=	(isset($o->atts['duration'])) ? $o->atts['duration'] : "+1 day";

	$o->value=date($format,strtotime($from . " $duration"));
	
	return;
}

static function get_sidebar($o){

	$sidebar=$o->pieces[0];
	ob_start();
		dynamic_sidebar( $sidebar );
		$output = ob_get_contents();
	ob_end_clean();

	array_shift($o->pieces);
	$o->value=$output;
	return;
}

static function get_sideload_media($o){

	$o->value='_error';
	if(!isset($o->atts['url']))
		return;
	if(!isset($o->atts['post_id']))
		return;
	
	$url		= 	$o->atts['url'];
	$post_id	= $o->atts['post_id'];
	$return	=	(isset($o->atts['return'])) ? $o->atts['return'] : "src";
	
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	
	$output = media_sideload_image($url, $post_id,'',$return);
	
	$o->value=$output;
	return;
}

static function resolve_array($o){
	
	//am i special or ordinary
	if(array_key_exists('instance',$o->value)){
	}	

	
	if(property_exists($o,'array_type')){
		$array_type=$o->array_type;
		unset($o->array_type);
	}
	else
	$array_type='';	
	switch ($array_type) {
		case 'data':
			self::resolve_data($o);
			break;
		default:
			$count=count($o->pieces);
			self::resolve_array_basic($o);
			if($count==count($o->pieces))
				$o->value='_error';
			break;
	}
}

static function resolve_data($o){
	if(empty($o->pieces)){
		return;
	}
	$count=count($o->pieces);
	$data=self::get_array_ref();
	switch ($o->pieces[0]) {
		default:
			self::resolve_array_basic($o);
	}
	if($count==count($o->pieces))
		$o->value='_error';
	
	return;
}

static function resolve_array_basic($o){
	if(empty($o->pieces)){
		return;
	}
	$arr=$o->value;
	$key=$o->pieces[0];

	if (array_key_exists($key,$arr)){
		array_shift($o->pieces);
		$o->value= $arr[$key];
		return;
	}

	switch ($key) {
		case 'exists':
			array_shift($o->pieces);
			$o->value=true;
			break;
		case 'count':
			array_shift($o->pieces);
			$o->value=count($arr);
			break;
		case 'dump':
			array_shift($o->pieces);
			$o->value=util::var_dump($arr,true);
			break;
		case 'echo':
			array_shift($o->pieces);
			util::var_dump($arr);
			$o->value='';
			break;			
		case 'empty':
			array_shift($o->pieces);
			if(count($arr)==0)
				$o->value=true;
			else
				$o->value=false;
			break;
		case 'not_empty':
			array_shift($o->pieces);
			if(count($arr)!=0)
				$o->value=true;
			else
				$o->value=false;
			break;
		case 'first':
			array_shift($o->pieces);
			reset($arr);
			$o->value= current($arr);
			break;
		case 'last':
			array_shift($o->pieces);
			$o->value=end($arr);
			reset($arr);
			break;
		case 'json_encode':
			array_shift($o->pieces);
			$o->value=json_encode($arr);
			break;
		case 'comma':
			array_shift($o->pieces);
			$o->value=implode ( ',' , $arr );
			break;
		case 'quote_comma':
			array_shift($o->pieces);
			if(count($arr)<1)
				$o->value='';
			
			if(count($arr)==1)
				$o->value="'" . $arr[0] . "'";
				
			if(count($arr)>1)
				$o->value="'" . implode ( "','" , $arr ) . "'";
			break;

		case 'space':
			array_shift($o->pieces);
			$o->value=implode ( ' ' , $arr );
			break;
		case 'stripslashes_deep':
			array_shift($o->pieces);
			$o->value = stripslashes_deep($arr);
			break;
	}

	return;	
	
}

static function resolve_string($o){
	if(empty($o->pieces)){
		return;
	}

	$string=$o->value;
	$count=count($o->pieces);
	
	switch ($o->pieces[0]) {
		case 'exists':
			if ($o->value=='_error')
				$o->value = false;
			else
				$o->value = true;
			array_shift($o->pieces);
			break;		
		case 'the_content':
			array_shift($o->pieces);
			//$string = apply_filters('the_content', $string);

			$string= self::the_content_filter($string);
			$o->value = do_shortcode($string);
			break;
		case 'esc_sql':
			array_shift($o->pieces);
			$o->value=esc_sql($string);
			break;			
		case 'encrypt':
			array_shift($o->pieces);
			$o->value=self::simple_encrypt($string);
			break;
		case 'decrypt':
			array_shift($o->pieces);
			$o->value=self::simple_decrypt($string);
			break;
		case 'json_decode':
			array_shift($o->pieces);
			$o->value=json_decode($string, true);
			break;
		case 'comma':
			array_shift($o->pieces);
			$o->value=explode(',', trim($string));
			$o->value=array_map('trim',$o->value);
			break;
		case 'dot':
			array_shift($o->pieces);
			$o->value=explode('.', trim($string));
			$o->value=array_map('trim',$o->value);
			break;			
		case 'run':
			array_shift($o->pieces);
			$o->value=self::parse_shortcode($string);
			break;
		case 'do_shortcode':
			array_shift($o->pieces);
			$string=wpautop($string);
			$string=shortcode_unautop($string);
			$o->value= do_shortcode($string);
			break;
		case 'lower':
			array_shift($o->pieces);
			$o->value=strtolower($string)	;	
			break;
		case 'upper':
			array_shift($o->pieces);
			$o->value=strtoupper($string)	;	
			break;
		case 'length':
			array_shift($o->pieces);
			$o->value=strlen($string)	;	
			break;
		case 'space':
			array_shift($o->pieces);
			$o->value=explode(' ', trim($string));
			$o->value=array_map('trim',$o->value);
			break;
		case 'trim':
			array_shift($o->pieces);
			$o->value=trim($string);
			break;
		case 'strip_tags':
			array_shift($o->pieces);
			$o->value=strip_tags($string);
			break;			
		case 'math':
			array_shift($o->pieces);
			$pattern = '/([^-\d.\(\)\+\*\/ \^%])/';
			$replacement = '';
			$result= preg_replace($pattern, $replacement, $string);
			$o->value=eval('return ' . $result .  ' ;');
			break;
		case 'dump':
			array_shift($o->pieces);
			$o->value=util::var_dump($string,true);
			break;
		case 'echo':
			array_shift($o->pieces);
			$o->value='';
			util::var_dump($string);
			break;		
	}
	if($count==count($o->pieces))
		$o->value='_error';
	return;	

}	

static function resolve_object($o){
	//am i special or ordinary
	$type=get_class($o->value);
	switch ($type) {
		case 'WP_Post':
			self::resolve_post($o);
			break;
		case 'WP_User':
			self::resolve_user($o);
			break;
		default:
			$data=$o->value;
			$count=count($o->pieces);
			self::resolve_object_basic($o);
			if($count==count($o->pieces))
				$o->value='_error';
			break;
	}
}

static function resolve_object_basic($o){
	if(empty($o->pieces)){
		return;
	}
	
	$obj=$o->value;
	$key=$o->pieces[0];
	
	if (property_exists($obj,$key)){
		array_shift($o->pieces);
		$o->value= $obj->$key;
		return;
	}

	switch ($key) {
		case 'exists':
			array_shift($o->pieces);
			$o->value=true;
			break;
		case 'count':
			array_shift($o->pieces);
			$o->value=count($obj);
			break;
		case 'dump':
			array_shift($o->pieces);
			$o->value=util::var_dump($obj,true);
			return ;
			break;
		case 'echo':
			array_shift($o->pieces);
			$o->value='';
			util::var_dump($obj);
			return ;
			break;			
	}
	return;
}

static function resolve_post($o){
	
	if(empty($o->pieces)){
		return;
	}
	
	$post=$o->value;
	$ID=$post->ID;
	$count=count($o->pieces);
	switch ($o->pieces[0]) {
		case 'meta':
			array_shift($o->pieces);
			$o->value=get_post_meta($ID, $o->pieces[0], 'single');
			array_shift($o->pieces);
			break;
		case 'author_meta':
			array_shift($o->pieces);
			$o->value=get_the_author_meta($o->pieces[0], $post->post_author );
			array_shift($o->pieces);
			break;
		case 'url':
			array_shift($o->pieces);
			$o->value=get_permalink($ID);
			break;
		case 'featured_image':
			array_shift($o->pieces);
			$size=isset($o->atts['size'])?$o->atts['size']:'thumbnail';
			$class=isset($o->atts['class'])?$o->atts['class']:'';
			$o->value=get_the_post_thumbnail($ID,$size,array( 'class' =>$class ) );
			break;
		case 'featured_image_url':
			array_shift($o->pieces);
			$size=isset($o->atts['size'])?$o->atts['size']:'thumbnail';
			$img=wp_get_attachment_image_src( get_post_thumbnail_id( $ID ), $size );
			$o->value= $img[0]; 
			break;
		case 'excerpt':
			array_shift($o->pieces);
			$length=isset($o->atts['length'])?$o->atts['length']:20;
			$ellipsis=isset($o->atts['ellipsis'])?' &hellip; &nbsp;':'';
			$o->value= self::pippin_excerpt_by_id($ID,$length,'<a><em><strong>',$ellipsis);
			break;
		case 'the_content':
			array_shift($o->pieces);
			$content = $post->post_content;
			//$content = apply_filters('the_content', $content);
			$content= self::the_content_filter($content);
			
			$o->value= do_shortcode($content);
			break;
		case 'parse_content':
			array_shift($o->pieces);
			$content = $post->post_content;
			$content= self::the_content_filter($content);
			
			$o->value= self::parse_shortcode($content);
			break;
		case 'taxonomy':
			array_shift($o->pieces);
			$fields=$o->pieces[0];
			array_shift($o->pieces);
			
			if(isset($o->atts['orderby']))
				$o->value= wp_get_post_terms($ID, $o->pieces[0], array("orderby"=>$o->atts['orderby'],"fields" => $fields));
			else
				$o->value= wp_get_post_terms($ID, $o->pieces[0], array("fields" => $fields));
			array_shift($o->pieces);
			break;
		default:
			self::resolve_object_basic($o);
			break;
	}
	if($count==count($o->pieces))
		$o->value='_error';
	
	return;
	
}

static function resolve_user($o){
	if(empty($o->pieces)){
		return;
	}
	$count=count($o->pieces);
	self::resolve_object_basic($o);
	
	if($count!=count($o->pieces))
		return;
	
	$user=$o->value;
	$field=$o->pieces[0];
	array_shift($o->pieces);
	$o->value=$user->$field;
	return;
}

static function generateCallTrace(){
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();
  
    for ($i = 0; $i < $length; $i++)
    {
        $result[] = '<li>' . substr($trace[$i], strpos($trace[$i], ' ')) . '</li>'; // replace '#someNum' with '$i)', set the right ordering
    }
	
    return "<div style='background-color:#f7f7f9'><ol>" . implode("<br>", $result) . '</ol></div>';
}
	

static  function simple_encrypt($text){
	/*
    return urlencode(trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, 'qwertyuiopasdfgh', $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))));
	*/

	$pass = 'qwertyuiopasdfgh';
	$method = 'aes128';

	$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ;
	$iv=substr( str_shuffle( $chars ), 0, 16 );
	
	$cipher=urlencode(openssl_encrypt ($text, $method, $pass,0, $iv) . ':::' . $iv)	;
	return $cipher;
}
static function simple_decrypt($text){
	/*
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, 'qwertyuiopasdfgh', base64_decode(urldecode($text)), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	*/	
	$text=urldecode($text);
	$pieces=explode(':::',$text);
	if(count($pieces)!=2)return '';	
	$pass = 'qwertyuiopasdfgh';
	$method = 'aes128';	
	return openssl_decrypt ($pieces[0], $method, $pass,0,$pieces[1])	;
}


static function d(){
		util::var_dump(self::$stack);
}

static function get_request($main=null){
	$value=null;
	if(empty($main))
		return $_REQUEST;
	if($main=='request_body'){
			$value = file_get_contents('php://input');
			return $value;
	}

	if($main=='post_json'){
			$value = json_encode($_POST);
			return $value;			
	}
	
	
	if(get_query_var($main)){
		$value=get_query_var($main);
	}
	else{
		if(isset($_REQUEST[$main])){
			$value=$_REQUEST[$main];
		}
	}
	return $value;	
}

static function pippin_excerpt_by_id($post, $length = 20, $tags = '<a><em><strong>', $extra = '') {
	/*
	 * Gets the excerpt of a specific post ID or object
	 * @param - $post - object/int - the ID or object of the post to get the excerpt of
	 * @param - $length - int - the length of the excerpt in words
	 * @param - $tags - string - the allowed HTML tags. These will not be stripped out
	 * @param - $extra - string - text to append to the end of the excerpt
	 */

	if (is_int($post)) {
		// get the post object of the passed ID
		$post = get_post($post);
	} elseif (!is_object($post)) {
		return false;
	}

	if (has_excerpt($post->ID)) {
		$the_excerpt = $post->post_excerpt;
		return apply_filters('the_content', $the_excerpt);
	} else {
		$the_excerpt = $post->post_content;
	}

	$the_excerpt = strip_shortcodes(strip_tags($the_excerpt), $tags);
	$the_excerpt = preg_split('/\b/', $the_excerpt, $length * 2 + 1);
	$excerpt_waste = array_pop($the_excerpt);
	$the_excerpt = implode($the_excerpt);
	$the_excerpt .= $extra;

	return apply_filters('the_content', $the_excerpt);
}

static function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}


static function removesmartquotes($content) {
     $content = str_replace('&#8220;', ",", $content);
     $content = str_replace('&#8221;', "'", $content);
     $content = str_replace('&#8216;', '"', $content);
     $content = str_replace('&#8217;', '"', $content);
     
     return $content;
}


/* Code Added by Ani - Start*/

	
	static function sideload_file($url, $post_id){
		if ( !$url || !$post_id ) return new WP_Error('missing', "Need a valid URL and post ID...");
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        // Download file to temp location, returns full server path to temp file, ex; /home/user/public_html/mysite/wp-content/26192277_640.tmp
        $tmp = download_url( $url );
     
        // If error storing temporarily, unlink
        if ( is_wp_error( $tmp ) ) {
            @unlink($file_array['tmp_name']);   // clean up
            $file_array['tmp_name'] = '';
            return $tmp; // output wp_error
        }
     
        preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);    // fix file filename for query strings
        $url_filename = basename($matches[0]);                                                  // extract filename from url for title
        $url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)
     
        // assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
        $file_array['tmp_name'] = $tmp;                                                         // full server path to temp file
 
        $file_array['name'] = $url_filename;
     
        // required libraries for media_handle_sideload
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
     
        // do the validation and storage stuff
        $att_id = media_handle_sideload( $file_array, $post_id, null );
     
        // If error storing permanently, unlink
        if ( is_wp_error($att_id) ) {
            @unlink($file_array['tmp_name']);   // clean up
            return $att_id; // output wp_error
        }
          
        return $att_id;
	}
	
/* Code Added by Ani - End*/
	static function get_parameters($atts){
		$parameters = array();
		$i=1;
		$found=true;
		while ($found==true) {
			$pname='p' . strval($i);
			if(isset($atts[$pname])){
				array_push($parameters,$atts[$pname]);
				$i++;
			}
			else{
				$found=false;
			}
		}	
		return $parameters;
	}	
// ----------------------------------------------------------------------------------------------------------------------

	static function the_content_filter($content){
		global $wp_embed;
			
		$content = wptexturize($content);
		$content = convert_smilies($content);
		$content = wpautop($content);
		$content = shortcode_unautop($content);
		$content = prepend_attachment($content);
		$content = wp_make_content_images_responsive($content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$content = $wp_embed->autoembed( $content );
		
		return $content;
	}

static function get_collection($collection){
	global $wpdb;
	
	if(isset($collection['post_type'])){
		$hash=$collection['post_type'];
		$return_value=null;
		//check cache
		if(!current_user_can('develop_for_awesomeui')){
			$return_value=aw2_global_cache_get(["main"=>$hash,"prefix"=>"collection"],null,null);
		}
		if(!$return_value){
			global $wpdb;
			$sql="select post_content,post_type,ID,post_name,post_title from  ".$wpdb->posts."  where post_status='publish' and post_type='" . $collection['post_type'] . "'";
			$results = $wpdb->get_results($sql,'ARRAY_A');	

			$posts=array();
			
			foreach ($results as $result) {
				$post=array();
				$post['module']=$result['post_name'];
				$post['title']=$result['post_title'];
				$post['id']=$result['ID'];
				$post['code']=$result['post_content'];
				$post['collection']=$result['post_type'];
				$post['hash']=$post['collection'] . '_' . $post['module'];		
				$posts[$post['module']]=$post;
			}
			aw2_global_cache_set(["key"=>$hash,"prefix"=>"collection"],json_encode($posts),null);
		}
		else{
			$posts=json_decode($return_value,true);
		}
		return $posts;
	}
}

	
static function get_module($collection,$module){
	global $wpdb;
	
	if(isset($collection['post_type'])){
		$hash=$collection['post_type'] . '_' . $module;
		$return_value=null;
		//check cache
		if(!current_user_can('develop_for_awesomeui')){
			$return_value=aw2_global_cache_get(["main"=>$hash,"prefix"=>"module"],null,null);
		}
		
		if(!$return_value){
			global $wpdb;
			$sql="select post_content,post_type,ID,post_name,post_title from  ".$wpdb->posts."  where post_type='" . $collection['post_type'] . "' and post_name='" . $module . "'";
			$results = $wpdb->get_results($sql,'ARRAY_A');	
			if(count($results)!==1)return null;
			$arr=array();
			$arr['module']=$results[0]['post_name'];
			$arr['title']=$results[0]['post_title'];
			$arr['id']=$results[0]['ID'];
			$arr['code']=$results[0]['post_content'];
			$arr['post_type']=$results[0]['post_type'];
			
			$arr['collection']=$collection;
			$arr['hash']=$hash;		
			aw2_global_cache_set(["key"=>$hash,"prefix"=>"module"],json_encode($arr),null);
		}
		else{
			$arr=json_decode($return_value,true);
		}
		
		return $arr;
	}

	if(isset($collection['app'])){
		$post_type=self::$stack['apps'][$collection['app']]['collection']['modules']['post_type'];
		
		$hash=$post_type . '_' . $module;
		$return_value=null;
		//check cache
		if(!current_user_can('develop_for_awesomeui')){
			$return_value=aw2_global_cache_get(["main"=>$hash,"prefix"=>"module"],null,null);
		}
		
		if(!$return_value){
			global $wpdb;
			$sql="select post_content,post_type,ID,post_name,post_title from  ".$wpdb->posts."  where post_type='" . $post_type . "' and post_name='" . $module . "'";
			$results = $wpdb->get_results($sql,'ARRAY_A');	
			if(count($results)!==1)return null;
			$arr=array();
			$arr['module']=$results[0]['post_name'];
			$arr['title']=$results[0]['post_title'];
			$arr['id']=$results[0]['ID'];
			$arr['code']=$results[0]['post_content'];
			$arr['post_type']=$results[0]['post_type'];
			
			$arr['collection']=$collection;
			$arr['hash']=$hash;		
			aw2_global_cache_set(["key"=>$hash,"prefix"=>"module"],json_encode($arr),null);
		}
		else{
			$arr=json_decode($return_value,true);
		}
		
		return $arr;
	}

	
	if(isset($collection['shared_app'])){
		$hash=$collection['shared_app'] . '_' . $module;
		$return_value=null;
		//check cache
		if(!current_user_can('develop_for_awesomeui')){
			$return_value=aw2_global_cache_get(["main"=>$hash,"prefix"=>"module"],null,null);
		}
		
		if(!$return_value){
			global $wpdb;
			$path=self::$stack['settings']['shared_app_path'] . $collection['shared_app'] . '/modules/' . $module . '.html';
			$code = file_get_contents($path);
			
			$arr=array();
			$arr['module']=$module;
			$arr['title']=$module;
			$arr['id']=$module;
			$arr['code']=$code;
			$arr['shared_app']=$collection['shared_app'];
			
			$arr['collection']=$collection;
			$arr['hash']=$hash;		
			aw2_global_cache_set(["key"=>$hash,"prefix"=>"module"],json_encode($arr),null);
		}
		else{
			$arr=json_decode($return_value,true);
		}
		
		return $arr;
	}
	
}


static function get_post_meta($post_id,$meta_key=null){
	global $wpdb;
	
	$hash='meta' . '_' . $post_id;
	
	$return_value=null;
	//check cache
	if(!current_user_can('develop_for_awesomeui')){
		$return_value=aw2_global_cache_get(["main"=>$hash,"prefix"=>""],null,null);
	}
	
	if(!$return_value){
		global $wpdb;
		$sql="select post_id,meta_key,meta_value from  ".$wpdb->postmeta."  where post_id='" . $post_id . "'";
		$results = $wpdb->get_results($sql,'ARRAY_A');	

		$metas=array();
		
		foreach ($results as $result) {
			$metas[$result['meta_key']]=$result['meta_value'];
		}
		aw2_global_cache_set(["key"=>$hash,"prefix"=>""],json_encode($metas),null);
	}
	else{
		$metas=json_decode($return_value,true);
	}
	
	if($meta_key){
		if(isset($metas[$meta_key]))
			return $metas[$meta_key];
		else
			return '';
	}
	else
		return $metas;
}

static function module_push($arr){
	$stack_id='module:' .  $arr['hash'] . ':' . aw2_library::get_rand(6);

	$module=array();
	$module['obj_type']='module';
	$module['obj_id']=$stack_id;
	$module['slug']=$arr['module'];
	$module['hash']=$arr['hash'];
	$module['title']=$arr['title'];
	$module['collection']=$arr['collection'];

	
	self::$stack['call_stack'][$stack_id]=$module;
	self::$stack['module']=&self::$stack['call_stack'][$stack_id];	
	return $stack_id;
}
	
static function module_forced_run($collection,$module,$template,$content,$atts){
	$arr=self::get_module($collection,$module);
	if(!$arr)return 'Module not found in Collection';
	$stack_id=self::module_push($arr);
	if($content){
		$content=self::removesmartquotes($content);	
		self::parse_shortcode($content);
	}
	self::push_this($stack_id);
	self::push_atts($stack_id,$atts);
	$return_value=self::parse_shortcode($arr['code']);
	if(isset(self::$stack['module']['templates']['main']) && !$template){
		$template='main';
	}
	if($template)$return_value=self::template_run($template);
	
	if(isset(self::$stack['module']['_return'])){
		unset(self::$stack['_return']);
		$return_value=self::$stack['module']['_return'];
	}
	aw2_library::pop_child($stack_id);
	return $return_value;	
}


static function module_run($collection,$module,$template=null,$content=null,$atts=null){
	

	$arr=self::get_module($collection,$module);

	if(!$arr)return 'Module not found in Collection';
	$stack_id=self::module_push($arr);

	if(!$template){
		if($content){
			$content=self::removesmartquotes($content);	
			self::parse_shortcode($content);
		}
		self::push_this($stack_id);
		self::push_atts($stack_id,$atts);
	}
	
	$return_value=self::parse_shortcode($arr['code']);

	if(isset(self::$stack['module']['templates']['main']) && !$template){
		$return_value=self::template_run('main');
	}
	
	if($template)$return_value=self::template_run($template,$content,$atts);
	
	if(isset(self::$stack['module']['_return'])){
		unset(self::$stack['_return']);
		$return_value=self::$stack['module']['_return'];
	}
	aw2_library::pop_child($stack_id);
	return $return_value;	
}


static function template_run($template,$content=null,$atts=array()){
	$content=self::removesmartquotes($content);		
	if(!isset(self::$stack['module']['templates'][$template]))return 'Template not found';
	$template_ptr=self::$stack['module']['templates'][$template];
	$stack_id=self::push_child('template',$template_ptr['name']);
	
	if($content)self::parse_shortcode($content);
	self::push_this($stack_id);
	self::push_atts($stack_id,$atts);
	
	$return_value=self::parse_shortcode($template_ptr['code']);
	if(isset(self::$stack['template']['_return'])){
		unset(self::$stack['_return']);
		$return_value=self::$stack['template']['_return'];
	}
	aw2_library::pop_child($stack_id);
	return $return_value;	
}

static function module_include($collection,$module){
	$arr=self::get_module($collection,$module);
	if(!$arr)return 'Module not found in Collection';
	$return_value=self::parse_shortcode($arr['code']);	
	return $return_value;	
}

static function module_include_raw($collection,$module){
	$arr=self::get_module($collection,$module);
	if(!$arr)return 'Module not found in Collection';
	$return_value=$arr['code'];	
	return $return_value;	
}
	
}
