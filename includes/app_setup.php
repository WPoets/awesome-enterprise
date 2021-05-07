<?php
add_action('plugins_loaded','aw2_apps_library::initialize',1);
add_action('init','aw2_apps_library::wp_init',11);
add_action('admin_init','aw2_apps_library::admin_init',1);
add_action( 'parse_request', 'aw2_apps_library::app_takeover' );

//add_action( 'admin_menu', 'aw2_apps_library::register_menus' );
//add_action('template_redirect', 'aw2_apps_library::template_redirect');

add_action('generate_rewrite_rules', 'aw2_apps_library::app_slug_rewrite');
add_filter( 'post_type_link', 'aw2_apps_library::fix_app_slug', 10, 3 );
add_filter( 'nav_menu_css_class', 'aw2_apps_library::nav_menu_css_class', 10, 3 );

add_action('wp_head', 'aw2_apps_library::wp_head');
add_action('wp_footer', 'aw2_apps_library::wp_footer');

add_filter( 'wpseo_sitemap_index', 'aw2_apps_library::add_apps_to_yoast_sitemap' );

//To remove all the app pages and app post from the sitemap_index
add_filter( 'wpseo_sitemap_exclude_post_type', 'aw2_apps_library::sitemap_exclude_post_type', 10, 2 );

require_once('app-rights.php');
require_once('awesome-menus.php');

class aw2_apps_library{
	
	static function initialize(){
		
		\aw2\debug\setup([]);	
		
		if(current_user_can('develop_for_awesomeui') || !empty(\aw2_library::get('debug_config.output'))){
			setcookie("wordpress_no_cache", 'yes', time()+3600);  /* expire in 1 hour */
		}
		
		$flag=true;
		
		if(current_user_can('develop_for_awesomeui') && isset($_REQUEST['del_env'])){
				aw2\global_cache\del(['main'=>'#cached_enviroment'],null,null);
		}	
		
		if((current_user_can('develop_for_awesomeui') || isset($_COOKIE['dev_no_cache']))){
			//echo '/*' .  'no cache*/';
			header("Awesome-Cache: NO");			
		}
		
		if(!(current_user_can('develop_for_awesomeui') || isset($_COOKIE['dev_no_cache']))){
			$cached=aw2\global_cache\get(["main"=>'#cached_enviroment'],null,null);
			if($cached){
				$cached=unserialize($cached);
				$ref=&aw2_library::get_array_ref();
				$ref['handlers']=$cached['handlers'];
				$ref['apps']=$cached['apps'];
				$ref['apphelp']=$cached['apphelp'];
				$ref['awesome_core']=$cached['awesome_core'];
				//$ref['content_types']=$cached['content_types'];
				$ref['settings']=$cached['settings'];
				//echo '/*' .  'from cache*/';
				header("Awesome-Cache: YES");
				$flag=false;
			}
		}
		
		if($flag){
				self::load_apps();
				\aw2\debug\flow(['main'=>'Apps Loaded']);


				aw2_library::add_service('core','core service refers to core posts for config etc.',['post_type'=>'awesome_core']);
				self::run_core('services');
				\aw2\debug\flow(['main'=>'Services Setup']);

				self::run_core('config');
				self::load_env_settings();
				\aw2\debug\flow(['main'=>'Env Setup']);
				
				//self::run_core('content-types');
				// \aw2\debug\flow(['main'=>'Content Types']);
	
				if(!current_user_can('develop_for_awesomeui')  && !isset($_COOKIE['dev_no_cache']) ){
					$ref=aw2_library::get_array_ref();
					$cached=array();
					$cached['handlers']=$ref['handlers'];
					$cached['apps']=$ref['apps'];
					$cached['apphelp']=$ref['apphelp'];
					$cached['awesome_core']=$ref['awesome_core'];
					//$cached['content_types']=isset($ref['content_types'])? $ref['content_types'] : array();
					$cached['settings']=$ref['settings'];
					aw2\global_cache\set(["key"=>'#cached_enviroment',"prefix"=>""],serialize($cached),null);
					//echo 'caching';
				}	
	
		} 
		//time/zone
		$time_zone = aw2_library::get('settings.time_zone');
		if(!empty($time_zone))date_default_timezone_set($time_zone);
		
		
	}
	
	static function run_core($slug){
		
		$awesome_core=&aw2_library::get_array_ref('awesome_core');
		
		if(!isset($awesome_core[$slug])) return;
		
		aw2_library::parse_shortcode($awesome_core[$slug]['code']);
		//consume
		unset($awesome_core[$slug]);
		
	}


	static function load_env_settings(){
		$settings=&aw2_library::get_array_ref('settings');
		
		$awesome_core=&aw2_library::get_array_ref('awesome_core');
		
		if(!isset($awesome_core['settings']))return;
			
		$post_id = $awesome_core['settings']['id'];
		$all_post_meta = aw2_library::get_post_meta($post_id);
		
		foreach($all_post_meta as $key=>$meta){
			
			//ignore private keys
			if(strpos($key, '_') === 0 )
				continue;
			
			$settings[$key] = $meta;
		}
	
	}

