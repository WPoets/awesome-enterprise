<?php

namespace aw2\date;

\aw2_library::add_service('date','Date Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('date.get','Returns DateTime',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>null
	), $atts, 'aw2_get' ) );
	
	$return_value=\aw2_library::get($main,$atts,$content);
	$return_value=new \DateTime($return_value);	
	if(!$return_value && $default!==null)
		$return_value==new \DateTime($default);
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('date.create','Create & return DateTime',['namespace'=>__NAMESPACE__]);
function create($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'aw2_get' ) );
	
	
	try {
		$return_value=new \DateTime($main);
	}
	catch (Exception $ex) {
		$return_value = '';
		aw2_library::set_error($ex->getMessage());
	}
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


/*
	[date.modify date='' frequency='' o.set=template.modified_date /]

	- frequency will be of the format : '+1 day','+2 months', '-36 minutes' etc
	- modified_date will be of DateTime format
	
*/
\aw2_library::add_service('date.modify','Modify & return DateTime',['namespace'=>__NAMESPACE__]);
function modify($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'date'=>null,
	'frequency'=>null
	), $atts, 'aw2_get' ) );
	
	if(is_null($date) || is_null($frequency)) return ;

	if(!is_a($date, 'DateTime')) {
	  $date = new \DateTime($date);
	}
	
	$return_value = $date->modify($frequency);

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('date.format','Format & return date string',['namespace'=>__NAMESPACE__]);
function format($atts,$content=null,$shortcode){
    
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null,
    'format'=>null
    ), $atts, 'date.format' ) );
    
    if(is_null($main))
        throw new \Exception('main parameter is required in date.format');
        
    if(is_null($format))
        throw new \Exception('format parameter is required in date.format');
    
    if(!is_a($main, 'DateTime')) {
        try {
            $main = new \DateTime($main);
        } catch(\Exception $e) {
            throw new \Exception('Invalid date string provided: ' . $main);
        }
    }
    
    try {
        $return_value = $main->format($format);
    } catch(\Exception $e) {
        throw new \Exception('Invalid format string provided: ' . $format);
    }
    
    return $return_value;
}


/*
[date.diff date1= date2='' diff_type]

date1 is string or datetime object
date2
diff_type: mins
diff_type: english

*/


\aw2_library::add_service('date.diff','returns the differnce between two dates',['namespace'=>__NAMESPACE__]);
function diff($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'type'=>'mins',
	'date_from'=>null,
	'date_to'=>null
	), $atts, 'aw2_get' ) );
	
	if(is_null($date_from) || is_null($date_to)) return ;
	
	if(!is_a($date_from, 'DateTime')) {
	  $date_from = new \DateTime($date_from);
	}
	
	if(!is_a($date_to, 'DateTime')) {
	  $date_to = new \DateTime($date_to);
	}
	
	//$date_diff= $date_from->diff($date_to, true);
	
	//$date_diff_seconds = $date_diff->days * 24 * 60 * 60 + $date_diff->h * 60 *60 + $date_diff->i * 60 + $date_diff->s;
	
	if($type=='mins'){
		$interval = new \DateInterval('PT1M');
		$periods = new \DatePeriod($date_from, $interval, $date_to);
		$return_value=iterator_count($periods);
	}
	
	if($type=='hours'){
		$interval = new \DateInterval('PT1H');
		$periods = new \DatePeriod($date_from, $interval, $date_to);
		$return_value=iterator_count($periods);
	}
	
	if($type=='days'){
		$interval = new \DateInterval('P1D');
		$periods = new \DatePeriod($date_from, $interval, $date_to);
		$return_value=iterator_count($periods);
	}
	
	if($type=='years'){
		$interval = new \DateInterval('P1Y');
		$periods = new \DatePeriod($date_from, $interval, $date_to);
		$return_value=iterator_count($periods);
	}
	
	if($type=='english'){
		$date_diff= $date_from->diff($date_to, true);
		
		$return_value = '';
		
		  if ( $date_diff->y >= 1 ) $return_value = pluralize( $date_diff->y, 'year' ).' ' ;
		  if ( $date_diff->m >= 1 ) $return_value .= pluralize( $date_diff->m, 'month' ).' ' ;
		  if ( $date_diff->d >= 1 ) $return_value .= pluralize( $date_diff->d, 'day' ).' ' ;
		  if ( $date_diff->h >= 1 ) $return_value .= pluralize( $date_diff->h, 'hour' ) .' ';
		  if ( $date_diff->i >= 1 ) $return_value .= pluralize( $date_diff->i, 'minute' ).' ' ;
		  if ( $date_diff->s >= 1 ) $return_value .= pluralize( $date_diff->s, 'second' ).' ' ;
		
	}

	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('date.aw2_period','Given a period, returns start_date and end_date',['namespace'=>__NAMESPACE__]);
function aw2_period($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'period'=>null
	), $atts, 'aw2_get' ) );
	
	if(is_null($period)) return ;
	
	if( strpos( $period, ":" ) === false ) {
		return;
	}
	
	$str_arr=explode(":",$period);
	switch ($str_arr[0]) {
		
		case "day":
					$period_str="-".$str_arr[1]." days";
					
					$period_start_str=$period_str;
					$period_end_str=$period_str;
						
					if($str_arr[1]=="today" || $str_arr[1]=="yesterday"){
						$period_start_str=$str_arr[1];
						$period_end_str=$str_arr[1];
					}				
					break;
		case "days":	
					$period_start_str="-".$str_arr[1]." days";
					$period_end_str="today";
					break;		
		case "months":
					$period_start_str="first day of -".$str_arr[1]." months";
					$period_end_str="today";
					break;
		case "month":										
					$period_start_str="first day of -".$str_arr[1]." month";
					$period_end_str="last day of -".$str_arr[1]." month";
					
					if($str_arr[1]=="last_month"){
						$period_start_str="first day of last month";
						$period_end_str="last day of last month";
					}	
					if($str_arr[1]=="this_month" ){
						$period_start_str="first day of this month";
						$period_end_str="today";
					}	
					break;
		case "year":
					if($str_arr[1]=="last_year"){
						$period_start_str="last year January 1st";
						$period_end_str="last year December 31st";
					}	
					if($str_arr[1]=="this_year" ){
						$period_start_str="this year January 1st";
						$period_end_str="today";
					}	
					break;			
		default:
					$period_start_str= "today";
					$period_end_str= "today";
	}
	
	$start_time = new \DateTime($period_start_str);
	$end_time = new \DateTime($period_end_str);
	
	$return_value=array();
	$return_value['start_date'] = $start_time->format('YmdHis');
	$return_value['end_date'] = $end_time->format('YmdHis');

	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

function pluralize( $count, $text ){ 
    return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
}
