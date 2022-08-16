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



*/

namespace aw2\mysqli;

\aw2_library::add_service('mysqli.cud','Create/Update/Delete Query',['namespace'=>__NAMESPACE__]);
function cud($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

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
	if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();

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
		$cud = \aw2_library::$mysqli->query($sql);
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

\aw2_library::add_service('mysqli.fetch','Fetch Associative Array Query',['namespace'=>__NAMESPACE__]);	
function fetch($atts,$content=null,$shortcode){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='mysqli';
		$live_debug_event['action']='query.called';
		$live_debug_event['stream']='mysqli.fetch';
		$live_debug_event['mysqli_service']='mysqli.fetch';
		$live_debug_event['raw_query']=$content;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}
	
	//**Instantiate the DB Connection**//
	if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();
	
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
			$obj = \aw2_library::$mysqli->query($sql);
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

\aw2_library::add_service('mysqli.multi','Multi Queries',['namespace'=>__NAMESPACE__]);	
function multi($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='mysqli';
		$live_debug_event['action']='query.called';
		$live_debug_event['stream']='mysqli.multi';
		$live_debug_event['mysqli_service']='mysqli.multi';
		$live_debug_event['raw_query']=$content;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}
	
	//**Instantiate the DB Connection**//
	if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();

	$return_value = null;
	try{
	if(isset($shortcode['tags_left'][0])){
		$action=array_shift($shortcode['tags_left']);

	if(\aw2_library::is_live_debug()){
		$live_debug_event['fetch_type']=$action;
		$live_debug_event['tags_left']=$shortcode['tags_left'];
		\aw2_library::set('@live_debug.multi_query',$live_debug_event);
	}
		
		if($action==='fetch')$return_value=multi_fetch($atts,$content,$shortcode['tags_left']);
		if($action==='cud')$return_value=multi_cud($atts,$content,$shortcode['tags_left']);
		if($action==='search')$return_value=multi_search($atts,$content,$shortcode['tags_left']);
		if($action==='self')$return_value=multi_self($atts,$content,$shortcode['tags_left']);
		
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
			\aw2_library::set('@live_debug.multi_query','');			
		}
		
		throw $e;
	}	
	
	\aw2_library::$mysqli->close();
	 \aw2_library::$mysqli =null;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	
	
	return $return_value;	
	
}

function multi_self($atts,$content,$tags_left){
	$return_value = array();
	
    //**Parse the query from content**//
    $sql=\aw2_library::parse_shortcode($content);
	
	if(empty($return_value)){
		$obj = \aw2_library::$mysqli->multi_query($sql);
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


function multi_search($atts,$content,$tags_left){


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
	
	$obj = \aw2_library::$mysqli->multi_query($sql);
	
	if((isset($atts['dataset']['transpose']) && $atts['dataset']['transpose'] == "yes") || (isset($atts['dataset']['transform']) && $atts['dataset']['transform'] == "yes") ){
		$return_value=$obj->fetchTranspose(true);	
	}else{
		$return_value=$obj->fetchTranspose(false);
	}
	
	$return_value = array_merge($atts['dataset'],$return_value);
	
	return $return_value;
}

function multi_read_committed($atts,$content,$tags_left){
	
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
			$obj = \aw2_library::$mysqli->multi_query($sql);
			$return_value=common_fetch($obj,$action);	
			$return_value['sql']=$sql;				
		}else{
			$cud = \aw2_library::$mysqli->multi_query($sql);
			$return_value['status']="success";
			$return_value['message']="Success";
			$return_value['matched_rows']=$cud->rowsMatched();
			$return_value['affected_rows']=$cud->affectedRows();	
			$return_value['sql']=$sql;
		}
	}
	return $return_value;
		
}

function multi_fetch($atts,$content,$tags_left){
	
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
	//echo $sql;
	if(empty($return_value)){
		if(isset($tags_left[0])){
			$action=$tags_left[0];
			$obj = \aw2_library::$mysqli->multi_query($sql);
			$return_value=common_fetch($obj,$action);	
			$return_value['sql']=$sql;				
		}else{
			throw new \SimpleMySQLiException("Query should have exactly 3 parts");
		}
	}
	
	return $return_value;
		
} 
 


function multi_cud($atts,$content,$tags_left){
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
		if(isset($shortcode['tags_left'][0]))$isolation=$shortcode['tags_left'];
		
		if($isolation=='repeatable_read')
			$sql='SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;' . PHP_EOL . $sql ;

		if($isolation=='read_committed')
			$sql='SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;' . PHP_EOL . $sql ;
		
			
		
    if(empty($return_value)){
        $cud = \aw2_library::$mysqli->multi_query($sql);
        $return_value['status']="success";
        $return_value['message']="Success";
        $return_value['matched_rows']=$cud->rowsMatched();
        $return_value['affected_rows']=$cud->affectedRows();    
        $return_value['sql']=$sql;    
    }
    return $return_value;
}


\aw2_library::add_service('mysqli.transaction','Multi Queries with transaction',['namespace'=>__NAMESPACE__]);	
function transaction($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( \aw2_library::shortcode_atts( array(
	'isolation'=>'read_committed'
	), $atts) );


	if(\aw2_library::is_live_debug()){
		
		$live_debug_event=array();
		$live_debug_event['flow']='mysqli';
		$live_debug_event['action']='query.called';
		$live_debug_event['stream']='mysqli.transaction';
		$live_debug_event['mysqli_service']='mysqli.transaction';
		$live_debug_event['raw_query']=$content;
		$live_debug_event['isolation']=$isolation;
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}


	
	//**Instantiate the DB Connection**//
	if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();

	$return_value = null;
	try{
	
	if(isset($shortcode['tags_left'][0])){
		$action=array_shift($shortcode['tags_left']);		


		if(\aw2_library::is_live_debug()){
			$live_debug_event['tags_left']=$shortcode['tags_left'];
			\aw2_library::set('@live_debug.multi_query',$live_debug_event);
		}
	
		$return_value=transaction_exec($content,$action,$isolation);		
		
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
function transaction_exec($content,$action,$isolation='read_committed'){
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
		$cud = \aw2_library::$mysqli->multi_query($sql);
		$return_value['status']="success";
		$return_value['message']="Success";
		$return_value['matched_rows']=$cud->rowsMatched();
		$return_value['affected_rows']=$cud->affectedRows();	
		$return_value['sql']=$sql;
	}
	return $return_value;
}
