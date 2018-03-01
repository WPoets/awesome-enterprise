<?php

add_action('plugins_loaded','aw2_apps_library::load_apps',1);

add_action('init','aw2_apps_library::wp_init',2);
add_action('init', 'aw2_register',1);

function aw2_register() {
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
	) ); 
	  
			
		
}


add_action( 'admin_menu', 'aw2_apps_library::register_menus' );
add_action( 'cmb2_admin_init', 'aw2_apps_library::set_app_metabox' );


add_action( 'parse_request', 'aw2_apps_library::app_takeover' );
add_action('template_redirect', 'aw2_apps_library::template_redirect');

add_action('generate_rewrite_rules', 'aw2_apps_library::app_slug_rewrite');

require_once 'apps/app-settings.php';

class aw2_apps_library{
	static function load_apps(){
	
		$config_posts=get_posts('post_type=awesome_core&posts_per_page=-1&post_status=publish');
		foreach($config_posts as $config_post){
			
			aw2_library::parse_shortcode($config_post->post_content);

		}
		
		self::load_settings();
		
		if(!is_admin())
			return;
		
		//only run for admin backend
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		$app_posts=get_posts('post_type=aw2_app&posts_per_page=-1&post_status=publish');
		foreach($app_posts as $app_post){
			$app = new stdclass();

			$app->base_path=site_url().'/'.$app_post->post_name;
			$app->path=site_url().'/'.$app_post->post_name;
			$app->name=$app_post->post_title;
			$app->slug=$app_post->post_name;
			$app->post_id=$app_post->ID;
			$app->collection=array();
			
			$pages=get_post_meta($app_post->ID,'pages_collection',true);
			if($pages){
				$app->collection['pages']['post_type']=$pages;
			}
			
			$modules=get_post_meta($app_post->ID,'modules_collection',true);
			if($modules){
				$app->collection['modules']['post_type']=$modules;
			}

			$triggers=get_post_meta($app_post->ID,'triggers_collection',true);
			if($triggers){
				$app->collection['triggers']['post_type']=$triggers;
			}
			
			$app->config=get_post_meta($app_post->ID,'config',true);
			$ptr=&aw2_library::get_array_ref('');
			$ptr['app']=$app;
			aw2_library::parse_shortcode($app->config);

			$registered_apps[$app_post->post_name]=$ptr['app'];
		}
		
		
	}
	
	static function load_settings(){}
	
	static function wp_init(){
		self::register_app_cpts();
		self::run_setup();
		if(is_admin())
			return;
		
		//run init modules.
		self::frontend_init();
	}
	
	static function run_setup(){
		
	}	
	
