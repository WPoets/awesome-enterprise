<?php

namespace aw2\c;

\aw2_library::add_service('c.ignore','Ignore this condition check. Use c.ignore',['namespace'=>__NAMESPACE__]);
function ignore($atts,$content=null,$shortcode){
	return false;
}

\aw2_library::add_service('c.odd','Check if the param is an odd number. Use c.odd',['namespace'=>__NAMESPACE__]);
function odd($atts,$content=null,$shortcode){
	
	$odd = \aw2_library::resolve_chain($atts['odd']);
	if((int)$odd % 2 === 0)
		return false;
}

\aw2_library::add_service('c.even','Check if the param is an even number. Use c.even',['namespace'=>__NAMESPACE__]);
function even($atts,$content=null,$shortcode){
	
	$even = \aw2_library::resolve_chain($atts['even']);
	if((int)$even % 2 !== 0)
		return false;
}


\aw2_library::add_service('c.eq','Check if two params are equal. Use c.cond and c.eq',['namespace'=>__NAMESPACE__]);
function eq($atts,$content=null,$shortcode){
	$cond = \aw2_library::resolve_chain($atts['cond']);
	$eq = \aw2_library::resolve_chain($atts['eq']);
	
	if($cond !== $eq)
		return false;
}

\aw2_library::add_service('c.neq','Check if two params are not equal. Use c.cond and c.neq',['namespace'=>__NAMESPACE__]);
function neq($atts,$content=null,$shortcode){
	$cond = \aw2_library::resolve_chain($atts['cond']);
	$neq = \aw2_library::resolve_chain($atts['neq']);
	
	if($cond === $neq)
		return false;
}

