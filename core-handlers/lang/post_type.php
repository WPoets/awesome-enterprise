<?php

namespace aw2\post_type;

\aw2_library::add_service('post_type.service.register', 'Used to register a post type', ['namespace' => __NAMESPACE__]);

function register($atts, $content = null, $shortcode = null) {
    // Extract and validate required attributes
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'desc' => '',
        'post_type' => null,
        'connection' => '#default'
    ), $atts));
    
    // Validate main parameter
    if (!is_string($main) || empty($main)) {
        throw new \Exception("post_type.service.register requires main attribute to be a non-empty string");
    }
    
    // Validate post_type parameter
    if (!is_string($post_type) || empty($post_type)) {
        throw new \Exception("post_type.service.register requires post_type attribute to be a non-empty string");
    }
    
    // Set up default values for service registration
    $defaults = array(
        'post_type' => $post_type,
        'connection' => $connection
    );
    
    // Register the service with aw2_library
    \aw2_library::add_service(
        $main,
        $desc,
        [
            '$defaults' => $defaults,
            'func' => 'load',
            'namespace' => __NAMESPACE__
        ]
    );
    
    return '';
}


function load($atts, $content = null, $shortcode = null){

      $handler=$shortcode['handler'];
      $defaults=$handler['$defaults'];
      $post_type=$defaults['post_type'];
      $connection=$defaults['connection'];

    if (!isset($shortcode['tags_left'][0])) {
        throw new \Exception("Module name not provided in the shortcode");
    }
    $module = $shortcode['tags_left'][0];


	//check the location
	$connection_arr=\aw2_library::get('code_connections.' . $connection);
    $connection_service = '\\aw2\\'.$connection_arr['connection_service'].'\\module\\get';

    $a['connection']=$connection;
    $a['post_type']=$post_type;
    $a['module']=$module;

    $arr = call_user_func($connection_service,$a);
    if(!isset($arr['module']))return null;

    \util::var_dump($arr);

    $tags_used = implode('.', array_diff($shortcode['tags'], $shortcode['tags_left']));

    // Set up default values for service registration
    $defaults = array(
        'post_type' => $post_type,
        'discovered' => true,
        'module' => $module
    );
    
    // Register the service with aw2_library
    \aw2_library::add_service(
        $tags_used . '.' . $module,
        $module,
        [
            '$defaults' => $defaults,
            'func' => 'module_load',
            'namespace' => __NAMESPACE__
        ]
    );
    // run the module    
    $p=array();
    $p['module']=$module;
    $p['post_type']=$post_type;
    $p['hash']=$arr['hash'];
    $p['title']=$arr['title'];
    $p['connection']=$connection;
    $p['module_service_path']=$tags_used . '.' . $module;
    \aw2\module\code_run($p,$arr['code']);

    // run the template

}
