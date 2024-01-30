<?php

namespace aw2\o;

//exit
\aw2_library::add_service('o.exit','Dump the value and exit. Use o.exit',['func'=>'_exit','namespace'=>__NAMESPACE__]);
function _exit($value, $atts){
	exit(\util::var_dump($value,true));
}

//die
\aw2_library::add_service('o.die','Dump the value and die. Use o.die',['func'=>'_die','namespace'=>__NAMESPACE__]);
function _die($value, $atts){
	die(\util::var_dump($value,true));
}

//echo
\aw2_library::add_service('o.echo','Echo the value. Use o.echo',['func'=>'_echo','namespace'=>__NAMESPACE__]);
function _echo($value, $atts){
	\util::var_dump($value);
	return $value;
}

//dump
\aw2_library::add_service('o.dump','Dump the value. Use o.dump',['namespace'=>__NAMESPACE__]);
function dump($value, $atts){	
	$value = \util::var_dump($value,true);
	return $value;
}

//console
\aw2_library::add_service('o.console','Output the value in console log. Use o.console',['namespace'=>__NAMESPACE__]);
function console($value, $atts){
	echo('<script type="text/spa" spa_activity="core:console_log">' . \util::var_dump($value,true) .'</script>');
	return $value;
}

//log
\aw2_library::add_service('o.log','Output the value in log file in defined directory. Use o.log',['namespace'=>__NAMESPACE__]);
function log($value, $atts){
	if(!defined(('LOG_PATH')))
		return $value;

	if($atts['log'] === 'yes')
		$filename = "log.html";
	else
		$filename = $atts['log'];
	
	//LOG_PATH - Defined in wp-config	
	$path= LOG_PATH . '/' . $filename;
	$fp = fopen($path, 'a');
	fwrite($fp, \util::var_dump($value,true));
	return $value;
}

//destroy
\aw2_library::add_service('o.destroy','Do not output the value. Use o.destroy',['namespace'=>__NAMESPACE__]);
function destroy($value, $atts){
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


//merge_with
\aw2_library::add_service('o.arr_push','Merge the value with specified variable. Use o.arr.push',['namespace'=>__NAMESPACE__]);
function arr_push($value, $atts){
		$arr=\aw2_library::get($atts['arr_push']);
		if(!is_array($arr))$arr=array();
		array_push($arr,$value);
		\aw2_library::set($atts['arr_push'],$arr,null,$atts);
		$value='';	
	return $value;
}



//merge_r_with
\aw2_library::add_service('o.merge_r_with','Merge the value with specified variable. Use o.merge_with',['namespace'=>__NAMESPACE__]);
function merge_r_with($value, $atts){
	if(is_array($value)){
		$merge_with_array=\aw2_library::get($atts['merge_r_with']);
		if(!is_array($merge_with_array))$merge_with_array=array();
		$final_array=array_merge_deep_array([$merge_with_array,$value]);
		\aw2_library::set($atts['merge_r_with'],$final_array,null,$atts);
		$value='';	
	}
	return $value;
}


function array_merge_deep_array($arrays) {
    $result = array();
    foreach ($arrays as $array) {
        foreach ($array as $key => $value) {
            // Renumber integer keys as array_merge_recursive() does. Note that PHP
            // automatically converts array keys that are integer strings (e.g., '1')
            // to integers.
            if (is_integer($key)) {
                $result[] = $value;
            }
            elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                $result[$key] = array_merge_deep_array(array(
                    $result[$key],
                    $value,
                ));
            }
            else {
                $result[$key] = $value;
            }
        }
    }
    return $result;
}
