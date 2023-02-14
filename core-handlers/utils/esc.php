<?php

namespace aw2\esc;

\aw2_library::add_service('esc.unsafe','Escape the Value and Return',['namespace'=>__NAMESPACE__]);
function unsafe($atts,$content=null,$shortcode){

	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );
	
	$main = \aw2_library::get($main);
	
    if(isset($shortcode['tags_left'][0])){
		$action=$shortcode['tags_left'][0];

		if($action==='quotes')
			$return_value=unsafe_quotes($main);
			
	}
	else{
		//nothing was set
		//**If an array then loop and escape the whole array and return comma separated with  quote**//
		if(is_array($main)){
			
			$arr = array();
			
			//Loop and escape the whole array
			foreach($main as $k=>$v){
				$arr[]=mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]','\\\0',$v);
			}
			
			//comma separated
			$return_value=implode ( "," , $arr );

		}else{
			$return_value=mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]','\\\0',$main);
		}
	}
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function unsafe_quotes($main){

	//**If an array then loop and escape the whole array and return comma separated with quotes**//
	if(is_array($main)){
		
		$arr = array();
		
		//Loop and escape the whole array
		foreach($main as $k=>$v){
			$arr[]=mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]','\\\0',$v);
		}
		
		//comma separated with  quote
		if(count($arr)<1)
			$return_value='';
		
		if(count($arr)==1)
			$return_value="'" . $arr[0] . "'";
			
		if(count($arr)>1)
			$return_value="'" . implode ( "','" , $arr ) . "'";

	}else{
		$return_value="'" . mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]','\\\0',$main) . "'";
	}
		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.safe','Return an already escaped Value',['namespace'=>__NAMESPACE__]);
function safe($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);    
	
	if(isset($shortcode['tags_left'][0])){
		$action=$shortcode['tags_left'][0];

		if($action==='quotes')
			$return_value=safe_quotes($main);
			
	}
	else{
		//nothing was set
		//**If an array then loop and escape the whole array and return comma separated with  quote**//
		if(is_array($main)){
			//comma separated
			$return_value=implode ( "," , $main );

		}else{
			$return_value=$main;
		}
	}	
	
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function safe_quotes($main){
	//**If an array, implode and return comma separated with quotes**//
	if(is_array($main)){
		
		//comma separated with  quote
		if(count($main)<1)
			$return_value='';
		
		if(count($main)==1)
			$return_value="'" . $main[0] . "'";
			
		if(count($main)>1)
			$return_value="'" . implode ( "','" , $main ) . "'";

	}else{
		$return_value="'" . $main . "'";
	}
		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.table','Sanitize table name and return',['namespace'=>__NAMESPACE__]);
function table($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);
    
	$return_value=preg_replace('/[^A-Za-z0-9_\.]/','',$main);

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.int','Remove all characters except 0-9 & - in the beginning',['func'=>'_int','namespace'=>__NAMESPACE__]);
function _int($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);
		
	$return_value=preg_replace('/[^0-9-]/','',$main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.num','Remove all characters except 0-9,. & -',['namespace'=>__NAMESPACE__]);
function num($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);
    
	$return_value=preg_replace('/[^0-9-\.]/','',$main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.str','Escape the string and return',['namespace'=>__NAMESPACE__]);
function str($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);
		
	$return_value=\mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x27\x5C]','\\\0',$main);
	$return_value=AW2_APOS.$return_value.AW2_APOS;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.id','Escape the string and return',['namespace'=>__NAMESPACE__]);
function id($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);

	$return_value=preg_replace('/[^A-Za-z0-9\-\_\ ]/','',$main);
	$return_value=AW2_APOS.$return_value.AW2_APOS;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('esc.in','Return the comma seperated string in single quotes',['namespace'=>__NAMESPACE__]);
function in($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);
		
	if(is_array($main))
		$main=implode ( "," , $main );
	
	$return_value=mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x27\x5C]','\\\0',$main);
	$return_value=AW2_APOS.$return_value.AW2_APOS;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.like','Return the escaped string',['namespace'=>__NAMESPACE__]);
function like($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);
    	
	$return_value=mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]','\\\0',$main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('esc.date','Return the string wrapped in DATE function',['func' => '_date','namespace'=>__NAMESPACE__]);
function _date($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array(
		'main'  	  => ""
        ), $atts) );

	$main = \aw2_library::get($main);
    	
	$return_value= "DATE('".$main."')";
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}
