<?php

/*
mysqli.fetch.rows
mysqli.fetch.row
<name value pairs>
mysqli.fetch.exactly_one_row
<name value pairs>
mysqli.fetch.col
mysqli.fetch.scalar
mysqli.fetch.grouped
mysqli.fetch.count
mysqli.fetch.meta_keys


mysqli.multi.fetch.sets
mysqli.multi.fetch.rows
mysqli.multi.fetch.row
mysqli.multi.fetch.exactly_one_row
mysqli.multi.fetch.col
mysqli.multi.fetch.scalar
mysqli.multi.fetch.grouped
mysqli.multi.fetch.count
mysqli.multi.fetch.meta_keys

mysqli.transaction.commit
mysqli.transaction.rollback
	same as mysqli.multi.fetch.rows

[dbserver.connect db_connection=external_db   o.set=settings.connections.secondary_server /]

[dbconn.register notification_log db_name=logs conn_path=settings.connections.secondary_server /]

[dbconn.register mysqli db_name=loantap_in conn_path=settings.connections.secondary_server /]


*/
namespace aw2\dbserver;

\aw2_library::add_service('dbserver.connect','Create a new connection',['namespace'=>__NAMESPACE__]);

function connect($atts,$content=null,$shortcode=null){
/*
	Creates a connect to the db server. The db name is not there and therefore you cannot fire queries
	[dbserver.connect db_connection=base   o.set=settings.connections.content_server /]
*/
	
	//php8OK
	extract( \aw2_library::shortcode_atts( array(
	'db_connection'=>null,
	), $atts) );
	
	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='dbserver';
		$live_debug_event['action']='dbserver.connect';
		$live_debug_event['stream']='dbserver.connect';

	}
	if(!defined('DB_CONNECTIONS')){
		/**
		define('DB_CONNECTIONS',
			array(
				'primary_db'=>array(
					'host'=>DB_HOST,
					'user'=>DB_USER,
					'password'=>DB_PASSWORD
				)
			));		
		**/	
		if(\aw2_library::is_live_debug()){
			
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']='DB_CONNECTIONS - connection is not defined';
			$temp_debug['error_type']='db_conn_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#FFC3C3']);
		}
		return '';
	}
	$db_conections = DB_CONNECTIONS;
	
	if(is_null($db_connection) ) {
		
		if(\aw2_library::is_live_debug()){
			
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']='db_connection/DB_CONNECTIONS - connection not specified';
			$temp_debug['error_type']='db_conn_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#FFC3C3']);
		}
	}
	
	
	
	
	if(!isset($db_conections[$db_connection])){
		if(\aw2_library::is_live_debug()){
			
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']='db_connection '.$db_connection.' is not defined.';
			$temp_debug['error_type']='query_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#FFC3C3']);
		}
	}
	
	$return_value = new \SimpleMySQLi($db_conections[$db_connection]['host'], $db_conections[$db_connection]['user'], $db_conections[$db_connection]['password'], '', "utf8mb4", "assoc");
	$return_value->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


namespace aw2\dbconn;


function add_comment_to_sql($sql){
	$comment='/* ' 
	. 	' app:' .\aw2_library::get('app.slug')
	. 	' module:' .\aw2_library::get('module.slug')
	. 	' post_type:' .\aw2_library::get('module.collection.post_type')
	. 	' template:' .\aw2_library::get('template.name')
	. 	' service_id:' .\aw2_library::get('module.collection.service_id')
	. 	' connection:' .\aw2_library::get('module.collection.connection')
	. 	' user:' .\aw2_library::get('app.user.email')
	. '*/' ;
	
	return 	$comment . $sql . $comment;
	
}


\aw2_library::add_service('dbconn.register','Create a new connection',['namespace'=>__NAMESPACE__]);

