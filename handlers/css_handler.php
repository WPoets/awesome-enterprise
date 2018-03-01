<?php

//////// CSS Library ///////////////////
aw2_library::add_library('css','CSS Handler');

function aw2_css_less($atts,$content=null,$shortcode){
	require_once (aw2_library::$plugin_path . "/libraries/wp-less/wp-less.php");
	$string=aw2_library::parse_shortcode($content);
	$less = new lessc;
	$return_value = $less->compile($string);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}


