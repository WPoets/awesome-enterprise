<?php
//////// Collections Library ///////////////////
aw2_library::add_library('collections','Collections Library');

function aw2_collections_add($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	'desc'=>null,
	'post_type'=>null,
	
	), $atts) );
	aw2_library::add_collection($main,$atts,$desc);
}

function aw2_collection_unhandled($atts,$content,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'template'=>null
	), $atts) );
	$pieces=$shortcode['tags'];
	if(!count($pieces)>=1)return 'Module not defined';
	array_shift($pieces);
	$module=array_shift($pieces);
	
	$t=implode('.',$pieces);

	if($template)
		$return_value=aw2_library::module_forced_run($shortcode['handler'],$module,$template,$content,$atts);	
	else
		$return_value=aw2_library::module_run($shortcode['handler'],$module,$t,$content,$atts);		
	
	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	return $return_value;
}

function aw2_collection_run($atts,$content,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'main'=>null,
		'template'=>null,
		'module'=>null
	), $atts) );
	if($main==null)return 'Module/Template must be provided';	

	if(!$module && $template)$return_value=aw2_library::module_forced_run($shortcode['handler'],$main,$template,$content,$atts);	
	if(!$module && !$template)$return_value=aw2_library::module_run($shortcode['handler'],$main,null,$content,$atts);
	if($module && !$template)$return_value=aw2_library::module_run($shortcode['handler'],$module,$main,$content,$atts);	
		
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}

function aw2_collection_include($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	$return_value=aw2_library::module_include($shortcode['handler'],$main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

//////// Collections Library ///////////////////
aw2_library::add_library('collection','Collections Library');

function aw2_collection_export($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null
	), $atts ) );

	$post_type=$main;	
	if(!$post_type)return 'Post Type not provided';
	
	$posts=get_posts('post_type=' . $post_type . '&posts_per_page=-1&post_status=publish');
	$upload_dir=wp_upload_dir()['basedir'];
	
	$backup_path=$upload_dir . '/collection_backup';
	if (!file_exists($backup_path)) {
		mkdir($backup_path, 0777, true);
	}
	
	$collection_directory=$backup_path . '/' . $post_type;
	deleteDir($collection_directory);
	
	if (!file_exists($collection_directory)) {
		mkdir($collection_directory, 0777, true);
	}
	

	foreach ( $posts as $post ){
		$one_page=new stdClass();
		$one_page->post_name=$post->post_name;
		$one_page->post_content=$post->post_content;
		$one_page->post_title=$post->post_title;
		$one_page->post_type=$post->post_type;
		$one_page_json=json_encode($one_page);
		$file = $collection_directory . '/' . $post->post_name . '.json';
		file_put_contents($file,$one_page_json);
	}
	return 'Done. Taken Backup';	
}

function aw2_collection_import($atts,$content=null,$shortcode){
	global $wbdb;
	
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	'overwrite'=>'no'
	), $atts ) );
	$post_type=$main;	
	if(!$post_type)return 'Post Type not provided';

	global $wpdb;
	$sql="select post_name,ID from  ".$wpdb->posts."  where post_status='publish' and post_type='" . $post_type . "'";
	$posts = $wpdb->get_results($sql, 'ARRAY_A');	

	foreach($posts as $post){
		$post_names[$post['post_name']]['post_name']=$post['post_name'];
		$post_names[$post['post_name']]['ID']=$post['ID'];
	}
	
	$upload_dir=wp_upload_dir()['basedir'];
	$collection_directory=$upload_dir . '/collection_backup' . '/' . $post_type;
	
	if (!file_exists($collection_directory)) {
		return 'Collection Folder Not found';
	}
	
	$files = scandir($collection_directory);
	foreach($files as $file){
		if( !is_file($collection_directory . '/' . $file) )continue;
		
		$name=str_replace('.json','',$file);
		if(isset($post_names[$name]) && $overwrite=='no'){
			
		}
		else{
			$json=file_get_contents($collection_directory . '/' . $file);
			$arr=json_decode($json);
			$args=array();
			$args['post_name']=$arr->post_name;
			$args['post_title']=$arr->post_title;
			$args['post_type']=$post_type;
			$args['post_content']=$arr->post_content;
			$args['post_status']='publish';
			if(isset($post_names[$name])){
				wp_delete_post($post_names[$name]['ID']);
				echo '<div/>Deleted: ' . $post_names[$name]['post_name'] . '</div>';
			}
			$return_value= wp_insert_post($args,true);
			echo '<div>Inserted: ' . $name . '</div>';
		};
	}
	return 'Done. Imported';	
}


