<?php
namespace aw2\facebook;

// Include required libraries
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

\aw2_library::add_service('facebook','facebook api support',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('facebook.login_url','returns the login URL for facebook',['namespace'=>__NAMESPACE__]);

function login_url($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'ticket_id'=>null,
	'scope'=>null,
	'app_id'=>null,
	'app_secret'=>null
	), $atts) );
	
	if(empty($ticket_id)) return '';
	if(empty($app_id) || empty($app_secret)) return '';	
	
	$path = plugin_dir_path( __DIR__ );
	
	require_once $path.'libraries/social/Facebook/autoload.php';
	
	$return_value='';

	$fb = new Facebook(array(
			'app_id' => $app_id,
			'app_secret' => $app_secret,
			'default_graph_version' => 'v2.10',
		));
	// Get redirect login helper
	$helper = $fb->getRedirectLoginHelper();
	
	$app_path = \aw2_library::get('app.path');
	
	$scope = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'scope'],null,null);
	$scope= explode(',',$scope);
	
	
	
	 $return_value = $helper->getLoginUrl(SITE_URL .'?social_auth=facebook', $scope);
		
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}


\aw2_library::add_service('facebook.auth','Check the auth for facebook',['namespace'=>__NAMESPACE__]);

function auth($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'ticket_id'=>null,
	'scope'=>null,
	'app_id'=>null,
	'app_secret'=>null
	), $atts) );
	
	if(empty($ticket_id)) return '';
	if(empty($app_id) || empty($app_secret)) return '';	
	
	$path = plugin_dir_path( __DIR__ );
	
	require_once $path.'libraries/social/Facebook/autoload.php';
	
	$return_value='';

	$fb = new Facebook(array(
			'app_id' => $app_id,
			'app_secret' => $app_secret,
			'default_graph_version' => 'v2.10',
		));
		
	$app_path = \aw2_library::get('app.path');
	
	$helper = $fb->getRedirectLoginHelper();

	$helper->getPersistentDataHandler()->set('state', $_REQUEST['state']);
	
	try {
		$accessToken = $helper->getAccessToken(SITE_URL .'/?social_auth=facebook');
		
	} catch(FacebookResponseException $e) {
		 \aw2_library::user_notice('Graph returned an error: '. $e->getMessage());
		return '';

	} catch(FacebookSDKException $e) {
		\aw2_library::user_notice('Facebook SDK returned an error: ' . $e->getMessage());
		return '';
	}
	
	if (!isset($accessToken)) {
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'error'],null,null);
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'description',"value"=>$_REQUEST['error_description']],null,null);
	  return;
	}
	
	$oAuth2Client = $fb->getOAuth2Client();
        
	// Exchanges a short-lived access token for a long-lived one
	$long_access_token = $oAuth2Client->getLongLivedAccessToken($accessToken);
	
	$acc = \util::var_dump($long_access_token,true);
	
	\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'access_token',"value"=>$acc],null,null);
	// Set default access token to be used in script
	$fb->setDefaultAccessToken($long_access_token);	
	$return_value='';
	try {
		
        $profileRequest = $fb->get('/me?fields=name,first_name,last_name,email,link,gender,locale,cover,picture');
        $return_value = $profileRequest->getGraphNode()->asArray();
    } catch(FacebookResponseException $e) {
         \aw2_library::user_notice('Graph returned an error: '. $e->getMessage());
		return '';
    } catch(FacebookSDKException $e) {
        \aw2_library::user_notice('Facebook SDK returned an error: ' . $e->getMessage());
		return '';
    }		
		  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'success'],null,null);

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

if(IS_WP){
	\add_action( 'wp', 'aw2\facebook\auth_check', 10, 3 );

	function auth_check(){
		
		if(!isset($_REQUEST['social_auth'])) return;
		if($_REQUEST['social_auth'] !== 'facebook') return;
		
		$ticket_id = $_COOKIE['facebook_login'];
		
		$query_string=explode('&',$_SERVER["QUERY_STRING"]);

		
		array_shift($query_string);
		$query_string =  implode('&',$query_string);
		
		$app_path = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'app_path'],null,null);
		
		wp_redirect($app_path.'/t/'.$ticket_id.'?'.$query_string);

		die();
	}
}