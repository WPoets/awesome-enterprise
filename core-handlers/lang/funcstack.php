<?php
namespace aw2\funcstack;


\aw2_library::add_service('funcstack.dump','Dump a funcstack function',['namespace'=>__NAMESPACE__]);

function dump($atts,$content=null,$shortcode){

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts, 'dump' ) );

    // Get reference to function stack
	$fstack = &\aw2_library::$funcstack;

    if(empty($main))
    	$return_value=\util::var_dump($fstack,true);
    else{
        if(isset($fstack[$main]))
            $return_value=\util::var_dump($fstack[$main],true);
        else
            $return_value=\util::var_dump("$main Function not found",true);
    }

	return $return_value;
}


