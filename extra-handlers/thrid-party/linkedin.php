<?php
namespace aw2\linkedin;

use LinkedIn\AccessToken;
use LinkedIn\Client;
use LinkedIn\Scope;
use LinkedIn\AbstractEnum;


\aw2_library::add_service('linkedin','linkedin api support',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('linkedin.login_url','returns the login URL for linkedin',['namespace'=>__NAMESPACE__]);

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
	
	require_once $path.'libraries/social/linkedin/vendor/autoload.php';
	
	$return_value='';
	
	$client = new Client($app_id,$app_secret) ;
	
	$scope = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'scope'],null,null);
	$scopes= explode(',',$scope);
	
	$client->setRedirectUrl(SITE_URL.'?social_auth=linkedin');
    $return_value = $client->getLoginUrl($scopes); // get url on LinkedIn to start linking
  
    \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'state',"value"=>$client->getState()],null,null); // save state for future validation
    \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'redirect_url',"value"=>$client->getRedirectUrl()],null,null); // save state for future validation
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}


\aw2_library::add_service('linkedin.auth','Check the auth for linkedin',['namespace'=>__NAMESPACE__]);

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
	
	require_once $path.'libraries/social/linkedin/vendor/autoload.php';
	
	
	if (isset($_GET['error']) || !isset($_GET['code'])) {
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'error'],null,null);
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'description',"value"=>$_REQUEST['error_description']],null,null);
	  return;
	}
		
	
	$client = new Client($app_id,$app_secret) ;
	
	 try {
            // you have to set initially used redirect url to be able
            // to retrieve access token
            $client->setRedirectUrl(SITE_URL.'?social_auth=linkedin');
            // retrieve access token using code provided by LinkedIn
            $accessToken = $client->getAccessToken($_GET['code']);
           \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'access_token',"value"=>$accessToken],null,null);
            // perform api call to get profile information
            $return_value = $client->get(
                'people/~:(id,email-address,first-name,last-name,public-profile-url,picture-url)'
            );
           
            
        } catch (\LinkedIn\Exception $exception) {
            // in case of failure, provide with details
             \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'error'],null,null);
			\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'description',"value"=>$exception->getMessage()],null,null);
			return;
        }
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'success'],null,null);

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
if(IS_WP){
	\add_action( 'wp', 'aw2\linkedin\auth_check', 11 );
	
	function auth_check(){

		if(!isset($_REQUEST['social_auth'])) return;
		
		if($_REQUEST['social_auth'] !== 'linkedin') return;
		
		$ticket_id = $_COOKIE['linkedin_login'];
		
		$query_string=explode('&',$_SERVER["QUERY_STRING"]);

		array_shift($query_string);
		
		$query_string =  implode('&',$query_string);
		
		$app_path = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'app_path'],null,null);
		
		

		wp_redirect($app_path.'/t/'.$ticket_id.'?'.$query_string);

		die();
	}
}