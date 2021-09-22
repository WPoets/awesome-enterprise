<?php
namespace aw2\_if;

\aw2_library::add_service('if.user_can_access','Returns if user can access particular url',['namespace'=>__NAMESPACE__]);

function user_can_access($atts,$content=null,$shortcode){
    if(isset($atts['link'])){
        $res = check_access($atts['link']);
    }else{
        $res = false;
    }
    
    return \aw2_library::post_actions('all', $res, $atts);
}

function check_access($link){
    
    if(substr( $link, 0, strlen(home_url()) ) != home_url()) return true;
    if(current_user_can('administrator'))return true;
    
    $path = str_replace(home_url(), '', $link);
    $path = explode('/', $path);
    
    $app_slug = $path[1];
    $registered_apps=\aw2_library::get_array_ref('apps');
    if(!isset($registered_apps[$app_slug])) return true;
    
    //app is valid, check the rights

    $options = \aw2_library::get_option('awesome-app-' . $app_slug);
    if(!isset($options) || ('1' != $options['enable_rights'])) return true;
    
    if('1' == $options['enable_vsession']){
        $vsession_key = $options['vsession_key'] ? $options['vsession_key'] : 'email';
        $vsession = check_vsession2($vsession_key, $app_slug);
        if($vsession) return true;
    }
    
    $module = $path[2];
    
    if('ajax' == $module) $module = $path[3];
    if('css' == $module || 'js' == $module || 't' == $module) return true;
    if(!$module) $module = 'home';

    $modular_check = check_modulewise_rights_only($options, $app_slug, $module);
    if($modular_check) return true;

    return false;
}

function check_vsession2($vsession_key, $app_slug){
    if(!isset($_COOKIE['aw2_vsession'])) return false;
    
    $name = $app_slug.'_valid';
    $vsession=\aw2\vsession\get([],null,'');
    
    if(isset($vsession[$name]) && $vsession[$name] === 'yes'){
        return true;
    }
    return false;
}

function check_modulewise_rights_only($options, $app_slug, $module){
    if(!is_user_logged_in()) return false;
    
    $roles = $options['roles'];
    if( 0 == count($roles) ) return true;		//return true if no roles selected
    
    foreach($roles as $key => $val){
        if(current_user_can($key)){
            if('1' == $val['access']) return true;
            
            $acees_cap = 'm_' . $app_slug . '_' . $module;
            if(current_user_can($acees_cap)) return true;
        }
    }
    
    return false;
}