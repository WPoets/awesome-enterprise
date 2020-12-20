<?php
/*
SBI Payment Gateway Helper 

*/
namespace aw2;

class SBI_Pay {
	//static $iv ='1234567890123456';
	const METHOD = 'AES-128-CBC';
	//static $prod_url = 'https://www.onlinesbi.com/merchant/merchantprelogin.htm';
	static $prod_url = 'https://merchant.onlinesbi.com/merchant/merchantprelogin.htm';
	static $uat_url = 'https://uatmerchant.onlinesbi.com/merchantgst/merchantprelogin.htm';
	//static $prod_verify_url = 'https://www.onlinesbi.com/thirdparties/doubleverification.htm';
	static $prod_verify_url = 'https://merchant.onlinesbi.com/thirdparties/doubleverification.htm';
	static $uat_verify_url = 'https://uatmerchant.onlinesbi.com/thirdparties/doubleverification.htm';
	public static function sbi_encrypt($message, $key,$iv){
        if (mb_strlen($key, '8bit') !== 16) {
            throw new Exception("Needs a 256-bit key! " .mb_strlen($key, '8bit'));
        }
             
        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            0,
            $iv
        );
        
        return $ciphertext;
    }
	
	public static function sbi_decrypt($ciphertext, $key,$iv){
        if (mb_strlen($key, '8bit') !== 16) {
            throw new Exception("Needs a 256-bit key!");
        }
		
	      
        return openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $key,
            0,
            $iv
        );
    }
	
	public static function process_payment($dev_mode='on', $encdata='',$merchant_code=''){
		if(empty($encdata)){
			aw2_library::set_error('encdata is empty'); 
			return;
		}
		
		if(empty($merchant_code)){
			aw2_library::set_error('merchant code is empty'); 
			return;
		}
			
		$url=self::$prod_url;
		if($dev_mode=='on')
			$url=self::$uat_url;
		$form ='
		<form name="paymentform" id="paymentform" method="POST" action="'.$url.'">
			<input type="hidden" name="encdata" value="'.$encdata.'"/>
			<input type="hidden" name="merchant_code" value="'.$merchant_code.'"/>
		</form>
		<script>
			document.paymentform.submit();
		</script>
		';
		
		return $form;
	}
	public static function re_verify_payment($dev_mode='on', $data='',$merchant_code='',$key, $iv){
		
		if(empty($data)){
			aw2_library::set_error('data is empty'); 
			return;
		}
		
		if(empty($merchant_code)){
			aw2_library::set_error('merchant code is empty'); 
			return;
		}
		
		
			
		$url=self::$prod_verify_url;
		if($dev_mode=='on')
			$url=self::$uat_verify_url;
		
		//$url='https://uatmerchant.onlinesbi.com/thirdparties/doubleverification.htm';
		
		$verification_string ="ref_no=".$data['ref_no']."|amount=".$data['amount'] ;
		$verification_string .="|checkSum=".md5($verification_string);
		
		$rencdata=SBI_Pay::sbi_encrypt($verification_string, $key,$iv);
		$v_data= array(
			"body"=>array(
					"encdata"=>$rencdata,
					"merchant_code" =>$merchant_code
				)
		);
		//send to server
		// [aw2.get function.wp_remote_post p1='https://uatmerchant.onlinesbi.com/thirdparties/doubleverification.htm ' p2="{dbv}" set='sbiresponse'/]
		
		$sbiresponse = wp_remote_post($url,$v_data);
				
		$v_dec_data=self::sbi_decrypt($sbiresponse['body'],$key,$iv);
		
		$v_dec_data=explode('|',$v_dec_data);
		
		$checksum=array_pop($v_dec_data);
		$checksum=explode('=',$checksum);
		
		$v_dec_data = implode('|',$v_dec_data);
		
		$tmp_checksum=md5($v_dec_data);
		
		$vpairs=array();
		
		if($tmp_checksum == $checksum[1]){
			
			$temp = explode ('|',$v_dec_data);
			foreach ($temp as $pair) 
			{
				list ($k,$v) = explode ('=',$pair);
				$vpairs[$k] = $v;
			}
		}
		else{
			aw2_library::set_error('verification checksum failed'); 
			return ;
		}
		
		
		return $vpairs;
	}
	public static function verify_payment($dev_mode='on', $encdata='',$merchant_code='',$key, $iv){
		
		if(empty($encdata)){
			aw2_library::set_error('encdata is empty'); 
			return;
		}
		
		if(empty($merchant_code)){
			aw2_library::set_error('merchant code is empty'); 
			return;
		}
		
		$dec_data=self::sbi_decrypt($encdata,$key,$iv);
		$dec_data=explode('|',$dec_data);
		
		$checksum=array_pop($dec_data);
		$checksum=explode('=',$checksum);
		
		$dec_data = implode('|',$dec_data);
		
		$tmp_checksum=md5($dec_data);
	
		if($tmp_checksum == $checksum[1]){
			$temp = explode ('|',$dec_data);
			foreach ($temp as $pair) 
			{
				list ($k,$v) = explode ('=',$pair);
				$pairs[$k] = $v;
			}
		}
		else{
			aw2_library::set_error('checksum failed'); 
			return ;
		}
			
		$url=self::$prod_verify_url;
		if($dev_mode=='on')
			$url=self::$uat_verify_url;
		
		//$url='https://uatmerchant.onlinesbi.com/thirdparties/doubleverification.htm';
		
		$verification_string ="ref_no=".$pairs['ref_no']."|amount=".$pairs['amount'] ;
		$verification_string .="|checkSum=".md5($verification_string);
		
		$rencdata=SBI_Pay::sbi_encrypt($verification_string, $key,$iv);
		$v_data= array(
			"body"=>array(
					"encdata"=>$rencdata,
					"merchant_code" =>$merchant_code
				)
		);
		//send to server
		// [aw2.get function.wp_remote_post p1='https://uatmerchant.onlinesbi.com/thirdparties/doubleverification.htm ' p2="{dbv}" set='sbiresponse'/]
		
		$sbiresponse = wp_remote_post($url,$v_data);
				
		$v_dec_data=self::sbi_decrypt($sbiresponse['body'],$key,$iv);
		
		$v_dec_data=explode('|',$v_dec_data);
		
		$checksum=array_pop($v_dec_data);
		$checksum=explode('=',$checksum);
		
		$v_dec_data = implode('|',$v_dec_data);
		
		$tmp_checksum=md5($v_dec_data);
		
		$vpairs=array();
		
		if($tmp_checksum == $checksum[1]){
			
			$temp = explode ('|',$v_dec_data);
			foreach ($temp as $pair) 
			{
				list ($k,$v) = explode ('=',$pair);
				$vpairs[$k] = $v;
			}
		}
		else{
			aw2_library::set_error('verification checksum failed'); 
			return ;
		}
		
		
		return array_merge($pairs,$vpairs);
	}
	
}