	static function frontend_init(){
		
	}
	static function register_app_cpts(){
		// register APP CPT itself.
		
		register_post_type('aw2_app', array(
			'label' => 'Local Apps',
			'public' => false,
			'show_in_nav_menus'=>false,
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
				
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		foreach($registered_apps as $key => $app){
			foreach($app->collection as $collection_name => $collection){
				self::register_cpt($collection['post_type'],$collection_name,false,true);
			}
		}

		//aw2_setup_services(aw2_library::get_array_ref('services'));
		
	}

	static function register_menus(){
		add_menu_page('Services', 'Services - Awesome Studio', 'develop_for_awesomeui','awesome-services', 'edit.php?post_type=aw2_app','dashicons-admin-network',2 );
		
		
		$services=&aw2_library::get_array_ref('services');
		foreach($services as $key => $service){
			
			add_submenu_page('awesome-services', $service['label'], $service['label'],  'develop_for_awesomeui','edit.php?post_type='.$service['post_type']);
				
		}	
		
		
		add_submenu_page('awesome-studio', 'Apps - Awesome Studio', 'Apps', 'develop_for_awesomeui', 'edit.php?post_type=aw2_app' );
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach($registered_apps as $key => $app){
			add_menu_page($app->name, $app->name.' App', 'manage_options', 'awesome-app-'.$app->slug, 'aw2_apps_library::show_app_pages', 'dashicons-admin-multisite',3);
			
			foreach($app->collection as $collection_name => $collection){
				add_submenu_page('awesome-app-'.$app->slug, $app->name . ' ' . $collection_name, $collection_name,  'develop_for_awesomeui','edit.php?post_type='.$collection['post_type']);
			}
			add_submenu_page('awesome-app-'.$app->slug, $app->name . ' config', 'Config',  'develop_for_awesomeui','post.php?post=' . $app->post_id . '&action=edit');
				
		}	
	}
	
	static function show_app_pages(){
		echo '<div class="wrap ">';        	
		echo 'Not Yet Implemented';
		echo '</div>';		
	}
	
	static function app_takeover($query){
		if(empty($query->request))return;
		
		$pieces = explode('/',$query->request);
		$app_slug=$pieces[0];
		array_shift($pieces);
	
		$arr=array();
		$arr['status']='';
		if(aw2_library::get_post_from_slug($app_slug,'aw2_app',$post)){
			$app=new aw2_app();

			$arr=$app->setup($post);
		}
		else{
			//possible issue
			return;
		}		
			
		if($arr['status']=='error')return;
		$arr=$app->check_rights();
		

		if($arr['status']=='invalid_rights'){
			echo '<h3>You dont have rights to access this app</h3>';
			exit();
		}
		
		if($arr['status']=='error'){
			if(aw2_library::get_correct_post('login',$app->default_modules,$post)){
				echo aw2_library::run_module('login');
				exit();
			}
			
			$login_url=wp_login_url(site_url().'/'.$query->request);
			if(!empty(aw2_library::get('app.options.login_url'))){
			   $login_url=aw2_library::get('app.options.login_url');
			   $separator = (parse_url($login_url, PHP_URL_QUERY) == NULL) ? '?' : '&';
			   $login_url .= $separator.'redirect_to='.urlencode(site_url().'/'.$query->request);
			}
			wp_redirect( $login_url );
			exit();
		}
		
		
		
		//define the collections
		
		//aw2_library::parse_shortcode(init);
	
		$app->resolve_route($pieces,$query);		
	
	}
	
	static function template_redirect(){
		
		//single or archive
		if($action == 'single' ){
			aw2_library::set('app.content_overide',true);
			add_filter('the_content','aw2_app::takeover_the_content',1); 
		}
		
		if($action == 'archive'){
			// not sure how to override in generic wp themes. monomyth handles it.
		}
		
		//page
		if($action == 'page' || $action == '404'){
			//do nothing it will be handled by theme 
		}
	}
	
	//supporting functions
	static function do_not_include_template($template){
		return false;//do not include any thing
	}

	static function app_slug_rewrite($wp_rewrite) {
    	
		$rules = array();
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach($registered_apps as $key => $app){
			$rules[$app->slug . '/?$'] = 'index.php?pagename=home&post_type='.$app->collection['pages']['post_type'];
		}	
		
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;

	}
	static function set_app_metabox(){
		$app_meta_box = new_cmb2_box( array(
			'id'            => 'app_metabox',
			'title'         => 'App Defaults',
			'object_types'  => array( 'aw2_app' ), // Post type
			'context'    => 'normal',
			'priority'   => 'high',
			'show_names' => true
			) 
		);

				
		$app_meta_box->add_field( array(
			'name' => "Pages Collection",
			'desc' => "Pages Collection. Leave empty if you do not need the collection",
			'id'   => 'pages_collection',
			'type' => 'text'
		) );
		
		$app_meta_box->add_field( array(
			'name' => "Modules Collection",
			'desc' => "Modules Collection. Leave empty if you do not need the collection",
			'id'   => 'modules_collection',
			'type' => 'text'
		) );
		
		$app_meta_box->add_field( array(
			'name' => "Triggers Collection",
			'desc' => "Triggers Collection. Leave empty if you do not need the collection",
			'id'   => 'triggers_collection',
			'type' => 'text'
		) );
		
		$app_meta_box->add_field( array(
			'name' => "Config",
			'desc' => "Config",
			'id'   => 'config',
			'type' => 'textarea'
		) );

	}
	static function register_cpt($post_type,$name,$public,$hierarchical=false,$slug=null){
		
		if($slug==null)$slug=$post_type;
		
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
			'supports' => array('title','editor','revisions','thumbnail'),
			'labels' => array (
				  'name' => $name,
				  'singular_name' => rtrim($name,'s'),
				  'add_new_item' => 'Add New '.rtrim($name,'s'),
				  'edit_item' => 'Edit '.rtrim($name,'s'),
				  'new_item' => 'New '.rtrim($name,'s'),
				  'view_item' => 'View '.rtrim($name,'s'),
				  'search_items' => 'Search '.$name,
				  'not_found' => 'No '.$name.' Found',
				  'not_found_in_trash' => 'No '.$name.' Found in Trash',
				)
			) 
		);
	}

}

class aw2_app{
	public $lib;
	
