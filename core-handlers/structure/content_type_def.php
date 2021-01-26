<?php
namespace aw2\content_type_def;


\aw2_library::add_service('content_type_def','Runs Templates or gets data',['namespace'=>__NAMESPACE__]);
function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;	
	extract(\aw2_library::shortcode_atts( array(
	'default'=>''
	), $atts) );
	
	
	$content_type=$shortcode['content_type']['content_type'];	
	\aw2_library::load_content_type($content_type);
	
	$main=implode('.',$shortcode['tags_left']);
	$return_value=\aw2_library::get('content_types.' . $content_type . '.' . $main,$atts,$content);


	if(is_object($return_value) && get_class($return_value)==='aw2_template'){
		if($return_value->code!==AW2_ERROR){
			$stack_id=\aw2_library::push_child('@content_type',$content_type);
			\aw2_library::set('@content_type.content_type',$content_type);
			$shared_id=\aw2_library::push_child('@shared',$content_type);
			\aw2_library::set('@shared.content_type',$content_type);

			$template_id=\aw2_library::push_child('@template',$return_value->name);

			$sc_exec=&\aw2_library::get_array_ref('@sc_exec');
			$restore=$sc_exec;	
			if(isset($return_value->content_pos)){
				$sc_exec['start_pos']=$return_value->content_pos;
				$sc_exec['collection']=$return_value->collection;
				$sc_exec['module']=$return_value->module;
			}
			
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
			//if(is_object($return_value))$return_value='Object';
		}
	}	

	if($return_value==='')$return_value=$default;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('content_type_def.run','Run a template',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>''
	), $atts) );
	
	
	$content_type=$shortcode['content_type']['content_type'];	
	\aw2_library::load_content_type($content_type);

	$return_value=\aw2_library::get('content_types.' . $content_type . '.' . $main,$atts,$content);


	if(is_object($return_value) && get_class($return_value)==='aw2_template'){
		if($return_value->code!==AW2_ERROR){
			$stack_id=\aw2_library::push_child('@content_type',$content_type);
			\aw2_library::set('@content_type.content_type',$content_type);
			$shared_id=\aw2_library::push_child('@shared',$content_type);
			\aw2_library::set('@shared.content_type',$content_type);

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
	else
		$return_value='Template not found::' . $main ;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('content_type_def.cnt','Counts a content type path',['namespace'=>__NAMESPACE__]);

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



\aw2_library::add_service('content_type_def.template','Add a Template to a content type',['namespace'=>__NAMESPACE__]);
function template($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );

	$content_type=$shortcode['content_type']['content_type'];	
	\aw2_library::load_content_type($content_type);
	
	if($main){
		$obj=new \aw2_template();
		$obj->code=$content;
		$obj->name=$main;
		
		
		$sc_exec=\aw2_library::get_array_ref('@sc_exec');
		if(isset($sc_exec['content_pos'])){
			$obj->collection=$sc_exec['collection'];
			$obj->module=$sc_exec['module'];
			$obj->content_pos=$sc_exec['content_pos'];
		}
	
		\aw2_library::set('content_types.' . $content_type . '.' . $main,$obj);		
	}

	return;
}


\aw2_library::add_service('content_type_def.get','Get an Environment Value',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );

	$content_type=$shortcode['content_type']['content_type'];	
	\aw2_library::load_content_type($content_type);

	$return_value=\aw2_library::get('content_types.' . $content_type . '.' . $main,$atts,$content);

	if($return_value==='')$return_value=$default;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('content_type_def.meta','Set a Meta Value',['namespace'=>__NAMESPACE__]);
function meta($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$content_type=$shortcode['content_type']['content_type'];	
	\aw2_library::load_content_type($content_type);

	array_shift($shortcode['tags_left']);

	
	if(isset($shortcode['tags_left'][0])){
		
		$action=$shortcode['tags_left'][0];

		if($action==='set_array')
			$return_value=meta_set_array($content_type,$content);
		else
			$return_value=meta_get($content_type,$shortcode['tags_left'],$atts,$content);
			
	}
	else{
		//nothing was set
		$return_value=\aw2_library::get('content_types.' . $content_type . '.meta');
	}	

/*
	if($main){
		$obj=new \ct();
		if(isset($arr['help']))$obj->help=$arr['help'];
		if(isset($arr['prop']))$obj->prop=$arr['prop'];
		
		$main=$_prefix . '.' . $main;
		set_helper($main,$obj,$content_type);
	}
*/
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
}


function meta_set_array($content_type,$content){
	$ref=\aw2_library::get_array_ref('content_types',$content_type,'meta');	

	$ab=new \array_builder();
	$arr=$ab->parse($content);
	
	$final=array_merge($ref,$arr);
	\aw2_library::set('content_types.' . $content_type . '.meta',$final);

	return;
}


function meta_get($content_type,$arr,$atts,$content){
	//\util::var_dump($arr); 
	$main=implode('.',$arr);
	$return_value=\aw2_library::get('content_types.' . $content_type . '.meta.' . $main,$atts,$content);
	
	return $return_value;
}



\aw2_library::add_service('content_type_def.config','Set a Config Value',['namespace'=>__NAMESPACE__]);
function config($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$content_type=$shortcode['content_type']['content_type'];	
	\aw2_library::load_content_type($content_type);

	array_shift($shortcode['tags_left']);

	
	if(isset($shortcode['tags_left'][0])){
		
		$action=$shortcode['tags_left'][0];

		if($action==='set_array')
			$return_value=config_set_array($content_type,$main,$content);
		else
			$return_value=config_get($content_type,$shortcode['tags_left'],$atts,$content);
			
	}
	else{
		//nothing was set
		$return_value=\aw2_library::get('content_types.' . $content_type . '.config');
	}	

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
}


function config_set_array($content_type,$main,$content){

	$ab=new \array_builder();
	$arr=$ab->parse($content);
	
	if($main){
		\aw2_library::set('content_types.' . $content_type . '.config' . '.' . $main ,$arr);
	}	
	else{
		$ref=\aw2_library::get_array_ref('content_types',$content_type,'config');	
		$final=array_merge($ref,$arr);
		\aw2_library::set('content_types.' . $content_type . '.config',$final);
		
	}

	return;
}


function config_get($content_type,$arr,$atts,$content){
	$main=implode('.',$arr);
	$return_value=\aw2_library::get('content_types.' . $content_type . '.config.' . $main,$atts,$content);
	
	return $return_value;
}




\aw2_library::add_service('content_type_def.dump','Dump a content type',['namespace'=>__NAMESPACE__]);

function dump($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts, 'dump' ) );

	$content_type=$shortcode['content_type']['content_type'];	
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

