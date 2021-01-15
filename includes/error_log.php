<?php 

class aw2_error_log{
	
	static function save($atts){
		$nmysqli = new SimpleMySQLi(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, "utf8mb4", "assoc");
		$nmysqli->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
		
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
		$request = isset($atts['request'])?$atts['request']:'';
		$header_value = isset($atts['header_value'])?$atts['header_value']:'';
		$call_stack = isset($atts['call_stack'])?$atts['call_stack']:'';
				
		if(!defined('AWESOME_LOG_DB'))
			define('AWESOME_LOG_DB', DB_NAME);
		
		$sql = "
		start TRANSACTION;
		set @post_type='".$post_type."';
		set @source='".$source."';
		set @module='".$module."';
		set @pos='".$position."';
		set @errno='".$errno."';
		set @errfile='".$errfile."';
		set @errline='".$errline."';
	
		SELECT @id:=ID FROM ".AWESOME_LOG_DB.".awesome_exceptions WHERE post_type=@post_type and source=@source and module=@module and position=@pos and errno=@errno and errfile=@errfile and errline=@errline;
	
		IF @id is not null THEN
			UPDATE ".AWESOME_LOG_DB.".awesome_exceptions SET no_of_times = no_of_times + 1,  status ='active' WHERE ID=@id;
			SELECT @id;
		ELSE
			INSERT INTO ".AWESOME_LOG_DB.".`awesome_exceptions` (`exception_type`, `post_type`, `source`, `module`, `location`, `app_name`, `sc`, `position`, `link`,`user`, `header_data`,`request_data`,`sql_query`,`request_url`,`message`, `errno`, `errfile`, `errline`, `call_stack`,`trace`, `no_of_times`, `status`) VALUES ( '".$exception_type."', '".$post_type."', '".$source."', '".$module."', '".$location."', '".$app_name."', '".$sc."', '".$position."', '".$link."','".$user."', '".$header_value."','".$request."','".$sql_query."','".$url."','".$message."', '".$errno."', '".$errfile."', '".$errline."', '".$call_stack."','".$trace."', '1', '".$status."');
		
			SELECT LAST_INSERT_ID();
		END IF;
			
		
		COMMIT;
		";

		//echo $sql;
		
		$obj = $nmysqli->multi_query($sql);
		
	
		$result = $obj->fetchAll("col");
		$last_insert_id='';

		if(is_array($result))
			$last_insert_id=$result[0];
			
		return $last_insert_id;
	}

}