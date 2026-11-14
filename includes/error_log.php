<?php 

class aw2_error_log{
	
	static function awesome_exception($location,$exception=null){
		if(!defined('DEVELOP_FOR_AWESOMEUI') || !DEVELOP_FOR_AWESOMEUI){
			$error_msg ='Something is wrong (000), enable debug to see details.';
			
			if(!LOG_EXCEPTIONS)
				return $error_msg;
		}
		
		$atts=array();
		if(empty($location)) return 'location is missing.';
		
		
		$atts['location']= $location;
		$atts['post_type']= aw2_library::get('env.@sc_exec.collection.post_type');
		$atts['connection']= aw2_library::get('env.@sc_exec.collection.connection');
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
		
		
		$flag=true;
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
		
		if($atts['exception_type']==='E_USER_NOTICE'){
			$flag=false;
		}
		if(!isset($error_msg))$error_msg ='Developer:Something is wrong ('.$error_id.')';	
		if($flag===true && isset($_COOKIE['debug_now'])){
			\util::var_dump($atts);
			\util::var_dump('wp_debug:'.WP_DEBUG);
			\util::var_dump('log_exceptions:'.LOG_EXCEPTIONS);
		}
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

		if((strpos($err_msg, 'open_basedir') !== false) && (strpos($err_file, 'matthiasmullie/minify/src/Minify.php') !== false)) return;


		$sc_exec=&aw2_library::get_array_ref('@sc_exec');
		$sc_exec['err_msg']=$err_msg;
		$sc_exec['err_file']=$err_file;
		$sc_exec['err_severity']=$err_severity;
		$sc_exec['err_line']=$err_line;
		
		$reply=self::awesome_exception('global_error_handler');
		
		return true;
	}

