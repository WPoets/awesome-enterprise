<?php
namespace aw2\workflow;

\aw2_library::add_service('workflow.steps.run', 'Executes workflow steps in sequence', ['func'=>'steps_run', 'namespace'=>__NAMESPACE__]);

function steps_run($atts, $content=null, $shortcode = array()) {
    // Validate context name is provided
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('workflow.steps.run: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    // Validate context starts with @
    if(strpos($context, '@') !== 0) {
        throw new \Exception('workflow.steps.run: Context name must start with @');
    }
    
    // Setup the context stack
    $stack_id = \aw2\call_stack\push_context('workflow', $context, $context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);
	$call_stack['info']=array();
    $info = &$call_stack['info'];
    $info['index'] = 0;
    $info['state'] = array();
	$info['state']['@result']='';

    // Backup existing handler if any
    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    // Get workflow array using build_array
    $workflow = \aw2\common\build_array($atts, $content);
    
    // Validate steps exist
    if(!isset($workflow['steps']) || !is_array($workflow['steps'])) {
        throw new \Exception('workflow.steps.run: No steps defined in workflow');
    }
    
    $info['steps'] = $workflow['steps'];
    $info['steps_count'] = count($workflow['steps']);
    
    // Execute each step
    $index = 0;

    foreach($info['steps'] as $key => &$step) {
        // Validate step has service
        if(!isset($step['service'])) {
            throw new \Exception("workflow.steps.run: No service defined for step $key");
        }
        
        $info['index'] = $index;
        $info['step'] = $step;
        $info['key'] = $key;
        
        $service = $step['service'];
        $atts = array();
        
        // Setup data for service
        $atts['data'] = isset($step['data']) ? $step['data'] : array();
        $atts['info'] = $info;
        $atts['state'] = $info['state'];
        
        // Run service and update state
        try {
            $result = \aw2_library::service_run($service, $atts, null);

            if(isset($step['result']) && isset($step['result']['state_path'])) {
                $state_path = $step['result']['state_path'];
                $info['state'][$state_path] = $result;
            }

        } catch(\Exception $e) {
            throw new \Exception("workflow.steps.run: Error in step '$key' with service '$service': " . $e->getMessage());
        }
        
        $index++;

    }
    
    \aw2\call_stack\pop_context($stack_id);
    
    // Restore existing handler if any
    \aw2_library::set('handlers.' . $context, $backup_service);
    
    // Return final result from state
    return $info['state']['@result'];
}