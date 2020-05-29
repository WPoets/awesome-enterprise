<?php

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class wp_posts extends \WP_List_Table {
    public $roleobj;
    public $appslug;
    function __construct() {
        parent::__construct( array(
            'singular' => 'Module',
            'plural'   => 'Modules',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No Modules Found', 'awesome' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'post_title':
                return $item->post_title;

            case 'post_name':
                return $item->post_name;

            case 'post_status':
                return $item->post_status;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'ID'      => __( 'Post Title', 'awesome' ),
            'post_name'      => __( 'Post Name', 'awesome' ),
            'post_status'      => __( 'Post Status', 'awesome' ),

        );

        return $columns;
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_ID( $item ) {

        $actions           = array();
        $actions['edit']   = sprintf( '<a href="%s" title="%s" target=_blank>%s</a>', admin_url( 'post.php?action=edit&post=' . $item->ID ), __( 'Edit this item', 'awesome' ), __( 'Edit', 'awesome' ) );

        return sprintf( '<a href="%1$s" target=_blank><strong>%2$s</strong></a> %3$s', admin_url( 'post.php?action=edit&post=' . $item->ID ), $item->post_title, $this->row_actions( $actions ) );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array( 'name', true ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'assign'  => __( 'Assign to ' . $_REQUEST['role'], 'awesome' ),
        );
        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        $checked = '';
        $postname = 'm_' . $this->appslug . '_' . $item->post_name;
        if( array_key_exists( $postname, $this->roleobj->capabilities ) ){
            $checked = 'checked=checked';
        }
        return sprintf(
            '<input type="hidden" value="" name="%s">
            <input type="checkbox" name="%s" value="true" %s/>', $postname, $postname, $checked
        );
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items($post_type = 'post') {
        $this->process_bulk_action();

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 50;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // $options = get_option( $_REQUEST['page'] . '-cap' );
        $roleobj = get_role($_REQUEST['role']);
        // $role = $_REQUEST['role'];

        $this->roleobj = $roleobj;
        $this->appslug = substr($_REQUEST['page'], 12);
        
        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
            'post_type' => $post_type
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $this->items  = aw_get_all_Module( $args );

        $this->set_pagination_args( array(
            'total_items' => aw_get_Module_count($post_type),
            'per_page'    => $per_page
        ) );

    }
    

    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();
        if(isset($_POST['rights_table'])){
            if('assign' == $action){
                $filtered = array_filter($_POST, function($key) {
                    return strpos($key, 'm_') === 0;
                },ARRAY_FILTER_USE_KEY);
            
                
                $roleobj = get_role($_REQUEST['role']);
                
                foreach($filtered as $key => $value){
                    if($value == ''){
                        $roleobj->remove_cap( $key );
                    }else{
                        if(!array_key_exists( $key, $roleobj->capabilities )){
                            $roleobj->add_cap( $key );
                        }
                    }
                }
            }
        }
        return;
    }
}

/**
 * Get all Module
 *
 * @param $args array
 *
 * @return array
 */
function aw_get_all_Module( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = $args['post_type'] . 'Module-all';
    $items     = wp_cache_get( $cache_key, 'awesome' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'posts where post_type = "' . $args['post_type'] . '" ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

        wp_cache_set( $cache_key, $items, 'awesome', 60 );
    }

    return $items;
}

/**
 * Fetch all Module from database
 *
 * @return array
 */
function aw_get_Module_count($post_type) {
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'posts where post_type = "' . $post_type . '"' );
}