	static function wp_init(){
		\aw2\debug\flow(['main'=>'WP Init Started']);	
	
		self::register_default_cpts();

		\aw2\debug\flow(['main'=>'Default CPTs Registered']);	
		
		self::register_app_cpts();

		\aw2\debug\flow(['main'=>'App CPTs Registered']);	
		
		
		self::register_service_cpts();

		\aw2\debug\flow(['main'=>'Service CPTs Registered']);	
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach($registered_apps as $key=>$app){
			if(!self::enable_sitemap($app)) continue;
			
			self::setup_yoast_links($app['slug']);	
		}
		
		self::run_core('register');

		\aw2\debug\flow(['main'=>'Custom CPTs Registered']);			
		
		if(is_admin())return;
		
		$flag=true;

		
		if(current_user_can('develop_for_awesomeui') && isset($_REQUEST['del_env'])){
				aw2\global_cache\del(['main'=>'#cached_content_types'],null,null);
		}	
		
		if(!(current_user_can('develop_for_awesomeui') || isset($_COOKIE['dev_no_cache']))){

			
			$cached=aw2\global_cache\get(["main"=>'#cached_content_types'],null,null);

			//$timeConsumed = round(microtime(true) - $GLOBALS['curTime'],3)*1000; 
			//echo '<h4>' .  '::after cache get:' .$timeConsumed . '</h4>';
			
			if($cached){
				$cached=unserialize($cached);
				//$timeConsumed = round(microtime(true) - $GLOBALS['curTime'],3)*1000; 
				//echo '<h4>' .  '::after unserialize:' .$timeConsumed . '</h4>';
				$ref=&aw2_library::get_array_ref();
				$ref['content_types']=$cached['content_types'];
				$ref['handlers']=$cached['handlers'];				

				//echo 'from cache content type'	;
				$flag=false;
			}
		}
		self::run_core('init');	 // this was done as init may set variables in the env that are not cached.
		if($flag){
			//self::run_core('init');			
			if(!current_user_can('develop_for_awesomeui')){
				$ref=aw2_library::get_array_ref();
				$cached=array();
				$cached['handlers']=$ref['handlers'];
				$cached['content_types']=isset($ref['content_types'])? $ref['content_types'] : array();
				aw2\global_cache\set(["key"=>'#cached_content_types',"prefix"=>""],serialize($cached),null);

			}	
	
		} 
		

		
		if(current_user_can('develop_for_awesomeui') && isset($_COOKIE['debug_init_module']) && !empty($_COOKIE['debug_init_module'])){
			$user_init_module = $_COOKIE['debug_init_module'];
			self::run_core($user_init_module);
		} 	
		\aw2\debug\flow(['main'=>'Init fired']);			

		//Decide caching or not caching
		$cache=array();
		$cache['enable']='no';
		if($_SERVER['REQUEST_METHOD']==='GET'){
			if(!isset($_SERVER['QUERY_STRING']) || empty($_SERVER['QUERY_STRING'])){
				if(!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER'])){
					if(!(array_key_exists('wordpress_logged_in',$_COOKIE) || array_key_exists('aw2_vsession',$_COOKIE) || array_key_exists('wordpress_no_cache',$_COOKIE))){
						if(!is_user_logged_in()){
							$cache['enable']='yes';
						}
						else{
							$cache['failed']='Logged in User';
						}
						
					}
					else{
						$cache['failed']='Restricted Cookies are there';
					}
						
					
				}
				else{
					$cache['failed']='Referrer is there';
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
	
	/***
	 * This function will remove all the sitemap.xml files,
	 * of posts and pages of awesome app
	 */
	static function sitemap_exclude_post_type( $value, $post_type ) {
		$registered_apps=&aw2_library::get_array_ref('apps');
		$remove_cpt_from_sitemap=array();
		foreach($registered_apps as $key=>$app){
			array_push($remove_cpt_from_sitemap,$app['collection']['pages']['post_type'],$app['collection']['posts']['post_type']);
		}
		if( in_array( $post_type, $remove_cpt_from_sitemap ) ) return true;
	}

	static function register_service_cpts(){
		
		$handlers=&aw2_library::get_array_ref('handlers');
		
		foreach($handlers as $key => $handler){
			if(!isset($handler['post_type']))
				continue;
			
			if(isset($handler['@service']) && $handler['@service'] === true){
				//$service_post_type[] =  $handler['post_type'];
				if(!post_type_exists( $handler['post_type'] ))
					//self::register_cpt($handler['post_type'],$handler['service_label'],'',false);
					\aw2_library::register_module($handler['post_type'],$handler['service_label'],$handler['service_label'],'service');
			}	
		}
		
	}
	
	static function register_default_cpts(){
			
		register_post_type('awesome_core', array(
			'label' => 'Core',
			'description' => '',
			'public' => false,
			'show_in_nav_menus'=>false,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => true,
			'menu_icon'   => 'dashicons-align-right',
			'menu_position'   => 26,
			'rewrite' => false,
			'delete_with_user' => false,
			'query_var' => true,
			'supports' => array('title','editor','excerpt','revisions','custom-fields'),
			'labels' => array (
			  'name' => 'Core',
			  'singular_name' => 'Core',
			  'menu_name' => 'Core',
			  'add_new' => 'Add Core',
			  'add_new_item' => 'Add New Core',
			  'edit' => 'Edit',
			  'edit_item' => 'Edit Core',
			  'new_item' => 'New Core',
			  'view' => 'View Core',
			  'view_item' => 'View Core',
			  'search_items' => 'Search Core',
			  'not_found' => 'No Core Found',
			  'not_found_in_trash' => 'No Core Found in Trash',
			  'parent' => 'Parent Core',
			)
		)); 
		
		register_post_type('aw2_app', array(
			'label' => 'Local Apps',
			'public' => false,
			'show_in_nav_menus'=>true,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'query_var' => false,
			'menu_icon'=>'dashicons-archive',
			'supports' => array('title','editor','revisions','thumbnail','custom-fields'),
			'rewrite' => false,
			'delete_with_user' => false,
			'labels' => array (
				  'name' => 'Local Apps',
				  'singular_name' => 'Local App',
				  'menu_name' => 'Local Apps',
				  'add_new' => 'Create New App',
				  'add_new_item' => 'Add New Local App',
				  'new_item' => 'New Local App',
				  'edit' => 'Edit Local App',
				  'edit_item' => 'Edit Local App',
				  'view' => 'View Local App',
				  'view_item' => 'View Local App',
				  'search_items' => 'Search Local Apps',
				  'not_found' => 'No Local App Found',
				  'not_found_in_trash' => 'No Local App Found in Trash'
				)
			) 
		);
		
	}
	static function register_app_cpts(){
	
				
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		foreach($registered_apps as $key => $app){
			foreach($app['collection'] as $collection_name => $collection){
				$supports='';
				$hierarchical=false;
				$public=false;
				$slug=null;
				if($collection_name == 'config'){
					$supports = array('title','editor','revisions','custom-fields');
					
				if(!post_type_exists( $collection['post_type'] ))
					\aw2_library::register_module($collection['post_type'],ucwords($app['name'] . ' ' . rtrim($collection_name,'s')) , ucwords($app['name'] . ' ' . $collection_name),'config',$supports );
					
				}
				
				if($collection_name == 'pages'){
					$hierarchical=true;
					$public=true;
					$slug=$key;
					$supports='';
					
					if(!post_type_exists( $collection['post_type'] ))
						self::register_cpt($collection['post_type'],$collection_name,$app['name'],$public,$supports,$hierarchical,$slug);
				}	
				
				if($collection_name == 'modules'){
					if(!post_type_exists( $collection['post_type'] ))
						\aw2_library::register_module($collection['post_type'],ucwords($app['name'] . ' ' . rtrim($collection_name,'s')) , ucwords($app['name'] . ' ' . $collection_name),'modules' );

				}
				

	
				if(isset($collection['post_type']) && !post_type_exists( $collection['post_type'] ))
					self::register_cpt($collection['post_type'],$collection_name,$app['name'],$public,$supports,$hierarchical,$slug);
			}
			
		}
	}
	
	static function admin_init(){
		
		self::run_core('backend-init');
		
		self::purge_cache();
		
	}
	
	
	static function load_apps(){
		
	$registered_apps=&aw2_library::get_array_ref('apps');
	$apphelp=&aw2_library::get_array_ref('apphelp');

	
	$app_key='registered_apps';
	$return_value=null;
	
	if(!current_user_can('develop_for_awesomeui')){
			$return_value=\aw2\global_cache\get(["main"=>$app_key,"prefix"=>""],null,null);
	}
	
	if(!$return_value){
		$app_posts= aw2_library::get_collection(["post_type"=>"aw2_app"]);
		foreach($app_posts as $app_post){
			$app = array();

			$app['base_path']=site_url().'/'.$app_post['module'];
			$app['path']=site_url().'/'.$app_post['module'];
			$app['name']=$app_post['title'];
			$app['slug']=$app_post['module'];
			$app['post_id']=$app_post['id'];
			$app['hash']=$app_post['hash'];
			
			$app['collection']=array();
			
			$app_config=aw2_library::get_post_meta($app_post['id'],'config_collection');
			if($app_config){
				$app['collection']['config']['post_type']=$app_config;
			}
			
			$modules=aw2_library::get_post_meta($app_post['id'],'modules_collection');
			if($modules){
				$app['collection']['modules']['post_type']=$modules;
			}
			
			$pages=aw2_library::get_post_meta($app_post['id'],'pages_collection');
			if($pages){
				$app['collection']['pages']['post_type']=$pages;
			}	
			
			$posts=aw2_library::get_post_meta($app_post['id'],'posts_collection');
			if($posts){
				$app['collection']['posts']['post_type']=$posts;
			}
			
			$app_help=aw2_library::get_post_meta($app_post['id'],'apphelp_collection');
			if($app_help){
				$app['collection']['apphelp']['post_type']=$app_help;
				$apphelp[]=$app_help;
			}
			$registered_apps[$app_post['module']]=$app;
		}
		if(current_user_can('develop_for_awesomeui')){
			\aw2\global_cache\set(["key"=>$app_key,"prefix"=>""],json_encode($registered_apps),null);
		}
	}
	else{
		$decoded=json_decode($return_value,true);
		$registered_apps=$decoded;
	}	
		
		//load all config post, as they are used they will be consumed.
		$awesome_core=&aw2_library::get_array_ref('awesome_core');
		$awesome_core=aw2_library::get_collection(["post_type"=>"awesome_core"]);
		
	}
		
	static function app_takeover($query){

		\aw2\debug\flow(['main'=>'App Takeover']);
	
		if(empty($query->request)){
			self::initialize_root(); // it is front page hence request is not set so setup root.
			return;
		}
		
		$pieces = explode('/',urldecode($query->request));
		
		// do we own the app?
		$app_slug= $pieces[0];
		if($app_slug == 'wp-admin') return;
		
		$app = new awesome_app();

		//is it a ticket
		if($app_slug==='t'){
			$ticket=$pieces[1];
			$app_slug=$app->get_app_ticket($ticket);
			array_unshift($pieces,$app_slug);
		}
		if($app_slug==='ts'){
			$ticket=$pieces[1];
			$app_slug=$app->get_app_ts($ticket);
			array_unshift($pieces,$app_slug);
		}
		
		
		if($app->exists($app_slug)){
			//yes - setup app
			$app->setup($app_slug);
			array_shift($pieces); 
		}
		else if($app->exists('root')){
			//No - Root Exists?  - setup root app
			$app->setup('root');
		}
		else{
			//No - possible issue
			return;
		}

		$app->load_settings();
		$app->setup_collections();
		\aw2\debug\flow(['main'=>'App Setup Done']);				
		$arr=array();
		$arr['status']='';
		$arr=$app->check_rights($query);

		// run init
		$app->run_init();

		\aw2\debug\flow(['main'=>'App Init done']);		
		//now resolve the route.
		if($app->slug!='root' || $app_slug=='ajax'){
			$app->resolve_route($pieces,$query);
		}
		\aw2\debug\flow(['main'=>'Wordpress Theme taking Over']);	
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
	
	static function manage_cache(){
		
		$nginx_purge_url = add_query_arg( array( 'nginx_helper_action' => 'purge', 'nginx_helper_urls' => 'all' ) ); 
		
		$nginx_nonced_url = wp_nonce_url( $nginx_purge_url, 'nginx_helper-purge_all' );
		$global_nonced_url = wp_nonce_url( admin_url('admin.php?page=awesome-studio-cache&awesome_purge=global'), 'global_nonced-purge_all' );
		$session_nonced_url = wp_nonce_url(admin_url('admin.php?page=awesome-studio-cache&awesome_purge=session'), 'session_nonced-purge_all' );
		
		echo '<div class="wrap ">'; 
		echo '<h2>Manage Awesome Cache</h2><hr>';
		echo "<a href='".$global_nonced_url."' class='page-title-action'>Purge Global Cache (Modules & Taxonomy etc)</a> <br /><br />"; //11       	
		echo "<a href='".$nginx_nonced_url."' class='page-title-action'>Purge NGINX Cache</a> <br /><br />";
		echo "<a href='".$session_nonced_url."' class='page-title-action'>Purge Session Cache (Search. OTP & self expiry)</a> <br /><br />";//12
		echo '</div>';	
	}
	
	static function purge_cache(){
		if ( !isset( $_REQUEST['awesome_purge'] ) )
				return;

			if ( !current_user_can( 'manage_options' ) )
				wp_die( 'Sorry, you do not have the necessary privileges to edit these options.' );

			$action = $_REQUEST['awesome_purge'];

			if ( $action == 'done' ) {
				//add_action( 'admin_notices', array( &$this, 'show_notice' ) );
				//add_action( 'network_admin_notices', array( &$this, 'show_notice' ) );
				return;
			}
			
			switch ( $action ) {
				case 'global':
					check_admin_referer( 'global_nonced-purge_all' );
					\aw2\global_cache\flush(null,null,null);
					break;
				case 'session':
					check_admin_referer( 'session_nonced-purge_all' );
					\aw2\session_cache\flush(null,null,'');
					break;
			}
			
			wp_redirect( esc_url_raw( add_query_arg( array( 'awesome_purge' =>'done' ) ) ) );
	}
	
	static function show_app_pages($app){
		
		if('root' != $app['slug']){
			rights_options_page($app);
		}else{
			echo '<div class="wrap ">';        	
			echo 'Not Yet Implemented';
			echo '</div>';
		}
	}
	
	
	static function app_slug_rewrite($wp_rewrite) {
    	
		$rules = array();
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach($registered_apps as $key => $app){
			if(!isset($app['collection']['pages']['post_type']))
				continue;
			
			$rules[$app['slug'] . '/?$'] = 'index.php?pagename=home&post_type='.$app['collection']['pages']['post_type'];
		}	
		
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	
	}
	
	static function fix_app_slug( $post_link, $post, $leavename ) {
 		//now apps show list show up in the menu to make it easy to add to nav menu
		if ( 'aw2_app' != $post->post_type || 'publish' != $post->post_status ) {
			return $post_link;
		}
		
		$post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
		return $post_link;
	}
	
	static function nav_menu_css_class( $classes , $item, $args){
		//ensures currect classes in menu if app is set
		$current_app_id=aw2_library::get('app.post_id');
		
		if($current_app_id == $item->object_id && $item->current_item_parent === false){
			$classes[] = 'current-menu-item';
		}
		
		return $classes;
	}
	
	//supporting functions
	static function do_not_include_template($template){
		return false;//do not include any thing
	}
	
	static function register_cpt($post_type,$name,$app_name='',$public,$supports=null,$hierarchical=false,$slug=null){
		
		if(empty($supports)|| !is_array($supports))
			$supports = array('title','editor','revisions','thumbnail');
		
		if($slug==null)$slug=$post_type;
		
		$name =ucwords($name);
		$app_name =ucwords($app_name);
		
		register_post_type($post_type, array(
			'label' => $name,
			'description' => '',
			'public' => $public,
			'show_in_nav_menus'=>false,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'page',
			'delete_with_user'    => false,
			'map_meta_cap' => true,
			'hierarchical' => $hierarchical,
			'query_var' => true,
			'rewrite' => array("slug"=>$slug,'with_front'=>false),
			'supports' => $supports,
			'labels' => array (
				  'name' => $app_name.' '.$name,
				  'singular_name' => $app_name.' '.rtrim($name,'s'),
				  'add_new_item' => 'Add New '.$app_name.' '.rtrim($name,'s'),
				  'edit_item' => 'Edit '.$app_name.' '.rtrim($name,'s'),
				  'new_item' => 'New '.$app_name.' '.rtrim($name,'s'),
				  'view_item' => 'View '.$app_name.' '.rtrim($name,'s'),
				  'search_items' => 'Search '.$app_name.' '.$name,
				  'not_found' => 'No '.$app_name.' '.$name.' Found',
				  'not_found_in_trash' => 'No '.$app_name.' '.$name.' Found in Trash',
				)
			) 
		);
	}

	static function wp_head(){
		
		$awesome_core=&aw2_library::get_array_ref('awesome_core');
		
		if(isset($awesome_core['scripts'])){
			echo aw2_library::parse_shortcode($awesome_core['scripts']['code']);
			unset($awesome_core['scripts']); // now we don't need this data
		}
		
		$app = &aw2_library::get_array_ref('app');
		
		if(isset($app['configs']['scripts'])){
			$scipts = $app['configs']['scripts'];
			echo aw2_library::parse_shortcode($scipts['code']);
		}
		
		//not sure about collections
		if(isset($app['collection']) && is_array($app['collection'])){
			foreach($app['collection'] as $name=>$collection){
				$collection_post = $collection['post_type'];
				
				if(isset($app['configs'][$collection_post.'-scripts'])){
					$scipts = $app['configs'][$collection_post.'-scripts'];
					echo aw2_library::parse_shortcode($scipts['code']);
				}
			}
		}		
		
		
		
	}	
	
	static function wp_footer(){
		self::run_core('footer-scripts');
	}
	
	static function  add_apps_to_yoast_sitemap(){
		global $wpseo_sitemaps;
		global $wpdb;
		
		$sql  = $wpdb->prepare(" SELECT MAX(p.post_modified_gmt) AS lastmod
						FROM	$wpdb->posts AS p
						WHERE post_status IN ('publish') AND post_type = %s ", 'aw2_app' );
		$mod = $wpdb->get_var( $sql )." +00:00";
				
		//$date = $wpseo_sitemaps->get_last_modified('aw2_app');
		if(!class_exists(WPSEO_Date_Helper)){
			$timezone =  new WPSEO_Sitemap_Timezone();
			$mod = $timezone->format_date($mod );
		}
		else{
			$date = new WPSEO_Date_Helper();
			$mod = $date->format($mod );
		}
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		
		$smp ='';
		foreach($registered_apps as $key=>$app){
			
			if(!self::enable_sitemap($app)) continue;
			
			$smp .= '<sitemap>' . "\n";
			$smp .= '<loc>' . site_url() .'/'.$app['slug'].'-app-sitemap.xml</loc>' . "\n";
			$smp .= '<lastmod>' . htmlspecialchars( $mod ) . '</lastmod>' . "\n";
			$smp .= '</sitemap>' . "\n";
		}
		
		/* $smp .= '<sitemap>' . "\n";
		$smp .= '<loc>' . site_url() .'/awesome-apps-sitemap.xml</loc>' . "\n";
		$smp .= '<lastmod>' . htmlspecialchars( $mod ) . '</lastmod>' . "\n";
		$smp .= '</sitemap>' . "\n"; */
		
		return $smp;
		
	}
	
	static function setup_yoast_links($slug){
		add_action( "wpseo_do_sitemap_".$slug."-app",  function() use ($slug){
														aw2_apps_library::awesome_apps_pages_sitemap($slug);
												});
	}
	
	static function enable_sitemap($app){
		
		if(!isset($app['collection']['config'])) return false;
			
		$arr=aw2_library::get_module($app['collection']['config'],'settings');
		if(!$arr) return false;
		aw2_library::module_run($app['collection']['config'],'settings');
		$enable_sitemap = aw2_library::get_post_meta($arr['id'],'enable_sitemap');
		
		if($enable_sitemap !== 'yes')  return false;
		
		return true;
	}
	static function awesome_apps_pages_sitemap($slug){
		global $wpseo_sitemaps;
		global $wpdb;
		
		$registered_apps=&aw2_library::get_array_ref('apps');

		$skip_slugs=array('single','archive','header','footer');
		
		$output = '';
		$app=$registered_apps[$slug];
		
		//foreach($registered_apps as $key => $app){
		if(!self::enable_sitemap($app)) {
            $wpseo_sitemaps->bad_sitemap = true;
			return;
		}	
			
		if(isset($app['collection']['pages']['post_type'])){
			$args = array(
				'posts_per_page'   => 500,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'post_type'        => $app['collection']['pages']['post_type'],
				'post_status'      => 'publish',
				'meta_query'  => array(
					'relation' => 'OR',
				   array(
				   'key'      => '_yoast_wpseo_meta-robots-noindex',
				   'compare' => 'NOT EXISTS'
				   )
				   ,array(
				   'key'      => '_yoast_wpseo_meta-robots-noindex',
				   'value'      => '2'
				   )
			   ),
				'suppress_filters' => true
			);
			
			$app_pages = new WP_Query( $args );
			
			
			if( $app_pages->have_posts() ){
				$chf = 'weekly';
				$pri = 1.0;
				foreach ( $app_pages->posts as $p ) {
					if(in_array($p->post_name,$skip_slugs)){
						continue;
					}
					$slug= $p->post_name.'/';
					if($slug=='home/')
						$slug='';
					
					$url = array();
					if ( isset( $p->post_modified_gmt ) && $p->post_modified_gmt != '0000-00-00 00:00:00' && $p->post_modified_gmt > $p->post_date_gmt ) {
						$url['mod'] = $p->post_modified_gmt;
					} else {
						if ( '0000-00-00 00:00:00' != $p->post_date_gmt ) {
							$url['mod'] = $p->post_date_gmt;
						} else {
							$url['mod'] = $p->post_date;
						}
					}
					$url['loc'] = $app['path'].'/'.$slug;
					$url['chf'] = $chf;
					$url['pri'] = $pri;
					$output .= $wpseo_sitemaps->renderer->sitemap_url( $url );
				}
			}
		}
			
		
		if(isset($app['collection']['posts']['post_type'])){
			$args = array(
				'posts_per_page'   => 500,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'post_type'        => $app['collection']['posts']['post_type'],
				'post_status'      => 'publish',
				'meta_query'  => array(
					'relation' => 'OR',
				   array(
				   'key'      => '_yoast_wpseo_meta-robots-noindex',
				   'compare' => 'NOT EXISTS'
				   )
				   ,array(
				   'key'      => '_yoast_wpseo_meta-robots-noindex',
				   'value'      => '2'
				   )
			   ),
				'suppress_filters' => true
			);
			
			$app_posts = new WP_Query( $args );
			
			
			if( $app_posts->have_posts() ){
				$chf = 'weekly';
				$pri = 1.0;
				foreach ( $app_posts->posts as $p ) {
							
					$url = array();
					if ( isset( $p->post_modified_gmt ) && $p->post_modified_gmt != '0000-00-00 00:00:00' && $p->post_modified_gmt > $p->post_date_gmt ) {
						$url['mod'] = $p->post_modified_gmt;
					} else {
						if ( '0000-00-00 00:00:00' != $p->post_date_gmt ) {
							$url['mod'] = $p->post_date_gmt;
						} else {
							$url['mod'] = $p->post_date;
						}
					}
					$url['loc'] = site_url().'/'.$app['slug'].'/'.$p->post_name.'/';
					$url['chf'] = $chf;
					$url['pri'] = $pri;
					$output .= $wpseo_sitemaps->renderer->sitemap_url( $url );
				}
			}
		}
			
			
		$arr=aw2_library::get_module($app['collection']['config'],'settings');
		$default_taxonomy = aw2_library::get_post_meta($arr['id'],'default_taxonomy');
		
			
		if(!empty($default_taxonomy)){
			$sql  = $wpdb->prepare(" SELECT MAX(p.post_modified_gmt) AS lastmod
					FROM	$wpdb->posts AS p
					WHERE post_status IN ('publish') AND post_type = %s ", $app['collection']['posts']['post_type'] );
			$mod = $wpdb->get_var( $sql );

			$terms = get_terms( array(
						'taxonomy' => $default_taxonomy,
						'hide_empty' => false,
					) );
			if( ! empty( $terms ) && ! is_wp_error( $terms )  ){
				$chf = 'weekly';
				$pri = 1.0;
				foreach ( $terms as $term  ) {

					$url = array();
					$url['loc'] = site_url().'/'.$app['slug'].'/'.$term->slug.'/';
					$url['pri'] = $pri;
					$url['mod'] = $mod;
					$url['chf'] = $chf;
					$output .= $wpseo_sitemaps->renderer->sitemap_url( $url );
					
				}
			}
			
		} 
			
	
		
		
		if(aw2_library::get_module($app['collection']['config'],'custom-sitemap',true))
			$output .= aw2_library::module_run($app['collection']['config'],'custom-sitemap');
		
		//Build the full sitemap
        $sitemap = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $sitemap .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ';
        $sitemap .= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $sitemap .= $output . '</urlset>';
        //echo $sitemap;
        $wpseo_sitemaps->set_sitemap($sitemap);
	}
}

class awesome_app{
	
	public function exists($slug){
		$registered_apps=&aw2_library::get_array_ref('apps');
		$return_val = isset($registered_apps[$slug]);
		
		return $return_val;
	}
	
		
	public function setup($slug){
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		$this->base_path=site_url().'/'.$slug;
		$this->path=site_url().'/'.$slug;
		
		if($slug=='root'){
			$this->base_path=site_url();
			$this->path=site_url();
		}
		
		$this->safe_id=uniqid();
		$this->slug=$slug;
		$this->name=$registered_apps[$slug]['name'];
		$this->post_id=$registered_apps[$slug]['post_id'];

		$this->collection=$registered_apps[$slug]['collection'];
			
		$this->settings = array();
		
		if(isset($this->collection['config'])){
		$config_posts=aw2_library::get_collection(['post_type'=>$this->collection['config']['post_type']]);
		$this->configs = $config_posts	;
			
		}
		
		//set up the current user details
		
		$this->user=array();
		$user=&$this->user;
		if(is_user_logged_in()){
			$current_user = wp_get_current_user();
			$user['login']=$current_user->user_login;
			$user['email']=$current_user->user_email;
			$user['display_name']=$current_user->display_name;
			$user['ID']=$current_user->ID;
		}
		else{
			$user['login']='guest';
			$user['email']='guest';
			$user['display_name']='guest';
			$user['ID']=null;
			
		}
		aw2_library::set('app',(array) $this);				

	}
	

	
	public function load_settings(){
		$app=&aw2_library::get_array_ref('app');
	
		if(!isset($app['configs']['settings']))
			return;
		
		$settings_post_id = $app['configs']['settings']['id'];
		$all_post_meta = aw2_library::get_post_meta($settings_post_id);
	
		foreach($all_post_meta as $key=>$meta){
			
			//ignore private keys
			if(strpos($key, '_') === 0 )
				continue;
			
			$app['settings'][$key] = $meta;

		}
	}	
	
	public function setup_collections(){
		
		//setup and define collections
		foreach($this->collection as $collection_name => $collection){
			aw2_library::add_service(strtolower($collection_name),'app collections',$collection);
		}
		
		//setup services
		if(!isset($this->configs['services']))
			return;
		
		$service_post = $this->configs['services'];
		
		aw2_library::parse_shortcode($service_post['code']);

		$services=&aw2_library::get_array_ref('app.services');
		
		foreach($services as $service_name =>$service){
			aw2_library::add_service($service_name,$service['desc'], $service['post_type']);
		}
	}
	
	public function run_init(){
				
		if(!isset($this->configs['init']))
			return;
		
		$init = $this->configs['init'];
		
		aw2_library::parse_shortcode($init['code']);
	}
	
	
	public function check_rights($query){		//any changes to this function or related to this function should reflect in the if.user_can_access service
		if(current_user_can('administrator'))return;
		
		if(isset($this->configs['rights'])){
			
			aw2_library::parse_shortcode($this->configs['rights']['code']);
			
			$rights =&aw2_library::get_array_ref('app','rights');
			
			if(!isset($rights['access']) || strtolower($rights['access']['mode']) === 'public')return;
			
			if(strtolower($rights['access']['mode']) === 'private'){
				wp_die('Access to this app is private.');
			}
			
			// must be logged in
			if(!isset($rights['auth']) && is_user_logged_in() )return;

			foreach($rights['auth'] as $auth){
				if(is_callable(array('awesome_auth', $auth['method']))){
					$pass = call_user_func(array('awesome_auth', $auth['method']),$auth);
					if($pass === true)return;
				}
			}
		
			$login_url=wp_login_url();
			if(isset($rights['access']['unlogged']) && $rights['access']['unlogged'] !== 'wp_login'){
			$login_url=site_url().'/'. $rights['access']['unlogged'];
			}
			
			$separator = (parse_url($login_url, PHP_URL_QUERY) == NULL) ? '?' : '&';
			$login_url .= $separator.'redirect_to='.urlencode(site_url().'/'.$query->request);
			
			if(isset($rights['access']['title'])){
				$login_url .= '&title='. urlencode($rights['access']['title']);
			}
			
			header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
			wp_redirect( $login_url );
			exit();
		
		}else{
			$options = get_option('awesome-app-' . $this->slug);
			if(!isset($options) || ('1' != $options['enable_rights'])) return true;
			
			if('1' == $options['enable_vsession']){
				$vsession_key = $options['vsession_key'] ? $options['vsession_key'] : 'email';
				$vsession = awesome_auth::vsession2($vsession_key);
				if($vsession) return;
			}
			
			if('1' == $options['enable_single_access']){
				$auth_for_single = array();
				$auth_for_single['all_roles'] = $options['single_access_roles'];
				$has_single_access = awesome_auth::single_access($auth_for_single);
				if($has_single_access) return;
			}
			
			$modular_check = $this->check_modulewise_rights($options);
			if($modular_check) return;
			
			$login_url=wp_login_url();
			if('' != $options['unlogged'] && $options['unlogged'] !== 'wp_login'){
				$login_url=site_url().'/'. $options['unlogged'];
			}
			
			$separator = (parse_url($login_url, PHP_URL_QUERY) == NULL) ? '?' : '&';
			$login_url .= $separator.'redirect_to='.urlencode(site_url().'/'.$query->request);
			
			header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
			wp_redirect( $login_url );
			exit();
		}
	}

	public function check_modulewise_rights($options){
		if(!is_user_logged_in()) return false;
		
		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		$path = str_replace($this->base_path, '', $current_url);
		$path = explode('/', $path);
		$module = $path[1];
		
		if('ajax' == $module){
			$module = $path[2];
		}
		
		$open_endpoints = array("css","js","t","file","fileviewer","excel","search","callback","csv_download","report_csv","report_raw","mreports_csv");
		
		if(in_array($module, $open_endpoints)){
			return true;
		}
		
		if(!$module){
			$module = 'home';
		}
		
		$module = explode(".", $module)[0];
		
		$roles = $options['roles'];
		if( 0 == count($roles) ) return true;		//return true if no roles selected
		
		foreach($roles as $key => $val){
			if(current_user_can($key)){
				if('1' == $val['access']) return true;
				
				$acees_cap = 'm_' . $this->slug . '_' . $module;
				if(current_user_can($acees_cap)) return true;
			}
		}
		
		return false;
	}
	
	
	public function resolve_route($pieces,$query){
		controllers::resolve_route($pieces,$query);
	}	

	public function get_app_ticket($ticket){
		$json=\aw2\session_ticket\get(["main"=>$ticket,"field"=>'ticket_activity'],null,null);
		if(!$json){
			echo 'Ticket is invalid in get_app_ticket: ' . $ticket;
			exit();			
		}
		$ticket_activity=json_decode($json,true);
		
		if(!isset($ticket_activity['app'])){
			echo 'App is not set in ticket: ' . $ticket;
			exit();			
		}
		return $ticket_activity['app'];
	}
	public function get_app_ts($ticket){
		$slug=\aw2\session_ticket\get(["main"=>$ticket,"field"=>'app'],null,null);
		if(!$slug){
			echo 'Ts App is not set in ticket: ' . $ticket;
			exit();			
		}
		return $slug;
	}	
	
}

class awesome_auth{
	static function twofactor_login_authenticator($auth){
		if(!is_user_logged_in())return false;
		if(!isset($auth['all_roles']))return true;
		
		//check roles
		$all_roles = explode(',',$auth['all_roles']);	
		foreach($all_roles as $role){
			
			if(!current_user_can($role))return false; //if any of the role/capability is not valid throw the user
		}
		return true; //user is logged in and has all the roles/capabilities.
	}
	
	static function single_access($auth){

		if(!isset($_REQUEST['username']) || !isset($_REQUEST['password'])){
			if(isset($_REQUEST['force_single_access']))
			{
				echo 'error::Password and Username Required';
				exit;
			}		
			else
				return false;
		}
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
		

		$app = &aw2_library::get_array_ref('app'); 
		
		$user = get_user_by( 'login', $username );
		
		//check that password is valid
		if(!$user || !wp_check_password( $password, $user->data->user_pass, $user->ID )){
				if(isset($_REQUEST['force_single_access']))
				{
					echo 'error::Invalid Username or Password';
					exit;
				}		
				else
					return false;
		}
		$result=array();
		$result['login']=$user->user_login;
		$result['email']=$user->user_email;
		$result['display_name']=$user->display_name;
		$result['ID']=$user->ID;
		$app['user']=$result;				
		
		

		if(in_array('administrator',(array)$user->roles))return true;
		if(!isset($auth['all_roles']))return true;
		
	
		
		//check roles
		$all_roles = explode(',',$auth['all_roles']);
		
		foreach($all_roles as $role){
			
			if(in_array($role,(array)$user->roles)){
				return true;
			}
		}
		
		if(isset($_REQUEST['force_single_access'])){	 //if any of the role/capability is not valid throw the user
			echo 'error::Role Mismatch';
			exit;
		}
		
		return false; //user is logged in and has all the roles/capabilities.
	}
	
	static function vsession($auth){
		
		//check for cookie -> 
		if(isset($_COOKIE['aw2_vsession'])){
						
			$app = &aw2_library::get_array_ref('app'); 
		//cookie = yes
			//get app_valid status
			$name=$app['slug'].'_valid';
			
			$vsession=\aw2\vsession\get([],null,'');
			//if app_valid=yes  return true
			if(isset($vsession[$name]) && $vsession[$name] === 'yes'){
				$app['session']=$vsession;
				return true;
			} 
	
			//else
				//set auth_data
			if(!isset($auth['code'])){
				wp_die('Authentication code is missing');
			}
			
			
			
				//run auth code
			aw2_library::parse_shortcode($auth['code']);

			 //if auth_data.status == success
			if(isset($app['auth']['status']) && $app['auth']['status'] == 'success'){
					//set app_valid=yes and return true
				$atts['key']=$app['slug'].'_valid';
				$atts['value']='yes';
				\aw2\vsession\set($atts,null,'');
				return true;
			}				
			//else go login	
		}
		
		//at this stage with either cookie is not set or user did not authenticate
		//create a cookie for vsession
		
		$reply=aw2\vsession\create('','','');
		return false;
		
	}

	static function vsession2($vsession_key){
		
		//check for cookie -> 
		if(isset($_COOKIE['aw2_vsession'])){
						
			$app = &aw2_library::get_array_ref('app'); 
		//cookie = yes
			//get app_valid status
			$name=$app['slug'].'_valid';
			
			$vsession=\aw2\vsession\get([],null,'');
			
			if(isset($vsession[$name]) && $vsession[$name] === 'yes'){
				$app['session']=$vsession;
				return true;
			}
			
			if(isset($vsession[$vsession_key])){
				$app['user'][$vsession_key]= $vsession[$vsession_key];
				if('' != $vsession[$vsession_key]){
					$app['auth']['status']= 'success';
				}else{
					$app['auth']['status']= 'error';
				}
			}
			
			//if auth_data.status == success
			if(isset($app['auth']['status']) && $app['auth']['status'] == 'success'){
					//set app_valid=yes and return true
				$atts['key']=$app['slug'].'_valid';
				$atts['value']='yes';
				\aw2\vsession\set($atts,null,'');
				return true;
			}	
		}
		
		//at this stage with either cookie is not set or user did not authenticate
		//create a cookie for vsession
		
		$reply=aw2\vsession\create('','','');
		return false;
	}
}

class controllers{
	static $module;
	static $template;
	
	static function set_index_header(){
		

		$app=&aw2_library::get_array_ref('app');
		if(!isset($app['collection']['config'])) return false;
		
		$arr=aw2_library::get_module($app['collection']['config'],'settings');
		if(!$arr) return false;
		
		aw2_library::module_run($app['collection']['config'],'settings');
		$no_index = aw2_library::get_post_meta($arr['id'],'no_index');
		
		if($no_index !== 'yes')  return false;
		
		header("X-Robots-Tag: noindex", true);
		
	}
	
	static function set_cache_header($cache){
		
		// skip cache false
		//logged in user
		// app enables cache
		$c=&aw2_library::get_array_ref('cache');
		
		if($cache==='yes' && $c['enable']==='yes'){
			header("Cache-Control: max-age=31536000, public");
			header("Pragma: public");
		}	
		else{
			header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.		
			header("Pragma: no-cache"); // HTTP 1.0.
			header("Expires: 0"); // Proxies.
		}
	}
	
	static function resolve_route($pieces,$query){

		$ajax=false;
		$app=&aw2_library::get_array_ref('app');
				
		$app['active']=array('controller'=>'','collection'=>'','module'=>'','template'=>'');
		
		$o=new stdClass();
		$o->pieces=$pieces;
		
		if(empty($o->pieces))
			$o->pieces=array('home');
		
		$controller = $o->pieces[0];	
		if($controller == "ajax"){
			array_shift($o->pieces);
			$controller = $o->pieces[0]; 
			$ajax = true;
		}
	
		if(is_callable(array('controllers', 'controller_'.$controller))){
			array_shift($o->pieces);
			
			$app['active']['controller'] = $controller;
			$app['active']['collection'] = $app['collection']['modules'];
			
			call_user_func(array('controllers', 'controller_'.$controller),$o, $query);
		}
		
		if($ajax != true){
			self::controller_pages($o, $query);
			self::controller_posts($o, $query);
			self::controller_taxonomy($o, $query);
		}

		self::controller_modules($o);
		
		self:: controller_404($o);
		
	}
	
	static function controller_apphelp($o, $query){
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');
		
		if(!isset($app['collection']['apphelp'])) return;
		
		$slug= $o->pieces[0];
		$post_type = $app['collection']['apphelp']['post_type'];
		
		if(!aw2_library::get_post_from_slug($slug,$post_type,$post)) return;
			
		array_shift($o->pieces);
		self::set_qs($o);
		
		$app['active']['collection'] = $app['collection']['apphelp'];
		$app['active']['module'] = $slug; // this is kept to keep this workable
		$app['active']['controller'] = 'apphelp';	
		
		if(isset($app['configs'])){
			$layout='';
			$app_config = $app['configs'];
			$awesome_core=&aw2_library::get_array_ref('awesome_core');
			
			if(isset($awesome_core['layout'])){
				$layout=$awesome_core['layout']['code'];
			}
			if(isset($awesome_core['apphelp-content-layout'])){
				$layout=$awesome_core['apphelp-content-layout']['code'];
			}

			if(!empty($layout)){
				$output = aw2_library::parse_shortcode($layout);
			}
		}
		
		if($output !== false){
			echo $output;
			exit();
		}
		
		$query->query_vars[$post_type]=$slug;
		$query->query_vars['post_type']=$post_type;
		$query->query_vars['name']=$slug;
		unset($query->query_vars['error']);
		
		return;		
	}	
	
	static function controller_css($o){
		self::$module=array_shift($o->pieces);
		$app=&aw2_library::get_array_ref('app');
		self::module_parts();
		self::set_qs($o);
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;
		
		$result=aw2_library::module_run($app['active']['collection'],self::$module,self::$template);


		header("Content-type: text/css");
		$c=&aw2_library::get_array_ref('cache');
		$c['enable']='yes';
		self::set_cache_header('yes');
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60*24*365))); // 1 year
		echo $result;
		exit();	
	}	
	
	static function controller_js($o){	
		self::$module=array_shift($o->pieces);

		$app=&aw2_library::get_array_ref('app');
		self::module_parts();
		self::set_qs($o);
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;
		
		$result=aw2_library::module_run($app['active']['collection'],self::$module,self::$template);
		
		header("Content-type: application/javascript");
		header("Service-Worker-Allowed: /");
		$c=&aw2_library::get_array_ref('cache');
		$c['enable']='yes';
		self::set_cache_header('yes');
		
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60*24*365))); // 1 year
		echo $result;
		exit();	
	}
	
	static function controller_file($o){
		self::$module=array_shift($o->pieces);
		$app=&aw2_library::get_array_ref('app');
		self::module_parts();
		self::set_qs($o);
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;
		
		$filename=$_REQUEST['filename'];
		$file_extension=explode('.',$filename);
		$extension=end($file_extension);
		
		$folder=aw2_library::get('realpath.app_folder');
		$path=$folder . $filename;
		
		switch ($extension) {
			case 'excel':
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');	
				break;				
			case 'xls':
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');	
				break;
			case 'xlsx':
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');	
				break;
			case 'pdf':
				header('Content-Type: application/pdf');	
				break;
		}
		
		header('Content-Disposition: attachment;filename="' . $filename.'"');
		self::set_cache_header('no');
		self::set_index_header();
		
		$result=file_get_contents($path);	
		echo $result;
		exit();	
	}
	
	static function controller_fileviewer($o){
		self::$module=array_shift($o->pieces);
		$app=&aw2_library::get_array_ref('app');
		self::module_parts();
		self::set_qs($o);
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;
		
		$filename=$_REQUEST['filename'];
		$file_extension=explode('.',$filename);
		$extension=end($file_extension);
		
		$folder=aw2_library::get('realpath.app_folder');
		$path=$folder . $filename;
	
		switch ($extension) {
			case 'excel':
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');	
				break;				
			case 'xls':
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');	
				break;
			case 'xlsx':
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');	
				break;
			case 'pdf':
				header('Content-Type: application/pdf');	
				break;
			default:
				header('Content-Type: '.mime_content_type($filename));
				break;	
		}			
		
		header("Cache-Control: max-age=2792000,public");
		header("Pragma: public");
		
		$result=file_get_contents($path);	
		echo $result;
		exit();	
	}

	static function controller_excel($o){
		self::$module=array_shift($o->pieces);
		$app=&aw2_library::get_array_ref('app');
		self::module_parts();
		self::set_qs($o);
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;
		
		$filename=self::$module;	
		$folder=aw2_library::get('realpath.app_folder');
		$path=$folder . $filename;

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename.'"');
		
		self::set_cache_header('no');
		self::set_index_header();
		
		$result=file_get_contents($path);	
		echo $result;
		exit();	
	}
	
