<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//$GLOBALS['curTime'] = microtime(true);

//Are we using Word Press. This is required because of the way Wordpress works
define('IS_WP', false);
//define('ROOT_APP', 'home_pages');
define('AWESOME_DEBUG', false);

//Load the config
require_once '../wp-config.php';


// Include Util so that you can do var_dump
require_once AWESOME_PATH.'/includes/util.php';

//Main library of awesome
require_once AWESOME_PATH.'/includes/aw2_library.php';
require_once AWESOME_PATH.'/includes/error_log.php';

set_exception_handler(function($e) {
	
	$reply=aw2_error_log::awesome_exception('global_index_bootstrap',$e);
	
	header("HTTP/1.0 500"); 
	die($reply);
});
	
//Load the Flow Class
require_once AWESOME_PATH.'/includes/awesome_flow.php';

//Load the Class to setup the Active App
require_once AWESOME_PATH.'/includes/awesome_app.php';

//Load the Class to register Controllers
require_once AWESOME_PATH.'/includes/awesome-controllers.php';

//setup different authentication methods
require_once AWESOME_PATH.'/includes/awesome_auth.php';

//Optional - Depends on what libraries we require - We need to split this
//require_once AWESOME_PATH.'/vendor/autoload.php';

define('AWESOME_CORE_POST_TYPE', 'awesome_core');
define('AWESOME_APPS_POST_TYPE', 'aw2_app');

//What do the requests start with
define('REQUEST_START_POINT', '/bs');

define('AWESOME_APP_BASE_PATH', SITE_URL . REQUEST_START_POINT);

//Standard setup for developer
aw2_library::setup_develop_for_awesomeui();

//standard setup for cache
aw2_library::setup_env_cache('bootstrap_env_cache');

define('HANDLERS_PATH', '/var/www/awesome-enterprise/core-handlers');
define('EXTRA_HANDLERS_PATH', AWESOME_PATH.'/extra-handlers');

//load whatever handlers we want
aw2_library::load_handlers_from_path(HANDLERS_PATH,'structure','lang','cache','session');
aw2_library::load_handlers_from_path(EXTRA_HANDLERS_PATH,'debug');
aw2_library::load_handlers_from_path(HANDLERS_PATH,'utils');
aw2_library::load_handlers_from_path(HANDLERS_PATH,'database');
aw2_library::load_handlers_from_path(HANDLERS_PATH,'front-end');

//load controllers
aw2_library::load_handlers_from_path(HANDLERS_PATH,'controllers','connectors');

//Load the initial services	
awesome_flow::env_setup();

//setup basic awesome
awesome_flow::init();

//$timeConsumed = round(microtime(true) - $GLOBALS['curTime'],3)*1000; 
//echo '/*' .  '::includes done:' .$timeConsumed . '*/';


$parts = explode( '?', $_SERVER['REQUEST_URI'] );
$query = new stdClass();
$query->request=$parts[0];

//Let Awesome take over
awesome_flow::app_takeover($query);