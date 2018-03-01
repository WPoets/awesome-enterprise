<?php
//duplicate modules

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'Awesome_Admin_Duplicate_Modules' ) ) :

/**
 * WC_Admin_Duplicate_Product Class
 */
class Awesome_Admin_Duplicate_Modules {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'post_submitbox_start', array( $this, 'awesome_dupe_button' ) );
		//add_filter( 'post_row_actions',  array( $this,'awesome_duplicate_post_link'), 100, 2 );
		add_filter( 'page_row_actions',  array( $this,'awesome_duplicate_post_link'), 100, 2 );
		add_action( 'admin_action_awesome_duplicate_post_as_draft',  array( $this,'awesome_duplicate_post_as_draft') );
	}

	/*
	 * Add the duplicate link to action list for post_row_actions
	 */
	public function awesome_duplicate_post_link( $actions, $post ) {

		if (current_user_can('edit_post') && Monoframe::is_awesome_post_type($post)) {
			$actions['duplicate-module'] = '<a href="admin.php?action=awesome_duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
		}
		
		return $actions;
	}
	

	/**
	 * Show the dupe product link in admin
	 */
	public function awesome_dupe_button() {
		global $post;

		if ( ! is_object( $post ) ) {
			return;
		}

		if ( !Monoframe::is_awesome_post_type($post) ) {
			return;
		}

		if ( isset( $_GET['post'] ) ) {
			$notifyUrl = admin_url( "admin.php?action=awesome_duplicate_post_as_draft&amp;post=" . absint( $_GET['post'] ) );
			?>
			<div id="duplicate-action"><a class="submitduplicate duplication" href="<?php echo esc_url( $notifyUrl ); ?>"><?php _e( 'Duplicate', 'awesome-studio' ); ?></a></div>
			<?php
		}
	}

	/*
	 * Function creates post duplicate as a draft and redirects then to the edit post screen
	 */
	public function awesome_duplicate_post_as_draft(){
		global $wpdb;
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'awesome_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
			wp_die('No post to duplicate has been supplied!');
		}
	 
		/*
		 * get the original post id
		 */
		$post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		/*
		 * and all the original post data then
		 */
		$post = get_post( $post_id );
	 
		/*
		 * if you don't want current user to be the new post author,
		 * then change next couple of lines to this: $new_post_author = $post->post_author;
		 */
		$current_user = wp_get_current_user();
		$new_post_author = $current_user->ID;
	 
		/*
		 * if post data exists, create the post duplicate
		 */
		if (isset( $post ) && $post != null) {
	 
			/*
			 * new post data array
			 */
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				'post_title'     => $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
	 
			/*
			 * insert the post by wp_insert_post() function
			 */
			$new_post_id = wp_insert_post( $args );
	 
			/*
			 * get all current post terms ad set them to the new post draft
			 */
			$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
			}
	 
			/*
			 * duplicate all post meta
			 */
			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
			if (count($post_meta_infos)!=0) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					$meta_value = addslashes($meta_info->meta_value);
					$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}
				$sql_query.= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}
	 
	 
			/*
			 * finally, redirect to the edit post screen for the new draft
			 */
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
			exit;
		} else {
			wp_die('Post creation failed, could not find original post: ' . $post_id);
		}
	}

	
}

endif;

new Awesome_Admin_Duplicate_Modules();