	static function controller_z($o){
		//not cached since only admin has rights
		if(!current_user_can("develop_for_awesomeui")) exit;
		
		self::$module='';	
		if(count($o->pieces)==1 ){
			self::$module=array_shift($o->pieces);	
		}
		
		$app=&aw2_library::get_array_ref('app');
		
		if(empty(self::$module) ){
			//show list of modules
			$args=array(
				'post_type' => $app['collection']['modules']['post_type'],
				'post_status'=>'publish',
				'posts_per_page'=>500,
				'no_found_rows' => true, // counts posts, remove if pagination required
				'update_post_term_cache' => false, // grabs terms, remove if terms required (category, tag...)
				'update_post_meta_cache' => false, // grabs post meta, remove if post meta required	
				'orderby'=>'title',
				'order'=>'ASC'
			);

			self::set_cache_header('no');
			self::set_index_header();
			
			$results = new WP_Query( $args );
			$my_posts=$results->posts;

			foreach ($my_posts as $obj){
				echo('<a target=_blank href="' . site_url("wp-admin/post.php?post=" . $obj->ID  . "&action=edit") .'">' . $obj->post_title . '(' . $obj->ID . ')</a>' . '<br>');
			}
				echo('<br><a target=_blank href="' . site_url("wp-admin/post-new.php?post_type=" . $app['active']['collection']['post_type']) .'">Add New</a><br>');

		
		} else {
			aw2_library::get_post_from_slug(self::$module,$app['active']['collection']['post_type'],$post);
			header("Location: " . site_url("wp-admin/post.php?post=" . $post->ID  . "&action=edit"));
		}		
		exit();	
	}
	static function controller_s($o){
		global $wpdb;
		
		if(!current_user_can("develop_for_awesomeui")) return;
		
		self::$module=array_shift($o->pieces);	
		$app=&aw2_library::get_array_ref('app');
		
		$post_type=$app['active']['collection']['post_type'];
		echo '<h3>Searching for:' . urldecode(self::$module) . '</h3>';
		$sql="Select * from  ".$wpdb->posts."  where post_status='publish' and post_content like '%" . urldecode(self::$module) . "%' and post_type='" . $post_type . "'";
		global $wpdb;
		$results = $wpdb->get_results($sql,ARRAY_A);
		foreach ($results as $result){
			echo('<a target=_blank href="' . site_url("wp-admin/post.php?post=" . $result['ID']  . "&action=edit") .'">' . $result['post_title'] . '(' . $result['ID'] . ')</a>' . '<br>');
		}		
		exit();	
	}	
	