function register($atts,$content=null,$shortcode=null){
/*
Registers a db conn. Points to a database at run time. WIll switch the connection to the suggested db
*/
	//php8OK
	extract( \aw2_library::shortcode_atts( array(
	'main'=>null,
	'conn_path'=>null,
	'db_name'=>null,
	'db_connection'=>null,
	'desc'=>''
	), $atts) );

	if(is_null($db_name))throw new Exception('db_name is not defined');
	if(is_null($conn_path))throw new Exception('conn_path is not defined');
	if(is_null($main))throw new Exception('main is not defined');
	
	if(is_null($db_connection) && !defined('MYSQLI_CONNECTION')) throw new Exception('db_conection is not passed and MYSQLI_CONNECTION is not defined');
	
	if(is_null($db_connection)) $db_connection=MYSQLI_CONNECTION;


	//\aw2_library::set('settings.connections.' . $main,$conn);
	$p=array();
	$p['conn_path']=$conn_path;
	$p['db_name']=$db_name;
	$p['db_connection']=$db_connection;
	$p['namespace']=__NAMESPACE__;
	$p['func']='conn_handler';
	\aw2_library::add_service($main,$desc,$p);

	return;
}


function conn_handler($atts,$content=null,$shortcode=null){
	//php8OK
	
	$service='dbconn.' . implode('.', $shortcode['tags_left']);
	
	$conn_path=$shortcode['handler']['conn_path'];
	
	$mysqli=\aw2_library::get($conn_path);

	$db_name=$shortcode['handler']['db_name'];
	$db_connection=$shortcode['handler']['db_connection'];
	//$result=$mysqli->select_db($db_name);
	$db_conections = DB_CONNECTIONS;

	if(!isset($db_conections[$db_connection])){
		if(\aw2_library::is_live_debug()){
			
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']='db_connection '.$db_connection.' is not defined.';
			$temp_debug['error_type']='query_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#FFC3C3']);
		}
	}

	$result=$mysqli->change_user($db_conections[$db_connection]['user'], $db_conections[$db_connection]['password'], $db_name);

	if(\aw2_library::is_live_debug() && !$result){
		
		$live_debug_event=array();
		$live_debug_event['flow']='dbconn';
		$live_debug_event['action']='dbconn.called';
		$live_debug_event['stream']='conn_handler';
		$temp_debug=$live_debug_event;
		$temp_debug['error']='yes';
		$temp_debug['error_message']=$db_name .' is not selected';
		$temp_debug['error_type']='query_error';
		\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#FFC3C3']);

	}
	
	$atts['dbserver']=$mysqli;
	
	$return_value =\aw2_library::service_run($service,$atts,$content,'service');
	return $return_value;
}



\aw2_library::add_service('dbconn.cud','Create/Update/Delete Query',['namespace'=>__NAMESPACE__]);
function cud($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( \aw2_library::shortcode_atts( array(
	'dbserver'=>null
	), $atts) );

	if(is_null($dbserver))throw new Exception('Database connection is not defined');
	
	//**Instantiate the DB Connection**//
	$mysqli=$dbserver;
	unset($dbserver);
	
	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='mysqli';
		$live_debug_event['action']='query.called';
		$live_debug_event['stream']='mysqli.cud';
		$live_debug_event['mysqli_service']='mysqli.cud';
		$live_debug_event['raw_query']=$content;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}

    
	//**Instantiate the DB Connection**//
	//if(!$mysqli)$mysqli = \aw2_library::new_mysqli();

	$return_value = array();
	$start=microtime(true);

	//**Parse the query from content**//
	$sql=\aw2_library::parse_shortcode($content);

	
	if(\aw2_library::is_live_debug()){
		$live_debug_event['action']='query.executing';
		$live_debug_event['start_time']=$start;
		$live_debug_event['built_query']=$sql;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#DFDFDE']);
	}

	try{
	if(empty($return_value)){
		$cud = $mysqli->query(add_comment_to_sql($sql));
		$return_value['status']="success";
		$return_value['message']="Success";
		$return_value['matched_rows']=$cud->rowsMatched();
		$return_value['affected_rows']=$cud->affectedRows();	
		
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='query.executed';
			
			$debug_stop_time=microtime(true);
			$live_debug_event['stop_time']=$debug_stop_time;
			$live_debug_event['execution_time']=round($debug_stop_time - $start,3)*1000;
			$live_debug_event['result_array']=$return_value;
			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#F0EBE3']);
		}
		
	}
	
	}
	catch(\Throwable $e){
		$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
		$sc_exec['query']=$sql;
		

		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='query.error';
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']=print_r($e,true);
			$temp_debug['error_type']='query_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#FFC3C3']);
		}

		
		throw $e;
	}
	if(\aw2_library::get('debug_config.mysqli')==='yes')\aw2\debug\query(['start'=>$start,'main'=>$sql]);		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('dbconn.fetch','Fetch Associative Array Query',['namespace'=>__NAMESPACE__]);	
