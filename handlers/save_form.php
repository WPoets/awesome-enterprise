<?php

aw2_library::add_shortcode('aw2','save_form', 'awesome2_save_form','Save Form Values Automatically');

function awesome2_save_form($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
		'tag' => '',
		'set_post_id'=>''
	), $atts) );
		
    $post=new stdClass();
    foreach($_REQUEST as $key => $value){
		$parts = explode('|', $key);
		if(count($parts)==3 && $parts[0]==$tag){

			$fieldtype = $parts[1];
			$fieldname = $parts[2];
			if($fieldtype=='post'){
				$post->$fieldname=stripslashes_deep($value);
			}
		}	
	}
	
	$args=aw2_library::get_clean_args($content,$atts);
	if($args!=''){
		foreach ($args as $key => $value) {
			$post->$key=$value;
		}
	}
	
	if(property_exists($post,'ID') && $post->ID !='')
		$postid=wp_update_post($post);
	else
		$postid=wp_insert_post($post);
	
	aw2_library::set($set_post_id,$postid);
	
    foreach($_REQUEST as $key => $value){
            $parts = explode('|', $key);
			if(count($parts)==3 && $parts[0]==$tag){
				$fieldtype = $parts[1];
				$fieldname = $parts[2];
				if($fieldtype=='meta'){
                    update_post_meta($postid, $fieldname, rawurldecode(stripslashes_deep($value)));
				}
			}	
		}
	
    foreach($_REQUEST as $key => $value){
		$parts = explode('|', $key);
		if(count($parts)==3 && $parts[0]==$tag){
			$fieldtype = $parts[1];
			$fieldname = $parts[2];
			if($fieldtype=='taxonomy'){
				$terms=explode(",", $value);
				
				wp_set_object_terms( $postid, $terms, $fieldname);
			}
		}	
	}
	
	$return_value=$postid;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}