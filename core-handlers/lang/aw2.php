<?php
namespace aw2;


if(IS_WP){
	add_shortcode('aw2.module', 'aw2\module');
	add_shortcode('aw2.this', 'aw2\this');
}


\aw2_library::add_service('aw2.module','Call a Module',['namespace'=>__NAMESPACE__]);

function module($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'slug' =>null,
		'module' =>null,
		'template'=>null,
		'post_type'=>null
	), $atts) );
	
	if(!$post_type){
		$handlers=\aw2_library::get_array_ref('handlers');
		if(!isset($handlers['modules']))return 'No Collection found';
		$collection=$handlers['modules'];
	} else {
		$collection = ["post_type"=>$post_type];
	}

	if($slug)$module=$slug;	
	$return_value=\aw2_library::module_run($collection,$module,$template,$content,$atts);	
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

\aw2_library::add_service('aw2.this','Set Module Parameters',['namespace'=>__NAMESPACE__]);

function this($atts,$content=null,$shortcode){

	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );

	if($main){
		\aw2_library::set('this.' . $main,null,$content,$atts);
	}
	unset($atts['main']);
	foreach ($atts as $loopkey => $loopvalue) {
		\aw2_library::set('this.' . $loopkey,$loopvalue,null,$atts);
	}
	return;
}	

\aw2_library::add_service('aw2.echo','Echo a Chain',['func'=>'_echo','namespace'=>__NAMESPACE__]);

function _echo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value=\aw2_library::get($main,$atts,$content);
	\util::var_dump($return_value);	
	return;
}


\aw2_library::add_service('aw2.set','Set a chain',['namespace'=>__NAMESPACE__]);
function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'overwrite'=>'yes',
	'default'=>'',
	'assume_empty' => null,
	'main'=>null
	), $atts) );
	
	unset($atts['assume_empty']);
	unset($atts['overwrite']);
	unset($atts['default']);
	unset($atts['main']);
	
	if($main){
		\aw2_library::set($main,null,$content,$atts);
	}	
	
	foreach ($atts as $loopkey => $loopvalue) {
		$newvalue=$loopvalue;
		if($loopvalue==$assume_empty)$newvalue='';
		if($loopvalue=='' || $loopvalue==null)$newvalue=$default;
		\aw2_library::set($loopkey,$newvalue,null,$atts);
	}
	return;
}

\aw2_library::add_service('aw2.set_array','Set an array',['namespace'=>__NAMESPACE__]);
function set_array($atts,$content=null,$shortcode){
		if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'with'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['with']);
	
	if(\aw2_library::endswith($main, '.new')){
		\aw2_library::set($main,null);	
		$path=substr($main, 0, -4);
		foreach ($atts as $loopkey => $loopvalue) {
			\aw2_library::set($path . '.last.' . $loopkey,$loopvalue);
		}
		if($content)
			\aw2_library::set($path . '.last.' . 'content',$content);
			
	}
	else{
		foreach ($atts as $loopkey => $loopvalue) {
			\aw2_library::set($main . '.' . $loopkey,$loopvalue);
		}
		if($content)
			\aw2_library::set($main . '.' . 'content',$content);

	}
	return;
}

\aw2_library::add_service('aw2.get','Get a variable',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	

	$return_value=\aw2_library::get($main,$atts,$content);
	
	if($return_value==='' || is_null($return_value))$return_value=$default;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))
		$return_value='Object';
	return $return_value;
}


\aw2_library::add_service('aw2.raw','Get a Raw Value. Will not be parsed',['namespace'=>__NAMESPACE__]);
function raw($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts) );
	

	$return_value=\aw2_library::get('raw',$atts,$content);
	
	if($return_value==='')
		$return_value=$default;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))
		$return_value='Object';
	return $return_value;
}


\aw2_library::add_service('aw2.die','Echo a chain and die',['namespace'=>__NAMESPACE__]);
function awesome2_die($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	if($content)echo \aw2_library::parse_shortcode($content);
	die();
}

