<?php
/**
 * Handles seaching post meta table.
 */
if(!class_exists('WPASM_Search') && !class_exists('Awesome_Meta_Search')) {
	class Awesome_Meta_Search {

		/**
		 * Initialize wp-admin post meta search.
		 *
		 * @return void
		 */
		public static function init() {
			self::add();
		}

		/**
		 * Adds filters on search.
		 *
		 * @return void
		 */
		public static function add() {
			add_filter( 'posts_join', array( __CLASS__, 'posts_join' ) );
			add_filter( 'posts_where', array( __CLASS__, 'posts_where' ) );
			add_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ) );
		}

		/**
		 * Removes filters from search.
		 *
		 * @return void
		 */
		public static function remove() {
			remove_filter( 'posts_join', array( __CLASS__, 'posts_join' ) );
			remove_filter( 'posts_where', array( __CLASS__, 'posts_where' ) );
			remove_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ) );
		}

		/**
		 * Constructs JOIN part of query.
		 *
		 * @param string $join
		 *
		 * @return string
		 */
		public static function posts_join( $join ) {
			global $wpdb;

			if ( ! self::_is_active() ) {
				return $join;
			}

			$join .= " LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";

			return $join;
		}

		/**
		 * Constructs WHERE part of query.
		 *
		 * @param string $where
		 *
		 * @return string
		 */
		public static function posts_where( $where ) {
			global $wpdb, $wp;

			if ( ! self::_is_active() ) {
				return $where;
			}

			$where = preg_replace( "/($wpdb->posts.post_title LIKE '%{$wp->query_vars['s']}%')/i", "$0 OR $wpdb->postmeta.meta_value LIKE '%{$wp->query_vars['s']}%' ", $where );
			//$where .= " OR ( $wpdb->postmeta.meta_value LIKE '%{$wp->query_vars['s']}%' ) ";

			return $where;
		}

		/**
		 * Constructs GROUP BY part of query.
		 *
		 * @param string $groupby
		 *
		 * @return string
		 */
		public static function posts_groupby( $groupby ) {
			global $wpdb;

			if ( ! self::_is_active() ) {
				return $groupby;
			}

			if ( empty( $groupby ) ) {
				$groupby = "$wpdb->posts.ID";
			}

			return $groupby;
		}

		/**
		 * Checks if we are on right page.
		 *
		 * @return bool
		 */
		protected static function _is_active() {
			global $pagenow, $wp_query;

			if ( ! is_admin() ) {
				return false;
			}

			if ( 'edit.php' != $pagenow ) {
				return false;
			}

			if ( ! isset( $_GET['s'] ) ) {
				return false;
			}

			if ( ! $wp_query->is_search ) {
				return false;
			}

			return true;
		}

	}
	
	Awesome_Meta_Search::init();
}