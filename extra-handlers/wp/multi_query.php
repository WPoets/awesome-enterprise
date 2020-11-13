<?php
namespace aw2\multi;

\aw2_library::add_service('multi','Multi Query Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('multi.update','Update Multi Query',['namespace'=>__NAMESPACE__]);


function update($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	if(!\aw2_library::$conn)\aw2_library::$conn = new \mysqli(DB_HOST,DB_USER , DB_PASSWORD, DB_NAME);
	// Check connection
	if (\aw2_library::$conn->connect_error)die("Connection failed: ");
	
	\aw2_library::$conn->set_charset("utf8mb4");
	\aw2_library::$conn->query("SET collation_connection = utf8_unicode_ci");
	
	
	$sql=\aw2_library::parse_shortcode($content);
	
	//fix for % placeholder, that was introduced with esc_sql
	global $wpdb;
	$sql = $wpdb->remove_placeholder_escape($sql);
	
	if(mysqli_multi_query(\aw2_library::$conn,$sql)){
			do{
				if (mysqli_store_result(\aw2_library::$conn)) {}
				
				} while(mysqli_more_results(\aw2_library::$conn) && mysqli_next_result(\aw2_library::$conn));
	}
	if( mysqli_errno(\aw2_library::$conn))
	{
		/*
		category = db 
  error_file  __file__
  error_line __line__
  error_function __function__
  error_code mysqli_errno,
  int_reason mysqli_error,
  ext_reason  Unexpected Error 
	
  html '<h1>ERROR</h1>
					<span style="color:red;">' . mysqli_error(\aw2_library::$conn) . '</span>'
	
		*/
			die(
					'<h1>ERROR</h1>
					<span style="color:red;">' . mysqli_error(\aw2_library::$conn) . '</span>'
			);
	}
	
}

\aw2_library::add_service('multi.select','Select Multi Query',['namespace'=>__NAMESPACE__]);

function select($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	if(!\aw2_library::$conn)\aw2_library::$conn = new \mysqli(DB_HOST,DB_USER , DB_PASSWORD, DB_NAME);
	// Check connection
	if (\aw2_library::$conn->connect_error)die("Connection failed: ");
	
	\aw2_library::$conn->set_charset("utf8mb4");
	\aw2_library::$conn->query("SET collation_connection = utf8_unicode_ci");
	
	$sql=\aw2_library::parse_shortcode($content);
	
	//fix for % placeholder, that was introduced with esc_sql
	global $wpdb;
	$sql = $wpdb->remove_placeholder_escape($sql);
	
	$return_value=array();		
	if(mysqli_multi_query(\aw2_library::$conn,$sql)){
			do{
				if ($result=mysqli_store_result(\aw2_library::$conn)) {
					$return_value=array();
					for($i = 0; $return_value[$i] = mysqli_fetch_assoc($result); $i++) ;
					array_pop($return_value);
					// Free result set
					//mysqli_free_result($result);
				}
				} while(mysqli_more_results(\aw2_library::$conn) && mysqli_next_result(\aw2_library::$conn));
	}
	
	if( mysqli_errno(\aw2_library::$conn))
	{
			die(
					'<h1>ERROR</h1>
					<span style="color:red;">' . mysqli_error(\aw2_library::$conn) . '</span>'
			);
	}
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


