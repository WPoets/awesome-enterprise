<?php
namespace aw2\_namespace;

\aw2_library::add_service('namespace.build', 'Build functions within a namespace', ['func'=>'build', 'namespace'=>__NAMESPACE__]);


function build($atts, $content=null, $shortcode=null) {

	
	//throw exception if not found	
    if(!isset($atts['main'])) {
        throw new \Exception('namespace is required');
    }

    $namespace = $atts['main'];

	// Set up namespace context
	$stack_id = \aw2\call_stack\push_context('namespace_build', 'namespace_build', 'namespace_build');
	$call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
	$info = &$call_stack['info'];
	$info['namespace'] = $namespace;



	// Parse and execute the function code
	\aw2_library::parse_shortcode($content);

    \aw2\call_stack\pop_context($stack_id);
	
	return ;

}
