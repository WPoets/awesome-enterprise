<?php
namespace aw2\redis_db;

\aw2_library::add_service('redis_db.stream_add','Run the Code Library',['namespace'=>__NAMESPACE__]);

function stream_add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'stream_id'=>null,
		'entry_id' => '*',
		'data' => null
	), $atts) );

	if($stream_id == null || $data == null){
		return array('status'=>'error', 'message'=>'Bad Request');
	}

	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DB);
	
		
	$redis_ack = $redis->xAdd($stream_id, $entry_id, $data);

	if(!empty($redis_ack)){

		$return_value = array('status'=>'success', 'message'=>'Stream added successfully','data'=>$redis_ack);
	}else{
		$return_value = array('status'=>'error', 'message'=>'Unable to add data to stream','data'=>$redis_ack);
	}

	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}



\aw2_library::add_service('redis_db.stream_last','Run the Code Library',['namespace'=>__NAMESPACE__]);

function stream_last($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'stream_id'=>null,
	), $atts) );

	if($stream_id == null){
		return array('status'=>'error', 'message'=>'Bad Request');
	}

	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DB);
	
		
	$redis_ack = $redis->xREVRANGE($stream_id, '+', '-',1);

	if(!empty($redis_ack)){

		$return_value = array('status'=>'success', 'message'=>'Last stream fetched successfully','data'=>$redis_ack);
	}else{
		$return_value = array('status'=>'error', 'message'=>'Unable to fetch data from stream','data'=>$redis_ack);
	}

	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('redis_db.stream_fetch_all','Run the Code Library',['namespace'=>__NAMESPACE__]);

function stream_fetch_all($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( \aw2_library::shortcode_atts( array(
		'main'=>null,
		'stream_id'=>null,
	), $atts) );

	if($stream_id == null){
		return array('status'=>'error', 'message'=>'Bad Request');
	}

	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DB);
	
		
	$redis_ack = $redis->xREVRANGE($stream_id, '+', '-');

	if(!empty($redis_ack)){

		$return_value = array('status'=>'success', 'message'=>'Last stream fetched successfully','data'=>$redis_ack);
	}else{
		$return_value = array('status'=>'error', 'message'=>'Unable to fetch data from stream','data'=>$redis_ack);
	}

	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('redis_db.stream_fetch_usage','Run the Code Library',['namespace'=>__NAMESPACE__]);

function stream_fetch_usage($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( \aw2_library::shortcode_atts( array(
		'main'=>null,
		'stream_id'=>null,
	), $atts) );

	if($stream_id == null){
		return array('status'=>'error', 'message'=>'Bad Request');
	}

	$redis = \aw2_library::redis_connect(REDIS_LOGGING_DB);
	$redis_ack = $redis->GET($stream_id);

	if(!empty($redis_ack)){

		$return_value = array('status'=>'success', 'message'=>'Last stream fetched successfully','data'=>$redis_ack);
	}else{
		$return_value = array('status'=>'error', 'message'=>'Unable to fetch data from stream','data'=>$redis_ack);
	}

	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}



\aw2_library::add_service('redis_db.get','Get values of a ticket',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode=null){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null,
	'db'=>null
	), $atts) );

	if(!$main)return 'Main must be set';		
	
	$ticket=$main;
	if(is_null($db)){
		return '';
		
	}
	$redis = \aw2_library::redis_connect($db);

	$return_value='';	
		
	if($field)
		$return_value=$redis->hGet($ticket,$field);
	else	
		$return_value=$redis->hGetAll($ticket); 
			
	if($field==='ticket_expiry'){
		$ttl=$redis->ttl($ticket);
		$return_value=round($ttl/60,2);
	}		
	



	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}
