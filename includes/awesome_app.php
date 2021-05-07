<?php

class awesome_app{
	
	public function exists($slug){
		$registered_apps=&aw2_library::get_array_ref('apps');
		$return_val = isset($registered_apps[$slug]);
		
		return $return_val;
	}
	
		
	public function setup($slug){
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		$this->base_path=SITE_URL.'/'.$slug;
		$this->path=SITE_URL.'/'.$slug;
		
		if($slug=='root'){
			$this->base_path=SITE_URL;
			$this->path=SITE_URL;
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
		//vikas:: ticket must create a user
		if(IS_WP && is_user_logged_in()){
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
	
	public function check_rights_old($query){
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
		   $login_url=SITE_URL.'/'. $rights['access']['unlogged'];
		}
		
		$separator = (parse_url($login_url, PHP_URL_QUERY) == NULL) ? '?' : '&';
		$login_url .= $separator.'redirect_to='.urlencode(SITE_URL.'/'.$query->request);
		
		if(isset($rights['access']['title'])){
			$login_url .= '&title='. urlencode($rights['access']['title']);
		}
		
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		wp_redirect( $login_url );
		exit();
		
		
	}
	

	public function check_rights($request){		//any changes to this function or related to this function should reflect in the if.user_can_access service
		if(IS_WP && current_user_can('administrator'))return;
		
		if(isset($this->configs['rights'])){
			
			aw2_library::parse_shortcode($this->configs['rights']['code']);
			
			$rights =&aw2_library::get_array_ref('app','rights');
			
			if(!isset($rights['access']) || strtolower($rights['access']['mode']) === 'public')return;
			
			if(strtolower($rights['access']['mode']) === 'private'){
				die('Access to this app is private.');
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
			$login_url .= $separator.'redirect_to='.urlencode(site_url().'/'.$request);
			
			if(isset($rights['access']['title'])){
				$login_url .= '&title='. urlencode($rights['access']['title']);
			}
			
			header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
			wp_redirect( $login_url );
			exit();
		
		}else{
			$options = aw2_library::get_option('awesome-app-' . $this->slug);
			if(!is_array($options) || ('1' != $options['enable_rights'])) return true;
			
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
			$login_url .= $separator.'redirect_to='.urlencode(site_url().'/'.$request);
			
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