<?php
namespace aw2;

\aw2_library::add_service('aw2.upload','Upload',['namespace'=>__NAMESPACE__]);
function upload($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract(\aw2_library::shortcode_atts( array(
		'main' => 'attach_to_post',
		'post_id' => null,
		'upload_element_id'=>null,
		'upload_file_url'=>null,
		'dir_name'=>null,
		'file_name'=>null,
		'default_extension'=>null,
		'overwrite_file'=>'no',
		'allowed_file_types'=>null,
		'set_featured'=>false,
		'woo_product_gal'=>false
	), $atts, 'aw2_upload' ) );

	// Allow certain file formats
	$allowed = array('gif', 'png' ,'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'csv');
	
	if($allowed_file_types != null || $allowed_file_types != ''){
		$allowed_ext = explode(',', $allowed_file_types);
		$allowed = $allowed_ext;
	}
	
	if($main=='attach_to_post'){
		// These files need to be included as dependencies when on the front end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
			
		if ( $_FILES ) { 
			$files = $_FILES[$upload_element_id];  
			
			if(is_array($files['name'])){
				$attach_ids = array();
				foreach ($files['name'] as $key => $value) {            
					if ($files['name'][$key]) { 
						$file = array( 
							'name' => $files['name'][$key],
							'type' => $files['type'][$key], 
							'tmp_name' => $files['tmp_name'][$key], 
							'error' => $files['error'][$key],
							'size' => $files['size'][$key]
						); 
						$_FILES = array ($upload_element_id => $file); 
						foreach ($_FILES as $file => $array) {           
							$newupload = aw2_handle_attachment($allowed,$upload_element_id,$post_id,$set_featured,$upload_file_url);
							$attach_ids[]=$newupload;
						}
					} 
				} 
				$return_value = $attach_ids;
				if($woo_product_gal==true)
					aw2_woo_set_prodcut_gallery($post_id,$attach_ids);
			}
			else{
				$return_value = aw2_handle_attachment($allowed,$upload_element_id,$post_id,$set_featured,$upload_file_url); 
			}
		}
	}
        
	else if($main == 'upload_to_path'){
		if ( $_FILES ) { 
			$files = $_FILES[$upload_element_id];
			$filePathInfo = pathinfo($files['name']);
			$FileType = $filePathInfo['extension'];

			$FileType = $FileType ? $FileType : $default_extension;
			
			$upload_dir = realpath(ABSPATH . '/..').'/'.$dir_name.'/';
			if ($files["error"]) {
				$return_value['error'] = "An error occurred.";
			}
			
			if(!in_array($FileType,$allowed) ) {
				$return_value['error'] = "Sorry, only JPG, JPEG, PNG, GIF & PDF files are allowed.";
			}

			$file_name = $file_name ? $file_name.'.'.$FileType : $filePathInfo['filename'].'.'.$FileType;
			$original_file_name = $file_name;
			
			if($overwrite_file == 'no'){
				// don't overwrite an existing file
				$i = 0;
				$name = pathinfo($file_name);
				while (file_exists($upload_dir . $file_name)) {
					$i++;
					$file_name = $name["filename"] . "-" . $i . "." . $name["extension"];
				}
			}
			
			if (!file_exists($upload_dir)) {
				mkdir($upload_dir, 0777, true);
			}
						
			// preserve file from temporary directory
			$success = move_uploaded_file($files["tmp_name"],$upload_dir . $file_name);
			if (!$success) { 
				$return_value['error'] = "Unable to save file.";
			}else{
				$file_dir =  explode('/', $dir_name);
				$app_name = array_shift($file_dir);
				$app_name = substr($app_name, 0, -5);
				$file_dir = implode('/', $file_dir);
				
				$return_value['success'] = 'File uploaded';
				$return_value['original_file_name'] = $original_file_name;
				$return_value['filename'] = $file_name;
				$return_value['path'] = $upload_dir.$file_name;
				$return_value['url'] = SITE_URL.'/'.$app_name.'/file?filename='.$file_dir.'/'.$file_name;
			}			
		}
	}

	else if($main == 'upload_multiple_to_path'){
		if ( $_FILES ) { 
			$files = $_FILES[$upload_element_id];

			if(is_array($files['name'])){
				$file_dir =  explode('/', $dir_name);
				$app_name = array_shift($file_dir);
				$app_name = substr($app_name, 0, -5);
				$file_dir = implode('/', $file_dir);

				foreach ($files['name'] as $key => $value) {            
						
					$FileType = pathinfo($files['name'][$key], PATHINFO_EXTENSION);
					$upload_dir = realpath(ABSPATH . '/..').'/'.$dir_name.'/';
					if ($files["error"][$key]) {
						$return_value[$key]['error'] = "An error occurred.";
					}
					
					if(!in_array($FileType,$allowed) ) {
						$return_value[$key]['error'] = "Sorry, only JPG, JPEG, PNG, GIF & PDF files are allowed.";
					}
					$tmp_file_name = $file_name.$key.'.'.$FileType;
					
					$tmp_file_name = $file_name ? $file_name.$key.'.'.$FileType : $files['name'][$key];
					$original_file_name = $tmp_file_name;

					if($overwrite_file == 'no'){
						// don't overwrite an existing file
						$i = 0;
						$name = pathinfo($tmp_file_name);
						while (file_exists($upload_dir . $tmp_file_name)) {
							$i++;
							$tmp_file_name = $name["filename"] . "-" . $i . "." . $name["extension"];
						}
					}
					
					if (!file_exists($upload_dir)) {
						mkdir($upload_dir, 0777, true);
					}
					// preserve file from temporary directory
					$success = move_uploaded_file($files["tmp_name"][$key],$upload_dir . $tmp_file_name);
					if (!$success) { 
						$return_value[$key]['error'] = "Unable to save file.";
					}else{			
						$return_value[$key]['success'] = 'File uploaded';
						$return_value[$key]['original_file_name'] = $original_file_name;
						$return_value[$key]['filename'] = $tmp_file_name;
						$return_value[$key]['path'] = $upload_dir.$tmp_file_name;
						$return_value[$key]['url'] = SITE_URL.'/'.$app_name.'/file?filename='.$file_dir.'/'.$tmp_file_name;
					}
				}
			}else{
				$return_value['error'] = "File array is Invalid.";
			}	
		}
	}

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}	

function aw2_handle_attachment($allowed,$file_handler,$post_id,$set_thu=false,$upload_file_url=null) {
	// check to make sure its a successful upload
	if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) return false;

	$FileType = pathinfo($_FILES[$file_handler]['name'], PATHINFO_EXTENSION);

	if(!in_array($FileType,$allowed) ) {
		return false;
	}
	
	$attach_id = media_handle_upload( $file_handler, $post_id );
	if ( !is_wp_error( $attach_id ) ) {
		// The image was uploaded successfully!
		if($set_thu==true)
			update_post_meta( $post_id, '_thumbnail_id', $attach_id );			
		else{
			update_post_meta( $post_id, $file_handler, $attach_id );
			if(!is_null($upload_file_url)){
				update_post_meta( $post_id, $upload_file_url, wp_get_attachment_url( $attach_id ) );
			}
		}		
	}
	return $attach_id;
}

