<?php
namespace aw2\async_tickets;

\aw2_library::add_service('async_tickets.run','Run async tickets',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode){
	

	$GLOBALS['curTime'] = microtime(true);
	
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'tickets'=>null,
		'app'=>null,
		'payload_size'=>1,
	), $atts) );
	//$path = \aw2_library::$plugin_path . "/libraries";
	//require_once $path . '/reactphp/autoload.php';

	if(!$tickets)return 'Payload not defined';
	
	$loop = \React\EventLoop\Factory::create();
	$client = new \Clue\React\Buzz\Browser($loop);	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$payload_size=1;
	$i=0;
	while(count($tickets)>0) {
		$ctr=1;
		$ticketid=uniqid();
		$payload=array();
		$redis->hSet($ticketid,'app',$app);
		$redis->hSet($ticketid,'validation',json_encode(array()));
		
		while(count($tickets)>0 && $ctr<=$payload_size) {
			$payload[]=array_shift($tickets);
			$ctr++;
			$i++;
		}
		$json=json_encode($payload);
		$redis->hSet($ticketid,'payload',$json);
		$redis->setTimeout($ticketid, 60*60);
//		echo SITE_URL . '/ts/' . $ticketid;
	
		

		$client->get(SITE_URL . '/ts/' . $ticketid)->then(
			function (\Psr\Http\Message\ResponseInterface $response) use ($ticketid,$redis,$i)  {
				$redis->unlink($ticketid);
				//echo $response->getBody();
		
				//$timeConsumed = round(microtime(true) - $GLOBALS['curTime'],3)*1000; 
				//echo '<h4>' . $i . '::after content is loaded:' .$timeConsumed . '</h4>';
		});
		
	}
	
	$loop->run();
}	


