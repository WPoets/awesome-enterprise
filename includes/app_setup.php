<?php
add_action('plugins_loaded','aw2_apps_library::initialize',1);
add_action('init','aw2_apps_library::wp_init',11);
add_action('admin_init','aw2_apps_library::admin_init',1);
add_action( 'parse_request', 'aw2_apps_library::app_takeover' );

add_action( 'admin_menu', 'aw2_apps_library::register_menus' );
//add_action('template_redirect', 'aw2_apps_library::template_redirect');

add_action('generate_rewrite_rules', 'aw2_apps_library::app_slug_rewrite');
add_filter( 'post_type_link', 'aw2_apps_library::fix_app_slug', 10, 3 );
add_filter( 'nav_menu_css_class', 'aw2_apps_library::nav_menu_css_class', 10, 3 );

add_action('wp_head', 'aw2_apps_library::wp_head');
add_action('wp_footer', 'aw2_apps_library::wp_footer');

class aw2_apps_library{
	
	static function initialize(){
		self::load_apps();
		self::setup_services();
		self::run_core('config');
		self::load_env_settings();
		
		//time/zone
		$time_zone = aw2_library::get('settings.time_zone');
		if(!empty($time_zone))
			date_default_timezone_set($time_zone);
	}
	
	static function setup_services(){
		
		aw2_library::add_collection('core',['post_type'=>'awesome_core'],'core service refers to core posts for config etc.');
		
		self::run_core('services');
		$services=&aw2_library::get_array_ref('services');
		
		foreach($services as $key =>$service){
			aw2_library::register_service($key,$service,$service['desc']);
		}
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
		self::register_app_cpts();
		self::run_core('register');
		
		if(is_admin())
			return;
		
		self::run_core('init');
		
	}
	
	static function register_app_cpts(){
		
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
			'rewrite' => true,
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
		
				
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		foreach($registered_apps as $key => $app){
			foreach($app['collection'] as $collection_name => $collection){
				$supports='';
				$hierarchical=false;
				$public=false;
				$slug=null;
				if($collection_name == 'config'){
					$supports = array('title','editor','revisions','custom-fields');
				}
				
				if($collection_name == 'pages'){
					$hierarchical=true;
					$public=true;
					$slug=$key;
				}	
				
				if($collection_name == 'modules'){
					$hierarchical=true;
					$public=false;
				}
				
				if($collection_name == 'apphelp'){
					$hierarchical=false;
					$public=true;
				}
	
				if(!post_type_exists( $collection['post_type'] ))
					self::register_cpt($collection['post_type'],$collection_name,$app['name'],$public,$supports,$hierarchical,$slug);
			}
			
			if(isset($app['collection']['pages']['post_type']))
				$wp_post_types[$app['collection']['pages']['post_type']]->rewrite['slug'] = $key;
		}
	}
	
	static function admin_init(){
		
		self::run_core('backend-init');
		
		self::purge_cache();
		
	}
	
	static function load_apps(){
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		$apphelp=&aw2_library::get_array_ref('apphelp');
		
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
				
			
			$ptr=&aw2_library::get_array_ref('');
			$ptr['app']=$app;
			
			$registered_apps[$app_post['module']]=$ptr['app'];
		}
		
		//load all config post, as they are used they will be consumed.
		$awesome_core=&aw2_library::get_array_ref('awesome_core');
		
