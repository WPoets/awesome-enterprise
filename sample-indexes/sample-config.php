<?php 

//define('ROOT_APP', 'home_pages');
define('SITE_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] );
define('HOME_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] );

define( 'REDIS_HOST', '127.0.0.1' );
define( 'REDIS_PORT', '6379' );
define( 'REDIS_DATABASE_GLOBAL_CACHE', '4' );
define( 'REDIS_DATABASE_SESSION_CACHE', '5' );
define( 'REDIS_DATABASE_DB', '10' );

define('AWESOME_PATH', '/var/www/awesome-enterprise');

define('CONNECTIONS',
	array(
		'base_code'=>array(
			'connection_service'=>'folder_conn',
			'path'=>'/var/www/awnxt.thearks.in/base-code',
			'redis_db'=>101,
			'read_only'=>true,
			'cache_expiry'=>300
		),
		'common_code'=>array(
			'connection_service'=>'wp_conn',
			'db_name'=>'alpha_wordp',
			'db_user'=>'alphawoecKAE',
			'db_password'=>'c6ZQpHWYoX5r',
			'db_host'=>'localhost',
			'redis_db'=>102,
			'cache_expiry'=>600

		),
		'cdn_code'=>array(
			'connection_service'=>'url_conn',
			'url'=>'https://cdn.getawesomestudio.com/code',
			'redis_db'=>103,
			'read_only'=>true,
			'cache_expiry'=>300
		)
		
	));

//define('CODE_DEFAULT_CONNECTION','base_code');

/* define database connections - that you want to use in awesome. */
define('DB_CONNECTIONS',
	array(
		'primary_db'=>array(
			'host'=>DB_HOST,
			'user'=>DB_USER,
			'password'=>DB_PASSWORD
		),
		'external_db'=>array(
			'host'=>'localhost',
			'user'=>'dcwo',
			'password'=>'CB1ey'
		)
	));
//This is required so that we can use mysqli.* as a shortcode since it is already there. In a new system this is not required
define('MYSQLI_CONNECTION','primary_db');    