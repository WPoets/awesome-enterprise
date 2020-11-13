<?php

namespace aw2\posts;

\aw2_library::add_service('posts.export','Exports posts. Needs a posts array',['namespace'=>__NAMESPACE__]);

function export($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'posts'=>null,
	'path'=>null	
	), $atts ) );

	if(!$posts)return 'Posts not provided';
	if(!$path)return 'Path not provided';
	
	$backup_path=LOG_PATH . '/' . $path;
	

	if (is_dir($backup_path)) {
//		delete_files($backup_path);
	}
	
	mkdir($backup_path, 0777, true);
	
	
 
	foreach ( $posts as $post ){
		echo 'inside';
		$collection_directory=$backup_path . '/' . $post['post_type'];
		if (!file_exists($collection_directory)) {
			mkdir($collection_directory, 0777, true);
		}
		$file = $collection_directory . '/' . $post['post_name'] . '.html';
		file_put_contents($file,$post['post_content']);
	}
	return 'Done. Taken Backup';	
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