function aw2_woo_set_prodcut_gallery($post_id,$attach_ids){
	$img_gal = get_post_meta( $post_id, '_product_image_gallery', true );
	$attach_ids = implode(",", $attach_ids);
	
	if($img_gal != ""){
		if($attach_ids != "")
			$attach_ids = $img_gal.",".$attach_ids;
		else
			$attach_ids = $img_gal;
	}
	
	update_post_meta( $post_id, '_product_image_gallery', $attach_ids );
}

\aw2_library::add_service('aw2.sideload','Download a File from URL and attach to media',['namespace'=>__NAMESPACE__]);
function sideload($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

	extract(\aw2_library::shortcode_atts( array(
		'main' => 'attach_to_post',
		'post_id' => null,
		'file_url'=>null,
		'dir_path'=>null,
		'file_name'=>null,
		'overwrite_file'=>'no',
		'resize'=>false,
		'sizes'=>null,
		'crop'=>false,
		'attach'=>false,		
		'allowed_file_types'=>null,
		'set_featured'=>false,
		'woo_product_gal'=>false
	), $atts, 'aw2_upload' ) );
	
	
	// Allow certain file formats
	$allowed = array('gif', 'png' ,'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'csv');
	
        
	if($allowed_file_types != null || $allowed_file_types != ''){
		$allowed_ext = explode(',', $allowed_file_types);
		$allowed = $allowed_ext;
	}

	if(empty($file_url)){
		\aw2_library::set_error('file_url is blank. It is required.'); 
		return '';
	}
	
	// These files need to be included as dependencies when on the front end.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );	
	
	if($main=='attach_to_post'){
		if(empty($post_id)){
			\aw2_library::set_error('post_id is empty. It is required.'); 
			return '';
		}
			
		
		$tmp = download_url( $file_url);
		if( is_wp_error( $tmp ) ){
			\aw2_library::set_error('Failed to download : file '.$file_url.' : because '.$tmp->get_error_message()); 
			return '';
		}
		
		$desc = "";
		$file_array = array();
                
                $allowed = implode('|',$allowed);
                // Set variables for storage
                // fix file filename for query strings
                preg_match("/[^\?]+\.($allowed)/i", $file_url, $matches);
                $file_array['name'] = basename($matches[0]);

                $file_array['tmp_name'] = $tmp;
                // If error storing temporarily, unlink
                if ( is_wp_error( $tmp ) ) {
                        @unlink($file_array['tmp_name']);
                        $file_array['tmp_name'] = '';
                }
                
		$file_array['tmp_name'] = $tmp;
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
		}

		// do the validation and storage stuff
		$attachment_id = media_handle_sideload( $file_array, $post_id, $desc );
		// If error storing permanently, unlink
                
		if ( is_wp_error($attachment_id ) ) {
			@unlink($file_array['tmp_name']);
                        \aw2_library::set_error('Media Sideload failed. '.$attachment_id->get_error_message());
                        return $attachment_id->get_error_message();
		}

		$src = wp_get_attachment_url( $attachment_id );
	
		$return_value['id']= $attachment_id;
		$return_value['url']= $src;
		
		if($woo_product_gal==true)
			aw2_woo_set_prodcut_gallery($post_id,$attachment_id);
		
		if($set_featured==true)
			update_post_meta( $post_id, '_thumbnail_id', $attachment_id);	
	}
	
	if($main=='save_to_path'){
		//create the folder
		if(empty($dir_path)){
			\aw2_library::set_error('dir_path is required');
			return '';
		}
		if($attach=="true")	{
			$upload_dir = wp_upload_dir();
			$dir_path =$upload_dir['basedir'].'/'.$dir_path;
		}
		wp_mkdir_p($dir_path);
		// get the file path 
		//$filename=$dir_path.'/'.$file_name;
		//if overwite then fix the file path
		if($overwrite_file == 'no'){
			// don't overwrite an existing file
			$i = 0;
			$name = pathinfo($file_name);
			while (file_exists($dir_path.'/'.$file_name)) {
				$i++;
				$file_name = $name["filename"] . "-" . $i . "." . $name["extension"];
			}
		}
		//now download the file
		$content = file_get_contents($file_url);
		// save the file
		file_put_contents($dir_path.'/'.$file_name, $content);
		// resize the file
		if($resize=='true' && !empty($sizes)){
			
			$sizes = explode(',',$sizes);
			foreach($sizes as $size){
				$image = wp_get_image_editor( $dir_path.'/'.$file_name );
				if ( ! is_wp_error( $image ) ) {
					$name = pathinfo($file_name);
					$new_filename=$name["filename"] . "-" . $size . "." . $name["extension"];
					$size = explode('x',$size);
					$image->resize( $size[0], $size[1], true );
					$image->save( $dir_path.'/'.$new_filename );
				}
			}
		}
		
		if($attach=="true"){
			if(empty($post_id)){
				$post_id=1;
			}
			$filetype = wp_check_filetype( basename( $file_name ), null );
			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $dir_path .'/'. basename( $file_name ), 
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			// Insert the attachment.
			$attach_id = wp_insert_attachment( $attachment, $dir_path.'/'.$file_name , $post_id );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $dir_path.'/'.$file_name );
			util::var_dump($attach_data);
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}
		$return_value['filename'] = $file_name;
		$return_value['path'] = $dir_path.'/'.$file_name;
		$return_value['url'] = SITE_URL.'/'.$dir_path.'/'.$file_name;
	}
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	
	return $return_value;
}