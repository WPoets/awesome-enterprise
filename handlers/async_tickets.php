<?php
namespace aw2\async_tickets;

\aw2_library::add_service('async_tickets.run','Run async tickets',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode){
	
	//$GLOBALS['curTime'] = microtime(true);
	
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract( shortcode_atts( array(
		'tickets'=>null
	), $atts) );
	$path = \aw2_library::$plugin_path . "/libraries";
	require_once $path . '/reactphp/autoload.php';

	if(!$tickets)return 'Payload not defined';
	
	$loop = \React\EventLoop\Factory::create();
	$client = new \Clue\React\Buzz\Browser($loop);	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	$i=0;
	foreach ($tickets as $name=>$value) {
		$ticketid=uniqid();
		$json=json_encode($value['data']);
		$redis->hSet($ticketid,'service',$value['service']);
		$redis->hSet($ticketid,'data',$json);
		$redis->setTimeout($ticketid, 60*60);
		//echo XHR_URL . 't.php?t=' . $ticketid;
		//$i++;
		//	$timeConsumed = round(microtime(true) - $GLOBALS['curTime'],3)*1000; 
		//	echo '<h4>' . $i . '::before content is loaded:' .$timeConsumed . '</h4>';
		//echo XHR_URL . 't.php?t=' . $ticketid;
		$client->get(XHR_URL . 't.php?t=' . $ticketid)->then(
			function (\Psr\Http\Message\ResponseInterface $response) use ($ticketid,$redis,$i)  {
				$redis->unlink($ticketid);
				//echo $response->getBody();
		
			//	$timeConsumed = round(microtime(true) - $GLOBALS['curTime'],3)*1000; 
			//	echo '<h4>' . $i . '::after content is loaded:' .$timeConsumed . '</h4>';
		});
	}	
	$loop->run();
}	


