<?php
namespace aw2\guzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

\aw2_library::add_service('guzzle','Guzzle cURL',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('guzzle.get','Get data from URL',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'url'    =>"",
		'type'		=>'sync',
		'header'    =>null,
		'params' => null,
		), $atts) );
			
	/** Include guzzle **/
	$path = \aw2_library::$plugin_path . "/libraries";
	require_once $path . '/guzzle/autoloader.php';
	
	$client = new Client();
	$response = null;
	$result = null;
	$second_param = array();
	/* $header = [
				'User-Agent' => 'testing/1.0', 
				'Accept'     => 'application/json', 
				'X-Foo' => [
					'Bar', 
					'Baz'
				]
			];  */
	if(is_array($header) && count($header)>0){
		$second_param['headers'] = $header;
	}
	if(is_array($params) && count($params)>0){
		$second_param['query'] = $params;
	}
	if($type=="async"){ 
		$response = $client->getAsync($url, $second_param);		
		$res = Promise\unwrap($response);
		$res = $response->wait();		
		
		$result['state'] = $response->getState();
		$result['body'] = $res->getBody()->getContents();
		$result['header'] = $res->getHeaders();
		$result['statusCode'] = $res->getStatusCode();
		$result['statusText'] = $res->getReasonPhrase();
	}else{
		$response = $client->get($url, $second_param);		
		$result['body'] = $response->getBody()->getContents();
		$result['header'] = $response->getHeaders();
		$result['statusCode'] = $response->getStatusCode();
		$result['statusText'] = $response->getReasonPhrase();
	}
	return $result;
}

\aw2_library::add_service('guzzle.post','pOST data from URL',['namespace'=>__NAMESPACE__]);
function post($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'url'    =>"",
		'type'		=>'sync',
		'header'    =>null,
		'params' => null,
		), $atts) );
		
	//\util::var_dump($atts);
	
	/** Include PHPExcel */
	$path = \aw2_library::$plugin_path . "/libraries";
	require_once $path . '/guzzle/autoloader.php';
	
	$client = new Client();
	$response = null;
	$result = null;
	$second_param = array();
	/* $header = [
				'User-Agent' => 'testing/1.0', 
				'Accept'     => 'application/json', 
				'X-Foo' => [
					'Bar', 
					'Baz'
				]
			]; */ 
	if(is_array($header) && count($header)>0){
		$second_param['headers'] = $header;
	}
	if(is_array($params) && count($params)>0){
		// FORM FIELDS SUPPORT
		$second_param['form_params'] = $params;
	}
	if($type=="async"){ 
		$response = $client->postAsync($url, $second_param);		
		$res = Promise\unwrap($response);
		$res = $response->wait();		
		
		$result['state'] = $response->getState();
		$result['body'] = $res->getBody()->getContents();
		$result['header'] = $res->getHeaders();
		$result['statusCode'] = $res->getStatusCode();
		$result['statusText'] = $res->getReasonPhrase();
	}else{ 
		$response = $client->post($url, $second_param);		
	    //\util::var_dump($atts);
		
		$result['body'] = $response->getBody()->getContents();
		$result['header'] = $response->getHeaders();
		$result['statusCode'] = $response->getStatusCode();
		$result['statusText'] = $response->getReasonPhrase();
	}
	return $result;
}

?>