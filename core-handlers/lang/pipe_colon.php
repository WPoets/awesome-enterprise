<?php
namespace aw2\pipe_colon;


function pipe_run($atts = array(),$reply) {
    if (!isset($atts['@']))
        throw new \InvalidArgumentException("Error: '@' missing in pipe:");

    $pipe=array();
    if (isset($atts['@'])) {
        $pipe['@'] = $atts['@'];
        unset($atts['@']);
    }

    // All remaining attributes go into 'c.atts'
    $pipe['atts']=array();
    if (!empty($atts)) {
        $pipe['atts'] = $atts;
    }
    $pipe['atts']['main']=$reply;
    return \aw2_library::service_run($pipe['@'], $pipe['atts'],null);
}


