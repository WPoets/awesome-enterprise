<?php
namespace aw2\code;

\aw2_library::add_service('code','Code Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('code.run','Run the Code Library',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=\aw2_library::parse_shortcode($return_value);	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('code.dump','Dump the Code as is without parsing',['namespace'=>__NAMESPACE__]);
function dump($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$return_value='<code>' . trim($content) . '</code>';	
	return $return_value;
}

\aw2_library::add_service('code.highlight','Dump the code with syntax highlighter',['namespace'=>__NAMESPACE__]);
function highlight($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'lang'=>'html',
	'code'=>null,
	
	), $atts) );
	$return_value='';
	
	if(!is_null($code))$content=$code;
	\aw2_library::set('@prismjs.active','yes');
	\aw2_library::set('@prismjs.active','no');
	if(\aw2_library::get('@prismjs.active')==='no'){
	
		$return_value .='<script type="spa/axn" axn="core.run_script" cdn_js_files="prismjs/prism.js" cdn_css_files="prismjs/prism.css"></script>';
	}
	
	$return_value .='<pre class="line-numbers"><code class="language-'.$lang.'">'.htmlentities($content) .'</code></pre>';
	
		
	return $return_value;
}
