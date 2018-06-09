<?php

namespace aw2\phantomjs;
use JonnyW\PhantomJs\Client;

\aw2_library::add_service('phantomjs','PhantomJS Library',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('phantomjs.generate','Generate a PDF from URL or HTML. Use phantomjs.generate',['namespace'=>__NAMESPACE__]);
function generate($atts,$content=null,$shortcode){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'url'=>'',
		'output_folder'=>'',
		'output_file'=>'',
		'format'=>'A4',
		'orientation' =>'landscape',
		'margin'=>'1cm'
		
		), $atts) );
	
	/*Needed to load required files from JonnyW-PhantomJs Library*/
	$plugin_path=plugin_dir_path( __DIR__ );
	require_once($plugin_path . '/libraries/phantomjs/autoload.php');
	
	$client = Client::getInstance();
	
	/*Custom path where phantomjs is installed - defined in wp-config*/
	$client->getEngine()->setPath(PHANTOMJS_PATH);
	
	/*Create the output file path with filename*/
	if (!file_exists($output_folder)) {
		mkdir($output_folder, 0755, true);
	}
	$output_file_path = $output_folder . $output_file;
	
	/*Setup content if passed*/	
	if(empty($url)){
		$content=\aw2_library::parse_shortcode($content);
		
		//TEMP_PATH is defined in wp-config
		$temp_file_url = TEMP_PATH . uniqid().".html";
		
		$handle = fopen($temp_file_url, "w+");
		fwrite($handle,$content); 
		fclose($handle);
		$url = 'file://'.$temp_file_url;
	}
	
    /** 
     * @see JonnyW\PhantomJs\Http\PdfRequest
     **/
    $request = $client->getMessageFactory()->createPdfRequest($url, 'GET');
    $request->setOutputFile($output_file_path);
    $request->setFormat($format);
    $request->setOrientation($orientation);
    $request->setMargin($margin);

    /** 
     * @see JonnyW\PhantomJs\Http\Response 
     **/
    $response = $client->getMessageFactory()->createResponse();

    // Send the request
    $return_value = $client->send($request, $response);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	
	/*unlink the file if it was created*/
	if(!empty($temp_file_url)) unlink($temp_file_url);
	
	return $return_value;
}