	static function controller_search($o){
		self::$module=array_shift($o->pieces);
		$pieces=explode('.',self::$module);
		self::set_qs($o);
		
		$token=$pieces[0];
		$nonce=$pieces[1];
		
		//verify that nonce is valid
		if(wp_create_nonce($token)!=$nonce){
			echo 'Error E1:The Data Submitted is not valid. Check with Administrator';
			exit();		
		}
		$collection=aw2_library::get('services.search_service');
		echo aw2_library::module_run($collection,'search-submit',null,null,["ticket"=>self::$module]);
		exit();	
	}
	
	static function controller_callback($o){
		self::$module=array_shift($o->pieces);
		$pieces=explode('.',self::$module);
		self::set_qs($o);
		
		$token=$pieces[0];
		$nonce=$pieces[1];
		
		//verify that nonce is valid
		if(wp_create_nonce($token)!=$nonce){
			echo 'Error E1:The Data Submitted is not valid. Check with Administrator';
			exit();		
		}

		$json=get_option($token);
		if(empty($json)){
			echo 'Error E2:The Data Submitted is not valid. Check with Administrator';
			exit();		
		}				
		echo aw2_library::call_api($json);
		
		exit();	
	}
	
	static function controller_csv_download($o){

		$csv_ticket=array_shift($o->pieces);
		self::set_qs($o);
		
		$filename=$_REQUEST['filename'];
		
		header("Content-type: application/csv");
		header('Content-Disposition: attachment;filename="' . $filename);
		
		self::set_cache_header('no');

		
		$redis = aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		
		if($redis->exists($csv_ticket)){
			$result = $redis->zRange($csv_ticket, 0, -1);
			$output=implode('',$result);
			echo $output;
		}
		exit();	
	}
	
