<?php
namespace aw2\parser;


function parse_array($arr){

//    \util::var_dump($arr);
    //when
    if(isset($arr['when'])){
        $block=$arr['when'];
        //parse the attributes
        $c_reply=when_check($block);
		if($c_reply===false)return '';
	}



    //when.1
    if(isset($arr['when.1'])){
        $block=$arr['when.1'];
        //parse the attributes
        if(!empty($block['atts']))$block['atts']=parse_atts($block['atts']);
		$c_reply=when_check($block);
		if($c_reply===false)return '';
	}

   
    //do the service
    if (!isset($arr['do']['service'])) {
        throw new \Exception("Service is not set in the array.");
    }

    $block=$arr['do'];

     //$arr[content]
    //parse the attributes
    if(!empty($block['atts']))$block['atts']=parse_atts($block['atts']);
    $content=null;
    if(isset($block['content'])){
		$content=$block['content'];
	}

    if(isset($arr['content'])){
		$content=\aw2_library::get($arr['content']);
	}

    $reply=service_run($block['service'], $block['atts'], $content);

//    \util::var_dump('before pipe');
//    \util::var_dump($block);    
//    \util::var_dump($reply);    
     //pipe
     if(isset($arr['pipe'])){
        $block=$arr['pipe'];
		$reply=pipe_run($block,$reply);
	}


    //pipe.1
    if(isset($arr['pipe.1'])){
        $block=$arr['pipe.1'];
        //parse the attributes
		$reply=pipe_run($block,$reply);
	}

//    \util::var_dump('before out');
//    \util::var_dump($reply);

	if(isset($arr['out'])){
        $block=$arr['out'];
		$final=out_run($block,$reply);
		if(!isset($arr['out.1']))$reply=$final;
	}
	if(isset($arr['out.1'])){
        $block=$arr['out'];
		$reply=out_run($arr['out.1'],$reply);
	}

//    \util::var_dump($reply);

    return $reply;


}

