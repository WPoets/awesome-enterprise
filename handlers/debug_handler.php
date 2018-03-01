<?php


aw2_library::add_library('debug','Debug Handler');

function aw2_debug_ignore($atts,$content=null,$shortcode){
	return;
}

function aw2_debug_get($atts,$content=null,$shortcode){
	return aw2_env_key_get($atts,$content,$shortcode);
}