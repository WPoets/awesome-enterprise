<?php
namespace aw2\_file;


\aw2_library::add_service('file','File Services',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('file.write','Write content to file',['namespace'=>__NAMESPACE__]);
function awesome2_file_write($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( \aw2_library::shortcode_atts( array(
	'file_name'    =>'',
	'folder'		=>'',
	'child_folder'		=>'',
	'mode'	=>'',
	'content_to_write'	=>''
	), $atts ) );

	
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


\aw2_library::add_service('file.file_put','Will put the content into defined path',['namespace'=>__NAMESPACE__]);

function file_put($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( \aw2_library::shortcode_atts( array(
	'path'    =>'',
	'file_content' =>'',
	'safe'=>''
	), $atts ) );
	
	if($safe!=='yes'){
		return 'error';
	}
	
	if($path && $file_content){
		file_put_contents($path, $file_content);
		return 'success';
	}
	return 'error';
}

\aw2_library::add_service('file.file_get','Will get the content from file',['namespace'=>__NAMESPACE__]);
function file_get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( \aw2_library::shortcode_atts(array(
	'file_url' =>'',
	'safe'=>''
	), $atts ) );
	
	if($safe!=='yes'){
		return 'error';
	}
	if($file_url){
		$content = file_get_contents($file_url);
		return $content;
	}
	return 'error';
}

\aw2_library::add_service('file.open_file','Will open the file',['namespace'=>__NAMESPACE__]);
function open_file($atts,$content=null,$shortcode){
	/*
	This function takes a path to a file to output ($file),  the filename that the browser will see ($name) and  the MIME type of the file ($mime_type, optional).
	*/
	extract( \aw2_library::shortcode_atts( array(
	'file' =>'',
	'name'=>'',
	'mime_type'=>'',
	'safe'=>''
	), $atts ) );
	
	if($safe!=='yes'){
		return 'error';
	}
	
	//Check the file premission
	if(!is_readable($file)) die('File not found or inaccessible!');

	$size = filesize($file);
	$name = rawurldecode($name);

	/* Figure out the MIME type | Check in array */
	$known_mime_types=array(
	"pdf" => "application/pdf",
	"txt" => "text/plain",
	"html" => "text/html",
	"htm" => "text/html",
	"exe" => "application/octet-stream",
	"zip" => "application/zip",
	"doc" => "application/msword",
	"xls" => "application/vnd.ms-excel",
	"ppt" => "application/vnd.ms-powerpoint",
	"gif" => "image/gif",
	"png" => "image/png",
	"jpeg"=> "image/jpg",
	"jpg" =>  "image/jpg",
	"php" => "text/plain"
	);

	if($mime_type==''){
	 $file_extension = strtolower(substr(strrchr($file,"."),1));
	 if(array_key_exists($file_extension, $known_mime_types)){
		$mime_type=$known_mime_types[$file_extension];
	 } else {
		$mime_type="application/force-download";
	 };
	};

	//turn off output buffering to decrease cpu usage
	@ob_end_clean(); 

	// required for IE, otherwise Content-Disposition may be ignored
	if(ini_get('zlib.output_compression'))
	ini_set('zlib.output_compression', 'Off');

	header('Content-Type: ' . $mime_type);
	header("Content-Transfer-Encoding: binary");
	header('Accept-Ranges: bytes');

	/* The three lines below basically make the 
	download non-cacheable */
	header("Cache-control: private");
	header('Pragma: private');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	// multipart-download and download resuming support
	if(isset($_SERVER['HTTP_RANGE']))
	{
	list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
	list($range) = explode(",",$range,2);
	list($range, $range_end) = explode("-", $range);
	$range=intval($range);
	if(!$range_end) {
		$range_end=$size-1;
	} else {
		$range_end=intval($range_end);
	}
	$new_length = $range_end-$range+1;
	header("HTTP/1.1 206 Partial Content");
	header("Content-Length: $new_length");
	header("Content-Range: bytes $range-$range_end/$size");
	} else {
	$new_length=$size;
	header("Content-Length: ".$size);
	}

	/* Will output the file itself */
	$chunksize = 1*(1024*1024); //you may want to change this
	$bytes_send = 0;
	if ($file = fopen($file, 'r'))
	{
	if(isset($_SERVER['HTTP_RANGE']))
	fseek($file, $range);

	while(!feof($file) && (!connection_aborted()) && ($bytes_send<$new_length))
	{
		$buffer = fread($file, $chunksize);
		print($buffer); //echo($buffer); // can also possible
		flush();
		$bytes_send += strlen($buffer);
	}
	fclose($file);

	} else
	//If no permissiion
	die('Error - can not open file.');
	//die
	die();
}


\aw2_library::add_service('file.log_reader_sql_error','Will read the content from file',['namespace'=>__NAMESPACE__]);
function log_reader_sql_error($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	$response=array();
	extract( \aw2_library::shortcode_atts( array(
	'file_url' =>'',
	'safe'=>''
	), $atts ) );
	
	if($safe!=='yes'){
		return $response;
	}
	if($file_url){
		$fn = fopen($file_url,"r");
		$i=0;
		$j=0;
		$logs = array();
		$unrecognized = array();
		while(! feof($fn))  {
			$result = fgets($fn);
			preg_match_all("~(^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) ([0-z@.]+) @ ([$-z]+) ([0-z.]+) ([A-Z]+) ([0-9]+): ((.)*)$~",
				$result,
				$out, PREG_PATTERN_ORDER);
			if(!empty($out[0])){
				$logs[$i] = $out;
				$i++;
			}else{
				$unrecognized[$j] = $result;
				$j++;
			}
		}
		fclose($fn);
		$response['logs'] = $logs;
		$response['unrecognized'] = $unrecognized;
			return $response;
	}
	return $response;
}

\aw2_library::add_service('file.read_deadlock','Will read the content from query',['namespace'=>__NAMESPACE__]);
function read_deadlock($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
		$response=array();
		extract( \aw2_library::shortcode_atts( array(
		'data' =>'',
		'safe'=>''
		), $atts ) );

		if($safe!=='yes'){
		return $response;
		}
		
		if($data){
			preg_match_all("~LATEST DETECTED DEADLOCK\n------------------------\n([0-z-: *(),.'$\n\t]+)------------\n([0-z]+)\n------------((\n|.)+)~",
				$data,
				$out, PREG_PATTERN_ORDER);
			preg_match_all("~(^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) [0-z]+~",
			$out[1][0],
			$res, PREG_PATTERN_ORDER);
			return $data;
			$res2 = str_replace($res[0][0], '', $out[1][0]);
			preg_match_all("~[*]+ [0-z() ,-.]+~",
			$res2,
			$pattern_matched, PREG_PATTERN_ORDER);
			$response['timestamp'] = $res[1][0];
			$response['concerns'] = $pattern_matched;
			return $response;
		}
		return $response;
}