	static function controller_send_mail($o){

		$csv_ticket=array_shift($o->pieces);
		self::set_qs($o);
		
		$filename=$_REQUEST['filename'];
		
		header("Content-type: application/csv");
		header('Content-Disposition: attachment;filename="' . $filename);
		
		self::set_cache_header('no');

		
		$redis = aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		
		if($redis->exists($csv_ticket)){
			$result = $redis->zRange($csv_ticket, 0, -1);
			$output=implode('',$result);
			echo $output;
		}
		exit();	
	}
	
	static function controller_report_csv($o){

		$csv_ticket=array_shift($o->pieces);
		self::set_qs($o);
		
		header("Content-type: text/csv");
		self::set_cache_header('no');
		header('Content-Disposition: attachment;filename="' . $csv_ticket . '.csv');		

		$sql=\aw2\session_ticket\get(["main"=>$csv_ticket,"field"=>'sql'],null,null);
		if(empty($sql)){
			echo 'Ticket is invalid: ' . $csv_ticket;
			exit();			
		}
		
		$redis = aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		
		$conn = new \mysqli(DB_HOST,DB_USER , DB_PASSWORD, DB_NAME);
			if(mysqli_multi_query($conn,$sql)){
					do{
						if ($result=mysqli_store_result($conn)) {

							$buffer = fopen('php://memory','w');
							
							$first_row=\aw2\session_ticket\get(["main"=>$csv_ticket,"field"=>'first_row'],null,null);
							if($first_row){
								$data = trim($first_row) . PHP_EOL;
								fwrite($buffer, $data );
							}
						
							for($i = 0; $row = mysqli_fetch_assoc($result); $i++){
									fputcsv($buffer, $row);
							}
							rewind($buffer);
							$csv = stream_get_contents($buffer);
							echo $csv;
			
						}
						} while(mysqli_more_results($conn) && mysqli_next_result($conn));
			}
		exit();	
	}		

