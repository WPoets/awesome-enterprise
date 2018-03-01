<?php


aw2_library::add_library('int','Integer Functions');


function aw2_int_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>0
	), $atts, 'aw2_get' ) );
	
	$return_value=aw2_library::get($main,$atts,$content);
	$return_value=(int)$return_value;	
	
	if($return_value===0)$return_value=(int)$default;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_int_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(int)$main;	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



aw2_library::add_library('str','String Functions');

function aw2_str_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'aw2_get' ) );
	
	$return_value=aw2_library::get($main,$atts,$content);
	$return_value=(string)$return_value;	
	
	if($return_value==='')$return_value=(string)$default;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_str_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(string)$main;	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



aw2_library::add_library('num','Number Functions');


function aw2_num_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>0.00
	), $atts, 'aw2_get' ) );
	
	$return_value=aw2_library::get($main,$atts,$content);
	$return_value=(float)$return_value;	
	
	if($return_value===0.00)$return_value=(float)$default;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_num_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=(float)$main;	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



aw2_library::add_library('bool','Boolean Functions');


function aw2_bool_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>false
	), $atts, 'aw2_get' ) );
	
	$return_value=aw2_library::get($main,$atts,$content);
	$return_value=(bool)$return_value;	
	if($return_value===false)$return_value=(bool)$default;
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_bool_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	if($main==='true')
		$return_value=true;
	else{
		if($main==='false'){
			$return_value=false;
		}		
		else{
			$return_value=(bool)$main;
		} 
	}
		
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

aw2_library::add_library('date','date Functions');


function aw2_date_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	'default'=>null
	), $atts, 'aw2_get' ) );
	
	$return_value=aw2_library::get($main,$atts,$content);
	$return_value=new DateTime($return_value);	
	if(!$return_value && $default!==null)
		$return_value==new DateTime($default);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function aw2_date_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	$return_value=new DateTime($main);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}



aw2_library::add_library('arr','Array Functions');


function aw2_arr_set($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;

	extract( shortcode_atts( array(
	'main'=>null
	), $atts) );
	
	unset($atts['main']);
	
	foreach ($atts as $loopkey => $loopvalue) {
		$arr[$loopkey]=$loopvalue;
	}	
	aw2_library::set($main,$arr);
	return;
}

function aw2_arr_create($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	$ab=new array_builder();
	$return_value=$ab->parse($content);
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}




class array_builder{
public $str;
public $stack=array();
public $arr=array();
public $ptr=null;
public $is_api=false;
//public $ctr=0;

public function parse($str){
	$this->str=$str;
	while (!ctype_space($this->str)) {
		//$this->ctr++;
		//d('counter',$this->ctr); 
		//if($this->ctr>=50)die();
		if(empty($this->stack)){
			$this->next_element();
		}
		else{
			$this->within_element();

		}
	}
	if(!empty($this->stack)){
		echo '<br>You have nodes which have not been completed: ';
		util::var_dump($this->stack);
		return '';
	}
	return $this->arr;
}	

private function next_element(){
	$pattern = '/\s*\[([a-zA-Z].*?)(\/]|])/';
	$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>No elements found in the above string.';
		die();
	}
	$text=$match[1][0];	
	$state=$match[2][0];
	$next_char= strlen($match[2][0]) + $match[2][1];	
	$this->str=substr($this->str,$next_char);

	$this->new_node($text,$state);
}

