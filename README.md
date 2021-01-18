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

##### 3.0.4
* Improved: Added support to read Redis stream data.
* Improved: Added support to trap all errors and exception in a separate database table.
* Improved: File manipulation and added support to parse sql error log.
* Fixed: Issues with settings not loading properly
* Fixed: m modifier was calling wrong the_content_filter
* Fixed: Changed the table strucutre for storing the evaluated conditional values


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