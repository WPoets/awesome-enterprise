<?php

add_action('wp_loaded', 'piklist_wordpress_helpers_init');

function piklist_wordpress_helpers_init(){
	
	//aw2_library::d();
	//util::var_dump(aw2_library::get('site_settings'));
	
	if(aw2_library::get('site_settings.featured_image_in_feed') == "on")
	{	
	  //case 'featured_image_in_feed':
	  add_filter('the_excerpt_rss', 'piklist_wordpress_helpers_featured_image');
	  add_filter('the_content_feed', 'piklist_wordpress_helpers_featured_image');
	}

	if(aw2_library::get('site_settings.all_options') == "on")
	{	
		//case 'all_options':
		add_action('admin_menu', 'piklist_wordpress_helpers_all_options_menu');
	}

	if(aw2_library::get('site_settings.enhanced_classes') == "on" )
	{	
	//case 'enhanced_classes':
	  add_filter('body_class', 'piklist_wordpress_helpers_body_class');
	  add_filter('post_class', 'piklist_wordpress_helpers_post_class');
	  add_action('wp_footer','piklist_wordpress_helpers_no_js');
	}

	if(aw2_library::get('site_settings.show_ids') == "on")
	{	
		//case 'show_ids':
		//add_action('init', 'piklist_wordpress_helpers_show_ids');
		piklist_wordpress_helpers_show_ids();
		//add_action('piklist_wordpress_helpers_admin_css', array('piklist_wordpress_helpers', 'column_id_width'), self::$filter_priority);
	}

	if(aw2_library::get('site_settings.private_site') == "on")
	{	
		//case 'private_site':
		add_action('wp', 'piklist_wordpress_helpers_redirect_to_login');
	}

	if( aw2_library::get('site_settings.redirect_to_home') == "on")
	{	
		//case 'redirect_to_home':
		add_action('login_redirect','piklist_wordpress_helpers_go_home', 10, 3);
	}

	if(aw2_library::get('site_settings.notice_admin') == "on")
	{	
		//case 'notice_admin':
		add_action('admin_notices', 'piklist_wordpress_helpers_notice_admin');
	}

	if(aw2_library::get('site_settings.mail_from') == "on")
	{	
	  //case 'mail_from':
	  add_filter('wp_mail_from', 'piklist_wordpress_helpers_mail_from');
	}

	if(aw2_library::get('site_settings.mail_from_name') == "on")
	{	
		//case 'mail_from_name':
		add_filter('wp_mail_from_name', 'piklist_wordpress_helpers_mail_from_name');
	}
	
	if( aw2_library::get('site_settings.edit_posts_per_page') == "on")
	{	
		add_filter('edit_posts_per_page', 'piklist_wordpress_helpers_edit_posts_per_page');
	}

	//case 'excerpt_box_height':
	add_action('piklist_wordpress_helpers_admin_css','piklist_wordpress_helpers_excerpt_box_height');
 
	//case 'show_system_information':
	add_filter('piklist_admin_pages', 'piklist_wordpress_helpers_system_admin_page');

	if(aw2_library::get('site_settings.delete_orphaned_meta') == "on" )
	{	
		//case 'delete_orphaned_meta':
		add_action('wp_scheduled_delete', 'piklist_wordpress_helpers_delete_orphaned_meta');
	}

	//disable emoji
	if( aw2_library::get('site_settings.disable_emojis') == "on")
	{	
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );	
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
	}
	
	if( aw2_library::get('site_settings.disable_embed') == "on")
	{	
		add_action( 'wp_footer', 'piklist_wordpress_helpers_deregister_scripts' );
	}
	//case 'search_post_types':
	//  add_filter('pre_get_posts', 'piklist_wordpress_helpers_set_search');          

}

function piklist_wordpress_helpers_deregister_scripts(){
  wp_deregister_script( 'wp-embed' );
}

