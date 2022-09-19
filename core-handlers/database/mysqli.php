<?php


namespace aw2\mysqli;

//\aw2_library::add_service('mysqli.cud','Create/Update/Delete Query',['namespace'=>__NAMESPACE__]);
function cud($atts,$content=null,$shortcode=null){
	
    
	//**Instantiate the DB Connection**//
	
	$atts['dbserver']=\aw2_library::get_default_db_conn();
	return \aw2\dbconn\cud($atts,$content,$shortcode);
}

//\aw2_library::add_service('mysqli.fetch','Fetch Associative Array Query',['namespace'=>__NAMESPACE__]);	
function fetch($atts,$content=null,$shortcode){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	$atts['dbserver']=\aw2_library::get_default_db_conn();
	return \aw2\dbconn\fetch($atts,$content,$shortcode);

}

//\aw2_library::add_service('mysqli.multi','Multi Queries',['namespace'=>__NAMESPACE__]);	
function multi($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	$atts['dbserver']=\aw2_library::get_default_db_conn();
	return \aw2\dbconn\multi($atts,$content,$shortcode);
}




//\aw2_library::add_service('mysqli.transaction','Multi Queries with transaction',['namespace'=>__NAMESPACE__]);	
function transaction($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	
	$atts['dbserver']=\aw2_library::get_default_db_conn();
	return \aw2\dbconn\transaction($atts,$content,$shortcode);
}