	static function controller_report_raw($o){

		$csv_ticket=array_shift($o->pieces);
		
		$sql=\aw2\session_ticket\get(["main"=>$csv_ticket,"field"=>'sql'],null,null);
		
		if(empty($sql)){
			echo 'Ticket is invalid: ' . $csv_ticket;
			exit();			
		}
		
		$redis = aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		
		$conn = new \mysqli(DB_HOST,DB_USER , DB_PASSWORD, DB_NAME);
		
		if(mysqli_multi_query($conn,$sql)){
			echo "<table border='1' cellpadding='0' cellspacing='0'>";
			do{
				if ($result=mysqli_store_result($conn)) {

					$first_row=\aw2\session_ticket\get(["main"=>$csv_ticket,"field"=>'first_row'],null,null);
					if($first_row){
						$th_data = explode(",",$first_row);
						foreach($th_data as $th){
							echo "<th align='left'>".str_replace('"',"",$th)."</th>";
						}
					}
					
					for($i = 0; $row = mysqli_fetch_assoc($result); $i++){
						echo "<tr>";
						foreach($row as $td){
							echo "<td>".$td."</td>";
						}
						echo "</tr>";
					}
	
				}
			} while(mysqli_more_results($conn) && mysqli_next_result($conn));
			echo "</table>";
		}
		
		exit();	
	}

	
	static function controller_pages($o, $query){
		if(empty($o->pieces))return;

		\aw2\debug\flow(['main'=>'Before running Page/Module']);			
 
	
		$slug= $o->pieces[0];
		
		$app=&aw2_library::get_array_ref('app');
		
	if(isset($app['settings']['enable_cache'])){
		self::set_cache_header($app['settings']['enable_cache']);
	}
	else	
			self::set_cache_header('no'); // HTTP 1.1.
	
	self::set_index_header();
	
		if(isset($app['collection']['pages'])){
			$post_type = $app['collection']['pages']['post_type'];
			
			
			if(aw2_library::get_post_from_slug($slug,$post_type,$post)){
				array_shift($o->pieces);
				self::set_qs($o);
				$app['active']['collection'] = $app['collection']['pages'];
				$app['active']['module'] = $slug;
				$app['active']['controller'] = 'page';
				
				$output = self::run_layout($app, 'pages', $slug,$query);
			
				if($output !== false){
					echo $output; 
					\aw2\debug\flow(['main'=>'After running Page']);			
				
					exit();
				}
				
				
				return;
			}
		}
	
		if(isset($app['collection']['modules'])){
			$post_type = $app['collection']['modules']['post_type'];
			if(aw2_library::get_post_from_slug($slug,$post_type,$post)){
				array_shift($o->pieces);
				self::set_qs($o);
				
				$app['active']['collection'] = $app['collection']['modules'];
				$app['active']['module'] = $slug;
				$app['active']['controller'] = 'module';
				

				$output = self::run_layout($app, 'modules', $slug,$query);
				if($output !== false){
					echo $output;
					\aw2\debug\flow(['main'=>'After running Module']);		
					
					exit();
				}
				
				return;
				
			}
		}	
		
		
		return;
	}
	