function fetch($atts,$content=null,$shortcode){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract( \aw2_library::shortcode_atts( array(
	'dbserver'=>null
	), $atts) );

	if(is_null($dbserver))throw new Exception('Database connection is not defined');
	
	//**Instantiate the DB Connection**//
	$mysqli=$dbserver;
	unset($dbserver);
	
	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='mysqli';
		$live_debug_event['action']='query.called';
		$live_debug_event['stream']='mysqli.fetch';
		$live_debug_event['mysqli_service']='mysqli.fetch';
		$live_debug_event['raw_query']=$content;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}
	
	
	$return_value = array();
	$start=microtime(true);
	//**Parse the query from content**//
	$sql=\aw2_library::parse_shortcode($content);
	
	if(\aw2_library::is_live_debug()){
		$live_debug_event['action']='query.executing';
		$live_debug_event['start_time']=$start;
		$live_debug_event['built_query']=$sql;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#DFDFDE']);
	}

	
	try{
	if(empty($return_value)){
		if(isset($shortcode['tags_left'][0])){
			$action=$shortcode['tags_left'][0];
			$obj = $mysqli->query(add_comment_to_sql($sql));
			$return_value=common_fetch($obj,$action);
			$return_value['sql']=$sql;				
			
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='query.executed';
			$live_debug_event['fetch_type']=$action;
			$debug_stop_time=microtime(true);
			$live_debug_event['stop_time']=$debug_stop_time;
			$live_debug_event['execution_time']=round($debug_stop_time - $start,3)*1000;
			$live_debug_event['result_array']=$return_value;
			\aw2\live_debug\publish_event(['event'=>$live_debug_event]);
		}
		
		
			
		}else{
			throw new \SimpleMySQLiException("Query should have exactly 3 parts");
		}
	}
	}
	catch(\Throwable $e){
		$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
		$sc_exec['query']=$content;
		
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='query.error';
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']=print_r($e,true);
			$temp_debug['error_type']='query_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug, 'bgcolor'=>'#FFC3C3']);
		}
		
		throw $e;
	}
	if(\aw2_library::get('debug_config.mysqli')==='yes')\aw2\debug\query(['start'=>$start,'main'=>$sql]);			
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function common_fetch($obj,$action){

	$return_value=[];
	$return_value['status']="success";
	switch($action){
		case 'rows':
			$result = $obj->fetchAll("assoc");
			$result = is_null($result) ? [] : $result;
			$return_value['rows']=$result;
			$return_value['message']=(!$result) ? "No rows found" : count($result)." rows found";
		break;
		case 'row':
			$result = $obj->fetch("assoc");
			$result = is_null($result) ? [] : $result;
			$return_value['row']=$result;
			$return_value['message']=(empty($row)) ? "No data found" : "One row found";
		break;
		case 'exactly_one_row':
			$result = $obj->fetchAll("assoc");
			$result = is_null($result) ? [] : $result;
			if(count($result)>1 || count($result)===0){
				throw new \SimpleMySQLiException("Result should return 1 and exactly 1 row");
			}
			else{
				$return_value['status']="success";
				$return_value['message']="One row found";
				$return_value['found_rows']=count($result);
				$return_value['row']=array_shift($result);
			}
		break;
		case 'col':
			$result = $obj->fetchAll("col");
			$result = is_null($result) ? [] : $result;
			$return_value['col']=$result;
			$return_value['message']=(!$result) ? "No rows found" : count($result)." rows found";
		break;
		case 'scalar':
			$result = $obj->fetchAll("scalar");
			$result = is_null($result) ? [] : $result;
			if(!empty($result))
				$return_value['scalar']=$result[0];
			
			$return_value['message']=(count($result) < 1) ? "No result found" : "One result found";
		break;
		case 'count':
			$result = $obj->fetchAll("count");
			$result = is_null($result) ? [] : $result;
			$return_value['count']=$result[0];
			
			$return_value['message']=(count($result) < 1) ? "No result found" : "One result found";
		break;
		case 'grouped':
			$result = $obj->fetchAll("group");
			$result = is_null($result) ? [] : $result;
			$return_value['rows']=$result;
			$return_value['message']=(!$result) ? "No rows found" : count($result)." rows found";
		break;
		case 'meta_keys':
			$result = $obj->fetchAll("metaKeys");
			$result = is_null($result) ? [] : $result;
			$return_value['rows']=$result;
			$return_value['message']=(!$result) ? "No rows found" : count($result)." rows found";
		break;
		
	}				
	//**Set found rows for all except exactly_one_row**//
	if(!isset($return_value['found_rows']))
		$return_value['found_rows']=count($result);	
	
	return $return_value;
}