function piklist_wordpress_helpers_system_admin_page($pages){
    $pages[] = array(
      'page_title' => __('System Information')
      ,'menu_title' => __('System', 'piklist')
      ,'sub_menu' => 'tools.php'
      ,'capability' => 'manage_options'
      ,'menu_slug' => 'piklist_wp_helpers_system_information'
      ,'icon_url' => plugins_url('piklist/parts/img/piklist-icon.png') 
      ,'icon' => 'piklist-page'
    );

    return $pages;
  }
function piklist_wordpress_helpers_edit_posts_per_page($posts_per_page)
{
  return 50;
  //return self::$options['edit_posts_per_page'];
}

function piklist_wordpress_helpers_excerpt_box_height(){
  //echo '#excerpt { height:' . self::$options['excerpt_box_height'] . 'px; }' . PHP_EOL;
  echo '#excerpt { height:50px; }' . PHP_EOL;
}
  
function piklist_wordpress_helpers_featured_image($content){
  global $post;
    
  if (has_post_thumbnail($post->ID))
  {
    $content = '<p class="entry-featured-image entry-featured-image-' . $post->ID . '">' . get_the_post_thumbnail($post->ID) . '</p>' . $content;
  }
    
  return $content;
}
  
function piklist_wordpress_helpers_delete_orphaned_meta(){
  global $wpdb;
  
  $tables = array(
    array(
      'type' => 'post'
      ,'object' => $wpdb->posts
      ,'meta' => $wpdb->postmeta
    )
    ,array(
      'type' => 'user'
      ,'object' => $wpdb->users
      ,'meta' => $wpdb->usermeta
    )
    ,array(
      'type' => 'term'
      ,'object' => $wpdb->terms
      ,'meta' => $wpdb->termmeta
    )
  );

  foreach ($tables as $table)
  {
    $wpdb->query($wpdb->prepare("DELETE meta FROM %s meta LEFT JOIN %s object ON object.ID = %s WHERE object.ID IS NULL", $table['meta'], $table['object'], 'meta.' . $table['type'] . '_id'));
  }
} 

function piklist_wordpress_helpers_all_options_menu(){
  if (current_user_can('manage_options'))
  {
    global $submenu;
    
    $all_options_menu = array('All','manage_options','options.php');
    array_unshift($submenu['options-general.php'], $all_options_menu);
  }
}
  
function piklist_wordpress_helpers_go_home($redirect_to, $request, $user){
  return home_url();
} 

function piklist_wordpress_helpers_mail_from($old)
{
	
	return aw2_library::get('site_settings.set_wp_mail_from');
  //return self::$options['mail_from'];
}

function piklist_wordpress_helpers_mail_from_name($old)
{
	
	return aw2_library::get('site_settings.wp_mail_from_name');
}  
function piklist_wordpress_helpers_redirect_to_login()
{
  if(!is_user_logged_in())
  {
    wp_redirect(home_url() . '/wp-login.php', 302);
    exit;
  }
}
function piklist_wordpress_helpers_notice_admin()
{
    //piklist_wordpress_helpers_admin_notice(self::$options['admin_message'], false); 
}

function piklist_wordpress_helpers_helpers_css()
{
    //piklist_wordpress_helpers_admin_notice(self::$options['admin_message'], false); 
}

/* function piklist_wordpress_helpers_set_search($query)
{
  if (is_admin())
  {
    return;
  }
  elseif ($query->is_search)
  {
    $value = self::$options['search_post_types'];
    $value = is_array($value) ? $value : array($value);
    $query->set('post_type', $value);
  }
  return $query;
}
 */  
function piklist_wordpress_helpers_clean_phpinfo()
{
  ob_start();
  
    phpinfo();
  
    $phpinfo = ob_get_contents();
  
  ob_end_clean();

  $phpinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$phpinfo);
  $phpinfo = str_ireplace('width="600"','class="wp-list-table widefat"', $phpinfo);

  echo $phpinfo;
}

