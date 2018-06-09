<?php

add_action('wp', 'awesome_social::initiate_login');

add_action('init', 'awesome_social::start_php_session', 1);
add_action('wp_logout', 'awesome_social::end_php_session');
add_action('wp_login', 'awesome_social::end_php_session');


Class awesome_social{
	
	static function initiate_login(){
			
		if( isset($_REQUEST['provider']) && !is_user_logged_in()){
			self::login($_REQUEST['provider']);
		}		
	}
	
	static function login( $provider_name ){
		try {
			
			$monomyth_options=&aw2_library::get_array_ref('settings');
			$upload_dir = wp_upload_dir();
			
			if(!isset($monomyth_options['opt-yahoo-id']))
			{
				$monomyth_options['opt-yahoo-id']="";
				$monomyth_options['opt-yahoo-secret']="";
			}		
			if(!isset($monomyth_options['opt-google-id']))
			{
				$monomyth_options['opt-google-id']="";
				$monomyth_options['opt-google-secret']="";
			}	
			if(!isset($monomyth_options['opt-facebook-id']))
			{
				$monomyth_options['opt-facebook-id']="";
				$monomyth_options['opt-facebook-secret']="";
			}	
			if(!isset($monomyth_options['opt-twitter-id']))
			{
				$monomyth_options['opt-twitter-id']="";
				$monomyth_options['opt-twitter-secret']="";
			}	
			if(!isset($monomyth_options['opt-winlive-id']))
			{
				$monomyth_options['opt-winlive-id']="";
				$monomyth_options['opt-winlive-secret']="";
			}	
			$config = array(
				
				"base_url" => plugins_url( 'hybridauth/', __FILE__ ),

				"providers" => array (
					"Yahoo" => array (
						"enabled" => true,
						"keys"    => array ( "key" => $monomyth_options['opt-yahoo-id'], "secret" => $monomyth_options['opt-yahoo-secret'] ),
					),
					"Google" => array (
						"enabled" => true,
						"keys"    => array ( "id" => $monomyth_options['opt-google-id'], "secret" => $monomyth_options['opt-google-secret'] ),
						"scope"           => "https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email", // optional
						"access_type"     => "offline",   // optional
						"approval_prompt" => "force",     // optional
					),

					"Facebook" => array (
						"enabled" => true,
						"keys"    => array ( "id" => $monomyth_options['opt-facebook-id'], "secret" => $monomyth_options['opt-facebook-secret']),
						"trustForwarded" => false,
						"scope"          => "email,user_birthday,public_profile", // optional
					),

					"Twitter" => array (
						"enabled" => true,
						"keys"    => array ( "key" => $monomyth_options['opt-twitter-id'], "secret" => $monomyth_options['opt-twitter-secret'] )
					),

					// windows live
					"Live" => array (
						"enabled" => true,
						"keys"    => array ( "id" => get_option('opt-winlive-id'), "secret" => $monomyth_options['opt-winlive-secret'] )
					),

					"LinkedIn" => array (
						"enabled" => true,
						"keys"    => array ( "key" => $monomyth_options['opt-linkedin-id'], "secret" => $monomyth_options['opt-linkedin-secret'] )
					)
				),
				// If you want to enable logging, set 'debug_mode' to true.
				// You can also set it to
				// - "error" To log only error messages. Useful in production
				// - "info" To log info and error messages (ignore debug messages)
				"debug_mode" => true,
				// Path to file writable by the web server. Required if 'debug_mode' is not false
				"debug_file" => $upload_dir['basedir']."/log.html",
			);

			require_once( plugin_dir_path( __FILE__ )."hybridauth/Hybrid/Auth.php" );
		 
			// initialize Hybrid_Auth class with the config file
			$hybridauth = new Hybrid_Auth( $config );
			if(Hybrid_Auth::isConnectedWith($provider_name))
			{
				$adapter=Hybrid_Auth::getAdapter($provider_name);
				//$user_profile = $adapter->getUserProfile();
				//util::var_dump($user_profile);

			}
			else{
				// try to authenticate with the selected provider
				$adapter = $hybridauth->authenticate( $provider_name );
			}
			
			// then grab the user profile
			$user_profile = $adapter->getUserProfile();
			
		} catch( Exception $e ){
			// something went wrong?
			switch( $e->getCode() ){
			  case 0 : echo "Unspecified error."; break;
			  case 1 : echo "Hybriauth configuration error."; break;
			  case 2 : echo "Provider not properly configured."; break;
			  case 3 : echo "Unknown or disabled provider."; break;
			  case 4 : echo "Missing provider application credentials."; break;
			  case 5 : echo "Authentification failed. "
						  . "The user has canceled the authentication or the provider refused the connection.";
					   break;
			  case 6 : echo "User profile request failed. Most likely the user is not connected "
						  . "to the provider and he should authenticate again.";
					   $adapter->logout();
					   break;
			  case 7 : echo "User not connected to the provider.";
					   $adapter->logout();
					   break;
			  case 8 : echo "Provider does not support this feature."; break;
			}
 
			// well, basically your should not display this to the end user, just give him a hint and move on..
			//echo "<br /><br /><b>Original error message:</b> " . $e->getMessage();
			wp_die();
		}
		// check if slug is provided, in that case execute the slug and stop.
		if(isset($_GET['handled_by']) && $_GET['handled_by']=='me'){
			aw2_library::set('social_profile.user',$user_profile);
			aw2_library::set('social_profile.provider',$provider_name);
			aw2_library::set('social_profile.social_login','yes');
		} else {
			//else, create a WP_USER and redirect to initator page.
			// check if the current user already have authenticated using this provider before
			$user_exist = self::get_user_by_provider_and_id( $provider_name, $user_profile->identifier );
			
			// if the used didn't authenticate using the selected provider before
			// we create a new entry on database.users for him
			if( ! $user_exist ){	

				$_SESSION["social_login"]     = 'yes';
				$_SESSION["provider"]         = $provider_name;
				$_SESSION["email"]            = $user_profile->email;
				$_SESSION["firstName"]        = $user_profile->firstName;
				$_SESSION["lastName"]         = $user_profile->lastName;
				$_SESSION["provider_user_id"] = $user_profile->identifier;
				$_SESSION["firsttime"] = 1;	
					
			} elseif( self::sign_in_existing_user( $provider_name, $user_profile->identifier ) ){
				$_SESSION["user_connected"] = true;
			}
		}		
	}
	
	
	/**
	 *
	 * Get the user data from database by provider name and provider user id.
	 *
	 * @param  string  	Name of social login provider
	 * @param  string 	User id for provider
	 * @return boolean	User already already exist
	 */
	static function get_user_by_provider_and_id( $provider_name, $provider_user_id )
	{
		// WP_User_Query arguments
		$args = array (
			'order' => 'ASC',
			'orderby' => 'display_name',
			'meta_key' => $provider_name,
			'meta_value' => $provider_user_id
			
		);

		// Create the WP_User_Query object
		$wp_user_query = new WP_User_Query($args);

		// Get the results
		$users = $wp_user_query->get_results();

		// Check for results
		if (!empty($users))
			return true;
		
		return false;
	}
	
	/**
	 *
	 * Sign in existing user.
	 * 
	 * @param  string 	Name of social login provider
	 * @param  string 	User id for provider
	 * @return boolean 	Success of login process
	 */
	static function sign_in_existing_user( $provider_name, $provider_user_id ){
		// WP_User_Query arguments
		
		$args = array (
			'order' => 'ASC',
			'orderby' => 'display_name',
			'meta_key' => $provider_name,
			'meta_value' => $provider_user_id
			
		);
		
		if($provider_user_id==""){return false;}
		// Create the WP_User_Query object
		$wp_user_query = new WP_User_Query($args);

		// Get the results
		$users = $wp_user_query->get_results();

		// Check for results
		if (!empty($users)) {
			$user_id = $users[0]->ID;
			$user = get_user_by( 'id', $user_id ); 
			
			if( $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				do_action( 'wp_login', $user->user_login );
				return true;	
			}
			
			return false;
		} 
		
		return false;
	}
	
	static function start_php_session() {
		if(!session_id()) {
			session_start();
		}
	}

	/**
	 * End WordPress session.
	 */
	static function end_php_session() {
		session_destroy ();
	}

}







