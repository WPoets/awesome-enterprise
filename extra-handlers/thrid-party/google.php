<?php
namespace aw2\google;

\aw2_library::add_service('google','google api support',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('google.login_url','returns the login URL for google',['namespace'=>__NAMESPACE__]);

function login_url($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'ticket_id'=>null,
	'scope'=>null,
	'app_id'=>null,
	'version'=>7.4,
	'app_secret'=>null
	), $atts) );
	
	if(empty($ticket_id)) return '';
	if(empty($app_id) || empty($app_secret)) return '';	
	
	$path = plugin_dir_path( __DIR__ );
	if($version=='7.0')
		require_once $path.'libraries/social/google-api/php7/vendor/autoload.php';
	else
		require_once $path.'libraries/social/google-api/php74/vendor/autoload.php';
	
	$return_value='';
	
	$scope = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'scope'],null,null);
	$scopes= explode(',',$scope);
	
	
	// create Client Request to access Google API
	$client = new \Google_Client();
	$client->setClientId($app_id);
	$client->setClientSecret($app_secret);
	$client->setRedirectUri(SITE_URL.'?social_auth=google');
	foreach($scopes as $s){
		$client->addScope($s);
	}
	
	$client->setAccessType('offline');
	
	
	
	
	//$client = new Client($app_id,$app_secret) ;
	
	
    $return_value = $client->createAuthUrl(); // get url on Google to start linking
  
    //\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'state',"value"=>$client->getState()],null,null); // save state for future validation
    //\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'redirect_url',"value"=>$return_value],null,null); // save state for future validation
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}


\aw2_library::add_service('google.auth','Check the auth for linkedin',['namespace'=>__NAMESPACE__]);

function auth($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'ticket_id'=>null,
	'scope'=>null,
	'version'=>7.4,
	'app_id'=>null,
	'app_secret'=>null
	), $atts) );
	
	if(empty($ticket_id)) return '';
	if(empty($app_id) || empty($app_secret)) return '';	
	
	$path = plugin_dir_path( __DIR__ );
	
	if($version=='7.0')
		require_once $path.'libraries/social/google-api/php7/vendor/autoload.php';
	else
		require_once $path.'libraries/social/google-api/php74/vendor/autoload.php';
	
	
	if (isset($_GET['error']) || !isset($_GET['code'])) {
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'error'],null,null);
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'description',"value"=>$_REQUEST['error_description']],null,null);
	  return;
	}
	$return_value='';
	
	$app_path = \aw2_library::get('app.path');
	
	$client = new \Google_Client();
	$client->setClientId($app_id);
	$client->setClientSecret($app_secret);
	$client->setRedirectUri(SITE_URL.'?social_auth=google');
	
	$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
	$client->setAccessToken($token['access_token']);
  
 
	\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'access_token',"value"=>$token['access_token']],null,null);
	// perform api call to get profile information
	// get profile info
	$google_oauth = new \Google_Service_Oauth2($client);
	
	$return_value = $google_oauth->userinfo->get();
			   
    \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'success'],null,null);

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
if(IS_WP){
	\add_action( 'wp', 'aw2\google\auth_check', 11 );

		
	function auth_check(){

		if(!isset($_REQUEST['social_auth'])) return;
		
		if($_REQUEST['social_auth'] !== 'google') return;
		
		$ticket_id = $_COOKIE['google_login'];
		
		$query_string=explode('&',$_SERVER["QUERY_STRING"]);

		array_shift($query_string);
		
		$query_string =  implode('&',$query_string);
		
		$app_path = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'app_path'],null,null);
		
		

		wp_redirect($app_path.'/t/'.$ticket_id.'?'.$query_string);

		die();
	}
}	