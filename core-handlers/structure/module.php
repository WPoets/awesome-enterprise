<?php
namespace aw2\module;


if (!defined('AWESOME_LCNC') || AWESOME_LCNC === 'no') {
    \aw2_library::add_service('module','Handles the active module',['env_key'=>'module']);

    //deprecated
    \aw2_library::add_service('module.set', 'Set module Value', ['namespace' => __NAMESPACE__]);
    function set($atts, $content = null, $shortcode = array()) {
        $atts['_prefix'] = 'module';
        return \aw2\env\set($atts,$content,null);
    }

    \aw2_library::add_service('module.register','Register an arbitrary module',['namespace'=>__NAMESPACE__]);

    function register($atts,$content=null,$shortcode = array()){
        if(\aw2_library::pre_actions('all',$atts,$content)==false)return;


        $atts=\aw2_library::split_array_on($atts,'collection');	

        extract(\aw2_library::shortcode_atts( array(
        'main'=>null,
        'desc'=>null
        ), $atts) );

        if ($main === null) {
            $main = '';
        }

        unset($atts['main']);
        unset($atts['desc']);
        
        \aw2_library::add_service($main,$desc,$atts);

    }



    \aw2_library::add_service('module.code.run','Run module code',['func'=>'code_run','namespace'=>__NAMESPACE__]);

    function code_run($atts,$content=null,$shortcode=null){

        $hash=$atts['hash'];

        // Setup the context stack
        $stack_id = \aw2\call_stack\push_context('module', 'module', $hash);
        $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
        $info = &$call_stack['info'];
        $info['index'] = 0;
        $info['title'] = isset($atts['title']) ? $atts['title'] : '';    
        $info['slug'] = isset($atts['slug']) ? $atts['slug'] : '';    
        $info['connection'] = isset($atts['connection']) ? $atts['connection'] : '';    
        $info['hash'] = $hash;    
        $info['module_service_path']=$atts['module_service_path'];

        \aw2_library::parse_shortcode($content);
        \util::var_dump('module');
        \util::var_dump($content);
        \util::var_dump(\aw2_library::get('module'));
        
        \aw2\call_stack\pop_context($stack_id);
    }


    \aw2_library::add_service('module.template.register','register a template for a module',['func'=>'template_register','namespace'=>__NAMESPACE__]);

    function template_register($atts,$content=null,$shortcode = array()){
        \util::var_dump('template_register');
        \util::var_dump(\aw2_library::get('module'));
        \util::var_dump($atts);
        \util::var_dump($content);
        $module=\aw2_library::get('module');
        // Set up default values for service registration
        $defaults = array(
            'code' => $content,
            'template_type'=>'template'
        );
        
        // Register the service with aw2_library
        \aw2_library::add_service(
            $module['info']['module_service_path'] . '.' . $atts['main'],
            $atts['main'],
            [
                '$defaults' => $defaults,
                'func' => 'module_template_run',
                'namespace' => __NAMESPACE__
            ]
        );
    }


    \aw2_library::add_service('module.service.register','register a service for a module',['func'=>'service_register','namespace'=>__NAMESPACE__]);

    function service_register($atts,$content=null,$shortcode = array()){
        \util::var_dump('service_register');
        \util::var_dump(\aw2_library::get('module'));
        \util::var_dump($atts);
        \util::var_dump($content);
        $module=\aw2_library::get('module');
        // Set up default values for service registration
        $defaults = array(
            'code' => $content,
            'template_type'=>'service'
        );
        
        // Register the service with aw2_library
        \aw2_library::add_service(
            $module['info']['module_service_path'] . '.' . $atts['main'],
            $atts['main'],
            [
                '$defaults' => $defaults,
                'func' => 'module_template_run',
                'namespace' => __NAMESPACE__
            ]
        );
    }

}



	
\aw2_library::add_service('module.run','Run an arbitrary module',['namespace'=>__NAMESPACE__]);

function run($atts,$content=null,$shortcode = array()){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'template'=>null
	), $atts) );
	unset($atts['main']);
	unset($atts['template']);
	
	$return_value=\aw2_library::module_forced_run($atts,$main,$template,$content,$atts);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('module.include','Include an arbitrary module',['func'=>'_include','namespace'=>__NAMESPACE__]);

function _include($atts,$content=null,$shortcode = array()){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	unset($atts['main']);
	$return_value=\aw2_library::module_include($atts,$main);
	return $return_value;
}

\aw2_library::add_service('module.return','Return an active module',['func'=>'_return','namespace'=>__NAMESPACE__]);

function _return($atts,$content=null,$shortcode = array()){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	\aw2_library::set('_return',true);	
	\aw2_library::set('module._return',$return_value);
	return;
}


\aw2_library::add_service('module.dump', 'Dump module Value', ['namespace' => __NAMESPACE__]);
function dump($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'module';
    return \aw2\common\env_services\dump($atts);
}

\aw2_library::add_service('module.echo', 'Echo module Value', ['func' => '_echo', 'namespace' => __NAMESPACE__]);
function _echo($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'module';
    \aw2\common\env_services\_echo($atts);
}




// Additional set services
\aw2_library::add_service('module.set.path', 'Set module Value with Path', ['func' => 'set_path', 'namespace' => __NAMESPACE__]);
function set_path($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'module';
    return \aw2\common\env_services\set_path($atts);
}

\aw2_library::add_service('module.set.paths', 'Set multiple module Values with Paths', ['func' => 'set_paths', 'namespace' => __NAMESPACE__]);
function set_paths($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'module';
    return \aw2\common\env_services\set_paths($atts);
}

\aw2_library::add_service('module.set.value', 'Set module Value directly', ['func' => 'set_value', 'namespace' => __NAMESPACE__]);
function set_value($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'module';
    return \aw2\common\env_services\set_value($atts);
}

\aw2_library::add_service('module.set.content', 'Set module Value from Content', ['func' => 'set_content', 'namespace' => __NAMESPACE__]);
function set_content($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'module';
    return \aw2\common\env_services\set_content($atts, $content);
}

\aw2_library::add_service('module.set.raw', 'Set Raw unparsed Content to module', ['func' => 'set_raw', 'namespace' => __NAMESPACE__]);
function set_raw($atts, $content = null, $shortcode = array()) {
    $atts['start'] = 'module';
    return \aw2\common\env_services\set_raw($atts, $content);
}



if (defined('AWESOME_LCNC') && AWESOME_LCNC === 'yes') {

    // Register basic func services
    \aw2_library::add_service('module.path', 'Get a module Value', ['namespace' => __NAMESPACE__]);
    function path($atts, $content = null, $shortcode = array()) {
        $atts['start'] = 'module';
        return \aw2\common\env_services\get($atts);
    }    

	// Register basic func services
	\aw2_library::add_service('module.exists', 'Check existence of a path', ['namespace' => __NAMESPACE__]);
	function exists($atts, $content = null, $shortcode = array()) {
		$atts['start'] = 'module';
		return \aw2\common\env_services\exists($atts);
	}

}