function piklist_wordpress_helpers_admin_notice($message, $error = false)
{
  piklist('shared/admin-notice', array(
    'type' => $error ? 'error' : 'updated'
    ,'notices' => $message
  ));
}

function piklist_wordpress_helpers_login_notice($message, $error = false)
{
  piklist('shared/admin-login-message', array(
    'type' => $error ? 'error' : 'updated'
    ,'notices' => $message
  ));
}

  
    // @credit http://themehybrid.com/
function piklist_wordpress_helpers_body_class($classes)
{
  global $post, $wp_roles, $blog_id;

  $extended_classes = array();
  
  $extended_classes[] = 'no-js';


  if (is_singular())
  {
    $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_taxonomy_class($classes));
    $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_date_class($classes));

    if (has_post_thumbnail())
    {
      $extended_classes[] = 'has-post-thumbnail';
    }

    if (is_multi_author())
    {
      $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_author_class($classes));
    }
  }
  
  if (is_archive())
  {
    if (is_year())
    {
      $extended_classes[] = 'year';
    }

    if (is_month())
    {
      $extended_classes[] = 'month';
    }

    if (is_day())
    {
      $extended_classes[] = 'day';
    }
  }

  if (is_user_logged_in())
  {
    $current_user = wp_get_current_user();
    $roles = $current_user->roles;
    $role = array_shift($roles);
    $extended_classes[] = ($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role]) : false;
  }

  if (is_multisite())
  {       
     $sitename = str_replace(' ', '-', strtolower(get_bloginfo('name')));
     $extended_classes[] = 'multisite';
     $extended_classes[] = 'site-' . $blog_id;
     $extended_classes[] = 'site-' . $sitename;
  }

  if (function_exists('bp_get_the_body_class'))
  {
    $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_buddypress_class($classes));
  }

  $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_browser_class($classes));

  $classes = array_merge((array) $classes, (array) $extended_classes);

  $classes = array_map('strtolower', $classes);
  $classes = sanitize_html_class($classes);
  $classes = array_unique($classes);

  return $classes;
}


function piklist_wordpress_helpers_post_class($classes = '', $post_id = null)
{
  global $post, $wp_query;

  $extended_classes = array();

  if(!is_singular())
  {
    $extended_classes[] = (($wp_query->current_post + 1) % 2) ? 'odd' : 'even';
  }

  $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_date_class($classes));
  $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_taxonomy_class($classes));

  if (has_post_thumbnail())
  {
    $extended_classes[] = 'has-post-thumbnail';
  }

  if (is_multi_author())
  {
    $extended_classes = array_merge($extended_classes, piklist_wordpress_helpers_author_class($classes));
  }

  if (post_type_supports($post->post_type, 'excerpt') && has_excerpt())
  {
    $extended_classes[] = 'has-excerpt';
  }

  $classes = array_merge((array) $classes, (array) $extended_classes);
  $classes = array_map('strtolower', $classes);
  $classes = sanitize_html_class($classes);
  $classes = array_unique($classes);

  return $classes;
}

// @credit: http://wordpress.org/extend/plugins/genesis-js-no-js/
// @credit: http://core.svn.wordpress.org/trunk/wp-admin/admin-header.php
function piklist_wordpress_helpers_no_js()
{ ?>
  <script type="text/javascript">
    document.body.className = document.body.className.replace('no-js','js');
  </script>
<?php
}
  
