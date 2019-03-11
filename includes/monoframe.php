<?php
if (!defined('ABSPATH'))
{
	exit;
}

//Solving the CPT UI Issue of not showing private objects
add_filter( 'cptui_attach_taxonomies_to_post_type', 'cptui_all_objects', 10, 1 );
add_filter( 'cptui_attach_post_types_to_taxonomy', 'cptui_all_objects', 10, 1 );

function cptui_all_objects( $args) {
	unset($args['public']);
	return $args;
}


class Monoframe
{

	public static function load()
	{
		add_filter('acf/settings/remove_wp_meta_box', '__return_false', 20);
		
		//https://gist.github.com/mikeschinkel/523831/
		//require_once  __DIR__ . '/monoframe/instrument-hooks.php';
		/**
		 * Initialize the CMB 2 metabox class.  help is currently at https://github.com/WebDevStudios/CMB2/wiki/Basic-Usage
		 */
	/* 
		if ( file_exists(  __DIR__ . '/monoframe/cmb/core/init.php' ) ) {
			require_once  __DIR__ . '/monoframe/cmb/core/init.php';
			require_once __DIR__ . '/monoframe/cmb/extras/field-gallery/cmb-field-gallery.php';
			require_once __DIR__ . '/monoframe/cmb/extras/field_map/cmb-field-map.php';
			require_once __DIR__ . '/monoframe/cmb/extras/rgba_picker/jw-cmb2-rgba-colorpicker.php';
			require_once __DIR__ . '/monoframe/cmb/extras/cmb2-attached-posts/cmb2-attached-posts-field.php';
			require_once __DIR__ . '/monoframe/cmb/extras/post-search-field/cmb2_post_search_field.php';
			require_once __DIR__ . '/monoframe/cmb/extras/field-select2/cmb-field-select2.php';
			require_once __DIR__ . '/monoframe/cmb/extras/field-slider/cmb2_field_slider.php';
			require_once __DIR__ . '/monoframe/cmb/extras/remote-image-select-field/cmb2-remote-img-sel.php';
			require_once __DIR__ . '/monoframe/cmb/extras/date-range-field/wds-cmb2-date-range-field.php';
			require_once __DIR__ . '/monoframe/cmb/extras/user-search-field/cmb2_user_search_field.php';
			require_once __DIR__ . '/monoframe/cmb/extras/cmb2-conditionals/cmb2-conditionals.php';
			require_once __DIR__ . '/monoframe/cmb/extras/cmb2-yesno-field/cmb2-yesno-field.php';
			require_once __DIR__ . '/monoframe/cmb/extras/cmb2-field-type-tags/cmb2-field-type-tags.php';
			require_once __DIR__ . '/monoframe/cmb/extras/post-list-select.php';
			require_once __DIR__ . '/monoframe/cmb/extras/cmb2-field-ajax-search/cmb2-field-ajax-search.php';
			require_once __DIR__ . '/monoframe/cmb/extras/select-multiple-field-type.php';
		}
		 
		require_once __DIR__ . '/monoframe/basic-maintenance.php';

		// initialize cmb2 site settings/site-settings
		//require_once( dirname( __FILE__ ) . '/monoframe/site-settings/site-settings.php' );
		//require_once( dirname( __FILE__ ) . '/monoframe/site-settings-config.php' );

		//require_once( dirname( __FILE__ ) . '/monoframe/hm-rewrites.php' ); // to ensure and give easy ability to add new rewrite rules
		// Automatically compiles any .less file to css.
		require_once( dirname( __FILE__ ) . '/monoframe/wp-less/wp-less.php' );
		
		//Include CSV/Excel file Importer, Example to use it is in Site_specific plugin
		//require_once( dirname( __FILE__ ) . '/monoframe/mm-csv-importer.php' );
		
		
		// Mobile detect and helpers
		
		require_once( dirname( __FILE__ ) . '/monoframe/wphelpers.php' );
		require_once( dirname( __FILE__ ) . '/monoframe/apis/mailchimp/class-api.php' );
		require_once( dirname( __FILE__ ) . '/monoframe/iptocountry/iptocountry.php' );
		require_once( dirname( __FILE__ ) . '/monoframe/revision_control.php' );
		require_once( dirname( __FILE__ ) . '/monoframe/duplicate-posts.php' );
		require_once( dirname( __FILE__ ) . '/monoframe/admin-meta-search.php' );
		
		//nav walkers
		require( dirname( __FILE__ ) . '/monoframe/menu-walkers/navwalkers.php' ); 
		
		//optimised default functions
		require( dirname( __FILE__ ) . '/monoframe/optimized-functions.php' ); 
		 */
		$plugin_path=plugin_dir_path( __DIR__ );
		// Include Ace Editor files.
		require_once( $plugin_path . '/libraries/social-login-improved.php' );

		require_once( $plugin_path . '/libraries/editor/aw-code-editor.php' );
		require_once( $plugin_path . '/libraries/editor/devcap.php' );
		//require_once( dirname( __FILE__ ) . '/monoframe/editor/preset.php' );
		//require_once( $plugin_path . '/libraries/editor/gmet.php' );
		
		require_once( $plugin_path . '/libraries/Mobile_Detect.php' );
		require(  $plugin_path . '/libraries/menu-walkers/navwalkers.php' ); 
		
		// Include Module Distributable files.
		//require_once( dirname( __FILE__ ) . '/monoframe/module-distribution/shortcode-generator.php' );
		
		//Apps Inclusion
		
	
		//require_once( dirname( __FILE__ ) . '/monoframe/awesome-app.php' );
		//zoho
		//require_once( dirname( __FILE__ ) . '/monoframe/zoho-crm.php' );
		
		
		//notification
		//require_once( dirname( __FILE__ ) . '/monoframe/notifications.php' );
		//require_once( dirname( __FILE__ ) . '/monoframe/WebSocket/autoload.php' );
		
		//require_once( dirname( __FILE__ ) . '/monoframe/awesome-app-o.php' );
	}
		
