<?php
namespace aw2\util;

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
	
	extract( shortcode_atts( array(
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
		$redis->setTimeout($key, 60*60);
	else
		$redis->setTimeout($key, $ttl*60);
	
}



/*
\aw2_library::add_shortcode('search','new_ticket', 'search_new_ticket');



function search_new_ticket($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
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
	
	extract( shortcode_atts( array(
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
	
	extract( shortcode_atts( array(
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