function piklist_wordpress_helpers_show_ids() 
  {
    if (is_multisite())
    {
      add_action( 'manage_sites_custom_column', 'piklist_wordpress_helpers_edit_column_echo', 10, 2);
      add_action( 'manage_blogs_custom_column', 'piklist_wordpress_helpers_edit_column_echo', 10, 2);
      add_filter( 'wpmu_blogs_columns', 'piklist_wordpress_helpers_edit_column_header', 10, 2);
    }

    add_filter('request','piklist_wordpress_helpers_column_orderby_id');

    add_action('manage_users_custom_column', 'piklist_wordpress_helpers_edit_column_return', 10, 3);
    add_filter('manage_users_columns', 'piklist_wordpress_helpers_edit_column_header', 10, 2);
    add_filter('manage_users_sortable_columns', 'piklist_wordpress_helpers_register_sortable_column');

    add_action('manage_link_custom_column', 'piklist_wordpress_helpers_edit_column_echo', 10, 2);
    add_filter('manage_link-manager_columns', 'piklist_wordpress_helpers_edit_column_header', 10, 2);
    add_filter('manage_link-manager_sortable_columns', 'piklist_wordpress_helpers_register_sortable_column'); 

    $post_types = array(
      'posts' => 'post'
      , 'pages' => 'page'
      , 'media' => 'media'
    );

    foreach ($post_types as $post_type => $value)
    {
      add_action('manage_' . $post_type . '_custom_column', 'piklist_wordpress_helpers_edit_column_echo', 10, 2);
      add_filter('manage_' . $post_type . '_columns', 'piklist_wordpress_helpers_edit_column_header', 10, 2);
      add_filter('manage_edit-' .  $value . '_sortable_columns', 'piklist_wordpress_helpers_register_sortable_column', 10);
    }
    
    if ($custom_post_types = get_post_types(array('_builtin' => false)))
    {
      foreach ($custom_post_types  as $custom_post_type)
      {
        add_action('manage_' . $custom_post_type . '_custom_column', 'piklist_wordpress_helpers_edit_column_echo', 10, 2);
        add_filter('manage_' . $custom_post_type . '_columns', 'piklist_wordpress_helpers_edit_column_header', 10, 2);
        add_filter('manage_edit-' .  $custom_post_type . '_sortable_columns', 'piklist_wordpress_helpers_register_sortable_column', 10);
      }
    }

    $taxonomies_builtin = array(
      'category'
      , 'post_tag'
    );

    foreach ($taxonomies_builtin as $taxonomy_builtin)
    {
      add_action('manage_' . $taxonomy_builtin . '_custom_column', 'piklist_wordpress_helpers_edit_column_return', 10, 3);
      add_filter('manage_edit-' . $taxonomy_builtin . '_columns', 'piklist_wordpress_helpers_edit_column_header', 10, 2);
      add_filter('manage_edit-' . $taxonomy_builtin . '_sortable_columns', 'piklist_wordpress_helpers_register_sortable_column', 10);
    }
    
    if ($custom_taxonomies = get_taxonomies(array('_builtin' => false)))
    {
      foreach ($custom_taxonomies  as $custom_taxonomy)
      {
        add_action('manage_' . $custom_taxonomy . '_custom_column', 'piklist_wordpress_helpers_edit_column_return', 10, 3);
        add_filter('manage_edit-' . $custom_taxonomy . '_columns', 'piklist_wordpress_helpers_edit_column_header', 10, 2);
        add_filter('manage_edit-' . $custom_taxonomy . '_sortable_columns', 'piklist_wordpress_helpers_register_sortable_column', 10);
      }
    }
  }

function piklist_wordpress_helpers_column_orderby_id($vars)
{
  if (is_admin())
  {
    if (isset($vars['orderby']) && 'piklist_id' == $vars['orderby'])
    {
      $vars = array_merge($vars, array(
                          'meta_key' => 'piklist_id',
                          'orderby' => 'meta_value_num'
                          ));
    }
  }
  return $vars;
}

function piklist_wordpress_helpers_register_sortable_column($columns)
{
  $columns['piklist_id'] =  __('ID', 'wp-helpers');
 
  return $columns;
}


function piklist_wordpress_helpers_edit_column_header($columns)
{
  //$column_id = array('piklist_id' =>  __('ID', 'wp-helpers'));

  //$columns = array_slice( $columns, 0, 1, true ) + $column_id + array_slice( $columns, 1, NULL, true );
   $columns['piklist_id'] =  __('ID', 'wp-helpers');
 
   return $columns;
} /* */

