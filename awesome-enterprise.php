<?php
/*
Plugin Name: Awesome Studio Enterprise
Plugin URI: http://www.getawesomestudio.com
Description: Awesome Studio is a shortcode based platform along with massive collection beautifully designed, fully responsive and easy to use UI parts. 
Version: 1.2.8
Author: WPoets
Author URI: http://www.wpoets.com
License: GPLv2 or Later
*/

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
define('AWE_VERSION',$plugin_data['Version']);
/**
 * plugin-update-checker
 *
 * A custom update checker for WordPress plugins. Useful if you don't want to host your project in the official WP repository, but would still * like it to support automatic updates. Despite the name, it also works with themes.
 *
 * Developere: Dev Danidhariya
 * @author     Original Author <Jānis Elsts>
 * @author     Another Author  devidas Dandidhariya <devidas@amiworks.com>
 * @copyright  Copyright (c) 2017 Jānis Elsts
 * @license    https://github.com/YahnisElsts/plugin-update-checker/blob/master/license.txt MIT License
 * @link       https://github.com/YahnisElsts/plugin-update-checker#github-integration
 * @param      Wpoets Repo URL,Repo name
*/
require 'libraries/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
  'https://github.com/WPoets/awesome-enterprise',
  __FILE__,
  'awesome-enterprise'
);


//Optional: If you're using a private repository, specify the access token like this:
//$myUpdateChecker->setAuthentication('your-token-here');

//Optional: Set the branch that contains the stable release.
//$myUpdateChecker->setBranch('master');

//Optional: Check for automatical release
$myUpdateChecker->getVcsApi()->enableReleaseAssets();
/*********************** plugin-update-checker code end ***************************/



require_once 'libraries/util/util.php';
require_once 'includes/aw2_library.php';
aw2_library::setup();	

if(!class_exists('Monoframe')) {
	require_once 'includes/monoframe.php';
}
require 'vendor/autoload.php';
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
 	

    function pluginUpdateChecker(){

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