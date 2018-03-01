<?php

add_action( 'save_post', 'awesome_save_postdata' );
add_action( 'add_meta_boxes', 'awesome_add_custom_box' );
add_action( 'post_submitbox_start', 'awesome_custom_button' );
add_action('wp_ajax_codeeditor_update', 'awesome_save_without_refersh');
add_action('edit_form_after_title','awesome_add_before_editor');


function awesome_add_before_editor($post) {
	global $post;
	if(class_exists('Monoframe'))
		return;
			
	do_meta_boxes(get_current_screen(), 'monoframe_pre_editor', $post);
}


function awesome_add_custom_box() {
	$screens = Monoframe::get_awesome_post_type();

	foreach ( $screens as $screen ) {
		
		add_meta_box(
			'awesome_codemirror',
		   'Awesome Code Editor',
			'awesome_init_codemirror',
			$screen,'monoframe_pre_editor','high'
		);
	}
}


	/**
	 * Prints the box content.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
function awesome_init_codemirror( $post ) {
	$codemirror_js_url = plugins_url( 'editor/codemirror/lib/codemirror.min.js' , dirname(__FILE__) );
	$codemirror_css_url = plugins_url( 'editor/codemirror/lib/codemirror.css' , dirname(__FILE__));
	
	$codemirror_addon_simple = plugins_url( 'editor/codemirror/addon/mode/simple.js' , dirname(__FILE__) );
	
	$codemirror_mode_xml = plugins_url( 'editor/codemirror/mode/xml/xml.js' , dirname(__FILE__) );
	$codemirror_mode_css = plugins_url( 'editor/codemirror/mode/css/css.js' , dirname(__FILE__) );
	$codemirror_mode_js = plugins_url( 'editor/codemirror/mode/javascript/javascript.js' , dirname(__FILE__) );
	$codemirror_mode_mixed = plugins_url( 'editor/codemirror/mode/htmlmixed/htmlmixed.js' , dirname(__FILE__) );
	$codemirror_mode_awcode = plugins_url( 'editor/codemirror/mode/awcode/awcode.js' , dirname(__FILE__) );
	
	$codemirror_addon_jump = plugins_url( 'editor/codemirror/addon/search/jump-to-line.js' , dirname(__FILE__) );
	$codemirror_addon_match = plugins_url( 'editor/codemirror/addon/edit/matchbrackets.js' , dirname(__FILE__) );
	$codemirror_addon_foldcode = plugins_url( 'editor/codemirror/addon/fold/foldcode.js' , dirname(__FILE__) );
	$codemirror_addon_hint = plugins_url( 'editor/codemirror/addon/hint/show-hint.js' , dirname(__FILE__) );
	$codemirror_addon_jshint = plugins_url( 'editor/codemirror/addon/hint/javascript-hint.js' , dirname(__FILE__) );
	$codemirror_addon_htmlhint = plugins_url( 'editor/codemirror/addon/hint/html-hint.js' , dirname(__FILE__) );
	$codemirror_addon_csshint = plugins_url( 'editor/codemirror/addon/hint/css-hint.js' , dirname(__FILE__) );
	$codemirror_addon_anyhint = plugins_url( 'editor/codemirror/addon/hint/anyword-hint.js' , dirname(__FILE__) );
	$codemirror_addon_highlighter = plugins_url( 'editor/codemirror/addon/search/match-highlighter.js' , dirname(__FILE__) );
	$codemirror_addon_lint = plugins_url( 'editor/codemirror/addon/lint/lint.js' , dirname(__FILE__) );
	$codemirror_addon_jslint = plugins_url( 'editor/codemirror/addon/lint/javascript-lint.js' , dirname(__FILE__) );
	$codemirror_addon_jsonlint = plugins_url( 'editor/codemirror/addon/lint/json-lint.js' , dirname(__FILE__) );
	$codemirror_addon_activeline = plugins_url( 'editor/codemirror/addon/selection/active-line.js' , dirname(__FILE__) );
	$codemirror_addon_fullscreen = plugins_url( 'editor/codemirror/addon/display/fullscreen.js' , dirname(__FILE__) );
	$codemirror_addon_foldgutter = plugins_url( 'editor/codemirror/addon/fold/foldgutter.js' , dirname(__FILE__) );
	
	$codemirror_addon_bracefold = plugins_url( 'editor/codemirror/addon/fold/brace-fold.js' , dirname(__FILE__) );
	$codemirror_addon_xmlfold = plugins_url( 'editor/codemirror/addon/fold/xml-fold.js' , dirname(__FILE__) );
	$codemirror_addon_indentfold = plugins_url( 'editor/codemirror/addon/fold/indent-fold.js' , dirname(__FILE__) );
	$codemirror_addon_commentfold = plugins_url( 'editor/codemirror/addon/fold/comment-fold.js' , dirname(__FILE__) );
	
	
	$codemirror_theme = plugins_url( 'editor/codemirror/theme/monokai-aw.css' , dirname(__FILE__) );
	
	  // Add an nonce field so we can check for it later.
	  wp_nonce_field( 'awesome_cm_custom_box', 'awesome_cm_custom_box_nonce' );

	  /*
	   * Use get_post_meta() to retrieve an existing value
	   * from the database and use the value for the form.
	   */
	//  $value = get_post_meta( $post->ID, '_my_meta_value_key', true );
	echo '<link rel="stylesheet" href="'.$codemirror_css_url.'">
		 <link rel="stylesheet" href="'.$codemirror_theme.'">
		 
		 <script src="'.$codemirror_js_url.'"></script>
		 <script src="'.$codemirror_addon_simple.'"></script>
		 <script src="'.$codemirror_mode_xml.'"></script>
		 <script src="'.$codemirror_mode_css.'"></script>
		 <script src="'.$codemirror_mode_js.'"></script>
		 <script src="'.$codemirror_mode_mixed.'"></script>
		 <script src="'.$codemirror_mode_awcode.'"></script>
		 <script src="'.$codemirror_addon_jump.'"></script>
		 <script src="'.$codemirror_addon_match.'"></script>
		 <script src="'.$codemirror_addon_foldcode.'"></script>
		 <script src="'.$codemirror_addon_hint.'"></script>
		 <script src="'.$codemirror_addon_jshint.'"></script>
		 <script src="'.$codemirror_addon_htmlhint.'"></script>
		 <script src="'.$codemirror_addon_csshint.'"></script>
		 <script src="'.$codemirror_addon_anyhint.'"></script>
		 <script src="'.$codemirror_addon_highlighter.'"></script>
		 <script src="'.$codemirror_addon_lint.'"></script>
		 <script src="'.$codemirror_addon_jslint.'"></script>
		 <script src="'.$codemirror_addon_jsonlint.'"></script>
		 <script src="'.$codemirror_addon_activeline.'"></script>
		 <script src="'.$codemirror_addon_fullscreen.'"></script>	 
		 <script src="'.$codemirror_addon_foldgutter.'"></script>	 
		 <script src="'.$codemirror_addon_bracefold.'"></script>	 
		 <script src="'.$codemirror_addon_xmlfold.'"></script>	 
		 <script src="'.$codemirror_addon_xmlfold.'"></script>	 
		 <script src="'.$codemirror_addon_indentfold.'"></script>	 
		 <script src="'.$codemirror_addon_commentfold.'"></script>	 
		 
		 ';

	  echo'<style>
	  .postarea{display:none} 
	  .CodeMirror-fullscreen {
			 position: fixed;
			  top: 0; left: 0; right: 0; bottom: 0;
			  height: auto;
			z-index: 100000;
		}
		
		.CodeMirror-foldmarker {
		  color: blue;
		  text-shadow: #b9f 1px 1px 2px, #b9f -1px -1px 2px, #b9f 1px -1px 2px, #b9f -1px 1px 2px;
		  font-family: arial;
		  line-height: .3;
		  cursor: pointer;
		}
		.CodeMirror-foldgutter {
		  width: .7em;
		}
		.CodeMirror-foldgutter-open,
		.CodeMirror-foldgutter-folded {
		  cursor: pointer;
		}
		.CodeMirror-foldgutter-open:after {
		  content: "\25BE";
		}
		.CodeMirror-foldgutter-folded:after {
		  content: "\25B8";
		}
		
		.fullScreen {
			overflow: hidden
		}
		.CodeMirror {
			height: auto;
		  }
		</style>';
		
	//  echo'<pre id="ace_ui_code" style="width:100%;height:30em" ></pre>';
	  
	  $content=$post->post_content;
	  $content=str_replace('<','__lt__',$content);
	  
	  echo '
	  <div id="cm_editor"></div>
	  <textarea id="awesome_code" name="awesome_code" rows="20" cols="100">'.$content.'</textarea>';
	  
	  echo'
	  <script>
	    var textarea = jQuery("#awesome_code");
		var myCodeEditor = CodeMirror.fromTextArea(document.getElementById("awesome_code"), {
						lineNumbers: true,
						lineWrapping: true,
						styleActiveLine: true,
						matchBrackets: true,
						viewportMargin: Infinity,
						theme: "monokai",
						mode: "awcode",
						foldGutter: true,
						gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
						extraKeys: {
							"F11": function(cm) {
							  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
							},
							"Esc": function(cm) {
							  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
							},
							"Ctrl-Q": function(cm){ 
								cm.foldCode(cm.getCursor()); 
							}
						}
					  });
		
		CodeMirror.commands.save = function(cm) { 
				cm.save();
				var b = false;
				if(jQuery("input#update-no-refresh").length == 1){
					b=jQuery("input#update-no-refresh");
				}
				else if(jQuery("input#publish").length == 1)
				{
					b = jQuery("input#publish");
				}
				
				if(b != false)
				{
					b.click();
				}
				
		};
		var content=textarea.val();
		content=content.replace(/__lt__/g, "<");
		
		myCodeEditor.setValue(content);
		textarea.val(myCodeEditor.getValue());
	</script>';
}

	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
