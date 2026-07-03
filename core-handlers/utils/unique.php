<?php
namespace aw2\unique;

\aw2_library::add_service('unique.token', 'Generate a random string token of specified length', ['func'=>'token', 'namespace'=>__NAMESPACE__]);
function token($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'length' => 12,
        'chars' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
    ), $atts, 'token'));
    
    // Convert length to integer
    $length = (int)$length;
    
    // Ensure we have enough characters for the requested length
    if (strlen($chars) < $length) {
        // Repeat the chars string until it's long enough
        $chars = str_repeat($chars, ceil($length / strlen($chars)));
    }
    
    // Generate and return the random token
    return substr(str_shuffle($chars), 0, $length);
}

\aw2_library::add_service('unique.id', 'Generate a unique identifier using uniqid', ['func'=>'id', 'namespace'=>__NAMESPACE__]);
function id($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'prefix' => ''
    ), $atts, 'id'));
    
    return uniqid($prefix);
}

\aw2_library::add_service('unique.longid', 'Generate a longer unique identifier with entropy', ['func'=>'longid', 'namespace'=>__NAMESPACE__]);
function longid($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'prefix' => ''
    ), $atts, 'longid'));
    
    return uniqid($prefix, true);
}

\aw2_library::add_service('unique.guid', 'Generate a GUID/UUID v4', ['func'=>'guid', 'namespace'=>__NAMESPACE__]);
function guid($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
    ), $atts, 'guid'));
    
    // Generate 16 random bytes
    if (function_exists('random_bytes')) {
        $data = random_bytes(16);
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $data = openssl_random_pseudo_bytes(16);
    } else {
        // Fallback to less secure method if no better options available
        $data = '';
        for ($i = 0; $i < 16; $i++) {
            $data .= chr(mt_rand(0, 255));
        }
    }
    
    // Set version to 4 (random)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    // Format as GUID string
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}