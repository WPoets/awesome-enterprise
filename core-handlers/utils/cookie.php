<?php
namespace aw2\cookie;

// Set cookie
\aw2_library::add_service('cookie.set', 'Set a cookie with specified parameters', ['func'=>'set_cookie', 'namespace'=>__NAMESPACE__]);

function set_cookie($atts, $content=null, $shortcode=null) {
    if(empty($atts['key']))
        throw new \Exception('key parameter is required for cookie.set');
        
    if(!isset($atts['value']))
        throw new \Exception('value parameter is required for cookie.set');
        
    // Set default parameters
    $expire = isset($atts['expire']) ? (int)$atts['expire'] : 0;
    $path = isset($atts['path']) ? $atts['path'] : '/';
    $domain = isset($atts['domain']) ? $atts['domain'] : '';
    $secure = isset($atts['secure']) && $atts['secure'] === 'true';
    $httponly = isset($atts['httponly']) && $atts['httponly'] === 'true';
    
    // Set the cookie
    $result = setcookie(
        $atts['key'],
        $atts['value'],
        $expire,
        $path,
        $domain,
        $secure,
        $httponly
    );
    
    // Return status
    return array(
        'status' => $result ? 'success' : 'error',
        'message' => $result ? 'Cookie set successfully' : 'Failed to set cookie',
        'cookie_info' => array(
            'key' => $atts['key'],
            'value' => $atts['value'],
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        )
    );
}

// Get cookie
\aw2_library::add_service('cookie.get', 'Get cookie value', ['func'=>'get', 'namespace'=>__NAMESPACE__]);

function get($atts, $content=null, $shortcode) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'default' => '#_not_set_#'
    ), $atts, 'aw2_get'));
    
    if($main === null)
        throw new \Exception('main parameter is required for cookie.get');
    
    $return_value = '';
    
    if(isset($_COOKIE[$main])) {
        $return_value = $_COOKIE[$main];
    }
    
    if($return_value === '' && $default !== '#_not_set_#') {
        $return_value = $default;
    }
    
    return $return_value;
}

// Check if cookie is valid
\aw2_library::add_service('cookie.is.valid', 'Check if cookie exists and is valid', ['func'=>'is_cookie_valid', 'namespace'=>__NAMESPACE__]);

function is_cookie_valid($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'is_cookie_valid'));
    
    if($main === null)
        throw new \Exception('main parameter is required for cookie.is.valid');
    
    if(!isset($_COOKIE[$main]))
        return false;
        
    // If expire parameter is set, check if cookie is expired
    if(isset($atts['expire'])) {
        $expire_time = (int)$atts['expire'];
        if($expire_time > 0 && $expire_time < time())
            return false;
    }
    
    return true;
}

// Compare cookie value
\aw2_library::add_service('cookie.comp.equal', 'Compare cookie value with string', ['func'=>'comp_equal', 'namespace'=>__NAMESPACE__]);

function comp_equal($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'with' => null
    ), $atts, 'comp_equal'));
    
    if($main === null || $with === null)
        throw new \Exception('Both main and with parameters are required for cookie.comp.equal');
    
    if(!isset($_COOKIE[$main]))
        return false;
        
    return $_COOKIE[$main] === $with;
}

// Dump cookies
\aw2_library::add_service('cookie.dump', 'Dump cookie values', ['namespace'=>__NAMESPACE__]);

function dump($atts, $content=null, $shortcode) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false)
        return;
        
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'dump'));
    
    if($main !== null) {
        if(!isset($_COOKIE[$main])) {
            return \util::var_dump('Cookie not found: ' . $main, true);
        }
        return \util::var_dump($_COOKIE[$main], true);
    }
    
    if(empty($_COOKIE)) {
        return \util::var_dump('No cookies found', true);
    }
    
    return \util::var_dump($_COOKIE, true);
}

// Echo cookies
\aw2_library::add_service('cookie.echo', 'Echo cookie values', ['func'=>'_echo', 'namespace'=>__NAMESPACE__]);

function _echo($atts, $content=null, $shortcode) {
    if(\aw2_library::pre_actions('all', $atts, $content) == false)
        return;
        
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'dump'));
    
    if($main !== null) {
        if(!isset($_COOKIE[$main])) {
            return \util::var_dump('Cookie not found: ' . $main);
        }
        return \util::var_dump($_COOKIE[$main]);
    }
    
    if(empty($_COOKIE)) {
        return \util::var_dump('No cookies found');
    }
    
    return \util::var_dump($_COOKIE);
}