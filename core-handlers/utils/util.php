<?php
namespace aw2\util;

\aw2_library::add_service('util.otp','Generate a 6 digit OTP',['namespace'=>__NAMESPACE__]);


function otp($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	$return_value=\mt_rand(100000,999999);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('util.form_data_array','Collect Form Data and return an array',['namespace'=>__NAMESPACE__]);


function form_data_array($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	foreach($_REQUEST as $key => $value){
		$final=$value;
		if(is_array($value))$final=implode(',',$value);
			$data_arr[$key]=stripcslashes($final);
	}
	$return_value=\aw2_library::post_actions('all',$data_arr,$atts);
	return $return_value;
}

	
\aw2_library::add_service('util.save_csv_page','Save CSV Page',['namespace'=>__NAMESPACE__]);
function save_csv_page($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'pageno'=>'',
	'rows'=>'',
	'key'=>'',
	'ttl' => ''
	), $atts) );
	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
 
		

	$buffer = fopen('php://memory','w');
	foreach ($rows as $line) {
		fputcsv($buffer, $line);
	}
	rewind($buffer);
	$csv = stream_get_contents($buffer);
	
	$redis->zAdd($key,$pageno,$csv);
	
	if(!$ttl)
		$redis->expire($key, 60*60);
	else
		$redis->expire($ticket, $ttl*60);
	
}

\aw2_library::add_service('util.async_url','Run async url',['namespace'=>__NAMESPACE__]);
function async_url($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'url'=>''
	), $atts) );
	

	$cron_request = array(
		'args' => array(
			'blocking'  => false,
			'sslverify' => false 
		)
	);
	
	if(defined('HTTP_AUTH')){
		$cron_request['args']['headers'] = array('Authorization' => 'Basic ' . base64_encode( HTTP_AUTH )); // HTTP_AUTH='easyengine:q5jgE6'
	}
	
	
	wp_remote_post($url, $cron_request['args'] );	

	return 'success';
}

\aw2_library::add_service('util.nonce','creates nonce value for given string',['namespace'=>__NAMESPACE__]);
function nonce($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts) );
	
	if(!$main)return 'Main must be set';
	
	$return_value = \wp_create_nonce($main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	
	return $return_value;
}

\aw2_library::add_service('util.qs_parse','parse the query string and return back array',['namespace'=>__NAMESPACE__]);
function qs_parse($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts) );
	
	$qs = \aw2_library::get_array_ref('qs');
	
	$i = 0;
	$return_value=array();
	foreach ($qs as $value){
		$pos = \strpos($value, '$$');
		if ($pos !== false) {
			$arr=\explode('$$',$value);
			$return_value[$arr[0]]=\aw2\clean\safe(['main'=>$arr[1]]);
		}else{
			$return_value[$i]=\aw2\clean\safe(['main'=>$value]);
			$i++;
		}
	}

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	
	return $return_value;
}

\aw2_library::add_service('util.constant','Returns an associative array with the names of all the constants and their values',['namespace'=>__NAMESPACE__]);
function constant($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract(\aw2_library::shortcode_atts( array('constant_key'=>'all'), $atts) );
	
	if($constant_key=='all'){
		$return_value=\get_defined_constants();  
	}else{
		$return_value=\constant($constant_key);  
	}

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

/*
\aw2_library::add_shortcode('search','new_ticket', 'search_new_ticket');



function search_new_ticket($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>'',
	'chars' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
	'ttl' => ''
	), $atts) );
	
	
	$length=15;
	
	$args=array();
	$args = $atts['args'];
	$args['token']=substr( str_shuffle( $chars ), 0, $length );
	$args['nonce']=wp_create_nonce($args['token']);
	$args['ticket']=$args['token'] . '.' . $args['nonce'];
	
	$ticket=$args['ticket'];
	
	//Connect to Redis and store the data
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 12;

	$redis->select($database_number);
	
	$content = json_encode($args);
	
	$redis->set($ticket, $content);

	if(!$ttl)
		$redis->setTimeout($ticket, 60*60);
	else
		$redis->setTimeout($ticket, $ttl*60);
	
	$return_value=\aw2_library::post_actions('all',$ticket,$atts);
	return $return_value;
}

\aw2_library::add_shortcode('search','set_ticket', 'search_set_ticket');

function search_set_ticket($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>'',
	'ticket' => '',
	'ttl' => ''
	), $atts) );
	
	//Connect to Redis and store the data
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 12;
	$redis->select($database_number);
	
	$content = json_encode($atts['args']);
	
	$redis->set($ticket, $content);

	if(!$ttl)
		$redis->setTimeout($ticket, 60*60);
	else
		$redis->setTimeout($ticket, $ttl*60);
	
	$return_value=\aw2_library::post_actions('all',$ticket,$atts);
	return $return_value;
}

\aw2_library::add_shortcode('search','get_ticket', 'search_get_ticket');
function search_get_ticket($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>''
	), $atts) );
	
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$database_number = 12;
	$redis->select($database_number);
	if($redis->exists($main))
		$data = $redis->get($main);
	
	$data_arr = json_decode($data, true);
	

	$return_value=\aw2_library::post_actions('all',$data_arr,$atts);
	return $return_value;
}
 
*/ 