function piklist_wordpress_helpers_edit_column_echo($column, $value)
{
  switch ($column)
  {
    case 'piklist_id' :

      echo $value;

      break;
  }
}

function piklist_wordpress_helpers_edit_column_return($value, $column, $userid)
{
  switch ($column)
  {
    case 'piklist_id':
      
      $value = (int) $userid;
    
    break;

    default:

      $value = '';

    break;
  }
  
  return $value;
}


  
  function piklist_site_inventory()
  {
    global $wp_version, $wpdb;

    $theme_data = wp_get_theme();
    $theme = $theme_data->Name . ' ' . $theme_data->Version  . '( ' . strip_tags($theme_data->author) . ' )';

    $page_on_front_id = get_option('page_on_front'); 
    $page_on_front = get_the_title($page_on_front_id) . ' (#' . $page_on_front_id . ')';

    $page_for_posts_id = get_option('page_for_posts'); 
    $page_for_posts = get_the_title($page_for_posts_id) . ' (#' . $page_for_posts_id . ')';

    $table_prefix_length = strlen($wpdb->prefix);
    if (strlen( $wpdb->prefix )>16 )
    {
      $table_prefix_status = sprintf(__('%1$sERRO: Too long%2$s', 'wp-helpers'), ' (', ')');
    }
    else
    {
      $table_prefix_status = sprintf(__('%1$sAcceptable%2$s', 'wp-helpers'), ' (', ')');
    };

    $wp_debug = defined('WP_DEBUG') ? WP_DEBUG ? __('Enabled', 'wp-helpers') . "\n" : __('Disabled', 'wp-helpers') . "\n" : __('Not set', 'wp-helpers');

    $php_safe_mode = ini_get('safe_mode') ? __('Yes', 'wp-helpers') : __('No', 'wp-helpers');
    $allow_url_fopen = ini_get('allow_url_fopen') ? __('Yes', 'wp-helpers') : __('No', 'wp-helpers'); 

    $plugins_active = array();
    $plugins = get_plugins();
    $active_plugins = get_option( 'active_plugins', array() );

    foreach ($plugins as $plugin_path => $plugin)
    {
      if (in_array($plugin_path, $active_plugins))
      {
        $plugins_active[] = $plugin['Name'] . ': ' . $plugin['Version'] . ' (' . strip_tags($plugin['Author']) . ')';
      }
    }

    // Widgets
    $all_widgets = '';
    $sidebar_widgets = '';
    $current_sidebar = '';
    $active_widgets = get_option('sidebars_widgets');
    
    if (is_array($active_widgets) && count($active_widgets))
    {
      foreach ($active_widgets as $sidebar => $widgets)
      {
        if (is_array($widgets))
        {
          if ($sidebar != $current_sidebar)
          {
            $sidebar_widgets .= $sidebar . ': ';
            $current_sidebar = $sidebar;
          }
          
          if (count($widgets))
          {
            $sidebar_widgets .= implode(', ', $widgets);
            $all_widgets[] = $sidebar_widgets;
          }
          else
          {
            $sidebar_widgets .= __('(none)', 'piklist');
            $all_widgets[] = $sidebar_widgets;
          }
        
          $sidebar_widgets = '';
        }
      }
    }

    piklist::render('shared/system-info', array(
      'theme' => $theme
      ,'wp_version' => get_bloginfo('version')
      ,'multisite' => is_multisite() ? __('WordPress Multisite', 'piklist') : __('WordPress (single user)', 'piklist')
      ,'permalinks' => get_option('permalink_structure') == '' ? $permalinks = __('Query String (index.php?p=123)', 'piklist') : $permalinks = __('Pretty Permalinks', 'piklist')
      ,'page_on_front' => $page_on_front
      ,'page_for_posts' => $page_for_posts
      ,'table_prefix_status' => $table_prefix_status
      ,'table_prefix_length' => $table_prefix_length
      ,'wp_debug' => $wp_debug
      ,'users_can_register' => get_option('users_can_register') == '1' ?  __('Yes', 'piklist') : __('No', 'piklist')
      ,'enable_xmlrpc' => get_option('enable_xmlrpc') == '1' ?  __('Yes', 'piklist') : __('No', 'piklist')
      ,'enable_app' => get_option('enable_app') == '1' ? __('Yes', 'piklist') : __('No', 'piklist')
      ,'blog_public' => get_option('blog_public') == '1' ? __('Public', 'piklist') : __('Private', 'piklist')
      ,'rss_use_excerpt' => get_option('rss_use_excerpt') == '1' ? __('Summaries', 'piklist') : __('Full Content', 'piklist')
      ,'php_safe_mode' => $php_safe_mode
      ,'allow_url_fopen' => $allow_url_fopen
      ,'plugins_active' => $plugins_active
      ,'sidebar_widgets' => $all_widgets
    ));
  }

 
  // @credit http://www.mattvarone.com/wordpress/browser-body-classes-function/
