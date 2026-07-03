<?php
 
class awesome_flow{
	
	static function env_setup(){

		$now = new DateTime();
		$val = $now->format("m-d-Y H:i:s.u");
		header('start:' . $val);

		
		if(function_exists('\aw2\live_debug\setup_cookie'))\aw2\live_debug\setup_cookie([]);	
		self::setup_constants();
		
		
		if(WP_DEBUG){
			error_reporting(E_ALL);
			$old_error_handler = set_error_handler("aw2_error_log::awesome_error_handler");
		}


		if(\aw2_library::is_live_debug()){
			
			$debug_format=array();
			$debug_format['bgcolor']='#C1CFC0';
			
			$live_debug_event=array();
			$live_debug_event['flow']='live_debug';
			$live_debug_event['action']='debug.started';
			$live_debug_event['live_debug']=\aw2_library::get('@live_debug');
			$live_debug_event['develop_for_awesomeui']=DEVELOP_FOR_AWESOMEUI;
			$live_debug_event['set_env_cache']=SET_ENV_CACHE;
			$live_debug_event['use_env_cache']=USE_ENV_CACHE;
			$live_debug_event['log_exceptions']=LOG_EXCEPTIONS;
			$live_debug_event['env_cache']=ENV_CACHE;
			$live_debug_event['error_level']=error_reporting();
			$live_debug_event['e_all']=E_ALL;
			$live_debug_event['php_version']=phpversion();
			
			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}


		
		//if($old_error_handler)restore_error_handler();
		try {

		//get all the locations for code`
		$ref=&aw2_library::get_array_ref();
		$ref['code_connections']=array();
		
		if(defined('CONNECTIONS')){
			//put the locations in the env
			$ref['code_connections']=CONNECTIONS;
			
			if(defined('CODE_DEFAULT_CONNECTION')){
				//put the locations in the env
				$ref=&aw2_library::get_array_ref();
				$ref['code_connections']['#default']=$ref['code_connections'][CODE_DEFAULT_CONNECTION];
			}
		}	
		if(!isset($ref['code_connections']['#default']))
			$ref['code_connections']['#default']=array(
				'connection_service'=>'wp_conn',
				'db_host'=>DB_HOST,
				'db_user'=>DB_USER,
				'db_password'=>DB_PASSWORD,
				'db_name'=>DB_NAME,
				'redis_db'=>REDIS_DATABASE_GLOBAL_CACHE
			);

		if(\aw2_library::is_live_debug()){
			
			$debug_format=array();
			$debug_format['bgcolor']='#C1CFC0';
			
			$live_debug_event=array();
			$live_debug_event['flow']='awesome_setup';
			$live_debug_event['stream']='env_setup';
			$live_debug_event['action']='setup.env';
			$live_debug_event['reason']='reached till code connections';
			$live_debug_event['live_debug_active']=\aw2_library::get('@live_debug.active');
			$live_debug_event['code_connections']=$ref['code_connections'];
			$live_debug_event['error_level']=error_reporting();
			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}	
	
		if(\aw2_library::get('@live_debug.config.del_env_cache')==='yes'){
			aw2\global_cache\del(['main'=>ENV_CACHE],null,null);

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='setup.cache.deleted';
				$live_debug_event['cache_deleted']='yes';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}	
			
		}
		//clear_redis_cache= global,redis_db,connections as required

		header('use_env_cache:' . USE_ENV_CACHE);
		
		if(USE_ENV_CACHE && aw2\global_cache\exists(["main"=>ENV_CACHE])){
			header('awesome_cache: used');
			$now = DateTime::createFromFormat('U.u', microtime(true));
			$val=$now->format("m-d-Y H:i:s.u");
			header('cache_time:' . $val);

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='setup.cache.used';
				$live_debug_event['stream']='cache';
				$live_debug_event['cache_used']='yes';

				$debug_format['bgcolor']='#F3F1F5';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}	

			$ref=&aw2_library::get_array_ref();
			
			$handlers=aw2\global_cache\hget(["main"=>ENV_CACHE,"field"=>"handlers"]);

			$ref['handlers']=unserialize($handlers);
			
			$ref['apps']=unserialize(aw2\global_cache\hget(["main"=>ENV_CACHE,"field"=>"apps"]));
			
			$ref['awesome_core']=unserialize(aw2\global_cache\hget(["main"=>ENV_CACHE,"field"=>"awesome_core"]));
			$ref['settings']=unserialize(aw2\global_cache\hget(["main"=>ENV_CACHE,"field"=>"settings"]));
			$ref['css']=unserialize(aw2\global_cache\hget(["main"=>ENV_CACHE,"field"=>"css"]));
			//These are content type stubs and not actual content types
			$ref['content_types']=unserialize(aw2\global_cache\hget(["main"=>ENV_CACHE,"field"=>"content_types"]));
		}
		else{
			header('awesome_cache: not used');
			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='setup.cache.not_used';
				$live_debug_event['stream']='cache';
				$debug_format['bgcolor']='#F3F1F5';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}	
			
