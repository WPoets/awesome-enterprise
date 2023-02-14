<?php
namespace aw2\app;

\aw2_library::add_service('app','Handles the active app',['env_key'=>'app']);


\aw2_library::add_service('app.run','Run the active module of the current app',['namespace'=>__NAMESPACE__]);

function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'module'=>null,
		'template'=>null
		), $atts) );
	if(!$main)return 'app not defined';
	unset($atts['main']);
	
	if($main==='active_module'){
		$ref=\aw2_library::get_array_ref('app','active');
		$return_value=\aw2_library::module_run($ref['collection'],$ref['module'],$ref['template'],$content,$atts);
	}	

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

\aw2_library::add_service('app.register','Register an App',['namespace'=>__NAMESPACE__]);

function register($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'title'=>'',
	), $atts) );
	
	$ab=new \array_builder();
	$arr=$ab->parse($content);
	$registered_apps=&\aw2_library::get_array_ref('apps');


	$app=array();
	//path has to be handled correctly
	$app['base_path']=AWESOME_APP_BASE_PATH .'/'.$main;
	$app['path']=AWESOME_APP_BASE_PATH .'/'.$main;
	$app['name']=$title;
	$app['slug']=$main;
	$app['post_id']='';
	$app['hash']='app:' . $main;
			
	$app['collection']=$arr['collection'];

	$registered_apps[$main]=$app;
	return;
}

//not sure how to use it
function aw2_app_return($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	$return_value=\aw2_library::get($main,$atts,$content);
	\aw2_library::set('_return',true);	
	\aw2_library::set('app._return',$return_value);
	return;
}



\aw2_library::add_service('app.collection.modules.list','get a list of modules of collection',['func'=>'modules_list','namespace'=>__NAMESPACE__]);


//[app.collection.modules.list active collection=config /]	
function modules_list($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'collection'=>null
	), $atts) );
	
	$collection=\aw2_library::get('apps.' . $main . '.collection.' . $collection);
	//$modules=\aw2_library::get_collection($collection);
 		if(!isset($collection['connection']))$collection['connection']='#default';
	$connection_arr=\aw2_library::$stack['code_connections'][$collection['connection']];
	
	$connection_service = '\\aw2\\'.$connection_arr['connection_service'].'\\collection\\_list';
		
	$atts['connection']=$collection['connection'];
	$atts['post_type']=$collection['post_type'];
	$modules = call_user_func($connection_service,$atts);

	return $modules;
}

\aw2_library::add_service('app.collection.module.get','get a module',['func'=>'module_get','namespace'=>__NAMESPACE__]);

//[app.collection.module.get  active collection=config module='m1'/]	

function module_get($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'collection'=>null,
		'module'=>null
	), $atts) );
	
	$collection=\aw2_library::get('apps.' . $main . '.collection.' . $collection);
	$module=\aw2_library::get_module($collection,$module);
 	
	return $module;
}





