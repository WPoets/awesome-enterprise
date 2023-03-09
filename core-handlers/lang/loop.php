<?php
namespace aw2\loop;
\aw2_library::add_service('loop.live_arr','Loop Library',['namespace'=>__NAMESPACE__]);
\aw2_library::add_service('loop','Loop Library',['namespace'=>__NAMESPACE__]);


function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'main' => null,
		'start' => null,
		'stop' => null,
		'step' => 1
		), $atts) );
	$pieces=$shortcode['tags'];
	if(count($pieces)!=2)return 'error:You must have exactly two parts to the loop shortcode';
	$ctr=$pieces[1];
	$stack_id=\aw2_library::push_child($ctr,$main);
	$call_stack=&\aw2_library::get_array_ref('call_stack',$stack_id);
	 
	$is_done=false;
	//decide loop or for loop
	
	if($main){
		$is_done=true;
		$items=\aw2_library::get($main);
		if(!is_array($items) && !is_object($items)){
			\aw2_library::set_error('Loop Element is not an Array:' . $main);
			return;
		}

		$call_stack['source']=$items;
		$call_stack['count']=0;
        if(is_countable($items))
            $call_stack['count']=count($items);
		
		$index=1;
		$output=array();
		foreach ($items as $key =>&$item) {
			if(isset($call_stack['break_on_next']))break;
			$call_stack['index']=$index;
			$call_stack['counter']=$index-1;
			$call_stack['item']=&$item;
			$call_stack['key']=$key;
			
			$call_stack['first']=false;
			$call_stack['last']=false;
			$call_stack['between']=false;
			$call_stack['odd']=false;
			$call_stack['even']=false;
			
			if ($index % 2 != 0)
				$call_stack['odd']= true;
			else
				$call_stack['even']= true;
			if($index==1)$call_stack['first']=true;
			if($index==$call_stack['count'])$call_stack['last']=true;
			if($index!=$call_stack['count'])$call_stack['between']=true;
			$output[]=\aw2_library::parse_shortcode($content,false,'yes');
			$index++;
		}
		$string=implode($output);	
		
	}
	if(!is_null($start) ){
		$is_done=true;
		$index=1;
		$call_stack['start']=$start;
		$call_stack['stop']=$stop;
		$call_stack['step']=$step;
		
		$string=null;
		$current=$start;
		$call_stack['count']=0;
		if($stop>=$start){
			$output=array();
			for ($i = $start; $i <= $stop; $i+=$step) {
			if(isset($call_stack['break_on_next']))break;				
				$call_stack['index']=$index;
				$call_stack['counter']=$index-1;
				$call_stack['item']=$i;
			
				$call_stack['first']=false;
				$call_stack['last']=false;
				$call_stack['between']=false;
				$call_stack['odd']=false;
				$call_stack['even']=false;
				
				if ($index % 2 != 0)
					$call_stack['odd']= true;
				else
					$call_stack['even']= true;
				if($index==1)$call_stack['first']=true;
				if($index==$call_stack['count'])$call_stack['last']=true;
				if($index!=$call_stack['count'])$call_stack['between']=true;
				$output[]=\aw2_library::parse_shortcode($content);
				$index++;
			}
			$string=implode($output);	
		}
		else{
			$output=array();
			for ($i = $start; $i >= $stop; $i=$i-$step) {
				$call_stack['index']=$index;
				$call_stack['counter']=$index-1;
				$call_stack['item']=$i;
			
				$call_stack['first']=false;
				$call_stack['last']=false;
				$call_stack['between']=false;
				$call_stack['odd']=false;
				$call_stack['even']=false;
				
				if ($index % 2 != 0)
					$call_stack['odd']= true;
				else
					$call_stack['even']= true;
				if($index==1)$call_stack['first']=true;
				if($index==$call_stack['count'])$call_stack['last']=true;
				if($index!=$call_stack['count'])$call_stack['between']=true;
					
				$output[]=$string . \aw2_library::parse_shortcode($content);
				$index++;
			}
			$string=implode($output);	
			
		} 
		
	}	
	
	if($is_done===false)return 'error: either array or start stop must be provided';
			
	\aw2_library::pop_child($stack_id);	
	$return_value=\aw2_library::post_actions('all',$string,$atts);
	return $return_value;
}



function live_arr($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
    
    extract(\aw2_library::shortcode_atts( array(
        'main' => null,
        'start' => null,
        'stop' => null,
        'step' => 1
        ), $atts) );
    $pieces=$shortcode['tags'];
    if(count($pieces)!=3)return 'error:You must have exactly two parts to the loop shortcode';
    $ctr=$pieces[2];
    $stack_id=\aw2_library::push_child($ctr,$main);
    $call_stack=&\aw2_library::get_array_ref('call_stack',$stack_id);
    

    $index=1;
    $output=array();

    $items=\aw2_library::get($main);
        
    while (!empty($items)) {
        $item = array_shift($items);
        \aw2_library::set($main,$items);


        if(isset($call_stack['break_on_next']))break;
        $call_stack['index']=$index;
        $call_stack['counter']=$index-1;
        $call_stack['item']=&$item;
        $call_stack['key']=$key;
        
        $call_stack['first']=false;
        $call_stack['last']=false;
        $call_stack['between']=false;
        $call_stack['odd']=false;
        $call_stack['even']=false;
        
        if ($index % 2 != 0)
            $call_stack['odd']= true;
        else
            $call_stack['even']= true;
        if($index==1)$call_stack['first']=true;
        if($index==$call_stack['count'])$call_stack['last']=true;
        if($index!=$call_stack['count'])$call_stack['between']=true;
        $output[]=\aw2_library::parse_shortcode($content,false,'yes');
        $index++;
        $items=\aw2_library::get($main);
		
    }
    $string=implode($output);    
        
            
    \aw2_library::pop_child($stack_id);    
    $return_value=\aw2_library::post_actions('all',$string,$atts);
    return $return_value;
}

