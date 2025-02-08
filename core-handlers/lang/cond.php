<?php
namespace aw2\cond;

// Register cond.select service
\aw2_library::add_service('cond.select', 'Select between two services based on condition', ['func'=>'cond_select', 'namespace'=>__NAMESPACE__]);

// cond.select service
function cond_select($atts, $content=null, $shortcode=null) {
    // Check condition
    $check = \aw2\common\cond_check($atts);
    
    if($check === true) {
        // Get then service
        $then = \aw2\common\atts_to_service($atts, 'then');
        if(empty($then['name']))
            throw new \Exception('then.@ service not specified in cond.select');
            
        return \aw2_library::service_run($then['name'], $then['atts'],null);
    }
    
    // Get else service
    $else = \aw2\common\atts_to_service($atts, 'else');
    if(empty($else['name']))
        throw new \Exception('else.@ service not specified in cond.select');
        
    return \aw2_library::service_run($else['name'], $else['atts'],null);
}
// cond.true service
\aw2_library::add_service('cond.true', 'Conditional execution if condition is true', ['func'=>'cond_true', 'namespace'=>__NAMESPACE__]);
function cond_true($atts, $content=null, $shortcode=null) {
    $cond = \aw2\common\cond_check($atts);
    if ($cond === true) {  // Strict comparison
        return \aw2_library::parse_shortcode($content);
    }
    return '';
}

// cond.false service
\aw2_library::add_service('cond.false', 'Conditional execution if condition is false', ['func'=>'cond_false', 'namespace'=>__NAMESPACE__]);
function cond_false($atts, $content=null, $shortcode=null) {
    $cond = \aw2\common\cond_check($atts);
    if ($cond === false) {  // Strict comparison
        return \aw2_library::parse_shortcode($content);
    }
    return '';
}

