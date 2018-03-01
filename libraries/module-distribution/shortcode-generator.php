<?php

 /**
 * Adds a box to the main column on the Post and Page edit screens.
 */
 
function aw2_generator_cm_add_custom_box() {

    //$screens = array( 'page','aw2_page','aw2_trigger' );
    $screens = array( 'page','aw2_page');

    foreach ( $screens as $screen ) {

        add_meta_box(
            'aw_ui_modulecode',
			'Local Module Selection',
            //'aw2_ui_module_selection_int',
            'asf_installed_distributables_page_callback',
            $screen,'advanced','high'
        );
    }
}
add_action( 'add_meta_boxes', 'aw2_generator_cm_add_custom_box' );

function aw2_ui_module_selection_int(){
  echo '
  <div id="shotcode_slug">
	<p>Step 1</p>
	<input type="text" value="slug" class="js-aw2-slug" > <input type="button" id="slug_button" value="OK">
  </div>
  <div id="step">
	<p>Step 2</p>
	
  </div>
  <div id="code_samples">
	<p>Step 3</p>
	<div class="loader" style="display:none">Loading...</div>
  </div> 
  <div>
	<p>Step 4</p>
	<input type="button" value="Generate Code" id="aw2_cmb" style="display:none" />
	<input type="button" value="Generate and Insert" id="aw2_cmb_insert" style="display:none" />
  </div>
 <div id="generated_shortcode"></div> 
 ';
}


add_action('wp_ajax_aw2_cmb2_form', 'aw2_cmb2_form');
add_action('wp_ajax_nopriv_aw2_cmb2_form', 'aw2_cmb2_form');

function aw2_cmb2_form($do="set_shortcode",$ajax=true){
    $slug = $_REQUEST['slug'];
	$trigger = (isset($_REQUEST['trigger'])) ? $_REQUEST['trigger'] : '';
	$trigger_button = "";
    $posts = get_posts(
        array(
        'name'      => $slug,
        'post_type' => 'aw2_module'
        )
    );
    
    if(!$posts){
        echo 'invalid block';
		
		echo  $slug . ' does not exists locally, Please install it. <input type="button" id="install_module_btn" value="Install" data-slug="'.$slug.'"> ';
		
        wp_die();
    }
    $post=$posts[0];

    $cmb = new_cmb2_box( array(
        'id'           => 'shortcode_params',
        'object_types' => array( 'post' ),
        'hookup'       => false,
        'save_fields'  => false,
    ) );
    $params=get_post_meta($post->ID,'_params',true);
    $params=json_decode($params,true);
    if(is_array($params)){
        foreach ($params as $value){
                $cmb->add_field( $value);
        }
	}

	switch($do){
		case 'set_trigger':
			$cmb->add_field( array(
				'name'       =>'Trigger On',
				'desc'       =>'Select when this trigger will be called',
				'id' 		 =>'aw2_trigger_when',
				'taxonomy'   =>'aw2_trigger_when', // Enter Taxonomy Slug
				'type'       =>'taxonomy_select'
			) );
			$button = "<button type='button' class='btn btn-primary ladda-button aw2_cmb_set_trigger' data-style='slide-down'><span class='ladda-label'>Set Trigger</span></button>";
		break;
		case 'get_shortcode':
			$button = "<input type='button' class='btn btn-primary aw2_cmb' value='Generate Shortcode' />";
		break;
		default:
			$button = "<input type='button' class='btn btn-primary aw2_cmb_insert' value='Generate & Insert Shortcode' />";
		break;
	}

	/* if($trigger == 'yes'){
		$trigger_button = $button;
	}
	if(is_array($params) || $trigger == 'yes'){
		$form = cmb2_get_metabox_form( $cmb, 'fake-id');
		$form .= "<div class='gap-4'></div>";
		$form .= "$trigger_button<input type='button' class='btn btn-primary aw2_cmb' value='Generate Shortcode' />";
    }else{
		//$form = "[aw2_module slug='$slug' ]";
		if($trigger == 'yes'){
			$form .= "<div class='gap-4'></div>";
			$form .= "$trigger_button";
		}
	} */
	if(!empty($cmb->meta_box['fields'])){
		$form = "<h3>Set Parameters</h3>";
		$form .= "<div class='gap-4'></div>";
		$form .= cmb2_get_metabox_form( $cmb, 'fake-id');
    }

	$form .= '<div class="cmb-th"></div><div class="cmb-td">'.$button.'</div>';
	$form .= "<input type='hidden' value='$slug' class='slug' />";

    echo $form;
	if($ajax === true)
		wp_die();
}

function aw2_filter_content($post) {

  // Process content here
	$matches = array();
	if(preg_match_all('/\[aw2.module.*?slug=(".*?"|\'.*?\').*?shid=(".*?"|\'.*?\').*?\]((?:.|[\r\n])*?)\[\/aw2.module\]/', $post->post_content, $matches)){
		$shotcodes = $matches[0];
		$slugs = $matches[1];
		$shids = $matches[2];
		
		$processedArray = array();

		foreach($shids as $key => $shid){
			$pattern = '/["\']/';
			$shid = preg_replace($pattern,"",$shid);
			$slug = preg_replace($pattern,"",$slugs[$key]);
			$processedArray[$shid] = array($slug, $shotcodes[$key]);
		}
		
		foreach($processedArray as $id => $data){
			$return=aw2_library::get_post_from_slug($data[0],'aw2_module',$module);
			
			$thumb_src = wp_get_attachment_image_src( get_post_thumbnail_id($module->ID), 'full' );
			$featured_img = $thumb_src[0];
			if($featured_img == "")
				$featured_img = plugins_url('css/aw-default-module.jpg',dirname(__FILE__));

			
			$featuredImg = "<img id='$id' width='250' class='aw2-shortcode' data-module_slug='".$data[0]."' src='".$featured_img."' />";
			$shortcode = str_replace("'",'"',$data[1]);
			echo "<script>";
					echo 'jQuery(document).data("'.$data[0].$id.'","'.$featuredImg.'");';
					echo "jQuery(document).data('$id','$shortcode');";
			echo "</script>";
		}
	}

}
add_action( 'edit_form_top', 'aw2_filter_content' ); 