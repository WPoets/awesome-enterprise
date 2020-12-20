<?php


function awesome_export_html( $args = array() ) {
	global $wpdb, $post;

	$defaults = array(
		'activity'    => 'all',
		'filename'     => '',
		'start_date' => false,
		'end_date'   => false
	);
	$args     = wp_parse_args( $args, $defaults );

	/**
	 * Fires at the beginning of an export, before any headers are sent.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args An array of export arguments.
	 */

	$post_types = array();
	
	switch($args['activity']){
	
		case "selected":
			if(isset($_REQUEST['services'])){
				foreach($_REQUEST['services'] as $service){
					$post_types[]=$service;
				}
			}
			break;
	}

	$esses      = array_fill( 0, count( $post_types ), '%s' );



	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	$where = $wpdb->prepare( "{$wpdb->posts}.post_type IN (" . implode( ',', $esses ) . ')', $post_types );


	$where .= " AND {$wpdb->posts}.post_status != 'auto-draft'";
	
	$join = '';
		
	if ( $args['author'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_author = %d", $args['author'] );
	}

	if ( $args['start_date'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date >= %s", gmdate( 'Y-m-d', strtotime( $args['start_date'] ) ) );
	}

	if ( $args['end_date'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date < %s", gmdate( 'Y-m-d', strtotime( '+1 month', strtotime( $args['end_date'] ) ) ) );
	}
	
	
echo "SELECT ID FROM {$wpdb->posts} $join WHERE $where" ;


	// Grab a snapshot of post IDs, just in case it changes during the export.
	$post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} $join WHERE $where" );

		
		
	if ( $post_ids ) {
		/**
		 * @global WP_Query $wp_query WordPress Query object.
		 */
		global $wp_query;

		// Fake being in the loop.
		$wp_query->in_the_loop = true;
		
		$base_path=dirname(ABSPATH);
	echo "$base_path" ;	
		// Fetch 20 posts at a time rather than loading the entire table into memory.
		while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
			$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
			$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} $where" );
			
			// Begin Loop.
			
			foreach ( $posts as $post ) {
				$collection_directory= $base_path . '/' . $post->post_type;
				if (!file_exists($collection_directory)) {
					mkdir($collection_directory, 0777, true);
				}
				setup_postdata( $post );
				$file = $collection_directory . '/' . $post->post_name . '.module.html';
				file_put_contents($file,$post->post_content);
			}
		}	
	}
}
