<?php


/*
static function checkcondition(&$atts){
	if(!$atts)return true;


		if(array_key_exists('ignore',$atts)){
			return false;
		}

		if(array_key_exists('odd',$atts)){
			if((int)$atts['odd'] % 2 == 0)
		return false;
	else
		unset($atts['odd']);  
		}
		
		if(array_key_exists('even',$atts)){
			if((int)$atts['even'] % 2 != 0)
		return false;
	else
		unset($atts['even']);  
		}

		if(array_key_exists('true',$atts)){
			if($atts['true']!=true)
		return false;
	else
		unset($atts['true']); 
	}

	if(array_key_exists('false',$atts)){
			if($atts['false']==true)
		return false;
	else
		unset($atts['false']); 
	}

	if(array_key_exists('yes',$atts)){
		if($atts['yes']!=='yes')
			return false;
		else
			unset($atts['yes']); 
	}

	if(array_key_exists('no',$atts)){
		if($atts['no']!=='no')
			return false;
		else
			unset($atts['no']); 
	}
	
	if(array_key_exists('arr',$atts)){
		if(!is_array($atts['arr']))
			return false;
		else
			unset($atts['arr']); 
	}
	
	if(array_key_exists('not_arr',$atts)){
		if(is_array($atts['not_arr']))
			return false;
		else
			unset($atts['not_arr']); 
	}
	
	if(array_key_exists('str',$atts)){
		if(!is_string($atts['str']))
			return false;
		else
			unset($atts['str']); 
	}
	
	if(array_key_exists('not_str',$atts)){
		if(is_string($atts['not_str']))
			return false;
		else
			unset($atts['not_str']); 
	}
	
	if(array_key_exists('bool',$atts)){
		if(!is_bool($atts['bool']))
			return false;
		else
			unset($atts['bool']); 
	}
	
	if(array_key_exists('not_bool',$atts)){
		if(is_bool($atts['not_bool']))
			return false;
		else
			unset($atts['not_bool']); 
	}
	
	if(array_key_exists('num',$atts)){
		if(!is_numeric($atts['num']))
			return false;
		else
			unset($atts['num']); 
	}
	
	if(array_key_exists('greater_than_zero',$atts)){
		if(!is_numeric($atts['greater_than_zero']) || $atts['greater_than_zero']<=0 )
			return false;
		else
			unset($atts['greater_than_zero']); 
	}

	
	if(array_key_exists('is_num',$atts)){
		if(!is_numeric($atts['is_num']))
			return false;
		else
			unset($atts['is_num']); 
	}
	
	if(array_key_exists('not_num',$atts)){
		if(is_numeric($atts['not_num']))
			return false;
		else
			unset($atts['not_num']); 
	}

	if(array_key_exists('int',$atts)){
		if(!is_int($atts['int']))
			return false;
		else
			unset($atts['int']); 
	}
	
	if(array_key_exists('not_int',$atts)){
		if(is_int($atts['not_int']))
			return false;
		else
			unset($atts['not_int']); 
	}
	
	if(array_key_exists('date_obj',$atts)){
		if(!get_class($atts['date_obj'])=='DateTime')
			return false;
		else
			unset($atts['date_obj']); 
	}
	
	if(array_key_exists('not_date_obj',$atts)){
		if(get_class($atts['date_obj']))
			return false;
		else
			unset($atts['not_date_obj']); 
	}

	if(array_key_exists('obj',$atts)){
		if(!is_object($atts['obj']))
			return false;
		else
			unset($atts['obj']); 
	}
	
	if(array_key_exists('not_obj',$atts)){
		if(is_object($atts['not_obj']))
			return false;
		else
			unset($atts['not_obj']); 
	}
	
	
		if(array_key_exists('empty',$atts)){
			if(!empty($atts['empty']))
		return false;
	else
		unset($atts['empty']); 
	}

	if(array_key_exists('not_empty',$atts)){
		if(empty($atts['not_empty']))
			return false;
		else
			unset($atts['not_empty']); 
	}

	if(array_key_exists('whitespace',$atts)){
		if($atts['whitespace'] === '' || !(ctype_space($atts['whitespace'])))return false;
	else
		unset($atts['whitespace']); 
	}

	if(array_key_exists('not_whitespace',$atts)){
		if(ctype_space($atts['not_whitespace']) || $atts['not_whitespace'] === '')return false;
	else
		unset($atts['not_whitespace']); 
	}

	
		if(array_key_exists('user_can',$atts)){
	if(current_user_can($atts['user_can'])===false)
		return false;
	else
		unset($atts['user_can']); 
		}
	
		if(array_key_exists('user_cannot',$atts)){
	if(current_user_can($atts['user_cannot']))
		return false;
	else
		unset($atts['user_cannot']); 
		}
		
		if(array_key_exists('logged_in',$atts)){
	if(!is_user_logged_in())
		return false;
	else
		unset($atts['logged_in']); 
		}

		if(array_key_exists('not_logged_in',$atts)){
	if(is_user_logged_in())
		return false;
	else
		unset($atts['not_logged_in']); 
		}

		if(array_key_exists('request_exists',$atts)){
	if(self::get_request($atts['request_exists'])==null)
		return false;
	else
		unset($atts['request_exists']); 		  
		}	  
	
		if(array_key_exists('request_not_exists',$atts)){
	if(self::get_request($atts['request_not_exists'])!=null)
		return false;
	else
		unset($atts['request_not_exists']); 		  
		}

		if(array_key_exists('ajax',$atts)){
	if(self::get_request('ajax')!='true')
		return false;
	else
		unset($atts['ajax']); 
		}

		if(array_key_exists('not_ajax',$atts)){
	if(self::get_request('ajax')=='true')
		return false;
	else
		unset($atts['not_ajax']); 
		}
	
		if(array_key_exists('request_part',$atts)){
	if((self::get_request('part') ==$atts['request_part']) || (self::get_request('part')==null && $atts['request_part']=='default') )
		unset($atts['request_part']); 
	else
		return false;	
		}	  

		if(array_key_exists('list',$atts) && array_key_exists('contains',$atts) ){
			if(!is_array($atts['list']))
				$arr= explode( ',' ,$atts['list'] );
			else
				$arr=$atts['list']; 
			if(!in_array($atts['contains'],$arr))
		return false;
			else 
	{unset($atts['list']);unset($atts['contains']); }		
		}

		if(array_key_exists('list',$atts) && array_key_exists('not_contains',$atts) ){
			if(!is_array($atts['list']))
				$arr= explode( ',' ,$atts['list'] );
			else
				$arr=$atts['list']; 
			if(in_array($atts['not_contains'],$arr))
		return false;
			else 
	{unset($atts['list']);unset($atts['not_contains']); }		
		}

		if(array_key_exists('cond',$atts) && array_key_exists('not_equal',$atts) ){
			if($atts['cond']!=$atts['not_equal'])
		{unset($atts['cond']);unset($atts['not_equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('equal',$atts) ){
			if($atts['cond']==$atts['equal'])
		{unset($atts['cond']);unset($atts['equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('greater_than',$atts) ){
			if($atts['cond']>$atts['greater_than'])
		{unset($atts['cond']);unset($atts['greater_than']); }		
			else 
		return false;
		}
	
		if(array_key_exists('cond',$atts) && array_key_exists('less_than',$atts) ){
			if($atts['cond']<$atts['less_than'])
		{unset($atts['cond']);unset($atts['less_than']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('greater_equal',$atts) ){
			if($atts['cond']>=$atts['greater_equal'])
		{unset($atts['cond']);unset($atts['greater_equal']); }		
			else 
		return false;
		}

		if(array_key_exists('cond',$atts) && array_key_exists('less_equal',$atts) ){
			if($atts['cond']<=$atts['less_equal'])
		{unset($atts['cond']);unset($atts['less_equal']); }		
			else 
		return false;
		}
	
	
		if(array_key_exists('require_once',$atts)){
	$stack=&self::get_array_ref('require_once_stack');
	if(array_key_exists($atts['require_once'],$stack))
		return false;
	else
	{
		self::set('require_once_stack.' . $atts['require_once'] ,true);
		unset($atts['require_once']);	
	}
		}


		if(array_key_exists('device',$atts)){
	$detect = new Mobile_Detect;
	$device_status=false;
			$arr= explode( ',' ,$atts['device'] );
			if($detect->isMobile() && !$detect->isTablet() && in_array('mobile',$arr) )
		$device_status=true;
	
			if($detect->isTablet() && in_array('tablet',$arr) )
		$device_status=true;
	
			if(!$detect->isMobile() && !$detect->isTablet() && in_array('desktop',$arr) )
		$device_status=true;

	if($device_status==false)
		return false;		
			else 
		unset($atts['device']);	
		}
	
	if(array_key_exists('in_array',$atts) && array_key_exists('contains',$atts) ){
			
			if(!self::in_array_r($atts['contains'],self::get($atts['in_array'])))
		return false;
			else 
	{unset($atts['in_array']);unset($atts['contains']); }		
		}
	
	if(array_key_exists('in_array',$atts) && array_key_exists('not_contains',$atts) ){
			if(self::in_array_r($atts['not_contains'],self::get($atts['in_array'])))
		return false;
			else 
	{unset($atts['in_array']);unset($atts['not_contains']); }		
		}
	
	return true;
}
*/
namespace aw2\c;