//first node or child node
private function new_node($text,$state){
	$atts=array();
	$pattern = '/([-a-zA-Z0-9_.]+)\s*=\s*"([^"]*)"(?:\s|$)|([-a-zA-Z0-9_.]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([-a-zA-Z0-9_.]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
	
	$reply=preg_match_all($pattern, $text, $match, PREG_SET_ORDER);
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>The string being parsed: ' . $text;
		echo '<br>. Something is wrong with the above text';
		die();
	}
	
	//extract all the attributes
	
	foreach ($match as $m) {
		if (!empty($m[1]))
			$atts[strtolower($m[1])] = stripcslashes($m[2]);
		elseif (!empty($m[3]))
			$atts[strtolower($m[3])] = stripcslashes($m[4]);
		elseif (!empty($m[5]))
			$atts[strtolower($m[5])] = stripcslashes($m[6]);
		elseif (isset($m[7]) && strlen($m[7]))
			$atts[] = stripcslashes($m[7]);
		elseif (isset($m[8]))
			$atts[] = stripcslashes($m[8]);
	}
	//decide the name of the item
	$item_name=$atts[0]; 
	unset($atts[0]);
	
	//pre actions - the type attribute will now be main
	//if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	$reply=aw2_library::pre_actions('all',$atts,null,null);
	
	//decide whether we want to keep this node
	if($reply==false){
		//we dont want to keep this node
		if ($state=='/]'){
			//it is a self closing node
			return;
		}
			//open node
		$this->remove_named_node($item_name);
		return;
	}
	
	//decide where the array will go
	$last_node=end($this->stack);
	if($last_node)
		$ptr=&$last_node->ptr;
	else
		$ptr=&$this->arr; 


	
	//it is do. It has to be executed
	if($item_name=='do'){
		$pattern = '/^((?s:.*?))(\[\/do\])/';
		$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
		if(!$reply){
			echo '<br>Remaining String.' . $this->str;
			echo '<br>. Something is wrong. A do was not closed';
			die();
		}
		$do=$match[1][0];
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);
		$result=aw2_library::parse_shortcode($do);

		if(array_key_exists('_return',aw2_library::$stack)){
			$result=aw2_library::$stack['_return'];
			unset(aw2_library::$stack['_return']);
		}
	
		if(is_array($result)){
			if(!is_array($ptr))$ptr=array();

			foreach ($result as $key => $value) {
				if(array_key_exists($key,$ptr) &&  is_array($ptr[$key])){
					$ptr[$key]=array_merge($ptr[$key],$value);
				}
				else	
					$ptr[$key]=$value;
			}
		}
		else
			$this->str=$result . $this->str ;

		return;
	}

	//it is do. It has to be executed
	if($item_name=='aw2.do'){
		$pattern = '/^((?s:.*?))(\[\/aw2.do\])/';
		$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
		if(!$reply){
			echo '<br>Remaining String.' . $this->str;
			echo '<br>. Something is wrong. A do was not closed';
			die();
		}
		$do=$match[1][0];
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);
		$result=aw2_library::parse_shortcode($do);
		if(array_key_exists('_return',aw2_library::$stack)){
			$result=aw2_library::$stack['_return'];
			unset(aw2_library::$stack['_return']);
		}
	
		if(is_array($result)){
			if(!is_array($ptr))$ptr=array();

			foreach ($result as $key => $value) {
				if(is_array($ptr[$key])){
					$ptr[$key]=array_merge($ptr[$key],$value);
				}
				else	
					$ptr[$key]=$value;
			}
		}
		else
			$this->str=$result . $this->str ;

		return;
	}
	
	//it is run. Module has to be called
	if($item_name=='aw2.run'){
		$run_content='';

		if ($state==']'){
			//it is a open node closing node
			$pattern = '/^((?s:.*?))(\[\/aw2.run\])/';
			$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
			if(!$reply){
				echo '<br>Remaining String.' . $this->str;
				echo '<br>. Something is wrong. aw2.run was not closed';
				die();
			}
			$run_content=$match[1][0];
			$next_char= strlen($match[2][0]) + $match[2][1];	
			$this->str=substr($this->str,$next_char);
		}
	
		$result=awesome2_run($atts,$run_content,'aw2.run');
		if(array_key_exists('_return',aw2_library::$stack)){
			$result=aw2_library::$stack['_return'];
			unset(aw2_library::$stack['_return']);
		}
	
		if(is_array($result)){
			if(!is_array($ptr))$ptr=array();

			foreach ($result as $key => $value) {
				if(is_array($ptr[$key])){
					$ptr[$key]=array_merge($ptr[$key],$value);
				}
				else	
					$ptr[$key]=$value;
			}
		}
		else
			$this->str=$result . $this->str ;

		return;
	}
	
	if(!is_array($ptr))$ptr=array();
	$raw=false;			
	
	
	if($item_name!='atts'){
		if(!array_key_exists($item_name,$ptr)){
			$ptr[$item_name]=null;
		}	
		$ptr=&$ptr[$item_name];
		
		if(array_key_exists('main',$atts)){
			$type=$atts['main'];
			unset($atts['main']);
			
			if($type=='raw'){
				$raw=true;
			}
			else{
				switch ($type) {
					case 'new':
						$ptr[]=null;
						break;
					default:
						$ptr[$type]=null;
						break;
				}
				end($ptr); 
				$ptr=&$ptr[key($ptr)];
			}
		}	
	}
	
	if(!empty($atts)){
		foreach ($atts as $key => $value) {
				$ptr[$key]=$value;
		}
	}	
	
	if($state==']'){
		$o=new stdClass();
		$o->element_type='OPEN';
		$o->element_name=$item_name;
		$o->ptr=&$ptr;
		$o->raw=$raw;
		
		$this->stack[]=$o;
	}
	
}