		// load core
			if(defined('AWESOME_CORE_POST_TYPE')){
				\aw2_library::add_service('core','core service refers to core posts for config etc.',['post_type'=>AWESOME_CORE_POST_TYPE]);
				
				if(\aw2_library::is_live_debug()){
					$live_debug_event['action']='setup.core';
					$live_debug_event['stream']='env_setup';
					
					$live_debug_event['awesome_core_post_type']=AWESOME_CORE_POST_TYPE;
					$debug_format['bgcolor']='#E7E0C9';
					\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
				}					
			}		

			//load all the apps
			self::load_apps();
			self::run_core('apps');

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='setup.apps';
				$live_debug_event['stream']='env_setup';
				$live_debug_event['apps']=\aw2_library::get('apps');
				
				$debug_format['bgcolor']='#E7E0C9';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}	

			self::run_core('services');

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='setup.services';
				$live_debug_event['stream']='env_setup';
				$debug_format['bgcolor']='#E7E0C9';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}	

			self::run_core('less-variables');
				
			//self::run_core('config');
			self::load_env_settings();
			
			
			
			$ref=&aw2_library::get_array_ref();
			if(!isset($ref['content_types']))$ref['content_types']=array();
			self::run_core('content-types');

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='setup.content-types';
				$debug_format['bgcolor']='#E7E0C9';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}	

			header('set_env_cache:' . SET_ENV_CACHE);			
			if(SET_ENV_CACHE){
				$ref=aw2_library::get_array_ref();
				$handlers=serialize($ref['handlers']);
				aw2\global_cache\hset(["main"=>ENV_CACHE,"field"=>"handlers","value"=>$handlers]);				
				aw2\global_cache\hset(
				["main"=>ENV_CACHE,"field"=>"apps","value"=>serialize($ref['apps'])]);				
				
				aw2\global_cache\hset(["main"=>ENV_CACHE,"field"=>"settings","value"=>serialize($ref['settings'])]);

				$css = isset($ref['css']) ? serialize($ref['css']) : '';
				aw2\global_cache\hset(["main"=>ENV_CACHE,"field"=>"css","value"=>$css]);

				$content_types=$ref['content_types'];
				$ct_arr=array();
				if($content_types){
					foreach($content_types as $field=>$def){
						aw2\global_cache\hset(["main"=>ENV_CACHE,"field"=>"#ct_" . $field,"value"=>serialize($def)]);					
						$ct_arr[$field]='#cached';
					}
					aw2\global_cache\hset(["main"=>ENV_CACHE,"field"=>"content_types","value"=>serialize($ct_arr)]);					
				}

				if(\aw2_library::is_live_debug()){
					$live_debug_event['action']='setup.cache.set';
					$live_debug_event['stream']='cache';
					$live_debug_event['cache_set']='yes';
					$debug_format['bgcolor']='#F3F1F5';
					\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
				}	
				
			}	
		}	
		//time_zone
		$time_zone = aw2_library::get('settings.time_zone');
		if(empty($time_zone) && defined('TIMEZONE'))$time_zone=TIMEZONE;
		if(!empty($time_zone))date_default_timezone_set($time_zone);
		
		//$timeConsumed = round(microtime(true) - $GLOBALS['curTime'],3)*1000; 
		//echo '/*' .  '::end initialize:' .$timeConsumed . '*/';
		self::setup_libraries();


		$now = new DateTime();
		$val = $now->format("m-d-Y H:i:s.u");
		header('env_setup:' . $val);	
	}
		catch(Throwable $e){
			$reply=aw2_error_log::awesome_exception('env_setup',$e);
		}	
	}
	

	static function setup_libraries(){
		// Get code connections from AW2 library stack
		$connection_arr = \aw2_library::$stack['code_connections'];

		// Loop through the connections array
		foreach ($connection_arr as $connection_key => $connection_value) {
			// Check if the current connection has a library_services child item
			if (isset($connection_value['library_services'])) {
				// Get the service value
				$service = $connection_value['library_services'];
				
				// Set up defaults array
				$defaults = array();
				
				// Set the connection key in defaults
				$defaults['connection'] = $connection_key;
				
				// Add the service to the library
				\aw2_library::add_service(
					$service,              // Service name
					'Unhandled repository services',  // Description
					[
						'func' => '_library',
						'namespace' => 'aw2\library',
						'#defaults' => $defaults
					]
				);
			}
		}
	}

	static function setup_constants(){
		//develop_for_awesomeui
		if(!defined('DEVELOP_FOR_AWESOMEUI')){
			$val=\aw2_library::get('@live_debug.config.develop_for_awesomeui');
			if($val==='yes')
				define('DEVELOP_FOR_AWESOMEUI', true);
			else	
				define('DEVELOP_FOR_AWESOMEUI', false);
		}
		if(!defined('USE_ENV_CACHE')){
			$val=\aw2_library::get('@live_debug.config.use_env_cache');
			if($val==='no')
				define('USE_ENV_CACHE', false);
			else	
				define('USE_ENV_CACHE', true);
		}

		if(!defined('SET_ENV_CACHE')){
			
			if(DEVELOP_FOR_AWESOMEUI)
				define('SET_ENV_CACHE', false);
			else{
				$val=\aw2_library::get('@live_debug.config.set_env_cache');
				if($val==='no')
					define('SET_ENV_CACHE', false);
				else	
					define('SET_ENV_CACHE', true);
			}
		}

		if(!defined('LOG_EXCEPTIONS')){
			define('LOG_EXCEPTIONS', true);
		}


		if(DEVELOP_FOR_AWESOMEUI){
			error_reporting(E_ALL);
		}

		if(LOG_EXCEPTIONS){
			$old_error_handler = set_error_handler("aw2_error_log::awesome_error_handler");		
		}

		
	}
	
		
	static function run_core($module){
		if(!defined('AWESOME_CORE_POST_TYPE'))return;
		
		$arr=\aw2_library::get_module(['post_type'=>AWESOME_CORE_POST_TYPE],$module);
		if($arr)\aw2_library::module_run(['post_type'=>AWESOME_CORE_POST_TYPE],$module);
	}
		

	static function load_env_settings(){
		$settings=&aw2_library::get_array_ref('settings');
		if(!is_array($settings)) $settings=array();
		
		$exists=aw2_library::module_exists_in_collection(['post_type'=>AWESOME_CORE_POST_TYPE],'settings');
		if(!$exists) return;
			
		$all_post_meta = aw2_library::get_module_meta(['post_type'=>AWESOME_CORE_POST_TYPE],'settings');
		
		foreach($all_post_meta as $key=>$meta){
			
			//ignore private keys
			if(strpos($key, '_') === 0 )
				continue;
			
			$settings[$key] = $meta;
		}

	}

	static function init(){
		try{	
		self::run_core('init');

		//Decide caching or not caching
		$cache=array();
		$cache['enable']='no';
		if(isset($_SERVER['REQUEST_METHOD'])&& $_SERVER['REQUEST_METHOD']==='GET'){
			if(!isset($_SERVER['QUERY_STRING']) || empty($_SERVER['QUERY_STRING'])){
				
					if(!(array_key_exists('wordpress_logged_in',$_COOKIE) || array_key_exists('aw2_vsession',$_COOKIE) || array_key_exists('wordpress_no_cache',$_COOKIE))){
						if(!IS_WP){
							$cache['failed']='Not WP';
						}
						else{
							if(!is_user_logged_in()){
								$cache['enable']='yes';
							}
							else{
								$cache['failed']='Logged in User';
							}
						}
					}
					else{
						$cache['failed']='Restricted Cookies are there';
					}
				
			}
			else{
				$cache['failed']='Query String is there';
			}
		}
		else{
			$cache['failed']='Not GET Method';
			
		}
		$env=&aw2_library::get_array_ref();
		$env['cache']=$cache;
	}
		catch(Throwable $e){
			$reply=aw2_error_log::awesome_exception('init',$e);
		}
	}
	
	static function load_apps(){
		$registered_apps=&aw2_library::get_array_ref('apps');
		if(!defined('AWESOME_APPS_POST_TYPE')){
			$registered_apps=array();
			return;
		}

		$app_posts= aw2_library::get_collection(["post_type"=>AWESOME_APPS_POST_TYPE]);
		foreach($app_posts as $app_post){
			$app = array();

			//path has to be handled correctly
			$app['base_path']=AWESOME_APP_BASE_PATH .'/'.$app_post['module'];
			$app['path']=AWESOME_APP_BASE_PATH .'/'.$app_post['module'];
			$app['name']=$app_post['title'];
			$app['slug']=$app_post['module'];
			$app['post_id']=$app_post['id'];
			$app['hash']=$app_post['hash'];
			
			$app['collection']=array();

			$app_meta=aw2_library::get_module_meta(["post_type"=>AWESOME_APPS_POST_TYPE],$app['slug']);
			$app_config=isset($app_meta['config_collection']) ? $app_meta['config_collection'] :'' ;
			if($app_config){
				$app['collection']['config']['post_type']=$app_config;
			}
			
			$modules=isset($app_meta['modules_collection']) ? $app_meta['modules_collection'] :'' ;
			if($modules){
				$app['collection']['modules']['post_type']=$modules;
			}
			
			$pages=isset($app_meta['pages_collection']) ? $app_meta['pages_collection'] :'' ;
			if($pages){
				$app['collection']['pages']['post_type']=$pages;
			}	
			
			$posts=isset($app_meta['posts_collection']) ? $app_meta['posts_collection'] :'' ;
			if($posts){
				$app['collection']['posts']['post_type']=$posts;
			}
			
			$registered_apps[$app_post['module']]=$app;

		}
		
		
	}
		
	static function app_takeover($query){
		try {

		$now = new DateTime();
		$val = $now->format("m-d-Y H:i:s.u");
		header('app_takeover:' . $val);
		
		$request=$query->request;

		//remove REQUEST_START_POINT
		if(defined('REQUEST_START_POINT'))
			$request=substr($request, strlen(REQUEST_START_POINT));	


		if(\aw2_library::startsWith($request,'/'))
			$request=substr($request, 1);	
			
		if(\aw2_library::endswith($request,'/'))
			$request=substr($request, 0,-1);

		if(empty($request) && defined('DEFAULT_APP')){
			$request = DEFAULT_APP;
		}
		else if(empty($request)){
			self::initialize_root($query); // it is front page hence request is not set so setup root.
			return;
		}

		$pieces = explode('/',urldecode($request));
		
		// do we own the app?
		$app_slug= $pieces[0];



		if($app_slug == 'wp-admin') return;


		if(\aw2_library::is_live_debug()){
			$debug_format=array();
			$debug_format['bgcolor']='#E7E0C9';
			
			$live_debug_event=array();
			$live_debug_event['flow']='controller';
			$live_debug_event['stream']='controller_setup';
			$live_debug_event['action']='controller.called';
			$live_debug_event['slug']=$app_slug;
			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
		}	

		$app = new awesome_app();

		//is it a ticket
		if($app_slug==='t'){
			$ticket=$pieces[1];
			$app_slug=$app->get_app_ticket($ticket);
			array_unshift($pieces,$app_slug);

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='controller.found';
				$live_debug_event['controller_type']='ticket';
				$debug_format['bgcolor']='#E7E0C9';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}
		
		}
		if($app_slug==='ts'){
			$ticket=$pieces[1];
			$app_slug=$app->get_app_ts($ticket);
			array_unshift($pieces,$app_slug);

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='controller.found';
				$live_debug_event['controller_type']='ticket';
				$debug_format['bgcolor']='#E7E0C9';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}
			
		}
		
		$cs=aw2_library::get_array_ref('handlers','controllers');

		if(isset($cs[$app_slug])){
			$o=new stdClass();
			$o->pieces=$pieces;
			$name=array_shift($o->pieces);

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='controller.found';
				$live_debug_event['controller_type']='controller';
				$live_debug_event['name']=$name;
				$debug_format['bgcolor']='#E7E0C9';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}
			
			\aw2_library::service_run('controllers.' . $name,['o'=>$o],null,'service'); // run the controller service, it is responsible for handling echo and exit.
		}

		if(!$app->exists($app_slug)  && defined('DEFAULT_APP')){
			$app_slug = DEFAULT_APP;
			//prepend to array $pieces the ROOT_APP
			array_unshift($pieces,$app_slug);
		}
		
		if($app->exists($app_slug)){
			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='controller.found';
				$live_debug_event['controller_type']='app';
				$live_debug_event['name']=$app_slug;
				$debug_format['bgcolor']='#E7E0C9';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}

			if(\aw2_library::is_live_debug()){
				$debug_format=array();
				$debug_format['bgcolor']='#E7E0C9';
				
				$app_debug_event=array();
				$app_debug_event['flow']='app';
				$app_debug_event['stream']='app_setup';
				$app_debug_event['action']='app.found';
				$app_debug_event['slug']=$app_slug;
				\aw2\live_debug\publish_event(['event'=>$app_debug_event,'format'=>$debug_format]);
			}	

			//yes - setup app
			$app->setup($app_slug);
			array_shift($pieces); 

			if(\aw2_library::is_live_debug()){
				$debug_format=array();
				$debug_format['bgcolor']='#E7E0C9';
				
				$app_debug_event['action']='app.loaded';
				$app_debug_event['app']=\aw2_library::get('app');
				\aw2\live_debug\publish_event(['event'=>$app_debug_event,'format'=>$debug_format]);
			}	

			
		}
		else if($app->exists('root')){
			//No - Root Exists?  - setup root app
			$app->setup('root');
		}
		else{
			//No - possible issue
			if(\aw2_library::is_live_debug()){
				$debug_format=array();
				$debug_format['bgcolor']='#E7E0C9';
				
				$live_debug_event['action']='controller.not_found';
				$live_debug_event['controller_type']='wp';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'format'=>$debug_format]);
			}
			return;
		}

		$app->load_settings();
		$app->setup_collections();

		if(\aw2_library::is_live_debug()){
			$app_debug_event['action']='app.setup';
			$app_debug_event['app']=\aw2_library::get('app');
			\aw2\live_debug\publish_event(['event'=>$app_debug_event,'format'=>$debug_format]);
		}	

		$arr=array();
		$arr['status']='';
		$arr=$app->check_rights($request);

		if(\aw2_library::is_live_debug()){
			$debug_format=array();
			$debug_format['bgcolor']='#E7E0C9';
				
			$app_debug_event['action']='app.valid';
			$app_debug_event['app']=\aw2_library::get('app');
			\aw2\live_debug\publish_event(['event'=>$app_debug_event,'format'=>$debug_format]);
		}	

		// run init
		$app->run_init();

		//now resolve the route.
		
		if($app->slug!='root'){
			if(\aw2_library::is_live_debug()){
				$debug_format=array();
				$debug_format['bgcolor']='#E7E0C9';
				
				$app_debug_event['action']='app.routing';
				$app_debug_event['reason']='App is being Routed. Will not come back';
				$app_debug_event['app']=\aw2_library::get('app');
				\aw2\live_debug\publish_event(['event'=>$app_debug_event,'format'=>$debug_format]);
			}	
			$app->resolve_route($pieces,$query);
		}

		if(\aw2_library::is_live_debug()){
			$debug_format=array();
			$debug_format['bgcolor']='#C1CFC0';
				
			$live_debug_event['action']='app.wp';
			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#C1CFC0']);
		}
	} 
		catch(Throwable $e){
			$reply=aw2_error_log::awesome_exception('app_takeover',$e);
				die($reply);
		}	
	} 
	
	static function initialize_root(){

		$app = new awesome_app();
		if($app->exists('root')){
			$app->setup('root');
			$app->load_settings();
			$app->setup_collections();
			$app->run_init();
		}	
		
	}
	
    static function head(){
		
		if(defined('AWESOME_CORE_POST_TYPE')){
			$arr=\aw2_library::get_module(['post_type'=>AWESOME_CORE_POST_TYPE],'scripts');
			if($arr)echo \aw2_library::module_run(['post_type'=>AWESOME_CORE_POST_TYPE],'scripts');
		}
		
		$app = &aw2_library::get_array_ref('app');
		if(isset($app['collection']['config'])){
			$arr=\aw2_library::get_module($app['collection']['config'],'scripts');
			if($arr)echo \aw2_library::module_run($app['collection']['config'],'scripts');
		}		
		
	}
	
	static function footer(){
		self::run_core('footer-scripts');
	}
}