	static function controller_modules($o){ 

		if(empty($o->pieces))return;

		
		self::set_cache_header('no'); // HTTP 1.1.
		self::set_index_header();
		
		$app=&aw2_library::get_array_ref('app');
		self::$module= $o->pieces[0];
		self::module_parts();
		
		$post_type = $app['collection']['modules']['post_type'];
		if(aw2_library::get_post_from_slug(self::$module,$post_type,$post)){
			array_shift($o->pieces);

			$app['active']['collection'] = $app['collection']['modules'];
			$app['active']['controller'] = 'modules';
			
			self::set_qs($o);
			$app['active']['module'] = self::$module;
			$app['active']['template'] = self::$template;
			$result=aw2_library::module_run($app['active']['collection'],self::$module,self::$template);
			echo $result;
			//render debug bar if needs to be rendered	
			echo \aw2\debugbar\ajax_render([]);

			exit();	
		}
	}

	static function controller_t($o){ 
		if(empty($o->pieces))return;

		self::set_cache_header('no'); // HTTP 1.1.
		
		$app=&aw2_library::get_array_ref('app');
		$ticket=array_shift($o->pieces);
		$hash=\aw2\session_ticket\get(["main"=>$ticket],null,null);
		if(!$hash || !$hash['ticket_activity']){
			echo 'Ticket is invalid: ' . $ticket;
			exit();			
		}
		$ticket_activity=json_decode($hash['ticket_activity'],true);
		
		
		self::set_qs($o);
		$app['active']['controller'] = 'ticket';
		$app['active']['ticket'] = $ticket;
		
		if(isset($ticket_activity['service'])){
			//$hash['main']=$ticket_activity['service'];
			$hash['service']=$ticket_activity['service'];
			$result=\aw2\service\run($hash,null,[]);
			echo $result;
			//render debug bar if needs to be rendered	
			echo \aw2\debugbar\ajax_render([]);		
			exit();	
		}
		
		if(!isset($ticket_activity['module'])){
			echo 'Ticket is invalid for module: ' . $ticket;
			exit();			
		}		
		
		
		self::$module= $ticket_activity['module'];
		self::module_parts();

		if(isset($ticket_activity['collection']))
			$app['active']['collection'] = $ticket_activity['collection'];
		else
			$app['active']['collection'] = $app['collection']['modules'];
			
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;

		$result=aw2_library::module_run($app['active']['collection'],self::$module,self::$template,null,$hash);

		echo $result;
		//render debug bar if needs to be rendered	
		echo \aw2\debugbar\ajax_render([]);
			
		exit();	
	}

	static function controller_ts($o){ 
		if(empty($o->pieces))return;

		self::set_cache_header('no'); // HTTP 1.1.
		
		$app=&aw2_library::get_array_ref('app');
		$ticket=array_shift($o->pieces);
		

		$hash=\aw2\session_ticket\get(["main"=>$ticket],null,null);
		if(!$hash || !$hash['payload']){
			echo 'Ticket is invalid: ' . $ticket;
			exit();			
		}
		$payload=json_decode($hash['payload'],true);
		//\util::var_dump($payload);
		self::set_qs($o);
		$app['active']['controller'] = 'ticket';
		$app['active']['ticket'] = $ticket;
		$result=array();
		foreach ($payload as $one) {
			$arr=isset($one['data'])?$one['data']:array();
			$arr['service']=$one['service'];
			$result[]=\aw2\service\run($arr,null,[]);
		}
		echo implode('',$result);
		//render debug bar if needs to be rendered	
		echo \aw2\debugbar\ajax_render([]);		
		exit();	
	}	
	
	static function controller_posts($o, $query){
	
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');

		if(isset($app['settings']['enable_cache'])){
			self::set_cache_header($app['settings']['enable_cache']);
		}
		else	
				self::set_cache_header('no'); // HTTP 1.1.
		
		self::set_index_header();
		
		if(!isset($app['collection']['posts'])) return;
		
		$slug= $o->pieces[0];
	
		$post_type = $app['collection']['posts']['post_type'];
			
			
		if(!aw2_library::get_post_from_slug($slug,$post_type,$post)) return;
			
		array_shift($o->pieces);
		self::set_qs($o);
		$app['active']['collection'] = $app['collection']['posts'];
		$app['active']['module'] = $slug; // this is kept to keep this workable
		$app['active']['controller'] = 'posts';	
		$output = false;
		
		if(isset($app['configs'])){
			$layout='';
			$app_config = $app['configs'];
			
			if(isset($app_config['layout'])){
				$layout='layout';
			}
			if(isset($app_config['posts-single-layout'])){

				$layout='posts-single-layout';
			}
			
			if(!empty($layout)){
				aw2_library::set('current_post',$post);
				$output = aw2_library::module_run($app['collection']['config'],$layout,null,null);
			}
		}
		
		if($output !== false){
			echo $output;
			exit();
		}
		
		$query->query_vars[$post_type]=$slug;
		$query->query_vars['post_type']=$post_type;
		$query->query_vars['name']=$slug;
		unset($query->query_vars['error']);

		return;
	}
	
	static function controller_taxonomy($o, $query){
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');

		if(isset($app['settings']['enable_cache'])){
			self::set_cache_header($app['settings']['enable_cache']);
		}
		else	
				self::set_cache_header('no'); // HTTP 1.1.
		
		self::set_index_header();
		
		if(!isset($app['settings']['default_taxonomy'])) return;
		
		$slug= $o->pieces[0];
		$taxonomy	= $app['settings']['default_taxonomy'];
		$post_type	= $app['collection']['posts']['post_type'];
	
		if(empty($taxonomy) || !term_exists( $slug, $taxonomy )) return;
			
		array_shift($o->pieces);
		self::set_qs($o);
		//taxonomy archive will be handled by archive.php == archive-content-layout;		
		$query->query_vars[$taxonomy]=$slug;
		$query->query_vars['post_type']=$post_type;
		unset($query->query_vars['attachment']);
		unset($query->query_vars['name']);
		unset($query->query_vars[$app['collection']['pages']['post_type']]);

		return;
	}
	
	static function controller_404($o){
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');
		
		if(isset($app['settings']['enable_cache'])){
			self::set_cache_header($app['settings']['enable_cache']);
		}
		else	
				self::set_cache_header('no'); // HTTP 1.1.
		self::set_index_header();
		
		$post_type = $app['collection']['modules']['post_type'];
		
		if(isset($app['settings']['unhandled_module'])){
			self::$module=$app['settings']['unhandled_module'];

			$app['active']['collection'] = $app['collection']['modules'];
			$app['active']['controller'] = 'unhandled_module';
		
			self::module_parts();
			self::set_qs($o);
			
			$app['active']['module'] = self::$module;
			$app['active']['template'] = self::$template;
			
			$result=aw2_library::module_run($app['active']['collection'],self::$module,self::$template);

			echo $result;
			exit();	
		}
		
		if(aw2_library::get_post_from_slug('404-page',$post_type,$post)){
			array_shift($o->pieces);
			$this->action='404';
			
			$query->query_vars['post_type']=$post_type;
			$query->query_vars['pagename']='404-page';
			return;
		}	
	}
	
	
	static function controller_mreports_csv($o){
		
		$app=&aw2_library::get_array_ref('app');
		$ticket=\aw2\request2\get(['main'=>'ticket_id']);
		
		header("Content-type: text/csv");
		self::set_cache_header('no');
		header('Content-Disposition: attachment;filename="' . $ticket . '.csv');		


		$hash=\aw2\session_ticket\get(["main"=>$ticket],null,null);
		if(!$hash || !$hash['ticket_activity']){
			echo 'Ticket is invalid: ' . $ticket;
			exit();			
		}
		$ticket_activity=json_decode($hash['ticket_activity'],true);
		
		
		self::set_qs($o);
		$app['active']['controller'] = 'ticket';
		$app['active']['ticket'] = $ticket;
		$app['ticket']['data'] = $hash;
		
		if(isset($ticket_activity['service'])){
			$hash['service']=$ticket_activity['service'];
			$result=\aw2\service\run($hash,null,[]);
			$buffer = fopen('php://memory','w');
			
			$first_row=isset($app['first_row']) ? $app['first_row'] : null;
			if($first_row){
				$data = trim($first_row) . PHP_EOL;
				fwrite($buffer, $data );
			}
		
			foreach($app['result'] as $row){
					fputcsv($buffer, $row);
			}
			rewind($buffer);
			$csv = stream_get_contents($buffer);
			echo $csv;
			exit();	
		}
		exit();	
	}			
	
