<?php

class awesome_auth{
	static function twofactor_login_authenticator($auth){
		//only with wordpress
		if(!IS_WP)return false;
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
		if(!IS_WP)return false;

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
		$result['ID']=isset($user->ID)? $user->ID : $user->user_email;
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
		//if(!IS_WP)return false;
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
				die('Authentication code is missing');
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

	
	static function wp_vsession($auth){

		$app = &aw2_library::get_array_ref('app'); 
		$name=$app['slug'].'_valid';

		if(isset($_COOKIE['wp_vsession'])){
			
			$vsession=\aw2\vsession\get(['id'=>'wp_vsession'],null,'');
			if(isset($vsession[$name]) && $vsession[$name] === 'yes'){
				$app['session']=$vsession;
				$app['user']=json_decode($vsession['user'],true);	
				return true;
			}
			
			if(isset($vsession['user'])){

				//check the status and roles are matching then allow the pass
				$user= json_decode($vsession['user'],true);		
				$app['auth']['status']= 'success';
				
				if(isset($auth['all_roles'])){
					$all_roles = explode(',',$auth['all_roles']);
					foreach($all_roles as $role){
						//if any of the role is missing then fail.
					
						if(!empty($role) && !in_array($role,$user['allcaps'])){
							$app['auth']['status']= 'error';
							break;
						}
					}
				}

				//if auth_data.status == success
				if(isset($app['auth']['status']) && $app['auth']['status'] == 'success'){
					//set app_valid=yes and return true
					$app['user']=$user;

					$atts['key']=$app['slug'].'_valid';
					$atts['value']='yes';
					$atts['ticket']=$vsession['ticket_id'];
					\aw2\vsession\set($atts,null,'');
					return true;
				}
			}		
		}
		
		$reply=aw2\vsession\create(['id'=>'wp_vsession'],'','');

		aw2\vsession\set(['id'=>'wp_vsession','key'=>$name, 'value'=>'no','ticket'=>$reply],'','');
		aw2\vsession\set(['id'=>'wp_vsession','key'=>'app_name', 'value'=>$app['slug'],'ticket'=>$reply],'','');
		return false;
	}

	static function wp_vession_login($user_login, $user){
		if(isset($_COOKIE['wp_vsession'])){
			$vsession=\aw2\vsession\get(['id'=>'wp_vsession'],null,'');
		
			$result=array();
			$result['login']=$user->user_login;
			$result['email']=$user->user_email;
			$result['display_name']=$user->display_name;
			$result['ID']=isset($user->ID)? $user->ID : $user->user_email;
			$result['roles']=$user->roles;
			$result['allcaps']=$user->allcaps;

			$args= [
				"ticket" =>$vsession['ticket_id'],
				"key" =>"user",
				"value" =>json_encode($result)
			];

			aw2\vsession\set($args,'','');
		}
	}
}

