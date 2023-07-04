<?php
namespace aw2\validate;


\aw2_library::add_service('validate.email','Checks email validation',['namespace'=>__NAMESPACE__]);

function email($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='email is not valid';
	$return_value->error_code='validation_failed';
	
	if(filter_var($main, FILTER_VALIDATE_EMAIL))
		$return_value = true;
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.positive_int','Checks positive_int',['namespace'=>__NAMESPACE__]);

function positive_int($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => '',
		'min_range' => null,
		'max_range' => null
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message=' number is not valid';
	$return_value->error_code='validation_failed';
	
	// default min and max ranges
	$min = !$min_range ? 1 : $min_range;
	$max = !$max_range ? 10000000000 : $max_range;
	
	// validation check for positive_int
	if (filter_var($main, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max))) === false) {
		$return_value = $return_value;
	} else {
		$return_value = true;
	}
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.zero_positive_int','Checks zero_positive_int',['namespace'=>__NAMESPACE__]);

function zero_positive_int($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => '',
		'min_range' => null,
		'max_range' => null
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='number is not valid';
	$return_value->error_code='validation_failed';
	
	// default min and max ranges
	$min = 0;
	$max = !$max_range ? 10000000000 : $max_range;
	
	// validation check for positive_int
	if (filter_var($main, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max))) === false) {
		$return_value = $return_value;
	} else {
		$return_value = true;
	}
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.zero_positive_float','Checks zero_positive_float',['namespace'=>__NAMESPACE__]);

function zero_positive_float($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

   extract(\aw2_library::shortcode_atts( array(
        'main' => '',
		'min_range' => null,
		'max_range' => null
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='float is not valid';
	$return_value->error_code='validation_failed';
	
	// default min and max ranges
	$min = 0;
	$max = !$max_range ? 10000000000 : $max_range;
	
	// validation check for positive_int
	if (filter_var($main, FILTER_VALIDATE_FLOAT, array("options" => array("min_range"=>$min, "max_range"=>$max))) === false) {
		$return_value = $return_value;
	} else {
		$return_value = true;
	}
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.name','Checks name validation',['namespace'=>__NAMESPACE__]);

function name($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => null
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='name is not valid';
	$return_value->error_code='validation_failed';
	
	if(preg_match('/^[a-zA-Z0-9 \.]+$/', $main))
		$return_value = true;
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.pan_card','Checks pan_card validation',['namespace'=>__NAMESPACE__]);

function pan_card($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ''
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='pan_card is not valid';
	$return_value->error_code='validation_failed';
	
	if(preg_match('/^[A-Z]{5}\d{4}[A-Z]$/', $main))
		$return_value = true;
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.zip_code','Checks zip_code validation',['namespace'=>__NAMESPACE__]);

function zip_code($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ''
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='zip_code is not valid';
	$return_value->error_code='validation_failed';
	
	if(preg_match('/^\d{6}$/', $main))
		$return_value = true;
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.mobile_number','Checks zip_code validation',['namespace'=>__NAMESPACE__]);

function mobile_number($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ''
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='mobile_number is not valid';
	$return_value->error_code='validation_failed';
	
	if(preg_match('/^\d{10}$/', $main))
		$return_value = true;
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('validate.printable','Checks printable validation',['namespace'=>__NAMESPACE__]);

function printable($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ''
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='non printable value';
	$return_value->error_code='validation_failed';
	
	//if(preg_match('/^[[:print:]]+$/', $value))
		$return_value = true;
			
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

// id: A-Z, a-z, 0-9, - _
\aw2_library::add_service('validate.object_id','Checks object_id validation',['namespace'=>__NAMESPACE__]);
function object_id($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'main' => ""
    ), $atts) );
	
	// default error value
	$return_value=new \aw2_error();
	$return_value->message='not a valid Object ID';
	$return_value->error_code='validation_failed';
	
	if(preg_match('/^[a-zA-Z0-9_.-]+$/', $main))
		$return_value = true;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}