	static function log_datatype_mismatch($arr){
		// Staged for deletion. This method previously contained transaction-based logic that caused database deadlocks.
		return;
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

	private static function safe_truncate($val, $length) {
		if (is_null($val)) {
			return '';
		}
		$val = (string)$val;
		if (strlen($val) > $length) {
			return substr($val, 0, $length);
		}
		return $val;
	}

	private static function get_mysql_errno($mysqli, $e) {
		if (class_exists('ReflectionProperty')) {
			try {
				$ref = new ReflectionProperty($mysqli, 'mysqli');
				$ref->setAccessible(true);
				$conn = $ref->getValue($mysqli);
				if ($conn instanceof mysqli) {
					return $conn->errno;
				}
			} catch (Throwable $t) {
				// Fall back to message parsing
			}
		}
		return 0;
	}

	private static function migrate_database_schema($mysqli, $db_name) {
		try {
			$lock_res = $mysqli->query("SELECT GET_LOCK('awesome_exceptions_migrate', 0) as get_lock")->fetchAll("col");
			if (empty($lock_res) || (int)$lock_res[0] !== 1) {
				return false;
			}
		} catch (Throwable $t) {
			return false;
		}

		$migrated = false;
		try {
			$columns_list = $mysqli->query("SHOW COLUMNS FROM `$db_name`.`awesome_exceptions`")->fetchAll("assoc");
			$existing_cols = [];
			$id_col_type = '';
			foreach ($columns_list as $col) {
				$existing_cols[$col['Field']] = $col;
				if ($col['Field'] === 'ID') {
					$id_col_type = strtolower($col['Type']);
				}
			}

			$alters = [];
			if (strpos($id_col_type, 'unsigned') === false || strpos($id_col_type, 'bigint') === false) {
				$alters[] = "MODIFY `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT";
			}

			if (!isset($existing_cols['last_seen'])) {
				$alters[] = "ADD COLUMN `last_seen` TIMESTAMP NULL DEFAULT NULL";
			}

			if (!isset($existing_cols['exception_hash'])) {
				$alters[] = "ADD COLUMN `exception_hash` CHAR(32) CHARACTER SET ascii GENERATED ALWAYS AS (
					MD5(CONCAT_WS('|',
						IFNULL(`post_type`, ''), IFNULL(`source`, ''), IFNULL(`module`, ''),
						IFNULL(`position`, '-1'),  IFNULL(`errno`, ''),  IFNULL(`errfile`, ''),
						IFNULL(`errline`, '')
					))
				) STORED";
			}

			if (!empty($alters)) {
				$mysqli->query("ALTER TABLE `$db_name`.`awesome_exceptions` " . implode(", ", $alters));
			}

			$indexes = $mysqli->query("SHOW INDEX FROM `$db_name`.`awesome_exceptions`")->fetchAll("assoc");
			$has_unique_hash_index = false;
			foreach ($indexes as $index) {
				if ($index['Key_name'] === 'uk_exception_hash') {
					$has_unique_hash_index = true;
					break;
				}
			}

			if (!$has_unique_hash_index) {
				$duplicates = $mysqli->query("SELECT `exception_hash`, MIN(`ID`) AS `keep_id`, SUM(IFNULL(`no_of_times`, 1)) AS `total_times`, MAX(CASE WHEN `status` = 'active' THEN 1 ELSE 0 END) AS `has_active`
					FROM `$db_name`.`awesome_exceptions`
					GROUP BY `exception_hash`
					HAVING COUNT(*) > 1")->fetchAll("assoc");

				if (!empty($duplicates)) {
					foreach ($duplicates as $dup) {
						$hash = $dup['exception_hash'];
						$keep_id = $dup['keep_id'];
						$total_times = $dup['total_times'];
						$status = $dup['has_active'] ? 'active' : 'inactive';

						$mysqli->query("UPDATE `$db_name`.`awesome_exceptions` SET `no_of_times` = ?, `status` = ? WHERE `ID` = ?", [$total_times, $status, $keep_id], "isi");
						$mysqli->query("DELETE FROM `$db_name`.`awesome_exceptions` WHERE `exception_hash` = ? AND `ID` != ?", [$hash, $keep_id], "si");
					}
				}

				$mysqli->query("ALTER TABLE `$db_name`.`awesome_exceptions` ADD UNIQUE KEY `uk_exception_hash` (`exception_hash`)");
			}
			$migrated = true;
		} catch (Throwable $e) {
			error_log("aw2_error_log::migrate_database_schema failed: " . $e->getMessage());
			$migrated = false;
		} finally {
			try {
				$mysqli->query("SELECT RELEASE_LOCK('awesome_exceptions_migrate')");
			} catch (Throwable $t) {}
		}

		return $migrated;
	}

	static function save($atts){
		try {
			if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();
			$mysqli = \aw2_library::$mysqli;

			if(!defined('AWESOME_LOG_DB'))
				define('AWESOME_LOG_DB', DB_NAME);
			$db_name = AWESOME_LOG_DB;

			if(!is_array($atts)) return 'Not Logged';

			$post_type = isset($atts['post_type']) ? trim((string)$atts['post_type']) : '';
			$source = isset($atts['source']) ? trim((string)$atts['source']) : '';
			$module = isset($atts['module']) ? trim((string)$atts['module']) : '';
			
			$pos_val = isset($atts['position']) ? $atts['position'] : '';
			$position = empty($pos_val) ? '-1' : trim((string)$pos_val);
			
			$errno = isset($atts['errno']) ? trim((string)$atts['errno']) : '';
			$errfile = isset($atts['errfile']) ? trim((string)$atts['errfile']) : '';
			$errline = isset($atts['errline']) ? trim((string)$atts['errline']) : '';

			$exception_type = isset($atts['exception_type']) ? (string)$atts['exception_type'] : '';
			$location = isset($atts['location']) ? (string)$atts['location'] : '';
			$app_name = isset($atts['app_name']) ? (string)$atts['app_name'] : '';
			$sc = isset($atts['sc']) ? (string)$atts['sc'] : '';
			$link = isset($atts['link']) ? (string)$atts['link'] : '';
			$user = isset($atts['user']) ? (string)$atts['user'] : '';
			$header_value = isset($atts['header_value']) ? (string)$atts['header_value'] : '';
			$request = isset($atts['request']) ? (string)$atts['request'] : '';
			$sql_query = isset($atts['sql_query']) ? (string)$atts['sql_query'] : '';
			$url = isset($atts['url']) ? (string)$atts['url'] : '';
			$call_stack = isset($atts['call_stack']) ? (string)$atts['call_stack'] : '';
			$trace = isset($atts['trace']) ? (string)$atts['trace'] : '';
			$message = isset($atts['message']) ? (string)$atts['message'] : '';
			$status = isset($atts['status']) ? (string)$atts['status'] : 'active';

			$post_type = self::safe_truncate($post_type, 50);
			$source = self::safe_truncate($source, 255);
			$module = self::safe_truncate($module, 100);
			$errno = self::safe_truncate($errno, 20);
			$errfile = self::safe_truncate($errfile, 255);
			$errline = self::safe_truncate($errline, 100);
			
			$exception_type = self::safe_truncate($exception_type, 100);
			$location = self::safe_truncate($location, 50);
			$app_name = self::safe_truncate($app_name, 50);
			$link = self::safe_truncate($link, 500);
			$user = self::safe_truncate($user, 500);
			$url = self::safe_truncate($url, 255);
			$status = self::safe_truncate($status, 20);

			$sc = self::safe_truncate($sc, 250000);
			$header_value = self::safe_truncate($header_value, 250000);
			$request = self::safe_truncate($request, 250000);
			$sql_query = self::safe_truncate($sql_query, 60000);
			$call_stack = self::safe_truncate($call_stack, 250000);
			$trace = self::safe_truncate($trace, 250000);
			$message = self::safe_truncate($message, 250000);

			$max_retries = 3;
			$retry_count = 0;
			$use_fallback_insert = false;

			while (true) {
				try {
					if ($use_fallback_insert) {
						$sql = "INSERT INTO `$db_name`.`awesome_exceptions` (
							`exception_type`, `post_type`, `source`, `module`, `location`, `app_name`, `sc`, `position`, `link`, `user`, `header_data`, `request_data`, `sql_query`, `request_url`, `message`, `errno`, `errfile`, `errline`, `call_stack`, `trace`, `no_of_times`, `status`
						) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
						
						$values = [
							$exception_type, $post_type, $source, $module, $location, $app_name, $sc, $position, $link, $user, $header_value, $request, $sql_query, $url, $message, $errno, $errfile, $errline, $call_stack, $trace, $status
						];
						$types = "sssssssisssssssssssss";
						
						$mysqli->query($sql, $values, $types);
						return $mysqli->insertId();
					}

					$sql = "INSERT INTO `$db_name`.`awesome_exceptions` (
						`exception_type`, `post_type`, `source`, `module`, `location`, `app_name`, `sc`, `position`, `link`, `user`, `header_data`, `request_data`, `sql_query`, `request_url`, `message`, `errno`, `errfile`, `errline`, `call_stack`, `trace`, `no_of_times`, `status`
					) VALUES (
						?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?
					)
					ON DUPLICATE KEY UPDATE 
						`ID` = LAST_INSERT_ID(`ID`),
						`no_of_times` = `no_of_times` + 1,
						`status` = 'active',
						`last_seen` = CURRENT_TIMESTAMP,
						`request_url` = VALUES(`request_url`),
						`user` = VALUES(`user`),
						`message` = VALUES(`message`),
						`link` = VALUES(`link`)";

					$values = [
						$exception_type, $post_type, $source, $module, $location, $app_name, $sc, $position, $link, $user, $header_value, $request, $sql_query, $url, $message, $errno, $errfile, $errline, $call_stack, $trace, $status
					];
					$types = "sssssssisssssssssssss";

					$mysqli->query($sql, $values, $types);
					return $mysqli->insertId();

				} catch (Throwable $e) {
					$mysql_errno = self::get_mysql_errno($mysqli, $e);

					if (($mysql_errno === 1213 || $mysql_errno === 1205 || strpos($e->getMessage(), 'Deadlock found') !== false || strpos($e->getMessage(), 'Lock wait timeout') !== false) && $retry_count < $max_retries) {
						$retry_count++;
						usleep(50000 + random_int(0, 25000));
						continue;
					}

					if ($mysql_errno === 1054 || strpos($e->getMessage(), 'Unknown column') !== false || strpos($e->getMessage(), 'exception_hash') !== false) {
						$migrated = self::migrate_database_schema($mysqli, $db_name);
						if ($migrated) {
							continue;
						} else {
							$use_fallback_insert = true;
							continue;
						}
					}

					throw $e;
				}
			}

		} catch (Throwable $e) {
			error_log("aw2_error_log::save exception failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
			return 'Not Logged';
		}
	}

}