\aw2_library::add_service('dbconn.multi','Multi Queries',['namespace'=>__NAMESPACE__]);	
function multi($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract( \aw2_library::shortcode_atts( array(
	'dbserver'=>null
	), $atts) );

	if(is_null($dbserver))throw new Exception('Database connection is not defined');
	
	//**Instantiate the DB Connection**//
	$mysqli=$dbserver;
	unset($dbserver);
	$start=microtime(true);
	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='mysqli';
		$live_debug_event['action']='query.called';
		$live_debug_event['stream']='mysqli.multi';
		$live_debug_event['mysqli_service']='mysqli.multi';
		$live_debug_event['raw_query']=$content;
		$live_debug_event['start_time'] = $start;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}
	
	//**Instantiate the DB Connection**//
	//if(!$mysqli)$mysqli = \aw2_library::new_mysqli();

	$return_value = null;
	try{
	if(isset($shortcode['tags_left'][0])){
		$action=array_shift($shortcode['tags_left']);

		if(\aw2_library::is_live_debug()){
			$live_debug_event['fetch_type']=$action;
			$live_debug_event['tags_left']=$shortcode['tags_left'];
			\aw2_library::set('@live_debug.multi_query',$live_debug_event);
		}
		
		if($action==='fetch')$return_value=multi_fetch($mysqli,$atts,$content,$shortcode['tags_left']);
		if($action==='cud')$return_value=multi_cud($mysqli,$atts,$content,$shortcode['tags_left']);
		if($action==='search')$return_value=multi_search($mysqli,$atts,$content,$shortcode['tags_left']);
		if($action==='self')$return_value=multi_self($mysqli,$atts,$content,$shortcode['tags_left']);
		
		//if($action==='read_committed')$return_value=multi_read_committed($atts,$content,$shortcode['tags_left']);
		if(\aw2_library::is_live_debug()){
			$live_debug_event=\aw2_library::get('@live_debug.multi_query');
			$live_debug_event['action']='query.executed';
			$debug_stop_time=microtime(true);
			$live_debug_event['stop_time']=$debug_stop_time;
			$live_debug_event['execution_time']=round($debug_stop_time - $live_debug_event['start_time'],3)*1000;
			$live_debug_event['result_array']=$return_value;
			\aw2\live_debug\publish_event(['event'=>$live_debug_event]);
			\aw2_library::set('@live_debug.multi_query','');			
		}

		
	}else{
		throw new \SimpleMySQLiException("Tags missing in Multi");
	}
	}
	catch(Exception $e){
		$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
		$sc_exec['query']=$content;
		
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='query.error';
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']=print_r($e,true);
			$temp_debug['error_type']='query_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug, 'bgcolor'=>'#FFC3C3']);
	}
		
		throw $e;
	}	
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	
	
	return $return_value;	
	
}

function multi_self($mysqli,$atts,$content,$tags_left){
	$return_value = array();
	
    //**Parse the query from content**//
    $sql=\aw2_library::parse_shortcode($content);
	
	if(empty($return_value)){
		$obj = $mysqli->multi_query(add_comment_to_sql($sql));
		$result = $obj->fetchAll("assoc");
		$result = is_null($result) ? [] : $result;
        $return_value['status']="success";
        $return_value['message']="Success";
		$return_value['rows']=$result;
		$return_value['message']=(!$result) ? "No rows found" : count($result)." rows found";
		$return_value['sql']=$sql;				
	}
	return $return_value;
}


