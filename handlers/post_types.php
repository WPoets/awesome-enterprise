<?php
namespace aw2\post_types;

\aw2_library::add_service('post_types.add','Add a New post Type',['namespace'=>'aw2\post_types']);

function add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	'singular_name'=>null,
	'plural_name'=>null,
	'desc'=>null
	), $atts) );
	
	if(empty($main)) return;
	
	if(!isset($_COOKIE['amit'])) return;
	
	unset($atts['main']);
	unset($atts['desc']);
	$tags_left = $shortcode['tags_left'];
	
	$public=true;
	if(strtolower($tags_left[0])=='private')
		$public=false;
/* 		
 \util::var_dump($shortcode);
	echo $main;
	echo $singular_name;
	echo $plural_name;  */
	
	$args = array(
			'label' => $plural_name,
			'public' => $public,
			'show_in_nav_menus'=>false,
			'show_ui' => true,
			'show_in_menu' => true,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'query_var' => false,
			'menu_icon'=>'dashicons-archive',
			'supports' => array('title','editor','revisions','thumbnail','custom-fields'),
			'rewrite' => false,
			'delete_with_user' => false,
			'labels' => array (
				  'name' => $plural_name,
				  'singular_name' => $singular_name,
				  'menu_name' => $plural_name,
				  'add_new' => 'Create '.$singular_name,
				  'add_new_item' => 'Add New '.$singular_name,
				  'new_item' => 'New '.$singular_name,
				  'edit' => 'Edit '.$singular_name,
				  'edit_item' => 'Edit '.$singular_name,
				  'view' => 'View '.$singular_name,
				  'view_item' => 'View '.$singular_name,
				  'search_items' => 'Search '.$plural_name,
				  'not_found' => 'No '.$singular_name.' Found',
				  'not_found_in_trash' => 'No '.$singular_name.' Found in Trash'
				)
			) ;
	
	\register_post_type($main, $args );
	//$register=new \awesome2_register($pieces[1],$atts,$content);
	//$register->run();
}