/* 
function aw2_sbi_decrypt($encd){
	$key=(file_get_contents('/var/www/ilslaw.edu/ILSLAWCOLLEGE.key', true));
	$iv ='1234567890123456';
	$dec_data=SBI_Pay::sbi_decrypt($encd,$key,$iv);
	
	$dec_data=explode('|',$dec_data);
	
	$checksum=array_pop($dec_data);
	$checksum=explode('=',$checksum);
	
	$dec_data = implode('|',$dec_data);
	
	$tmp_checksum=md5($dec_data);
	
	if($tmp_checksum == $checksum[1]){
		echo 'checksum passed';
		$temp = explode ('|',$dec_data);
		foreach ($temp as $pair) 
		{
			list ($k,$v) = explode ('=',$pair);
			$pairs[$k] = $v;
		}
		aw2_library::set('sbi',$pairs);
	}
	else{
		echo "checksum failed.".$tmp_checksum." == ".$checksum[1];
	}
}

function aw2_sbi_dv_setup($data){
	$key=(file_get_contents('/var/www/ilslaw.edu/ILSLAWCOLLEGE.key', true));
	$iv ='1234567890123456';
	
	//$dec_data=SBI_Pay::sbi_decrypt($encd,$key);
	$verification_string ="ref_no=".$data['ref_no']."|amount=".$data['amount'] ;
	$verification_string .="|checkSum=".md5($verification_string);
	
	$encdata=SBI_Pay::sbi_encrypt($verification_string, $key,$iv);
	aw2_library::set('sbi.encdata',$encdata);
	
} */