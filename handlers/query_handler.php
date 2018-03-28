<?php

aw2_library::add_library('query','Queries');

function aw2_query_meta_query($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
		
	$dataset=$atts['dataset'];

	global $wpdb;
	$results = $wpdb->get_results($dataset['query'],ARRAY_A);
	
	$dataset['raw']=$results;
	
	if((isset($dataset['transpose']) && $dataset['transpose'] == "yes") || (isset($dataset['transform']) && $dataset['transform'] == "yes") ){
		$dataset['rows']=array();
		foreach($results as $result){
			switch($result['type']){
				case 'data_id':
					$dataset['rows'][$result['data_id']]=array();
					break;
				case 'meta':
					$dataset['rows'][$result['data_id']][$result['meta_key']]=$result['meta_value'];
					break;
			}
		}
	}else{
		$dataset['rows']=$dataset['raw'];
	}
	$return_value=aw2_library::post_actions('all',$dataset,$atts);
	return $return_value;
}


function aw2_query_unhandled($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;

	$pieces=$shortcode['tags'];
	if(count($pieces)!=2)return 'error:You must have exactly two parts to the query shortcode';
	$query_obj=new awesome2_query($pieces[1],$atts,$content);
	$return_value=$query_obj->run();
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}