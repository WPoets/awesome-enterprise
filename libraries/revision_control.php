<?php


add_action( 'plugins_loaded', 'awesome_check_user_capabilities' ); 

function awesome_check_user_capabilities() {
	
	if ( current_user_can( 'develop_for_awesomeui' ) ) { 
		add_action( 'admin_menu', 'register_awesome_studio_custom_menu_page' );
	}

}

function register_awesome_studio_custom_menu_page() {
	add_submenu_page('awesome-dev', 'Manage Revisions- Awesome Studio Platform', 'Manage Revisions', 'develop_for_awesomeui', 'awesome-revisions','rcm_menu_page_callback' );
}

function rcm_menu_page_callback() {

	//$blocks =  array( 'aw2_query','aw2_module','aw2_page','aw2_core','aw2_data','aw2_hook','aw2_trigger' );
	$blocks = Monoframe::get_awesome_post_type();

	echo '<h3>Awesome Modules Revision Control</h3>
	<p>Delete unnecessary revisions for Awesome Studio Platform</p>';

	foreach ($blocks as $block) {

		$args = array(
			'post_type' => $block,
			'posts_per_page' => -1,
		);

		$loop = new WP_Query($args);

		$exclude_string = '';
		$status = '';

		if( $loop->have_posts() ) {
			while( $loop->have_posts() ) {
				$loop->the_post();

				$rev_data = get_post_meta( get_the_ID(), '_rcm_revision_meta', true);
				if( !empty($rev_data))  {
					$exclude_string .= ( empty($exclude_string) ) ? $rev_data : ','.$rev_data;
					$rev_data_arr = explode(',', $rev_data);
				}
			}
		}

		if( isset($_POST['rcm_delete_submit'] ) && 1 == $_POST['rcm_delete_submit'] ) {
			if( (isset( $_POST['rcm_meta'] ) && !empty( $_POST['rcm_meta'] )) ) {
				global $wpdb;
				if( !empty( $exclude_string ) ) {
					$sql = "DELETE a,b,c FROM ".$wpdb->prefix."posts a 
					INNER JOIN ".$wpdb->prefix."posts d ON (a.post_parent = d.ID)
					LEFT JOIN ".$wpdb->prefix."term_relationships b ON (a.ID = b.object_id)
					LEFT JOIN ".$wpdb->prefix."postmeta c ON (a.ID = c.post_id)
					WHERE d.post_type = '".$block."' and a.post_type = 'revision' and a.ID NOT IN ( ".$exclude_string." )";


					$rows = $wpdb->get_results($sql);
					echo '<p>Revisions of '.$block.' deleted successfully</p>';
				}
			} else {
				echo '<p>Nothing was deleted.</p>';
			}
		}

	}
	
	echo '
	<form action="" method="POST" name="rcm_action">
		<table>
			<tr>
				<td>
					<input type="checkbox" name="rcm_meta" id="rcm_meta"><label for="rcm_meta">Delete revisions not marked as important</label>
				</td>
			</tr>
			
			<tr>
				<td>
					<input type="hidden" value="1" name="rcm_delete_submit"><br/>
					<input type="submit" value="Submit" class="button button-primary">
				</td>
			</tr>
		</table>
	</form>';

}


add_action( 'post_submitbox_misc_actions', 'aw_submitbox_misc_actions' );

function aw_submitbox_misc_actions() { 

	global $post;
	//$blocks =  array( 'aw2_query','aw2_module','aw2_page','aw2_core','aw2_data','aw2_hook','aw2_trigger' );
	
	if(Monoframe::is_awesome_post_type($post)){ 
		echo '
		<div class="misc-pub-section">
			<input type="checkbox" id="_rcm_revision_meta" name="_rcm_revision_meta" />
			<label for="_rcm_revision_meta"><strong>Mark this revision as Important</strong></label>
		</div>';
	}

}


add_action( 'save_post', 'rcm_save_meta_box_data' );

function rcm_save_meta_box_data( $post_id ) {
	//$blocks =  array( 'aw2_query','aw2_module','aw2_page','aw2_core','aw2_data','aw2_hook','aw2_trigger' );
	
	//$post_type=get_post_type( $post_id );
	$post = get_post( $post_id );
	if(Monoframe::is_awesome_post_type($post))
	{
		
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
		
		
		if ( ! isset( $_POST['_rcm_revision_meta'] ) ) {
			return $post_id;
		} else {
			
			$revision_data = wp_get_post_revisions( $post_id );

			if( is_array($revision_data) && !empty($revision_data) ) {

				$rev_array = array_keys($revision_data);
				$rev_id = $rev_array[0];

				$rev_data = get_post_meta( $post_id, '_rcm_revision_meta', true);

				if ( empty($rev_data) ) {
					update_post_meta( $post_id, '_rcm_revision_meta', $rev_id);
				} else {
					$check_exists = explode(',', $rev_data);
					if ( !in_array($rev_id, $check_exists) ) {
						$rev_data .= ','.$rev_id;
						update_post_meta( $post_id, '_rcm_revision_meta', $rev_data);
					} 
				}

			}
		
		}
		
	} 
	return $post_id;

}



//add_action('admin_print_styles', 'aw2_remove_preview_button');

function aw2_remove_preview_button() { 

	global $post;
	if(isset($post)) {

		if(Monoframe::is_awesome_post_type($post)){ ?>
		  <style>
			  #preview-action, #visibility, .misc-pub-curtime,#minor-publishing-actions {
				display:none;
			  }
		  </style><?php
		}
	}
}
