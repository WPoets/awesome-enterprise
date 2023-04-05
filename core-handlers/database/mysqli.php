<?php


namespace aw2\mysqli;
function get_default_conn($base,$s){
	$shortcode=array();
	
	if(!empty($s))
		$shortcode['tags_left']=array_unshift($s['tags_left'],$base); 
	else
		$shortcode['tags_left']=array($base);

	$shortcode['handler']['conn_path'] = 'connections.db.mysqli_db';
	$shortcode['handler']['db_name'] = DB_NAME;
	$shortcode['handler']['db_connection'] = MYSQLI_CONNECTION;

	return $shortcode;	
}
//\aw2_library::add_service('mysqli.cud','Create/Update/Delete Query',['namespace'=>__NAMESPACE__]);
function cud($atts,$content=null,$shortcode=null){
	
		
//	$mysqli=\aw2_library::get($conn_path);

//	$db_name=$shortcode['handler']['db_name'];
	//$db_connection=$shortcode['handler']['db_connection'];
    
	//**Instantiate the DB Connection**//
	

	//$atts['dbserver']=\aw2_library::get_default_db_conn();
	$s = get_default_conn('cud',$shortcode);
	return \aw2\dbconn\conn_handler($atts,$content,$s);
}

//\aw2_library::add_service('mysqli.fetch','Fetch Associative Array Query',['namespace'=>__NAMESPACE__]);	
function fetch($atts,$content=null,$shortcode){
	
	//if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	//$atts['dbserver']=\aw2_library::get_default_db_conn();
	//return \aw2\dbconn\fetch($atts,$content,$shortcode);
	$s = get_default_conn('fetch',$shortcode);
	return \aw2\dbconn\conn_handler($atts,$content,$s);

}

//\aw2_library::add_service('mysqli.multi','Multi Queries',['namespace'=>__NAMESPACE__]);	
function multi($atts,$content=null,$shortcode){
	//if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	//$atts['dbserver']=\aw2_library::get_default_db_conn();
	//return \aw2\dbconn\multi($atts,$content,$shortcode);
	$s = get_default_conn('multi',$shortcode);
	return \aw2\dbconn\conn_handler($atts,$content,$s);
}




//\aw2_library::add_service('mysqli.transaction','Multi Queries with transaction',['namespace'=>__NAMESPACE__]);	
function transaction($atts,$content=null,$shortcode){
	//if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	
	//$atts['dbserver']=\aw2_library::get_default_db_conn();
	//return \aw2\dbconn\transaction($atts,$content,$shortcode);
	$s = get_default_conn('transaction',$shortcode);
	return \aw2\dbconn\conn_handler($atts,$content,$s);
}