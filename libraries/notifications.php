<?php

add_action( 'init', 'awesome_notifications::register_device_token_cpt' );
add_action('wp_ajax_aw2_save_device_tokens', 'awesome_notifications::save_device_token');
add_action('wp_ajax_nopriv_aw2_save_device_tokens', 'awesome_notifications::save_device_token');

class awesome_notifications{
	
	//function generate_authcode( $object_id, $cmb_id, $updated_fields, $cmb){
	static function register_device_token_cpt(){
		register_post_type('device_tokens', array(
			'label' => 'Device Token',
			'description' => '',
			'public' => false,
			'exclude_from_search'=>true,
			'publicly_queryable'=>false,
			'show_in_nav_menus'=>false,
			'show_ui' => true,
			'show_in_menu' => true,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'query_var' => false,
			'supports' => array('title','editor','custom-fields'),
			'labels' => array (
				  'name' => 'Device Tokens',
				  'singular_name' => 'Device Token',
				  'menu_name' => 'Device Token',
				  'add_new' => 'Add Device Token',
				  'add_new_item' => 'Add New Device Token',
				  'edit' => 'Edit',
				  'edit_item' => 'Edit Device Token',
				  'new_item' => 'New Device Token',
				  'view' => 'View Device Token',
				  'view_item' => 'View Device Token',
				  'search_items' => 'Search Device Token',
				  'not_found' => 'No Device Token Found',
				  'not_found_in_trash' => 'No Device Token Found in Trash',
				  'parent' => 'Parent Device Token',
				)
			) ); 
			
		register_taxonomy('aw2_token_type', 'device_tokens', array(
			// Hierarchical taxonomy (like categories)
			'hierarchical' => true,
			'public' => false,
			'query_var' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_admin_column'=>true,
			// This array of options controls the labels displayed in the WordPress Admin UI
			'labels' => array(
				  'name' => _x( 'Device Token Type', 'taxonomy general name' ),
				  'singular_name' => _x( 'Device Token Type', 'taxonomy singular name' ),
				  'search_items' =>  __( 'Search Device Token Type' ),
				  'all_items' => __( 'All Device Token Type' ),
				  'parent_item' => __( 'Parent Device Token Type' ),
				  'parent_item_colon' => __( 'Parent Device Token Type:' ),
				  'edit_item' => __( 'Edit Device Token Type' ),
				  'update_item' => __( 'Update Device Token Type' ),
				  'add_new_item' => __( 'Add New Device Token Type' ),
				  'new_item_name' => __( 'New Device Token Type Name' ),
				  'menu_name' => __( 'Device Token Type' ),
				)
	
		));
			
	}
	
	
	
	static function save_device_token() {
		$result=array(
			"status"=>"fail",
			"token"=>""
		);
		
		if(empty($_REQUEST['device_token']) || empty($_REQUEST['device_type'])){
			echo json_encode($result);
			wp_die();
		}
		
		$post_arr = array(
			'post_title'   => 'token-'.date('dmYHis'),
			'post_type' => 'device_tokens',
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'meta_input'   => array(
				'device_token' => trim($_REQUEST['device_token']),
				'user_id' => trim($_REQUEST['user_id']),
			),
		);
		
		$post_id = wp_insert_post($post_arr);
	
		if(!is_wp_error( $post_id )){
			
			
			$term_id = term_exists( trim($_REQUEST['device_type']), 'aw2_token_type');
			if(empty($term_id)){
				$term_id = wp_insert_term(trim($_REQUEST['device_type']), 'aw2_token_type');
			}

			if(!is_wp_error( $term_id )){
				wp_set_post_terms( $post_id, $term_id, 'aw2_token_type' );
			}
			
			$result=array(
				"status"=>"success",
				"token"=>"awesome-ting"
			);
		}
			
		echo json_encode($result);
		wp_die();
		
	}
	
	static function get_all_device_tokens($device_type='all'){
		$args=array(
			"post_type"=>"device_tokens",
			"post_status"=>"publish",
			"posts_per_page"=>-1
		);
		
		if(!empty($device_type)&& !($device_type=="all")){
			$args['tax_query'] = array(
					array(
						'taxonomy' => 'aw2_token_type',
						'field'    => 'slug',
						'terms'    => $device_type
					),
				);
		}
		
		$devices= new WP_Query( $args );
		$tokens=array();
		foreach($devices->posts as $device){
			$term_list = wp_get_post_terms($device->ID, 'aw2_token_type', array("fields" => "all"));
			$d_type=$term_list[0]->slug;
			$tokens[$d_type][]=get_post_meta($device->ID, 'device_token',true);
		}
		return $tokens;
	}
	
	static function get_user_device_token($user_id){
		if(empty($user_id)) {
			aw2_library::set_error("user_id parameter is empty");
			return;
		}	
		
		$args=array(
			"post_type"=>"device_tokens",
			"post_status"=>"publish",
			"posts_per_page"=>1,
			'meta_query' => array(
					array(
						'key'     => 'user_id',
						'value'   => $user_id,
						'compare' => '='
					),
				)
			);
		
		$devices= new WP_Query( $args );
		$tokens=array();
		foreach($devices->posts as $device){
			$term_list = wp_get_post_terms($device->ID, 'aw2_token_type', array("fields" => "all"));
			$tokens['device_token']=get_post_meta($device->ID, 'device_token',true);
			$tokens['device_type']=$term_list[0]->slug;
		}
		return $tokens;
	}
	
}
