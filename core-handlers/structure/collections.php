<?php

namespace aw2\collection;


\aw2_library::add_service('collection.module_exists','Used to check a module of a collection. Cannot be called directly',['namespace'=>__NAMESPACE__]);

function module_exists($atts,$content,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	$module=>null
	), $atts) );

	$return_value=true;
	$return_value=\aw2_library::get_module($shortcode['collection'],$module,);	

	if($return_value==null) return false;
}

/*
function aw2_collections_add($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'desc'=>null,
	'post_type'=>null,
	
	), $atts) );
	\aw2_library::add_collection($main,$atts,$desc);
}
*/

\aw2_library::add_service('collection','Handles Collections',['namespace'=>__NAMESPACE__]);

function unhandled($atts,$content,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'template'=>null
	), $atts) );
	$pieces=$shortcode['tags_left'];
	if(!count($pieces)>=1)return 'Module not defined';
	$module=array_shift($pieces);
	
	$t=implode('.',$pieces);

	if($template)
		$return_value=\aw2_library::module_forced_run($shortcode['collection'],$module,$template,$content,$atts);	
	else
		$return_value=\aw2_library::module_run($shortcode['collection'],$module,$t,$content,$atts);		
	
	if(is_string($return_value))$return_value=trim($return_value);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	return $return_value;
}

\aw2_library::add_service('collection.register','Register a Collection.',['namespace'=>__NAMESPACE__]);

function register($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'desc'=>null
	), $atts) );
	
	unset($atts['main']);
	unset($atts['desc']);
	

	\aw2_library::add_service($main,$desc,$atts);
	
}


\aw2_library::add_service('collection.run','Used to call a module of a collection. Cannot be called directly',['namespace'=>__NAMESPACE__]);

function run($atts,$content,$shortcode){
	
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'main'=>null,
		'template'=>null,
		'module'=>null
	), $atts) );
	
	if($main===null)return 'Module/Template must be provided';	

	if(!$module && $template)$return_value=\aw2_library::module_forced_run($shortcode['collection'],$main,$template,$content,$atts);	
	if(!$module && !$template)$return_value=\aw2_library::module_run($shortcode['collection'],$main,null,$content,$atts);
	if($module && !$template)$return_value=\aw2_library::module_run($shortcode['collection'],$module,$main,$content,$atts);	
		
	if(is_string($return_value))$return_value=trim($return_value);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	//if(is_object($return_value))$return_value='Object';
	
	return $return_value;
}

\aw2_library::add_service('collection.include','Used to include a module of a collection. Cannot be called directly',['func'=>'_include','namespace'=>__NAMESPACE__]);

function _include($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	$return_value=\aw2_library::module_include($shortcode['collection'],$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('collection.include_raw','Used to include a module of a collection as a string. Cannot be called directly',['namespace'=>__NAMESPACE__]);

function include_raw($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts ) );
	$return_value=\aw2_library::module_include_raw($shortcode['collection'],$main);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('collection.export_compare','Exports a collection for compare. Needs a post_type',['namespace'=>__NAMESPACE__]);

function export_compare($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts ) );

	$post_type=$main;	
	if(!$post_type)return 'Post Type not provided';
	
	$posts=get_posts('post_type=' . $post_type . '&posts_per_page=-1&post_status=publish');
	$upload_dir=LOG_PATH;
	
	$backup_path=$upload_dir . '/collection_backup';
	if (!file_exists($backup_path)) {
		mkdir($backup_path, 0777, true);
	}
	
	$collection_directory=$backup_path . '/' . $post_type;
	delete_files($collection_directory);
	
	if (!file_exists($collection_directory)) {
		mkdir($collection_directory, 0777, true);
	}
	
 
	foreach ( $posts as $post ){
		$file = $collection_directory . '/' . $post->post_name . '.html';
		file_put_contents($file,$post->post_content);
	}
	return 'Done. Taken Backup';	
}


\aw2_library::add_service('collection.export','Exports a collection. Needs a post_type',['namespace'=>__NAMESPACE__]);

function export($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts ) );

	$post_type=$main;	
	if(!$post_type)return 'Post Type not provided';
	
	$posts=get_posts('post_type=' . $post_type . '&posts_per_page=-1&post_status=publish');
	$upload_dir=LOG_PATH;
	
	$backup_path=$upload_dir . '/collection_backup';
	if (!file_exists($backup_path)) {
		mkdir($backup_path, 0777, true);
	}
	
	$collection_directory=$backup_path . '/' . $post_type;
	delete_files($collection_directory);
	
	if (!file_exists($collection_directory)) {
		mkdir($collection_directory, 0777, true);
	}
	
 
	foreach ( $posts as $post ){
		$one_page=new \stdClass();
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

\aw2_library::add_service('collection.import','Imports a collection. Needs a post_type',['namespace'=>__NAMESPACE__]);

function import($atts,$content=null,$shortcode){
	global $wbdb;
	
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
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


function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

        foreach( $files as $file ){
            delete_files( $file );      
        }

        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}