	public function setup($post){
		$arr['status']='error';
		
		$this->base_path=site_url().'/'.$post->post_name;
		$this->path=site_url().'/'.$post->post_name;
		$this->slug=$post->post_name;
		$this->name=$post->post_title;
		$this->post_id=$post->ID;

		$this->collection=array();
		
		$pages=get_post_meta($post->ID,'pages_collection',true);
		if($pages)$this->collection['pages']['post_type']=$pages;
		
		$modules=get_post_meta($post->ID,'modules_collection',true);
		if($modules)$this->collection['modules']['post_type']=$modules;

		$triggers=get_post_meta($post->ID,'triggers_collection',true);
		if($triggers)$this->collection['triggers']['post_type']=$triggers;
		
		$this->config=get_post_meta($post->ID,'config',true);
		aw2_library::set('app',$this);				
		aw2_library::parse_shortcode($this->config);
		$arr['status']='success';
		return $arr;
	}

	public function setup_root(){
		echo 'inside root';
		$app_name='root';
		$app_slug='root';
		$arr['status']='error';

		$return=$this->setup($app_slug);
		
		if($return['status']=='success'){
			$arr['status']='success';
			return $arr;
		}
			
		$this->path=site_url().'/'.$app_name;
		$this->slug=$app_name;
		
		$this->options = cmb2_get_option( $app_name .'_options','all');
	
		$this->default_post_type='post';
		$this->default_taxonomy='category';
		$this->default_pages='page';
		$this->default_modules='root_module';
		$this->default_triggers='root_trigger';
		aw2_library::set('app',$this);				
		$arr['status']='success';
		return $arr;
		
	}
	
	public function check_rights(){
		$arr=array();
		$arr['status']='error';

		if(aw2_library::get_post_from_slug('check-rights',array($this->default_modules),$post)){
			return aw2_library::run_module('check-rights');
		}
		
		if(empty(aw2_library::get('app.options.members_only'))){
			$arr['status']='success';
			return $arr;
		}
		
		//You should be logged in
		if(!is_user_logged_in()){
			return $arr;
		}

		// The person is logged in

		if(current_user_can('administrator')){
			$arr['status']='success';
			return $arr;
			
		}
		
		if(empty(aw2_library::get('app.options.access_role'))){
			$arr['status']='success';
			return $arr;
		} 
		
		// We must match the role, check for multiple
		$roles=aw2_library::get('app.options.access_role');
		
		if(!current_user_can($roles)){
			$arr['status']='invalid_rights';
			return $arr;
		} 

		if(current_user_can($roles)){
			$arr['status']='success';
			return $arr;
		} 
		
		return $arr;
	}
	
	public function load_triggers(){
		$arr=array();
		$arr['status']='success';
		awesome2_trigger::load_app();
		
		return $arr;
	}
	
	static function takeover_the_content($content){
		
		//remove_filter('the_content','aw2_app::takeover_the_content',1);
		$action=aw2_library::get('app.action');
		if($action == 'single')
			aw2_library::get_post_from_slug('single',$post_type,$post);
		
		return $post->post_content;
	}
		
	public function resolve_route($pieces,$query){
		controllers::resolve_route($pieces,$query);
		/* 
		$o=new stdClass();
		$o->pieces=$pieces;
		$this->route=implode("/",$pieces);
	
		//Check if it home
		if(empty($o->pieces))
			$o->pieces=array('home');
		
		
		if(current_user_can("develop_for_awesomeui")){
			if(empty($this->action))
				$this->resolve_z($o);

			if(empty($this->action))
				$this->resolve_s($o);
		}
		
		if(empty($this->action))
			$this->resolve_ajax($o);

		
		if(empty($this->action))
			$this->resolve_ajax_data($o);
		
		if(empty($this->action))
			$this->resolve_modules($o);

		if(empty($this->action))
			$this->resolve_pages($o);

		if(empty($this->action))
			$this->resolve_data($o);
		
		if(empty($this->action))
			$this->resolve_css($o);

		if(empty($this->action))
			$this->resolve_js($o);		
		
		if(empty($this->action))
			$this->resolve_excel($o);

		if(empty($this->action))
			$this->resolve_file($o);
		
		if(empty($this->action)){
			
			unset($query->query_vars['name']);
			unset($query->query_vars['post_type']);
			unset($query->query_vars[$this->default_pages]);
			unset($query->query_vars[$this->default_post_type]);
			unset($query->query_vars[$this->default_taxonomy]);
			
			$this->resolve_leaf($o,$query);
		}


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
		}
 */
	}

}

class controllers{
	static $module;
	static $template;
	
	static function resolve_route($pieces,$query){
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
			call_user_func(array('controllers', 'controller_'.$controller),$o);
		}

		if($ajax != true){
			self::controller_pages($o, $query);
		}
		
		self::controller_modules($o);
		
