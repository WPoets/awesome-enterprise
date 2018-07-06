 <?php
 
 
// add action to hook option page creation to
add_action( 'cmb2_admin_init', 'monomyth_site_settings_example' );


/**
 * Callback for 'cmb2_admin_init'.
 *
 * In this example, 'boxes' and 'tabs' call functions simply to separate "normal" CMB2 configuration
 * from unique CMO configuration.
 */
function monomyth_site_settings_example() {

	$options_key = 'awesome_settings';
	$option_metabox = array();
	// use CMO filter to add an intro at the top of the options page
	add_filter( 'cmb2metatabs_before_form', 'cmb2_metatabs_options_add_intro_via_filter' );
	
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
        );

	// configuration array
	$args = array(
		'key'     => $options_key,
		'menu_title'   => 'Example Options',
		'page_title'   => 'Example Options Page Title',
		'menu_type' => 'plugin',  // true / false
		'settings' => $option_metabox,
		'savetxt' => 'BIG SAVE BUTTON',
	);

	// create the options page
	new Monoframe_Site_Settings( $args );
}
 
