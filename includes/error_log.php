<?php 

class aw2_error_log{
	
	static function awesome_exception($location,$exception=null){
		if(!DEVELOP_FOR_AWESOMEUI){
			$error_msg ='Something is wrong (000), enable debug to see details.';
			
			if(!LOG_EXCEPTIONS)
				return $error_msg;
		}
		
		$atts=array();
		if(empty($location)) return 'location is missing.';
		
		
		$atts['location']= $location;
		$atts['post_type']= aw2_library::get('env.@sc_exec.collection.post_type');
		$atts['source']= aw2_library::get('env.@sc_exec.collection.source');
		$atts['module']= aw2_library::get('env.@sc_exec.module');
		$atts['app_name']= aw2_library::get('env.app.name');
		$atts['sc']= aw2_library::get('env.@sc_exec.sc');
		
		$pos = aw2_library::get('env.@sc_exec.pos');
		$atts['position']= empty($pos)?"-1":$pos;
		unset($pos);
		
		$atts['link']= aw2_library::get('env.@sc_exec.link');
		$atts['sql_query']= aw2_library::get('env.@sc_exec.query');
		
		$atts['user']= aw2_library::get('app.user.email');
		$atts['url']= isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'';
		$atts['request']= empty($_REQUEST)?'':json_encode($_REQUEST);
		$atts['header_value']= file_get_contents('php://input');	
		
		$atts['call_stack']='';
		$stack=aw2_library::get('env.call_stack');
		$call_stack =array();
		if(!empty($stack)){
			foreach($stack as $entry){
				$post_type='';
				
				if(isset($entry['collection']['post_type']))
					$post_type=$entry['collection']['post_type'];
				else if(isset($entry['collection']['source']))
					$post_type=$entry['collection']['source'];
				
				$slug= isset($entry['slug'])?$entry['slug']:'';
				$obj_id= isset($entry['obj_id'])?$entry['obj_id']:'';
				$obj_type= isset($entry['obj_type'])?$entry['obj_type']:'';
				
				$call_stack[]=array(
					'obj_id'=>$obj_id,
					'obj_type'=>$obj_type,
					'slug'=>$slug,
					'post_type'=>$post_type
				);
			}
			
			unset($stack);
			$atts['call_stack'] = json_encode($call_stack);
			unset($call_stack);
		}
		$atts['message']=aw2_library::get('env.@sc_exec.err_msg');
		$atts['errno']=aw2_library::get('env.@sc_exec.err_severity');
		$atts['errfile']=aw2_library::get('env.@sc_exec.err_file');
		$atts['errline']=aw2_library::get('env.@sc_exec.err_line');
		$atts['trace']='';
		$atts['exception_type']='';
		
		if(!empty($atts['errno'])) {
			$atts['exception_type']= array_flip( array_slice( get_defined_constants(true)['Core'], 1, 15, true ) )[$atts['errno']];
			/* ob_start();
			debug_print_backtrace();
			$atts['trace']=ob_get_clean(); */
		}
		
		
		
		if(!is_null($exception)){
			$atts['exception_type'] = get_class($exception);
			$atts['errno'] = method_exists($exception,'getCode')? $exception->getCode() : '';
			$atts['message'] = method_exists($exception,'getMessage')? $exception->getMessage() : '';
			$atts['errfile'] = method_exists($exception,'getFile')? $exception->getFile() : '';
			$atts['errline'] = method_exists($exception,'getLine')? $exception->getLine() : '';
			//$atts['trace'] = method_exists($exception,'getTraceAsString')? $exception->getTraceAsString() : null;
			
		}
		
		$error_id='Not Logged';

		if(LOG_EXCEPTIONS)$error_id = self::save($atts);
		
		if(!isset($error_msg))$error_msg ='Developer:Something is wrong ('.$error_id.')';	

		if(\aw2_library::is_live_debug()){
			$live_debug_event=array();
			$live_debug_event['flow']='exception';

			$live_debug_event['action']='exception.error';
			$live_debug_event['error']='yes';
			$live_debug_event['error_type']='exception_error';
			$live_debug_event['atts']=$atts;
			$live_debug_event['exception_type']=$atts['exception_type'];
			
			
			\aw2\live_debug\publish_event(['event'=>$live_debug_event]);

		}
		
		
		
		$atts['error_db_id'] =$error_id;
		self::log_error($atts);
		return $error_msg;
		
	}

