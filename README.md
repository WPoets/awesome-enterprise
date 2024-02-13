<p align="center">
<a href="https://www.wpoets.com/" target="_blank"><img width="200"src="https://www.wpoets.com/wp-content/uploads/2018/05/WPoets-logo-1.svg"></a>
</p>

# Awesome Enterprise Framework

Awesome Enterprise is a shortcode based low code platform for PHP and WordPress.

You can set it up using composer

`composer create-project wpoets/awesome-enterprise`

For using custom bootsrtap files, you will need to modify the your webserver conf file to do something like below 

`location /bs/ {
  try_files $uri $uri/ /index-bootstrap.php?$args;
}
`
Above example is for Nginx server.

## Contribute

### Reporting a bug

Check out the [existing issues](https://github.com/WPoets/awesome-enterprise/issues) for solutions or upcoming fixes. 

If you don't find your issue already listed, do [create an issue](https://github.com/WPoets/awesome-enterprise/issues/new). Please include as much detail as you can.


### Changelog 

##### 3.5.6

* Fixed: Undefined array key "post_type"
* Fixed: Undefined array key "config"
* Fixed: if LOG_PATH is not defined do nothing.

##### 3.5.5

* Improved: added debug code for folder connection
* Fixed: switched to real path to ensure only correct files are allowed to be downloaded.
* Fixed: removed depreciation notices to improve compatibility with PHP 8.2
* Fixed: updated dependency of less.php to 4 to improve compatibility with PHP 8.2
* Fixed: moved the require_once to ensure that they are only used in specific cases and not all scenarios.
* Fixed: made changes so that error log becomes compatible with mysql 8.
* Fixed: made session cache dynamic, instead of using default.
* Fixed:  the notice of 'undefined variable css'


##### 3.5.4

* Improved: Added support for 'ROOT_APP' and if it is defined then in a Non-WordPress case also, we can add support for module based pages.

##### 3.5.3.3

* Fixed: dbconn was giving issues when using multi query
* Fixed: changed mysqli.php file to use the dbconn service when functions are directly called.

##### 3.5.3.2
* Improved: added new constant TIMEZONE, that can be used to define the timezone for php execution.

* Fixed: dbconn was not working whenever env variable was cached.
* Fixed: loop was giving a warning when we were looping objects
* Fixed: warning 'start_time' not defined
 
##### 3.5.3.1
* Fixed: dbconn had issues - 'set' was saving blank value.

##### 3.5.3
* Improved: added new shortcode _redis_db.get_ to ensure that we can read key from across any redis db specified in shortcode eg. `[redis_db.get redis_sample_key db='2' field='debug_code' /]`.
* Improved: Added ability to pass code as content in _code.highlight_ shortcode.

* Fixed: converted > & < etc to htmlentities to ensure that spa script tags don't get executed by mistake.

##### 3.5.2
* Improved: performance of apps being delivered from CDN, also added new key 'read_only'=>true, in connection to ensure remote code always stays cached.
* Improved: introduced _service.modules.list_, _service.module.get_,  _app.collection.modules.list_ and _app.collection.module.get_
* Improved: introduced MYSQLI_CONNECTION to set the default settings to use for mysqli.* database connection

* Fixed: fixed the issue with settings, it was resetting
* Fixed: if database had special character in name then it was giving sql error, fixed it.
* Fixed: fixed issues in the collection.get for folder_conn
* Fixed: fixed the situation so that incase error_log itself creates an issue, it does not disturb the flow.

##### 3.5.1
* Improved: added support for _arr.unshift_
* Improved: introduced _loop.live_arr_ to enable looping an array that is changing at runtime
* Improved: improved the way add_service function was working

* Fixed: fixed the issue with template type aw2_arr, it was getting executed
* Fixed: added unique keys for metas
* Fixed: fixed the issue where missing modules were checked everytime it was accessed by url_connector

##### 3.5
* Improved: *arr.create* now supports two new attributes _path_ and _raw_content_ making it even easier to create arrays 
* Improved: Introduced 2 new shortcodes to enable additional db connections as needed by the system *dbserver.connect* & *dbconn.register*. Also, mysqli shortcode now uses this new connection and keeps the WORDPRESS default connection and kept in primary_db
* Improved: Added support for *o.arr_push* to push a new item to an existing array, it uses array_push php function
* Improved: Introduced the support for *util.constant*. It returns an associative array with the names of all the constants and their values
* Improved: Added support for template types while adding new templates. By adding *template_type='aw2_arr'* will allows us to define templates that return arrays. 
* Improved: Added support for *_atts_arr* to *service.run* shortcode so that we can pass to services the attributes at a template level. 
* Fixed: Removed notices in case obj_id or obj_type is not defined.
* Fixed: Issue with converting a post_type to service.
* Fixed: Issue with .esc_sql - it was using WordPress function, converted it to a awesome function.
* Fixed: Issue: warning "failed to open stream" for external files.
* Fixed: Issue while redirecting query strings were not respected.

##### 3.4.4
* Improved: Added support for redis hash keys in session cache using _'session_cache.del'_ and _'session_cache.hlen'_
* Improved: Added support for m.sort for sorting arrays, it supports 'asort','arsort','krsort','ksort','rsort','sort','array_multisort'.
* Improved: Added support for _template.anon.run_ so that we can have anonymous code behave like template.  
* Fixed: Removed 404 header status in case ticket is not found.

##### 3.4.3
* Improved: Added support for redis hash keys in session cache using _'session_cache.hset'_ and _'session_cache.hget'_
* Improved: Added t2 controller that allows to run services and better handling of expired tickets. 
* Improved: removed a few warnings from session_tickets of PHP 8.1.

##### 3.4.2
* Improved: Errors can now be viewed directly after switching on debugging mode
* Improved: SQL queries now carry more info for debugging when view in sql process list
* Improved: Removed commented code.
 
##### 3.4.1
* Improved: Added support for `code.highlight` shortcode.

##### 3.4
* Improved: Added support for live debugging using "debugger app" all major activities now support the debugging data.
* Improved: Compatibility with php 7.4 and 8.1.
* Improved: Added `code.dump` to allow the ability to output the code shortcodes when needed.
* Improved: Added `js.run_on_activity` to wrap javascript code to execute on user interaction with the browser.
* Improved: Changed the way we can enable/disable or delete the code cache.   
* Fixed: In the wp_conn the module name is converted to lowercase before finding it in post table. 
* Fixed: Fixes in url connector.

##### 3.3
* Improved: Added support of checking for empty and Zero using not_blank and is_blank conditionals.
* Fixed: Showing http 404 headers for missing ticket urls from earlier 200.
* Improved: Added ability to check if a module exists within a collection using module_exists.
* Fixed: made changes to reduce the notices when using PHP 8.1. 

##### 3.2.3.1
* Fixed: In certain situations, error logging was resulting in multiple DB connections sometimes resulting in error 504.


##### 3.2.3
* Improved: added support to use wordpress user login as virtual session using wp_vession. To use it, make sure you have added following line in your __rights__ module in the app
`
[arr.create set='app.rights'] 
  [access mode='logged'  title='Login To APP'/]
  
  [auth a1 method=wp_vsession all_roles=''  /]
[/arr.create]
`
all_roles means, all the roles must be assigned to the user, it takes comma seprated list of roles and capabilites.
* Fixed: rights module usage was broken. 

##### 3.2.2
* Fixed: when using external connectors default apps settings were not being used

##### 3.2.1
* Fixed: aw2.module was missing the support for using external connectors.
* Improved: Added _m.number_to_word_ modifier to allows us to convert numeral to word representation. 


##### 3.2
* Improved: Added support to include the services and apps from external db, folder or cdn using external connectors.
* Improved: Added support to register external Apps by creating a module 'apps' in core and using following syntax 
`[app.register error-log title="Error Log"]
	[collection]
    [config  connection=external_code post_type=c-errlog-app /]
    [modules connection=external_code post_type=m-errlog-app /]
  [/collection]  
[/app.register]	`
* Imporved: External service can be registerd by specifying the _'connection'_ attibute in the services tag 
` [services.add form_control2  connection=external_code service_label='Form Control 2 Service' post_type='form_control2' desc='Form Control 2 Service' /]`
* Improved: Added ability to register collections and modules as services, using syntax shown below
`[collection.register partners_services.application  service_label='Samples' post_type='m_samples' desc='Samples Service' /]
`
and 
`
[module.register partners_services.application.l1  service_label='Samples' collection.post_type='m_samples' module='loops-sample' desc='Samples Service' /]
`
* Improved: Added support to use '.' in service name while registering it, so now you can do something like 
`[services.add partner_services.xyz  service_label='Samples' post_type='m_samples' desc='Samples Service' /]` and call this service using `[partners_services.xyz.check-folder-service /]`

##### 3.1.1
* Improved: Now Awesome Exception errors will get logged only when wp_debug is set to true in the wp-config.php file.
* Fixed: Bunch of syntax errors

##### 3.1.0
* Improved: Added support for creating empty array using m.empty_array
* Fixed: Access to undeclared static property: aw2_library::$cdn
* Fixed: PHP Notice: Undefined index: posts
* Fixed: esc_sql was using normal WordPress function, converted into a local function so that it can work in non-WP scenario
* Fixed: For Vsession if user_id is not available then email is set as ID.


##### 3.0.9.8
* Fixed: "Login Required" setting was not having any impact
* Fixed: "Trying to access array offset on value of type null" error in certain cases.

##### 3.0.9.7
* Improved: added suport for capturing the apps in use
* Fixed: added support for returning default value when aw2.get value is null.

##### 3.0.9.6
* Improved: added suport for capturing the modules and post_type in use. To enable this you will need to set followin gin wp-config.php, and you will need the latest version for Debug Handler

	`define( 'REDIS_DATABASE_DEBUG_CACHE', '12' );
	define( 'SET_DEBUG_CACHE', true );`

##### 3.0.9.5
 
 
##### 3.0.9.4 
* Fixed: __.exists__ in some situation used to return blank string, instead of boolean false. 

##### 3.0.9.3
* Fixed: removed the depracated money_format function and replaced it with NumberFormatter class, intl pecl libaray is now required.

##### 3.0.9.2
* Improved: added suport for fetching stream usage data from Redis using __redis_db.stream_fetch_usage__  eg. [redis_db.stream_fetch_usage stream_id="c_apply_layout" o.set=template.stream_data /]
* Fixed: rhs data type was wrongly calcuted while logging the data mismatch issue.

##### 3.0.9
* Fixed: The tracking of modules & apps being used is now kept in Redis streams, to ensure speed does not become an issue. It can be enabled by using defining REDIS_LOGGING_DB in wp-config.php file.
* Fixed: Removed Service logging separator.
* Fixed: Added support for WP Function esc_sql() in for non wp usage.

##### 3.0.8
* Improved: Moved the way less variables are registered to 'less-variables' module in the core.
* Fixed: get_option function moved to aw2_library so that we can access options from WordPress when using non WordPress flow.

##### 3.0.7
* Improved: Added support for logging usage of all post types and modules. This feature is disabled by default and can be enabled by adding define("AWESOME_LOG_DEBUG", "yes") in the wp_config of the specific site


##### 3.0.6
* Fixed: quote_comma was fixed
* Fixed: added a check so that if layout module does not exists it is handled by page.php
* Fixed: Notice - Trying to access array offset of bool.

##### 3.0.5
* Fixed: multiple php notices & warnings
* Fixed: in certain cases error login was creating it's own errors
* Improved: added support to capture the position of shortcode while executing content type
* Fixed: Notice - Trying to access array offset on the value of type bool
* Fixed: Notice - Undefined index: REQUEST_METHOD
* Fixed: Trying to get property 'request' of non-object
* Fixed: If the object is passed for comparison, data type mismatch failed.

##### 3.0.4
* Improved: Added support to read Redis stream data.
* Improved: Added support to trap all errors and exception in a separate database table.
* Improved: File manipulation and added support to parse SQL error log.
* Fixed: Issues with settings not loading properly
* Fixed: m modifier was calling wrong the_content_filter
* Fixed: Changed the table structure for storing the evaluated conditional values
* Fixed: $slug was not defined in 404 function.


##### 3.0.3
* Fixed: aw2.get was giving wrong data when we called [aw2.get post.post_title] in single-content-layout
* Improved: added support to log evaluated conditional values as well for php 7
* Improved: Added depreciation notice for aw2.get shortcode which should now be using wp.get
* Removed WP specific function to it's own utility class.
* Fixed: quote comma modifier fixed for empty return

##### 3.0.2
* Added missing Mobile Detect Library  

##### 3.0.1  
* Added missing util.php
* Added Simple-MySQLi Library

##### 3.0.0  
* Initial release

## We're Hiring!

<p align="center">
<a href="https://www.wpoets.com/careers/"><img src="https://www.wpoets.com/wp-content/uploads/2020/11/work-with-us_1776x312.png" alt="Join us at WPoets, We specialize in designing, building and maintaining complex enterprise websites and portals in WordPress."></a>
</p>