		self:: controller_404();
	}
	
	static function controller_css($o){
		self::$module=array_shift($o->pieces);
		
		self::module_parts();
		
		self::set_qs($o);
		
		$result=aw2_library::run_module(self::module, self::template);
		
		header("Content-type: text/css");
		header("Cache-Control: max-age=31536000"); 
		echo $result;
		exit();	
	}	
	
	static function controller_js($o){
		self::$module=array_shift($o->pieces);
		
		self::module_parts();
		
		self::set_qs($o);
		
		$result=aw2_library::run_module(self::module, self::template);
		
		header("Content-type: application/javascript");
		header("Cache-Control: max-age=31536000"); 
		echo $result;
		exit();	
	}
	
	static function controller_file($o){
		self::$module=array_shift($o->pieces);
		self::set_qs($o);
		
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
			
		self::set_qs($o);
		
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
				'post_type' => $app->collection['modules']['post_type'],
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
				echo('<br><a target=_blank href="' . site_url("wp-admin/post-new.php?post_type=" . $$app->collection['modules']['post_type']) .'">Add New</a><br>');

		
		} else {
			aw2_library::get_post_from_slug(self::$module,$app->collection['modules']['post_type'],$post);
			header("Location: " . site_url("wp-admin/post.php?post=" . $post->ID  . "&action=edit"));
		}		
		exit();	
	}
	static function controller_s($o){
		if(!current_user_can("develop_for_awesomeui")) return;
		
		self::$module=array_shift($o->pieces);	
		$app=&aw2_library::get_array_ref('app');
		
		$post_type=$app->collection['modules']['post_type'];
		echo '<h3>Searching for:' . urldecode(self::$module) . '</h3>';
		$sql="Select * from wp_posts where post_status='publish' and post_content like '%" . urldecode(self::$module) . "%' and post_type='" . $post_type . "'";
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

		echo aw2_library::run_module('search-submit',null,null,[ticket=>self::$module],'manage_service');
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
		self::$module=array_shift($o->pieces);
		$pieces=explode('.',self::$module);
		self::set_qs($o);
		
		$token=$pieces[0];
		$nonce=$pieces[1];
		$filename=$_REQUEST['filename'];
		
		//verify that nonce is valid
		if(wp_create_nonce($token)!=$nonce){
			echo 'Error E1:The Data Submitted is not valid. Check with Administrator';
			exit();		
		}

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
		
		$key = self::$module.":data";	//set the key
		
		$redis = new Redis();
		$redis->connect('127.0.0.1', 6379);
		$database_number = 12;
		$redis->select($database_number);
		if($redis->exists($key))
			$result = $redis->zRange($key, 0, -1);
		
		$output=implode('',$result);
			
		echo $output;
		
		
		exit();	
	}
	
	static function controller_data($o){
		self::$module=array_shift($o->pieces);
		
		self::module_parts();
		
		self::set_qs($o);
		
		$result=aw2_library::run_module(self::module, self::template);
		echo json_encode($result);
		exit();	
	}
	
	static function controller_pages($o, $query){
		
		if(empty($o->pieces))return;
		
		$slug= $o->pieces[0];
		
		
		$app=&aw2_library::get_array_ref('app');
		$post_type = $app->collection['pages']['post_type'];
		
		if(aw2_library::get_post_from_slug($slug,$post_type,$post)){
			array_shift($o->pieces);
			self::set_qs($o);
			unset($query->query_vars['name']);
			unset($query->query_vars['attachment']);
			unset($query->query_vars['post_type']);
			
			$query->query_vars['post_type']=$post_type;
			$query->query_vars['pagename']=$slug;
			
			return;
		}
		
		$post_type = $app->collection['modules']['post_type'];

		if(aw2_library::get_post_from_slug($slug,$post_type,$post)){
			array_shift($o->pieces);
			self::set_qs($o);
			
			echo aw2_library::run_module('custom-layout',null,"",array("slug"=>$slug),$post_type);
			echo awesome2_footer();
			exit();
		}	
	}
	
	static function controller_modules($o){
		if(empty($o->pieces))return;
		
		self::$module=array_shift($o->pieces);
		$pieces=explode('.',self::$module);
		self::set_qs($o);
		
		$result=aw2_library::run_module(self::$module,self::$template);

		echo $result;
		exit();	
	}
	
	static function controller_404(){
		$app=&aw2_library::get_array_ref('app');
		$post_type = $app->collection['pages']['post_type'];
		
		if(aw2_library::get_post_from_slug('404-page',$post_type,$post)){
			array_shift($o->pieces);
			$this->action='404';
			
			$query->query_vars['post_type']=$post_type;
			$query->query_vars['pagename']='404-page';
			return;
		}	
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