	static function log_error($atts){
		error_log("Custom Logging Start \r\n");
		error_log(print_r($atts, true));
		error_log("\r\n");
		error_log("\r\n Custom Logging End \r\n");
	}

	static function awesome_error_handler($err_severity, $err_msg, $err_file, $err_line){
		
		if($err_msg == 'mysqli::real_connect() expects parameter 5 to be integer, string given') return;
		
		if(strpos($err_file, 'wordpress-seo/inc/class-wpseo-meta.php') !== false) return;
		if(strpos($err_file, 'wp-admin/includes/file.php') !== false) return;
		if(strpos($err_file, 'wp-includes/capabilities.php') !== false) return;

		$sc_exec=&aw2_library::get_array_ref('@sc_exec');
		$sc_exec['err_msg']=$err_msg;
		$sc_exec['err_file']=$err_file;
		$sc_exec['err_severity']=$err_severity;
		$sc_exec['err_line']=$err_line;
		
		$reply=self::awesome_exception('global_error_handler');
		
		return true;
	}

	static function log_datatype_mismatch($arr){
		return;
		if(!WP_DEBUG){
			return;
		}
		
		$template=aw2_library::get('template.name');
		$post_type= aw2_library::get('env.@sc_exec.collection.post_type');
		$source= aw2_library::get('env.@sc_exec.collection.source');
		$module= aw2_library::get('env.@sc_exec.module');
		$app_name= aw2_library::get('env.app.name');
		$sc= addslashes(aw2_library::get('env.@sc_exec.sc'));/* */
		$url= isset($_SERVER['REQUEST_URI'])?addslashes($_SERVER['REQUEST_URI']):'';
		
		$pos = aw2_library::get('env.@sc_exec.pos');
		$position= empty($pos)?"-1":$pos;
		unset($pos);
		
		$link = aw2_library::get('env.@sc_exec.link');
		
		
		$conditional= isset($arr['condition'])?$arr['condition']:'';
		if(isset($arr['php7result'])){
			$php7_result = $arr['php7result']?'true':'false';
		}
		$module_slug='';
		$invalid_lhs_dt='no';
		$invalid_rhs_dt='no';
		$invalid_match='no';

		$lhs_datatype='lhs';
		$rhs_datatype='rhs';
		
		$flag=false;
	
		

		
		$lhs=isset($arr['lhs'])?$arr['lhs']:'_xxx_';
		
		if($lhs!=='_xxx_')$lhs_datatype=gettype($lhs);
		if($lhs_datatype === 'string' && empty($lhs)){
			$lhs='_empty_';
		}
		
		$lhs_dt=isset($arr['lhs_dt'])?$arr['lhs_dt']:'';
		$valid = self::datatype_test($lhs,$lhs_dt);
		if($valid === false ){
			$flag=true;
			$invalid_lhs_dt='yes';

		}


		$rhs=isset($arr['rhs'])?$arr['rhs']:'_xxx_';
		if($rhs!=='_xxx_')$rhs_datatype=gettype($rhs);
		if($rhs_datatype === 'string' && empty($rhs)){
			$rhs='_empty_';
		}
		
		$rhs_dt=isset($arr['rhs_dt'])?$arr['rhs_dt']:'';
		$valid = self::datatype_test($rhs,$rhs_dt);
		if($valid === false ){
			$flag=true;
			$invalid_rhs_dt='yes';

		}

		$must_match=isset($arr['must_match'])?$arr['must_match']:'no';
		
		if($must_match === 'yes'){
			if($lhs_datatype!==$rhs_datatype){
				$flag=true;
				$invalid_match='yes';			
			}
		}
			
		if($flag===false)return;
		
		
		if(is_object($lhs)|| is_array($lhs)){
			$lhs=serialize($lhs);
		}
		
		if(is_object($rhs)|| is_array($rhs)){
			$rhs=serialize($rhs);
		}
		
		if(!defined('AWESOME_LOG_DB'))
			define('AWESOME_LOG_DB', DB_NAME);
		

		//**Instantiate the DB Connection**//
		if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();
		
		$sql = "
		start TRANSACTION;
		set @post_type='".$post_type."';
		set @source='".addslashes($source)."';
		set @module='".$module."';
		set @pos='".$position."';
		set @template='".$template."';
		
		SELECT @id:=ID FROM `".AWESOME_LOG_DB."`.datatype_mismatch WHERE post_type=@post_type and source=@source and module_slug=@module and position=@pos and template_name=@template ;
	
		IF @id is null THEN
			
			INSERT INTO `".AWESOME_LOG_DB."`.`datatype_mismatch` (`app_name`,`module_slug`,`source`,`post_type`,`template_name`,`sc`,`position`,`request_url`,`conditional`,`php7_result`,`lhs_value`,`lhs_datatype`,`rhs_value`,`rhs_datatype`,`invalid_lhs_dt`,`invalid_rhs_dt`,`invalid_match`,`link`) VALUES ( '".$app_name."','".$module."','".$source."','".$post_type."','".$template."','".$sc."','".$position."','".$url."','".$conditional."','".$php7_result."','".$lhs."','".$lhs_datatype."','".$rhs."','".$rhs_datatype."','".$invalid_lhs_dt."','".$invalid_rhs_dt."','".$invalid_match."','".$link."');
			
		END IF;
			
		COMMIT;
		";
				
		$obj = \aw2_library::$mysqli->multi_query($sql);

	}

