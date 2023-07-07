<?php
namespace aw2\math;

\aw2_library::add_service('math','Math Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('math.solve','Run the Code Library',['namespace'=>__NAMESPACE__]);

function solve($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	$pattern = '/([^-\d.\(\)\+\*\/ \^%])/';
	$replacement = '';
	$result= preg_replace($pattern, $replacement, $main);
	
	try {
		$return_value=eval('return ' . $result .  ' ;');
		
	} catch (Throwable $t) {
			$return_value = 'Math Error';
			\util::var_dump($t->getMessage());
	}
		$return_value=\aw2_library::post_actions('all',$return_value,$atts);
		return $return_value;	
}

\aw2_library::add_service('math.minus_one','Run the Code Library',['namespace'=>__NAMESPACE__]);

function minus_one($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value= $main - 1 ;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}


\aw2_library::add_service('math.plus_one','Run the Code Library',['namespace'=>__NAMESPACE__]);

function plus_one($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value= $main + 1 ;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