		$awesome_core=aw2_library::get_collection(["post_type"=>"awesome_core"]);
		
	}
		
	static function app_takeover($query){
		if(empty($query->request)){
			self::initialize_root(); // it is front page hence request is not set so setup root.
			return;
		}
		
		$pieces = explode('/',$query->request);
		
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
		
		$arr=array();
		$arr['status']='';
		$arr=$app->check_rights($query);

		// run init
		$app->run_init();
				
		//now resolve the route.
		if($app->slug!='root'){
			$app->resolve_route($pieces,$query);
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
	
	static function register_menus(){
		
		add_menu_page('Services', 'Services - Awesome Studio', 'develop_for_awesomeui','awesome-services', 'edit.php?post_type=aw2_app','dashicons-admin-network',2 );
		
		//register services
		$services=&aw2_library::get_array_ref('services');
		foreach($services as $key => $service){
			if(isset($service['post_type'])){
				add_submenu_page('awesome-services', $service['label'], $service['label'],  'develop_for_awesomeui','edit.php?post_type='.$service['post_type']);
			}
		}	
		
		
		add_submenu_page('awesome-studio', 'Apps - Awesome Studio', 'Apps', 'develop_for_awesomeui', 'edit.php?post_type=aw2_app' );
		add_submenu_page( 'awesome-studio', 'Core - Awesome Studio', 'Awesome Core', 'develop_for_awesomeui', 'edit.php?post_type=awesome_core' );
		add_submenu_page('awesome-studio', 'Manage Cache - Awesome Studio', 'Manage Cache', 'develop_for_awesomeui','awesome-studio-cache' ,'aw2_apps_library::manage_cache');
		//register apps menu
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach($registered_apps as $key => $app){
			add_menu_page($app['name'], $app['name'].' App', 'manage_options', 'awesome-app-'.$app['slug'], 'aw2_apps_library::show_app_pages', 'dashicons-admin-multisite',3);
			
			foreach($app['collection'] as $collection_name => $collection){
				add_submenu_page('awesome-app-'.$app['slug'], $app['name'] . ' ' . $collection_name, $collection_name,  'develop_for_awesomeui','edit.php?post_type='.$collection['post_type']);
			}
			//add_submenu_page('awesome-app-'.$app->slug, $app->name . ' config', 'Config',  'develop_for_awesomeui','post.php?post=' . $app->post_id . '&action=edit');
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
					aw2_global_cache_flush(null,null,null);
					break;
				case 'session':
					check_admin_referer( 'session_nonced-purge_all' );
					aw2_session_cache_flush(null,null,'');
					break;
			}
			
			wp_redirect( esc_url_raw( add_query_arg( array( 'awesome_purge' =>'done' ) ) ) );
	}
	
	static function show_app_pages(){
		echo '<div class="wrap ">';        	
		echo 'Not Yet Implemented';
		echo '</div>';		
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
		foreach($app['collection'] as $name=>$collection){
			$collection_post = $collection['post_type'];
			
			if(isset($app['configs'][$collection_post.'-scripts'])){
				$scipts = $app['configs'][$collection_post.'-scripts'];
				echo aw2_library::parse_shortcode($scipts['code']);
			}
		}
		
		
		
	}	
	
	static function wp_footer(){
		self::run_core('footer-scripts');
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
		
		$this->slug=$slug;
		$this->name=$registered_apps[$slug]['name'];
		$this->post_id=$registered_apps[$slug]['post_id'];

		$this->collection=$registered_apps[$slug]['collection'];
			
		$this->settings = array();
		
		if(isset($this->collection['config'])){
		$config_posts=aw2_library::get_collection(['post_type'=>$this->collection['config']['post_type']]);
		$this->configs = $config_posts	;
			
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
			aw2_library::add_collection(strtolower($collection_name),$collection,'app collections ');
		}
		
		//setup services
		if(!isset($this->configs['services']))
			return;
		
		$service_post = $this->configs['services'];
		
		aw2_library::parse_shortcode($service_post['code']);

		$services=&aw2_library::get_array_ref('app.services');
		
		foreach($services as $service_name =>$service){
			aw2_library::add_collection($service_name, $service['post_type'],$service['desc']);
		}
	}
	
	public function run_init(){
				
		if(!isset($this->configs['init']))
			return;
		
		$init = $this->configs['init'];
		
		aw2_library::parse_shortcode($init['code']);
	}
	
	public function check_rights($query){
		
		if(current_user_can('administrator'))return;
		
		if(!isset($this->configs['rights']))return;
		
		aw2_library::parse_shortcode($this->configs['rights']['code']);
		
		$rights =&aw2_library::get_array_ref('app','rights');
		
		if(!isset($rights['access']) || strtolower($rights['access']['mode']) === 'public')return;
		
		if(strtolower($rights['access']['mode']) === 'private'){
			wp_die('Access to this app is private.');
		}
		
		// must be logged in
		if(!isset($rights['auth']) && is_user_logged_in() )return;
		
		
		//must be authenticated


					
		foreach($rights['auth'] as $auth){
			if(is_callable(array('awesome_auth', $auth['method']))){
				$pass = call_user_func(array('awesome_auth', $auth['method']),$auth);
				if($pass === true)return;
			}
		}
			
		//all conditions failed, but use needs to be logged-in so redirect

		$login_url=wp_login_url();
		if(isset($rights['access']['unlogged']) && $rights['access']['unlogged'] !== 'wp_login'){
		   $login_url=site_url().'/'. $rights['access']['unlogged'];
		}
		
		$separator = (parse_url($login_url, PHP_URL_QUERY) == NULL) ? '?' : '&';
		$login_url .= $separator.'redirect_to='.urlencode(site_url().'/'.$query->request);
		
		if(isset($rights['access']['title'])){
			$login_url .= '&title='. urlencode($rights['access']['title']);
		}
		
		wp_redirect( $login_url );
		exit();
		
		
	}
	
	public function resolve_route($pieces,$query){
		controllers::resolve_route($pieces,$query);
	}	

	public function get_app_ticket($ticket){
		$json=aw2_session_ticket_get(["main"=>$ticket,"field"=>'ticket_activity'],null,null);
		if(!$json){
			echo 'Ticket is invalid: ' . $ticket;
			exit();			
		}
		$ticket_activity=json_decode($json,true);
		
		if(!isset($ticket_activity['app'])){
			echo 'Ticket is invalid: ' . $ticket;
			exit();			
		}
		return $ticket_activity['app'];
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


		if(!isset($auth['all_roles']))return true;
		
		//check roles
		$all_roles = explode(',',$auth['all_roles']);
			
		foreach($all_roles as $role){
			
			if(!in_array($role,(array)$user->roles)){
				if(isset($_REQUEST['force_single_access']))
				{
					echo 'error::Role Mismatch';
					exit;
				}		
				else
					return false;
			} //if any of the role/capability is not valid throw the user
		}
	
		return true; //user is logged in and has all the roles/capabilities.
	}
	
	static function vsession($auth){
		
		//check for cookie -> 
		if(isset($_COOKIE['aw2_vsesssion'])){
						
			$app = &aw2_library::get_array_ref('app'); 
		//cookie = yes
			//get app_valid status
			$name=$app['slug'].'_valid';
			
			$vsession=aw2_vsession_get([],null,'');
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
			if($app['auth']['status'] == 'success'){
					//set app_valid=yes and return true
				$atts['key']=$app['slug'].'_valid';
				$atts['value']='yes';
				aw2_vsession_set($atts,null,'');
				return true;
			}				
			//else go login	
		}
		
		//at this stage with either cookie is not set or user did not authenticate
		//create a cookie for vsession
		
		aw2_vsession_create('','','');
		
		return false;
		
	}
}

class controllers{
	static $module;
	static $template;
	
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

		header("Pragma: public");
		header("Content-type: text/css");
		header("Cache-Control: max-age=31536000, public"); 
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
		
		header("Pragma: public");
		header("Content-type: application/javascript");
		header("Cache-Control: max-age=31536000"); 
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
		
		header('Content-Disposition: attachment;filename="' . $filename);
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
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
		header('Content-Disposition: attachment;filename="' . $filename);
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		$result=file_get_contents($path);	
		echo $result;
		exit();	
	}
	
	static function controller_z($o){
		
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
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$redis = new Redis();
		$redis->connect('127.0.0.1', 6379);
		$database_number = 12;
		$redis->select($database_number);
		if($redis->exists($csv_ticket)){
			$result = $redis->zRange($csv_ticket, 0, -1);
			$output=implode('',$result);
			echo $output;
		}
		exit();	
	}
	
	static function controller_data($o){
		self::$module=array_shift($o->pieces);
		$app=&aw2_library::get_array_ref('app');
		
		self::module_parts();
		self::set_qs($o);
		
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;
		
		$result=aw2_library::module_run($app['active']['collection'],self::$module,self::$template);
		echo json_encode($result);
		exit();	
	}
	
	static function controller_pages($o, $query){
		
		if(empty($o->pieces))return;
		
		$slug= $o->pieces[0];
		
		$app=&aw2_library::get_array_ref('app');
	
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
					exit();
				}
				
				return;
				
			}
		}	

		return;
	}
	
	static function controller_modules($o){ 
		if(empty($o->pieces))return;
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
			exit();	
		}
	}

	static function controller_t($o){ 

		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');
		$ticket=array_shift($o->pieces);
		$hash=aw2_session_ticket_get(["main"=>$ticket],null,null);
		if(!$hash || !$hash['ticket_activity']){
			echo 'Ticket is invalid: ' . $ticket;
			exit();			
		}
		$ticket_activity=json_decode($hash['ticket_activity'],true);
		
		if(!isset($ticket_activity['module'])){
			echo 'Ticket is invalid for module: ' . $ticket;
			exit();			
		}		
		
		self::$module= $ticket_activity['module'];
		self::module_parts();
		self::set_qs($o);
		$app['active']['controller'] = 'ticket';
		$app['active']['ticket'] = $ticket;
		
		if(isset($ticket_activity['collection']))
			$app['active']['collection'] = $ticket_activity['collection'];
		else
			$app['active']['collection'] = $app['collection']['modules'];
			
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;
		//util::var_dump($hash);
		$result=aw2_library::module_run($app['active']['collection'],self::$module,self::$template,null,$hash);

		echo $result;
		exit();	
	}
	
	static function controller_posts($o, $query){
	
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');
		
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

		return;
	}
	
	static function controller_404($o){
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');
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
		
		// well none of the layout optins exists so hand it over to page.php
		 
		unset($query->query_vars['name']);
		unset($query->query_vars['attachment']);
		unset($query->query_vars['post_type']);
		unset($query->query_vars['page']);
		unset($query->query_vars['error']);
		
		$query->query_vars['post_type']=$app['active']['collection']['post_type'];
		$query->query_vars['pagename']=$slug;
		
		//exit();
		return false;
	}
	
	static function set_qs($o){
		$qs=&aw2_library::get_array_ref('qs');
		$i=0;
		foreach ($o->pieces as $value){
			$pos = strpos($value, '$');
			if ($pos === false) {
				$qs[$i]=$value;
				$i++;
			} else {
				$arr=explode('~',$value);
				$qs[$arr[0]]=$arr[1];
			}
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
}