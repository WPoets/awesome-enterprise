<?php

function awesome_custom_metaboxes() {
   
	$custom_meta_boxes=&\aw2_library::get_array_ref('custom_meta_boxes');

	if(empty($custom_meta_boxes)) return;

	foreach($custom_meta_boxes as $key=>$value){
		if(empty($value) || !is_array($value)) continue;
		
		$id='';
		if(isset($value['id'])) $id = $value['id'];
		
		$title='';
		if(isset($value['title'])) $title = $value['title'];
		
		$callback='';
		if(isset($value['callback_service'])) $callback = $value['callback_service'];
		
		$screen='post';
		if(isset($value['screen'])) $screen = $value['screen'];
		
		$context='advanced';
		if(isset($value['context'])) $context = $value['context'];
		
		$priority='default';
		if(isset($value['priority'])) $priority = $value['priority'];
		
		if(empty($id) ||empty($callback) || empty($title)  ) continue;
		
		add_meta_box( $id, __( $title, 'awesome' ), 'awesome_custom_metabox_callback', $screen,$context,$priority, array('service' => $callback));
		
	}
	
	
}

add_action( 'add_meta_boxes', 'awesome_custom_metaboxes' );

function awesome_custom_metabox_callback($post,$data){

		$service=$data['args']['service'];

		$collection=['main'=>$service,'post'=>$post];
		echo \aw2\service\run($collection);		
}

function awesome_custom_save_metabox( $post_id, $post){
		
	  // Verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
      // to do anything
      if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return $post_id;


      // Check permissions to edit pages and/or posts
     
	  if ( !current_user_can( 'edit_page', $post_id ) || !current_user_can( 'edit_post', $post_id ))
		return $post_id;
     

      // OK, we're authenticated: we need to find and save the data
		$custom_meta_boxes=&\aw2_library::get_array_ref('custom_meta_boxes');

	if(empty($custom_meta_boxes)) return $post_id;
	
	foreach($custom_meta_boxes as $key=>$value){
		if(empty($value) || !is_array($value)) continue;
		
		$callback='';
		if(isset($value['callback_save'])) $callback = $value['callback_save'];

		if(isset($value['screen'])) $screen = $value['screen'];
		
		if($screen == $post->post_type){
			if(empty($callback)) return $post_id;
 			
			$collection=['main'=>$callback,'post'=>$post];
			\aw2\service\run($collection);

		}

	}	
		
	return $post_id;	
}

add_action( 'save_post', 'awesome_custom_save_metabox', 10, 2 );