function multi_search($mysqli,$atts,$content,$tags_left){


	$return_value = array();
	
	//**Prepare the query**//
	$sql="SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; ". PHP_EOL . $atts['dataset']['query'];


	$start=microtime(true);
	if(\aw2_library::is_live_debug()){
		$live_debug_event=\aw2_library::get('@live_debug.multi_query');
		$live_debug_event['action']='query.executing';
		$live_debug_event['start_time']=$start;
		$live_debug_event['built_query']=$sql;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#DFDFDE']);
		\aw2_library::set('@live_debug.multi_query',$live_debug_event);
	}
	
	$obj = $mysqli->multi_query(add_comment_to_sql($sql));
	
	if((isset($atts['dataset']['transpose']) && $atts['dataset']['transpose'] == "yes") || (isset($atts['dataset']['transform']) && $atts['dataset']['transform'] == "yes") ){
		$return_value=$obj->fetchTranspose(true);	
	}else{
		$return_value=$obj->fetchTranspose(false);
	}
	
	$return_value = array_merge($atts['dataset'],$return_value);
	
	return $return_value;
}

function multi_read_committed($mysqli,$atts,$content,$tags_left){
	
	$return_value = array();
	
	//**throw exception if query is empty**//
	if(empty($content)){
		throw new \SimpleMySQLiException("Query cannot be empty");
	}
	
	//**Parse the query from content**//
	$sql="SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; ". PHP_EOL . 	\aw2_library::parse_shortcode($content);

	$start=microtime(true);
	if(\aw2_library::is_live_debug()){
		$live_debug_event=\aw2_library::get('@live_debug.multi_query');
		$live_debug_event['action']='query.executing';
		$live_debug_event['start_time']=$start;
		$live_debug_event['built_query']=$sql;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#DFDFDE']);
		\aw2_library::set('@live_debug.multi_query',$live_debug_event);
	}
	
	if(empty($return_value)){
		if(isset($tags_left[0])){
			$action=$tags_left[0];
			$obj = $mysqli->multi_query(add_comment_to_sql($sql));
			$return_value=common_fetch($obj,$action);	
			$return_value['sql']=$sql;				
		}else{
			$cud = $mysqli->multi_query(add_comment_to_sql($sql));
			$return_value['status']="success";
			$return_value['message']="Success";
			$return_value['matched_rows']=$cud->rowsMatched();
			$return_value['affected_rows']=$cud->affectedRows();	
			$return_value['sql']=$sql;
		}
	}
	return $return_value;
		
}

function multi_fetch($mysqli,$atts,$content,$tags_left){
	
	$return_value = array();
	
	//**Prepare the query**//
	$sql="SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;". PHP_EOL . \aw2_library::parse_shortcode($content);

	$start=microtime(true);
	if(\aw2_library::is_live_debug()){
		$live_debug_event=\aw2_library::get('@live_debug.multi_query');
		$live_debug_event['action']='query.executing';
		$live_debug_event['start_time']=$start;
		$live_debug_event['built_query']=$sql;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#DFDFDE']);
		\aw2_library::set('@live_debug.multi_query',$live_debug_event);
	}	
	 
	if(empty($return_value)){
		if(isset($tags_left[0])){
			$action=$tags_left[0];
			$obj = $mysqli->multi_query(add_comment_to_sql($sql));
			$return_value=common_fetch($obj,$action);	
			$return_value['sql']=$sql;				
		}else{
			throw new \SimpleMySQLiException("Query should have exactly 3 parts");
		}
	}
	
	return $return_value;
		
} 
 


function multi_cud($mysqli,$atts,$content,$tags_left){
    $return_value = array();
	
    //**Parse the query from content**//
    $sql=\aw2_library::parse_shortcode($content);

	$start=microtime(true);
	if(\aw2_library::is_live_debug()){
		$live_debug_event=\aw2_library::get('@live_debug.multi_query');
		$live_debug_event['action']='query.executing';
		$live_debug_event['start_time']=$start;
		$live_debug_event['built_query']=$sql;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#DFDFDE']);
		\aw2_library::set('@live_debug.multi_query',$live_debug_event);
	}
	
		$isolation='read_committed';
		if(isset($tags_left[0]))$isolation=$tags_left[0];
		
		if($isolation=='repeatable_read')
			$sql='SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;' . PHP_EOL . $sql ;

		if($isolation=='read_committed')
			$sql='SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;' . PHP_EOL . $sql ;
			
		
    if(empty($return_value)){
        $cud = $mysqli->multi_query(add_comment_to_sql($sql));
        $return_value['status']="success";
        $return_value['message']="Success";
        $return_value['matched_rows']=$cud->rowsMatched();
        $return_value['affected_rows']=$cud->affectedRows();    
        $return_value['sql']=$sql;    
    }
    return $return_value;
}


