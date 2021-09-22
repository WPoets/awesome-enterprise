<?php 

class aw2_usage_log{
	
	static function log_usage($collection,$module=null){

		$stream_id = null;
		$post_type=null;
		
		if(isset($collection['post_type'])){
			$post_type = $collection['post_type'];
			$stream_id = $post_type;
		}

		if($module != null){
			$stream_id = $stream_id.'_'.$module;
		}

		//** Add to stream - usage-logging **//
		if(defined('REDIS_LOGGING_DB')){
			$redis = \aw2_library::redis_connect(REDIS_LOGGING_DB);
			$redis_ack = $redis->INCR($stream_id);

			if(!empty($redis_ack)){
				$return_value = array('status'=>'success', 'message'=>'Stream incremented successfully','data'=>$redis_ack);
			}else{
				$return_value = array('status'=>'error', 'message'=>'Unable to increase count in stream','data'=>$redis_ack);
			}
		}

	}
}
