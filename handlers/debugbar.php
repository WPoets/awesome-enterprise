<?php
namespace aw2\debugbar;
use DebugBar\StandardDebugBar;
function setup(){
	$debugbar=\aw2_library::get('debugbar.instance');
	if(!empty($debugbar))return $debugbar;
	
	$plugin_path=\aw2_library::$plugin_path;
	require_once $plugin_path .'/vendor/autoload.php';
	//use DebugBar\StandardDebugBar;
	$debugbar = new StandardDebugBar();
	\aw2_library::set('debugbar.instance',$debugbar);
	return $debugbar;
}


\aw2_library::add_service('debugbar.head','Generate Scripts for DebugBar',['namespace'=>__NAMESPACE__]);

function head($atts,$content=null,$shortcode){
	if(\aw2_library::get('debug_config.active')!=='yes')return;
	if(\aw2_library::get('debug_config.output')!=='debugbar')return;
	
	$debugbar=setup();
	$debugbarRenderer = $debugbar->getJavascriptRenderer();
	$debugbarRenderer->setOptions(["base_url"=>plugin_dir_url( __DIR__ )."vendor/maximebf/debugbar/src/DebugBar/Resources"]);
	return $debugbarRenderer->renderHead();
}

\aw2_library::add_service('debugbar.html','Add a message',['namespace'=>__NAMESPACE__]);

function html($atts,$content=null,$shortcode=null){
	if(\aw2_library::get('debug_config.active')!=='yes')return;
	if(\aw2_library::get('debug_config.output')!=='debugbar')return;

	extract( shortcode_atts( array(
	'channel'=>'messages'
	
	), $atts, 'dump' ) );
	
	$debugbar=setup();
	$debugbar[$channel]->addMessage($content);
	
}






\aw2_library::add_service('debugbar.render','Generate Scripts for DebugBar',['namespace'=>__NAMESPACE__]);

function render($atts,$content=null,$shortcode=null){
	if(\aw2_library::get('debug_config.active')!=='yes')return;
	if(\aw2_library::get('debug_config.output')!=='debugbar')return;

	$debugbar=setup();
	$debugbarRenderer = $debugbar->getJavascriptRenderer();

	return $debugbarRenderer->render();
}

\aw2_library::add_service('debugbar.ajax_render','Generate Scripts for DebugBar',['namespace'=>__NAMESPACE__]);

function ajax_render($atts,$content=null,$shortcode=null){
	if(\aw2_library::get('debug_config.active')!=='yes')return;
	if(\aw2_library::get('debug_config.output')!=='debugbar')return;

	$debugbar=setup();
	$debugbarRenderer = $debugbar->getJavascriptRenderer();	
	$reply= $debugbarRenderer->render(false);			
	$reply=str_replace('<script type="text/javascript">','<script type="spa/axn" axn=core.run_script>',$reply);
	//$reply='<script type="spa/axn" axn=core.run_script>console.log("hero hero")</script>';

	return $reply;

}


