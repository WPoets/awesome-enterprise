<?php

namespace aw2\request2;

\aw2_library::add_service('request2','Request Library',['namespace'=>__NAMESPACE__]);
function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	$main=array_shift($shortcode['tags_left']);
	$return_value=get_request($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('get','Get the request from URL',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts) );
	
	$return_value=get_request($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('dump','Get the request from URL and dump',['namespace'=>__NAMESPACE__]);
function dump($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts) );

	$return_value=get_request($main);
	$return_value=\util::var_dump($return_value,true);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

\aw2_library::add_service('echo','Echo the request from URL',['func'=>'_echo','namespace'=>__NAMESPACE__]);
function _echo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts) );

	$return_value=get_request($main);
	\util::var_dump($return_value);
	return;
}

function get_request($main){
	if($main === 'request_body'){
		$value = file_get_contents('php://input');
		return xss_clean($value);
	}
	
	if($main === 'post_json'){
		$post = $_POST;
		array_walk_recursive(
			$post,
			function(&$item,$key){
				$item=xss_clean($item);
			}
		);
		return json_encode($post);
	}
		
	if(array_key_exists($main, $_REQUEST)){
		
		$return_value=$_REQUEST[$main];
		
		if(is_array($return_value)){
			array_walk_recursive(
				$return_value,
				function(&$item,$key){
					$item=xss_clean($item);
				}
			);
		}else{
			$return_value=xss_clean($return_value);
		}		
		return $return_value;
	}
		
	if(empty($main) && !empty($_REQUEST)){
		$return_value = $_REQUEST;
		array_walk_recursive(
			$return_value,
			function(&$item,$key){
				$item=xss_clean($item);
			}
		);
		return $return_value;
	}
	return;
}


function xss_clean($data)
{
	$data=stripslashes(trim($data));
	$badwordchars=array( 
		chr(145), 
		chr(146), 
		chr(147), 
		chr(148), 
		chr(151) 
	); 
											
	$fixedwordchars=array( 
		"'", 
		"'", 
		'"', 
		'"', 
		'-' 
	);
											
	$data=str_replace($badwordchars,$fixedwordchars,$data);

	$data=unicode_decode($data);
	
	

											
	// Fix &entity\n;
	$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');


	
	// Remove any attribute starting with "on" or xmlns
	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	// Remove javascript: and vbscript: protocols
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
 
	// Remove namespaced elements (we do not need them)
	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

	do
	{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	}
	while ($old_data !== $data);

	$_never_allowed_str =	array(
		'document.cookie' => '[removed]',
		'(document).cookie' => '[removed]',
		'document.write'  => '[removed]',
		'(document).write'  => '[removed]',
		'.parentNode'     => '[removed]',
		'.innerHTML'      => '[removed]',
		'-moz-binding'    => '[removed]',
		'<!--'            => '[removed]',
		'-->'             => '[removed]',
		'<![CDATA['       => '[removed]',
		'<comment>'	  => '[removed]',
		'<%'              => '&lt;&#37;'
	);
	//$data = str_replace(array_keys($_never_allowed_str), $_never_allowed_str, $data);

	$_never_allowed_regex = array(
		'javascript\s*:',
		'(\(?document\)?|\(?window\)?(\.document)?)\.(location|on\w*)',
		'expression\s*(\(|&\#40;)', // CSS and IE
		'vbscript\s*:', // IE, surprise!
		'wscript\s*:', // IE
		'jscript\s*:', // IE
		'vbs\s*:', // IE
		'Redirect\s+30\d',
		"([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
	);

	foreach ($_never_allowed_regex as $regex)
	{
		//$data = preg_replace('#'.$regex.'#is', '[removed]', $data);
	}	
	
	// we are done...
	$data=filter_var($data, FILTER_DEFAULT, FILTER_FLAG_STRIP_HIGH);
	
	return $data;
}


function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

function unicode_decode($str) {
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', '\aw2\request2\replace_unicode_escape_sequence', $str);
}