function service_run($service, $atts=array(), $content=null) {
    // Get handlers reference

    $handlers = &\aw2_library::get_array_ref('handlers');
    
    // Split service into parts
    $tags_left = explode('.', $service);

    if(count($tags_left)<2)return '#not_awesome_sc#';

    $sc=array();
	$sc['tags']=$tags_left;

    // Start with no handler
    $handler = null;
    $current = &$handlers;
    $service=null;
    // Keep consuming tags while matches exist
    while (!empty($tags_left)) {
        $tag = $tags_left[0];
        
        if (isset($current[$tag])) {
            // Match found, update handler and move to next level
            $handler = &$current[$tag];
            $current = &$current[$tag];
            $service=$tag;
            array_shift($tags_left);
        } else {
            // No more matches possible, break
            break;
        }
    }
    $sc['tags_left']=$tags_left;
    $sc['handler']=$handler;
    $next_tag=null;
    if (isset($tags_left[0])) {
        $next_tag=$tags_left[0];
    }
    if(!isset($handler['type']))throw new \BadMethodCallException('Handler does not have a type'); 

    	//support for new structure of handler
	if(isset($handler['#call'])){
		$handler['type']='call';
	}

	if(!isset($handler['type']))throw new \BadMethodCallException('Handler does not have a type'); 
	

	$service_type = $handler['type'];

	$fn_name=null;
	switch($service_type){

		case 'call':
			$fn_name=$handler['#call']['namespace'] . '\\' . $handler['#call']['func'];
			break;
		case 'content_type_def':
			$sc['content_type']=$sc['handler'];	
			$sc['handler']=$handlers['content_type_def'];					
			$service='content_type_def';
			
			if(isset($sc['handler'][$next_tag])){
				$service=$next_tag;
				$sc['handler']=$sc['handler'][$next_tag];
				$next_tag=null;
			}	
			$handler = $sc['handler'];
			
			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
			
		case 'collection':
            $sc['collection']=$sc['handler'];	
            //it moves the handler to the collection hardcoded handler
			$sc['handler']=$handlers['collection'];
			$service='collection';
			
			if(isset($sc['handler'][$next_tag])){
				$service=$next_tag;
				$sc['handler']=$sc['handler'][$next_tag];
				$next_tag=null;
			}	
			$handler = $sc['handler'];		
			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
			
		case 'module':
			$sc['collection']=$sc['handler']['collection'];	
			$pre['primary']['module']=$sc['handler']['module'];
			
			if(!isset($pre['primary']['main']))
				$pre['primary']['main']=implode('.',$sc['tags_left']);


			$sc['handler']=$handlers['collection']['run'];
			$service='run';
			
			$handler = $sc['handler'];		

			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			
			
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			
			break;
			
		case 'namespace':
			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;

		case 'awesome':
			if(isset($handler['func']))
				$fn_name=$handler['func'];
			else{
					$fn_name='aw2_' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name='aw2_' .$service . '_'  . $next_tag;
			if (!is_callable($fn_name))$fn_name='aw2_' .$service . '_'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
			
		case 'env_key':
			$pre['primary']['_prefix']=$handler['env_key'];
			$sc['handler']=$handlers['env'];
			$service='env';
			
			if(isset($sc['handler'][$next_tag])){
				$service=$next_tag;
				$sc['handler']=$sc['handler'][$next_tag];
				$next_tag=null;
			}	
			$handler = $sc['handler'];		

			if(isset($handler['func']))
				$fn_name=$handler['namespace'] . '\\' . $handler['func'];
			else{
					$fn_name=$handler['namespace'] . '\\' . $service;					
			}
			if (!is_callable($fn_name) && $next_tag)$fn_name=$handler['namespace'] . '\\'  . $next_tag;
			if (!is_callable($fn_name))$fn_name=$handler['namespace'] . '\\'  . 'unhandled';
			if (!is_callable($fn_name))$fn_name=null;
			break;
	}

	if(!$fn_name)throw new \BadMethodCallException('Handler does not have a func');; 

	$reply = call_user_func($fn_name, $atts, $content, $sc );


    return $reply;

    
}


function when_check($when = array()) {
    if (!isset($when['service']))return true;

    if (isset($when['and']) and  isset($when['or']) )
        throw new \InvalidArgumentException("Error: and or both cannot be there in when:");

    if(isset($when['atts']))$when['atts']=parse_atts($when['atts']);

    $c_reply = service_run($when['service'], $when['atts'], null);
        
    if (!is_bool($c_reply)) 
        throw new \InvalidArgumentException("Error: 'when' condition must return a boolean value.");
    
    if ($c_reply === true && !isset($when['and'])) {
        return true;
    }
    
    if ($c_reply === false && isset($when['and'])) {
        return false;
    }
    if (isset($when['and'])) {
        if(isset($when['and']['atts']))$when['and']['atts']=parse_atts($when['and']['atts']);
        $and_reply = service_run($when['and']['service'], $when['and']['atts'], null);
        if (!is_bool($and_reply)) {
            throw new \InvalidArgumentException("Error: 'and' condition must return a boolean value.");
        }
        if ($and_reply === true) {
            return true;
        }
    }
    
    if (isset($when['or'])) {
        if(isset($when['or']['atts']))$when['or']['atts']=parse_atts($when['or']['atts']);
        $or_reply = service_run($when['or']['service'], $when['or']['atts'], null);
        if (!is_bool($or_reply)) {
            throw new \InvalidArgumentException("Error: 'or' condition must return a boolean value.");
        }
        if ($or_reply === true) {
            return true;
        }
    }
    
    return false;
}


function out_run($out,$reply) {

    if (!isset($out['service']))
    throw new \InvalidArgumentException("Error: service missing in out:");

    $service=$out['service'];    

    if ($service==='@destroy') {
        return;
    }
    if ($service==='@debug.dump') {
        return \util::var_dump($reply,true);
    }
    if ($service==='@debug.echo') {
        \util::var_dump($reply);
        return;
    }
    if ($service==='@env.set') {
        if (!isset($out['atts']['path']))
        throw new \InvalidArgumentException("Error: path missing in out:");

        \aw2_library::set($out['atts']['path'],$reply);
        return;
    }
    if ($service==='@module.set') {
        if (!isset($out['atts']['path']))
        throw new \InvalidArgumentException("Error: path missing in out:");
        \aw2_library::set('module.'.$out['atts']['path'],$reply);
        return;
    }
    if ($service==='@template.set') {
        if (!isset($out['atts']['path']))
        throw new \InvalidArgumentException("Error: path missing in out:");
        \aw2_library::set('template.'.$out['atts']['path'],$reply);
        return;
    }
     
    if (!isset($out['atts']))
        $out['atts'] = array();

    $out['atts'] = parse_atts($out['atts']);
    $out['atts']['main']=$reply;

    return \aw2_library::service_run($out['service'], $out['atts'],null);
}



function pipe_run($pipe,$reply) {
    if (!isset($pipe['service']))
        throw new \InvalidArgumentException("Error: service missing in out:");

    if (!isset($pipe['atts']))
        $pipe['atts'] = array();

    $pipe['atts'] = parse_atts($pipe['atts']);
    $pipe['atts']['main']=$reply;
    return \aw2_library::service_run($pipe['service'], $pipe['atts'],null);
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