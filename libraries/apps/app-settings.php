<?php

class Awesome_App_Settings {
	/**
	 * Options page hook, equivalent to get_current_screen()['id']
	 *
	 * @var string
	 *
	 * @since  1.0.0
	 */
	protected static $options_page = '';
	/**
	 * $props: Properties which can be injected via constructor
	 *
	 * @var array
	 *
	 * @since  1.0.0
	 */
	public static $options_key ='';
	/**
     * Constructor
     * @since 0.1.0
     */
	public function __construct( $args ) {

		
	}
	
	/**
	 * ADD WP ACTIONS
	 * Note, some additional actions are added elsewhere as they cannot be added this early.
	 *
	 * @since  1.0.0
	 */
	public static function add_wp_actions() {
		// Register setting
		add_action( 'admin_init', 'Awesome_App_Settings::register_setting' ) ;

		// Include CSS for this options page as style tag in head if tabs are configured
		add_action( 'admin_head', 'Awesome_App_Settings::add_css'  );
		add_action( "cmb2_save_options-page_fields", 'Awesome_App_Settings::settings_notices' , 10, 2 );
	}
	
	/**
     * Register our setting tabs to WP
     * @since  0.1.0
     */
    public static function register_setting() {
		$apps = self::get_registered_apps();
		foreach($apps as $app){
			register_setting( $app->slug.'_options', $app->slug.'_options' );
		}
		
	}
	/**
	 * ADD CSS
	 * Adds a couple of rules to clean up WP styles if tabs are included
	 *
	 * @since 1.0.0
	 */
	public static function add_css() {
		$style= plugins_url('module-distribution/css/studio-admin.css',dirname(__FILE__)) ;
		echo "<link rel='stylesheet' href='".$style."'>";

	}
	
	/**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page() {     
		
		// Include CMB CSS in the head to avoid FOUC, called here as we need the screen ID
		add_action( 'admin_print_styles-' . Awesome_App_Settings::$options_page , array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		
    }
	

	  /**
     * Admin page markup. Mostly handled by CMB
     * @since  0.1.0
     */
    public static function display_setting($app_slug, $option_tabs, $tab_url,$panel) {
		
    	//$option_tabs = self::option_fields($app_slug,$orignal_slug); //get all option tabs
		
    	$tab_forms = array();     	   	
        self::add_css();
		?>
		<div class="bhoechie-tab-container <?php echo $app_slug; ?>">
			<div class="col-md-3 col-sm-2 col-xs-3 bhoechie-tab-menu">
				<div class="list-group">
					<?php foreach ($option_tabs as $option_tab) :
						$tab_slug = $option_tab['id'];
						$nav_class = 'list-group-item text-center ';
						if ( $tab_slug == $panel ) {
							$nav_class .= ' active'; //add active class to current tab
							$tab_forms[] = $option_tab; //add current tab to forms to be rendered
						}
					?>            	
						<a class="<?php echo $nav_class; ?>" href="<?php echo $tab_url; ?>&panel=<?php echo $option_tab['id']; ?>"><?php esc_attr_e($option_tab['title']); ?></a>
					
					<?php endforeach; ?>
				</div>
			</div>
			<div class="col-md-8 col-sm-10 col-xs-9 bhoechie-tab">
				<?php
					foreach ($tab_forms as $tab_form) : //render all tab forms (normaly just 1 form) ?>
			
						<div id="<?php esc_attr_e($tab_form['id']); ?>" class="bhoechie-tab-content active">

							<?php cmb2_metabox_form( $tab_form, $app_slug.'_options' , array('save_button'=>'Save')); ?>
						</div>   
				<?php 
					endforeach; 
				?>		
			</div> 
		</div>
		<?php
    }
	
	/**
     * Defines the theme option metabox and field configuration
     * @since  0.1.0
     * @return array
     */
    public static function option_fields($app_slug,$orignal_slug) {
	   
	    $apps = self::get_registered_apps();
		
		/* $selected_app =false;
		foreach($apps as $app){
			if($app['slug']==$app_slug){
				$selected_app = $app;
				break;
			}
		} */
	   $selected_app = aw2_library::get('app');
	   if($selected_app)
       {
			$selected_app->options_metaboxes = apply_filters( $orignal_slug. '_settings', $selected_app->options_metaboxes );
			return $selected_app->options_metaboxes;
	   }
	   return array();
    }
	
	/**
	 * Register settings notices for display
	 *
	 * @since  0.1.0
	 * @param  int   $object_id Option key
	 * @param  array $updated   Array of updated fields
	 * @return void
	 */
	public static function settings_notices( $object_id, $updated ) {
		$selected_app = aw2_library::get('app');
		
		if ( $object_id !== $selected_app->slug.'_options' || empty( $updated ) ) {
			return;
		} 
		
		add_settings_error( 'app-setting-notices', '', 'Settings updated.', 'updated' );
		settings_errors( 'app-setting-notices' );
	}
	
	public static function get_registered_apps(){
		//global $registered_apps;
		$our_apps=&aw2_library::get_array_ref('apps');
		return $our_apps;
	}

}
