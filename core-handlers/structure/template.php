<?php
namespace aw2\template;

if (!defined('AWESOME_LCNC') || AWESOME_LCNC === 'no') {
    \aw2_library::add_service('template','Handles the active template',['env_key'=>'template']);

	//deprecated
	\aw2_library::add_service('template.set', 'Set template Value', ['namespace' => __NAMESPACE__]);
	function set($atts, $content = null, $shortcode = array()) {
		$atts['_prefix'] = 'template';
		return \aw2\env\set($atts,$content,null);
	}

}

\aw2_library::add_service('template.anon.run','Run an arbitrary template',['func'=>'anon_run','namespace'=>__NAMESPACE__]);
function anon_run($atts,$content=null,$shortcode = array()){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
	), $atts) );
	if(!$main)return 'Template Path not defined';
	unset($atts['main']);
	
	$template_content=\aw2_library::get($main);
	$return_value=\aw2_library::template_anon_run($template_content,$content,$atts);

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

\aw2_library::add_service('template.run','Run an arbitrary template',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode = array()){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'module'=>null
	), $atts) );
	if(!$main)return 'Template not defined';
	unset($atts['main']);
	unset($atts['module']);
	
	$return_value=\aw2_library::module_run($atts,$module,$main,$content,$atts);

	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

\aw2_library::add_service('template.return','End the active template',['func'=>'_return' , 'namespace'=>__NAMESPACE__]);

function _return($atts,$content=null,$shortcode = array()){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'service'=>null
	), $atts) );
	
	if($service){
		$return_value=\aw2_library::service_run($service,$atts,$content);		
	}
	else
	$return_value=\aw2_library::get($main,$atts,$content);

	\aw2_library::set('_return',true);	
	\aw2_library::set('template._return',$return_value);
	return;
}

\aw2_library::add_service('template.dump', 'Dump template Value', ['namespace' => __NAMESPACE__]);
function dump($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'template';
    return \aw2\common\env_services\dump($atts);
}

\aw2_library::add_service('template.echo', 'Echo template Value', ['func' => '_echo', 'namespace' => __NAMESPACE__]);
function _echo($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'template';
    \aw2\common\env_services\_echo($atts);
}




// Additional set services
\aw2_library::add_service('template.set.path', 'Set template Value with Path', ['func' => 'set_path', 'namespace' => __NAMESPACE__]);
function set_path($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'template';
    return \aw2\common\env_services\set_path($atts);
}

\aw2_library::add_service('template.set.paths', 'Set multiple template Values with Paths', ['func' => 'set_paths', 'namespace' => __NAMESPACE__]);
function set_paths($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'template';
    return \aw2\common\env_services\set_paths($atts);
}

\aw2_library::add_service('template.set.value', 'Set template Value directly', ['func' => 'set_value', 'namespace' => __NAMESPACE__]);
function set_value($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'template';
    return \aw2\common\env_services\set_value($atts);
}

\aw2_library::add_service('template.set.content', 'Set template Value from Content', ['func' => 'set_content', 'namespace' => __NAMESPACE__]);
function set_content($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'template';
    return \aw2\common\env_services\set_content($atts, $content);
}

\aw2_library::add_service('template.set.raw', 'Set Raw unparsed Content to template', ['func' => 'set_raw', 'namespace' => __NAMESPACE__]);
function set_raw($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'template';
    return \aw2\common\env_services\set_raw($atts, $content);
}



if (defined('AWESOME_LCNC') && AWESOME_LCNC === 'yes') {
    // Register basic template services
	\aw2_library::add_service('template.path', 'Get a template Value', ['namespace' => __NAMESPACE__]);
	function path($atts, $content = null, $shortcode = array()) {
		$atts['start'] = 'template';
		return \aw2\common\env_services\get($atts);
	}
	

	// Register template existence check
    \aw2_library::add_service('template.exists', 'Check existence of a path', ['namespace' => __NAMESPACE__]);
    function exists($atts, $content = null, $shortcode = array()) {
        $atts['start'] = 'template';
        return \aw2\common\env_services\exists($atts);
    }
}