private function within_element(){
	$last_node=end($this->stack);
	$name=$last_node->element_name;
	if($last_node->raw){
		$pattern = '/((?s:.*?))(\[\/' . $name . '\])/';
		$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
		if(!$reply){
			echo '<br>Remaining String.' . $this->str;
			echo '<br>Raw element was started but not ended';
			echo '<br>' . $name ;
			die();
		}
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);		
		$last_node->ptr=$match[1][0];
		array_pop($this->stack);
		return;
	}
	
	if($name=='code' && $this->is_api){
		$pattern = '/((?s:.*?))(\[\/' . $name . '\])/';
		$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
		if(!$reply){
			echo '<br>Remaining String.' . $this->str;
			echo '<br>Code element was started but not ended';
			echo '<br>' . $name ;
			die();
		}
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);		
		$last_node->ptr=$match[1][0];
		array_pop($this->stack);
		return;
	}	

	if($name=='on_submit' && $this->is_api){
		$pattern = '/((?s:.*?))(\[\/' . $name . '\])/';
		$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
		if(!$reply){
			echo '<br>Remaining String.' . $this->str;
			echo '<br>on_submit element was started but not ended';
			echo '<br>' . $name ;
			die();
		}
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);		
		$last_node->ptr=$match[1][0];
		array_pop($this->stack);
		return;
	}	
	
	$pattern = '/^(?:\s*\[raw\]((?s:.*?))\[\/raw\]\s*(\[\/' . $name .'\]))|(?:\s*\[([a-zA-Z].*?)(\/]|]))|(?s:(.*?)(\[\/' . $name .'\]))/s';	
	$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>Something went wrong within the content of the element';
		echo '<br>' . $name ;
		
		die();
	}

	//there is a raw node
	if(!empty($match[2][0])){
		$last_node=end($this->stack);
		if(is_array($last_node->ptr))
			;
		else	
			$last_node->ptr=$match[1][0];
		$next_char= strlen($match[2][0]) + $match[2][1];	
		$this->str=substr($this->str,$next_char);
		array_pop($this->stack);
	}
	
	//there is a child node
	if(!empty($match[4][0])){
		$next_char= strlen($match[4][0]) + $match[4][1];	
		$this->str=substr($this->str,$next_char);
		$this->new_node($match[3][0],$match[4][0]);
	}
	
	//open node being closed
	if(!empty($match[6][0])){
		$last_node=end($this->stack);
		if(is_array($last_node->ptr))
			;
		else	
			$last_node->ptr=$match[5][0];
		$next_char= strlen($match[6][0]) + $match[6][1];	
		$this->str=substr($this->str,$next_char);
		array_pop($this->stack);
	}
}


private function remove_named_node($item_name){
	$flag=1;		

	while ($flag>0) {
		$match=$this->find_next_named_node($item_name);
		if(!empty($match[1][0])){
			//found a opening node
			$flag=$flag + 1;
			$next_char= strlen($match[1][0]) + $match[1][1];	
			$this->str=substr($this->str,$next_char);
		}
		else{
			//close was found
			$flag=$flag - 1;
			$next_char= strlen($match[2][0]) + $match[2][1];	
			$this->str=substr($this->str,$next_char);
			if($flag==0)return;//found matching closing node
		}
	}
}

private function find_next_named_node($item_name){
	$pattern = '/((?:\[' . $item_name .'\s.*?])|(?:\[' . $item_name .'])|(?:\[' . $item_name .'\/]))|(\[\/' . $item_name .'])/';
	$reply=preg_match($pattern, $this->str, $match,PREG_OFFSET_CAPTURE);
	if(!$reply){
		echo '<br>Remaining String.' . $this->str;
		echo '<br>Was expecting to find a closing ' . $item_name . ' .Not Found';
		die();
	}
	return $match;
}


}


