<?php
namespace aw2\env;

\aw2_library::add_service('env','Handles the environment',['namespace'=>__NAMESPACE__]);

function unhandled($atts,$content=null,$shortcode){
	extract(\aw2_library::shortcode_atts( array(
	'_prefix'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );

	$chain='env';
	if($_prefix)$chain=$chain . '.' . $_prefix;
	if($shortcode['tags_left'] && count($shortcode['tags_left'])>0){
		$pieces=implode(".",$shortcode['tags_left']);		
		$chain=$chain . '.' . $pieces;
	}

	$return_value=\aw2_library::get($chain,$atts,$content);
	if($return_value==='')$return_value=$default;

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('env.get','Get an Environment Value',['namespace'=>__NAMESPACE__]);

function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'_prefix'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	if($_prefix)$main=$_prefix . '.' . $main;
	$return_value=\aw2_library::get($main,$atts,$content);
	
	if($return_value==='')$return_value=$default;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('env.exists','Get an Environment Value',['namespace'=>__NAMESPACE__]);

function exists($atts,$content=null,$shortcode){
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'_prefix'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	if($_prefix)$main=$_prefix . '.' . $main;

	if(\aw2_library::env_key_exists($main) === AW2_ERROR)
		return false;

	return true;
}

\aw2_library::add_service('env.set','Set an Environment Value',['namespace'=>__NAMESPACE__]);

function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'_prefix'=>null,
	'overwrite'=>'yes',
	'default'=>'##not_set##',
	'assume_empty' => '##not_set##',
	'main'=>null
	), $atts) );
	unset($atts['assume_empty']);
	unset($atts['overwrite']);
	unset($atts['default']);
	unset($atts['main']);
	unset($atts['_prefix']);
	
	if($main){
		if($_prefix)$main=$_prefix . '.' . $main;
		\aw2_library::set($main,null,$content,$atts);
	}	
	
	foreach ($atts as $loopkey => $loopvalue) {
		$newvalue=$loopvalue;
		if($loopvalue===$assume_empty)$newvalue='';
		if(($loopvalue=='' || $loopvalue==null) && $default!=='##not_set##')$newvalue=$default;
		if($_prefix)$loopkey=$_prefix . '.' . $loopkey;
		\aw2_library::set($loopkey,$newvalue,null,$atts);
	}
	return;
}


\aw2_library::add_service('env.set_value','Set an Environment Value',['namespace'=>__NAMESPACE__]);
function set_value($atts,$content=null,$shortcode){
	
	extract(\aw2_library::shortcode_atts( array(
	'_prefix'=>null,
	'path'=>null,
	'main'=>null
	), $atts) );

    if(!is_string($path)) {
        throw new \InvalidArgumentException('path must be a string value.');
    }
    if(!isset($atts['main'])) {
        throw new \InvalidArgumentException('main must be set');
    }

	
	if($_prefix)$path=$_prefix . '.' . $path;
	\aw2_library::set($path,$main,null,null);
	
	return;
}


\aw2_library::add_service('env.set.key','Set a complex Environment Value',['func'=>'_key' ,'namespace'=>__NAMESPACE__]);
function _key($atts,$content=null,$shortcode){
	
	extract(\aw2_library::shortcode_atts( array(
	'_prefix'=>null,
	'key'=>null,
	'value'=>null
	), $atts) );
	unset($atts['_prefix']);
	if($_prefix)$key=$_prefix . '.' . $key;
	\aw2_library::set($key,$value,null,$atts);
	return;
}


\aw2_library::add_service('env.set_raw','Set a Raw Value. Will not be parsed',['namespace'=>__NAMESPACE__]);
function set_raw($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'_prefix'=>null,
	'main'=>null
	), $atts) );

	if($_prefix)$main=$_prefix . '.' . $main;
	
	if($main){
		\aw2_library::set($main,$content);
	}	
	return;
}

\aw2_library::add_service('env.set_array','Set an Array.',['namespace'=>__NAMESPACE__]);

function set_array($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'_prefix'=>null,
	'main'=>null,
	'with'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['with']);
	unset($atts['_prefix']);
	if($_prefix)$main=$_prefix . '.' . $main;
	
	if(\aw2_library::endswith($main, '.new')){
		\aw2_library::set($main,null);	
		$path=substr($main, 0, -4);
		foreach ($atts as $loopkey => $loopvalue) {
			\aw2_library::set($path . '.last.' . $loopkey,$loopvalue);
		}
		if($content){
			$ab=new \array_builder();
			$arr=$ab->parse($content);
			\aw2_library::set($path . '.last' ,$arr);
		}
			
	}
	else{
		foreach ($atts as $loopkey => $loopvalue) {
			\aw2_library::set($main . '.' . $loopkey,$loopvalue);
		}
		if($content){
			$ab=new \array_builder();
			$arr=$ab->parse($content);
			\aw2_library::set($main ,$arr);
		}		
	}
	return;
	
}



\aw2_library::add_service('env.set_obj','Build an Object',['namespace'=>__NAMESPACE__]);
function set_obj($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'_prefix'=>null,
	'main'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['_prefix']);
	if($_prefix)$main=$_prefix . '.' . $main;
	
	$obj=\aw2_library::get($main);
	if(!is_object($obj)){
		$value=new \stdClass();
		\aw2_library::set($main,$value);
	}

	foreach ($atts as $loopkey => $loopvalue) {
		\aw2_library::set($main . '.' . $loopkey,$loopvalue);
	}
	if($content){
		$ab=new \array_builder();
		$arr=$ab->parse($content);
		\aw2_library::set($main ,(object)$arr);
	}		
	
	return;
}



\aw2_library::add_service('env.dump','Dump an environment Value',['namespace'=>__NAMESPACE__]);

function dump($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'_prefix'=>null
	), $atts, 'dump' ) );

	$c='env';
	if($_prefix)$c.='.' . $_prefix ;
	
	if($main)$c.='.' . $main ;

	$return_value=\aw2_library::get($c);
	$return_value=\util::var_dump($return_value,true);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);	
	return $return_value;
}

\aw2_library::add_service('env.echo','Dump an environment Value',['func'=>'_echo' ,'namespace'=>__NAMESPACE__]);

function _echo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'_prefix'=>null
	), $atts, 'dump' ) );

	$c='env';
	if($_prefix)$c.='.' . $_prefix ;
	
	if($main)$c.='.' . $main ;

	$return_value=\aw2_library::get($c);
	\util::var_dump($return_value);
}

