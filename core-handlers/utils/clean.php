<?php
namespace aw2\clean;


\aw2_library::add_service('clean.column_name','clean the column name',['namespace'=>__NAMESPACE__]);


// column_name: A-Z, a-z, 0-9, - _
function column_name($atts,$content=null,$shortcode){

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
    if (!is_string($main)) {
        throw new \InvalidArgumentException('Input must be a string, ' . gettype($main) . ' given');
    }
    
    // Convert to lowercase
    $output = strtolower($main);
    
    // Convert hyphens to underscores
    $output = str_replace('-', '_', $output);
    
    // Convert spaces to underscores (handles multiple spaces)
    $output = preg_replace('/\s+/', '_', $output);
    
    // Remove all characters except a-z, 0-9, _
    $output = preg_replace('/[^a-z0-9_]/', '', $output);
    
    return $output;
}



\aw2_library::add_service('clean.id','clean the id',['namespace'=>__NAMESPACE__]);


// id: A-Z, a-z, 0-9, - _
function id($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	$return_value=preg_replace('/[^A-Za-z0-9\-\_]/','',$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('clean.int','clean the int',['namespace'=>__NAMESPACE__]);

// 0-9 -
function int($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	$return_value=preg_replace('/[^0-9\-]/','',$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('clean.num','clean the num',['namespace'=>__NAMESPACE__]);

// 0-9 - .
function num($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	$return_value=preg_replace('/[^0-9\-\.]/','',$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('clean.date','clean the date',['namespace'=>__NAMESPACE__]);

// 0-9 (8 times)
function date($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	$number=preg_replace('/[^0-9]/','',$main);
	
	$return_value=substr($number, 0, 8);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('clean.safe','clean the string',['namespace'=>__NAMESPACE__]);

//[^\x20\x21\x23\x24\x2A-\x2E\x30-\x3A\x40-\x5A\x5F\x61-\x7A]
// (space) ! # $ * + , - . 0-9 : @ A-Z _ a-z 
function safe($atts,$content=null,$shortcode=null){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	$return_value=preg_replace('/[^\x20\x21\x23\x24\x2A-\x2E\x30-\x3A\x40-\x5A\x5F\x61-\x7A]/','',$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('clean.printable','clean the printable',['namespace'=>__NAMESPACE__]);

// [^\x09-\x0D\x20-\x7E]
// Form Feed, Line Feed, Carriage Return, Tab and (space) - ~
function printable($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	$return_value=preg_replace('/[^\x09-\x0D\x20-\x7E]/','',$main);
	//$return_value = $main;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}