<?php

add_action( 'wp_update_nav_menu', 'aw2_optimised::action_wp_update_nav_menu' );
add_action( 'edit_terms', 'aw2_optimised::wp_flush_get_term_by_cache', 10, 2 );

class aw2_optimised{
	static function cached_nav_menu($args = array(), $prime_cache = false ){
		global $wp_query;

		$queried_object_id = empty( $wp_query->queried_object_id ) ? 0 : (int) $wp_query->queried_object_id;

		$last_edit = get_option( 'nav_menu_last_edit', 0 );

		$nav_menu_key = 'nav-menu-' . md5( serialize( $args ) . '-' . $queried_object_id . '-' . $last_edit );
		$my_args = wp_parse_args( $args );
		$my_args = apply_filters( 'wp_nav_menu_args', $my_args );
		$my_args = (object) $my_args;

		if ( ( isset( $my_args->echo ) && true === $my_args->echo ) || !isset( $my_args->echo ) ) {
			$echo = true;
		} else {
			$echo = false;
		}

		if ( true === $prime_cache || false === ( $nav_menu = get_transient( $nav_menu_key ) ) ) {
			if ( false === $echo ) {
				$nav_menu = wp_nav_menu( $args );
			} else {
				ob_start();
				wp_nav_menu( $args );
				$nav_menu = ob_get_clean();
			}

			set_transient( $nav_menu_key, $nav_menu, MINUTE_IN_SECONDS * 15 );
		}
		if ( true === $echo ) {
			echo $nav_menu;
		} else {
			return $nav_menu;
		}
	}
	static function vip_get_term_by( $field, $value, $taxonomy, $output = OBJECT, $filter = 'raw' ) {
		// ID lookups are cached
		if ( 'id' == $field )
			return get_term_by( $field, $value, $taxonomy, $output, $filter );

		$cache_key = $field . '|' . $taxonomy . '|' . md5( $value );
		$term_id = wp_cache_get( $cache_key, 'get_term_by' );

		if ( false === $term_id ) {
			$term = get_term_by( $field, $value, $taxonomy );
			if ( $term && ! is_wp_error( $term ) )
				wp_cache_set( $cache_key, $term->term_id, 'get_term_by', 4 * HOUR_IN_SECONDS );
			else
				wp_cache_set( $cache_key, 0, 'get_term_by', 15 * MINUTE_IN_SECONDS ); // if we get an invalid value, let's cache it anyway but for a shorter period of time
		} else {
			$term = get_term( $term_id, $taxonomy, $output, $filter );
		}

		if ( is_wp_error( $term ) )
			$term = false;

		return $term;
	}
	static function action_wp_update_nav_menu() {
		update_option( 'nav_menu_last_edit', time() );
	}
	
	static function wp_flush_get_term_by_cache( $term_id, $taxonomy ){
		$term = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term ) {
			return;
		}
		foreach( array( 'name', 'slug' ) as $field ) {
			$cache_key = $field . '|' . $taxonomy . '|' . md5( $term->$field );
			$cache_group = 'get_term_by';
			wp_cache_delete( $cache_key, $cache_group );
		}
	}
}


