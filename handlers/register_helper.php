<?php

namespace aw2\register;

\aw2_library::add_service('register','Handles the registration of ctp, less variables etc.',['namespace'=>__NAMESPACE__]);


function aw2_register_unhandled($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;

	$pieces=$shortcode['tags'];

	if(count($pieces)!=2)return 'error:You must have exactly two parts to the query shortcode';
	
	$register=new awesome2_register($pieces[1],$atts,$content);
	if($register->status==false){
		return aw2_library::get('errors');
	}
	$return_value =$register->run();
		
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}
/* 
aw2_library::add_shortcode('aw2','register', 'awesome2_register','Register various wordpress objects');


function awesome2_register($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
		'main'=>null
		), $atts) );

	$register=new awesome2_register($main,$atts,$content);
	if($register->status==false){
		return aw2_library::get('errors');
	}
	
	$return_value =$register->run();
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	
	return $return_value;
}
 */
class awesome2_register{
	public $action=null;
	public $atts=null;
	public $content=null;
	public $status=false;
	
	function __construct($action,$atts,$content=null){
     if (method_exists($this, $action)){
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
		$this->status=true;
	 }
	}
	function run(){
     if (method_exists($this, $this->action))
		return call_user_func(array($this, $this->action));
     else
		aw2_library::set_error('Register Method does not exist'); 
	}
	
	function att($el,$default=null){
		if(array_key_exists($el,$this->atts))
			return $this->atts[$el];
		return $default;
	}

	function args(){
		if($this->content==null || $this->content==''){
			$return_value=array();	
		}
		else{
			$json=aw2_library::clean_specialchars($this->content);
			$json=aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				aw2_library::set_error('Invalid JSON' . $this->content); 
				$return_value=array();	
			}
		}

		$arg_list = func_get_args();
		foreach($arg_list as $arg){
			if(array_key_exists($arg,$this->atts))
				$return_value[$arg]=$this->atts[$arg];
		}
			return $return_value;
	}
	
	function widget(){
		$widgets=&aw2_library::get_array_ref('widgets');
		$new=array();
		$new['id']=$this->att('id');
		$new['name']=$this->att('name');
		$new['description']=$this->att('description');
		$widgets[]=$new;
	}
	
	function post(){
		register_post_type($this->att('slug'),$this->args());
		return;
	}
	
	function taxonomy(){
		register_taxonomy( $this->att('slug'), $this->att('post_type'), $this->args() );
		return;
	}
	
	function sidebar(){
		register_sidebar($this->args());
		return;
	}
	
	function menu_location(){
		register_nav_menus($this->args());
		return;
	}
	
		
	function custom_metabox(){
		
		$custom_meta_boxes=&aw2_library::get_array_ref('custom_meta_boxes');
		
		$args = $this->args();
		$id = $args['id'];
		$custom_meta_boxes[$id]=$args;
		
	}
	
	function less_variables(){
		
		$less_variables=aw2_library::get('less_variables');
		
		$args = aw2_library::parse_shortcode($this->content);
		$less_variables = $less_variables .' '.$args;
		
		aw2_library::set('less_variables',$less_variables );		
		
	}
	
	function rewrite_rule(){
		$args=$this->args();
		add_rewrite_rule($args['regex'], $args['redirect'], $args['after']);
		
		return;
	}

	function query_vars(){
		
		$query_vars=&aw2_library::get_array_ref('query_vars_array');
		$query_vars[]=$this->att('var');
	}
	
	function user(){
		
		$args=$this->args();
		$return_value= 'no';
		
		if(!wp_verify_nonce($_REQUEST['aw2_register_nonce'], 'register-nonce'))
			return $return_value;
		
		// this is required for username checks
		require_once(ABSPATH . WPINC . '/registration.php');
		
		$user=array();
		foreach($_REQUEST as $key => $value){
			$parts = explode('|', $key);
			if(count($parts)==2 && $parts[0]=='user'){
				$fieldname = $parts[1];
				$user[$fieldname]=stripslashes_deep($value);
			}
			
			if(count($parts)==3 && $parts[0]=='user' && $parts[1]=='meta'){
				
				$fieldname = $parts[2];
				$user['meta'][$fieldname]=stripslashes_deep($value);
			}	
		}
		
		
		$user_args= array_merge($user,$args);
		unset($args);
		
		$user = new stdClass();
		if(!isset($user_args['user_login'])){
			aw2_library::set_error('user_login not set'); 
			return $return_value;
		}
			
		
		if(empty($user_args['user_login'])){
			aw2_library::set_error('user_login not empty'); 
			return $return_value;
		}
			
		if(username_exists($user_args['user_login'])){
			aw2_library::set_error('user_login already exists'); 
			return $return_value;
		}
		
		if(!validate_username($user_args['user_login'])){
			aw2_library::set_error('user_login not set'); 
			return $return_value;
		}
		
		$user->user_login = sanitize_user($user_args['user_login']);
		
		//making email optional for user registration.. special case
		if(isset($user_args['user_email'])){
			if(email_exists($user_args['user_email'])){
				aw2_library::set_error('user_email already exists'); 
				return $return_value;
			}
		   
			if(!is_email($user_args['user_email'])){
				aw2_library::set_error('user_email is invalid email'); 
				return $return_value;
			}
			
			$user->user_email = sanitize_email($user_args['user_email']);
		}
		
		if(isset($user_args['user_pass'])){
			if(empty($user_args['user_pass'])){
				aw2_library::set_error('user_pass is empty'); 
				return $return_value;
			}
			
			if(isset($user_args['user_pass_confirm']) && ($user_args['user_pass']!=$user_args['user_pass_confirm'])){
				aw2_library::set_error('user_pass & user_pass_confirm are different'); 
				return $return_value;
			}
			
			$user->user_pass = esc_attr($user_args['user_pass']);
		}
		
		if(isset($user_args['user_role'])){
			$user->role =$user_args['user_role'];
		}
		
		if(isset($user_args['user_url'])){
			$user->user_url =esc_url_raw($user_args['user_url']);
		}
		
		if(!isset($user_args['flow']) || empty($user_args['flow']))
			$user_args['flow'] ='default';
		
		$user->user_registered = date('Y-m-d H:i:s');
		//since username is fine, and email and password are fine let us create the super user.
		
		$new_user_id = wp_insert_user($user);
		
		
		if(is_wp_error($new_user_id )){
			aw2_library::set_error('Error Creating User : '.$new_user_id->get_error_message()); 
			return $return_value;
		}
		if(isset($user_args['meta'])){
			foreach($user_args['meta'] as $k => $v){
				add_user_meta( $new_user_id, $k,  sanitize_text_field($v), true  );
			}
		}
		
		//if(isset($user_args['flow']) && )
		wp_send_new_user_notifications( $new_user_id, $notify = 'both' );	
		$return_value= 'yes';

		return $return_value;
	}

}