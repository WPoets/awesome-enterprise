<?php
namespace aw2\wp;

\aw2_library::add_service('wp.signon','Sign in a User',['namespace'=>__NAMESPACE__]);


function signon($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'username' =>null,
	'password'=>null,
	), $atts) );
	
	$creds = array();
	$creds['user_login'] = $username;
	$creds['user_password'] = $password;
	$user = wp_signon( $creds, false );
	
	$return_value='yes';
	if ( is_wp_error($user) )
		$return_value='no';
	else
		wp_set_current_user($user->ID);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


		