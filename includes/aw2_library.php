<?php
use DebugBar\StandardDebugBar;
use aw2\wp\aw2wp_get;

define('AW2_ERROR','_error');
define('AW2_APOS',"'");

class aw2_template{
	public $code=AW2_ERROR;
	public $name=AW2_ERROR;
	
}	

class aw2_error{
	public $status='error';
	public $message='';
	public $error_code='';
}
 

class aw2_library{

static $conn=null;
static $stack=array();
static $redis_conn=null;
static $mysqli=null;


static function load_handlers_from_path($handlers_path,...$paths){
	//php8OK
	
	foreach ($paths as $path) {
        
		if(!is_dir($handlers_path . "/".$path))
			continue;
		
		$files = glob($handlers_path . "/".$path."/*.php");
		foreach ($files as $filename){
			require_once $filename;
		}
    }
	
}

//this function is used to load all the handlers, this will load communication as well as all others like google
static function load_all_extra_handlers(){
	//php8OK
	
	$handler_path = AWESOME_PATH.'/extra-handlers';
        
	if(!is_dir($handler_path ))
		return;
	
	$folders = glob($handler_path . "/*",GLOB_ONLYDIR);
		
	foreach ($folders as $folder){
		$files = glob($folder."/*.php");
		foreach ($files as $filename){
			require_once $filename;
		}
	}
}

private static $hasArray = false;

static function esc_sql($value){
	if(!self::$mysqli)self::$mysqli = self::new_mysqli();
	return self::$mysqli->real_escape_string($value);
}

static function sc_exec_setup_pos(){
	$sc_exec=&self::get_array_ref('@sc_exec');
	$temp=$sc_exec;	
	$sc_exec['start_pos']=$sc_exec['content_pos'];
	return $temp;
}

static function sc_exec_restore($restore){
	$sc_exec=&self::get_array_ref('@sc_exec');
	$sc_exec=$restore;
}


static function get_results($sql){
	//php8OK
	
	if(!self::$mysqli)self::$mysqli = self::new_mysqli();
	$obj = self::$mysqli->query($sql);
	$results = $obj->fetchAll("assoc");				
	return $results;
}
static function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
	//php8OK
	
	$atts = (array)$atts;
	$out = array();
	foreach ($pairs as $name => $default) {
		if ( array_key_exists($name, $atts) )
			$out[$name] = $atts[$name];
		else
			$out[$name] = $default;
	}
	/**
	 * Filter a shortcode's default attributes.
	 *
	 * If the third parameter of the shortcode_atts() function is present then this filter is available.
	 * The third parameter, $shortcode, is the name of the shortcode.
	 *
	 * @since 3.6.0
	 * @since 4.4.0 Added the `$shortcode` parameter.
	 *
	 * @param array  $out       The output array of shortcode attributes.
	 * @param array  $pairs     The supported attributes and their defaults.
	 * @param array  $atts      The user defined shortcode attributes.
	 * @param string $shortcode The shortcode name.
	 */

	return $out;
}

static function dump_debug($arr=array(),$title='')
{
	//php8OK
	
	$html = '<pre style="margin-bottom: 18px;' .
			'background: #f7f7f9;' .
			'border: 1px solid #e1e1e8;' .
			'padding: 8px;' .
			'border-radius: 4px;' .
			'-moz-border-radius: 4px;' .
			'-webkit-border radius: 4px;' .
			'display: block;' .
			'font-size: 12.05px;' .
			'white-space: pre-wrap;' .
			'word-wrap: break-word;' .
			'color: #333;' .
			'font-family: Menlo,Monaco,Consolas,\'Courier New\',monospace;"><fieldset style="border: 1px solid green;padding: 5px;">';
				
	// Add Title
	if($title)$html .= "<legend>$title</legend>";
		
	//Add items
	
	foreach ($arr as $item) {
		if($item['type']==='html')
			$html .= '<h6 style="color:red;padding-top: 5px;">' . $item['value'] .'</h6>';
		else{
			$done  = array();
			$html .= self::recursiveVarDumpHelper($item['value'], 0, 0, $done);
		}		
			
	}
	$html .= '</fieldset></pre>';
	return $html;
		
}

static function var_dump($var, $return = false, $expandLevel = 1,$label='')
{
	//php8OK

		$html = '<pre style="margin-bottom: 18px;' .
				'background: #f7f7f9;' .
				'border: 1px solid #e1e1e8;' .
				'padding: 8px;' .
				'border-radius: 4px;' .
				'-moz-border-radius: 4px;' .
				'-webkit-border radius: 4px;' .
				'display: block;' .
				'font-size: 12.05px;' .
				'white-space: pre-wrap;' .
				'word-wrap: break-word;' .
				'color: #333;' .
				'font-family: Menlo,Monaco,Consolas,\'Courier New\',monospace;">';
		$done  = array();

		$html .= "<h6>$label</h6>";
		
		$html .= self::recursiveVarDumpHelper($var, intval($expandLevel), 0, $done);
		$html .= '</pre>';

		if (!$return) {
				echo $html;
		}

		return $html;
}

static function recursiveVarDumpHelper($var, $expLevel, $depth = 0, $done = array())
{
	//php8OK

		$html = '';

		if ($expLevel > 0) {
				$expLevel--;
				$setImg = 0;
				$setStyle = 'display:inline;';
		} elseif ($expLevel === 0) {
				$setImg = 1;
				$setStyle='display:none;';
		} elseif ($expLevel < 0) {
				$setImg = 0;
				$setStyle = 'display:inline;';
		}

		if (is_bool($var)) {
				$html .= '<span style="color:#588bff;">bool</span><span style="color:#999;">(</span><strong>' . (($var) ? 'true' : 'false') . '</strong><span style="color:#999;">)</span>';
		} elseif (is_int($var)) {
				$html .= '<span style="color:#588bff;">int</span><span style="color:#999;">(</span><strong>' . $var . '</strong><span style="color:#999;">)</span>';
		} elseif (is_float($var)) {
				$html .= '<span style="color:#588bff;">float</span><span style="color:#999;">(</span><strong>' . $var . '</strong><span style="color:#999;">)</span>';
		} elseif (is_string($var)) {
				$html .= '<span style="color:#588bff;">string</span><span style="color:#999;">(</span>' . strlen($var) . '<span style="color:#999;">)</span> <strong>"' . self::htmlentities($var) . '"</strong>';
		} elseif (is_null($var)) {
				$html .= '<strong>NULL</strong>';
		} elseif (is_resource($var)) {
				$html .= '<span style="color:#588bff;">resource</span>("' . get_resource_type($var) . '") <strong>"' . $var . '"</strong>';
		} elseif (is_array($var)) {
				// Check for recursion
				if ($depth > 0) {
						foreach ($done as $prev) {
								if ($prev === $var) {
										$html .= '<span style="color:#588bff;">array</span>(' . count($var) . ') *RECURSION DETECTED*';
										return $html;
								}
						}

						// Keep track of variables we have already processed to detect recursion
						$done[] = &$var;
				}

				self::$hasArray = true;
				$uuid = 'include-php-' . uniqid() . mt_rand(1, 1000000);

				$html .= (!empty($var) ? ' <img class=util-array data-state=' . $setImg . ' id="' . $uuid . '" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D" />' : '') . '<span style="color:#588bff;">array</span>(' . count($var) . ')';
				
				
				if (!empty($var)) {
						$html .= ' <span id="' . $uuid . '-collapsable" style="'.$setStyle.'"><br />[<br />';

						$indent = 4;
						$longest_key = 0;

						foreach ($var as $key => $value) {
								if (is_string($key)) {
										$longest_key = max($longest_key, strlen($key) + 2);
								} else {
										$longest_key = max($longest_key, strlen($key));
								}
						}

						foreach ($var as $key => $value) {
								if (is_numeric($key)) {
										$html .= str_repeat(' ', $indent) . str_pad($key, $longest_key, ' ');
								} else {
										$html .= str_repeat(' ', $indent) . str_pad('"' . self::htmlentities($key) . '"', $longest_key, ' ');
								}

								$html .= ' => ';

								$value = explode('<br />', self::recursiveVarDumpHelper($value, $expLevel, $depth + 1, $done));

								foreach ($value as $line => $val) {
										if ($line != 0) {
												$value[$line] = str_repeat(' ', $indent * 2) . $val;
										}
								}

								$html .= implode('<br />', $value) . '<br />';
						}

						$html .= ']</span>';
				}
		} elseif (is_object($var)) {
				// Check for recursion
				foreach ($done as $prev) {
						if ($prev === $var) {
								$html .= '<span style="color:#588bff;">object</span>(' . get_class($var) . ') *RECURSION DETECTED*';
								return $html;
						}
				}

				// Keep track of variables we have already processed to detect recursion
				$done[] = &$var;

				self::$hasArray=true;
				$uuid = 'include-php-' . uniqid() . mt_rand(1, 1000000);

				$html .= ' <img class=util-array  data-state=' . $setImg . ' id="' . $uuid . '" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D" /><span style="color:#588bff;">object</span>(' . get_class($var) . ') <span id="' . $uuid . '-collapsable" style="'.$setStyle.'"><br />[<br />';

				$varArray = (array) $var;

				$indent = 4;
				$longest_key = 0;

				foreach ($varArray as $key => $value) {
						if (substr($key, 0, 2) == "\0*") {
								unset($varArray[$key]); 
								$key = 'protected:' . substr($key, 3);
								$varArray[$key] = $value;
						} elseif (substr($key, 0, 1) == "\0") {
								unset($varArray[$key]);
								$key = 'private:' . substr($key, 1, strpos(substr($key, 1), "\0")) . ':' . substr($key, strpos(substr($key, 1), "\0") + 2);
								$varArray[$key] = $value;
						}

						if (is_string($key)) {
								$longest_key = max($longest_key, strlen($key) + 2);
						} else {
								$longest_key = max($longest_key, strlen($key));
						}
				}

				foreach ($varArray as $key => $value) {
						if (is_numeric($key)) {
								$html .= str_repeat(' ', $indent) . str_pad($key, $longest_key, ' ');
						} else {
								$html .= str_repeat(' ', $indent) . str_pad('"' . self::htmlentities($key) . '"', $longest_key, ' ');
						}

						$html .= ' => ';

						$value = explode('<br />', self::recursiveVarDumpHelper($value, $expLevel, $depth + 1, $done));

						foreach ($value as $line => $val) {
								if ($line != 0) {
										$value[$line] = str_repeat(' ', $indent * 2) . $val;
								}
						}

						$html .= implode('<br />', $value) . '<br />';
				}

				$html .= ']</span>';
		}

		return $html;
}



/**
 * Convert entities, while preserving already-encoded entities.
 *
 * @param  string $string The text to be converted
 * @return string
 */
static function htmlentities($string, $preserve_encoded_entities = false)
{
	//php8OK
	
		if ($preserve_encoded_entities) {
				// @codeCoverageIgnoreStart
				if (defined('HHVM_VERSION')) {
						$translation_table = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
				} else {
						$translation_table = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, self::mbInternalEncoding());
				}
				// @codeCoverageIgnoreEnd

				$translation_table[chr(38)] = '&';
				return preg_replace('/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/', '&amp;', strtr($string, $translation_table));
		}

		return htmlentities($string, ENT_QUOTES, self::mbInternalEncoding());
}

protected static function mbInternalEncoding($encoding = null)
{
	//php8OK

		if (function_exists('mb_internal_encoding')) {
				return $encoding ? mb_internal_encoding($encoding) : mb_internal_encoding();
		}

		// @codeCoverageIgnoreStart
		return 'UTF-8';
		// @codeCoverageIgnoreEnd
}
		
		
static function user_notice($message) {
	//php8OK
	
	$x=debug_backtrace();
	$caller = next($x);
	$fn = isset($caller['function']) ? $caller['function'] : 'no func';
	$file = isset($caller['file']) ? $caller['file'] : 'no file';
	$line = isset($caller['line']) ? $caller['line'] : 'no line';

	$data='[';
	if(isset(self::$stack['module']['slug']))
		$data.='module::' . self::$stack['module']['slug'] . ' ';
	if(isset(self::$stack['module']['collection']['post_type']))
		$data.='post_type::' . self::$stack['module']['collection']['post_type'];
	$data.=']';
	trigger_error($data . $message . ' in <strong>'.$fn.'</strong> called from <strong>'.$file.'</strong> on line <strong>'.$line.'</strong>'."\n<br />Awesome Notice ", E_USER_NOTICE); 
}

static function redis_connect($database_number){
	//php8OK

	if(!self::$redis_conn){
		self::$redis_conn = new Redis();
		self::$redis_conn->connect(REDIS_HOST, REDIS_PORT);
	}
	self::$redis_conn->select($database_number);
	return self::$redis_conn;	
}

static function new_mysqli(){
	//php8OK
	
	$mysqli = new SimpleMySQLi(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, "utf8mb4", "assoc");
	$mysqli->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
	return $mysqli;
}

static function cleanup(){
	if(self::$mysqli) {
		self::$mysqli->close();
	}
	
}

static function convert_name_value_string($arr){
	//php8OK
	
	$str='';

	array_walk_recursive($arr,function($item, $key) use (&$str)
	{
			$str.=$key . ':' . $item . '::';
	}
);
		return $str;
}

// takes a json and returns back an array
static function get_clean_args($content,&$atts=null){
	//php8OK
	
	if(is_null($content) || $content==='')return '';
	$json=self::clean_specialchars($content);
	$json=self::checkshortcode(self::parse_shortcode($json));
	$args=json_decode($json, true);
	if($json && is_null($args)){
		self::set_error('Invalid JSON' . $json);
	}
	return $args;
}


