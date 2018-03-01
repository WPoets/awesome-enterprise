<?php

if( ! class_exists( 'WP_List_Table' ) ) {
 require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Awesome_Apps_List_Table extends WP_List_Table {
	
	private $props = array(
		"list_post_type"=>""
	);
	/**
     * Constructor
     * @since 0.1.0
     */
	public function __construct( $args ) {
		parent::__construct();
		$this->props = wp_parse_args( $args, $this->$props );
	}
	
	
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		//$this->process_bulk_action();
		//$this->_column_headers = $this->get_column_info();
		
		$args = array(
		  'posts_per_page'   => -1,
		  'orderby'          => 'date',
		  'order'            => 'DESC',
		  'post_type'        => array($this->props['list_post_type']),
		  'post_status'      => 'publish',
		  'suppress_filters' => true 
		);
		$posts_array = get_posts( $args );
		
		$post_items=array();
		foreach($posts_array as $key=>$p){         
			
			$post_items[]=array(
			'title' => '<a href="'.get_edit_post_link($p->ID).'">'.$p->post_title.'</a>',
			'author'    => $p->post_author,
			'date'    => 'Last Modified '.date(' d/m/Y H:i:s',strtotime($p->post_date)),
			'action'  => '<a class="delete-trigger" data-id="'.$p->ID.'" data-slug="'.$p->post_name.'" href="#">Delete</a>'
			);
		}

		$this->items = $post_items;
	}
	
	function get_columns(){
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'title' 	=> 'Title',
			'author'  => 'Author',
			'date'    => 'Date',
			'action'  => 'Action'
		);
		return $columns;
	}  
	
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
		  case 'title':
		  case 'author':
		  case 'date':
		  case 'action':
			return $item[ $column_name ];
		  default:
			return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
	
	function column_name( $item ) {

	  // create a nonce
	  $delete_nonce = wp_create_nonce( 'sp_delete_customer' );

	  $title = '<strong>' . $item['name'] . '</strong>';

	  $actions = [
		'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
	  ];

	  return $title . $this->row_actions( $actions );
	}
	
	public function get_hidden_columns(){
		  return array();
	}
	
	public function get_sortable_columns() {
	  $sortable_columns = array(
		'title' => array( 'title', true ),
		'date' => array( 'date', false )
	  );

	  return $sortable_columns;
	}
	
	public function get_bulk_actions() {
	  $actions = [
		'bulk-delete' => 'Delete'
	  ];

	  return $actions;
	}
	
	function column_cb( $item ) {
	  return sprintf(
		'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
	  );
	}
	
	function extra_tablenav( $which ){
		if('top'==$which){
			echo '<a href="'.admin_url('post-new.php?post_type='.$this->props['list_post_type']).'" class="page-title-action" style="margin-left:0;top:10px;padding:5px 8px;">Add New Page Layout</a>';
		}
	}
}