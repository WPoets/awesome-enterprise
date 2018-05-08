<?php

namespace aw2\o;

//run
\aw2_library::add_service('o.exit','Dump the value and exit. Use o.exit',['func'=>'_exit','namespace'=>__NAMESPACE__]);
function _exit($value, $atts){
	exit(\util::var_dump($value,true));
}

//console
\aw2_library::add_service('o.console','Output the value in console log. Use o.console',['namespace'=>__NAMESPACE__]);
function console($value, $atts){
	echo('<script type="text/spa" spa_activity="core:console_log">Memory Usage ' . \util::var_dump($value,true) .'</script>');
}

//log
\aw2_library::add_service('o.log','Output the value in log.html file in uploads directory. Use o.log',['namespace'=>__NAMESPACE__]);
function log($value, $atts){
	$upload_dir = wp_upload_dir();
	$path= $upload_dir['path'] . '/log.html';
	$fp = fopen($path, 'a');
	fwrite($fp, \util::var_dump($value,true));
}

//no_output
\aw2_library::add_service('o.no_output','Do not output the value. Use o.no_output',['namespace'=>__NAMESPACE__]);
function no_output($value, $atts){
	$value='';
	return $value;
}

//set
\aw2_library::add_service('o.set','Set the value to specified variable. Use o.set="<chain>"',['namespace'=>__NAMESPACE__]);
function set($value, $atts){
	\aw2_library::set($atts['set'],$value,null,$atts);
	$value='';
	return $value;
}

//merge_with
\aw2_library::add_service('o.merge_with','Merge the value with specified variable. Use o.merge_with',['namespace'=>__NAMESPACE__]);
function merge_with($value, $atts){
	if(is_array($value)){
		$merge_with_array=\aw2_library::get($atts['merge_with']);
		if(!is_array($merge_with_array))$merge_with_array=array();
		$final_array=array_merge($merge_with_array,$value);
		\aw2_library::set($atts['merge_with'],$final_array,null,$atts);
		$value='';	
	}
	return $value;
}

