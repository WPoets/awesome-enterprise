<?php

aw2_library::add_shortcode('file','write', 'awesome2_file_write');
function awesome2_file_write($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'file_name'    =>'',
	'folder'		=>'',
	'child_folder'		=>'',
	'mode'	=>'',
	'content_to_write'	=>'',
	), $atts, 'aw2_get' ) );

	
	if($child_folder){
		$folder=$folder . $child_folder . '/';
		
		if (!is_dir($folder))mkdir($folder);
	}
	
	$myfile = fopen($folder . $file_name, $mode) or die("Unable to open file!");
	fwrite($myfile, $content_to_write);
	fclose($myfile);
	
	if (file_exists($folder . $file_name))
		return $folder . $file_name;
	else
		return 'error';
		
}