/**
 *
 * Get the user data from database by provider name and provider user id.
 * 
 * @param  string	$email 				Email of user
 * @param  string	$first_name 		First name 
 * @param  string	$last_name 			Last name
 * @param  string	$provider_name 		Provider name
 * @param  string 	$provider_user_id	Provider user id
 * @param  string   $password           Password of user 
 * @return object 	User object of wordpress user
 */
function create_new_hybridauth_user( $email, $first_name, $last_name, $provider_name, $provider_user_id, $password = '' )
{
	
	if( $password == '' ){
		$pwd = wp_generate_password();
	}else{
		$pwd = $password;
	}

	$userdata = array(
	    'user_login'  =>  $email,
	    'user_url'    =>  $website,
	    'user_pass'   =>  $pwd,  // When creating an user, `user_pass` is expected.,
	    'user_email'  =>  $email,
	    'first_name'  =>  $first_name,
	    'last_name'   =>  $last_name
	);

	$user_id = wp_insert_user( $userdata ); 

	add_user_meta( $user_id, $provider_name, $provider_user_id, true );


	$creds                  = array();
	$creds['user_login']    = $email;
	$creds['user_password'] = $pwd;
	$creds['remember']      = true;


	$user                   = wp_signon( $creds, false );

	return $user;
}