function piklist_wordpress_helpers_browser_class() {
    global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

    $classes = array();

    if ($is_lynx)
    {
      $classes[] = 'lynx';
    }
    elseif ($is_gecko)
    {
      $classes[] = 'gecko';
    }
    elseif ($is_opera)
    {
      $classes[] = 'opera';
    }
    elseif ($is_NS4)
    {
      $classes[] = 'ns4';
    }
    elseif ($is_safari)
    {
      $classes[] = 'safari';
    }
    elseif ($is_chrome)
    {
      $classes[] = 'chrome';
    }
    elseif ($is_IE)
    {
      $classes[] = 'ie';
      if (preg_match('/MSIE ([0-9]+)([a-zA-Z0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $browser_version))
      {
        $classes[] = 'ie-' . $browser_version[1];
      }
    }
    else
    {
      $classes[] = 'browser-unknown';
    }

    if ($is_iphone)
    {
      $classes[] = 'iphone';
    }
    
    return $classes;
}

function piklist_wordpress_helpers_date_class() {
    $classes = array();
    $classes[] = get_the_date('F');
    $classes[] = 'day-' . get_the_date('j');
    $classes[] = get_the_date('Y');
    $classes[] = 'time-' . get_the_date('a');

    return $classes;
}

function piklist_wordpress_helpers_author_class() {
    global $post;

    $classes = array();

    $author_id = $post->post_author;
    $classes[] = 'author-' . get_the_author_meta('user_nicename', $author_id);

    return $classes;
}

function piklist_wordpress_helpers_taxonomy_class() {
    global $post, $post_id;

    $classes = array();
        
    $post = get_post($post->ID);
    $post_type = $post->post_type;

    $taxonomies = get_object_taxonomies($post_type);
    foreach ($taxonomies as $taxonomy)
    {
      $terms = get_the_terms($post->ID, $taxonomy);
      if (!empty($terms))
      {
        $output = array();
        foreach ($terms as $term)
        {
          $classes[] .= $term->taxonomy . '-' . $term->name ;
          if (is_taxonomy_hierarchical($term->taxonomy))
          {
            $counter = 1;
            while (!is_wp_error(get_term($term->parent, $term->taxonomy)))
            {
              $term_parent = get_term($term->parent, $term->taxonomy);
              $classes[] .= $term->taxonomy . '-' . 'level-' . $counter . '-' . $term_parent->name;
              $classes[] .= $term->taxonomy . '-hierarchical-' . $term_parent->name;
              $term = $term_parent;
              $counter++;
            }
          }
        }              
      }
    }

    return $classes;
}

function piklist_wordpress_helpers_buddypress_class() {
    $classes = array();
    $classes = bp_get_the_body_class();

    return $classes;
}

/**
 * Filter function used to remove the tinymce emoji plugin.
 * 
 * @param    array  $plugins  
 * @return   array             Difference betwen the two arrays
 */
function disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}