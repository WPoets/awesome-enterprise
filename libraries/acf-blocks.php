<?php
namespace aw2\acf_blocks;

add_action('init','aw2\acf_blocks\setup_acf_blocks',1);
add_action('acf/init', 'aw2\acf_blocks\register_acf_blocks',15);

function setup_acf_blocks(){
	\aw2_apps_library::run_core('gutenberg-blocks');
}


function register_acf_blocks(){
	$gutenberg_blocks=&\aw2_library::get_array_ref('gutenberg_blocks');
	if( function_exists('acf_register_block_type') ){
		  
		  foreach($gutenberg_blocks as $key=>$block){
		    acf_register_block_type($block);
			 unset($gutenberg_blocks[$key]);
		  }
		  
		 
	}
}