\aw2_library::add_service('c.equal','Check if two params are equal. Use c.cond and c.equal',['namespace'=>__NAMESPACE__]);
function equal($atts,$content=null,$shortcode){
	if($atts['cond']!==$atts['equal'])
		return false;
}

\aw2_library::add_service('c.not_equal','Check if two params are not equal. Use c.cond and c.not_equal',['namespace'=>__NAMESPACE__]);
function not_equal($atts,$content=null,$shortcode){
	if($atts['cond']===$atts['not_equal'])
		return false;
}

\aw2_library::add_service('c.greater_than','Check if one param is greater than the other. Use c.cond and c.greater_than',['namespace'=>__NAMESPACE__]);
function greater_than($atts,$content=null,$shortcode){
	if($atts['cond']<$atts['greater_than'])
		return false;
}

\aw2_library::add_service('c.less_than','Check if one param is less than the other. Use c.cond and c.less_than',['namespace'=>__NAMESPACE__]);
function less_than($atts,$content=null,$shortcode){
	if($atts['cond']>$atts['less_than'])
		return false;
}

\aw2_library::add_service('c.greater_equal','Check if one param is greater than or equal to the other. Use c.cond and c.greater_equal',['namespace'=>__NAMESPACE__]);
function greater_equal($atts,$content=null,$shortcode){
	if($atts['cond']<$atts['greater_equal'])
		return false;
}

