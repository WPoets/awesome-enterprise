<?php

namespace aw2;

\aw2_library::add_service('aw2.pay','Payments Library',['namespace'=>__NAMESPACE__]);
function pay($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts) );
	
	unset($atts['main']);

	
	$return_value='';
	$pieces=explode('.',$main);

	$plugin_path=dirname(plugin_dir_path( __DIR__ )).'/awesome-enterprise';

	if($pieces['0'] == 'sbi'){
		require_once ($plugin_path."/libraries/pay-sbi/api.php"); //SBI

		$pay=new aw2_sbi_payments($pieces['1'],$atts,$content);
		$return_value=$pay->run();
	}
	
	if($pieces['0'] == '2checkout'){
		$pay=new aw2_sbi_payments($pieces['1'],$atts,$content);
		$return_value=$pay->run();
	}
	
	if($pieces['0'] == 'razorpay'){
		require_once ($plugin_path."/monoframe/razorpay-php/Razorpay.php"); //Razorpay.php
		$pay=new aw2_razor_payments($pieces['1'],$atts,$content);
		$return_value=$pay->run();
	}
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	unset($pieces);
	
	return $return_value;
}


class aw2_sbi_payments{
	public $action=null;
	public $atts=null;
	public $content=null;
	public $iv=null;
	public $merchant_code=null;
	public $key_path=null;
	public $dev_mode='on';
	
	function __construct($action,$atts,$content=null){
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
		
		if(!empty($atts['iv']))
			$this->iv=$atts['iv'];
		else
			$this->iv=\aw2_library::get('site_settings.sbi_iv');	
		
		if(!empty($atts['merchant_code']))
			$this->merchant_code=$atts['merchant_code'];
		else
			$this->merchant_code=\aw2_library::get('site_settings.sbi_merchant_code');	
		
		if(!empty($atts['key']))
			$this->key_path=$atts['key'];
		else
			$this->key_path=\aw2_library::get('site_settings.sbi_secret_key');	

		if(!empty($atts['dev_mode']))
			$this->dev_mode=$atts['dev_mode'];
		else
			$this->dev_mode=\aw2_library::get('site_settings.sbi_staging_mode');	
	
	}
	
	public function run(){
		
		$return_value='';
		if (method_exists($this, $this->action))
			return call_user_func(array($this, $this->action));
		
		return $return_value;	
	}
	
	private function pay(){
		if(empty($this->key_path)){
			\aw2_library::set_error('Secret Key is Missing'); 
			return;
		}
		
		$args = $this->args();
		$str=array();
		
		foreach($args as $key=>$value){
			$str[] = $key.'='.$value;
		}
		
		$str=implode('|',$str);
		$str = $str.'|checkSum='.md5($str);
		
		$key=(file_get_contents($this->key_path, true));
		$encdata=SBI_Pay::sbi_encrypt($str, $key,$this->iv);
		
		$form = SBI_Pay::process_payment($this->dev_mode, $encdata,$this->merchant_code);
		
		return $form;
	}
	
	private function decrypt(){
		if(empty($this->key_path)){
			\aw2_library::set_error('Secret Key is Missing'); 
			return;
		}
		
		$key=(file_get_contents($this->key_path, true));
		$args = $this->args();
	
				
		$dec_data=SBI_Pay::sbi_decrypt($this->atts['encdata'],$key,$this->iv);
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
			\aw2_library::set_error('checksum failed'); 
			return ;
		}

	
		return $pairs;
	}
	
		
	private function verify(){
		$args = $this->args();
		$key=(file_get_contents($this->key_path, true));
		if(!empty($this->atts['encdata'])){
			$encdata = $this->atts['encdata'];
			$payment_response = SBI_Pay::verify_payment($this->dev_mode, $encdata,$this->merchant_code,$key,$this->iv);
		}
		
		if(!empty($this->atts['data'])){
			$encdata = $this->atts['data'];
			$payment_response = SBI_Pay::re_verify_payment($this->dev_mode, $encdata,$this->merchant_code,$key,$this->iv);
		}
	
		return $payment_response;
		
	}
	
		
	private	function args(){
		if($this->content==null || $this->content==''){
			$return_value=array();	
		}
		else{
			$json=\aw2_library::clean_specialchars($this->content);
			$json=\aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				\aw2_library::set_error('Invalid JSON' . $content); 
				$return_value=array();	
			}
		}
		return $return_value;
	}
}

class aw2_razor_payments{
	public $action=null;
	public $atts=null;
	public $content=null;
	
	public $api_key=null;
	public $api_secret=null;
	public $dev_mode='on';
	
