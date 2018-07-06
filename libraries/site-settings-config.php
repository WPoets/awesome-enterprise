<?php

add_action( 'cmb2_admin_init', 'monoframe_site_settings_callback' );

function monoframe_site_settings_callback() {

	$options_key = 'awesome_site';
	$option_metabox = array();
	// use CMO filter to add an intro at the top of the options page

	/* 
	$option_metabox[] = array(
            'id'         => 'general_options', //id used as tab page slug, must be unique
            'title'      => 'General Options',
            'show_on'    => array( 'key' => 'options-page', 'value' => array( 'general_options' ), ), //value must be same as id
            'show_names' => true,
            'fields'     => array(
				array(
					'name' => __('Header Logo', 'theme_textdomain'),
					'desc' => __('Logo to be displayed in the header menu.', 'theme_textdomain'),
					'id' => 'header_logo', //each field id must be unique
					'default' => '',
					'type' => 'file',
				),		
				array(
					'name' => __('Login Logo', 'theme_textdomain'),
					'desc' => __('Logo to be displayed in the login page. (320x120)', 'theme_textdomain'),
					'id' => 'login_logo',
					'default' => '',
					'type' => 'file',
				),
				array(
					'name' => __('Favicon', 'theme_textdomain'),
					'desc' => __('Site favicon. (32x32)', 'theme_textdomain'),
					'id' => 'favicon',
					'default' => '',
					'type' => 'file',
				),
				array(
					'name' => __( 'SEO', 'theme_textdomain' ),
					'desc' => __( 'Search Engine Optimization Settings.', 'theme_textdomain' ),
					'id'   => 'branding_title', //field id must be unique
					'type' => 'title',
				),
				array(
					'name' => __('Site Keywords', 'theme_textdomain'),
					'desc' => __('Keywords describing this site, comma separated.', 'theme_textdomain'),
					'id' => 'site_keywords',
					'default' => '',				
					'type' => 'textarea_small',
				),
			)
        );		

        $option_metabox[] = array(
            'id'         => 'social_options',
            'title'      => 'Social Media Settings',
            'show_on'    => array( 'key' => 'options-page', 'value' => array( 'social_options' ), ),
            'show_names' => true,
            'fields'     => array(
				array(
					'name' => __('Facebook Username', 'theme_textdomain'),
					'desc' => __('Username of Facebook page.', 'theme_textdomain'),
					'id' => 'facebook',
					'default' => '',					
					'type' => 'text'
				),
				array(
					'name' => __('Twitter Username', 'theme_textdomain'),
					'desc' => __('Username of Twitter profile.', 'theme_textdomain'),
					'id' => 'twitter',
					'default' => '',					
					'type' => 'text'
				),
				array(
					'name' => __('Youtube Username', 'theme_textdomain'),
					'desc' => __('Username of Youtube channel.', 'theme_textdomain'),
					'id' => 'youtube',
					'default' => '',					
					'type' => 'text'
				),
				array(
					'name' => __('Flickr Username', 'theme_textdomain'),
					'desc' => __('Username of Flickr profile.', 'theme_textdomain'),
					'id' => 'flickr',
					'default' => '',					
					'type' => 'text'
				),
				array(
					'name' => __('Google+ Profile ID', 'theme_textdomain'),
					'desc' => __('ID of Google+ profile.', 'theme_textdomain'),
					'id' => 'google_plus',
					'default' => '',					
					'type' => 'text'
				),
			)
        );

        $option_metabox[] = array(
            'id'         => 'advanced_options',
            'title'      => 'Advanced Settings',
            'show_on'    => array( 'key' => 'options-page', 'value' => array( 'advanced_options' ), ),
            'show_names' => true,
            'fields'     => array(
            	array(
					'name' => __('Color Scheme', 'theme_textdomain'),
					'desc' => __('Main theme color.', 'theme_textdomain'),
					'id' => 'color_scheme',
					'default' => '',				
					'type' => 'colorpicker',
				),
				array(
					'name' => __('Custom CSS', 'theme_textdomain'),
					'desc' => __('Enter any custom CSS you want here.', 'theme_textdomain'),
					'id' => 'new_custom_css',
					'default' => '',				
					'type' => 'textarea',
				),
			)
        ); */

	// configuration array
	$args = array(
		'key'     => $options_key,
		'menu_title'   => get_option('blogname').' Settings',
		'page_title'   => get_option('blogname').' Settings',
		'menu_type' => 'main',  // true / false
		'settings' => $option_metabox,
		'savetxt' => 'Save Settings',
	);

	// create the options page
	new Monoframe_Site_Settings( $args );
	
	
}

function monoframe_get_site_settings_callback(){
	$options_key = 'awesome_site';
	
	$awesome_site_settings=&aw2_library::get_array_ref('site_settings');
	$awesome_site_settings = cmb2_get_option($options_key,'all');
	
	//aw2_library::d();
}

add_action( 'cmb2_init', 'monoframe_get_site_settings_callback' );