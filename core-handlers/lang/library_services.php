<?php

namespace aw2\library;
\aw2_library::add_service('library', 'Unhandled repository services', ['func'=>'_library', 'namespace'=>__NAMESPACE__]);
function _library($atts, $content=null, $shortcode = array()) {
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('You cant call the library directly');
    }

    if(isset($shortcode['handler']['#defaults']))
        $settings = $shortcode['handler']['#defaults'];
    else
        throw new \Exception('library connection is not defined');

    $connection=$settings['connection'];        

    $connection_arr=\aw2_library::$stack['code_connections'];

    if(!isset($connection_arr[$connection])) 
        throw new \Exception('connection is not defined');


    // Join the tags_left array with dots to create the service name
    $service = implode('.', $shortcode['tags_left']);
    
    // Create parameters for folder_conn.service.get
    $params = array('main' => $service, 'connection' => $connection);
    

    // Get the service definition
    $connection_service = '\\aw2\\'.$connection_arr[$connection]['connection_service'].'\\service\\get';
	
    
     $reply = call_user_func($connection_service,$params);
    

    // If service doesn't exist, register a placeholder and return empty string
    if($reply['#exists'] === false) {
        \aw2_library::add_service('library.' . $service, 'does not exist', ['func'=>'unknown', 'namespace'=>'aw2\service']);
        return '';
    }

	$build=array(	'func' => $reply['func'], 
	'namespace' => $reply['namespace'],
	);
	if(isset($reply['#defaults'])){
		$build['#defaults']=$reply['#defaults'];
	}	
	
    // Register the service with the retrieved properties
    \aw2_library::add_service(
        'library.' . $service, 
        isset($reply['desc']) ? $reply['desc'] : '', 
		$build
    );

    // Run the service with the provided attributes and content
    $reply = \aw2_library::service_run('library.' . $service, $atts, $content);
    
    return $reply;
}
