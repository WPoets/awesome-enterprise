<?php
namespace aw2\out_colon;


function out_run($atts = array(),$reply) {

    if (isset($atts['@destroy'])) {
        return;
    }
    if (isset($atts['@debug.dump'])) {
        return \util::var_dump($reply,true);
    }
    if (isset($atts['@debug.echo'])) {
        \util::var_dump($reply);
        return;
    }

    if (isset($atts['@set'])) {
        \aw2_library::set($atts['@set'],$reply);
        return;
    }

    if (isset($atts['@env.set'])) {
        \aw2_library::set($atts['@env.set'],$reply);
        return;
    }
    if (isset($atts['@func.set'])) {
        \aw2_library::set('func.'.$atts['@func.set'],$reply);
        return;
    }
    if (isset($atts['@module.set'])) {
        \aw2_library::set('module.'.$atts['@module.set'],$reply);
        return;
    }
    if (isset($atts['@template.set'])) {
        \aw2_library::set('template.'.$atts['@template.set'],$reply);
        return;
    }
     
    
    if (!isset($atts['@']))
        throw new \InvalidArgumentException("Error: '@' missing in out:");

    $out=array();
    $out['@'] = $atts['@'];
    unset($atts['@']);

    // All remaining attributes go into 'out.atts'
    $out['atts']=array();
    if (!empty($atts)) {
        $out['atts'] = $atts;
    }
    $out['atts']['main']=$reply;
    return \aw2_library::service_run($out['@'], $out['atts'],null);
}


