<?php
//////// Math Library ///////////////////
aw2_library::add_library('math','Math Library');

function aw2_math_solve($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	$pattern = '/([^-\d.\(\)\+\*\/ \^%])/';
	$replacement = '';
	$result= preg_replace($pattern, $replacement, $main);
	try {
		$return_value=eval('return ' . $result .  ' ;');
		
	} catch (Throwable $t) {
			$return_value = 'Math Error';
			util::var_dump($t->getMessage());
	}
		$return_value=aw2_library::post_actions('all',$return_value,$atts);
		return $return_value;	
}