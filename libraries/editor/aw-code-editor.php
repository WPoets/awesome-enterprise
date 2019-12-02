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

	$codemirror_js = array(
		"https://cdn.getawesomestudio.com/lib/awesome-shortcodes/shortcode-tags.js",
		plugins_url("editor/codemirror/lib/codemirror.js", dirname(__FILE__)),
		plugins_url( 'editor/codemirror/lib/iao-alert.jquery.min.js' , dirname(__FILE__) ),
		plugins_url("editor/codemirror/mode/css/css.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/mode/javascript/javascript.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/mode/xml/xml.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/mode/xml/shortcode.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/mode/sql/sql.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/mode/htmlmixed/htmlmixed.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/mode/htmlmixed/shortcodemixed.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/mode/htmlmixed/awesome.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/keymap/sublime.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/selection/active-line.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/selection/selection-pointer.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/display/fullscreen.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/edit/matchtags.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/edit/closetag.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/foldcode.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/foldgutter.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/brace-fold.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/xml-fold.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/shortcode-fold.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/indent-fold.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/markdown-fold.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/comment-fold.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/hint/show-hint.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/hint/css-hint.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/hint/html-hint.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/hint/xml-hint.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/hint/shortcode-hint.js", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/hint/shortcodemixed-hint.js", dirname(__FILE__))
	);


	$codemirror_css = array(
		plugins_url( 'editor/codemirror/lib/iao-alert.min.css' , dirname(__FILE__)),
		plugins_url("editor/codemirror/lib/codemirror.css", dirname(__FILE__)),
		plugins_url("editor/codemirror/theme/monokai-aw.css", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/fold/foldgutter.css", dirname(__FILE__)),
		plugins_url("editor/codemirror/addon/hint/show-hint.css", dirname(__FILE__))
	);

	// Add an nonce field so we can check for it later.
  	wp_nonce_field( 'awesome_cm_custom_box', 'awesome_cm_custom_box_nonce' );

	  /*
	   * Use get_post_meta() to retrieve an existing value
	   * from the database and use the value for the form.
	   */
	//  $value = get_post_meta( $post->ID, '_my_meta_value_key', true );
	
	foreach ($codemirror_css as $css) {
    	echo '<link rel="stylesheet" href='.$css.'>';
	}

	foreach ($codemirror_js as $js) {
		echo '<script src='.$js.'></script>';
	}

	$apphelp=&aw2_library::get_array_ref('apphelp');

	if(!in_array($post->post_type, $apphelp)){
		echo '<style>.postarea{display:none} </style>';
	}
		
		
	  echo
	  '<style>
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
		
		.shortcode-doc p {
			margin: 0px;
			border-bottom: 1px solid #40403e;
			padding: 8px;
		}
		.shortcode-doc span {
			color: #c7c7c7;
	    font-size: 13px;
		}
		.shortcode-doc .sh-title{
			font-size: 13px;
    	color: #e2e2e2;
		}
		.shortcode-doc{
			margin: 0;
			font-family: monospace;
			position: absolute;
			color: #7d8486;
			background: #2e2f28;
			border: 0.5px solid #40403e;
			max-height: 300px;
			z-index: 100001;
			max-width: 240px;
			overflow: auto;
		}

		.shortcode-doc::-webkit-scrollbar-track
		{
				-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
				background-color: #8d8d8d;
				box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
				border-radius: 10px;
		}
		.shortcode-doc::-webkit-scrollbar
		{
			width: 5px;
			height: 5px;
			border-radius: 10px;
			background-color: #8d8d8d;
		}

		.shortcode-doc::-webkit-scrollbar-thumb
		{
			border-radius: 10px;
			background-color: #424242;
		}
	  </style>';
		
	//  echo'<pre id="ace_ui_code" style="width:100%;height:30em" ></pre>';
	  
	  $content=$post->post_content;
	  $content=str_replace('<','__lt__',$content);
		
		
		if(in_array($post->post_type, $apphelp)){
			$prefered_editor = get_post_meta($post->ID,'app_help_editor',true);
			
			if(empty($prefered_editor)){
				$prefered_editor='content-editor';
			}
			
			echo '
			<div>
				<p>Please select your preferred editor:</p>
				<input type="radio" id="app_help1" name="app_help_editor" value="code-editor" '; if($prefered_editor=='code-editor'){echo 'checked';} echo'>
				<label for="app_help1">Code Editor</label>

				<input type="radio" id="app_help2" name="app_help_editor" value="content-editor" ';if($prefered_editor=='content-editor'){echo 'checked';} echo'> 
				<label for="app_help2">Content Editor</label>
				<br>
				<br>
				<br>
			</div>
			
			';
		}
		
	  echo '
	  <div id="cm_editor"></div>
	  <textarea id="awesome_code" name="awesome_code" rows="20" cols="100">'.$content.'</textarea>';
	  
	  ?>
	  <script>
		var textarea = jQuery("#awesome_code");
		
		var show_doc = function(instance){
			var cur = instance.getCursor();
			var token = instance.getTokenAt(cur);
			var cur_tag = shortcodes[token.string];
			if(cur_tag){
				jQuery(".shortcode-doc").fadeOut("slow", function(){ jQuery(".shortcode-doc").remove(); });

				var doc_div = document.createElement("div");
				doc_div.classList.add("shortcode-doc");
				
				doc_div.innerHTML = "<p class='sh-title'>[" + token.string + "]</p>";
				if(cur_tag.doc){
					doc_div.innerHTML += "<p><span>Description:</span> " + cur_tag.doc + "</p>";
				}
				if(cur_tag.params){
					doc_div.innerHTML += "<p><span>Attributes:</span> " + cur_tag.params + "</p>";
				}
				if(cur_tag.return){
					doc_div.innerHTML += "<p><span>Return Type:</span> " + cur_tag.return + "</p>";
				}
				document.body.appendChild(doc_div);

				jQuery('.shortcode-doc').css('display','block');	
				jQuery('.shortcode-doc').css('left',myCodeEditor.cursorCoords().left);	
				jQuery('.shortcode-doc').css('top',myCodeEditor.cursorCoords().bottom);


				setTimeout(function(){
					if (jQuery('.shortcode-doc').is(":hover")) {
						jQuery('.shortcode-doc').mouseleave(function(){
							jQuery('.shortcode-doc').fadeOut('slow', function(){ jQuery('.shortcode-doc').remove(); });
						});							
					} else {
						jQuery('.shortcode-doc').fadeOut('slow', function(){ jQuery('.shortcode-doc').remove(); });
					}
					},3000);
			}
		}
		
		var showHintContainer = document.body;
		CodeMirror.commands.autocomplete = function(cm) {
		cm.showHint({
			hint: CodeMirror.hint.auto,
			container: showHintContainer
		});
		};

	    var mixedMode = {
    	    name: "awesome"
	  	};
	  	var myCodeEditor = CodeMirror.fromTextArea(document.getElementById("awesome_code"), {
			mode: mixedMode,
			theme: "monokai",
			  shortcodes: shortcodes,
			  lineNumbers: true,
			  lineWrapping: true,
			  styleActiveLine: true,
			  selectionPointer: true,
			  matchBrackets: true,
			  viewportMargin: Infinity,
			  keyMap: "sublime",
			  foldGutter: true,
			  
			  
			  matchTags: {bothTags: true},
			autoCloseTags: true,
			  gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
			  tabSize: 2,
			  smartIndent: false,
			  
			  extraKeys: {
				  "Ctrl-Space": "autocomplete",
				  "Ctrl-I": show_doc,
				  "F11": function(cm) {
					  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				  },
				  "Esc": function(cm) {
					  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				  },
				  "Ctrl-Q": function(cm){ 
					  cm.foldCode(cm.getCursor()); 
				  }
			  }
		});
		
		myCodeEditor.on("inputRead", function(instance) {
			var cur = instance.getCursor();
			var token = instance.getTokenAt(cur);
			if (instance.state.completionActive) {
					return;
			}
			var cur = instance.getCursor();
			var token = instance.getTokenAt(cur);
			if (token.type && token.type != "comment" && token.string != "]") {
					CodeMirror.commands.autocomplete(instance);
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
		myCodeEditor.clearHistory();
		textarea.val(myCodeEditor.getValue());
	</script>
	<?php
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

		if ( ! current_user_can( 'develop_for_awesomeui', $post_id ) )
			return $post_id;

	 // } else {
	//		return $post_id;
	 // }

	  /* OK, its safe for us to save the data now. */

	  // Sanitize user input.
	  
	  // Update the meta field in the database.
	  // unhook this function so it doesn't loop infinitely
	remove_action('save_post', 'awesome_save_postdata');
	
			
		$apphelp=&aw2_library::get_array_ref('apphelp');
		if(in_array($_POST['post_type'], $apphelp) ){
			update_post_meta($post_id,'app_help_editor',$_POST['app_help_editor']);	

			 if($_POST['app_help_editor'] == 'code-editor'){
				  $my_post = array(
					  'ID'           => $post_id,
					  'post_content' => $_POST['awesome_code']
				  );

				  wp_update_post( $my_post );
			 }  
		}
		else{
			 // Update post 37
			  $my_post = array(
				  'ID'           => $post_id,
				  'post_content' => $_POST['awesome_code']
			  );

			// Update the post into the database
			wp_update_post( $my_post );
		}
		
	aw2_library::get_module(["post_type"=>$_POST['post_type']],$_POST['post_name']);
	
	  // re-hook this function
	 add_action('save_post', 'awesome_save_postdata');
}
	
function awesome_custom_button(){
		global $post;
		
		$apphelp=&aw2_library::get_array_ref('apphelp');
		if(in_array($post->post_type, $apphelp) ){
			return ;
		}
		
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
									jQuery.iaoAlert({msg: 'Saved',
										type: 'success',
										mode: 'light'}
									);
								}
							);
					}
					jQuery('a.current').parents('li.wp-has-submenu').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
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
		$new_post = get_post($_POST['post_id']);
		
		aw2_library::get_module(['post_type'=>$new_post->post_type],$new_post->post_name);
	}  
}