\aw2_library::add_service('c.less_equal','Check if one param is less than or equal to the other. Use c.cond and c.less_equal',['namespace'=>__NAMESPACE__]);
function less_equal($atts,$content=null,$shortcode){
	if($atts['cond']>$atts['less_equal'])
		return false;
}

\aw2_library::add_service('c.odd','Check if the param is an odd number. Use c.odd',['namespace'=>__NAMESPACE__]);
function odd($atts,$content=null,$shortcode){
	if((int)$atts['odd'] % 2 === 0)
		return false;
}

\aw2_library::add_service('c.even','Check if the param is an even number. Use c.even',['namespace'=>__NAMESPACE__]);
function even($atts,$content=null,$shortcode){
	if((int)$atts['even'] % 2 !== 0)
		return false;
}

\aw2_library::add_service('c.true','Check if the param is true. Use c.true',['func'=>'_true','namespace'=>__NAMESPACE__]);
function _true($atts,$content=null,$shortcode){
	if($atts['true']!=true)
		return false;
}

\aw2_library::add_service('c.false','Check if the param is false. Use c.false',['func'=>'_false','namespace'=>__NAMESPACE__]);
function _false($atts,$content=null,$shortcode){
	if($atts['false']==true)
		return false;
}

\aw2_library::add_service('c.yes','Check if the param is yes. Use c.yes',['namespace'=>__NAMESPACE__]);
function yes($atts,$content=null,$shortcode){
	if($atts['yes']!=='yes')
		return false;
}

\aw2_library::add_service('c.no','Check if the param is no. Use c.no',['namespace'=>__NAMESPACE__]);
function no($atts,$content=null,$shortcode){
	if($atts['no']!=='no')
		return false;
}

\aw2_library::add_service('c.arr','Check if the param is an array. Use c.arr',['namespace'=>__NAMESPACE__]);
function arr($atts,$content=null,$shortcode){
	if(!is_array($atts['arr']))
		return false;
}

\aw2_library::add_service('c.not_arr','Check if the param is not an array. Use c.not_arr',['namespace'=>__NAMESPACE__]);
function not_arr($atts,$content=null,$shortcode){
	if(is_array($atts['not_arr']))
		return false;
}

\aw2_library::add_service('c.str','Check if the param is a string. Use c.str',['namespace'=>__NAMESPACE__]);
function str($atts,$content=null,$shortcode){
	if(!is_string($atts['str']))
		return false;
}

\aw2_library::add_service('c.not_str','Check if the param is not a string. Use c.not_str',['namespace'=>__NAMESPACE__]);
function not_str($atts,$content=null,$shortcode){
	if(is_string($atts['not_str']))
		return false;
}

\aw2_library::add_service('c.bool','Check if the param is a boolean. Use c.bool',['func'=>'_bool','namespace'=>__NAMESPACE__]);
function _bool($atts,$content=null,$shortcode){
	if(!is_bool($atts['bool']))
		return false;
}

\aw2_library::add_service('c.not_bool','Check if the param is not a boolean. Use c.not_bool',['namespace'=>__NAMESPACE__]);
function not_bool($atts,$content=null,$shortcode){
	if(is_bool($atts['not_bool']))
		return false;
}

\aw2_library::add_service('c.num','Check if the param is numeric. Use c.num',['func'=>'num','namespace'=>__NAMESPACE__]);
function num($atts,$content=null,$shortcode){
	if(!is_numeric($atts['num']))
		return false;
}

\aw2_library::add_service('c.not_num','Check if the param is not numeric. Use c.not_num',['namespace'=>__NAMESPACE__]);
function not_num($atts,$content=null,$shortcode){
	if(is_numeric($atts['not_num']))
		return false;
}

