<?php 


define('SITE_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] );
define('HOME_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] );

define( 'REDIS_HOST', '127.0.0.1' );
define( 'REDIS_PORT', '6379' );
define( 'REDIS_DATABASE_GLOBAL_CACHE', '4' );
define( 'REDIS_DATABASE_SESSION_CACHE', '5' );
define( 'REDIS_DATABASE_DB', '10' );

define('AWESOME_PATH', '/var/www/awesome-enterprise');
