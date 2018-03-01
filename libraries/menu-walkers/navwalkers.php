<?php

if (!defined('ABSPATH')){
	exit;
}
//this function controls the walker class for aw2_menu class

function aw2_navwalker_modify_nav_menu_args( $args )
{

	if(!isset($args['container']))
	{
		$args['container'] ='div';
	}
	if(!isset($args['container_class']) || empty($args['container_class']))
	{
		$args['container_class'] = 'collapse navbar-collapse';
	}

	if(!isset($args['walker']) || empty($args['walker']))
	{
		
		if ( !class_exists( 'wp_bootstrap_navwalker' )  ) {
			require('wp_bootstrap_navwalker.php');
		}
		
		$args['walker'] = new wp_bootstrap_navwalker();
		$args['fallback_cb']='wp_bootstrap_navwalker::fallback';
		@$args['menu_class'] =$args['menu_class'].' nav navbar-nav';
	}
	
	if(isset($args['walker']) && $args['walker']=='default')
	{
		$args['walker'] = new Walker_Nav_Menu;
		$args['fallback_cb']='';
		$args['menu_class'] =$args['menu_class'].' nav navbar-nav';
		$args['container_class'] =$args['container_class'].' navbar-collapse ';
	}

	return $args;
}

add_filter( 'wp_nav_menu_args', 'aw2_navwalker_modify_nav_menu_args' );
