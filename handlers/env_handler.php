<?php

function aw2_env_key_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'_prefix'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	if($_prefix)$main=$_prefix . '.' . $main;
	$return_value=aw2_library::get($main,$atts,$content);
	
	if($return_value==='')$return_value=$default;
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))
		$return_value='Object';
	return $return_value;
}

function aw2_env_key_set($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'_prefix'=>null,
	'overwrite'=>'yes',
	'default'=>'',
	'assume_empty' => null,
	'main'=>null
	), $atts) );
	unset($atts['assume_empty']);
	unset($atts['overwrite']);
	unset($atts['default']);
	unset($atts['main']);
	unset($atts['_prefix']);
	
	if($main){
		if($_prefix)$main=$_prefix . '.' . $main;
		aw2_library::set($main,null,$content,$atts);
	}	
	
	foreach ($atts as $loopkey => $loopvalue) {
		$newvalue=$loopvalue;
		if($loopvalue==$assume_empty)$newvalue='';
		if($loopvalue=='' || $loopvalue==null)$newvalue=$default;
		if($_prefix)$loopkey=$_prefix . '.' . $loopkey;
		aw2_library::set($loopkey,$newvalue,null,$atts);
	}
	return;
}

function aw2_env_key_set_raw($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'_prefix'=>null,
	'main'=>null
	), $atts) );

	if($_prefix)$main=$_prefix . '.' . $main;
	
	if($main){
		aw2_library::set($main,$content);
	}	
	return;
}

function aw2_env_key_set_array($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'_prefix'=>null,
	'main'=>null,
	'with'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['with']);
	unset($atts['_prefix']);
	if($_prefix)$main=$_prefix . '.' . $main;
	
	if(aw2_library::endswith($main, '.new')){
		aw2_library::set($main,null);	
		$path=substr($main, 0, -4);
		foreach ($atts as $loopkey => $loopvalue) {
			aw2_library::set($path . '.last.' . $loopkey,$loopvalue);
		}
		if($content){
			$ab=new array_builder();
			$arr=$ab->parse($content);
			aw2_library::set($path . '.last' ,$arr);
		}
			
	}
	else{
		foreach ($atts as $loopkey => $loopvalue) {
			aw2_library::set($main . '.' . $loopkey,$loopvalue);
		}
		if($content){
			$ab=new array_builder();
			$arr=$ab->parse($content);
			aw2_library::set($main ,$arr);
		}		
	}
	return;
	
}

function aw2_env_key_dump($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract( shortcode_atts( array(
	'main'=>null,
	'_prefix'=>null
	), $atts, 'dump' ) );

	$c='';
	if($_prefix)$c=$_prefix . '.';
	
	if($main){
		$d=$c  . $atts['main'] . '.dump';
	}
	else
		$d=$c  . 'dump';

	$atts['main']=$d;
	$return_value=awesome2_get($atts,$content,$shortcode);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
	
}

function aw2_env_key_echo($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract( shortcode_atts( array(
	'main'=>null,
	'_prefix'=>null
	), $atts, 'dump' ) );

	$c='';
	if($_prefix)$c=$_prefix . '.';
	
	if($main){
		$d=$c  . $atts['main'] . '.echo';
	}
	else
		$d=$c  . 'echo';

	$atts['main']=$d;
	$return_value=awesome2_get($atts,$content,$shortcode);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
	
}

//////// Module Library ///////////////////
aw2_library::add_library('module','Module Functions');


function aw2_module_get($atts,$content=null,$shortcode){
	$atts['_prefix']='module';
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_module_unhandled($atts,$content=null,$shortcode){
	$atts['_prefix']='module';
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	$atts['main']=implode(".",$pieces);		
	return aw2_env_key_get($atts,$content,$shortcode);
}


function aw2_module_set($atts,$content=null,$shortcode){
	$atts['_prefix']='module';
	return aw2_env_key_set($atts,$content,$shortcode);
}


function aw2_module_dump($atts,$content=null,$shortcode){
	$atts['_prefix']='module';
	return aw2_env_key_dump($atts,$content,$shortcode);
}

function aw2_module_echo($atts,$content=null,$shortcode){
	$atts['_prefix']='module';
	return aw2_env_key_echo($atts,$content,$shortcode);
}

function aw2_module_set_raw($atts,$content=null,$shortcode){
	$atts['_prefix']='module';
	return aw2_env_key_set_raw($atts,$content,$shortcode);
}

function aw2_module_set_array($atts,$content=null,$shortcode){
	$atts['_prefix']='module';
	return aw2_env_key_set_array($atts,$content,$shortcode);
}


function aw2_module_run($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	'template'=>null
	), $atts) );
	unset($atts['main']);
	unset($atts['template']);
	
	$return_value=aw2_library::module_forced_run($atts,$main,$template,$content,$atts);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_module_include($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	unset($atts['main']);
	$return_value=aw2_library::module_include($atts,$main);
	return $return_value;
}


