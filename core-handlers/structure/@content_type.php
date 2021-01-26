<?php
namespace aw2\active_content_type;


\aw2_library::add_service('@content_type','Runs Templates or gets data',['namespace'=>__NAMESPACE__]);
function unhandled($atts,$content=null,$shortcode){
	extract(\aw2_library::shortcode_atts( array(
	'default'=>''
	), $atts) );
	

	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];
	\aw2_library::load_content_type($content_type);
 	$main=implode('.',$shortcode['tags_left']);
	$return_value=\aw2_library::get('content_types.' . $content_type . '.' . $main,$atts,$content);


	if(is_object($return_value) && get_class($return_value)==='aw2_template'){
		if($return_value->code!==AW2_ERROR){
			$stack_id=\aw2_library::push_child('@content_type',$content_type);
			\aw2_library::set('@content_type.content_type',$content_type);

			$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
			$restore=$sc_exec;	
			if(isset($return_value->content_pos)){
				$sc_exec['start_pos']=$return_value->content_pos;
				$sc_exec['collection']=$return_value->collection;
				$sc_exec['module']=$return_value->module;
			}


			$template_id=\aw2_library::push_child('@template',$return_value->name);

			foreach ($atts as $key => $value) {
					if(strpos($key,'@shared.')!==0)
						$key='@template.' . $key;	
					
					\aw2_library::set($key,$value);
			}

			$return_value=\aw2_library::parse_shortcode($return_value->code);
			
			$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
			$sc_exec=$restore;
			
			if(isset(\aw2_library::$stack['@template']['_return'])){
				unset(\aw2_library::$stack['_return']);
				$return_value=\aw2_library::$stack['@template']['_return'];
			}
			\aw2_library::pop_child($stack_id);

			if(is_string($return_value))$return_value=trim($return_value);
 		}
	}	

	if($return_value==='')$return_value=$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('@content_type.cnt','Counts a content type path',['namespace'=>__NAMESPACE__]);
function cnt($atts,$content=null,$shortcode){
	extract(\aw2_library::shortcode_atts( array(
	'main'=>''
	), $atts) );
	

	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];
	\aw2_library::load_content_type($content_type);
	
	$arr=\aw2_library::get('content_types.' . $content_type . '.' . $main);

	$return_value=count($arr);	


	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('@content_type.run','Runs a content type template',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode){
	extract(\aw2_library::shortcode_atts( array(
	'main'=>''
	), $atts) );
	

	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];
	\aw2_library::load_content_type($content_type);
	
	$return_value=\aw2_library::get('content_types.' . $content_type . '.' . $main);


	if(is_object($return_value) && get_class($return_value)==='aw2_template'){
		if($return_value->code!==AW2_ERROR){
			$stack_id=\aw2_library::push_child('@content_type',$content_type);
			\aw2_library::set('@content_type.content_type',$content_type);

			$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
			$restore=$sc_exec;	
			if(isset($return_value->content_pos)){
				$sc_exec['start_pos']=$return_value->content_pos;
				$sc_exec['collection']=$return_value->collection;
				$sc_exec['module']=$return_value->module;
			}

			$template_id=\aw2_library::push_child('@template',$return_value->name);

			foreach ($atts as $key => $value) {
					if(strpos($key,'@shared.')!==0)
						$key='@template.' . $key;	
					
					\aw2_library::set($key,$value);
			}

			$return_value=\aw2_library::parse_shortcode($return_value->code);
			
			$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
			$sc_exec=$restore;
	
			if(isset(\aw2_library::$stack['@template']['_return'])){
				unset(\aw2_library::$stack['_return']);
				$return_value=\aw2_library::$stack['@template']['_return'];
			}
			\aw2_library::pop_child($stack_id);

			if(is_string($return_value))$return_value=trim($return_value);
 		}
	}	

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('@content_type.get','Get an Environment Value',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];
	\aw2_library::load_content_type($content_type);
	
	$return_value=\aw2_library::get('content_types.' . $content_type . '.' . $main,$atts,$content);

	if($return_value==='')$return_value=$default;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



\aw2_library::add_service('@content_type.meta','Set a Meta Value',['namespace'=>__NAMESPACE__]);
function meta($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];
	\aw2_library::load_content_type($content_type);

	if(isset($shortcode['tags_left'][0])){
		
			$return_value=meta_get($content_type,$shortcode['tags_left'],$atts,$content);
			
	}
	else{
		//nothing was set
		$return_value=\aw2_library::get('content_types.' . $content_type . '.meta');
	}	

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
}



function meta_get($content_type,$arr,$atts,$content){
	$main=implode('.',$arr);
	$return_value=\aw2_library::get('content_types.' . $content_type . '.meta.' . $main,$atts,$content);
	return $return_value;
}



\aw2_library::add_service('@content_type.config','Get a config Value',['namespace'=>__NAMESPACE__]);
function config($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];	
	\aw2_library::load_content_type($content_type);

	if(isset($shortcode['tags_left'][0])){
		
			$return_value=config_get($content_type,$shortcode['tags_left'],$atts,$content);
			
	}
	else{
		//nothing was set
		$return_value=\aw2_library::get('content_types.' . $content_type . '.config');
	}	

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
}



function config_get($content_type,$arr,$atts,$content){

	$main=implode('.',$arr);
	$return_value=\aw2_library::get('content_types.' . $content_type . '.config.' . $main,$atts,$content);
	
	return $return_value;
}



\aw2_library::add_service('@content_type.dump','Dump a content type',['namespace'=>__NAMESPACE__]);

function dump($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts, 'dump' ) );

	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];
	\aw2_library::load_content_type($content_type);
	
	$_prefix='content_types.' . $content_type;
	
	if($main)
		$main= $_prefix . '.' . $main;
	else
		$main= $_prefix ;
		
	$return_value=\aw2_library::get($main);

	$return_value=\util::var_dump($return_value,true);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
}

\aw2_library::add_service('@content_type.echo','Echo a content type',['func'=>'_echo' ,'namespace'=>__NAMESPACE__]);

function _echo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts, 'dump' ) );

	$root=\aw2_library::get_array_ref();
	if(!isset($root['@content_type']))return;	
	$content_type=$root['@content_type']['name'];
	\aw2_library::load_content_type($content_type);
	
	$_prefix='content_types.' . $content_type;
	
	if($main)
		$main= $_prefix . '.' . $main;
	else
		$main= $_prefix ;

	$return_value=\aw2_library::get($main);
	\util::var_dump($return_value);
}

