<?php



aw2_library::add_shortcode('aw2','destroy_sessions', 'awesome2_destroy_sessions','Destroy Session');

function awesome2_destroy_sessions($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
 	extract( shortcode_atts( array(
		'main'=>null,
	), $atts) );
	
	global $wp_session;
	$user_id = get_current_user_id();
	$session = wp_get_session_token();
	$sessions = WP_Session_Tokens::get_instance($user_id);
	if($main=='all')
		$sessions->destroy_all();
	else
	$sessions->destroy_others($session);
}