	static function datatype_test($val, $data_type){
		
		switch( $data_type){
			case 'number':
				return is_numeric($val);
				break;
			case 'boolean':
				return is_bool($val);
				break;
			case 'string':
				return is_string($val);
				break;
		}
		
		return true;
	}

	static function deprecated($params){

		
		$func=isset($params['func'])?$params['func']:'';
		$class=isset($params['class'])?$params['class']:'';
		$method=isset($params['method'])?$params['method']:'';

		$comment=isset($params['comment'])?$params['comment']:'';
		
		$comment .=' function: '.$func.' class: '.$class.' Method: '.$method;
		
		trigger_error($comment);
		unset($comment);
		
	}

	static function save($atts){
		//**Instantiate the DB Connection**//
		if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();
		
		
		//NULL, current_timestamp(), current_timestamp(),
		if(!is_array($atts)) return;

		$location=isset($atts['location'])?$atts['location']:'';
		$post_type=isset($atts['post_type'])?$atts['post_type']:'';
		$source=isset($atts['source'])?$atts['source']:'';
		$module=isset($atts['module'])?$atts['module']:'';
		$app_name=isset($atts['app_name'])?addslashes($atts['app_name']):'';
		$sc=isset($atts['sc'])?addslashes($atts['sc']):'';
		$position=isset($atts['position'])?$atts['position']:'';
		$link=isset($atts['link'])?addslashes($atts['link']):'';
		$message=isset($atts['message'])?addslashes($atts['message']):'';
		$errno=isset($atts['errno'])?$atts['errno']:'';
		$errfile=isset($atts['errfile'])?addslashes($atts['errfile']):'';
		$errline=isset($atts['errline'])?$atts['errline']:'';
		$trace=isset($atts['trace'])?addslashes($atts['trace']):'';
		$status=isset($atts['status'])?addslashes($atts['status']):'active';
		$exception_type=isset($atts['exception_type'])?addslashes($atts['exception_type']):'';
		$sql_query = isset($atts['sql_query'])?addslashes($atts['sql_query']):'';
		$user = isset($atts['user'])?$atts['user']:'';
		$url = isset($atts['url'])?$atts['url']:'';
		$request = isset($atts['request'])?addslashes($atts['request']):'';
		$header_value = isset($atts['header_value'])?addslashes($atts['header_value']):'';
		$call_stack = isset($atts['call_stack'])?$atts['call_stack']:'';
				
		if(!defined('AWESOME_LOG_DB'))
			define('AWESOME_LOG_DB', DB_NAME);
	/** 	
		$sql = "
		start TRANSACTION;
		set @post_type='".$post_type."';
		set @source='".$source."';
		set @module='".$module."';
		set @pos='".$position."';
		set @errno='".$errno."';
		set @errfile='".$errfile."';
		set @errline='".$errline."';
	
		SELECT @id:=ID FROM `".AWESOME_LOG_DB."`.`awesome_exceptions` WHERE post_type=@post_type and source=@source and module=@module and position=@pos and errno=@errno and errfile=@errfile and errline=@errline;
	
		IF @id is not null THEN
			UPDATE `".AWESOME_LOG_DB."`.`awesome_exceptions` SET no_of_times = no_of_times + 1,  status ='active' WHERE ID=@id;
			SELECT @id;
		ELSE
			INSERT INTO `".AWESOME_LOG_DB."`.`awesome_exceptions` (`exception_type`, `post_type`, `source`, `module`, `location`, `app_name`, `sc`, `position`, `link`,`user`, `header_data`,`request_data`,`sql_query`,`request_url`,`message`, `errno`, `errfile`, `errline`, `call_stack`,`trace`, `no_of_times`, `status`) VALUES ( '".$exception_type."', '".$post_type."', '".$source."', '".$module."', '".$location."', '".$app_name."', '".$sc."', '".$position."', '".$link."','".$user."', ' ".$header_value."','".$request."','".$sql_query."','".$url."','".$message."', '".$errno."', '".$errfile."', '".$errline."', '".$call_stack."','".$trace."', '1', '".$status."');
		
			SELECT LAST_INSERT_ID();
		END IF;
			
		
		COMMIT;
		";
*/
		//echo $sql;

		$sql ="
		START TRANSACTION;
		set @post_type='".$post_type."';
		set @source='".$source."';
		set @module='".$module."';
		set @pos='".$position."';
		set @errno='".$errno."';
		set @errfile='".$errfile."';
		set @errline='".$errline."';

		INSERT INTO `".AWESOME_LOG_DB."`.`awesome_exceptions`
		(`exception_type`, `post_type`, `source`, `module`, `location`, `app_name`, `sc`, `position`, `link`, `user`, `header_data`, `request_data`, `sql_query`, `request_url`, `message`, `errno`, `errfile`, `errline`, `call_stack`, `trace`, `no_of_times`, `status`) SELECT 
		'".$exception_type."' exception_type,
		  '".$post_type."' post_type,
		  '".$source."' source,
		  '".$module."' module,
		  '".$location."' location, 
		  '".$app_name."' app_name, 
		  '".$sc."' sc, 
		  '".$position."' position, 
		  '".$link."' link,
		  '".$user."' user, 
		  '".$header_value."' header_data,
		  '".$request."' request_data,
		  '".$sql_query."' sql_query,
		  '".$url."' request_url,
		  '".$message."' message, 
		  '".$errno."' errno, 
		  '".$errfile."' errfile, 
		  '".$errline."' errline, 
		  '".$call_stack."' call_stack,
		  '".$trace."' trace,
		   '0' no_of_times, 
		   'active' status
	FROM DUAL
	WHERE NOT EXISTS (
	  SELECT 1
	  FROM `".AWESOME_LOG_DB."`.`awesome_exceptions`
	  WHERE 
		 post_type = @post_type
	  AND source = @source
	  AND module = @module
	  AND position = @pos
	  AND errno = @errno
	  AND errfile = @errfile
	  AND errline = @errline
	  LIMIT 1
	);

		SELECT @id:=ID FROM `".AWESOME_LOG_DB."`.`awesome_exceptions` WHERE post_type = @post_type AND source = @source AND module = @module AND position = @pos AND errno = @errno AND errfile = @errfile AND errline = @errline LIMIT 1;
 

		UPDATE `".AWESOME_LOG_DB."`.`awesome_exceptions`
		SET no_of_times = no_of_times + 1
		WHERE ID = @id;

		SELECT @id;
		COMMIT;
		";
		
		$obj = \aw2_library::$mysqli->multi_query($sql);
		
	
		try {
			$result = $obj->fetchAll("col");
		} catch (Exception $e) {} 
		//this is added to handle the situation where for some reason above code fails and $result is not set.

		$last_insert_id='';

		if(is_array($result) &&!empty($result))
			$last_insert_id=$result[0];
				
		return $last_insert_id;
	}

}