		// use the action to create a place for your meta box
	public function add_before_editor($post) {
	  global $post;
	  do_meta_boxes(get_current_screen(), 'monoframe_pre_editor', $post);
	}
	
	static function is_awesome_post_type($post){
		$blocks = self::get_awesome_post_type();			
		return in_array($post->post_type,$blocks);
	}

	static function get_awesome_post_type(){
		$default_post_types=array( 
						'aw2_core',
						'awesome_core'
					);
		$app_post_types=array();	
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach ($registered_apps as $app){
			
			foreach($app['collection'] as $collection_name => $collection){
				if($collection_name == 'posts')
					continue;
				if(isset($collection['post_type']))
					$app_post_types[]=$collection['post_type'];
			}
		}
		
		$app_post_types = array_merge($default_post_types, $app_post_types);
		
		
		$service_post_type= array();
		$handlers=&aw2_library::get_array_ref('handlers');
		
		foreach($handlers as $key => $handler){
			if(!isset($handler['post_type']))
				continue;
			
			if(isset($handler['@service']) && $handler['@service'] === true){
				$service_post_type[] =  $handler['post_type'];
			}	
		}
				
		$app_post_types = array_merge($app_post_types, $service_post_type);
		unset($service_post_type);
		
		$additional_slugs= aw2_library::get('settings.opt-editor-settings');
		if(!empty($additional_slugs)){
			$additional_slugs= explode(',',$additional_slugs);
			$app_post_types = array_merge($app_post_types, $additional_slugs);
		}
		
		return apply_filters('monoframe-awesome-post-types',$app_post_types);
		
	}
   /**
   * Add a nice red to the admin bar when we're in development mode
   */
  function mono_dev_colorize() { 
	global $monomyth_options;
	$MM_PRODUCTION = false;
	if(isset($monomyth_options['dev_mode']))
		$MM_PRODUCTION = $monomyth_options['dev_mode'];
    if(!$MM_PRODUCTION) {
    ?>
    <style>
      
      <?php if ( is_admin_bar_showing() ) : ?>
        html { 
          padding-top: 5px; 
        }
      <?php endif; ?>
      #wpadminbar {
        border-top: 5px solid #d84315;
        -moz-box-sizing: content-box !important;
        box-sizing: content-box !important;
      }
      #wp-admin-bar-site-name > a {
        background-color: #d84315;
        color: #f1f1f1;
      }
    </style>
    <?php }
  }
}

monoframe::load();
$monoframe = new monoframe();
add_action('edit_form_after_title',array($monoframe,'add_before_editor'));
add_filter( 'admin_head', array($monoframe,'mono_dev_colorize' ));
add_filter( 'wp_head', array($monoframe,'mono_dev_colorize' ));


add_filter('upload_mimes', 'monoframe_upload_mimes');

function monoframe_upload_mimes ( $existing_mimes=array() ) {

	// add the file extension to the array

	$existing_mimes['svg'] = 'mime/type';

        // call the modified list of extensions

	return $existing_mimes;

}


add_filter('wp_nav_menu', 'monoframe_do_menu_shortcodes'); 
function monoframe_do_menu_shortcodes( $menu ){ 
        return aw2_library::parse_shortcode( $menu ); 
}

function custom_taxonomy_tree_walker( $taxonomy, $parent = 0,$level = 1 ) {
    $terms = get_terms( $taxonomy, array( 'parent' => $parent, 'hide_empty' => false ) );
    if( count($terms) > 0 ) {
        $output = '';
        foreach ($terms as $term) {
            // function calls itself to display child elements, if any
            $output .= '<option value="'.$term->slug.'"> ' .str_repeat("-", $level).' '. $term->name . '</option>'. custom_taxonomy_tree_walker($taxonomy, $term->term_id,$level+1);
        }
         
        return $output;
    }
    return false;
}