function aw2_module_return($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=aw2_library::get($main,$atts,$content);
	aw2_library::set('_return',true);	
	aw2_library::set('module._return',$return_value);
	return;
}



//////// Templates Library ///////////////////
aw2_library::add_library('templates','Template Functions');

function aw2_templates_add($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$ref=&aw2_library::get_array_ref('module','templates');
	$ref[$main]['code']=$content;
	$ref[$main]['name']=$main;
}

function aw2_templates_unhandled($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );	
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	if(!count($pieces)>=1)return 'Template not defined';
	$template=implode('.',$pieces);	
	
	$return_value=aw2_library::template_run($template,$content,$atts);
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}

function aw2_templates_run($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );	
	unset($atts['main']);	
	$return_value=aw2_library::template_run($main,$content,$atts);
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}

//////// Template Library ///////////////////
aw2_library::add_library('template','Template Functions');

function aw2_template_get($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_template_unhandled($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	$atts['main']=implode(".",$pieces);		
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_template_set($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_set($atts,$content,$shortcode);
}


function aw2_template_set_raw($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_set_raw($atts,$content,$shortcode);
}

function aw2_template_set_array($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_set_array($atts,$content,$shortcode);
}


function aw2_template_dump($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_dump($atts,$content,$shortcode);

}

function aw2_template_echo($atts,$content=null,$shortcode){
	$atts['_prefix']='template';
	return aw2_env_key_echo($atts,$content,$shortcode);
}



function aw2_template_run($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'main'=>null,
		'module'=>null
	), $atts) );
	if(!$main)return 'Template not defined';
	unset($atts['main']);
	unset($atts['module']);
	
	$return_value=aw2_library::module_run($atts,$module,$main,$content,$atts);

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	return $return_value;
}

function aw2_template_return($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value=aw2_library::get($main,$atts,$content);
	aw2_library::set('_return',true);	
	aw2_library::set('template._return',$return_value);
	return;
}



//////// Env Library ///////////////////
aw2_library::add_library('env','Env Handler');


function aw2_env_get($atts,$content=null,$shortcode){
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_env_unhandled($atts,$content=null,$shortcode){
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	$atts['main']=implode(".",$pieces);		
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_env_set($atts,$content=null,$shortcode){
	return aw2_env_key_set($atts,$content,$shortcode);
}


function aw2_env_dump($atts,$content=null,$shortcode){
	return aw2_env_key_dump($atts,$content,$shortcode);
}

function aw2_env_echo($atts,$content=null,$shortcode){
	return aw2_env_key_echo($atts,$content,$shortcode);
}

function aw2_env_set_raw($atts,$content=null,$shortcode){
	return aw2_env_key_set_raw($atts,$content,$shortcode);
}

function aw2_env_set_array($atts,$content=null,$shortcode){
	return aw2_env_key_set_array($atts,$content,$shortcode);
}



//////// App Library ///////////////////
aw2_library::add_library('app','App Functions');

function aw2_app_get($atts,$content=null,$shortcode){
	$atts['_prefix']='app';
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_app_unhandled($atts,$content=null,$shortcode){
	$atts['_prefix']='app';
	$pieces=$shortcode['tags'];
	array_shift($pieces);
	$atts['main']=implode(".",$pieces);		
	return aw2_env_key_get($atts,$content,$shortcode);
}

function aw2_app_set($atts,$content=null,$shortcode){
	$atts['_prefix']='app';
	return aw2_env_key_set($atts,$content,$shortcode);
}


function aw2_app_set_raw($atts,$content=null,$shortcode){
	$atts['_prefix']='app';
	return aw2_env_key_set_raw($atts,$content,$shortcode);
}

function aw2_app_set_array($atts,$content=null,$shortcode){
	$atts['_prefix']='app';
	return aw2_env_key_set_array($atts,$content,$shortcode);
}


function aw2_app_dump($atts,$content=null,$shortcode){
	$atts['_prefix']='app';
	return aw2_env_key_dump($atts,$content,$shortcode);

}

function aw2_app_echo($atts,$content=null,$shortcode){
	$atts['_prefix']='app';
	return aw2_env_key_echo($atts,$content,$shortcode);
}



function aw2_app_run($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'main'=>null,
		'module'=>null,
		'template'=>null
		), $atts) );
	if(!$main)return 'app not defined';
	unset($atts['main']);
	
	if($main==='active_module'){
		$ref=aw2_library::get_array_ref('app','active');
		$return_value=aw2_library::module_run($ref['collection'],$ref['module'],$ref['template'],$content,$atts);
	}	

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	return $return_value;
}


function aw2_app_return($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value=aw2_library::get($main,$atts,$content);
	aw2_library::set('_return',true);	
	aw2_library::set('app._return',$return_value);
	return;
}
