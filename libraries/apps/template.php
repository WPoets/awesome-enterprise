<!doctype html>
<!--[if lt IE 7]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

<head>
	<meta charset="<?php echo bloginfo('charset'); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no"/>

	<link rel='dns-prefetch' href='cdn.getawesomestudio.com'>
	<link rel="stylesheet" href="//cdn.getawesomestudio.com/lib/bootstrap/3.3.6/css/bootstrap.min.css">
	<?php
	if(aw2_library::get('site_settings.opt-favicon.exists')){?>
	<link rel="icon" href="<?php
		echo aw2_library::get('site_settings.opt-favicon');
	 ?>"> 
	<?php
	}
	if(aw2_library::get('site_settings.opt-favicon.exists')){
		?>
	<!--[if IE]>
		<link rel="shortcut icon" href="<?php
		echo aw2_library::get('site_settings.opt-favicon');
	 ?>">
	<![endif]-->	 
	<?php 	
	}
	wp_head(); 
	 
	if(aw2_library::get('app.options.skin') !='-1'){
		aw2_library::get_post_from_slug(aw2_library::get('app.options.skin'),'aw2_module',$module_post);	
		echo aw2_library::parse_shortcode($module_post->post_content);
	}

	?>
</head>
<body <?php body_class(); ?>>
	<!--#header_area --->
	<?php 
	$app_page_type = aw2_library::get('app.default_pages');
	
	if(aw2_library::get('app.options.header.exists') && aw2_library::get_post_from_slug('header`',$app_page_type,$module_post)) {
	} else {
		aw2_library::get_post_from_slug('header','aw2_core',$module_post);	
		
	}
	echo aw2_library::parse_shortcode($module_post->post_content);
	?>
	<!--#header_area end --->
	<div class="wrapper">
		<div class="content">
			<main class="main" role="main">
				
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php 
					$action = aw2_library::get('app.action');
					$template= 'home';
					if($action=='single'){
						$template ='single';
					}
					
					if($action=='archive'){
						$template ='archive';
					}
					if($action=='page'){
						$template = get_query_var( 'pagename' );
						if(!aw2_library::get_post_from_slug($template,$app_page_type,$module_post))
							$template='home';
					}
					
					aw2_library::get_post_from_slug($template,$app_page_type,$module_post);
					echo aw2_library::parse_shortcode($module_post->post_content);	
					
					?>
				</article>

			</main><!-- /.main -->
		</div><!-- /.content -->
	</div><!--#container -->
	<!--#footer_area --->
	<?php 

	if(aw2_library::get('app.options.footer.exists') && aw2_library::get_post_from_slug('footer',$app_page_type,$module_post)){
	} else {
		aw2_library::get_post_from_slug('footer','aw2_core',$module_post);	
	}
	echo aw2_library::parse_shortcode($module_post->post_content);
	?>
	<!--#footer_area end--->
	<?php wp_footer(); ?>
</body>
</html>