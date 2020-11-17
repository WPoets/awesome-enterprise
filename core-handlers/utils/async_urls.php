<?php
namespace aw2\async_urls;

\aw2_library::add_service('async_urls.run','Run async tickets',['namespace'=>__NAMESPACE__]);
function run($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
 	extract(\aw2_library::shortcode_atts( array(
		'tickets'=>null
	), $atts) );
	//$path = \aw2_library::$plugin_path . "/libraries";
	//require_once $path . '/reactphp/autoload.php';

	if(!$tickets)return 'Payload not defined';
	
	$loop = \React\EventLoop\Factory::create();
	$client = new \Clue\React\Buzz\Browser($loop);	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_SESSION_CACHE);
	foreach ($tickets as $name=>$value) {
		$ticketid=uniqid();
		$url=$value['url'];
		$json = isset($value['data']) ? json_encode($value['data']) : array();
		$redis->hSet($ticketid,'data',$json);

		if(isset($value['service']))$redis->hSet($ticketid,'service',$value['service']);
		
		$redis->setTimeout($ticketid, 60*60);
		$client->get($url . '?t=' . $ticketid)->then(
			function (\Psr\Http\Message\ResponseInterface $response) use ($ticketid,$redis)  {
				$redis->unlink($ticketid);
			});
	}
	$loop->run();
	
}	


