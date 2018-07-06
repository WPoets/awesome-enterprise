<?php
/**
 *
 * Get the user data from database by provider name and provider user id.
 *
 * @param  string  	Name of social login provider
 * @param  string 	User id for provider
 * @return boolean	User already already exist
 */
function get_user_by_provider_and_id( $provider_name, $provider_user_id )
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
	if (!empty($users)) {
	    return true;
	} else {
	    return false;
	}
}

/**
 *
 * Sign in existing user.
 * 
 * @param  string 	Name of social login provider
 * @param  string 	User id for provider
 * @return boolean 	Success of login process
 */
function sign_in_existing_user( $provider_name, $provider_user_id ){
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
			   /*var_dump($users); 
			   echo $users[0]->user_login;*/

			   	$user_id = $users[0]->ID;
				$user = get_user_by( 'id', $user_id ); 
				
				if( $user ) {
				    wp_set_current_user( $user_id, $user->user_login );
				    wp_set_auth_cookie( $user_id );
				    do_action( 'wp_login', $user->user_login );
				    //echo "<pre>one".$user->data->user_login;print_r($user);echo "</pre>"; echo "cool";
					return true;	
				}else{
				
					return false;
				}

			} else {
			   return false;
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

/**
 *
 * Execute procedure to use social login functionality
 * 
 * @param  string  	Name of social login service provider
 * @return boolean 	Login success
 */
function monoframe_social_login_callback( $provider_name ){
	try
	{
		$monomyth_options=&aw2_library::get_array_ref('site_settings');
		// inlcude HybridAuth library
		// change the following paths if necessary
		//$config   = plugin_dir_path( __FILE__ ). 'hybridauth/config.php';
		$config   =array(
		"base_url" => site_url()."/wp-content/plugins/awesome-studio/monoframe/hybridauth/",

		"providers" => array (
			// openid providers
			"OpenID" => array (
				"enabled" => true
			),

			"Yahoo" => array (
				"enabled" => true,
				"keys"    => array ( "key" => @$monomyth_options['opt-yahoo-id'], "secret" => @$monomyth_options['opt-yahoo-secret'] ),
			),

			"AOL"  => array (
				"enabled" => true
			),

			"Google" => array (
				"enabled" => true,
				"keys"    => array ( "id" => @$monomyth_options['opt-google-id'], "secret" => @$monomyth_options['opt-google-secret'] ),
			),

			"Facebook" => array (
				"enabled" => true,
				"keys"    => array ( "id" => $monomyth_options['opt-facebook-id'], "secret" => $monomyth_options['opt-facebook-secret']),
				"trustForwarded" => false,
				"scope"          => "email, user_about_me, user_birthday, user_hometown", // optional
			),

			"Twitter" => array (
				"enabled" => true,
				"keys"    => array ( "key" => @$monomyth_options['opt-twitter-id'], "secret" => @$monomyth_options['opt-twitter-secret'] )
			),

			// windows live
			"Live" => array (
				"enabled" => true,
				"keys"    => array ( "id" => @$monomyth_options['opt-winlive-id'], "secret" => @$monomyth_options['opt-winlive-secret'] )
			),

			"LinkedIn" => array (
				"enabled" => true,
				"keys"    => array ( "key" => @$monomyth_options['opt-linkedin-id'], "secret" => @$monomyth_options['opt-linkedin-secret'] )
			),

			"Foursquare" => array (
				"enabled" => true,
				"keys"    => array ( "id" => "", "secret" => "" )
			),
		),

		// If you want to enable logging, set 'debug_mode' to true.
		// You can also set it to
		// - "error" To log only error messages. Useful in production
		// - "info" To log info and error messages (ignore debug messages)
		"debug_mode" => false,
		

		// Path to file writable by the web server. Required if 'debug_mode' is not false
		"debug_file" => "logfile.txt",
	);
	
		require_once( plugin_dir_path( __FILE__ )."hybridauth/Hybrid/Auth.php" );

		// initialize Hybrid_Auth class with the config file
		$hybridauth = new Hybrid_Auth( $config );
 		
		// try to authenticate with the selected provider
		$adapter = $hybridauth->authenticate( $provider_name );

 
		// then grab the user profile
		$user_profile = $adapter->getUserProfile();
		
	}
 
	// something went wrong?
	catch( Exception $e )
	{ 
		//util::var_dump($e);
		//echo "error";
	}
 	
	// check if the current user already have authenticated using this provider before
	$user_exist = get_user_by_provider_and_id( $provider_name, $user_profile->identifier );
	
	// if the used didn't authenticate using the selected provider before
	// we create a new entry on database.users for him
	if(!$user_profile){return false;}
	if( ! $user_exist ){	

		$_SESSION["social_login"]     = 'yes';
		$_SESSION["provider"]         = $provider_name;
		$_SESSION["email"]            = $user_profile->email;
		$_SESSION["firstName"]        = $user_profile->firstName;
		$_SESSION["lastName"]         = $user_profile->lastName;
		$_SESSION["provider_user_id"] = $user_profile->identifier;
		$_SESSION["profile_url"] = $user_profile->profileURL;
		$_SESSION["profile_photo"] = $user_profile->photoURL;
		$_SESSION["firsttime"] = 1;	
		return false; //login was interupted as sign up needed
			
	}else{
		
		if( sign_in_existing_user( $provider_name, $user_profile->identifier ) ){
			$_SESSION["user_connected"] = true;
			return true;
		}else{
			
			return false;
		}
	}

}

add_action('init', 'monomythStartSession', 1);
add_action('wp_logout', 'monomythEndSession');
add_action('wp_login', 'monomythEndSession');

/**
 * Start WordPress session.
 */
function monomythStartSession() {
    if(!session_id()) {
        session_start();
    }
}

/**
 * End WordPress session.
 */
function monomythEndSession() {
    session_destroy ();
}


/**
 *
 * Return and handle social login form.
 * 
 * @return $string HTML containing form for social login 
 */	
function monoframe_social_login_form(){
	
	if( isset($_REQUEST['provider'])  ){
		if (!is_user_logged_in())
		{
			if( monoframe_social_login_callback($_REQUEST['provider']) ){   
				return "login successful";
				//return $script = '<script>location.reload();</script>';
			}else{
				return "login Not successful";
				
			}
		}	
		
		
		
	}	
}

add_action('wp', 'monoframe_social_login_form');
//add_shortcode( 'monoframe_social_login','monoframe_social_login_form'	);

function register_monoframe_social_login_settings() {
	//register our settings
	register_setting( 'monoframe-social-login-settings-group', 'monoframe_facebook_app_id' );
	register_setting( 'monoframe-social-login-settings-group', 'monoframe_facebook_app_secret' );
	register_setting( 'monoframe-social-login-settings-group', 'monoframe_twitter_app_key' );
	register_setting( 'monoframe-social-login-settings-group', 'monoframe_twitter_app_secret' );
	register_setting( 'monoframe-social-login-settings-group', 'monoframe_google_app_id' );
	register_setting( 'monoframe-social-login-settings-group', 'monoframe_google_app_secret' );
	register_setting( 'monoframe-social-login-settings-group', 'monoframe_redirect_after_login' );
}


function monoframe_social_login_subpage_callback() {
?>	
	<div class="wrap"><div id="icon-tools" class="icon32"></div>
		<h2>Social Login Options</h2>

		<form method="post" enctype="multipart/form-data" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

			<table class="form-table">

			<tr valign="top">
			<th scope="row">Facebook</th>
			<td>App ID<input type="text" name="monoframe_facebook_app_id" value="<?php echo get_option('monoframe_facebook_app_id'); ?>" /></td>
			<td>Secret<input type="text" name="monoframe_facebook_app_secret" value="<?php echo get_option('monoframe_facebook_app_secret'); ?>" /></td>
			</tr>
			 
			<tr valign="top">
			<th scope="row">Twitter</th>
			<td>App Key<input type="text" name="monoframe_twitter_app_key" value="<?php echo get_option('monoframe_twitter_app_key'); ?>" /></td>
			<td>Secret<input type="text" name="monoframe_twitter_app_secret" value="<?php echo get_option('monoframe_twitter_app_secret'); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">Google</th>
			<td>App ID<input type="text" name="monoframe_google_app_id" value="<?php echo get_option('monoframe_google_app_id'); ?>" /></td>
			<td>Secret<input type="text" name="monoframe_google_app_secret" value="<?php echo get_option('monoframe_google_app_secret'); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">Redirect after login</th>
			<td><input type="text" name="monoframe_redirect_after_login" value="<?php echo get_option('monoframe_redirect_after_login'); ?>" /></td>
			</tr>

			</table>

			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="monoframe_facebook_app_id,monoframe_facebook_app_secret,monoframe_twitter_app_key,monoframe_twitter_app_secret,monoframe_google_app_id,monoframe_google_app_secret,monoframe_redirect_after_login" />

			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>

		</form>

	</div>
<?php
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