<?php

add_action( 'admin_menu', 'aw2_menu::register_menus' );
add_action( 'admin_bar_menu', 'aw2_menu::register_admin_bar_menus',2000 );

class aw2_menu{
	static function register_menus(){
		$awicon="PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE3LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyMHB4IiBoZWlnaHQ9IjEwcHgiIHZpZXdCb3g9IjAgMCAyMCAxMCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjAgMTAiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxnPg0KCQkJPHBhdGggZmlsbD0iI0YwNTkyQiIgZD0iTTcuNTQ4LDcuOTA0Yy0wLjY2NywwLTEuMTEyLTAuMjMzLTEuMzI5LTAuNjk3QzUuODQxLDcuNjc4LDUuMzU2LDcuOTE1LDQuNzYzLDcuOTE1DQoJCQkJYy0wLjU5MiwwLTEuMDktMC4yMDEtMS40OTMtMC42Yy0wLjQwNC0wLjQtMC42MDUtMC45MjYtMC42MDUtMS41OGMwLTAuOTUxLDAuMjc5LTEuNzEyLDAuODQtMi4yNzgNCgkJCQljMC41NTktMC41NjcsMS4yNjctMC44NSwyLjEyNS0wLjg1YzAuMDIyLDAsMC4wNDQsMCwwLjA2NSwwYzAuMzY0LDAsMC42OTgsMC4wNjIsMS4wMDMsMC4xODUNCgkJCQljMC4xOTUtMC4xMjMsMC40MzQtMC4xODUsMC43MTUtMC4xODVjMC4yNzksMCwwLjQ4LDAuMDM2LDAuNjA0LDAuMTA5QzcuOTUxLDMuMjUzLDcuODgxLDMuNzgyLDcuODEsNC4zMDINCgkJCQlDNy43MzcsNC44MjEsNy42ODYsNS4xOSw3LjY1Nyw1LjQwN2MtMC4wNjYsMC40OTQtMC4wOTksMC44NS0wLjA5OSwxLjA2OGMwLDAuMjkxLDAuMTE2LDAuNDM3LDAuMzUsMC40MzcNCgkJCQljMC4xODEsMCwwLjM3NC0wLjEyMywwLjU3OC0wLjM2NmMwLjIwMi0wLjI0NCwwLjM4LTAuNjI2LDAuNTMzLTEuMTVjMC4yMTIsMC4xODksMC4zNTYsMC40LDAuNDM2LDAuNjMyDQoJCQkJYy0wLjIxLDAuNjkxLTAuNDk0LDEuMTc1LTAuODUsMS40NTVDOC4yNDksNy43NjQsNy44OTYsNy45MDQsNy41NDgsNy45MDR6IE01Ljc5MywzLjQ1NmMtMC40NjUsMC0wLjgzMywwLjE3Ny0xLjEwNiwwLjUyOQ0KCQkJCUM0LjQxNSw0LjMzOCw0LjI1Myw0Ljg2Myw0LjIwMiw1LjU2QzQuMTk1LDUuNjI1LDQuMTkxLDUuNjg3LDQuMTkxLDUuNzQ2YzAsMC4zNTYsMC4wODUsMC42MzksMC4yNTcsMC44NDkNCgkJCQljMC4xNywwLjIxMSwwLjM5LDAuMzE4LDAuNjU4LDAuMzE4YzAuNDU4LDAsMC44MDMtMC4zMjUsMS4wMzUtMC45NzFsMC4yNjMtMi4zNTVDNi4xOTMsMy41LDUuOTg5LDMuNDU2LDUuNzkzLDMuNDU2eiIvPg0KCQkJPHBhdGggZmlsbD0iI0YwNTkyQiIgZD0iTTEzLjA3NCw2LjExNmMwLDAuNTIzLDAuMjIxLDAuNzg0LDAuNjY0LDAuNzg0YzAuMDE1LDAsMC4wMjcsMCwwLjAzMywwYzAuMjAzLDAsMC4zODUtMC4xMDksMC41NDUtMC4zMjYNCgkJCQljMC4xNi0wLjIxOSwwLjI4LTAuNDkyLDAuMzYtMC44MThjMC4xNzUtMC42NjgsMC4yNjItMS4yNzYsMC4yNjItMS44MkgxNC44NGMtMC4zMDYsMC0wLjU0MS0wLjA3LTAuNzA5LTAuMjA3DQoJCQkJYy0wLjE3NS0wLjE1OS0wLjI2Mi0wLjM1My0wLjI2Mi0wLjU3OGMwLTAuMjI2LDAuMDY5LTAuNDA4LDAuMjA3LTAuNTQ1YzAuMTY4LTAuMTUyLDAuMzc1LTAuMjI5LDAuNjIxLTAuMjI5DQoJCQkJYzAuNTM4LDAsMC45MDIsMC4yNzYsMS4wOTEsMC44MjdjMC41MDgtMC4xMjMsMS4wNDYtMC4yOTMsMS42MTMtMC41MTJ2MC42MjJjLTAuNTM4LDAuMjAzLTEuMDQsMC4zNi0xLjUwNCwwLjQ2OQ0KCQkJCWMwLjAwOCwwLjA1OSwwLjAxMSwwLjE0MiwwLjAxMSwwLjI1MWMtMC4wMjksMS41MDMtMC4zODIsMi41OTMtMS4wNTcsMy4yN2MtMC4zOTksMC40LTAuOTE4LDAuNTk5LTEuNTUzLDAuNTk5DQoJCQkJYy0wLjYzNiwwLTEuMTEtMC4yNjgtMS40MjMtMC44MDZjLTAuMzU2LDAuNTQ1LTAuOTAxLDAuODE3LTEuNjM0LDAuODE3Yy0wLjQzNywwLTAuNzk0LTAuMTU1LTEuMDc0LTAuNDYzDQoJCQkJYy0wLjI4LTAuMzA5LTAuNDItMC42NzQtMC40Mi0xLjA5NmMwLTAuMDcyLDAuMDA0LTAuMTYsMC4wMTItMC4yNjFDOC45MSw0LjgzLDkuMDM4LDMuNzExLDkuMTM5LDIuNzM3DQoJCQkJQzkuNDAxLDIuNjUsOS42MywyLjYwNyw5LjgyNiwyLjYwN2MwLjUxNiwwLDAuNzc0LDAuMTc4LDAuNzc0LDAuNTM0YzAsMC4xMzgtMC4wNTQsMC42MTUtMC4xNjQsMS40MjcNCgkJCQljLTAuMTM4LDEuMDI0LTAuMjA4LDEuNjAxLTAuMjA4LDEuNzI4YzAsMC4xMjcsMC4wNDksMC4yNTMsMC4xNDgsMC4zNzZjMC4wOTgsMC4xMjMsMC4yNDEsMC4xODYsMC40MjUsMC4xODYNCgkJCQljMC4xODYsMCwwLjM1MS0wLjA4NSwwLjQ5Ni0wLjI1MWMwLjE0NC0wLjE2NywwLjI1MS0wLjM3MSwwLjMxNi0wLjYxbDAuMzgyLTMuMjU5YzAuMjMyLTAuMDg3LDAuNDc5LTAuMTMxLDAuNzQxLTAuMTMxDQoJCQkJYzAuNDcyLDAsMC43MDksMC4xNzgsMC43MDksMC41MzRjMCwwLjEzMi0wLjA1MiwwLjU1Ny0wLjE1MywxLjI3NUMxMy4xNDYsNS40MzMsMTMuMDc0LDYsMTMuMDc0LDYuMTE2eiBNMTQuNDkxLDMuMTUyDQoJCQkJYzAsMC4xMjMsMC4xMDUsMC4xODUsMC4zMTcsMC4xODVjMC4wMiwwLDAuMDQzLDAsMC4wNjUsMGMtMC4wNTEtMC4xOTctMC4xMjgtMC4yOTUtMC4yMjktMC4yOTUNCgkJCQlDMTQuNTQyLDMuMDQyLDE0LjQ5MSwzLjA3OSwxNC40OTEsMy4xNTJ6Ii8+DQoJCTwvZz4NCgkJPGc+DQoJCQk8Zz4NCgkJCQk8cmVjdCB4PSIwIiBmaWxsPSIjRjA1OTJCIiB3aWR0aD0iMS4xMTEiIGhlaWdodD0iMTAiLz4NCgkJCQk8cmVjdCB4PSIwIiB5PSIwIiBmaWxsPSIjRjA1OTJCIiB3aWR0aD0iMy4zMzQiIGhlaWdodD0iMS4xMTEiLz4NCgkJCQk8cmVjdCB4PSIwIiB5PSI4Ljg4OSIgZmlsbD0iI0YwNTkyQiIgd2lkdGg9IjMuMzM0IiBoZWlnaHQ9IjEuMTExIi8+DQoJCQk8L2c+DQoJCQk8Zz4NCgkJCQk8cmVjdCB4PSIxOC44ODkiIGZpbGw9IiNGMDU5MkIiIHdpZHRoPSIxLjExMSIgaGVpZ2h0PSIxMCIvPg0KCQkJCTxyZWN0IHg9IjE2LjY2NiIgeT0iMCIgZmlsbD0iI0YwNTkyQiIgd2lkdGg9IjMuMzM0IiBoZWlnaHQ9IjEuMTExIi8+DQoJCQkJPHJlY3QgeD0iMTYuNjY2IiB5PSI4Ljg4OSIgZmlsbD0iI0YwNTkyQiIgd2lkdGg9IjMuMzM0IiBoZWlnaHQ9IjEuMTExIi8+DQoJCQk8L2c+DQoJCTwvZz4NCgk8L2c+DQo8L2c+DQo8L3N2Zz4=";
		
		add_menu_page('Services', 'Services - Awesome Enterprise', 'develop_for_awesomeui','awesome-services', 'edit.php?post_type=aw2_app','dashicons-admin-network',2 );
		add_menu_page( 'Awesome Enterprise', 'Awesome Enterprise', 'develop_for_awesomeui', 'awesome-enterprise', 'aw2_menu::awesome_enterprise_menu_page',  'data:image/svg+xml;base64,'.$awicon,3 ); 
		
		//register services
		$handlers=&aw2_library::get_array_ref('handlers');
		
		foreach($handlers as $key => $handler){
			
			if(isset($handler['post_type']) && isset($handler['@service']) && $handler['@service'] === true ){
				if(!isset($handler['service_label'])) continue;
				
				add_submenu_page('awesome-services', $handler['service_label'], $handler['service_label'],  'develop_for_awesomeui','edit.php?post_type='.$handler['post_type']);
			}
		}
		
		add_submenu_page('awesome-enterprise', 'Apps - Awesome Enterprise', 'Apps', 'develop_for_awesomeui', 'edit.php?post_type=aw2_app' );
		add_submenu_page( 'awesome-enterprise', 'Core - Awesome Enterprise', 'Awesome Core', 'develop_for_awesomeui', 'edit.php?post_type=awesome_core' );
		//add_submenu_page('awesome-enterprise', 'Manage Cache - Awesome enterprise', 'Manage Cache', 'develop_for_awesomeui','awesome-enterprise-cache' ,'aw2_apps_library::manage_cache');
		//register apps menu
		$registered_apps=&aw2_library::get_array_ref('apps');
		ksort($registered_apps);
		
		foreach($registered_apps as $key => $app){
			add_menu_page($app['name'], $app['name'].' App', 'manage_options', 'awesome-app-'.$app['slug'], function() use($app){aw2_apps_library::show_app_pages($app);}, 'dashicons-admin-multisite',3);
			
			foreach($app['collection'] as $collection_name => $collection){
				
				if(isset($collection['post_type']))
					add_submenu_page('awesome-app-'.$app['slug'], $app['name'] . ' ' . $collection_name, $collection_name,  'develop_for_awesomeui','edit.php?post_type='.$collection['post_type']);
				else
					add_submenu_page('awesome-app-'.$app['slug'], $app['name'] . ' ' . $collection_name, $collection_name,  'develop_for_awesomeui','display_source.php?source='.$collection['source']);
			}
			//add_submenu_page('awesome-app-'.$app->slug, $app->name . ' config', 'Config',  'develop_for_awesomeui','post.php?post=' . $app->post_id . '&action=edit');
		}
		
	}
	