static function checkshortcode($string ) {
	//php8OK
	
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
	//php8OK
	if(is_null($content)) return $content;
	$content=str_replace ( "&#8216;" , "'" ,$content );
	$content=str_replace ( "&#8217;" , "'" ,$content );
	$content=str_replace ( "&#8220;" , '"' ,$content );
	$content=str_replace ( "&#8221;" , '"' ,$content );
	$content=str_replace ( "&#8243;" , '"' ,$content );
	$content=str_replace ( "&#039;" , "'" ,$content );
	return $content;
}

static function clean_html($content){
	//php8OK
	if(is_null($content)) return $content;
	$content=preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $content);
	return $content;
}

static function break_words($content, $length , $tags = '<a><em><strong>',$extra = '') {
	//php8OK
	$output=$content;
	if(IS_WP)$output = strip_shortcodes(strip_tags($output), $tags);
	$output = preg_split('/\b/', $output, $length * 2 + 1);
	$excerpt_waste = array_pop($output);
	$output = implode($output);
	$output .= $extra;

	return $output ;
}	


static function endswith($string, $test) {
	//php8OK
	
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

static function startsWith($haystack, $needle)
{
	//php8OK
	
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

//----------------------------------------------------------------------------------------------
//Shortcode Functions
static function reg_shortcode($shortcode_name, $func_name){
	//php8OK	
		add_shortcode($shortcode_name, $func_name);
	}

static function unescape_invalid_shortcodes( $content ) {
	//php8OK	
    // Clean up entire string, avoids re-parsing HTML.
    $trans = array(
        '&#91;' => '[',
        '&#93;' => ']',
        '&osb;' => '[',
        '&csb;' => ']',
    );
    $content = strtr( $content, $trans );
 
    return $content;
}
static function parse_shortcode( $content, $ignore_html = false,$sc_exec_restore='no') {
	//php8OK	
	if(is_null($content)) return $content;
	$content = preg_replace("/\/\/\*.*\*\/\//sU", "", $content);
	if ( false === strpos( $content, '[' ) )return $content;

	
	$pattern = self::get_shortcode_regex();


	
	$pattern =str_replace("_handler","(?:[a-zA-Z0-9\-._@])+",$pattern);	

	if($sc_exec_restore==='yes')$restore=self::sc_exec_setup_pos();	
	$count=0;
	$content = preg_replace_callback( "/$pattern/s", 'self::shortcode_tag', $content,-1, $count ,PREG_OFFSET_CAPTURE );
	if($sc_exec_restore==='yes')self::sc_exec_restore($restore);	
	//$content = preg_replace_callback( "/$pattern/s", 'self::shortcode_tag', $content );
		
	
	

	

	if(isset(self::$stack['_return']))return '';
	
	// Always restore square braces so we don't break things like <!--[if IE ]>
	$content = self::unescape_invalid_shortcodes( $content );

	return  self::safe_trim($content);
}


static function service_helper_old($tag,$attr,$content){
	//php8OK	
	
		$tag=str_replace('service:','',$tag);
	
	$pieces=self::safe_explode('.',$tag);
	$service=null;
	
	if(count($pieces)>=2){
		$sc=array();
		$sc['tags']=$pieces;
		
		//awesome handles this block
		$handlers=self::get_array_ref('handlers');

		$next_tag=$pieces[0];
		
		if(isset($handlers[$next_tag]) ){
			$service=array_shift($pieces);
			$sc['handler']=$handlers[$service];
			$next_tag=$pieces[0];
			
			if(isset($sc['handler'][$next_tag])){
				$service=array_shift($pieces);
				$sc['handler']=$sc['handler'][$service];
				$next_tag=null;
				if(isset($pieces[0]))$next_tag=$pieces[0];	
			}	
		}
		$sc['tags_left']=$pieces;
	}

	if(!$service)return 'Service Not Found';
	
	if(!empty($attr))self::pre_action_parse($attr);
		
	$pre_compiler_check=array('c','and','or','m','m2','o','o2');
	$pre=array();
	$pre['primary']=array();
		
	if(!empty($attr)){
		foreach ($attr as $key => $value) {
			
			$pre_key = self::safe_explode('.',$key);
			
			if(count($pre_key)>1 && in_array($pre_key[0],$pre_compiler_check)){
				$pre[$pre_key[0]][$pre_key[1]] = $value;
			}else{
				$pre['primary'][$key] = $value;
			}
		}
	}	
				
	$check_cond = true ;
	if(isset($pre['c'])){
		//loop and call chain which will update all atts
		foreach ($pre['c'] as $key => $value) {
			if(isset($handlers['c'][$key])){
				if (isset($handlers['c'][$key]['func']))
					$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
				else
					$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
				
				$check_cond=call_user_func($c_fn_name, $pre['c'], '', '' );
				if($check_cond === false && !isset($pre['or']))	return '';
				break;
			}
		}
	}
	
	if(isset($pre['and'])){
		foreach ($pre['and'] as $key => $value) {
			if(isset($handlers['c'][$key])){
				if (isset($handlers['c'][$key]['func']))
					$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
				else
					$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
				
				$check_and=call_user_func($and_fn_name, $pre['and'], '', '' );
				if($check_and === false) return '';
				break;
			}
		}
	}
	
	if(isset($pre['or']) && ($check_cond === false)){
		foreach ($pre['or'] as $key => $value) {
			if(isset($handlers['c'][$key])){
				if (isset($handlers['c'][$key]['func']))
					$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
				else
					$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
				
				$check_or=call_user_func($or_fn_name, $pre['or'], '', '' );
				if($check_or === false) return '';
				break;
			}
		}
	}
		
	$flag = false;
		
		
	$handler=$sc['handler'];

	if(isset($handler['type'])){
		$service_type = $handler['type'];

		$fn_name=null;
		switch($service_type){
			
			case 'content_type_def':
				$sc['content_type']=$sc['handler'];	
				$sc['handler']=$handlers['content_type_def'];					
				$service='content_type_def';
				
				if(isset($sc['handler'][$next_tag])){
					$service=$next_tag;
					$sc['handler']=$sc['handler'][$next_tag];
					$next_tag=null;
				}	
				$handler = $sc['handler'];
				
				if(isset($handler['func']))
					$fn_name=$handler['namespace'] . '\\' . $handler['func'];
				else{
						$fn_name=$handler['namespace'] . '\\' . $service;					
				}
				if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
				if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
				if (!is_callable($fn_name))$fn_name=null;
				break;
				
			case 'collection':
				$sc['collection']=$sc['handler'];	
				$sc['handler']=$handlers['collection'];
				$service='collection';
				
				if(isset($sc['handler'][$next_tag])){
					$service=$next_tag;
					$sc['handler']=$sc['handler'][$next_tag];
					$next_tag=null;
				}	
				$handler = $sc['handler'];		
	
				if(isset($handler['func']))
					$fn_name=$handler['namespace'] . '\\' . $handler['func'];
				else{
						$fn_name=$handler['namespace'] . '\\' . $service;					
				}
				if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
				if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
				if (!is_callable($fn_name))$fn_name=null;
				break;
			case 'namespace':
				if(isset($handler['func']))
					$fn_name=$handler['namespace'] . '\\' . $handler['func'];
				else{
						$fn_name=$handler['namespace'] . '\\' . $service;
				}
				if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
				if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
				if (!is_callable($fn_name))$fn_name=null;
				break;

			case 'awesome':
				if(isset($handler['func']))
					$fn_name=$handler['func'];
				else{
						$fn_name='aw2_' . $service;					
				}
				if (!is_callable($fn_name) && $next_tag)$fn_name='aw2_' .$service . '_'  . $next_tag;
				if (!is_callable($fn_name))$fn_name='aw2_' .$service . '_'  . 'unhandled';
				if (!is_callable($fn_name))$fn_name=null;
				break;
				
			case 'env_key':
				$pre['primary']['_prefix']=$handler['env_key'];
				$sc['handler']=$handlers['env'];
				$service='env';
				
				if(isset($sc['handler'][$next_tag])){
					$service=$next_tag;
					$sc['handler']=$sc['handler'][$next_tag];
					$next_tag=null;
				}	
				$handler = $sc['handler'];		
	
				if(isset($handler['func']))
					$fn_name=$handler['namespace'] . '\\' . $handler['func'];
				else{
						$fn_name=$handler['namespace'] . '\\' . $service;					
				}
				if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
				if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
				if (!is_callable($fn_name))$fn_name=null;
				break;
		}
		
		if($fn_name){
			$flag = true;
			$reply = call_user_func($fn_name, $pre['primary'], $content, $sc );
		}
	}


	if ($flag===true){
		if(isset($pre['m'])){
			//$reply=self::modify_output($reply,$pre['m']);
			foreach ($pre['m'] as $key => $value) {
				if(isset($handlers['m'][$key])){
					if (isset($handlers['m'][$key]['func']))
						$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
					else
						$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
					$reply=call_user_func($m_fn_name, $reply, $pre['m'] );
				}
			}
		}
		
		if(isset($pre['m2'])){
			foreach ($pre['m2'] as $key => $value) {
				if(isset($handlers['m'][$key])){
					if (isset($handlers['m'][$key]['func']))
						$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
					else
						$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
					$reply=call_user_func($m_fn_name, $reply, $pre['m2'] );
				}
			}
		}
		
		if(isset($pre['o'])){
			//$reply=self::redirect_output($reply,$pre['o']);
			foreach ($pre['o'] as $key => $value) {
				if(isset($handlers['o'][$key])){
					if (isset($handlers['o'][$key]['func']))
						$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
					else
						$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
					$reply=call_user_func($o_fn_name, $reply, $pre['o'] );
				}
			}
		}
		
		if(isset($pre['o2'])){
			//$reply=self::redirect_output($reply,$pre['o']);
			foreach ($pre['o2'] as $key => $value) {
				if(isset($handlers['o'][$key])){
					if (isset($handlers['o'][$key]['func']))
						$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
					else
						$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
					$reply=call_user_func($o_fn_name, $reply, $pre['o2'] );
				}
			}
		}
		

		return $reply;
		}
	

	return '';	
}

static function service_helper($tags,$atts=array(),$content=null){
	//php8OK	
	
	$inputs=array();
	$inputs['tags']= str_replace('service:','',$tags);
	$inputs['atts'] = $atts;


	if (!is_null($content))
		$inputs['content']=$content;
		
	$reply=self::process_handler($inputs);
		

	if($reply==='#not_awesome_sc#'){
		return '';
	}	

	return $reply;			
}

static function service_run($tag,$attr,$content,$default='service'){
	//php8OK	
	
	if(is_array($tag) || is_object($tag))return $tag;
	if(strlen($tag) <= 1)return (string)$tag;

	if($tag==='yes')return (string)'yes';
	if($tag==='no')return (string)'no';

	if($tag==='true')return true;
	if($tag==='false')return false;

	if($tag==='null')return NULL;
	
	//Collect first 2 chars of the string to check the type.
	$str_type = substr( $tag, 0, 2 );	
	$trunc_str = substr( $tag, 2, strlen($tag) );
	
	if($str_type === 'x:')return self::service_helper($trunc_str,$attr,$content);
	if($str_type === 's:')return (string) $trunc_str;
	if($str_type === 'n:')return (float) $trunc_str;
	if($str_type === 'i:')return (int) $trunc_str;

	if($str_type === 'b:'){
		if($trunc_str === '' || $trunc_str === 'false')
			return (bool) false;
		else
			return (bool) $trunc_str;
	}
	
	 
	switch ($default) {
		//case 'parse_attributes':
		//	$return_value=self::pre_action_parse($atts);
		//	break;
		case 'service':
			return self::service_helper($tag,$attr,$content);
			break;
		case 'string':
			return (string) $tag;
			break;
		case 'number':
			return (float) $tag;
			break;
		case 'int':
			return (int) $tag;
			break;		
		case 'comma':
			return self::safe_explode(',',(string)$tag);
			break;
			
		case 'bool':
			if($tag === '' || $tag === 'false')
				return (bool) false;
			else
				return (bool) $tag;
			break;	
		case 'env':
			return self::get($tag,$atts,$content);
			break;		
			
	}		
	return '';
	
} 


static function shortcode_tag_old_jan_9( $m ) {
	//php8OK	
	
		
	global $shortcode_tags;
	if(isset(self::$stack['_return']))return '';

	$sc_exec=&self::get_array_ref('@sc_exec');
	$sc_exec['sc']=$m[0][0];
	$sc_exec['pos']=$m[0][1];
	$sc_exec['content_pos']=$m[5][1];

	if(isset($sc_exec['start_pos']) && $sc_exec['start_pos']){
		$sc_exec['pos']=$sc_exec['pos'] + $sc_exec['start_pos'];
		if($sc_exec['content_pos']!==-1)$sc_exec['content_pos']=$sc_exec['content_pos'] +  $sc_exec['start_pos'];
	}
	$sc_exec['match']=$m;
	#generate link
	$sc_exec['link']=SITE_URL . '/d?module=' . ((isset($sc_exec['module'])) ? $sc_exec['module'] : '' ) .  '&post_type=' . ((isset($sc_exec['collection']['post_type'])) ? $sc_exec['collection']['post_type'] : '')  . '&pos=' . $sc_exec['pos'] ;
	
	
	// allow [[foo]] syntax for escaping a tag
	if ( $m[1][0] === '[' && $m[6][0] === ']' ) {
		return substr($m[0], 1, -1);
	}

	$tag = $m[2][0];
	$attr = self::shortcode_parse_atts( $m[3][0] );

	if ( isset( $m[5][0] ) )
		$content=$m[5][0];	
	else
		$content=null;

	$pieces=self::safe_explode('.',$tag);
	$service=null;

	
	if(count($pieces)>=2){
		$sc=array();
		$sc['tags']=$pieces;
		
		//awesome handles this block
		$handlers=self::get_array_ref('handlers');

		$next_tag=$pieces[0];
		
		if(isset($handlers[$next_tag]) ){
			$service=array_shift($pieces);
			$sc['handler']=$handlers[$service];
			$next_tag=$pieces[0];
			
			if(isset($sc['handler'][$next_tag])){
				$service=array_shift($pieces);
				$sc['handler']=$sc['handler'][$service];
				$next_tag=null;
				if(isset($pieces[0]))$next_tag=$pieces[0];	
			}	

			#for 3rd level		
			if(isset($sc['handler'][$next_tag])){
				$service=array_shift($pieces);
				$sc['handler']=$sc['handler'][$service];
				$next_tag=null;
				if(isset($pieces[0]))$next_tag=$pieces[0];	
			}	

			#for 4th level		
			if(isset($sc['handler'][$next_tag])){
				$service=array_shift($pieces);
				$sc['handler']=$sc['handler'][$service];
				$next_tag=null;
				if(isset($pieces[0]))$next_tag=$pieces[0];	
			}				
			
		}
		$sc['tags_left']=$pieces;
		
	}
	
	if($service){	

		if(!empty($attr))self::pre_action_parse($attr);
		
		$pre_compiler_check=array('c','and','or','m','m2','o','o2');
		$pre=array();
		$pre['primary']=array();
		if(!empty($attr)){
			foreach ($attr as $key => $value) {
				
				$pre_key = self::safe_explode('.',$key);
				
				if(count($pre_key)>1 && in_array($pre_key[0],$pre_compiler_check)){
					$pre[$pre_key[0]][$pre_key[1]] = $value;
				}else{
					$pre['primary'][$key] = $value;
				}
			}
		}	
				
		$check_cond = true ;
		if(isset($pre['c'])){
			//loop and call chain which will update all atts
			foreach ($pre['c'] as $key => $value) {
				if(isset($handlers['c'][$key])){
					if (isset($handlers['c'][$key]['func']))
						$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
					else
						$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
					
					$check_cond=call_user_func($c_fn_name, $pre['c'], '', '' );
					if($check_cond === false && !isset($pre['or']))	return '';
					break;
				}
			}
		}
	
		if(isset($pre['and'])){
			foreach ($pre['and'] as $key => $value) {
				if(isset($handlers['c'][$key])){
					if (isset($handlers['c'][$key]['func']))
						$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
					else
						$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
					
					$check_and=call_user_func($and_fn_name, $pre['and'], '', '' );
					if($check_and === false) return '';
					break;
				}
			}
		}
	
		if(isset($pre['or']) && ($check_cond === false)){
			foreach ($pre['or'] as $key => $value) {
				if(isset($handlers['c'][$key])){
					if (isset($handlers['c'][$key]['func']))
						$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
					else
						$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
					
					$check_or=call_user_func($or_fn_name, $pre['or'], '', '' );
					if($check_or === false) return '';
					break;
				}
			}
		}
		
		/* if(isset($pre['and'])){
			$check_and = self::checkcondition($pre['and']);
			if($check_and === false) return '';
		}
	
		if($check_cond === false && isset($pre['or'])){
			$check_or = self::checkcondition($pre['or']);
			if($check_or === false)	return '';			
		} */
		
		$flag = false;
		

		$handler=$sc['handler'];
		
		//support for new structure of handler
		if(isset($handler['#call'])){
			$handler['type']='call';
		}
		
		
		if(isset($handler['type'])){
			$service_type = $handler['type'];

			$fn_name=null;
			switch($service_type){
				
				case 'call':
					$fn_name=$handler['#call']['namespace'] . '\\' . $handler['#call']['func'];
					break;
				case 'content_type_def':
					$sc['content_type']=$sc['handler'];	
					$sc['handler']=$handlers['content_type_def'];					
					$service='content_type_def';
					
					if(isset($sc['handler'][$next_tag])){
						$service=$next_tag;
						$sc['handler']=$sc['handler'][$next_tag];
						$next_tag=null;
					}	
					$handler = $sc['handler'];
					
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
					
				case 'collection':
					$sc['collection']=$sc['handler'];	
					$sc['handler']=$handlers['collection'];
					$service='collection';
					
					if(isset($sc['handler'][$next_tag])){
						$service=$next_tag;
						$sc['handler']=$sc['handler'][$next_tag];
						$next_tag=null;
					}	
					$handler = $sc['handler'];		
		
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
				case 'namespace':
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;

				case 'awesome':
					if(isset($handler['func']))
						$fn_name=$handler['func'];
					else{
							$fn_name='aw2_' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name='aw2_' .$service . '_'  . $next_tag;
					if (!is_callable($fn_name))$fn_name='aw2_' .$service . '_'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
					
				case 'env_key':
					$pre['primary']['_prefix']=$handler['env_key'];
					$sc['handler']=$handlers['env'];
					$service='env';
					
					if(isset($sc['handler'][$next_tag])){
						$service=$next_tag;
						$sc['handler']=$sc['handler'][$next_tag];
						$next_tag=null;
					}	
					$handler = $sc['handler'];		
		
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
			}
			
			if($fn_name){
				//\util::var_dump($fn_name);
				//\util::var_dump($sc);
				//if(isset($sc['tags'][0]))echo $sc['tags'][0];

				$flag = true;
				$reply = call_user_func($fn_name, $pre['primary'], $content, $sc );
			}
		}
		
		if ($flag===true){
			if(isset($pre['m'])){
				//$reply=self::modify_output($reply,$pre['m']);
				foreach ($pre['m'] as $key => $value) {
					if(isset($handlers['m'][$key])){
						if (isset($handlers['m'][$key]['func']))
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
						else
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($m_fn_name, $reply, $pre['m'] );
					}
				}
			}
			if(isset($pre['m2'])){
				foreach ($pre['m2'] as $key => $value) {
					if(isset($handlers['m'][$key])){
						if (isset($handlers['m'][$key]['func']))
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
						else
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($m_fn_name, $reply, $pre['m2'] );
					}
				}
			}
			if(isset($pre['o'])){
				//$reply=self::redirect_output($reply,$pre['o']);
				foreach ($pre['o'] as $key => $value) {
					if(isset($handlers['o'][$key])){
						if (isset($handlers['o'][$key]['func']))
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
						else
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($o_fn_name, $reply, $pre['o'] );
					}
				}
			}
			if(isset($pre['o2'])){
				//$reply=self::redirect_output($reply,$pre['o']);
				foreach ($pre['o2'] as $key => $value) {
					if(isset($handlers['o'][$key])){
						if (isset($handlers['o'][$key]['func']))
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
						else
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($o_fn_name, $reply, $pre['o2'] );
					}
				}
			}
		if(is_array($reply) || is_object($reply)){
			self::user_notice("[A Shortcode ($tag) has replied with an array/object and there is no set command]");
			return;
		}	
		return $m[1][0] . $reply . $m[6][0];
		}
		
			
	}
	
	if(isset($shortcode_tags[$tag])){
		return $m[1][0] . call_user_func( $shortcode_tags[$tag], $attr, $content, $tag ) . $m[6][0];
	}

	return $m[0][0];
	
}

static function shortcode_tag( $m ) {
	//php8OK	
	
		
	global $shortcode_tags;
	if(isset(self::$stack['_return']))return '';

	$sc_exec=&self::get_array_ref('@sc_exec');
	$sc_exec['sc']=$m[0][0];
	$sc_exec['pos']=$m[0][1];
	$sc_exec['content_pos']=$m[5][1];

	if(isset($sc_exec['start_pos']) && $sc_exec['start_pos']){
		$sc_exec['pos']=$sc_exec['pos'] + $sc_exec['start_pos'];
		if($sc_exec['content_pos']!==-1)$sc_exec['content_pos']=$sc_exec['content_pos'] +  $sc_exec['start_pos'];
	}
	$sc_exec['match']=$m;
	#generate link
	$sc_exec['link']=SITE_URL . '/d/?module=' . ((isset($sc_exec['module'])) ? $sc_exec['module'] : '' ) .  '&post_type=' . ((isset($sc_exec['collection']['post_type'])) ? $sc_exec['collection']['post_type'] : '')  . '&pos=' . $sc_exec['pos'] ;
	
	// allow [[foo]] syntax for escaping a tag
	if ( $m[1][0] === '[' && $m[6][0] === ']' ) {
		return substr($m[0][0], 1, -1);
	}

	$tag = $m[2][0];
	$attr = self::shortcode_parse_atts( $m[3][0] );

	if ( isset( $m[5][0] ) )
		$content=$m[5][0];	
	else
		$content=null;
		
	$inputs=array();
	$inputs['tags']= $tag;
	$inputs['atts'] = $attr;

	if ( isset( $m[5][0] ) )
		$inputs['content']=$m[5][0];	

	$reply=self::process_handler($inputs);

	if($reply==='#not_awesome_sc#'){
		if(isset($shortcode_tags[$tag])){
			return $m[1][0] . call_user_func( $shortcode_tags[$tag], $attr, $content, $tag ) . $m[6][0];
		}

		return $m[0][0];
	}	

	if(is_array($reply) || is_object($reply)){
		//trigger error and continue		
		//self::user_notice("[A Shortcode ($tag) has replied with an array/object and there is no set command]");
		//return;
	}	
	return $m[1][0] . $reply . $m[6][0];
}

static function process_handler($inputs){

	if(isset($inputs['tags']))
		$pieces=self::safe_explode('.',$inputs['tags']);
	else
		$pieces=$inputs['pieces'];
	
	if(count($pieces)<2)return '#not_awesome_sc#';

	$content=null;
	if ( isset( $inputs['content']) )
		$content=$inputs['content'];	


	$sc=array();
	$sc['tags']=$pieces;
		
	//awesome handles this block
	$handlers=self::get_array_ref('handlers');

	$next_tag=$pieces[0];

	$service=null;			
	if(isset($handlers[$next_tag]) ){
		$service=array_shift($pieces);
		$sc['handler']=$handlers[$service];
		$next_tag=$pieces[0];
		
		if(isset($sc['handler'][$next_tag])){
			$service=array_shift($pieces);
			$sc['handler']=$sc['handler'][$service];
			$next_tag=null;
			if(isset($pieces[0]))$next_tag=$pieces[0];	
		}

		#for 3rd level		
		if(isset($sc['handler'][$next_tag])){
			$service=array_shift($pieces);
			$sc['handler']=$sc['handler'][$service];
			$next_tag=null;
			if(isset($pieces[0]))$next_tag=$pieces[0];	
		}	

		#for 4th level		
		if(isset($sc['handler'][$next_tag])){
			$service=array_shift($pieces);
			$sc['handler']=$sc['handler'][$service];
			$next_tag=null;
			if(isset($pieces[0]))$next_tag=$pieces[0];	
		}				
		
	}
	$sc['tags_left']=$pieces;

	if(is_null($service))return '';

	$atts=array();
	if ( isset( $inputs['atts']) )
		$atts=$inputs['atts'];	

	if(!empty($atts))self::pre_action_parse($atts);

	$pre_compiler_check=array('c','and','or','m','m2','o','o2');
	$pre=array();
	$pre['primary']=array();
		
	if(!empty($atts)){
		foreach ($atts as $key => $value) {
			
			$pre_key = self::safe_explode('.',$key);
			
			if(count($pre_key)>1 && in_array($pre_key[0],$pre_compiler_check)){
				$pre[$pre_key[0]][$pre_key[1]] = $value;
			}else{
				$pre['primary'][$key] = $value;
			}
		}
	}	
				
	$check_cond = true ;
	if(isset($pre['c'])){
		//loop and call chain which will update all atts
		foreach ($pre['c'] as $key => $value) {
			if(isset($handlers['c'][$key])){
				if (isset($handlers['c'][$key]['func']))
					$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
				else
					$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;

				$check_cond=call_user_func($c_fn_name, $pre['c'], '', '' );
				if($check_cond === false && !isset($pre['or']))	return '';
				break;
			}
		}
	}
	
	if(isset($pre['and'])){
		foreach ($pre['and'] as $key => $value) {
			if(isset($handlers['c'][$key])){
				if (isset($handlers['c'][$key]['func']))
					$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
				else
					$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
				
				$check_and=call_user_func($and_fn_name, $pre['and'], '', '' );
				if($check_and === false) return '';
				break;
			}
		}
	}
	
	if(isset($pre['or']) && ($check_cond === false)){
		foreach ($pre['or'] as $key => $value) {
			if(isset($handlers['c'][$key])){
				if (isset($handlers['c'][$key]['func']))
					$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
				else
					$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
				
				$check_or=call_user_func($or_fn_name, $pre['or'], '', '' );
				if($check_or === false) return '';
				break;
			}
		}
	}
	

	
	$flag = false;
	
	
	$handler=$sc['handler'];
	
	//support for new structure of handler
	if(isset($handler['#call'])){
		$handler['type']='call';
	}
	
	if(!isset($handler['type']))throw new BadMethodCallException('Handler does not have a type'); 
	

	$service_type = $handler['type'];

	$fn_name=null;
	switch($service_type){

		case 'call':
			$fn_name=$handler['#call']['namespace'] . '\\' . $handler['#call']['func'];
			break;
		case 'content_type_def':
			$sc['content_type']=$sc['handler'];	
			$sc['handler']=$handlers['content_type_def'];					
			$service='content_type_def';
			
			if(isset($sc['handler'][$next_tag])){
				$service=$next_tag;
				$sc['handler']=$sc['handler'][$next_tag];
				$next_tag=null;
			}	
			$handler = $sc['handler'];
			
			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
			
		case 'collection':
			$sc['collection']=$sc['handler'];	
			$sc['handler']=$handlers['collection'];
			$service='collection';
			
			if(isset($sc['handler'][$next_tag])){
				$service=$next_tag;
				$sc['handler']=$sc['handler'][$next_tag];
				$next_tag=null;
			}	
			$handler = $sc['handler'];		

			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
			
		case 'module':
			$sc['collection']=$sc['handler']['collection'];	
			$pre['primary']['module']=$sc['handler']['module'];
			
			if(!isset($pre['primary']['main']))
				$pre['primary']['main']=implode('.',$sc['tags_left']);


			$sc['handler']=$handlers['collection']['run'];
			$service='run';
			
			$handler = $sc['handler'];		

			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			
			
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			
			//\util::var_dump($pre);
			//\util::var_dump($sc);
			//\util::var_dump($fn_name);

			break;
			
		case 'namespace':
			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;

		case 'awesome':
			if(isset($handler['func']))
				$fn_name=$handler['func'];
			else{
					$fn_name='aw2_' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name='aw2_' .$service . '_'  . $next_tag;
			if (!is_callable($fn_name))$fn_name='aw2_' .$service . '_'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
			
		case 'env_key':
			$pre['primary']['_prefix']=$handler['env_key'];
			$sc['handler']=$handlers['env'];
			$service='env';
			
			if(isset($sc['handler'][$next_tag])){
				$service=$next_tag;
				$sc['handler']=$sc['handler'][$next_tag];
				$next_tag=null;
			}	
			$handler = $sc['handler'];		

			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
	}

	if(!$fn_name)throw new BadMethodCallException('Handler does not have a func');; 
	
	$reply = call_user_func($fn_name, $pre['primary'], $content, $sc );
	if(isset($pre['m'])){
		//$reply=self::modify_output($reply,$pre['m']);
		foreach ($pre['m'] as $key => $value) {
			if(isset($handlers['m'][$key])){
				if (isset($handlers['m'][$key]['func']))
					$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
				else
					$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
				$reply=call_user_func($m_fn_name, $reply, $pre['m'] );
			}
		}
	}
	if(isset($pre['m2'])){
		foreach ($pre['m2'] as $key => $value) {
			if(isset($handlers['m'][$key])){
				if (isset($handlers['m'][$key]['func']))
					$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
				else
					$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
				$reply=call_user_func($m_fn_name, $reply, $pre['m2'] );
			}
		}
	}
	if(isset($pre['o'])){
		//$reply=self::redirect_output($reply,$pre['o']);
		foreach ($pre['o'] as $key => $value) {
			if(isset($handlers['o'][$key])){
				if (isset($handlers['o'][$key]['func']))
					$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
				else
					$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
				$reply=call_user_func($o_fn_name, $reply, $pre['o'] );
			}
		}
	}
	if(isset($pre['o2'])){
		//$reply=self::redirect_output($reply,$pre['o']);
		foreach ($pre['o2'] as $key => $value) {
			if(isset($handlers['o'][$key])){
				if (isset($handlers['o'][$key]['func']))
					$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
				else
					$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
				$reply=call_user_func($o_fn_name, $reply, $pre['o2'] );
			}
		}
	}
	return $reply;	
}

static function shortcode_tag_old( $m ) {
	//php8OK	
	
		
	global $shortcode_tags;
	if(isset(self::$stack['_return']))return '';
	
	// allow [[foo]] syntax for escaping a tag
	if ( $m[1] === '[' && $m[6] === ']' ) {
		return substr($m[0], 1, -1);
	}

	$tag = $m[2];
	$attr = self::shortcode_parse_atts( $m[3] );

	if ( isset( $m[5] ) )
		$content=$m[5];	
	else
		$content=null;

	$pieces=self::safe_explode('.',$tag);
	$service=null;

	
	if(count($pieces)>=2){
		$sc=array();
		$sc['tags']=$pieces;
		
		//awesome handles this block
		$handlers=self::get_array_ref('handlers');

		$next_tag=$pieces[0];
		
		if(isset($handlers[$next_tag]) ){
			$service=array_shift($pieces);
			$sc['handler']=$handlers[$service];
			$next_tag=$pieces[0];
			
			if(isset($sc['handler'][$next_tag])){
				$service=array_shift($pieces);
				$sc['handler']=$sc['handler'][$service];
				$next_tag=null;
				if(isset($pieces[0]))$next_tag=$pieces[0];	
			}	
		}
		$sc['tags_left']=$pieces;
		
	}
	
	if($service){	

		if(!empty($attr))self::pre_action_parse($attr);
		
		$pre_compiler_check=array('c','and','or','m','m2','o','o2');
		$pre=array();
		$pre['primary']=array();
		if(!empty($attr)){
			foreach ($attr as $key => $value) {
				
				$pre_key = self::safe_explode('.',$key);
				
				if(count($pre_key)>1 && in_array($pre_key[0],$pre_compiler_check)){
					$pre[$pre_key[0]][$pre_key[1]] = $value;
				}else{
					$pre['primary'][$key] = $value;
				}
			}
		}	
				
		$check_cond = true ;
		if(isset($pre['c'])){
			//loop and call chain which will update all atts
			foreach ($pre['c'] as $key => $value) {
				if(isset($handlers['c'][$key])){
					if (isset($handlers['c'][$key]['func']))
						$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
					else
						$c_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
					
					$check_cond=call_user_func($c_fn_name, $pre['c'], '', '' );
					if($check_cond === false && !isset($pre['or']))	return '';
					break;
				}
			}
		}
	
		if(isset($pre['and'])){
			foreach ($pre['and'] as $key => $value) {
				if(isset($handlers['c'][$key])){
					if (isset($handlers['c'][$key]['func']))
						$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
					else
						$and_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
					
					$check_and=call_user_func($and_fn_name, $pre['and'], '', '' );
					if($check_and === false) return '';
					break;
				}
			}
		}
	
		if(isset($pre['or']) && ($check_cond === false)){
			foreach ($pre['or'] as $key => $value) {
				if(isset($handlers['c'][$key])){
					if (isset($handlers['c'][$key]['func']))
						$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $handlers['c'][$key]['func'];
					else
						$or_fn_name=$handlers['c'][$key]['namespace'] . '\\' . $key;
					
					$check_or=call_user_func($or_fn_name, $pre['or'], '', '' );
					if($check_or === false) return '';
					break;
				}
			}
		}
		
		/* if(isset($pre['and'])){
			$check_and = self::checkcondition($pre['and']);
			if($check_and === false) return '';
		}
	
		if($check_cond === false && isset($pre['or'])){
			$check_or = self::checkcondition($pre['or']);
			if($check_or === false)	return '';			
		} */
		
		$flag = false;
		
		
		$handler=$sc['handler'];
		if(isset($handler['type'])){
			$service_type = $handler['type'];

			$fn_name=null;
			switch($service_type){
				
				case 'content_type_def':
					$sc['content_type']=$sc['handler'];	
					$sc['handler']=$handlers['content_type_def'];					
					$service='content_type_def';
					
					if(isset($sc['handler'][$next_tag])){
						$service=$next_tag;
						$sc['handler']=$sc['handler'][$next_tag];
						$next_tag=null;
					}	
					$handler = $sc['handler'];
					
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
					
				case 'collection':
					$sc['collection']=$sc['handler'];	
					$sc['handler']=$handlers['collection'];
					$service='collection';
					
					if(isset($sc['handler'][$next_tag])){
						$service=$next_tag;
						$sc['handler']=$sc['handler'][$next_tag];
						$next_tag=null;
					}	
					$handler = $sc['handler'];		
		
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
				case 'namespace':
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;

				case 'awesome':
					if(isset($handler['func']))
						$fn_name=$handler['func'];
					else{
							$fn_name='aw2_' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name='aw2_' .$service . '_'  . $next_tag;
					if (!is_callable($fn_name))$fn_name='aw2_' .$service . '_'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
					
				case 'env_key':
					$pre['primary']['_prefix']=$handler['env_key'];
					$sc['handler']=$handlers['env'];
					$service='env';
					
					if(isset($sc['handler'][$next_tag])){
						$service=$next_tag;
						$sc['handler']=$sc['handler'][$next_tag];
						$next_tag=null;
					}	
					$handler = $sc['handler'];		
		
					if(isset($handler['func']))
						$fn_name=$handler['namespace'] . '\\' . $handler['func'];
					else{
							$fn_name=$handler['namespace'] . '\\' . $service;					
					}
					if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
					if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
					if (!is_callable($fn_name))$fn_name=null;
					break;
			}
			
			if($fn_name){
				//\util::var_dump($fn_name);
				//\util::var_dump($sc);
				//if(isset($sc['tags'][0]))echo $sc['tags'][0];

				$flag = true;
				$reply = call_user_func($fn_name, $pre['primary'], $content, $sc );
			}
		}
		
		if ($flag===true){
			if(isset($pre['m'])){
				//$reply=self::modify_output($reply,$pre['m']);
				foreach ($pre['m'] as $key => $value) {
					if(isset($handlers['m'][$key])){
						if (isset($handlers['m'][$key]['func']))
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
						else
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($m_fn_name, $reply, $pre['m'] );
					}
				}
			}
			if(isset($pre['m2'])){
				foreach ($pre['m2'] as $key => $value) {
					if(isset($handlers['m'][$key])){
						if (isset($handlers['m'][$key]['func']))
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $handlers['m'][$key]['func'];
						else
							$m_fn_name=$handlers['m'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($m_fn_name, $reply, $pre['m2'] );
					}
				}
			}
			if(isset($pre['o'])){
				//$reply=self::redirect_output($reply,$pre['o']);
				foreach ($pre['o'] as $key => $value) {
					if(isset($handlers['o'][$key])){
						if (isset($handlers['o'][$key]['func']))
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
						else
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($o_fn_name, $reply, $pre['o'] );
					}
				}
			}
			if(isset($pre['o2'])){
				//$reply=self::redirect_output($reply,$pre['o']);
				foreach ($pre['o2'] as $key => $value) {
					if(isset($handlers['o'][$key])){
						if (isset($handlers['o'][$key]['func']))
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $handlers['o'][$key]['func'];
						else
							$o_fn_name=$handlers['o'][$key]['namespace'] . '\\' . $key;
						$reply=call_user_func($o_fn_name, $reply, $pre['o2'] );
					}
				}
			}
		if(is_array($reply) || is_object($reply)){
			self::user_notice("[A Shortcode ($tag) has replied with an array/object and there is no set command]");
			return;
		}	
		return $m[1] . $reply . $m[6];
		}
		
			
	}
	
	if(isset($shortcode_tags[$tag])){
		return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, $content, $tag ) . $m[6];
	}

	return $m[0];
	
}

static function resolve_chain($str,&$atts=null,$content=null){
	//php8OK	
	
	if(is_array($str))
		return $str;
	
	if(strlen($str) <= 1)
		return (string)$str;

	if($str==='yes')
		return (string)'yes';

	if($str==='no')
		return (string)'no';

	if($str==='true')
		return true;

	if($str==='false')
		return false;

	if($str==='null')
		return NULL;
	
	//Collect first 2 chars of the string to check the type.
	$str_type = substr( $str, 0, 2 );	
	$trunc_str = substr( $str, 2, strlen($str) );
	
	if($str_type === 's:')
		return (string) $trunc_str;
	
	if($str_type === 'n:')
		return (float) $trunc_str;
	
	if($str_type === 'i:')
		return (int) $trunc_str;
	
	if($str_type === 'b:'){
		if($trunc_str === '' || $trunc_str === 'false')
			return (bool) false;
		else
			return (bool) $trunc_str;
	}
	
	$str = self::get($str,$atts,$content);
	
	return $str;
}


static function shortcode_parse_atts($text) {
	//php8OK	
	$atts = array();
	$pattern = '/([-a-zA-Z0-9_.@\$]+)\s*=\s*"([^"]*)"(?:\s|$)|([-a-zA-Z0-9_.@\$]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([-a-zA-Z0-9_.@\$]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
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
		$atts =  self::safe_ltrim($text);
	}
	return $atts;
}

static function add_shortcode($library,$tag, $func,$desc=null) {
	//php8OK	
	$handler=&self::get_array_ref('handlers',$library);
	$handler[$tag]=array();
	$handler[$tag]['name']=$tag;
	$handler[$tag]['desc']=$desc;
	$handler[$tag]['func']=$func;
}

static $libraries=array();

static function add_library($library,$desc=null,$alias=null) {
	//php8OK	
	$handler=&self::get_array_ref('handlers',$library);
	if(!$alias)$alias=$library;
	$handler['alias']=$alias;
	$handler['desc']=$desc;
}

static function remove_service($keys) {
	//php8OK	
	$current=&self::get_array_ref('handlers');
	
	if(!is_array($keys))$keys= self::safe_explode('.',$keys);	
	
	while(!empty($keys)){
		$key=array_shift($keys);
		if(count($keys)<=0){
			unset($current[$key]);
			return;	
		}
		$current=&$current[$key];
	}
}


static function add_handler($name,$call,$desc='',$prop=array()) {
	$build=array();
	$build['#desc']=$desc;
	$build['#call']=$call;
	$build['#prop']=$prop;
	self::set('handlers.' . $name,$build);
}


static function add_service($service,$desc=null,$atts=array()) {
	
	//php8OK	
	$atts['desc']=$desc;

	if(isset($atts['code'])){
		$atts['type'] = 'namespace';
		$atts['@service'] = true;
		self::set('handlers.' . $service,$atts);
		return;
	}
	
	if(isset($atts['content_type_def'])){
		$handler=&self::get_array_ref('handlers',$service);
		$atts['type'] = 'content_type_def';
		$atts['@service'] = true;
		$handler = array_merge($handler,$atts);
		return;
	}

	
	if(isset($atts['content_type'])){
		$handler=&self::get_array_ref('handlers',$service);
		$atts['type'] = 'content_type';
		$atts['@service'] = true;
		$handler = array_merge($handler,$atts);
		return;
	}
	
	if(isset($atts['app'])){
		$handler=&self::get_array_ref('handlers',$service);
		$atts['type'] = 'app';
		$atts['@service'] = true;
		$handler = array_merge($handler,$atts);
		return;
	}
	
	if(isset($atts['module'])){
		$atts['type'] = 'module';
		$atts['@service'] = true;
		self::set('handlers.' . $service,$atts);
		return;
	}
	
	if(isset($atts['post_type']) || isset($atts['source'])){
		$atts['type'] = 'collection';
		$atts['@service'] = true;
		self::set('handlers.' . $service,$atts);
		return;
	}

	if(isset($atts['env_key'])){
		$atts['type'] = 'env_key';
		$atts['@service'] = true;
		self::set('handlers.' . $service,$atts);
		return;
	}
	
	if(isset($atts['namespace'])){
		$atts['type'] = 'namespace';
		$atts['@service'] = true;
		self::set('handlers.' . $service,$atts);
		return;
	}
	

	

	$handler=&self::get_array_ref('handlers',$service);
	$atts['type'] = 'awesome';
	$atts['@service'] = true;
	$handler = array_merge($handler,$atts);
	return;
}

static function add_ref($service,$ref_to) {
	//php8OK	
	self::$stack['handlers'][$service]=&self::get_array_ref('handlers',$ref_to);
}


static function register_service($name,$atts,$desc=null) {
	//php8OK	
	$arr=$atts;
	$arr['alias']='service';
	$arr['desc']=$desc;
	$handler=&self::get_array_ref('handlers',$name);
	$handler=array_merge($handler,$arr);
}


static function collection_define($collection,$atts){
	//php8OK	
	if (!is_array(self::$stack['collections']))self::$stack['collections']=array();
	self::$stack['collections'][$collection]=$atts;
	self::add_library($collection,'Collection','collection'); 
}

static function get_shortcode_regex() {
	//php8OK	

	if(IS_WP){
		global $shortcode_tags;
		$tagnames = array_keys($shortcode_tags);
	}
	else{
		$tagnames=array('not_required.XXXsda');
	}

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
	//php8OK	
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
	//php8OK	
	//Create Local Data
	if($create===true){
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
	//php8OK	
	self::set('errors.new',$msg);
}



//pre actions	
static function pre_actions($actions,&$atts=null,$content=null,$shortcode=null){
	//php8OK	
	$return_value=true;
	if(!$atts) return $return_value;
	if($actions==='all'){
		//$return_value=self::pre_action_parse($atts);
		$return_value=self::checkcondition($atts);
		return $return_value;
	}
	switch ($actions) {
		//case 'parse_attributes':
		//	$return_value=self::pre_action_parse($atts);
		//	break;
		case 'check_if':
			$return_value=self::checkcondition($atts);
			break;
	}
	return $return_value;
	
}

static function pre_action_parse(&$atts) {
	//php8OK	
	foreach ($atts as $key => $value) {
		if (is_int($key)) {
			$atts['main']=$value;	
			unset($atts[$key]);
		}
	}
	
	foreach ($atts as $key =>$value) {
		$pattern = '/{\s*\"/';
		if (is_string($value) && preg_match($pattern, $value)!==1 && strpos($value, '{') !== false && strpos($value, '}') !== false) {

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
		if(is_string($atts[$key])){
			$parts=explode(':',$atts[$key],2);
			if(count($parts)===2){
				if($parts[0]==='int')$atts[$key]=(int)$parts[1];
				if($parts[0]==='num')$atts[$key]=(float)$parts[1];
				if($parts[0]==='str')$atts[$key]=(string)$parts[1];
				if($parts[0]==='comma')$atts[$key]=explode(',', (string)$parts[1]);
				if($parts[0]==='bool'){
					if($parts[1] === '' || $parts[1] === 'false')
						$atts[$key]=false;
					else
						$atts[$key]=(bool)$parts[1];
				}
			}
		}
		
	}
	return;
}
	
static function checkcondition(&$atts){
	//php8Issue	
	if(!$atts)return true;


		if(array_key_exists('ignore',$atts)){
			return false;
		}

		if(array_key_exists('aw2_error',$atts)){
			if(is_object($atts['aw2_error']) && get_class($atts['aw2_error'])==='aw2_error'){
			    unset($atts['aw2_error']);
			}	
			else
				return false;
				
		}
		
		
		if(array_key_exists('odd',$atts)){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['odd'],'lhs_dt'=>'number','condition'=>'odd','php7result'=>((int)$atts['odd'] % 2 == 0)]);
			if((int)$atts['odd'] % 2 == 0)
				return false;
			else
				unset($atts['odd']);  
		}
		
		if(array_key_exists('even',$atts)){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['even'],'lhs_dt'=>'number','condition'=>'even','php7result'=>((int)$atts['even'] % 2 != 0)]);
			if((int)$atts['even'] % 2 != 0)
		return false;
	else
		unset($atts['even']);  
		}

		if(array_key_exists('true',$atts)){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['true'],'lhs_dt'=>'boolean','condition'=>'true','php7result'=>($atts['true']!=true)]);
			if($atts['true']!=true)
		return false;
	else
		unset($atts['true']); 
	}

	if(array_key_exists('false',$atts)){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['false'],'lhs_dt'=>'boolean','condition'=>'false','php7result'=>($atts['false']==true)]);
			if($atts['false']==true)
		return false;
	else
		unset($atts['false']); 
	}

	if(array_key_exists('yes',$atts)){
		aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['yes'],'lhs_dt'=>'string','condition'=>'yes','php7result'=>($atts['yes']!=='yes')]);
		if($atts['yes']!=='yes')
			return false;
		else
			unset($atts['yes']); 
	}

	if(array_key_exists('no',$atts)){
		aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['no'],'lhs_dt'=>'string','condition'=>'no','php7result'=>($atts['no']!=='no')]);
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
		if(is_null($atts['date_obj']))return false;
		if(!get_class($atts['date_obj'])=='DateTime')
			return false;
		else
			unset($atts['date_obj']); 
	}
	
	if(array_key_exists('not_date_obj',$atts)){
		//check if used. If not then resolve
		if(!is_null($atts['not_date_obj']) && get_class($atts['date_obj'])=== 'DateTime')
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
		if($atts['whitespace'] !== '' && !(ctype_space($atts['whitespace'])))return false;
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
	if(self::get_request($atts['request_exists'])===null)
		return false;
	else
		unset($atts['request_exists']); 		  
		}	  
	
		if(array_key_exists('request_not_exists',$atts)){
	if(self::get_request($atts['request_not_exists'])!==null)
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
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['cond'],'rhs'=>$atts['not_equal'],'must_match'=>'yes','condition'=>'not_equal','php7result'=>($atts['cond']!=$atts['not_equal'])]);
			if($atts['cond']!=$atts['not_equal'])
			{unset($atts['cond']);unset($atts['not_equal']); }		
			else 
			return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('equal',$atts) ){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['cond'],'rhs'=>$atts['equal'],'must_match'=>'yes','condition'=>'equal','php7result'=>($atts['cond']==$atts['equal'])]);
			if($atts['cond']==$atts['equal'])
		{unset($atts['cond']);unset($atts['equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('greater_than',$atts) ){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['cond'],'rhs'=>$atts['greater_than'],'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'greater_than','php7result'=>($atts['cond']>$atts['greater_than'])]);
			if($atts['cond']>$atts['greater_than'])
		{unset($atts['cond']);unset($atts['greater_than']); }		
			else 
		return false;
		}
	
		if(array_key_exists('cond',$atts) && array_key_exists('less_than',$atts) ){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['cond'],'rhs'=>$atts['less_than'],'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'less_than','php7result'=>($atts['cond']<$atts['less_than'])]);
			if($atts['cond']<$atts['less_than'])
		{unset($atts['cond']);unset($atts['less_than']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('greater_equal',$atts) ){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['cond'],'rhs'=>$atts['greater_equal'],'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'greater_equal','php7result'=>($atts['cond']>=$atts['greater_equal'])]);
			if($atts['cond']>=$atts['greater_equal'])
		{unset($atts['cond']);unset($atts['greater_equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('less_equal',$atts) ){
			aw2_error_log::log_datatype_mismatch(['lhs'=>$atts['cond'],'rhs'=>$atts['less_equal'],'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'less_equal','php7result'=>($atts['cond']<=$atts['less_equal'])]);
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
		 //monoframe
			$detect = new Mobile_Detect;
			$device_status=false;
			$arr= explode( ',' ,$atts['device'] );
			if($detect->isMobile() && !$detect->isTablet() && in_array('mobile',$arr) )
				$device_status=true;
	
			if($detect->isTablet() && in_array('tablet',$arr) )
				$device_status=true;
	
			if(!$detect->isMobile() && !$detect->isTablet() && in_array('desktop',$arr) )
				$device_status=true;

			if($device_status===false)
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
	//php8Issue	
	//$actions='all' is wrong
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
	//php8OK	
	
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
			
			$value = awesome_wp_utils::the_content_filter($value);
			$value = do_shortcode($value);
		}		
		
		if(array_key_exists('do_shortcode',$atts)){
			if(!IS_WP)die('do shortcode called in non WP environment');
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
			$value =  self::safe_trim($value);
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
		
		//functions added for this
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
			if($format=='')$format = 'M d, Y';

			if(is_object($value) && get_class($value)==='DateTime'){
				$value = date_format($value,$format);
			}
			else{
				$new_date = date_create($value);

				if (!$new_date) {
					$value='';
				}
				else{
					$value = date_format($new_date,$format);
				}
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

static function sentenceCase($value) { 
	//php8OK	
    $sentences = preg_split('/([.?!]+)/', $value, -1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE); 
    $return_value = ''; 
    foreach ($sentences as $key => $sentence) { 
        $return_value .= ($key & 1) == 0? 
            ucfirst(strtolower( self::safe_trim($sentence) )) : 
            $sentence.' '; 
    } 
    return self::safe_trim($return_value); 
}

static function redirect_output($value,&$atts){
	//php8OK	
		if($atts==null)return $value;

		if(array_key_exists('exit',$atts)){
			exit(util::var_dump($value,true));
		}
		
		if(array_key_exists('console',$atts)){
			echo('<script type="text/spa" spa_activity="core:console_log">Memory Usage ' . util::var_dump($value,true) .'</script>');
		}

		if(array_key_exists('log',$atts)){
			$path= LOG_PATH . '/log.html';
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

static function post_exists($slug,$posttype){
	//php8OK	
		global $table_prefix;
		
		$sql="select ID from ".$table_prefix."posts where post_type='" . $posttype . "' and post_name='".$slug."'";
		$results =self::get_results($sql);				

		if( $results ){
			return true;
		}
		else
		return false;
	}

static function get_post_from_slug($slug,$posttype,&$post,$site_id=null){
	//php8Amit	
		//should be only called from WP 
		if(!IS_WP)die('Not in WP');
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

static function get_option($option){
	//php8OK	
		global $table_prefix;
		
		$sql="SELECT option_value FROM ".$table_prefix."options WHERE option_name = '" . $option . "' LIMIT 1";
		$results =self::get_results($sql);				

		if( $results ){
		  return $results;
		}
		else
			return false;
}
 
//------------------------------------------------------------------------------------------------------------------------------
//Stack implementation

static function push_child($obj_type,$name){
	//php8OK	
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
	//php8OK	

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
	//php8OK	
	//echo $obj_type;
	$stack=&self::get_array_ref('call_stack');
	$new_obj=null;
	foreach ($stack as $key => $value) {
		if(!isset($stack[$key]['obj_type'])){
			self::user_notice("[You have destroyed the Key $key in the stack]");
			if (DEVELOP_FOR_AWESOMEUI){
				\util::var_dump($stack[$key]);
				\util::var_dump($stack);
				die();
			}
		}
		
		if($stack[$key]['obj_type']==$obj_type)
			$new_obj=$key;
	}
	return $new_obj;	
}

static function push_atts($stack_id,$atts=null){
	//php8OK	
	if(!$atts) return;
		$stack_ref=&self::get_array_ref('call_stack',$stack_id);
		foreach ($atts as $key => $value) {
				$stack_ref[$key]=$value;
		}
}

static function push_this($stack_id){
	//php8OK	
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
	//php8OK	
	return substr( str_shuffle( $chars ), 0, $length );
}

static function set($key,$value,$content=null,$atts=null){
	extract( self::shortcode_atts( array(
	'overwrite'=>'yes'
	), $atts) );
	if($key==null || $key=='')return;
	$return_value=null;
	if($value===null)$value=self::safe_trim(self::parse_shortcode($content));
	
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
				if (property_exists($arr,$pieces[0]) && $arr->{$pieces[0]}!='' && $arr->{$pieces[0]}!=null & $overwrite=='empty')$flag=false;
				if($flag)$arr->{$pieces[0]}=$value;
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
					$arr->{$pieces[0]}=null;
					
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
	//php8OK	
	$flag=true;
	if (array_key_exists($key, $_COOKIE) && $overwrite=='no')$flag=false;
	if (array_key_exists($key, $_COOKIE) && $_COOKIE[$key]!='' & $_COOKIE[$key]!=null & $overwrite=='empty')$flag=false;	
	
		if($flag){
			$_COOKIE[$key]=$value;
			if (!headers_sent()) {
				setcookie($key, $value,time()+60*60*24*30,'/');
			}
			echo('<script type=text/spa spa_activity=core:create_cookie days=30 key="' . $key . '" value="' . $value .  '"></script>');
		}	
}

		
static function set_session($key,$value,$overwrite='yes'){
	//php8OK	
	$flag=true;
	if (!isset($_SESSION)) return;
	if (array_key_exists($key, $_SESSION) && $overwrite=='no')$flag=false;
	if (array_key_exists($key, $_SESSION) && $_SESSION[$key]!='' & $_SESSION[$key]!=null & $overwrite=='empty')$flag=false;
	if($flag)$_SESSION[$key]=$value;	
	return $_SESSION[$key];
}

static function set_option($key,$value,$overwrite='yes'){
	//php8Amit	
	add_option( $key, $value, '', 'no' );
}


// -------------------------------------------------------------------------------------------------------------------------------
// implementation of get
static function get($main,&$atts=null,$content=null){
	//php8OK	

	$o=new stdClass();
	$o->main=$main;
	$o->atts=$atts;
	$o->content=$content;
	if(is_array($main))return 'array was passed to get';
	if(is_object($main))return 'object was passed to get';
	
	
	$o->pieces=self::safe_explode('.',$main);
	$o->value='';
	
	self::get_start($o);
	if(is_array($o->pieces) && (!empty($o->pieces)) ){
	while(count($o->pieces)>0) {
		
		$values=array_values($o->pieces);
		if($o->value=='_error' && end($values)==='exists'){
			return false;
		}
		
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
} 
	if($o->value==='_error') 
		$o->value='';

	return $o->value;
}

// Individual get functions

static function get_start($o){
	//php8OK
	
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
			//vikas:: Check with Monoframe
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
			self::aw2wpget('option',$o);
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
			self::aw2wpget('wpdb',$o);
			break;
		case 'post':
			array_shift($o->pieces);
			self::aw2wpget('post',$o);
			break;
		case 'wp_query':
			array_shift($o->pieces);
			self::aw2wpget('wp_query',$o);
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
		case 'unique_number_risky':
			array_shift($o->pieces);
			self::get_unique_number_risky($o); 
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
			self::aw2wpget('current_user',$o);
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

		case 'request2':
			array_shift($o->pieces);
			if(empty($o->pieces))
				$o->value=\aw2\request2\get(null);
			else{
				$o->value=\aw2\request2\get(['main'=>$o->pieces[0]]);
				if($o->value==null)
					$o->value='_error';
				array_shift($o->pieces);
			}
			break;
		case '@content_type':
			array_shift($o->pieces);
			$main=implode('.',$o->pieces);
			$o->value=\aw2\active_content_type\get(["main"=>$main],null,null);
			$o->pieces=array();
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
			self::aw2wpget('term_link',$o);
			unset($o->atts['slug']);
			unset($o->atts['taxonomy']);
			break;
		case 'term_meta':
			array_shift($o->pieces);
			self::aw2wpget('term_meta',$o);
			unset($o->atts['term_id']);
			unset($o->atts['key']);
			unset($o->atts['single']);
			break;
		case 'menu':
			array_shift($o->pieces);
			aw2_error_log::deprecated(['func'=>__FUNCTION__,'method'=>__METHOD__,'class'=>__CLASS__,'comment'=>"aw2.get menu is deprecated, use wp.menu"]);
			$o->value=aw2\wp\menu([],$o->content);
			break;
		case 'image_alt':
			array_shift($o->pieces);
			
			self::aw2wpget('image_alt',$o);
			
			unset($o->atts['post_id']);
			break;
		case 'attachment':
			array_shift($o->pieces);

			self::aw2wpget('attachment',$o);
			
			
			unset($o->atts['attachment_id']);
			
			break;
		case 'breadcrumb':
			array_shift($o->pieces);
			
			self::aw2wpget('breadcrumb',$o);
						
			unset($o->atts['main_menu_slug']);
			unset($o->atts['seperator']);
			break;
		case 'attachment_url':
			array_shift($o->pieces);
						
			self::aw2wpget('attachment_url',$o);
			
			unset($o->atts['size']);
			unset($o->atts['attachment_id']);
			break;
		case 'next_post':
			array_shift($o->pieces);				
			self::aw2wpget('next_post',$o);
			break;
		case 'prev_post':
			array_shift($o->pieces);
			self::aw2wpget('prev_post',$o);		
			
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
			self::aw2wpget('nonce',$o);
			break;		
		case 'denonce':
			array_shift($o->pieces);
			self::aw2wpget('denonce',$o);

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
			
			self::aw2wpget('sidebar',$o);
			
			break;
		case 'sideload_media':
			array_shift($o->pieces);
			self::aw2wpget('sideload_media',$o);
			
			break;
			
		case 'device_tokens':
			array_shift($o->pieces);
			self::get_device_tokens($o);
			break;	 
		
		case 'taxonomy_term_list':
			array_shift($o->pieces);
			self::aw2wpget('taxonomy_term_list',$o);

			break;
			
		default:
			if(isset(self::$stack['content_types'][$key])){
				self::get_content_type($o);
			}
			else{
				$o->value=self::$stack;
				$o->array_type='data';
			}
			break;
	}
}

static function aw2wpget($action,$o){
	

	$aw2wp_get=new aw2\wp\aw2wp_get($action,$o->atts,$o->content,$o->pieces);
	$o->pieces=array(); // resovle everthing in the wp.get 
	$o->value = $aw2wp_get->run();
	
}

static function get_content_type($o){
	//php8OK
	if(empty($o->pieces))return;
	$key=array_shift($o->pieces);
	
	self::load_content_type($key);
	$current=self::$stack['content_types'][$key];	
	

	
	while(!empty($o->pieces)){
		$key=array_shift($o->pieces);
		
		if(is_object($current) && get_class($current)==='ct'){
			if($current->code!==AW2_ERROR)
				$current= $current->code;
			else if($current->sql!==AW2_ERROR)
				$current= $current->sql;
			else
				$current= $current->value;
		}
		
		if(!is_object($current) && !is_array($current)){
			$current='';
			break;
		}
		
		if(is_object($current) && isset($current->$key)){
			$current=$current->$key;
		}
		
		if(is_array($current) && isset($current[$key])){
			$current=$current[$key];
		}
	}
	if(is_object($current) && get_class($current)==='ct'){
		if($current->code!==AW2_ERROR)
			$current= $current->code;
		else if($current->sql!==AW2_ERROR)
			$current= $current->sql;
		else
			$current= $current->value;
	}	
	$o->value=$current;
	
}

static function get_device_tokens($o){
	//php8Amit
	
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
	//php8Deprecated
	
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
	//php8Ok
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


static function get_url($o) {
	//php8Ok
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
			$o->value=self::get('env.settings.cdn');
			break;		
		case 'uploads':
			$o->value=wp_upload_dir()['baseurl'] . '/';
			break;
		case 'site':
			$o->value=SITE_URL . '/';
			break;
		case 'home':
			$o->value=HOME_URL . '/';
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
	//php8Amit
	
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
	//php8Ok
	
	//$amount isï¿½ how much of $what you want. 
	//$what is either paras, words, bytes or lists. 
	//$start is whether or not to start the result with Lorem ipsum dolor sit ametï¿½
	extract( self::shortcode_atts( array(
	'amount'=>30,
	'what'=>'words',
	'start'=>0
	), $o->atts) );
		
	$o->value=simplexml_load_file("http://www.lipsum.com/feed/xml?amount=$amount&what=$what&start=$start")->lipsum;
	return;	
}

static function get_now($o){
	//php8Ok
	$date_format=isset($o->atts['format'])?$o->atts['format']:'M d, Y';
	unset($o->atts['format']);
	$o->value=date($date_format);
	return ;
}

static function get_function($o){
	//php8Ok
	
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
	//php8Amit	
	if(!IS_WP)die('Not WP');
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
	//php8Ok
	$length=isset($o->atts['length'])?$o->atts['length']:12;
	$chars=isset($o->atts['chars'])?$o->atts['chars']:'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ;
	$o->value=substr( str_shuffle( $chars ), 0, $length );
	return;
}

static function get_aw2_secret( $o) {
	//php8Ok
	//php8amit
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
	//php8Ok	
	$s=hexdec(uniqid());
	$t=mt_rand(1000000, 9999999);
	$o->value=$s . $t;	
	return;
} 


static function get_unique_number_risky( $o) {
	//php8Ok
	$o->value=hexdec(uniqid());	
	return;
}


static function get_client($o){
	//php8Issue
	//Better to explicitly may $ref an array
	if(empty($o->pieces)){
		$o->value='_error';
		return;
	}	
	$content=self::clean_html(self::clean_specialchars($o->content));
	if(!is_null($content)){
		$content=str_replace ( "&#038;" , "&" ,$content );
		$content=str_replace("<script>", "", $content);
		$content=str_replace("</script>", "", $content);
		$content=str_replace("<style>", "", $content);
		$content=str_replace("</style>", "", $content);
	}
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

static function get_newdate($o){
	//php8ok		
	
	$format		= 	(isset($o->atts['format'])) ? $o->atts['format'] : "Y-m-d H:i:s";
	$from		=	(isset($o->atts['from'])) ? $o->atts['from'] : "";
	$duration	=	(isset($o->atts['duration'])) ? $o->atts['duration'] : "+1 day";

	$o->value=date($format,strtotime($from . " $duration"));
	
	return;
}

static function resolve_array($o){
	//php8ok		
	
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
	//php8ok		
	
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
	//php8ok		
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

			if(count($arr)>0)
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
	//php8ok		
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

			$string= awesome_wp_utils::the_content_filter($string);
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
			$o->value=self::safe_explode(',', self::safe_trim($string));
			$o->value=array_map('trim',$o->value);
			break;
		case 'dot':
			array_shift($o->pieces);
			$o->value=self::safe_explode('.', self::safe_trim($string));
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
			$o->value=self::safe_explode(' ',trim($string));
			$o->value=array_map('trim',$o->value);
			break;
		case 'trim':
			array_shift($o->pieces);
			$o->value=self::safe_trim($string);
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
	//php8ok		
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
	//php8ok		
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
	//php8ok		
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
			$o->value= is_array($img)?$img[0]:''; 
			break;
		case 'excerpt':
			array_shift($o->pieces);
			$length=isset($o->atts['length'])?$o->atts['length']:20;
			$ellipsis=isset($o->atts['ellipsis'])?' &hellip; &nbsp;':'';
			$o->value= awesome_wp_utils::pippin_excerpt_by_id($ID,$length,'<a><em><strong>',$ellipsis);
			break;
		case 'the_content':
			array_shift($o->pieces);
			$content = $post->post_content;
			//$content = apply_filters('the_content', $content);
			$content= awesome_wp_utils::the_content_filter($content);
			
			$o->value= do_shortcode($content);
			break;
		case 'parse_content':
			array_shift($o->pieces);
			$content = $post->post_content;
			$o->value= self::parse_shortcode($content);
			$o->value= awesome_wp_utils::the_content_filter($o->value);
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
	//php8ok		
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
	//php8ok		
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
	//php8ok		
	aw2_error_log::deprecated(['func'=>__FUNCTION__,'method'=>__METHOD__,'class'=>__CLASS__]);
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
	//php8ok		
	aw2_error_log::deprecated(['func'=>__FUNCTION__,'method'=>__METHOD__,'class'=>__CLASS__]);
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
	//php8ok	
	util::var_dump(self::$stack);
}

static function env_key_exists($keys){
	//php8ok	
	$current=self::$stack;		
	if(!is_array($keys))$keys=self::safe_explode('.',$keys);	
	if($keys==='env')array_shift($keys);

	while(!empty($keys)){
		$key=array_shift($keys);
		if(isset($current[$key]))	$current=$current[$key];
		else if(isset($current->$key))$current=$current->$key;
		else	return AW2_ERROR;
	}
	return $current;
	
}

static function get_request($main=null){
	//php8ok	
	$value=null;
	if(empty($main))
		return $_REQUEST;
	if($main==='request_body'){
			$value = file_get_contents('php://input');
			return $value;
	}

	if($main==='post_json'){
			$value = json_encode($_POST);
			return $value;			
	}
	
	
	if(IS_WP && get_query_var($main)){
		$value=get_query_var($main);
	}
	else{
		if(isset($_REQUEST[$main])){
			$value=$_REQUEST[$main];
		}
	}
	return $value;	
}


static function in_array_r($needle, $haystack, $strict = false) {
	//php8ok		
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}


static function removesmartquotes($content) {
	//php8ok	
	if(is_null($content)) return $content;	
     $content = str_replace('&#8220;', ",", $content);
     $content = str_replace('&#8221;', "'", $content);
     $content = str_replace('&#8216;', '"', $content);
     $content = str_replace('&#8217;', '"', $content);
     
     return $content;
}


/* Code Added by Ani - Start*/

	
	static function sideload_file($url, $post_id){
	//php8amit		
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
	//php8amit		
		$parameters = array();
		$i=1;
		$found=true;
		while ($found===true) {
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


static function get_collection($collection){
	//php8ok		
	if(!isset($collection['connection']))$collection['connection']='#default';
	$connection_arr=self::$stack['code_connections'][$collection['connection']];
	
	$connection_service = '\\aw2\\'.$connection_arr['connection_service'].'\\collection\\get';
		
	$atts['connection']=$collection['connection'];
	$atts['post_type']=$collection['post_type'];
	$modules = call_user_func($connection_service,$atts);
	foreach ($modules as $key=>$value) {
		$modules[$key]['collection']=$collection;
	}
	return $modules;

}

	
static function module_exists_in_collection($collection,$module){
	//util::var_dump($collection);
	if(!isset($collection['connection']))$collection['connection']='#default';
	$connection_arr=self::$stack['code_connections'][$collection['connection']];

	//\aw2\wp_conn\module\exists

		$connection_service = '\\aw2\\'.$connection_arr['connection_service'].'\\module\\exists';

		$atts['connection']=$collection['connection'];
		$atts['post_type']=$collection['post_type'];
		$atts['module']=$module;

		$arr = call_user_func($connection_service,$atts);
		return $arr;

}

static function get_module_meta($collection,$module){
	if(!isset($collection['connection']))$collection['connection']='#default';
	$connection_arr=self::$stack['code_connections'][$collection['connection']];
	

		$connection_service = '\\aw2\\'.$connection_arr['connection_service'].'\\module\\meta';
	
		$atts['connection']=$collection['connection'];
		$atts['post_type']=$collection['post_type'];
		$atts['module']=$module;

		$arr = call_user_func($connection_service,$atts);
		return $arr;
		
}

static function get_module($collection,$module){

	//check the location
	if(!isset($collection['connection']))$collection['connection']='#default';
	$connection_arr=self::$stack['code_connections'][$collection['connection']];

	if(isset($collection['app']))
		$collection['post_type']=self::$stack['apps'][$collection['app']]['collection']['modules']['post_type'];


	if(isset($collection['post_type'])){
		$connection_service = '\\aw2\\'.$connection_arr['connection_service'].'\\module\\get';
		
		$atts['connection']=$collection['connection'];
		$atts['post_type']=$collection['post_type'];
		$atts['module']=$module;

		$arr = call_user_func($connection_service,$atts);
		if(!isset($arr['module']))return null;
		
		$arr['collection']=$collection;
		return $arr;
			
	}

/*
	if(isset($collection['app'])){
		$post_type=self::$stack['apps'][$collection['app']]['collection']['modules']['post_type'];
		
		$hash=$post_type . '_' . $module;
		$return_value=null;
		//check cache
		if(USE_ENV_CACHE){
			$return_value=aw2\global_cache\get(["main"=>$hash,"prefix"=>"module"],null,null);
		}
		
		if(!$return_value){
			$sql="select post_content,post_type,ID,post_name,post_title from ".$table_prefix."posts where post_type='" . $post_type . "' and post_name='" . $module . "'";
			$results =self::get_results($sql);		
			
			if(count($results)!==1)return null;
			$arr=array();
			$arr['module']=$results[0]['post_name'];
			$arr['title']=$results[0]['post_title'];
			$arr['id']=$results[0]['ID'];
			$arr['code']=$results[0]['post_content'];
			$arr['post_type']=$results[0]['post_type'];
			
			$arr['collection']=$collection;
			$arr['hash']=$hash;		
			if(SET_ENV_CACHE)aw2\global_cache\set(["key"=>$hash,"prefix"=>"module"],json_encode($arr),null);
			if(defined('SET_DEBUG_CACHE') && SET_DEBUG_CACHE){
				$fields = array('last_accessed'=>date('Y-m-d H:i:s'));
				
				aw2\debug_cache\set_access_post_type(["post_type"=>$arr['post_type'],"fields"=>$fields],'',null);
				aw2\debug_cache\set_access_module(["post_type"=>$arr['post_type'],"module"=>$arr['module'],"fields"=>$fields],'',null);
				
				if(isset(self::$stack['app'][$collection['app']])){	
					$app_slug = self::$stack['apps'][$collection['app']]['slug'];
					$fields['app_name']= self::$stack['apps'][$collection['app']]['name'];
					aw2\debug_cache\set_access_app(["app"=>$app_slug,"fields"=>$fields],'',null);
					
				}
				unset($fields);
			}	
		}
		else{
			$arr=json_decode($return_value,true);
		}
		
		return $arr;
	}
*/
	
	if(isset($collection['source'])){
		$hash=$collection['source'] . '_' . $module;
		$return_value=null;
		//check cache
		if(USE_ENV_CACHE){
			$return_value=aw2\global_cache\get(["main"=>$hash,"prefix"=>"module"],null,null);
		}
		
		if(!$return_value){
			$path=$collection['source'] . '/' . $module . '.module.html';
			$code = file_get_contents($path);
			
			$arr=array();
			$arr['module']=$module;
			$arr['title']=$module;
			$arr['id']=$module;
			$arr['code']=$code;
			$arr['source']=$collection['source'];
			
			$arr['collection']=$collection;
			$arr['hash']=$hash;		
			if(SET_ENV_CACHE)aw2\global_cache\set(["key"=>$hash,"prefix"=>"module"],json_encode($arr),null);
		}
		else{
			$arr=json_decode($return_value,true);
		}
		
		return $arr;
	}
	
}


static function get_post_meta($post_id,$meta_key=null){
	//php8ok		
	$hash='meta' . '_' . $post_id;
	
	$return_value=null;
	//check cache
	if(USE_ENV_CACHE){
		$return_value=aw2\global_cache\get(["main"=>$hash,"prefix"=>""],null,null);
	}
	
	if(!$return_value){
		$sql="select post_id,meta_key,meta_value from  wp_postmeta  where post_id='" . $post_id . "'";
		$results =self::get_results($sql);				


		$metas=array();
		
		foreach ($results as $result) {
			$metas[$result['meta_key']]=$result['meta_value'];
		}
		if(SET_ENV_CACHE)aw2\global_cache\set(["key"=>$hash,"prefix"=>""],json_encode($metas),null);
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
	//php8ok		
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
	//php8ok		
	
	$arr=self::get_module($collection,$module);
	if(!$arr){
		$html=self::dump_debug(
		[
			[
				'type'=>'html',
				'value'	=>"Module:: $module"
			],
			[
				'type'=>'html',
				'value'	=>"Template:: $template"
			],
			[
				'type'=>'html',
				'value'	=>"Collection Array"
			],
			[
				'type'=>'arr',
				'value'	=>$collection
			],
			[
				'type'=>'html',
				'value'	=>'Stack Array'
			],
			[
				'type'=>'stack',
				'value'	=>self::get_array_ref('call_stack')
			]
		]		
		,
		"Module not found (module_forced_run)"
		);
		
		if(AWESOME_DEBUG)\aw2\debugbar\html(['value'=>$html,'tab'=>'error']);			
		return "$module Module not found " . self::convert_name_value_string($collection);
	}	
	//echo 'module::' . $module . 'collection:: ' . $collection['post_type'] . '<br />';

	$stack_id=self::module_push($arr);
	
	$sc_exec=&self::get_array_ref('@sc_exec');
	$restore=$sc_exec;	
	$sc_exec['collection']=$collection;
	$sc_exec['module']=$module;
	
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

	if(AWESOME_DEBUG)\aw2\debug\module(['start'=>$start,'template'=>$template]);	

	aw2_library::pop_child($stack_id);

	$sc_exec=&self::get_array_ref('@sc_exec');
	$sc_exec=$restore;
	
	return $return_value;	
}


static function module_run($collection,$module,$template=null,$content=null,$atts=null){
	//php8ok		
	$arr=self::get_module($collection,$module);
	
	/*
	parse_content=array
	Build the array . and merge with atts
	
	parse_content=string
	$content=parse the string  

	*/ 
	
	if(!$arr){
		$html=self::dump_debug(
		[
			[
				'type'=>'html',
				'value'	=>"Module:: $module"
			],
			[
				'type'=>'html',
				'value'	=>"Template:: $template"
			],
			[
				'type'=>'html',
				'value'	=>"Collection Array"
			],
			[
				'type'=>'arr',
				'value'	=>$collection
			],
			[
				'type'=>'html',
				'value'	=>'Stack Array'
			],
			[
				'type'=>'stack',
				'value'	=>self::get_array_ref('call_stack')
			]
		]		
		,
		"Module not found (module_run)"
		);
		
		if(AWESOME_DEBUG)\aw2\debugbar\html(['value'=>$html,'tab'=>'error']);
		
		return "$module Module not found " . self::convert_name_value_string($collection);
	}

	if(defined('AWESOME_LOG_USAGE') && AWESOME_LOG_USAGE == "yes"){
		require_once('usage_log.php');
		$log = aw2_usage_log::log_usage($collection, $module);
	}

	//echo 'module::' . $module . 'collection:: ' . $collection['post_type'] . '<br />';
	$stack_id=self::module_push($arr);
	
	
	$sc_exec=&self::get_array_ref('@sc_exec');
	$restore=$sc_exec;	
	$sc_exec['collection']=$collection;
	$sc_exec['module']=$module;
	
	
	if(!$template){
		if($content){
			$content=self::removesmartquotes($content);	
			self::parse_shortcode($content);
		}
		self::push_this($stack_id);
		self::push_atts($stack_id,$atts);
	}

	$return_value='';
	if(isset($arr['code'])){
	   $return_value=self::parse_shortcode($arr['code']);
	}

	if(isset(self::$stack['module']['templates']['main']) && !$template){
		$return_value=self::template_run('main');
	}
	
	if($template)$return_value=self::template_run($template,$content,$atts);
	
	if(isset(self::$stack['module']['_return'])){
		unset(self::$stack['_return']);
		$return_value=self::$stack['module']['_return'];
	}

	if(AWESOME_DEBUG)\aw2\debug\module(['start'=>$start,'template'=>$template]);	
	
	aw2_library::pop_child($stack_id);
	
	$sc_exec=&self::get_array_ref('@sc_exec');
	$sc_exec=$restore;	
	
	return $return_value;	
}


static function service_template_run($template,$atts=array()){
	//php8ok		
	$stack_id=self::push_child('template',$template['name']);
	
	self::push_this($stack_id);
	self::push_atts($stack_id,$atts);

	$sc_exec=&self::get_array_ref('@sc_exec');
	$restore=$sc_exec;	
	if(isset($template['module']))$sc_exec['module']=$template['module'];
	if(isset($template['collection']))$sc_exec['collection']=$template['collection'];
	if(isset($template['content_pos']))$sc_exec['start_pos']=$template['content_pos'];
		

	
	$return_value=self::parse_shortcode($template['code']);
	if(isset(self::$stack['template']['_return'])){
		unset(self::$stack['_return']);
		$return_value=self::$stack['template']['_return'];
	}
	
	$sc_exec=&self::get_array_ref('@sc_exec');
	$sc_exec=$restore;	
	
	aw2_library::pop_child($stack_id);
	return $return_value;	
}

static function template_run($template,$content=null,$atts=array()){
	//php8ok		
	$content=self::removesmartquotes($content);		
	if(!isset(self::$stack['module']['templates'][$template]))return 'Template not found - '.$template ;
	$template_ptr=self::$stack['module']['templates'][$template];
	$stack_id=self::push_child('template',$template_ptr['name']);
	
	if($content)self::parse_shortcode($content);
	self::push_this($stack_id);
	self::push_atts($stack_id,$atts);

	if(isset($template_ptr['content_pos'])){
		$sc_exec=&self::get_array_ref('@sc_exec');
		$restore=$sc_exec;	
		$sc_exec['start_pos']=$template_ptr['content_pos'];
	}
	
	
	
	$return_value=self::parse_shortcode($template_ptr['code']);
	
	if(isset($template_ptr['content_pos'])){
		$sc_exec=&self::get_array_ref('@sc_exec');
		$sc_exec=$restore;
	}

	
	if(isset(self::$stack['template']['_return'])){
		unset(self::$stack['_return']);
		$return_value=self::$stack['template']['_return'];
	}
	aw2_library::pop_child($stack_id);
	return $return_value;	
}

static function module_include($collection,$module){
	//php8ok		
	$arr=self::get_module($collection,$module);
		if(!$arr){
			$html=self::dump_debug(
			[
				[
					'type'=>'html',
					'value'	=>"Module:: $module"
				],
				[
					'type'=>'html',
					'value'	=>"Collection Array"
				],
				[
					'type'=>'arr',
					'value'	=>$collection
				],
				[
					'type'=>'html',
					'value'	=>'Stack Array'
				],
				[
					'type'=>'stack',
					'value'	=>self::get_array_ref('call_stack')
				]
			]		
			,
			"Module not found (module_include)"
			);
			
			if(AWESOME_DEBUG)\aw2\debugbar\html(['value'=>$html,'tab'=>'error']);			
			return "$module Module not found " . self::convert_name_value_string($collection);
		}	
		
		//echo 'module::' . $module . 'collection:: ' . $collection['post_type'] . '<br />';
	$return_value='';
	if(isset($arr['code'])){
		$return_value=self::parse_shortcode($arr['code']);
	}
		
	return $return_value;	
}

static function module_include_raw($collection,$module){
	//php8ok	
	$arr=self::get_module($collection,$module);
		if(!$arr)return "$module Module not found " . self::convert_name_value_string($collection);
	$return_value=$arr['code'];	
	return $return_value;	
}


//registeration of modules



static function load_content_type($field){
	//php8ok	
	$content_types=&self::get_array_ref('content_types');
	if(!isset($content_types[$field]))return;
	if($content_types[$field]==='#cached'){
		$data=aw2\global_cache\hget(["main"=>ENV_CACHE,"field"=>"#ct_" . $field]);
		$content_types[$field]=unserialize($data);
	}	
	return;	
}


static function setup_develop_for_awesomeui(){
	//php8ok
	//Are you a developer. A developer has certain priveleges
	//setup DEVELOP_FOR_AWESOMEUI
	if(isset($_COOKIE['develop_for_awesomeui'])){
		$develop_for_awesomeui=$_COOKIE['develop_for_awesomeui'];
		$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		if($redis->exists($develop_for_awesomeui)){
			$result=$redis->get($develop_for_awesomeui);
			if($result==='develop_for_awesomeui')define('DEVELOP_FOR_AWESOMEUI', true);
		}
	}
	else
		define('DEVELOP_FOR_AWESOMEUI', false);
}

static function setup_env_cache($key){
	//php8ok
	//Setting up the caching and the caching variables
	// ENV_CACHE - where to store the cache
	// USE_ENV_CACHE - Whether to Use the Env Cache or not
	// DEL_ENV_CACHE - Whether to Del the Env Cache or not
	// SET_ENV_CACHE - Whether to Set the Env Cache or not

	define('ENV_CACHE', $key);

	/*
	DEVELOP_FOR_AWESOMEUI can use the env cache. This allows him to not have to load cache unnecessarily and also test with cache on
	*/
	
	//USE_ENV_CACHE
	if(isset($_REQUEST['use_env_cache']) && $_REQUEST['use_env_cache']==='no'){
		setcookie("use_env_cache", 'no', time()+3600,'/','',true,true);
		define('USE_ENV_CACHE', false);
	}
	else{
		if(isset($_COOKIE['use_env_cache']) && $_COOKIE['use_env_cache']==='no')
			define('USE_ENV_CACHE', false);	
		else
			define('USE_ENV_CACHE', true);	
	}

	if(isset($_REQUEST['use_env_cache']) && $_REQUEST['use_env_cache']==='delete_cookie'){
		setcookie("use_env_cache", "", time() - 3600,'/','',true,true);
	}


	//DEL_ENV_CACHE
	if(DEVELOP_FOR_AWESOMEUI && isset($_REQUEST['del_env_cache']))
		define('DEL_ENV_CACHE', true);
	else
		define('DEL_ENV_CACHE', false);	

		
	//should we set cache or not 
	/*
	DEVELOP_FOR_AWESOMEUI cannot Set the Env Cache because he may have corrupted the environment 
	If you are not Using the Cache then you cannot setup the cache either
	*/
	if(DEVELOP_FOR_AWESOMEUI || !USE_ENV_CACHE)
		define('SET_ENV_CACHE', false);
	else
		define('SET_ENV_CACHE', true); 	

	
}


static function split_array_on($atts,$on){
	
	$arr=array();	
	foreach ($atts as $key=>$value){
		$parts=explode('.',$key,2);
		if(count($parts)===2 and $parts[0]===$on){
			$arr[$parts[1]]=$value;
			unset($atts[$key]);
		}
	}
	$atts[$on]=$arr;	
	return $atts;
}

static function redirect($location,$status=302){

	if ( ! $location ) return false;

	if(!IS_WP) {
		header( "Location: $location", true, $status );
		return true;
	}

	return wp_redirect($location,$status);
}

static function safe_explode($separator, $string_value){
	if(is_null($string_value))
		$string_value = (string) $string_value;

	return explode($separator, $string_value);	
}
static function safe_trim($string_value){
	if(is_null($string_value))
		$string_value = (string) $string_value;
		
	return trim($string_value);
}
static function safe_ltrim($string_value){
	if(is_null($string_value))
		$string_value = (string) $string_value;

	return ltrim($string_value);
}
	
}


class array_builder{
public $str;
public $stack=array();
public $arr=array();
public $ptr=null;
public $is_api=false;
//public $ctr=0;

public function parse($str){
	
	$this->str=$str;
	
	while (!ctype_space($this->str) && $this->str!=='') {
		//$this->ctr++;
		//d('counter',$this->ctr); 
		//if($this->ctr>=50)die();
		if(empty($this->stack)){
			$this->next_element();
		}
		else{
			$this->within_element();

		}
	}
	if(!empty($this->stack)){
		echo '<br>You have nodes which have not been completed: ';
		util::var_dump($this->stack);
		return '';
	}
	return $this->arr;
}	

private function next_element(){
	//$pattern = '/\s*\[([a-zA-Z].*?)(\/]|])/';
	
	$pattern = '/\s*\[([a-zA-Z0-9_\-@]*(?:(?:\s*)|(?:\s.*?)))(\/]|])/';
	// <whitespace>[<atleast one character><any thing lazy>(optional /)] 
	$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);

	
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>No elements found in the above string.';
		die();
	}
	$text=$match[1][0];	
	
	$state=$match[2][0];
	$next_char= strlen($match[2][0]) + $match[2][1];	
	$this->str=substr($this->str,$next_char);

	$this->new_node($text,$state);
}

//Extract all the attributes of the node
private function new_node($text,$state){
	$atts=array();
	$pattern = '/([-a-zA-Z0-9_.]+)\s*=\s*"([^"]*)"(?:\s|$)|([-a-zA-Z0-9_.]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([-a-zA-Z0-9_.]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
	
	$reply=preg_match_all($pattern, $text, $match, PREG_SET_ORDER);
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>The string being parsed: ' . $text;
		echo '<br>. Something is wrong with the above text';
		die();
	}
	
	//extract all the attributes
	
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
	//decide the name of the item
	$item_name=$atts[0]; 
	unset($atts[0]);
	
	//pre actions - the type attribute will now be main
	//if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	aw2_library::pre_action_parse($atts);
	$reply=aw2_library::checkcondition($atts);
	//decide whether we want to keep this node
	if($reply==false){
		//we dont want to keep this node
		if ($state=='/]'){
			//it is a self closing node
			return;
		}
			//open node
		$this->remove_named_node($item_name);
		return;
	}
	
	//decide where the array will go
	$last_node=end($this->stack);
	if($last_node)
		$ptr=&$last_node->ptr;
	else
		$ptr=&$this->arr; 


		
	
	//it is do. It has to be executed
	if($item_name=='do'){
		

		$pattern = '/^((?s:.*?))(\[\/do\])/';
		$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
		if(!$reply){
			echo '<br>Remaining String.' . $this->str;
			echo '<br>. Something is wrong. A do was not closed';
			die();
		}
		$do=$match[1][0];
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);
		$result=aw2_library::parse_shortcode($do);

		if(array_key_exists('_return',aw2_library::$stack)){
			$result=aw2_library::$stack['_return'];
			unset(aw2_library::$stack['_return']);
		}
	
		if(is_array($result)){
			if(!is_array($ptr))$ptr=array();

			foreach ($result as $key => $value) {
				if(array_key_exists($key,$ptr) &&  is_array($ptr[$key])){
					$ptr[$key]=array_merge($ptr[$key],$value);
				}
				else	
					$ptr[$key]=$value;
			}
		}
		else
			$this->str=$result . $this->str ;

		return;
	}

	
	if(!is_array($ptr))$ptr=array();
	$raw=false;			
	
	
	if($item_name!='atts'){
		if(!array_key_exists($item_name,$ptr)){
			$ptr[$item_name]=null;
		}	
		$ptr=&$ptr[$item_name];
		
		if(array_key_exists('main',$atts)){
			$type=$atts['main'];
			unset($atts['main']);
			
			if($type=='raw'){
				$raw=true;
			}
			else{
				switch ($type) {
					case 'new':
						$ptr[]=null;
						break;
					default:
						$ptr[$type]=null;
						break;
				}
				end($ptr); 
				$ptr=&$ptr[key($ptr)];
			}
		}	
	}

	if(!empty($atts)){
		foreach ($atts as $key => $value) {
			if($key==='_value'){
				$ptr=null;
				$ptr=$value;
			}
			else		
				$ptr[$key]=$value;
		}
	}
	
	if($state==']'){
		$o=new stdClass();
		$o->element_type='OPEN';
		$o->element_name=$item_name;
		$o->ptr=&$ptr;
		$o->raw=$raw;
		
		$this->stack[]=$o;
	}
	
}


private function within_element(){
	$last_node=end($this->stack);
	$name=$last_node->element_name;
	
	if($last_node->raw){
		
		$pattern = '/((?s:.*?))(\[\/' . $name . '\])/';
		$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
		if(!$reply){
			echo '<br>Remaining String.' . $this->str;
			echo '<br>Raw element was started but not ended';
			echo '<br>' . $name ;
			die();
		}
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);		
		$last_node->ptr=$match[1][0];
		array_pop($this->stack);
		return;
	}
	
	//$pattern = '/^(?:\s*\[raw\]((?s:.*?))\[\/raw\]\s*(\[\/' . $name .'\]))|(?:\s*\[([a-zA-Z].*?)(\/]|]))|(?s:(.*?)(\[\/' . $name .'\]))/s';	
		$pattern = '/^(?:\s*\[raw\]((?s:.*?))\[\/raw\]\s*(\[\/' . $name .'\]))|\s*\[([a-zA-Z0-9_\-@]*(?:(?:\s*)|(?:\s.*?)))(\/]|])|(?s:(.*?)(\[\/' . $name .'\]))/s';	
	$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>Something went wrong within the content of the element';
		echo '<br>' . $name ;
		
		die();
	}

	//there is a raw node
	if(!empty($match[2][0])){
		$last_node=end($this->stack);
		if(is_array($last_node->ptr))
			;
		else	
			$last_node->ptr=$match[1][0];
		
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);
		array_pop($this->stack);
	}
	
	//there is a child node
	if(!empty($match[4][0])){
		$next_char= strlen($match[4][0]) + $match[4][1];	
		$this->str=substr($this->str,$next_char);
		$this->new_node($match[3][0],$match[4][0]);
	}
	
	//open node being closed
	if(!empty($match[6][0])){
		$last_node=end($this->stack);
		if(is_array($last_node->ptr))
			;
		else{
			$last_node->ptr=$match[5][0];
			$pos = strrpos($last_node->ptr, "[");
			if ($pos !== false)$last_node->ptr=aw2_library::parse_shortcode($last_node->ptr);
		}	
		$next_char= strlen($match[6][0]) + $match[6][1];	
		$this->str=substr($this->str,$next_char);


		array_pop($this->stack);
	}
}


private function remove_named_node($item_name){
	$flag=1;		

	while ($flag>0) {
		$match=$this->find_next_named_node($item_name);
		if(!empty($match[1][0])){
			//found a opening node
			$flag=$flag + 1;
			$next_char= strlen($match[1][0]) + $match[1][1];	
			$this->str=substr($this->str,$next_char);
		}
		else{
			//close was found
			$flag=$flag - 1;
			$next_char= strlen($match[2][0]) + $match[2][1];	
			$this->str=substr($this->str,$next_char);
			if($flag==0)return;//found matching closing node
		}
	}
}

private function find_next_named_node($item_name){
	$pattern = '/((?:\[' . $item_name .'\s.*?])|(?:\[' . $item_name .'])|(?:\[' . $item_name .'\/]))|(\[\/' . $item_name .'])/';
	$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>Was expecting to find a closing ' . $item_name . ' .Not Found';
		die();
	}
	return $match;
}



}


function period_date($str){
	if($str=="")return;
	
	if( strpos( $str, ":" ) === false ) {
		return;
	}
	
	$str_arr=explode(":",$str);
	switch ($str_arr[0]) {
		
		case "day":
					$period_str="-".$str_arr[1]." days";
					
					$period_start_str=$period_str;
					$period_end_str=$period_str;
						
					if($str_arr[1]=="today" || $str_arr[1]=="yesterday"){
						$period_start_str=$str_arr[1];
						$period_end_str=$str_arr[1];
					}				
					break;
		case "days":	
					$period_start_str="-".$str_arr[1]." days";
					$period_end_str="today";
					break;		
		case "months":
					$period_start_str="first day of -".$str_arr[1]." months";
					$period_end_str="today";
					break;
		case "month":										
					$period_start_str="first day of -".$str_arr[1]." month";
					$period_end_str="last day of -".$str_arr[1]." month";
					
					if($str_arr[1]=="last_month"){
						$period_start_str="first day of last month";
						$period_end_str="last day of last month";
					}	
					if($str_arr[1]=="this_month" ){
						$period_start_str="first day of this month";
						$period_end_str="today";
					}	
					break;
		case "year":
					if($str_arr[1]=="last_year"){
						$period_start_str="last year January 1st";
						$period_end_str="last year December 31st";
					}	
					if($str_arr[1]=="this_year" ){
						$period_start_str="this year January 1st";
						$period_end_str="today";
					}	
					break;			
		default:
					$period_start_str= "today";
					$period_end_str= "today";
	}
	
	$start_time = strtotime($period_start_str);
	$end_time = strtotime($period_end_str);
	
	$rs=array();
	$rs['start_date'] = date('YmdHis',$start_time);
	$rs['end_date'] = date('YmdHis',$end_time);

	return $rs;
}	

if(!IS_WP)
{
	function stripslashes_deep( $value ) {
		return map_deep( $value, 'stripslashes_from_strings_only' );
	}

	function map_deep( $value, $callback ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$value[ $index ] = map_deep( $item, $callback );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
				$value->$property_name = map_deep( $property_value, $callback );
			}
		} else {
			$value = call_user_func( $callback, $value );
		}
	 
		return $value;
	}

	function stripslashes_from_strings_only( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
	
	function current_user_can($cap){
		
		if(!isset($_COOKIE['wp_vsession'])) return false;
		
		$vsession=\aw2\vsession\get(['id'=>'wp_vsession'],null,'');
		
		if(!isset($vsession['user'])) return false;
			
		//check the status and roles are matching then allow the pass
		$user= json_decode($vsession['user'],true);		
				
		if(!empty($cap) && in_array($cap,$user['allcaps'])) return true;
		
		return false;
	
	}
	
	function is_user_logged_in(){
		
		if(!isset($_COOKIE['wp_vsession'])) return false;
		
		$vsession=\aw2\vsession\get(['id'=>'wp_vsession'],null,'');
		
		if(!isset($vsession['user'])) return false;
			
		//check the status and roles are matching then allow the pass
		$user= json_decode($vsession['user'],true);		
				
		if(!empty($user['login'])) return true;
		
		return false;
	
	}

	function wp_login_url(){
		if(!defined('WP_LOGIN_URL')){
			define('WP_LOGIN_URL',site_url('wp-login.php'));
		}
		return WP_LOGIN_URL;
	}

	function site_url( $path = '', $scheme = null ) {
		$url = SITE_URL;
		if ( $path && is_string( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}
		return $url;
	}
}	