\aw2_library::add_service('aw2.switch','Initiate a switch case',['func'=>'_switch','namespace'=>__NAMESPACE__]);
function _switch($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	$stack_id=\aw2_library::push_child('switch','switch');
	$call_stack=&\aw2_library::get_array_ref('call_stack',$stack_id);
	$call_stack['status']=true;	
	$return_value=\aw2_library::parse_shortcode($content);
	\aw2_library::pop_child($stack_id);
	return \aw2_library::post_actions('all',$return_value,$atts);
}

\aw2_library::add_service('aw2.case','Conditional check of the case',['func'=>'_case','namespace'=>__NAMESPACE__]);
function _case($atts,$content=null,$shortcode){
	$cond=\aw2_library::pre_actions('all',$atts,$content,$shortcode);

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );

	if($main){
		$check=\aw2_library::get($main);
		if($check==false)
		$cond=false;
	}

	$stack_id=\aw2_library::last_child('switch');
	$call_stack=&\aw2_library::get_array_ref('call_stack',$stack_id);
	$status=$call_stack['status'];
	
	if(is_null($status)){
		\aw2_library::set_error('Case without Switch');
		return;
	}
	$return_value='';
	if($cond==true && $status==true){
		$call_stack['status']=false;
		$return_value= \aw2_library::parse_shortcode($content);
	}
	
	return \aw2_library::post_actions('all',$return_value,$atts);
}

\aw2_library::add_service('aw2.case_else','Default case',['namespace'=>__NAMESPACE__]);
function case_else($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	$return_value='';
	$stack_id=\aw2_library::last_child('switch');
	$call_stack=&\aw2_library::get_array_ref('call_stack',$stack_id);
	$status=$call_stack['status'];
	
	if(is_null($status)){
		\aw2_library::set_error('Case without Switch');
		return;
	}
	
	if($status){
		$call_stack['status']=false;
		$return_value= \aw2_library::parse_shortcode($content);
	}
	return \aw2_library::post_actions('all',$return_value,$atts);
}


\aw2_library::add_service('aw2.save_form','Save Form',['namespace'=>__NAMESPACE__]);
function save_form($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'tag' => '',
		'set_post_id'=>''
	), $atts) );
		
    $post=new \stdClass();
    foreach($_REQUEST as $key => $value){
		$parts = explode('|', $key);
		if(count($parts)==3 && $parts[0]==$tag){

			$fieldtype = $parts[1];
			$fieldname = $parts[2];
			if($fieldtype=='post'){
				$post->$fieldname=stripslashes_deep($value);
			}
		}	
	}
	
	$args=\aw2_library::get_clean_args($content,$atts);
	if($args!=''){
		foreach ($args as $key => $value) {
			$post->$key=$value;
		}
	}
	
	if(property_exists($post,'ID') && $post->ID !='')
		$postid=wp_update_post($post);
	else
		$postid=wp_insert_post($post);
	
	\aw2_library::set($set_post_id,$postid);
	
    foreach($_REQUEST as $key => $value){
            $parts = explode('|', $key);
			if(count($parts)==3 && $parts[0]==$tag){
				$fieldtype = $parts[1];
				$fieldname = $parts[2];
				if($fieldtype=='meta'){
                    update_post_meta($postid, $fieldname, rawurldecode(stripslashes_deep($value)));
				}
			}	
		}
	
    foreach($_REQUEST as $key => $value){
		$parts = explode('|', $key);
		if(count($parts)==3 && $parts[0]==$tag){
			$fieldtype = $parts[1];
			$fieldname = $parts[2];
			if($fieldtype=='taxonomy'){
				$terms=explode(",", $value);
				
				wp_set_object_terms( $postid, $terms, $fieldname);
			}
		}	
	}
	
	$return_value=$postid;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('aw2.destroy_sessions','Destroy Sessions',['namespace'=>__NAMESPACE__]);


function destroy_sessions($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
	), $atts) );
	
	global $wp_session;
	$user_id = get_current_user_id();
	$session = wp_get_session_token();
	$sessions = WP_Session_Tokens::get_instance($user_id);
	if($main=='all')
		$sessions->destroy_all();
	else
	$sessions->destroy_others($session);
}