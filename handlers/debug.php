<?php
namespace aw2\debug;
error_reporting(E_ALL);

set_exception_handler(function($e) {
	error_log('Custom Logging Start');
	error_log($e->getMessage());
	$errno = method_exists($e,'getCode')? $e->getCode() : '';
	$errstr = method_exists($e,'getMessage')? $e->getMessage() : '';
	$errfile = method_exists($e,'getFile')? $e->getFile() : '';
	$errline = method_exists($e,'getLine')? $e->getLine() : '';
	$trace = method_exists($e,'getTrace')? $e->getTrace() : null;
	
	error_log($errfile);
	error_log($errline);
	//error_log(var_dump($trace));
	error_log('Custom Logging End');
	
	header("HTTP/1.0 500"); 
	\aw2\debug\throw_error($errno, $errstr, $errfile, $errline,$trace);
	
	if(function_exists('wp_get_current_user') &&  current_user_can('develop_for_awesomeui')){
		exit('hello' . $e->getMessage());
	} 
	exit('Something weird happened'); //Should be a message a typical user could understand
});
	

function FriendlyErrorType($type) 
{ 
    switch($type) 
    { 
        case E_ERROR: // 1 // 
            return 'E_ERROR'; 
        case E_WARNING: // 2 // 
            return 'E_WARNING'; 
        case E_PARSE: // 4 // 
            return 'E_PARSE'; 
        case E_NOTICE: // 8 // 
            return 'E_NOTICE'; 
        case E_CORE_ERROR: // 16 // 
            return 'E_CORE_ERROR'; 
        case E_CORE_WARNING: // 32 // 
            return 'E_CORE_WARNING'; 
        case E_COMPILE_ERROR: // 64 // 
            return 'E_COMPILE_ERROR'; 
        case E_COMPILE_WARNING: // 128 // 
            return 'E_COMPILE_WARNING'; 
        case E_USER_ERROR: // 256 // 
            return 'E_USER_ERROR'; 
        case E_USER_WARNING: // 512 // 
            return 'E_USER_WARNING'; 
        case E_USER_NOTICE: // 1024 // 
            return 'E_USER_NOTICE'; 
        case E_STRICT: // 2048 // 
            return 'E_STRICT'; 
        case E_RECOVERABLE_ERROR: // 4096 // 
            return 'E_RECOVERABLE_ERROR'; 
        case E_DEPRECATED: // 8192 // 
            return 'E_DEPRECATED'; 
        case E_USER_DEPRECATED: // 16384 // 
            return 'E_USER_DEPRECATED'; 
    } 
    return ""; 
} 


function throw_error($errno, $errstr, $errfile, $errline,$trace=NULL){
		if(\aw2_library::get('debug_config.active')!=='yes') return;	
		
		$folder=LOG_PATH . '/debug/errors';
		if (!file_exists($folder)) {
			mkdir($folder, 0777, true); 
		}
		$t=time();
		$o=new \stdClass;
		$url =$_SERVER['REQUEST_URI'];
		$channel=str_replace('/','_',$url) . '_' . date("Y_m_d_h_i_s",$t);
		$path= $folder . '/' . $channel . '.html';
		$fp = fopen($path, 'a');
		$main=\util::var_dump(\aw2_library::get('module'),true);
		$main .='<br>' . $errno . $errstr . $errfile . $errline;
		$main .=\aw2_library::generateCallTrace();
		$main.=\util::var_dump(\aw2_library::get('env'),true);
		$main.=\util::var_dump(\aw2_library::get('env.last_shortcode'),true);
		
		if($trace==NULL)
			$trace=\aw2_library::generateCallTrace();
		
		// create data array to store data
		$data=array(
			"err_no"		=> $errno,
			"err_str"		=> $errstr,
			"err_file"		=> $errfile,
			"err_line"		=> $errline,
			"module_html"	=> \util::var_dump(\aw2_library::get_array_ref('module'),true),
			"env_html"		=> \util::var_dump(\aw2_library::get_array_ref(),true),
			"trace_html"	=> \util::var_dump($trace,true),
			"stack_html"	=> \util::var_dump(\aw2_library::get_array_ref('call_stack'),true),
			"block_html"	=> \util::var_dump(\aw2_library::get_array_ref('error_config','last_shortcode'),true),
			"server_json"	=> json_encode($_SERVER),
			"request_json"	=> json_encode($_REQUEST),
			"url"			=> $url
		);

		// pass data array to store into db
		store_error($data);
		
		fwrite($fp,$main);
	
}
// error handler function 
function myErrorHandler($errno, $errstr, $errfile, $errline)
{	
	if(\aw2_library::get('debug_config.active')!=='yes') return;
	
	if (!headers_sent()) {
			//setcookie("wordpress_no_cache", 'error', time()+100);  /* expire in 1 hour */
	}


	/*
	set_error_handler

		1. We record the error in database
		2. We return false - which means PHP will also handle error as it wants
		3. Execution will continue from next line after where the error occured
	*/	
	
	if($errstr == 'mysqli::real_connect() expects parameter 5 to be integer, string given') return;
	if($errfile == '/var/www/loantap.in/htdocs/wp-admin/includes/file.php') return;
	if($errfile == '/var/www/loantap.in/htdocs/wp-content/plugins/wordpress-seo/inc/class-wpseo-meta.php') return;

	
	
	
	$errno=FriendlyErrorType($errno);
	$folder=LOG_PATH . '/debug/errors';
	if (!file_exists($folder)) {
		mkdir($folder, 0777, true); 
	}
	$t=time();
	$o=new \stdClass;
	$url =$_SERVER['REQUEST_URI'];
	$channel=str_replace('/','_',$url) . '_' . date("Y_m_d_h_i_s",$t);
	$path= $folder . '/' . $channel . '.html';
	$fp = fopen($path, 'a');
	$main=\util::var_dump(\aw2_library::get('module'),true);
	$main .='<br>' . $errno . $errstr . $errfile . $errline;
	$main .=\aw2_library::generateCallTrace();
	$main.=\util::var_dump(\aw2_library::get('env'),true);
	$main.=\util::var_dump(\aw2_library::get('env.last_shortcode'),true);
	
	// create data array to store data
	$data=array(
		"err_no"		=> $errno,
		"err_str"		=> $errstr,
		"err_file"		=> $errfile,
		"err_line"		=> $errline,
		"module_html"	=> \util::var_dump(\aw2_library::get_array_ref('module'),true),
		"env_html"		=> \util::var_dump(\aw2_library::get_array_ref(),true),
		"trace_html"	=> \util::var_dump(\aw2_library::generateCallTrace(),true),
		"stack_html"	=> \util::var_dump(\aw2_library::get_array_ref('call_stack'),true),
		"block_html"	=> \util::var_dump(\aw2_library::get_array_ref('error_config','last_shortcode'),true),
		"server_json"	=> json_encode($_SERVER),
		"request_json"	=> json_encode($_REQUEST),
		"url"			=> $url
	);

	// pass data array to store into db
	store_error($data);
	
	fwrite($fp,$main);

	
    return false;
}

