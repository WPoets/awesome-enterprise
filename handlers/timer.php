<?php
namespace aw2\_time;

\aw2_library::add_service('time','Timer Library.',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('time.start','Start Time',['namespace'=>__NAMESPACE__]);
function start($atts,$content=null,$shortcode){
	$GLOBALS['time_start']= microtime(true); 
}

\aw2_library::add_service('time.diff','Difference from start',['namespace'=>__NAMESPACE__]);
function diff($atts,$content=null,$shortcode){
	$GLOBALS['time_end']= microtime(true); 
	$diff=$GLOBALS['time_end'] - $GLOBALS['time_start'];
	return $diff;
}

\aw2_library::add_service('time.diff_echo','Echo difference from start',['namespace'=>__NAMESPACE__]);
function diff_echo($atts,$content=null,$shortcode){
	$GLOBALS['time_end']= microtime(true); 
	$diff=$GLOBALS['time_end'] - $GLOBALS['time_start'];
	echo '<br>' . $diff;
}

\aw2_library::add_service('test.t1','Echo difference from start',['namespace'=>__NAMESPACE__]);
function t1($atts,$content=null,$shortcode){
	global $wpdb;
	\util::var_dump($wpdb);
	
	$args = array(
		'post_type' => 'post',
	);
	$query = new \WP_Query( $args );
	\util::var_dump($query);
}