\aw2_library::add_service('dbconn.transaction','Multi Queries with transaction',['namespace'=>__NAMESPACE__]);	
function transaction($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( \aw2_library::shortcode_atts( array(
	'isolation'=>'read_committed',
	'dbserver'=>null
	), $atts) );

	if(is_null($dbserver))throw new Exception('Database connection is not defined');
	
	//**Instantiate the DB Connection**//
	$mysqli=$dbserver;
	unset($dbserver);
	$start=microtime(true);
	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='mysqli';
		$live_debug_event['action']='query.called';
		$live_debug_event['stream']='mysqli.transaction';
		$live_debug_event['mysqli_service']='mysqli.transaction';
		$live_debug_event['start_time']=$start;
		$live_debug_event['raw_query']=$content;
		$live_debug_event['isolation']=$isolation;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}


	
	//**Instantiate the DB Connection**//
	//if(!$mysqli)$mysqli = \aw2_library::new_mysqli();

	$return_value = null;
	try{
	
	if(isset($shortcode['tags_left'][0])){
		$action=array_shift($shortcode['tags_left']);		


		if(\aw2_library::is_live_debug()){
			$live_debug_event['tags_left']=$shortcode['tags_left'];
			\aw2_library::set('@live_debug.multi_query',$live_debug_event);
		}
	
		$return_value=transaction_exec($mysqli,$content,$action,$isolation);		
		
		if(\aw2_library::is_live_debug()){
			$live_debug_event=\aw2_library::get('@live_debug.multi_query');
			$live_debug_event['action']='query.executed';
			$debug_stop_time=microtime(true);
			$live_debug_event['stop_time']=$debug_stop_time;
			$live_debug_event['execution_time']=round($debug_stop_time - $live_debug_event['start_time'],3)*1000;
			$live_debug_event['result_array']=$return_value;
			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#F0EBE3']);
			\aw2_library::set('@live_debug.multi_query','');			
		}
		
	}else{
		throw new \SimpleMySQLiException("Tags missing in Multi");
	}
	
	}
	catch(\Throwable $e){
		$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
		$sc_exec['query']=$content;
		
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='query.error';
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_message']=print_r($e,true);
			$temp_debug['error_type']='query_error';
			\aw2\live_debug\publish_event(['event'=>$temp_debug, 'bgcolor'=>'#FFC3C3']);
			\aw2_library::set('@live_debug.multi_query','');			
		}
		
		throw $e;
	}	
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
	
}

/**
param $action can be commit OR rollback
**/
function transaction_exec($mysqli,$content,$action,$isolation='read_committed'){
	
	$return_value = array();
	
	$isolation_statement ='';
	
	if($isolation=='repeatable_read')
		$isolation_statement='SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;';

	if($isolation=='read_committed')
		$isolation_statement='SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;';

	$start=microtime(true);
	//**Prepend "start transaction; " and append $action to the query and parse from content**//
	$sql=$isolation_statement . PHP_EOL . "start transaction; ". PHP_EOL . \aw2_library::parse_shortcode($content). " ". PHP_EOL . $action.";";

	if(\aw2_library::is_live_debug()){
		$live_debug_event=\aw2_library::get('@live_debug.multi_query');
		$live_debug_event['action']='query.executing';
		$live_debug_event['start_time']=$start;
		$live_debug_event['built_query']=$sql;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#F0EBE3']);
		\aw2_library::set('@live_debug.multi_query',$live_debug_event);
	}
	
	if(empty($return_value)){
		$cud = $mysqli->multi_query(add_comment_to_sql($sql));
		$return_value['status']="success";
		$return_value['message']="Success";
		$return_value['matched_rows']=$cud->rowsMatched();
		$return_value['affected_rows']=$cud->affectedRows();	
		$return_value['sql']=$sql;
	}
	return $return_value;
}


