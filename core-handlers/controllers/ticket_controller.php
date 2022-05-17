<?php
namespace aw2\controllers;

\aw2_library::add_service('controllers.tkt','Ticket Controller',['namespace'=>__NAMESPACE__]);


function tkt($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)exit();
	
	$o=$atts['o'];

	if(empty($o->pieces))exit();
	\controllers::set_cache_header('no'); // HTTP 1.1.
	
	$app=\aw2_library::get_array_ref('app');
	$ticket=array_shift($o->pieces);
	

	$hash=\aw2\session_ticket\get(["main"=>$ticket],null,null);
	if(!$hash || !$hash['payload']){
		header("HTTP/1.1 404 Not Found");
		echo 'Ticket is invalid: ' . $ticket;
		exit();			
	}
	$payload=json_decode($hash['payload'],true);
	//\util::var_dump($payload);
	\controllers::set_qs($o);
	$app['active']['controller'] = 'ticket';
	$app['active']['ticket'] = $ticket;
	$result=array();
					
	foreach ($payload as $one) {
		$arr=isset($one['data'])?$one['data']:array();
		$arr['service']=$one['service'];
		$result[]=\aw2\service\run($arr,null,[]);
	}
	echo implode('',$result);
	//render debug bar if needs to be rendered	
	if(AWESOME_DEBUG)echo \aw2\debugbar\ajax_render([]);		
	exit();	

	return ;
}


