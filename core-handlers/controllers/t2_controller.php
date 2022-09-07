<?php
namespace aw2\controllers;

\aw2_library::add_service('controllers.t2','Ticket 2 Controller',['namespace'=>__NAMESPACE__]);


function t2($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)exit();
	
	$o=$atts['o'];

	if(empty($o->pieces))exit();
	\controllers::set_cache_header('no'); // HTTP 1.1.
	
	$app=&\aw2_library::get_array_ref('app');
	$ticket=array_shift($o->pieces);
	

	$hash=\aw2\session_ticket\get(["main"=>$ticket],null,null);
	if(!$hash){
		//header("HTTP/1.1 404 Not Found");
		echo 'Ticket is invalid: ' . $ticket;
		echo "<script type=spa/axn axn='core.run_script' alert='This form has expired, please refresh and start again.'> </script>";
		exit();			
	}
	
	$ticket_activity=json_decode($hash['ticket_activity'],true);
		
	\controllers::set_qs($o);
	$app['active']['controller'] = 'ticket';
	$app['active']['ticket'] = $ticket;
	
	if(\aw2_library::is_live_debug()){
		$live_debug_event=\aw2_library::get('@live_debug.app_debug_event');
		$live_debug_format=\aw2_library::get('@live_debug.app_debug_format');

		$live_debug_event['action']='app.route.found';
		$live_debug_event['stream']='ticket';
		$live_debug_event['ticket']=$ticket;
		$live_debug_event['qs']=\aw2_library::get('qs');
		\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
	}	
		
		if(isset($ticket_activity['service'])){
			
			$hash['service']=$ticket_activity['service'];
			$result=\aw2\service\run($hash,null,[]);
			echo $result;

			if(\aw2_library::is_live_debug()){
				$live_debug_event['action']='app.done';
				\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
			}	
			exit();	
		}
	
	/**	
		if(!isset($ticket_activity['module'])){
			echo 'Ticket is invalid for module: ' . $ticket;
			exit();			
		}		
		
		
		self::$module= $ticket_activity['module'];
		self::module_parts();

		if(isset($ticket_activity['collection']))
			$app['active']['collection'] = $ticket_activity['collection'];
		else
			$app['active']['collection'] = $app['collection']['modules'];
			
		$app['active']['module'] = self::$module;
		$app['active']['template'] = self::$template;


		$result=\aw2_library::module_run($app['active']['collection'],self::$module,self::$template,null,$hash);

		echo $result;
	**/ 
		if(\aw2_library::is_live_debug()){
			$live_debug_event['action']='app.done';
			\aw2\live_debug\publish_event(['event'=>$live_debug_event,'bgcolor'=>'#E7E0C9']);
		}	
		
			
	exit();	
	return ;
}


