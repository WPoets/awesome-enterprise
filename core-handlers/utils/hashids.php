<?php
namespace aw2\hashids;


\aw2_library::add_service('hashids.set','Set the hashids in the Options table',['namespace'=>__NAMESPACE__]);
function set($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'value'=>null,
	'prefix'=>'aw2_token_'
	), $atts) );
	
	$token=uniqid ($prefix,true);

	$sql="INSERT INTO wp_options ( option_name, option_value, autoload) VALUES ('$token', '$value', 'no');SELECT LAST_INSERT_ID() as ID;";
	$r=\aw2\multi\select(array(),$sql,null);
	$id=$r[0]['ID'];

	//$plugin_path=plugin_dir_path( __DIR__ );
	//require_once( $plugin_path . '/libraries/Hashids/HashGenerator.php' );
	//require_once( $plugin_path . '/libraries/Hashids/Hashids.php' );

	$hashids = new \Hashids\Hashids('this is Bingo',10,"abcdefghijklmnopqrstuvwxyz1234567890");
	$hash = $hashids->encode($id);
	
	$sql="update wp_options set option_name='$prefix" . "$hash' where option_id=$id";
	$r=\aw2\multi\update(array(),$sql,null);
  	
	$return_value=$hash;	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('hashids.get','Get the hashids in the Options table',['namespace'=>__NAMESPACE__]);
function get($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'hash'=>null,
	'prefix'=>'aw2_token_'
	), $atts) );
	
	$return_value=array('status'=>'error');
	$flag=true;
	
	if(!$hash){
		$return_value['message']='No hash provided';
		$flag=false;
	}
	

	
	if($flag){
		//$plugin_path=plugin_dir_path( __DIR__ );
		//require_once( $plugin_path . '/libraries/Hashids/HashGenerator.php' );
		//require_once( $plugin_path . '/libraries/Hashids/Hashids.php' );

		$hashids = new \Hashids\Hashids('this is Bingo',10,"abcdefghijklmnopqrstuvwxyz1234567890");
		$reply = $hashids->decode($hash);
		if (!empty($reply)) {
			$id=$reply[0];		
			$sql="select option_value from wp_options where option_name='$prefix" . "$hash' and option_id=$id";
			$r=\aw2\multi\select(array(),$sql,null);
			
			if(count($r)===1){
				$return_value['value']=$r[0]['option_value'];
				$return_value['status']='success';
			}
			else{
				$return_value['message']='Invalid Hash';
			}
		}else{
				$return_value['message']='Invalid Hash';
		}
		
	}
		
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}