add_shortcode( 'social_create_user','social_create_user_callback');
add_shortcode( 'social_update_user','social_update_user_callback');


function social_create_user_callback($atts, $content=NULL,$shortcode)
{
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'main'          => '',
		'email'      => '',
		'provider_user_id' => '',
		'provider_name' => ''
	), $atts) );


    $email_id     	   = $email;
	$provider_user_id  = $provider_user_id;
	$provider_name     = $provider_name;
	$user_name = $email_id;
	$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
	
	
	$user_id = wp_create_user( $user_name, $random_password, $email_id );
	
	if($_REQUEST['first_name']!=""){
		wp_update_user( array( 'ID' => $user_id, 'first_name' => $_REQUEST['first_name']) );
	}
	if($_REQUEST['last_name']!=""){
		wp_update_user( array( 'ID' => $user_id, 'last_name'=>$_REQUEST['last_name']) );
	}	
	
	
	if($provider_name!="" && $provider_user_id!="")
		{
			add_user_meta( $user_id, $provider_name, $provider_user_id, true );
		}	
		
    
		$creds                  = array();
		$creds['user_login']    = $user_name;
		$creds['user_password'] = $random_password;
		$creds['remember']      = true;
		
		
		$user                   = wp_signon( $creds, false );
		
				
		if ( is_wp_error($user) ){
			 $error=$user->get_error_message();
			 $pos = strpos($error, 'incorrect');
				if (is_int($pos)) 
				{
					//its the right error so you can overwrite it
					$error = "Invalid login information!";
				}
			$return_value= $error;
		}else{
			$return_value= "success";
		}
		aw2_library::set('new_user_id',$user_id);
		if(aw2_library::set($atts,$return_value))return;
	return $return_value;
	
}

function social_update_user_callback($atts, $content=NULL,$shortcode)
{
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'main'          => '',
		'email'      => '',
		'provider_user_id' => '',
		'provider_name' => ''
	), $atts) );


    $email_id     	   = $email;
	$provider_user_id  = $provider_user_id;
	$provider_name     = $provider_name;
	$user_name = $email_id;
	$user = get_user_by( 'email', $email_id );
	if($provider_name!="" && $provider_user_id!="")
		{
			update_user_meta( $user->ID, $provider_name, $provider_user_id );
				if($_REQUEST['first_name']!=""){
					wp_update_user( array( 'ID' => $user->ID, 'first_name' => $_REQUEST['first_name']) );
				}
				if($_REQUEST['last_name']!=""){
					wp_update_user( array( 'ID' => $user->ID, 'last_name'=>$_REQUEST['last_name']) );
				}	
		}
					
			$args = array (
			    'order' => 'ASC',
			    'orderby' => 'display_name',
				'meta_key' => $provider_name,
			    'meta_value' => $provider_user_id
			    
			);
			
			if($provider_user_id=="")
			{
				$return_value="error code 1";
				if(aw2_library::set($atts,$return_value))return;
				return $return_value;
			}
			// Create the WP_User_Query object
			$wp_user_query = new WP_User_Query($args);

			// Get the results
			$users = $wp_user_query->get_results();

			// Check for results
			if (!empty($users)) {

			   	$user_id = $users[0]->ID;
				$user = get_user_by( 'id', $user_id ); 
				if( $user ) {
				    wp_set_current_user( $user_id, $user->user_login );
				    wp_set_auth_cookie( $user_id );
				    do_action( 'wp_login', $user->user_login );

				    $return_value= "success";
				}else{
					$return_value= "error code 2";
				}

			} else {
			   $return_value= "error code 3";
			}
	
		if(aw2_library::set($atts,$return_value))return;
	return $return_value;
	
}