\aw2_library::add_service('c.gt','Check if one param is greater than the other. Use c.cond and c.gt',['namespace'=>__NAMESPACE__]);
function gt($atts,$content=null,$shortcode){
	$cond = \aw2_library::resolve_chain($atts['cond']);
	$gt = \aw2_library::resolve_chain($atts['gt']);

	\aw2_error_log::log_datatype_mismatch(['lhs'=>$cond,'rhs'=>$gt,'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'c.gt','php7result'=>($cond > $gt)]);

	
	// default return value
	$returnValue = false;
	
	// check condition
	if($cond > $gt)
		$returnValue = true;
	
	// return result
	return $returnValue;
	
}

\aw2_library::add_service('c.lt','Check if one param is less than the other. Use c.cond and c.lt',['namespace'=>__NAMESPACE__]);
function lt($atts,$content=null,$shortcode){
	$cond = \aw2_library::resolve_chain($atts['cond']);
	$lt = \aw2_library::resolve_chain($atts['lt']);

	\aw2_error_log::log_datatype_mismatch(['lhs'=>$cond,'rhs'=>$lt,'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'c.lt','php7result'=>($cond < $lt)]);
	
	// default return value
	$returnValue = false;
	
	// check condition
	if($cond < $lt)
		$returnValue = true;
	
	// return result
	return $returnValue;
}

\aw2_library::add_service('c.gte','Check if one param is greater than or equal to the other. Use c.cond and c.gte',['namespace'=>__NAMESPACE__]);
function gte($atts,$content=null,$shortcode){
	$cond = \aw2_library::resolve_chain($atts['cond']);
	$gte = \aw2_library::resolve_chain($atts['gte']);

	\aw2_error_log::log_datatype_mismatch(['lhs'=>$cond,'rhs'=>$gte,'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'c.gte','php7result'=>($cond >= $gte)]);
	
	// default return value
	$returnValue = false;
	
	// check condition
	if($cond >= $gte)
		$returnValue = true;
	
	// return result
	return $returnValue;
	
}

\aw2_library::add_service('c.lte','Check if one param is less than or equal to the other. Use c.cond and c.lte',['namespace'=>__NAMESPACE__]);
function lte($atts,$content=null,$shortcode){
	$cond = \aw2_library::resolve_chain($atts['cond']);
	$lte = \aw2_library::resolve_chain($atts['lte']);

	\aw2_error_log::log_datatype_mismatch(['lhs'=>$cond,'rhs'=>$lte,'lhs_dt'=>'number','rhs_dt'=>'number','condition'=>'c.lte','php7result'=>($cond <= $lte)]);
	
	// default return value
	$returnValue = false;
	
	// check condition
	if($cond <= $lte)
		$returnValue = true;
	
	// return result
	return $returnValue;

}

\aw2_library::add_service('c.true','Check if the param is true. Use c.true',['func'=>'_true','namespace'=>__NAMESPACE__]);
function _true($atts,$content=null,$shortcode){
	$true = \aw2_library::resolve_chain($atts['true']);
	if((bool)$true === false)
		return false;
}

\aw2_library::add_service('c.false','Check if the param is false. Use c.false',['func'=>'_false','namespace'=>__NAMESPACE__]);
function _false($atts,$content=null,$shortcode){
	$false = \aw2_library::resolve_chain($atts['false']);
	if((bool)$false === true)
		return false;
}

\aw2_library::add_service('c.yes','Check if the param is yes. Use c.yes',['namespace'=>__NAMESPACE__]);
function yes($atts,$content=null,$shortcode){
	$yes = \aw2_library::resolve_chain($atts['yes']);
	if((string)$yes !== 'yes')
		return false;
}

\aw2_library::add_service('c.no','Check if the param is no. Use c.no',['namespace'=>__NAMESPACE__]);
function no($atts,$content=null,$shortcode){
	$no = \aw2_library::resolve_chain($atts['no']);
	if((string)$no !== 'no')
		return false;
}

\aw2_library::add_service('c.arr','Check if the param is an array. Use c.arr',['namespace'=>__NAMESPACE__]);
function arr($atts,$content=null,$shortcode){
	$arr = \aw2_library::resolve_chain($atts['arr']);
	if(is_array($arr) === false)
		return false;
}

\aw2_library::add_service('c.not_arr','Check if the param is not an array. Use c.not_arr',['namespace'=>__NAMESPACE__]);
function not_arr($atts,$content=null,$shortcode){
	$not_arr = \aw2_library::resolve_chain($atts['not_arr']);
	if(is_array($not_arr) !== false)
		return false;
}

\aw2_library::add_service('c.str','Check if the param is a string. Use c.str',['namespace'=>__NAMESPACE__]);
function str($atts,$content=null,$shortcode){
	$str = \aw2_library::resolve_chain($atts['str']);
	if(is_string($str) === false)
		return false;
}

\aw2_library::add_service('c.not_str','Check if the param is not a string. Use c.not_str',['namespace'=>__NAMESPACE__]);
function not_str($atts,$content=null,$shortcode){
	$not_str = \aw2_library::resolve_chain($atts['not_str']);
	if(is_string($not_str) !== false)
		return false;
}

\aw2_library::add_service('c.bool','Check if the param is a boolean. Use c.bool',['func'=>'_bool','namespace'=>__NAMESPACE__]);
function _bool($atts,$content=null,$shortcode){
	$bool = \aw2_library::resolve_chain($atts['bool']);
	if(is_bool($bool) === false)
		return false;
}

\aw2_library::add_service('c.not_bool','Check if the param is not a boolean. Use c.not_bool',['namespace'=>__NAMESPACE__]);
function not_bool($atts,$content=null,$shortcode){
	$not_bool = \aw2_library::resolve_chain($atts['not_bool']);
	if(is_bool($not_bool) !== false)
		return false;
}

\aw2_library::add_service('c.num','Check if the param is numeric. Use c.num',['func'=>'num','namespace'=>__NAMESPACE__]);
function num($atts,$content=null,$shortcode){
	$num = \aw2_library::resolve_chain($atts['num']);
	if(is_numeric($num) === false)
		return false;
}

\aw2_library::add_service('c.not_num','Check if the param is not numeric. Use c.not_num',['namespace'=>__NAMESPACE__]);
function not_num($atts,$content=null,$shortcode){
	$not_num = \aw2_library::resolve_chain($atts['not_num']);
	if(is_numeric($not_num) !== false)
		return false;
}


\aw2_library::add_service('c.int','Check if the param is an integer. Use c.int',['func' => '_int','namespace'=>__NAMESPACE__]);
function _int($atts,$content=null,$shortcode){
	$int = \aw2_library::resolve_chain($atts['int']);
	if(is_int($int) === false)
		return false;
}

\aw2_library::add_service('c.not_int','Check if the param is an integer. Use c.not_int',['namespace'=>__NAMESPACE__]);
function not_int($atts,$content=null,$shortcode){
	$not_int = \aw2_library::resolve_chain($atts['not_int']);
	if(is_int($not_int) !== false)
		return false;
}

\aw2_library::add_service('c.date_obj','Check if the param is a DateTime object. Use c.date_obj',['namespace'=>__NAMESPACE__]);
function date_obj($atts,$content=null,$shortcode){
	if(is_null($atts['date_obj']))return false;
	
	if(get_class($atts['date_obj']) !== 'DateTime')
		return false;
}

\aw2_library::add_service('c.not_date_obj','Check if the param is not a DateTime object. Use c.not_date_obj',['namespace'=>__NAMESPACE__]);
function not_date_obj($atts,$content=null,$shortcode){
		if(!is_null($atts['not_date_obj']) && get_class($atts['not_date_obj']) === 'DateTime')
		return false;
}

\aw2_library::add_service('c.obj','Check if the param is an object. Use c.obj',['namespace'=>__NAMESPACE__]);
function obj($atts,$content=null,$shortcode){
	if(is_object($atts['obj']) === false)
		return false;
}

\aw2_library::add_service('c.not_obj','Check if the param is not an object. Use c.not_obj',['namespace'=>__NAMESPACE__]);
function not_obj($atts,$content=null,$shortcode){
	if(is_object($atts['not_obj']) !== false)
		return false;
}

\aw2_library::add_service('c.zero','Check if the param is Zero. Use c.zero',['namespace'=>__NAMESPACE__]);
function zero($atts,$content=null,$shortcode){
	$zero = \aw2_library::resolve_chain($atts['zero']);
	if((float)$zero !== (float)0)
		return false;
}

\aw2_library::add_service('c.positive','Check if the param is a positive number. Use c.positive',['namespace'=>__NAMESPACE__]);
function positive($atts,$content=null,$shortcode){
	$positive = \aw2_library::resolve_chain($atts['positive']);
	\aw2_error_log::log_datatype_mismatch(['lhs'=>$positive,'lhs_dt'=>'number','condition'=>'c.positive','php7result'=>((float)$positive <= (float)0)]);
	if((float)$positive <= (float)0)
		return false;
}

\aw2_library::add_service('c.negative','Check if the param is a negative number. Use c.negative',['namespace'=>__NAMESPACE__]);
function negative($atts,$content=null,$shortcode){
	$negative = \aw2_library::resolve_chain($atts['negative']);
	\aw2_error_log::log_datatype_mismatch(['lhs'=>$negative,'lhs_dt'=>'number','condition'=>'c.negative','php7result'=>((float)$negative >= (float)0)]);
	if((float)$negative >= (float)0)
		return false;
}



\aw2_library::add_service('c.ws','Check if the param has any whitespace. Use c.ws',['namespace'=>__NAMESPACE__]);
function ws($atts,$content=null,$shortcode){
	$ws = (string)\aw2_library::resolve_chain($atts['ws']);
	if($ws !== '' && !(ctype_space($ws)))
		return false;
}

\aw2_library::add_service('c.not_ws','Check if the param has no whitespace. Use c.not_ws',['namespace'=>__NAMESPACE__]);
function not_ws($atts,$content=null,$shortcode){
	$not_ws = (string)\aw2_library::resolve_chain($atts['not_ws']);
	if(ctype_space($not_ws) || $not_ws === '')
		return false;
}

\aw2_library::add_service('c.empty','Check if the param is empty. Use c.empty',['func' => '_empty','namespace'=>__NAMESPACE__]);
function _empty($atts,$content=null,$shortcode){
	$empty = \aw2_library::resolve_chain($atts['empty']);
	if(empty($empty) === false)
		return false;
}

\aw2_library::add_service('c.not_empty','Check if the param is not empty. Use c.not_empty',['namespace'=>__NAMESPACE__]);
function not_empty($atts,$content=null,$shortcode){
	$not_empty = \aw2_library::resolve_chain($atts['not_empty']);
	if(empty($not_empty) !== false)
		return false;
}

/* used to check value set */
\aw2_library::add_service('c.not_blank','Check if the param is not blank space allowed. Use c.not_blank',['namespace'=>__NAMESPACE__]);
function not_blank($atts,$content=null,$shortcode){
	$not_blank = \aw2_library::resolve_chain($atts['not_blank']);
	//$not_blank =trim($not_blank);
	if(empty($not_blank) && !is_numeric($not_blank))return false;
}

/* used to check no value*/
\aw2_library::add_service('c.is_blank','Check if the param is blank space allowed. Use c.is_blank',['namespace'=>__NAMESPACE__]);
function is_blank($atts,$content=null,$shortcode){
	$is_blank = \aw2_library::resolve_chain($atts['is_blank']);
	//$is_blank =trim($is_blank);
	if(empty($is_blank) && !is_numeric($is_blank))return;
	
	return false;
}
\aw2_library::add_service('c.null','Check if the param is null. Use c.null',['func'=>'_null','namespace'=>__NAMESPACE__]);
function _null($atts,$content=null,$shortcode){
	$null = \aw2_library::resolve_chain($atts['null']);
	if(is_null($null) === false)
		return false;
}

\aw2_library::add_service('c.not_null','Check if the param is not null. Use c.not_null',['namespace'=>__NAMESPACE__]);
function not_null($atts,$content=null,$shortcode){
	$not_null = \aw2_library::resolve_chain($atts['not_null']);
	if(is_null($not_null) !== false)
		return false;
}

\aw2_library::add_service('c.request_exists','Check if the request exists. Use c.request_exists',['namespace'=>__NAMESPACE__]);
function request_exists($atts,$content=null,$shortcode){
	if(\aw2_library::get_request($atts['request_exists']) === null)
		return false;
}

\aw2_library::add_service('c.request_not_exists','Check if the request does not exist. Use c.request_not_exists',['namespace'=>__NAMESPACE__]);
function request_not_exists($atts,$content=null,$shortcode){
	if(\aw2_library::get_request($atts['request_not_exists']) !== null)
		return false;
}

\aw2_library::add_service('c.contains','Check if the haystack contains the param. Use c.contains and c.haystack',['namespace'=>__NAMESPACE__]);
function contains($atts,$content=null,$shortcode){
	$contains = \aw2_library::resolve_chain($atts['contains']);
	$haystack = \aw2_library::resolve_chain($atts['haystack']);
	
	if(!is_array($haystack))
		$arr= explode( ',' ,$haystack );
	else
		$arr=$haystack;
	
	if(!in_array($contains,$arr))
		return false;
}

\aw2_library::add_service('c.not_contains','Check if the haystack does not contain the param. Use c.not_contains and c.haystack',['namespace'=>__NAMESPACE__]);
function not_contains($atts,$content=null,$shortcode){
	$not_contains = \aw2_library::resolve_chain($atts['not_contains']);
	$haystack = \aw2_library::resolve_chain($atts['haystack']);
	
	if(!is_array($haystack))
		$arr= explode( ',' ,$haystack );
	else
		$arr=$haystack;
	
	if(in_array($not_contains,$arr))
		return false;
}

\aw2_library::add_service('c.aw2_error','Check if c has an error object',['namespace'=>__NAMESPACE__]);
function aw2_error($atts,$content=null,$shortcode){
	$error = \aw2_library::resolve_chain($atts['aw2_error']);
	if(is_object($error) && get_class($error)==='aw2_error'){
	}	
	else
		return false;
}


\aw2_library::add_service('c.exists','Check if the param exists in the environment. Use c.exists',['namespace'=>__NAMESPACE__]);
function exists($atts,$content=null,$shortcode){
	if(\aw2_library::env_key_exists($atts['exists']) === '_error')
		return false;
}

\aw2_library::add_service('c.not_exists','Check if the param does not exist in the environment. Use c.not_exists',['namespace'=>__NAMESPACE__]);
function not_exists($atts,$content=null,$shortcode){
	if(\aw2_library::env_key_exists($atts['not_exists']) !== '_error')
		return false;
}

\aw2_library::add_service('c.user_can','Check if the current user has the role assigned. Use c.user_can',['namespace'=>__NAMESPACE__]);
function user_can($atts,$content=null,$shortcode){
	if(current_user_can($atts['user_can']) === false)
		return false;
}

\aw2_library::add_service('c.user_cannot','Check if the current user does not have the role assigned. Use c.user_cannot',['namespace'=>__NAMESPACE__]);
function user_cannot($atts,$content=null,$shortcode){
	if(current_user_can($atts['user_cannot']))
		return false;
}

\aw2_library::add_service('c.logged_in','Check if the user is logged in the system. Use c.logged_in',['namespace'=>__NAMESPACE__]);
function logged_in($atts,$content=null,$shortcode){
	if(!is_user_logged_in())
		return false;
}

\aw2_library::add_service('c.not_logged_in','Check if the user is not logged in the system. Use c.not_logged_in',['namespace'=>__NAMESPACE__]);
function not_logged_in($atts,$content=null,$shortcode){
	if(is_user_logged_in())
		return false;
}

\aw2_library::add_service('c.device','Check if the device used is one of the params. Use c.device.',['namespace'=>__NAMESPACE__]);
function device($atts,$content=null,$shortcode){
	$device = \aw2_library::resolve_chain($atts['device']);
	
	$detect = new \Mobile_Detect;
	$device_status=false;
	$arr= explode( ',' ,$device );
	if($detect->isMobile() && !$detect->isTablet() && in_array('mobile',$arr) )
		$device_status=true;

	if($detect->isTablet() && in_array('tablet',$arr) )
		$device_status=true;

	if(!$detect->isMobile() && !$detect->isTablet() && in_array('desktop',$arr) )
		$device_status=true;

if($device_status===false)
	return false;	
}
