<?php

aw2_library::add_shortcode('time','start', 'time_start','Start Time');

function time_start($atts,$content=null,$shortcode){
	$GLOBALS['time_start']= microtime(true); 
}


aw2_library::add_shortcode('time','diff', 'time_diff','Difference from start');

function time_diff($atts,$content=null,$shortcode){
	$GLOBALS['time_end']= microtime(true); 
	$diff=$GLOBALS['time_end'] - $GLOBALS['time_start'];
	return $diff;
}

aw2_library::add_shortcode('time','diff_echo', 'time_diff_echo','Difference from start');

function time_diff_echo($atts,$content=null,$shortcode){
	$GLOBALS['time_end']= microtime(true); 
	$diff=$GLOBALS['time_end'] - $GLOBALS['time_start'];
	echo '<br>' . $diff;
}