	function __construct($action,$atts,$content=null){
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
		
		if(!empty($atts['iv']))
			$this->iv=$atts['iv'];
		else
			$this->iv=\aw2_library::get('site_settings.sbi_iv');	
		
		if(!empty($atts['api_key']))
			$this->api_key=$atts['api_key'];
		else
			$this->api_key=\aw2_library::get('site_settings.razor_api_key');	
		
		if(!empty($atts['api_secret']))
			$this->api_secret=$atts['api_secret'];
		else
			$this->api_secret=\aw2_library::get('site_settings.razor_api_secret');	

		if(!empty($atts['dev_mode']))
			$this->dev_mode=$atts['dev_mode'];
		else
			$this->dev_mode=\aw2_library::get('site_settings.razor_staging_mode');	
	
	}
	
	public function run(){
		
		$return_value='';
		if (method_exists($this, $this->action))
			return call_user_func(array($this, $this->action));
		
		return $return_value;	
	}
	
	private function pay(){
		
		$args = $this->args();
		
		
		$api = new \Razorpay\Api\Api($this->api_key, $this->api_secret);
		$data = array(
					'receipt' => $args['receipt'], 
					'amount' => $this->getOrderAmountAsInteger($args['amount']),
					'currency' => 'INR',
					'payment_capture'=>$args['payment_capture']
				);
				
		$order = $api->order->create($data);
		$razorpayOrderId = $order['id'];
		
		setcookie( 'razorpay_order_id', $razorpayOrderId, time() + (30 * MINUTE_IN_SECONDS),'/' );
		
		//save razor pay add 
		$paydata = array(
		  'key'          => $this->api_key,
		  'name'         => get_bloginfo('name'),
		  'currency'     => 'INR',
		  'description'  => $args['description'],
		  'notes'        => $args['notes'],
		  'order_id'     => $razorpayOrderId,
		  'callback_url' => $args['callback_url'],
		);
		
		$paydata['amount'] = $this->getOrderAmountAsInteger($args['amount']);
		$args['prefill'] = array(
				'name'    => $args['name'],
				'email'   => $args['email'],
				'contact' => $args['contact'],
			);
	
		wp_register_script('razorpay_checkout',
			'https://checkout.razorpay.com/v1/checkout.js',
			null, null);

		wp_register_script('razorpay_wc_script','https://cdn.getawesomestudio.com/lib/razorpay/script.js',
			array('razorpay_checkout'));

		wp_localize_script('razorpay_wc_script',
			'razorpay_wc_checkout_vars',
			$paydata
		);

		wp_enqueue_script('razorpay_wc_script');
		
		$form ='
		
		<form name="razorpayform" action="'.$args['callback_url'].'" method="POST">
			<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
			<input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
		</form>
		<p id="msg-razorpay-success" class="woocommerce-info woocommerce-message" style="display:none">
		Please wait while we are processing your payment.
		</p>
		<p>
			<button id="btn-razorpay">Pay Now</button>
			<button id="btn-razorpay-cancel" onclick="document.razorpayform.submit()">Cancel</button>
		</p>
		
		';

		return $form;
	}
	
	private function getOrderAmountAsInteger($amount){
		return (int) $amount * 100;
	}
	
	private function verify(){
		
		$args = $this->args();
		$api = new \Razorpay\Api\Api($this->api_key, $this->api_secret);
		
		$success=false;
		$error='';
		
		
		$attributes = array(
                'razorpay_payment_id' => $_POST['razorpay_payment_id'],
                'razorpay_order_id'   => $_COOKIE['razorpay_order_id'],
                'razorpay_signature'  => $_POST['razorpay_signature']
            );
		try
		{
			$api->utility->verifyPaymentSignature($attributes);
		    $success=true;
			$error="payment successful.";
		}
		catch (Exception $e)
		{
			$error = 'ERROR: Payment to Razorpay Failed. ' . $e->getMessage();
			\aw2_library::set_error($error); 
		}
        
		$order = $api->order->fetch($_COOKIE['razorpay_order_id']);	
		//util::var_dump($order);
		$payment_response = array(
			"success" =>$success,
			"razorpay_payment_id" => $_POST['razorpay_payment_id'],
			"razorpay_order_id" => $_COOKIE['razorpay_order_id'],
			"receipt" => $order->receipt,
			"notes" => $order['notes'],
			"status_desc" => $error,
			"status" => $order->status
		
		);
		
		setcookie( 'razorpay_order_id', '',time() - (30 * MINUTE_IN_SECONDS),'/');
		
		return $payment_response;
		
	}
	
		
	private	function args(){
		if($this->content==null || $this->content==''){
			$return_value=array();	
		}
		else{
			$json=\aw2_library::clean_specialchars($this->content);
			$json=\aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				\aw2_library::set_error('Invalid JSON' . $content); 
				$return_value=array();	
			}
		}
		return $return_value;
	}
}
