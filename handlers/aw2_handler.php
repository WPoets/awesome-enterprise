<?php

add_shortcode('aw2.module', 'awesome2_module');
aw2_library::add_shortcode('aw2','module', 'awesome2_module','Call a Module');
function awesome2_module($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
 	extract( shortcode_atts( array(
		'slug' =>null,
		'module' =>null,
		'template'=>null,
		'post_type'=>null
	), $atts) );
	
	if(!$post_type){
		$handlers=aw2_library::get_array_ref('handlers');
		if(!$handlers['modules'])return 'No Collection found';
		$post_type=$handlers['modules']['post_type'];
	}
	if($slug)$module=$slug;	
	$return_value=aw2_library::module_run(["post_type"=>$post_type],$module,$template,$content,$atts);	
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	return $return_value;
}

add_shortcode('aw2.this', 'awesome2_this');
aw2_library::add_shortcode('aw2','this', 'awesome2_this','Set Module Parameters');
function awesome2_this($atts,$content=null,$shortcode){

	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );

	if($main){
		aw2_library::set('this.' . $main,null,$content,$atts);
	}
	unset($atts['main']);
	foreach ($atts as $loopkey => $loopvalue) {
		aw2_library::set('this.' . $loopkey,$loopvalue,null,$atts);
	}
	return;
}	



aw2_library::add_shortcode('aw2','echo', 'awesome2_echo','Echo Something');
function awesome2_echo($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value=aw2_library::get($main,$atts,$content);
	util::var_dump($return_value);	
	return;
}


aw2_library::add_shortcode('aw2','set', 'awesome2_set','Set a Variable');
function awesome2_set($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
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
		aw2_library::set($main,null,$content,$atts);
	}	
	
	foreach ($atts as $loopkey => $loopvalue) {
		$newvalue=$loopvalue;
		if($loopvalue==$assume_empty)$newvalue='';
		if($loopvalue=='' || $loopvalue==null)$newvalue=$default;
		aw2_library::set($loopkey,$newvalue,null,$atts);
	}
	return;
}

aw2_library::add_shortcode('aw2','set_array', 'awesome2_set_array','Set elements of an Array');
function awesome2_set_array($atts,$content=null,$shortcode){
		if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'with'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['with']);
	
	if(aw2_library::endswith($main, '.new')){
		aw2_library::set($main,null);	
		$path=substr($main, 0, -4);
		foreach ($atts as $loopkey => $loopvalue) {
			aw2_library::set($path . '.last.' . $loopkey,$loopvalue);
		}
		if($content)
			aw2_library::set($path . '.last.' . 'content',$content);
			
	}
	else{
		foreach ($atts as $loopkey => $loopvalue) {
			aw2_library::set($main . '.' . $loopkey,$loopvalue);
		}
		if($content)
			aw2_library::set($main . '.' . 'content',$content);

	}
	return;
}


aw2_library::add_shortcode('aw2','get', 'awesome2_get');
function awesome2_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	

	$return_value=aw2_library::get($main,$atts,$content);
	
	if($return_value==='')$return_value=$default;
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))
		$return_value='Object';
	return $return_value;
}



aw2_library::add_shortcode('aw2','raw', 'awesome2_raw');
function awesome2_raw($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts) );
	

	$return_value=aw2_library::get('raw',$atts,$content);
	
	if($return_value==='')
		$return_value=$default;
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))
		$return_value='Object';
	return $return_value;
}



aw2_library::add_shortcode('aw2','die', 'awesome2_die','Die');
function awesome2_die($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	if($content)echo aw2_library::parse_shortcode($content);
	die();
}


aw2_library::add_shortcode('aw2','switch', 'awesome2_switch');
function awesome2_switch($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	$stack_id=aw2_library::push_child('switch','switch');
	$call_stack=&aw2_library::get_array_ref('call_stack',$stack_id);
	$call_stack['status']=true;	
	$return_value=aw2_library::parse_shortcode($content);
	aw2_library::pop_child($stack_id);
	return aw2_library::post_actions('all',$return_value,$atts);
}

aw2_library::add_shortcode('aw2','case', 'awesome2_case');
function awesome2_case($atts,$content=null,$shortcode){
	$cond=aw2_library::pre_actions('all',$atts,$content,$shortcode);

	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );

	if($main){
		$check=aw2_library::get($main);
		if($check==false)
		$cond=false;
	}

	$stack_id=aw2_library::last_child('switch');
	$call_stack=&aw2_library::get_array_ref('call_stack',$stack_id);
	$status=$call_stack['status'];
	
	if(is_null($status)){
		aw2_library::set_error('Case without Switch');
		return;
	}
	$return_value='';
	if($cond==true && $status==true){
		$call_stack['status']=false;
		$return_value= aw2_library::parse_shortcode($content);
	}
	
	return aw2_library::post_actions('all',$return_value,$atts);
}

aw2_library::add_shortcode('aw2','case_else', 'awesome2_case_else');
function awesome2_case_else($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	$return_value='';
	$stack_id=aw2_library::last_child('switch');
	$call_stack=&aw2_library::get_array_ref('call_stack',$stack_id);
	$status=$call_stack['status'];
	
	if(is_null($status)){
		aw2_library::set_error('Case without Switch');
		return;
	}
	
	if($status){
		$call_stack['status']=false;
		$return_value= aw2_library::parse_shortcode($content);
	}
	return aw2_library::post_actions('all',$return_value,$atts);
}