\aw2_library::add_service('content_type_def.echo','Echo a content type',['func'=>'_echo' ,'namespace'=>__NAMESPACE__]);

function _echo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts, 'dump' ) );

	$content_type=$shortcode['content_type']['content_type'];	
	\aw2_library::load_content_type($content_type);

	$_prefix='content_types.' . $content_type;
	
	if($main)
		$main= $_prefix . '.' . $main;
	else
		$main= $_prefix ;

	$return_value=\aw2_library::get($main);
	\util::var_dump($return_value);
}

/*
function get_helper($keys,$content_type){
	$current=\aw2_library::get_array_ref('content_types',$content_type);	
	if(!is_array($keys))$keys=explode('.',$keys);	
	
	while(!empty($keys)){
		$key=array_shift($keys);
		
		if(is_object($current) && get_class($current)==='ct'){
			if($current->code!==AW2_ERROR)
				$current= $current->code;
			else if($current->sql!==AW2_ERROR)
				$current= $current->sql;
			else
				$current= $current->value;
		}
		
		if(!is_object($current) && !is_array($current)){
			$current='';
			break;
		}
		
		if(is_object($current) && isset($current->$key)){
			$current=$current->$key;
		}
		
		if(is_array($current) && isset($current[$key])){
			$current=$current[$key];
		}
	}


	return $current;
}


function set_helper($str,$value,$content_type){
		$ptr=&\aw2_library::get_array_ref('content_types',$content_type);	
		$keys=explode('.',$str);


		while(count($keys)>0) {
			$key=array_shift($keys);
			
			if($key==='_new'){
				if(!is_array($ptr))$ptr=array();
				$ptr[] = null;
				end($ptr);
				$ptr= &$ptr[key($ptr)]; 
				continue;
			}

			if($key==='_first'){
				reset($ptr);
				$ptr= &$ptr[key($ptr)];
				continue;
			}
			
			if($key==='_last'){
				end($ptr);
				$ptr= &$ptr[key($ptr)];
				continue;
			}

			if($key==='_prop'){
				if(!is_object($ptr))
					$ptr=new \stdClass();
				continue;
			}
			
			if(is_object($ptr) && get_class($ptr)==='ct'){
				$ptr= &$ptr->value;
			}
			
			if(!is_object($ptr) && !is_array($ptr)){
				$ptr=array();
			}

			if(is_object($ptr)){
				if (!property_exists($ptr,$key)){
					$ptr->$key=null;
				}
				$ptr= &$ptr->$key;
				continue;
			}	
			if(is_array($ptr)){
				if (!array_key_exists($key,$ptr)){
					$ptr[$key]=null;
				}
				$ptr= &$ptr[$key];
				continue;
			}	
		}
		$ptr=$value;	
	}	
*/