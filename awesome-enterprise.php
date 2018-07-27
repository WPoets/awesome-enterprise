<?php
/*
Plugin Name: Awesome Studio Enterprise
Plugin URI: http://www.getawesomestudio.com
Description: Awesome Studio is a shortcode based platform along with massive collection beautifully designed, fully responsive and easy to use UI parts. 
Version: 1.2.1
Author: WPoets
Author URI: http://www.wpoets.com
License: GPLv2 or Later
*/

define('AWE_VERSION','1.2.1');

require_once 'libraries/util/util.php';
require_once 'includes/aw2_library.php';
aw2_library::setup();	

if(!class_exists('Monoframe')) {
	require_once 'includes/monoframe.php';
}
require_once 'includes/app_setup.php';

register_activation_hook( __FILE__,'awesome2_trigger::activation' );
register_activation_hook( __FILE__, array( 'AW_Studio', 'activation_check' ) );

add_action( 'in_plugin_update_message-awesome-studio/awesome-ui-2.php', array( 'AW_Studio', 'in_plugin_update_message' ) );

class AW_Studio {
	
	function __construct() {
        add_action( 'admin_init', array( $this, 'check_version' ) );
 
        // Don't run anything else in the plugin, if we're on an incompatible WordPress/PHP version
        if ( ! self::compatible_version() ) {
            return;
        }
    }
	
	// The primary sanity check, automatically disable the plugin on activation if it doesn't
    // meet minimum requirements.
    static function activation_check() {
        if ( ! self::compatible_version() ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'Awesome Studio requires WordPress 4.3 or higher and PHP 5.6 or higher!', 'my-plugin' ) );
        }
    }
 
    // The backup sanity check, in case the plugin is activated in a weird way,
    // or the versions change after activation.
    function check_version() {
        if ( ! self::compatible_version() ) {
            if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
            }
        }
    }
 
    function disabled_notice() {
       echo '<strong>' . esc_html__( 'Awesome Studio requires WordPress 4.3 or higher and PHP 5.6 or higher!', 'my-plugin' ) . '</strong>';
    }
 
    static function compatible_version() {
        if ( version_compare( $GLOBALS['wp_version'], '4.3', '<' ) ) {
            return false;
        }
		
		if (version_compare(PHP_VERSION, '5.6.0') < 0) {
			return false;
		}

 
        // Add sanity checks for other version requirements here
 
        return true;
    }
	
	/**
	 * Show plugin changes.
	*/
	static function in_plugin_update_message( $args ) {
		$transient_name = 'aw_upgrade_notice_' . $args['Version'];

		if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/awesome-studio/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = self::parse_update_notice( $response['body'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Parse update notice from readme file
	 * @param  string $content
	 * @return string
	 */
	static function parse_update_notice( $content ) {
		// Output Upgrade Notice
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( AWE_VERSION ) . '\s*=|$)~Uis';
		$upgrade_notice = '';

		if ( preg_match( $regexp, $content, $matches ) ) {
			$version = trim( $matches[1] );
			$notices = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );

			if ( version_compare( AWE_VERSION, $version, '<' ) ) {

				$upgrade_notice .= '<div style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
				}

				$upgrade_notice .= '</div> ';
			}
		}

		return wp_kses_post( $upgrade_notice );
	}
	
}

$awe = new AW_Studio();