function awesome_save_postdata( $post_id ) {

	  /*
	   * We need to verify this came from the our screen and with proper authorization,
	   * because save_post can be triggered at other times.
	   */
	  global $aw_post_type;
	  // Check if our nonce is set.
	  if ( ! isset( $_POST['awesome_cm_custom_box_nonce'] ) )
		return $post_id;

	  $nonce = $_POST['awesome_cm_custom_box_nonce'];

	  // Verify that the nonce is valid.
	  if ( ! wp_verify_nonce( $nonce, 'awesome_cm_custom_box' ) )
		  return $post_id;

	  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		  return $post_id;

	  // Check the user's permissions. //,'aw2_component','aw2_module','aw2_page'
	  
	  //if ( in_array($_POST['post_type'], Monoframe::get_awesome_post_type())) {

		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;

	 // } else {
	//		return $post_id;
	 // }

	  /* OK, its safe for us to save the data now. */

	  // Sanitize user input.
	  
	  // Update the meta field in the database.
	  // unhook this function so it doesn't loop infinitely
			remove_action('save_post', 'awesome_save_postdata');

	  // Update post 37
	  $my_post = array(
		  'ID'           => $post_id,
		  'post_content' => $_POST['awesome_code']
	  );

	// Update the post into the database
	  wp_update_post( $my_post );

	  // re-hook this function
			add_action('save_post', 'awesome_save_postdata');
}
	
function awesome_custom_button(){
		global $post;

		//if ( Monoframe::is_awesome_post_type($post)) 
		{
			if ( in_array( $post->post_status, array('publish', 'future', 'private') ) && 0 != $post->ID ) {
				echo "<div style='text-align:center;margin-bottom:10px;'><span id='uwrspin' class='spinner'></span><input type='button' class='button button-primary button-large' value='Update Without Refresh' id='update-no-refresh' onclick='save_aw_block()'></div>
				<script>
					function save_aw_block(){
						var aw_ui_code=jQuery('#awesome_code').val();
						var post_id=jQuery('#post_ID').val();
						jQuery('#uwrspin').addClass('is-active');
						jQuery.post(
							ajaxurl,
							{action:'codeeditor_update',awesome_code:aw_ui_code,post_id:post_id},
							function(data){
									jQuery('#uwrspin').removeClass('is-active');
								}
							);
					}
				</script>
				
				";
			}
		}	
}


function awesome_save_without_refersh(){
	
	if(intval($_POST['post_id']))
	{  
		$my_post = array(
		  'ID'           => $_POST['post_id'],
		  'post_content' => $_POST['awesome_code']
		);

		// Update the post into the database
		wp_update_post( $my_post );
	}  
}



