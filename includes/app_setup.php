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

require_once('awesome-menus.php');

class aw2_apps_library{
	
	static function initialize(){

		if(current_user_can('develop_for_awesomeui')){
			setcookie("wordpress_no_cache", 'yes', time()+3600);  /* expire in 1 hour */
		}
	
		self::load_apps();
		self::setup_services();
		self::run_core('config');
		self::load_env_settings();
		
		//time/zone
		$time_zone = aw2_library::get('settings.time_zone');
		if(!empty($time_zone))date_default_timezone_set($time_zone);
		
	}
	
	static function setup_services(){
		
		aw2_library::add_service('core','core service refers to core posts for config etc.',['post_type'=>'awesome_core']);
		self::run_core('services');
		
	}
	
	static function run_core($slug){
		
		if(aw2_library::get_module(['service'=>'core'],$slug,true))
			$result=aw2_library::module_run(['service'=>'core'],$slug);
		
	}


	static function load_env_settings(){
		$settings=&aw2_library::get_array_ref('settings');
		
		$arr=aw2_library::get_module(['service'=>'core'],'settings');
		if(!$arr)return;
		
		aw2_library::module_run(['service'=>'core'],'settings');
		$all_post_meta = aw2_library::get_post_meta($arr['id']);
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
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach($registered_apps as $key=>$app){
			if(!self::enable_sitemap($app)) continue;
			
			self::setup_yoast_links($app['slug']);	
		}
		
		
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
	
				if(isset($collection['post_type']) && !post_type_exists( $collection['post_type'] ))
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

	
	$app_key='registered_apps';
	$return_value=null;
	
	if(!current_user_can('develop_for_awesomeui')){
			$return_value=aw2\global_cache\get(["main"=>$app_key,"prefix"=>""],null,null);
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
				$decode=json_decode($app_config,true);
				if($decode)
					$app['collection']['config']=$decode;
				else	
					$app['collection']['config']['post_type']=$app_config;
			}

			$modules=aw2_library::get_post_meta($app_post['id'],'modules_collection');
			if($modules){
				$decode=json_decode($modules,true);
				if($decode)
					$app['collection']['modules']=$decode;
				else	
					$app['collection']['modules']['post_type']=$modules;
			}

			$pages=aw2_library::get_post_meta($app_post['id'],'pages_collection');
			if($pages){
				$decode=json_decode($pages,true);
				if($decode)
					$app['collection']['pages']=$decode;
				else	
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
		aw2\global_cache\set(["key"=>$app_key,"prefix"=>""],json_encode($registered_apps),null);
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
		if($app->slug!='root' || $app_slug=='ajax'){
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
		if(isset($app['collection']['config']) && aw2_library::get_module($app['collection']['config'],'scripts',true)){
			echo aw2_library::module_run($app['collection']['config'],'scripts');
		}
		
		//not sure about collections 
		foreach($app['collection'] as $name=>$collection){
			$collection_post = $collection['post_type'];
			
			if( aw2_library::get_module($collection,$collection_post.'-scripts',true)){
				echo aw2_library::module_run($collection,$collection_post.'-scripts');
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
		$timezone =  new WPSEO_Sitemap_Timezone();
		$mod = $timezone->format_date($mod );
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		
		$smp ='';
		foreach($registered_apps as $key=>$app){
			
			if(!self::enable_sitemap($app)) continue;
			
			$smp .= '<sitemap>' . "\n";
			$smp .= '<loc>' . site_url() .'/'.$app['slug'].'-sitemap.xml</loc>' . "\n";
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
		add_action( "wpseo_do_sitemap_".$slug,  function() use ($slug){
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
		
		$this->slug=$slug;
		$this->name=$registered_apps[$slug]['name'];
		$this->post_id=$registered_apps[$slug]['post_id'];

		$this->collection=$registered_apps[$slug]['collection'];
			
		$this->settings = array();
		
		/* if(isset($this->collection['config'])){
		$config_posts=aw2_library::get_collection(['post_type'=>$this->collection['config']['post_type']]);
		$this->configs = $config_posts	;
			
		} */

		aw2_library::set('app',(array) $this);				

	}
	
	public function run_config_module($slug){
		$app=&aw2_library::get_array_ref('app');
		
		if(!isset($app['collection']['config']))
			return false;		
			
		if(aw2_library::get_module($app['collection']['config'],$slug,true)){
			aw2_library::module_run($app['collection']['config'],$slug);
			return true;
		}
		return false;		
		
	}
	
	public function load_settings(){
		$app=&aw2_library::get_array_ref('app');
		
		if(!isset($app['collection']['config']))
			return;

		$config=$app['collection']['config'];
		
		$arr=aw2_library::get_module($config,'settings');
		if(!$arr)return;
		aw2_library::module_run($config,'settings');
		$all_post_meta = aw2_library::get_post_meta($arr['id']);
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
		
		//Run service module for any additional services
		$this->run_config_module('services');
	}
	
	public function run_init(){
				
		$this->run_config_module('init');
	}
	
	public function check_rights($query){
		
		if(current_user_can('administrator'))return;
		
		if(!$this->run_config_module('rights'))return;
		
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
		
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		wp_redirect( $login_url );
		exit();
		
		
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
			if($app['auth']['status'] == 'success'){
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
		
		aw2\vsession\create('','','');
		
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
		
		if(!isset($app['collection']['apphelp']) || !aw2_library::get_module($app['collection']['apphelp'],$slug,true)) return;
						
		array_shift($o->pieces);
		self::set_qs($o);
		
		$app['active']['collection'] = $app['collection']['apphelp'];
		$app['active']['module'] = $slug; // this is kept to keep this workable
		$app['active']['controller'] = 'apphelp';	
		
		
			$layout='';

			$awesome_core=&aw2_library::get_array_ref('awesome_core');
			if(aw2_library::get_module(['service'=>'core'],'layout',true))
				$layout='layout';
		
			if(aw2_library::get_module(['service'=>'core'],'apphelp-content-layout',true)){
				$layout='apphelp-content-layout';
			}

			if(!empty($layout)){
				$output = aw2_library::module_run(['service'=>'core'],$layout);
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
		
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		header("Pragma: no-cache"); // HTTP 1.0.
		header("Expires: 0"); // Proxies.
		
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
		
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		header("Pragma: no-cache"); // HTTP 1.0.
		header("Expires: 0"); // Proxies.

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
			
			$results = new WP_Query( $args );
			$my_posts=$results->posts;

			foreach ($my_posts as $obj){
				echo('<a target=_blank href="' . site_url("wp-admin/post.php?post=" . $obj->ID  . "&action=edit") .'">' . $obj->post_title . '(' . $obj->ID . ')</a>' . '<br>');
			}
				echo('<br><a target=_blank href="' . site_url("wp-admin/post-new.php?post_type=" . $app['active']['collection']['post_type']) .'">Add New</a><br>');

		
		} else {
			
			$post = aw2_library::get_module(['service'=>'core'],self::$module);
			if(!empty($post))
				header("Location: " . site_url("wp-admin/post.php?post=" . $post['ID']  . "&action=edit"));
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
		
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		header("Pragma: no-cache"); // HTTP 1.0.
		header("Expires: 0"); // Proxies.
		
		$redis = aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
		
		if($redis->exists($csv_ticket)){
			$result = $redis->zRange($csv_ticket, 0, -1);
			$output=implode('',$result);
			echo $output;
		}
		exit();	
	}

	
	static function controller_pages($o, $query){
		if(empty($o->pieces))return;
		
		$slug= $o->pieces[0];
		
		$app=&aw2_library::get_array_ref('app');
		
	if(isset($app['settings']['enable_cache']) && $app['settings']['enable_cache']==='yes'){
			header("Cache-Control: public, must-revalidate");
	}
	else	
			header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
	
		if(isset($app['collection']['pages'])){
			$check=aw2_library::get_module($app['collection']['pages'],$slug,true);

			if($check){
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
			$check=aw2_library::get_module($app['collection']['modules'],$slug,true);
			if($check){
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

		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		
		$app=&aw2_library::get_array_ref('app');
		self::$module= $o->pieces[0];
		self::module_parts();
		
		$check=aw2_library::get_module($app['collection']['modules'],self::$module,true);
		if($check){
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

		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		
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
			$hash['main']=$ticket_activity['service'];
			$result=\aw2\service\run($hash,null,[]);
			echo $result;
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
		exit();	
	}
	
	static function controller_posts($o, $query){
	
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');

		if(isset($app['settings']['enable_cache']) && $app['settings']['enable_cache']==='yes')
				header("Cache-Control: public, must-revalidate");
		else	
				header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		
		if(!isset($app['collection']['posts'])) return;
		
		$slug= $o->pieces[0];
	
		$post_type = $app['collection']['posts']['post_type'];
			
			
		if(!aw2_library::get_module($app['collection']['posts'],$slug,true)) return;
			
		array_shift($o->pieces);
		self::set_qs($o);
		$app['active']['collection'] = $app['collection']['posts'];
		$app['active']['module'] = $slug; // this is kept to keep this workable
		$app['active']['controller'] = 'posts';	
		$output = false;
		
		if(isset($app['collection']['configs'])){
			$layout='';
				
			if(aw2_library::get_module($app['collection']['config'],'layout',true)){
				$layout='layout';
			}
			
			if(aw2_library::get_module($app['collection']['config'],'posts-single-layout',true) ){
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

		if(isset($app['settings']['enable_cache']) && $app['settings']['enable_cache']==='yes')
				header("Cache-Control: public, must-revalidate");
		else	
				header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		
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
		unset($query->query_vars['page']);
		unset($query->query_vars[$app['collection']['posts']['post_type']]);
		unset($query->query_vars[$app['collection']['pages']['post_type']]);

		return;
	}
	
	static function controller_404($o){
	
		if(empty($o->pieces))return;
		
		$app=&aw2_library::get_array_ref('app');
		
		if(isset($app['settings']['enable_cache']) && $app['settings']['enable_cache']==='yes')
				header("Cache-Control: public, must-revalidate");
		else	
				header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		
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
		
		if(isset($app['collection']['config'])){
			$layout='';
			
			if(aw2_library::get_module($app['collection']['config'],'layout',true))
				$layout='layout';
				
			if(aw2_library::get_module($app['collection']['config'],$collection.'-layout',true))
				$layout=$collection.'-layout';

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