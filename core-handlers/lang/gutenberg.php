<?php

namespace aw2\gutenberg;


function call_service($fields, $block) {
    $result = array();
    
    // Process each field
    foreach ($fields as $path => $field) {
        // Skip if no type defined
        if (!isset($field['type'])) {
            continue;
        }
        
        // Split path into array parts
        $parts = explode('.', $path);
        
        // Get the value based on field type
        $value = null;
        switch ($field['type']) {
            case 'attributes-repeater':
                if (isset($field['value']) && is_array($field['value'])) {
                    $value = parse_atts($field['value']);
                }
                break;
                
            case 'innerblocks':
                if (isset($field['content'])) {
                    $value = $field['content'];
                }
                break;
                
            default:
                if (isset($field['value'])) {
                    $value = $field['value'];
                }
                break;
        }
        
        // Skip if no value determined
        if ($value === null) {
            continue;
        }
        
        // Build nested array structure
        $current = &$result;
        $lastIndex = count($parts) - 1;
        
        foreach ($parts as $i => $part) {
            if ($i === $lastIndex) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = array();
                }
                $current = &$current[$part];
            }
        }
    }
    

    $atts=$result;

    $path=explode("/", $block->name)[1];
    $service=\aw2_library::get('gb_blocks.' . $path . '.render.service');

//\util::var_dump($service);
//\util::var_dump(\aw2_library::$funcstack);
//\util::var_dump($atts);
//\util::var_dump(\aw2_library::get('handlers'));

$reply=\aw2_library::service_run($service, $atts,null);

    return $reply;
}

function parse_atts($atts=array()){


    if(empty($atts))
        return $atts;
    
    $updated=array();

    foreach ($atts as $key => $item) {

        $name=$item['name'];    
        $updated[$name]=resolve_value($item['value']);

        $type=$item['type'];
        
        if($type==='path')$updated[$name]=\aw2_library::get($updated[$name]);
        if($type==='request_safe')$updated[$name]=\aw2\request2\get($updated[$name]);
        if($type==='str')$updated[$name]=(string)$updated[$name];
        if($type==='int')$updated[$name]=(int)$updated[$name];
        if($type==='num')$updated[$name]=(float)$updated[$name];
        if($type==='arr_empty')$updated[$name]=array();
        if($type==='comma')$updated[$name]=explode(',', (string)$updated[$name]);

        if($type==='bool'){
            if($updated[$name] === '' || $updated[$name] === 'false')
                $updated[$name]=false;
            else
                $updated[$name]=(bool)$updated[$name];
        }


        if($type==='null')$updated[$name]=null; 
        if($type==='service')$updated[$name]=\aw2_library::parse_single('[' . $updated[$name] . ']');

	} 
    return $updated;
 
}


function resolve_value($value){

    $pattern = '/{\s*\"/';

    //This is to allow json to go through because json always starts with { and then a double quote
    if (is_string($value) && preg_match($pattern, $value)!==1 && strpos($value, '{') !== false && strpos($value, '}') !== false) {

        $startpos = strrpos($value, "{");
        $stoppos = strpos($value, "}");
        if ($startpos === 0 && $stoppos===strlen($value)-1 and strpos($value, " ")===false) {
            $value=str_replace("{","",$value);		
            $value=str_replace("}","",$value);		
            $value=\aw2_library::get($value);
        }
        else{
            $patterns = array();
            $patterns[0] = '/{{(.+?)}}/';
            $patterns[1] = '/{(.+?)}/';

            $replacements = array();
            $replacements[0] = '[$1]';
            $replacements[1] = '[aw2.get $1]';
            $value=preg_replace($patterns, $replacements, $value);
            $value=\aw2_library::parse_shortcode($value);
        }

    }
    if(is_string($value)){
        $parts=explode(':',$value,2);
        if(count($parts)===2){
            if($parts[0]==='get')$value=\aw2_library::get($parts[1]);
            if($parts[0]==='request2')$value=\aw2\request2\get(['main'=>$parts[1]]);
            if($parts[0]==='x')$value=\aw2_library::parse_single('[' . $parts[1] . ']');				
            if($parts[0]==='int')$value=(int)$parts[1];
            if($parts[0]==='num')$value=(float)$parts[1];
            if($parts[0]==='str')$value=(string)$parts[1];
            if($parts[0]==='null')$value=null;
            if($parts[0]==='arr' && $parts[1]==='empty')$value=array();
            if($parts[0]==='comma')$value=explode(',', (string)$parts[1]);
            if($parts[0]==='bool'){
                if($parts[1] === '' || $parts[1] === 'false')
                    $value=false;
                else
                    $value=(bool)$parts[1];
            }
        }
    }

    return $value;
}




\aw2_library::add_service('gb.register', 'Register a gutenberg block', ['func' => 'gb_register', 'namespace' => __NAMESPACE__]);

function gb_register($atts, $content = null, $shortcode) {
    // Check if main attribute exists
    if (!isset($atts['main']) || empty($atts['main'])) {
        throw new \Exception('Main attribute is required for Gutenberg block registration');
    }

    $main = $atts['main'];
    
    // Initialize array builder
    $ab = new \array_builder();
    
    // Parse content
    $items = $ab->parse($content);
    
    // Validate required sections
    if (!isset($items['config'])) {
        throw new \Exception('Config section is required in Gutenberg block definition');
    }
    
    if (!isset($items['render'])) {
        throw new \Exception('Render section is required in Gutenberg block definition');
    }
    
    // Set the items in the library
    return \aw2_library::set('gb_blocks.' .$main, $items);
}



\aw2_library::add_service('gb.render.blocks', 'Register a gutenberg block', ['func' => 'render_blocks', 'namespace' => __NAMESPACE__]);

function render_blocks($atts, $content = null, $shortcode) {
    // Check if main attribute exists
    if (!isset($atts['main'])) {
        throw new \Exception('Main attribute is required for Gutenberg block registration');
    }

    $main = $atts['main'];
    
    // Return early if empty
    if (empty($main)) {
        return '';
    }

    $out = ''; // Initialize output string
    
    // Iterate through blocks
    foreach ($main as $block) {
        $out .= render_block($block);
    }
    //\util::var_dump($out);
    //\util::var_dump($main);
    return $out;
}


