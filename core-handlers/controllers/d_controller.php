<?php
namespace aw2\controllers;

\aw2_library::add_service('controllers.d','D Controller',['namespace'=>__NAMESPACE__]);


function d($atts,$content=null,$shortcode=null){

	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)exit();
	
	$o=$atts['o'];
	//\util::var_dump($atts);
	//if(empty($o->pieces))exit();
	\controllers::set_cache_header('no'); // HTTP 1.1.
	
	if(!isset($_REQUEST['module'])) die('Invalid Request');
	if(!isset($_REQUEST['post_type'])) die('Invalid Request');
	
	$module=$_REQUEST['module'];
	$post_type=$_REQUEST['post_type'];
	
	if(empty($module)||empty($post_type)){
		
		die('Invalid Request');
	}
	$pos =  isset($_REQUEST['pos'])?intval($_REQUEST['pos']):'';
	
//?module=error-log-checks&post_type=samples_modules&pos=1
//https://v4.loantap.in/d?module=error-log-checks&post_type=samples_modules&pos=1	
	//\util::var_dump($module);
	//\util::var_dump($post_type);
	
	\aw2_library::get_post_from_slug($module,$post_type,$post);
	//\util::var_dump($post);
	//exit();
	header("Location: " . site_url("wp-admin/post.php?post=" . $post->ID  . "&action=edit&line_pos=".$pos));
	
	exit();
}


