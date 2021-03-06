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

Checkout the [existing issues](https://github.com/WPoets/awesome-enterprise/issues) for solutions or upcoming fixes. 

If you don't find your issue already listed, do [create an issue](https://github.com/WPoets/awesome-enterprise/issues/new), please include as much detail as you can.


### Changelog  



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