// store's the error data with in the db
function store_error($data) {
	return;
	try {
		// return when $data is not present
		if(empty($data))return;
		
		// getting fields from array
		$fields = implode(",", array_keys($data));
		
		// remove unwanted character from string
		foreach( array_keys($data) as $key ) {
			$values[] = addslashes($data[$key]);
		}
		
		// implode array to insert into db
		$newdata = "'" . implode("','", $values) . "'";
		
		// generate query using newdata
		$sql = ("INSERT INTO aw_logs.errors ($fields) VALUES ($newdata)");

		// store data in db
		if(!\aw2_library::$mysqli)\aw2_library::$mysqli = \aw2_library::new_mysqli();
		\aw2_library::$mysqli->query($sql);
	}

	//catch exception
	catch(Exception $e) {
		return;
	}
	
}

$old_error_handler = set_error_handler("\aw2\debug\myErrorHandler");
if($old_error_handler)restore_error_handler();



\aw2_library::add_service('debug','Debug Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('debug.ignore','Ignore what is inside',['namespace'=>__NAMESPACE__]);

function ignore($atts,$content=null,$shortcode){
	return;
}

\aw2_library::add_service('debug.setup','Debug Setup',['namespace'=>__NAMESPACE__]);
function setup($atts,$content=null,$shortcode=null){
		\aw2_library::set('debug_config.active','no');

		if(!isset($_COOKIE['debug_output']) || $_COOKIE['debug_output']==='no' )return;
		if(!current_user_can('develop_for_awesomeui')){
			if(!isset($_COOKIE['debug_ticket']))return;
			$t=\aw2\session_ticket\get(["main"=>$_COOKIE['debug_ticket']],null,null);
			if(!$t || !$t['debug_ticket'])return;
		}		
		\aw2_library::set('debug_config.active','yes');
		
		\aw2_library::set('debug_config.starttime',microtime(true));
		
		foreach($_COOKIE as $key=>$value){
			if(\aw2_library::startsWith($key,'debug_')){
				\aw2_library::set('debug_config.' . substr($key, 6),$value);
			}	
		}
		if(\aw2_library::get('debug_config.wp_queries')==='yes'){
			define( 'SAVEQUERIES', true );
		}		
}

function diff_time($start=null){
	if(!$start)$start=\aw2_library::get('debug_config.starttime');
	// Get the difference between start and end in microseconds, as a float value
	$diff = microtime(true) - $start;

	// Break the difference into seconds and microseconds
	$sec = intval($diff);
	$micro = $diff - $sec;

	// Format the result as you want it
	// $final will contain something like "00:00:02.452"
	$final = $sec . str_replace('0.', '.', sprintf('%.3f', $micro));
	return $final;	
}

\aw2_library::add_service('debug.z','Add z for app',['namespace'=>__NAMESPACE__]);
function z($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.z'))return;
	$app=\aw2_library::get_array_ref('app');
	if(!isset($app['collection']['modules']['post_type']))return;
	$post_type=$app['collection']['modules']['post_type'];

	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	if(isset($app['active']['module'])){
		\aw2_library::get_post_from_slug($app['active']['module'],$post_type,$post);
		if($post){
			$str = '<a target=_blank href="' . site_url("wp-admin/post.php?action=edit&post=" . $post->ID) .'">Edit Current Module(' . $post->post_name .')</a><br>';
			\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);
		}
	}
	
		$str = '<a target=_blank href="' . site_url("wp-admin/edit.php?post_type=" . $post_type) .'">Posts Archive</a><br>';
		\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);

		$str = '<br><a target=_blank href="' . site_url("wp-admin/post-new.php?post_type=" . $post_type) .'">Add New</a><br>';
		\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);
	
	
	$args=array(
		'post_type' => $post_type,
		'post_status'=>'publish',
		'posts_per_page'=>500,
		'no_found_rows' => true, // counts posts, remove if pagination required
		'update_post_term_cache' => false, // grabs terms, remove if terms required (category, tag...)
		'update_post_meta_cache' => false, // grabs post meta, remove if post meta required	
		'orderby'=>'title',
		'order'=>'ASC'
	);	

	$str='';	
	$results = new \WP_Query( $args );
	$my_posts=$results->posts;

	foreach ($my_posts as $obj){
		$str .= '<a target=_blank href="' . site_url("wp-admin/post.php?post=" . $obj->ID  . "&action=edit") .'">' . $obj->post_title . '(' . $obj->ID . ')</a>' . '<br>';
	}

	if(empty($service))return;
		\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);
	}