	static function run_layout($app, $collection, $slug,$query){
		
		if(isset($app['configs'])){
			$layout='';
			$app_config = $app['configs'];
			
			if(isset($app_config['layout'])){
				$layout='layout';
			}
			if(isset($app_config[$collection.'-layout'])){
				$layout=$collection.'-layout';

			}
			if(!empty($layout)){
				return aw2_library::module_run($app['collection']['config'],$layout,null,null);
			}
		}
				
		if($collection == 'modules'){
			return 	$result=aw2_library::module_run($app['active']['collection'],$slug,'');

		}
		
		if(!isset($app['active']['collection']['post_type'])){
				return aw2_library::module_run(['post_type'=>'awesome_core'],'layout',null,null);
		}	
		
		// well none of the layout optins exists so hand it over to page.php
		 
		unset($query->query_vars['name']);
		unset($query->query_vars['attachment']);
		unset($query->query_vars['post_type']);
		unset($query->query_vars['page']);
		unset($query->query_vars['error']);
		unset($query->query_vars[$app['active']['collection']['post_type']]);
		
		$query->query_vars['post_type']=$app['active']['collection']['post_type'];
		$query->query_vars['pagename']=$slug;
		
		//exit();
		return false;
	}
	
	static function set_qs($o){
		$qs=&aw2_library::get_array_ref('qs');
		$i=0;
		foreach ($o->pieces as $value){
			$qs[$i]=\aw2\clean\safe(['main'=>$value]);
			$i++;
			/* $pos = strpos($value, '$$');
			if ($pos === false) {
				$qs[$i]=\aw2\clean\safe(['main'=>$value]);
				$i++;
			} else {
				$arr=explode('$$',$value);
				$qs[$arr[0]]=\aw2\clean\safe(['main'=>$arr[1]]);
			} */
			array_shift($o->pieces);
		}
	}
	
	static function module_parts(){
		$t=strpos(self::$module,'.');
		if($t===false){
			self::$template='';
			return;	
		}
		$parts=explode ('.' , self::$module); 
		
		self::$module=$parts[0];
		array_shift($parts);
		self::$template=implode('.',$parts);
	}
	
	/// New function by Sam on 9th august related to Ag Grid 
	static function controller_report_grid($o){

		$grid_ticket=array_shift($o->pieces);
		
		$sql=\aw2\session_ticket\get(["main"=>$grid_ticket,"field"=>'sql'],null,null);
		
		if(empty($sql)){
			echo 'Ticket is invalid: ' . $grid_ticket;
			exit();			
		}
		
		$redis = aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		
		$conn = new \mysqli(DB_HOST,DB_USER , DB_PASSWORD, DB_NAME);

		$report_header_name= "";
		$header= "";
		$rows=array();
		
		if(mysqli_multi_query($conn,$sql)){


			do{
				if ($result=mysqli_store_result($conn)) {

					$first_row=\aw2\session_ticket\get(["main"=>$grid_ticket,"field"=>'first_row'],null,null);
					$header=\aw2\session_ticket\get(["main"=>$grid_ticket,"field"=>'custom_aggrid_header'],null,null);
					$report_header_name=\aw2\session_ticket\get(["main"=>$grid_ticket,"field"=>'header_name'],null,null);
					
					for($i = 0; $row = mysqli_fetch_assoc($result); $i++){

						$rows[] = $row;

				
					}
	
				}
			} while(mysqli_more_results($conn) && mysqli_next_result($conn));


			if(is_array($rows) && count($rows))
			{
				$total_records = count($rows);
			}

			
			
			//$json_decoded_header = json_decode($header);

			$json_decoded_header = json_decode($header,true);
			// print "<pre>";
			// print_r($json_decoded_header);
			$temp_arr = array();

			/* Few variables we have to change here as Ag Grid internally needs Capital case for ex. headerName, enableValue, however our awesome code is 
			making it all lower case of all the array keys hence we are using below code to change the keys in ag grid expected format */

			if(is_array($json_decoded_header) && count($json_decoded_header))
			{
				foreach($json_decoded_header as $key_outer=>$array1)
				{
					if(is_array($array1) && count($array1))
					{
						foreach($array1 as $key_inner=>$array2)
						{
							if($key_inner=="header_name")
							{
								$temp_arr[$key_outer]['headerName'] = $json_decoded_header[$key_outer]['header_name'];
							}
							else if($key_inner=="enable_value")
							{
								$temp_arr[$key_outer]['enableValue'] = (bool)$json_decoded_header[$key_outer]['enable_value'];
							}
							else if($key_inner=="enable_row_group")
							{
								$temp_arr[$key_outer]['enableRowGroup'] = (bool) $json_decoded_header[$key_outer]['enable_row_group'];
							}
							else if($key_inner=="row_group")
							{
								$temp_arr[$key_outer]['rowGroup'] = (bool)$json_decoded_header[$key_outer]['row_group'];
							}
							else if($key_inner=="hide")
							{
								$temp_arr[$key_outer]['hide'] = (bool)$json_decoded_header[$key_outer]['hide'];
							}
							else if($key_inner=="agg_func")
							{
								$temp_arr[$key_outer]['aggFunc'] = $json_decoded_header[$key_outer]['agg_func'];
							}
							else if($key_inner=="enable_pivot")
							{
								$temp_arr[$key_outer]['enablePivot'] = (bool)$json_decoded_header[$key_outer]['enable_pivot'];
							}
							else{
								$temp_arr[$key_outer][$key_inner] = $json_decoded_header[$key_outer][$key_inner];
							}

							$temp_arr[$key_outer]['filter'] = 'agSetColumnFilter';
							// echo "<br>=> key inner ".$key_inner;
							// echo "<br>=> array2 ".$array2;
						}

						if(isset($json_decoded_header[$key_outer]['to_int']) && $json_decoded_header[$key_outer]['to_int'] == 'yes')
						{
							for($counter = 0; $counter<$total_records;$counter++)
							{
								// print_r($temp_arr[$key_outer]);

								$key_name = $temp_arr[$key_outer]['field'];
								
								$rows[$counter][$key_name] = (int) $rows[$counter][$key_name]; 
							}
						}						

						
					}
					
				}
	
			}

			
			$columns_json = json_encode($temp_arr);
			
			

			// print "<pre>";
			// print_r($rows);
			// exit;

			if(isset($rows) && is_array($rows) && count($rows))
			{
				$rows  = json_encode($rows);
			}
			else
			{
				echo "<div style='text-align:center;'><h1>$report_header_name </h1><br><br><h3>No data to display for this selection</h3></div>";
				exit;
			}
			echo "
			<script>
				function onBtExport()
				{
					var params = {
					};					
					gridOptions.api.exportDataAsCsv(params);
				}

			</script>
			<label style='margin-left: 20px;'>
            	
			</label>
			
			<div style='text-align:center;'>
			<button onclick='onBtExport()'>Export to CSV</button><h1>$report_header_name </h1>
			</div>
			";

			echo '<div id="grid-wrapper" style="padding: 1rem; padding-top: 0; overflow:hidden;">';
			echo '<div id="myGrid" style="height: 85%; overflow:hidden;" class="ag-theme-balham" >';
			echo "</div></div>";			


			
			echo "
			<script src='https://unpkg.com/ag-grid-enterprise@21.0.1/dist/ag-grid-enterprise.min.js' ></script>
				<script >
				
				
			var columnDefs = $columns_json ;
			var gridOptions = {
			   defaultColDef: {
				   sortable: true,
				   resizable: true
			   },
			   // set rowData to null or undefined to show loading panel by default
			   
			   rowData: $rows,
			   columnDefs: columnDefs,
			   popupParent: document.body,
			   rowGroupPanelShow: 'always',
			   animateRows: true,
			   sideBar: 'columns',
			   enableCharts: true,
			   pivotMode: true, 
			   groupIncludeFooter: true,
               groupIncludeTotalFooter: true,
    		   pivotColumnGroupTotals: 'before',
			   enableRangeSelection: true,
			   enableRangeHandle: false,
			   enableFillHandle: false,    
			   rowSelection: 'multiple',
			   rowDeselection: true,
			   enablePivot: true,
			   filter: true,
			   sideBar: {
				toolPanels: [
					{
						id: 'columns',
						labelDefault: 'Columns',
						labelKey: 'columns',
						iconKey: 'columns',
						toolPanel: 'agColumnsToolPanel',
					},
					{
						id: 'filters',
						labelDefault: 'Filters',
						labelKey: 'filters',
						iconKey: 'filter',
						toolPanel: 'agFiltersToolPanel',
					}
				],
				defaultToolPanel: 'columns'
			}
			};

			 var gridDiv = document.querySelector('#myGrid');
			 new agGrid.Grid(gridDiv, gridOptions);		
			 
			 

		</script>
		<script type='text/javascript'>window.NREUM||(NREUM={});NREUM.info={'beacon':'bam.nr-data.net','licenseKey':'0dcebb20b3',
			'applicationID':'171679852','transactionName':'bldbMBMEDBFXAUIMWlcdbBYISgsMUgdOS0VRQg==','queueTime':0,
			'applicationTime':526,'atts':'QhBYRlseHx8=','errorBeacon':'bam.nr-data.net','agent':''}</script>
		";			
			
		}		
		exit();	
	}
}