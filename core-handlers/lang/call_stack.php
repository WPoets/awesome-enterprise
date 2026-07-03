<?php

namespace aw2\call_stack;

function is_within_context($obj_find){
	$stack=\aw2_library::get_array_ref('call_stack');
	$reverse=array_reverse ($stack);
	$flag=true;

	foreach ($reverse as $key => $value) {
		if(isset($value['obj_type']) && ($value['obj_type']==='module' || $value['obj_type']==='template')){
			$flag=false;
			break;
		}
		if(isset($value['#_obj_#']) && $value['#_obj_#']['obj_find']===$obj_find)
			break;
	}
	
	return $flag;

}

function push_context($obj_type,$stack_path,$name){
	//php8OK	
	$call_id=\aw2_library::get_rand(6);
	$stack_id=$obj_type . ':' .  $name . ':' . $call_id;

	$obj=array();
	$obj['obj_type']=$obj_type;
	$obj['name']=$name;
	$obj['stack_path']=$stack_path;
	$obj['name']=$name;
	$obj['obj_find']=$obj_type . ':' . $stack_path;

	$info=array('#_no_set_allowed_#'=>true,'obj'=>$obj);

	$context=array('#_no_set_allowed_#'=>true,'#_obj_#'=>$obj,'info'=>$info,'$'=>array());

	$call_stack=&\aw2_library::get_array_ref('call_stack');
	$call_stack[$stack_id]=$context;

    $stack=&\aw2_library::get_array_ref();
	$stack[$stack_path]=&$call_stack[$stack_id];	
	return $stack_id;
}

function pop_context($stack_id){
	//php8OK	

	$call_stack=&\aw2_library::get_array_ref('call_stack');


    $stack=&\aw2_library::get_array_ref();

    $reverse=array_reverse ($call_stack);
	
	foreach ($reverse as $key => $value) {
		unset($call_stack[$key]);

        if(isset($value['#_obj_#'])){
            unset($stack[$value['#_obj_#']['stack_path']]);
        }
        elseif(isset($value['obj_type'])){
			unset($stack[$value['obj_type']]);
		}

		if($key==$stack_id)
				break;

	}
	
	$call_stack=&\aw2_library::get_array_ref('call_stack');
    $stack=&\aw2_library::get_array_ref();

    foreach ($call_stack as $key => $value) {

		if(isset($value['#_obj_#']['stack_path'])){
            $stack[$value['#_obj_#']['stack_path']]=&$call_stack[$key];	
             continue;   
        }
		if(isset($value['obj_type'])){
			$stack[$value['obj_type']]=&$call_stack[$key];	
		}
    }   
}