	static function awesome_enterprise_menu_page(){
		$nginx_purge_url = add_query_arg( array( 'nginx_helper_action' => 'purge', 'nginx_helper_urls' => 'all' ) ); 
		
		$nginx_nonced_url = wp_nonce_url( $nginx_purge_url, 'nginx_helper-purge_all' );
		$global_nonced_url = wp_nonce_url( admin_url('admin.php?page=awesome-enterprise&awesome_purge=global'), 'global_nonced-purge_all' );
		$session_nonced_url = wp_nonce_url(admin_url('admin.php?page=awesome-enterprise&awesome_purge=session'), 'session_nonced-purge_all' );
		
		echo '<div class="wrap" style="float: left; width: 100%;">'; 
			echo '<h2>Awesome Enterprise</h2><hr>';
			echo '<h3>Manage Awesome Cache</h3>';
			echo "<a href='".$global_nonced_url."' class='page-title-action'>Purge Global Cache (Modules & Taxonomy etc)</a> <br /><br />"; //11       	
			echo "<a href='".$nginx_nonced_url."' class='page-title-action'>Purge NGINX Cache</a> <br /><br />";
			echo "<a href='".$session_nonced_url."' class='page-title-action'>Purge Session Cache (Search. OTP & self expiry)</a> <br /><br />";//12
		echo '</div>';	
		
		echo '<div class="wrap" style="float: left; width: 100%;">';        	
			echo '<h3>Services</h3>';			
			//register services
			$handlers=&aw2_library::get_array_ref('handlers');
			ksort($handlers);			
			echo "<ul class='inline'>";
			foreach($handlers as $key => $handler){
				if(isset($handler['post_type']) && isset($handler['@service']) && $handler['@service'] === true ){
					if(!isset($handler['service_label'])) continue;
				
					echo "<li style='float:left; width:33%;'>";
						echo "<a href='edit.php?post_type=".$handler['post_type']."'>".$handler['service_label']."</a>";
					echo "</li>";
				}
			}
			echo "</ul>";
		echo '</div>';
		
		echo '<div class="wrap" style="float: left; width: 100%;">'; 
			echo '<h3>Awesome Apps</h3>';			
			$registered_apps=&aw2_library::get_array_ref('apps');
			ksort($registered_apps);			
			echo "<ul class='inline'>";
			foreach($registered_apps as $key => $app){
				echo "<li style='float:left; width:33%;margin-bottom:15px;'>";
					echo "<a href='".$app['path']."'>".$app['name']."</a><br />";
					if(!empty($app['collection']['modules']['post_type'])){
						echo "<a href='edit.php?post_type=".$app['collection']['modules']['post_type']."'>Open Modules</a>";
					}else{
						echo "No Modules";
					}
					if(!empty($app['collection']['config']['post_type'])){
						echo " | <a href='edit.php?post_type=".$app['collection']['config']['post_type']."'>Open Config</a>";
					}else{
						echo " | No Config";
					}
					if(!empty($app['collection']['pages']['post_type'])){
						echo " | <a href='edit.php?post_type=".$app['collection']['pages']['post_type']."'>Open Pages</a>";
					}else{
						echo " | No Pages";
					}
					
					if(!empty($app['collection']['config']['post_type'])){
						
						$args=array(
							'name' => 'rights',
							'post_type' => $app['collection']['config']['post_type'],
							'post_status'=>'any'
						);
						$rights_post = get_posts( $args );
						
						if(!empty($rights_post)){
							$rights_post = $rights_post[0];
							echo "<br /><a href='post.php?post=".$rights_post->ID."&action=edit'>Open Rights</a>";
						}else{
							echo "<br />No Rights";
						}						
					}else{
						echo "<br />No Rights";
					}
				echo "</li>";
			}
			echo "</ul>";			
		echo '</div>';		
				
	}
	
	static function register_admin_bar_menus(){
	  global $wp_admin_bar;
	  
	  if(!current_user_can( 'develop_for_awesomeui' ))
		return;

		$menu_id = 'asf';
		$wp_admin_bar->add_menu(array('id' => $menu_id, 'title' => 'Awesome Enterprise', 'href' => get_admin_url(null,'admin.php?page=awesome-enterprise')));
		
		//$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => 'Pages', 'id' => 'asf-pages', 'href' => get_admin_url(null,'edit.php?post_type=aw2_page'), 'meta' => array('target' => '_blank')));
		//$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => 'Core', 'id' => 'asf-core', 'href' =>get_admin_url(null,'edit.php?post_type=aw2_core'), 'meta' => array('target' => '_blank')));
	}


}