\aw2_library::add_service('debug.wp_queries','WP Queries',['namespace'=>__NAMESPACE__]);
function wp_queries($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.wp_queries'))return;
	global $wpdb;

	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	foreach($wpdb->queries as $query){
			$html=\aw2_library::dump_debug(
			[
				[
					'type'=>'html',
					'value'	=>$query[0]
				]
			]		
			,
			"Query: " . $query[1]
			);
		\aw2\service\run(['service'=>$service . '.html','channel'=>'query'],$html);
	
	}
	
		
}

\aw2_library::add_service('debug.query','Output one query',['namespace'=>__NAMESPACE__]);
function query($atts,$content=null,$shortcode=null){
	extract( shortcode_atts( array(
	'main'=>'',
	'start'=>''
	), $atts, 'dump' ) );

	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
			$html=\aw2_library::dump_debug(
			[
				[
					'type'=>'html',
					'value'	=>$main
				]
			]		
			,
			"Query: Time Taken:" . diff_time($start)
			);
		\aw2\service\run(['service'=>$service . '.html','channel'=>'query'],$html);
		
}


\aw2_library::add_service('debug.flow','Flow',['namespace'=>__NAMESPACE__]);
function flow($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.flow'))return;	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'dump' ) );
	
	//get service to call
	$service=\aw2_library::get('debug_config.output');

	$html=\aw2_library::dump_debug(
	[]		
	,
	$main . ': time:' . diff_time()
	);
	
	\aw2\service\run(['service'=>$service . '.html','channel'=>'flow'],$html);
	
}
	
\aw2_library::add_service('debug.module','Flow',['namespace'=>__NAMESPACE__]);	
function module($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.module'))return;	
	extract( shortcode_atts( array(
	'template'=>'',
	'start'=>''
	), $atts, 'dump' ) );

	//get service to call
	$service=\aw2_library::get('debug_config.output');

	$html=\aw2_library::dump_debug(
	[
		[
			'type'=>'html',
			'value'	=>"Template:: $template"
		],
	
		[
			'type'=>'html',
			'value'	=>"Module Array"
		],
		[
			'type'=>'arr',
			'value'	=>\aw2_library::get('module')
		]
	]		
	,
	"Module: " . \aw2_library::$stack['module']['collection']['post_type'] . '::' . \aw2_library::$stack['module']['slug'] . ' Time Taken:' . diff_time($start)
	);
	\aw2\service\run(['service'=>$service . '.html','channel'=>'flow'],$html);
	
	}
	

	
\aw2_library::add_service('debug.dump','Dump something to messages',['namespace'=>__NAMESPACE__]);		
function dump($atts,$content=null,$shortcode=null)
{
	if(\aw2_library::get('debug_config.active')!=='yes')return;	
	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	extract( shortcode_atts( array(
	'main'=>'',
	'expandlevel'=>1,
	'title'=>null
	), $atts, 'dump' ) );	

	$arr[0]['type']='arr';
	$arr[0]['value']=$main;
	
	$html=\aw2_library::dump_debug($arr,$title);
	\aw2\service\run(['service'=>$service . '.html','channel'=>'messages'],$html);


}


\aw2_library::add_service('debug.html','Dump something to messages',['namespace'=>__NAMESPACE__]);		
function html($atts,$content=null,$shortcode=null)
{
	if(\aw2_library::get('debug_config.active')!=='yes')return;	
	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	extract( shortcode_atts( array(
	'main'=>'',
	'expandlevel'=>1,
	'title'=>null
	), $atts, 'dump' ) );	

	$arr[0]['type']='html';
	$arr[0]['value']=$main;
	
	$html=\aw2_library::dump_debug($arr,$title);
	
	\aw2\service\run(['service'=>$service . '.html','channel'=>'messages'],$html);
	

}
