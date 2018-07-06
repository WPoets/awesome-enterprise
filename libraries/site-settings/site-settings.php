<?php
/**
 * CMB Tabbed Theme Options & CMB2 Metatabs Options
 *
 * @author    Arushad Ahmed <@dash8x, contact@arushad.org>
 * @link      http://arushad.org/how-to-create-a-tabbed-options-page-for-your-wordpress-theme-using-cmb & https://github.com/rogerlos/cmb2-metatabs-options
 * @version   0.1.0
 */
class Monoframe_Site_Settings {
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
	private static $props = array(
		'key'        => 'monomyth_options',
		'menu_title'      => 'Site Options',
		'page_title'      => 'Site Options',
		'menu_type'    => 'main',
		'settings'      => array(),
		'savetxt'    => 'Save',
	);

	/**
     * Constructor
     * @since 0.1.0
     */
	public function __construct( $args ) {

		// require CMB2
		if ( ! class_exists( 'CMB2' ) )
			throw new Exception( 'CMB2 is required to use this class.' );

		// parse any injected arguments and add to self::$props
		self::$props = wp_parse_args( $args, self::$props );

		// Add actions
		$this->add_wp_actions();
	}
	
	/**
	 * ADD WP ACTIONS
	 * Note, some additional actions are added elsewhere as they cannot be added this early.
	 *
	 * @since  1.0.0
	 */
	private function add_wp_actions() {
		// Register setting
		add_action( 'admin_init', array( $this, 'register_setting' ) );

		// Adds page to admin with menu entry
		add_action( 'admin_menu', array( $this, 'add_options_page' ), 12 );

		// Include CSS for this options page as style tag in head if tabs are configured
		add_action( 'admin_head', array( $this, 'add_css' ) );

		// Adds JS to foot
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
		
		add_action( "cmb2_save_options-page_fields", array( $this, 'settings_notices' ), 10, 2 );
	}
	
	/**
     * Register our setting tabs to WP
     * @since  0.1.0
     */
    public function register_setting() {
		register_setting( self::$props['key'], self::$props['key'] );
	}
	
	/**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page() {     
		
		$params = apply_filters( self::$props['key'] . '_admin_menu', array(
            'title' => self::$props['page_title'],
            'menu_title' => self::$props['menu_title'],
            'permission' => 'manage_options',
            'namespace' => self::$props['key'],
            'template' =>  array( $this, 'admin_page_display' ),
            'submenu' => 'options-general.php'
            ) );

        if ( self::$props['menu_type'] == 'main' ){

            self::$options_page =add_menu_page(
					$params['title'],
					$params['menu_title'],
					$params['permission'],
					$params['namespace'],
					$params['template']
				);

        } elseif ( self::$props['menu_type'] == 'plugin' ) {

			self::$options_page = add_submenu_page(
						$params['submenu'],
						$params['title'],
						$params['menu_title'],
						$params['permission'],
						$params['namespace'],
						$params['template']
					);

        } elseif ( self::$props['menu_type'] == 'theme' ) {

           
			self::$options_page = add_theme_page(
						$params['title'],
						$params['menu_title'],
						$params['permission'],
						$params['namespace'],
						$params['template']
					);
        }
		
		// Include CMB CSS in the head to avoid FOUC, called here as we need the screen ID
		add_action( 'admin_print_styles-' . self::$options_page , array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		
    }
	
	/**
	 * ADD CSS
	 * Adds a couple of rules to clean up WP styles if tabs are included
	 *
	 * @since 1.0.0
	 */
	public function add_css() {

	}
	
	/**
	 * ADD SCRIPTS
	 * Add WP's metabox script, either by itself or as dependency of the tabs script. Added only to this options page.
	 * If you role your own script, note the localized values being passed here.
	 *
	 * @param string $hook_suffix
	 * @throws \Exception
	 *
	 * @since 1.0.1 Always add postbox toggle, removed toggle from tab handler JS
	 * @since 1.0.0
	 */
	public function add_scripts( $hook_suffix ) {

	}
	

	  /**
     * Admin page markup. Mostly handled by CMB
     * @since  0.1.0
     */
    public function admin_page_display() {
    	$option_tabs = self::option_fields(); //get all option tabs
    	$tab_forms = array();     	   	
        ?>
        <div class="wrap cmb_options_page <?php echo $this->key; ?>">        	
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <?php
				// allows filter to inject HTML before the form
				echo apply_filters( self::$props['key'].'_before_form', '' );
			?>	
            <!-- Options Page Nav Tabs -->           
            <h2 class="nav-tab-wrapper">
            	<?php foreach ($option_tabs as $option_tab) :
            		$tab_slug = $option_tab['id'];
            		$nav_class = 'nav-tab';
            		if ( isset($_GET['tab']) && $tab_slug == $_GET['tab'] ) {
            			$nav_class .= ' nav-tab-active'; //add active class to current tab
            			$tab_forms[] = $option_tab; //add current tab to forms to be rendered
            		}
            	?>            	
            	<a class="<?php echo $nav_class; ?>" href="<?php menu_page_url( self::$props['key'] ); ?>&tab=<?php echo $tab_slug; ?>"><?php esc_attr_e($option_tab['title']); ?></a>
            	<?php endforeach; ?>
            </h2>
            <!-- End of Nav Tabs -->

            <?php foreach ($tab_forms as $tab_form) : //render all tab forms (normaly just 1 form) ?>
            <div id="<?php esc_attr_e($tab_form['id']); ?>" class="group">
            	<?php cmb2_metabox_form( $tab_form, self::$props['key'], array('save_button'=>self::$props['savetxt'])); ?>
            </div>
            <?php endforeach; 
			
			// allows filter to inject HTML after the form
			echo apply_filters( self::$props['key'].'_after_form', '' );
			?>	
        </div>
        <?php
    }
	
	/**
     * Defines the theme option metabox and field configuration
     * @since  0.1.0
     * @return array
     */
    public function option_fields() {

       self::$props['settings'] = apply_filters( self::$props['key'] . '_settings', self::$props['settings'] );
	   
	   return self::$props['settings'];
    }
	
	/**
	 * Register settings notices for display
	 *
	 * @since  0.1.0
	 * @param  int   $object_id Option key
	 * @param  array $updated   Array of updated fields
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== self::$props['key'] || empty( $updated ) ) {
			return;
		}
		add_settings_error( self::$props['key'] . '-notices', '', 'Settings updated.', 'updated' );
		settings_errors( self::$props['key'] . '-notices' );
	}

}
 
//include 'example.php'; 
 
