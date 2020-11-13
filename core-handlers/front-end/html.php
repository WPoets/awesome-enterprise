<?php
namespace aw2\html;

\aw2_library::add_service('html.trusted','Throws out a trusted template',['namespace'=>__NAMESPACE__]);


function trusted($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	$html=\aw2_library::parse_shortcode($content);

	//$html=str_replace('<','__LT__',$html);
	//$html=str_replace('>','__GT__',$html);
	
	$data_check_id=\aw2_library::get('app.safe_id');
	
	$return_value='<template type=spa/axn axn=core.insert_trusted_html data-check_id="' . $data_check_id . '">' . $html . '</template>';
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}