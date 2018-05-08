<?php

namespace aw2\int;

\aw2_library::add_service('int','Integer Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('int.get','Returns value as an Integer',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>0
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(int)$return_value;	
	
	if($return_value===0)$return_value=(int)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('int.create','Create & Return value as an Integer',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(int)$main;	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

namespace aw2\str;

\aw2_library::add_service('str','String Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('str.get','Returns value as a String',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(string)$return_value;	
	
	if($return_value==='')$return_value=(string)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('str.create','Create & return value as a String',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(string)$main;	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

namespace aw2\num;

\aw2_library::add_service('num','String Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('num.get','Returns value as a Float',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>0.00
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(float)$return_value;	
	
	if($return_value===0.00)$return_value=(float)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('num.create','Create & return value as a Float',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(float)$main;	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

namespace aw2\bool;

\aw2_library::add_service('bool','Boolean Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('bool.get','Returns value as a Boolean',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>false
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=(bool)$return_value;	
	if($return_value===false)$return_value=(bool)$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('bool.create','Create & return value as a Boolean',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	if($main==='true')
		$return_value=true;
	else{
		if($main==='false'){
			$return_value=false;
		}		
		else{
			$return_value=(bool)$main;
		} 
	}
		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

namespace aw2\date;

\aw2_library::add_service('date','Date Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('date.get','Returns DateTime',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>null
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=new \DateTime($return_value);	
	if(!$return_value && $default!==null)
		$return_value==new \DateTime($default);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('date.create','Create & return DateTime',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=new \DateTime($main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

namespace aw2\arr;

\aw2_library::add_service('arr','Array Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('arr.set','Set a value in an array',['namespace'=>__NAMESPACE__]);
function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	unset($atts['main']);
	
	foreach ($atts as $loopkey => $loopvalue) {
		$arr[$loopkey]=$loopvalue;
	}	
	\aw2_library::set($main,$arr);
	return;
}

\aw2_library::add_service('arr.create','Build an array',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	$ab=new \array_builder();
	$return_value=$ab->parse($content);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

