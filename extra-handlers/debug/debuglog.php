<?php
namespace aw2\debuglog;



\aw2_library::add_service('debuglog.html','Add a message',['namespace'=>__NAMESPACE__]);

function html($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'channel'=>'messages'
	
	), $atts, 'dump' ) );
	
	$folder=LOG_PATH . '/debug/' . \aw2_library::get('app.user.login');
	if (!file_exists($folder)) {
		mkdir($folder, 0777, true);
	}

	$path= $folder . '/' . $channel . '.html';
	$fp = fopen($path, 'a');
	fwrite($fp, $main);
}

\aw2_library::add_service('debuglog.dump','Add a message',['namespace'=>__NAMESPACE__]);

