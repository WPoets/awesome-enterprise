<?php
namespace aw2\debug;


\aw2_library::add_service('debug','Debug Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('debug.ignore','Ignore what is inside',['namespace'=>__NAMESPACE__]);

function ignore($atts,$content=null,$shortcode){
	return;
}

