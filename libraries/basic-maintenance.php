<?php
/*
Maintenance mode ON
*/

add_action('wp', 'monoframe_template_redirect', 1);

function monoframe_template_redirect(){
	global $monomyth_options;
	
	
	
	if(aw2_library::get('site_settings.opt-m-mode.exists'))
	{  
		$m_mode = aw2_library::get('site_settings.opt-m-mode');
		$m_mode_html = aw2_library::get('site_settings.opt-m-html');
		if (!is_user_logged_in() && $m_mode==='on') { 
		  echo $m_mode_html;
		  exit;
		}
	}
}