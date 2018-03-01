<?php

aw2_library::add_shortcode('woo','get', 'awesome2_woo_get','Run WooCommerce actions');

function awesome2_woo_get($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts) );
	
	
	unset($atts['main']);

	if ( !class_exists( 'WooCommerce' ) ) 
		return 'WooCommerce not installed.';
	
	$return_value='';
	$pieces=explode('.',$main);

	switch ($pieces['0']) {

		case 'product':
			//$return_value=$woo->checkout($atts);
			$woo_product=new aw2_woo_product($pieces['1'],$atts,$content);
			$return_value=$woo_product->run();

			break;

		case 'cart':
			$woo_cart=new aw2_woo_cart($pieces['1'],$atts,$content);
			$return_value=$woo_cart->run();
			break;
				
		case 'order':
			$woo_order=new aw2_woo_order($pieces['1'],$atts,$content);
			$return_value=$woo_order->run();
			break;
			
		case 'wc':
			$woo=new aw2_woo($pieces['1'],$atts,$content);
			$return_value=$woo->run();
			break;
	}
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


class aw2_woo_product{
	public $action=null;
	public $atts=null;
	public $content=null;
	public $product=null;
	
	function __construct($action,$atts,$content=null){
	
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
			
	}
	public function run(){
		$this->product = wc_get_product( $this->atts['product_id'] );
		
		if(!is_object($this->product))
			return '';	
		
		$return_value='';
		if (method_exists($this, $this->action))
			return call_user_func(array($this, $this->action));
		else {
			if(isset($this->product,$this->action)){
				$return_value=$this->product->{$this->action};
			}
			
			if(method_exists($this->product,$this->action) && is_callable(array($this->product,$this->action))){
				$parameters = aw2_library::get_parameters($this->atts);
				$return_value= call_user_func_array(array($this->product, $this->action), $parameters);
			}
		}
		return $return_value;	
	}
	
	private function add_to_cart_html(){
		$add_to_cart_url = $this->product->add_to_cart_url();
		if($this->product->is_purchasable() && $this->product->is_in_stock())
			$text= $yes;
		else
			$text= $no;
		
		$return_value='<a class="button add_to_cart_button product_type_simple" data-quantity="1" data-product_sku="" data-product_id="' . $product_id . '" rel="nofollow" href="' . $add_to_cart_url . '">' . $text . '</a>';
		return $return_value;	
	}
	
	private function add_to_cart_url(){
		return $this->product->add_to_cart_url();
	}
	
	private function is_available(){
		if($this->product->is_purchasable() && $this->product->is_in_stock())
			$return_value= 'yes';
		else
			$return_value= 'no';
		return $return_value;	
	}
	
	private function average_rating(){
		$return_value = $this->product->get_average_rating();
		if(empty($return_value))
			$return_value ='0.0';
		return $return_value;	
	}
	
	private function review_count(){
		$return_value = $this->product->get_review_count();
		if(empty($return_value))
			$return_value ='0';
		return $return_value;	
	}
	
	private function gallery_images(){
		$return_value = $this->product->get_gallery_attachment_ids();
		if(empty($return_value))
			$return_value ='0';	
		return $return_value;	
	}
	
}


class aw2_woo_order {
	public $action=null;
	public $atts=null;
	public $content=null;
	public $order=null;
	
	function __construct($action,$atts,$content=null){
	
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
			
	}
	
	public function run(){	   
		$return_value='';
		if (method_exists($this, $this->action))
			$return_value = call_user_func(array($this, $this->action));
		else {
			$args=aw2_library::get_clean_args($this->content,$this->atts);
			$this->order = new WC_Order($this->atts['order_id']);
			
			
			if(isset($this->order,$this->action)){
				$return_value=$this->order->{$this->action};
			}
			
			if(method_exists($this->order,$this->action) && is_callable(array($this->order,$this->action))){
				$parameters = aw2_library::get_parameters($this->atts);
				$return_value= call_user_func_array(array($this->order, $this->action), $parameters);
			}
		}
		return $return_value;	
	}
	
	private function display_item_meta(){
		if(!isset($this->atts['order_id'])){
			aw2_library::set_error('order_id is required');
			return;
		}
		
		if(!isset( $this->atts['item_id'])){
			aw2_library::set_error('item_id is required');
			return;
		}
			
			
		$this->order = new WC_Order($this->atts['order_id']);
	   
		
		
		$order_item=$this->get_item_from_itemid($this->atts['item_id']);
		if(empty($order_item))
			return;
		
		$product   = $this->order->get_product_from_item( $order_item );
		$item_meta = new WC_Order_Item_Meta( $order_item, $product );
		
		return $item_meta->display(false, true); // return 
	}
	
	private function get_formatted_line_subtotal(){
		if(!isset($this->atts['order_id'])){
			aw2_library::set_error('order_id is required');
			return;
		}
		
		if(!isset( $this->atts['item_id'])){
			aw2_library::set_error('item_id is required');
			return;
		}
			
			
		$this->order = new WC_Order($this->atts['order_id']);
	   
		
		
		$order_item=$this->get_item_from_itemid($this->atts['item_id']);
		if(empty($order_item))
			return;
		
		return $this->order->get_formatted_line_subtotal( $order_item);
	}
	
	private function create(){
		
		wc_transaction_query( 'start' );
		$args=aw2_library::get_clean_args($this->content,$this->atts);
		$return_value=array();
		
		try {
			// Make sure customer exists.
			if ( 0 !== $this->atts['customer_id'] && false === get_user_by( 'id', $this->atts['customer_id'] ) ) {
				throw new Exception( 'Customer ID is invalid.' );
			}
 
			$data= array(
					'status'        => $this->atts['status'],
					'customer_id'   => $this->atts['customer_id']
				);	
			$order = wc_create_order( $data );
			
			if ( is_wp_error( $order ) ) {
				return $return_value;
			}
			
			$return_value['id']=$order->id;
			
			// Set addresses.
			if ( is_array( $args['billing'] ) ) {
				$fields = $order->get_address( 'billing' );
				foreach ( array_keys( $fields ) as $field ) {
					if ( isset( $args['billing'][ $field ] ) ) {
						$fields[ $field ] = $args['billing'][ $field ];
					}
				}
				// Set address.
				$order->set_address( $fields, 'billing' );
			}
			
			if ( is_array( $args['shipping'] ) ) {
				$fields = $order->get_address( 'shipping' );
				foreach ( array_keys( $fields ) as $field ) {
					if ( isset( $args['shipping'][ $field ] ) ) {
						$fields[ $field ] = $args['shipping'][ $field ];
					}
				}
				// Set address.
				$order->set_address( $fields, 'shipping' );
			}
			
			// Set currency.
			if ( is_array( $args['currency'] ) ) {
				update_post_meta( $order->id, '_order_currency', $args['currency'] );
			}
			
			if ( is_array( $args[ 'line_items' ] ) ) {
				foreach ( $args[ 'line_items' ]  as $item ) {
					$item_args = array();
					// Product is always required.
					if ( empty( $item['product_id'] ) ||(isset( $item['quantity'] ) && 0 > floatval( $item['quantity'] ) )) {
						throw new Exception( 'Missing required line_items data.' );
					}				
					if ( ! empty( $item['sku'] ) ) {
						$product_id = (int) wc_get_product_id_by_sku( $item['sku'] );
					} elseif ( ! empty( $item['product_id'] ) && empty( $item['variation_id'] ) ) {
						$product_id = (int) $item['product_id'];
					} elseif ( ! empty( $item['variation_id'] ) ) {
						$product_id = (int) $item['variation_id'];
					}
					
					$product = wc_get_product( $product_id );
					// Get variation attributes.
					if ( method_exists( $product, 'get_variation_attributes' ) ) {
						$item_args['variation'] = $product->get_variation_attributes();
					}
					$item_args['qty'] = $item['quantity'];
					// Total.
					if ( isset( $item['total'] ) ) {
						$item_args['totals']['total'] = floatval( $item['total'] );
					}
					// Total tax.
					if ( isset( $item['total_tax'] ) ) {
						$item_args['totals']['tax'] = floatval( $item['total_tax'] );
					}
					// Subtotal.
					if ( isset( $item['subtotal'] ) ) {
						$item_args['totals']['subtotal'] = floatval( $item['subtotal'] );
					}
					// Subtotal tax.
					if ( isset( $item['subtotal_tax'] ) ) {
						$item_args['totals']['subtotal_tax'] = floatval( $item['subtotal_tax'] );
					}
					$item_id = $order->add_product( $product, $item_args['qty'], $item_args );
					
					if(isset( $item['meta'] ) && is_array($item['meta'] )){
						foreach ( $item['meta'] as $meta_key => $meta_value ) {
							wc_update_order_item_meta($item_id,$meta_key,$meta_value);
						}
					}
					
					$return_value['item_id']=$item_id;
				}
			}
			
			if ( is_array( $args[ 'shipping_lines' ] ) ) {
				foreach ( $args[ 'shipping_lines' ] as $shipping ) {
					if ( ! empty( $shipping['total'] ) && 0 <= floatval( $shipping['total'] ) && !empty( $shipping['method_id'] )) {
						
						$rate = new WC_Shipping_Rate( $shipping['method_id'], isset( $shipping['method_title'] ) ? $shipping['method_title'] : '', isset( $shipping['total'] ) ? floatval( $shipping['total'] ) : 0, array(), $shipping['method_id'] );

						$shipping_id = $order->add_shipping( $rate );
					}
					else{
						throw new Exception( 'Missing required shipping_lines data.' );
					}
				}
			}
			if ( is_array( $args[ 'fee_lines' ] ) ) {
				foreach ( $args[ 'fee_lines' ] as $fee ) {
					// Fee name is required.
					if ( empty( $fee['name'] ) ) {
						throw new Exception('Fee name is required.');
					}
					
					$fee_data            = new stdClass();
					$fee_data->id        = sanitize_title( $fee['name'] );
					$fee_data->name      = $fee['name'];
					$fee_data->amount    = isset( $fee['total'] ) ? floatval( $fee['total'] ) : 0;
					$fee_data->taxable   = false;
					$fee_data->tax       = 0;
					$fee_data->tax_data  = array();
					$fee_data->tax_class = '';

					// If taxable, tax class and total are required.
					if ( isset( $fee['tax_status'] ) && 'taxable' === $fee['tax_status'] ) {

						if ( ! isset( $fee['tax_class'] ) ) {
							throw new Exception('Fee tax class is required when fee is taxable.');
						}

						$fee_data->taxable   = true;
						$fee_data->tax_class = $fee['tax_class'];

						if ( isset( $fee['total_tax'] ) ) {
							$fee_data->tax = isset( $fee['total_tax'] ) ? wc_format_refund_total( $fee['total_tax'] ) : 0;
						}
					}

					$fee_id = $order->add_fee( $fee_data );

					if ( ! $fee_id )
						throw new Exception('Cannot create fee, try again.');	
				}
			}
			if ( is_array( $args[ 'coupon_lines' ] ) ) {
				foreach ( $args[ 'coupon_lines' ] as $coupon ) {
					
					// Coupon discount must be positive float.
					if ( isset( $coupon['discount'] ) && 0 > floatval( $coupon['discount'] ) ) {
						throw new Exception( 'Coupon discount must be a positive amount.');
					}
					// Coupon code is required.
					if ( empty( $coupon['code'] ) ) {
						throw new Exception(  'Coupon code is required.', 'woocommerce' );
					}

					$coupon_id = $order->add_coupon( $coupon['code'], floatval( $coupon['discount'] ) );

					if ( ! $coupon_id ) {
						throw new Exception(  'Cannot create coupon, try again.');
					}	
				}
			}
			// Calculate totals and set them.
			if(isset($args[ 'apply_tax' ]) && $args[ 'apply_tax' ]=="no"){
				$order->calculate_totals(false);
			}else{
				$order->calculate_totals();
			}			

			// Set payment method.
			if ( ! empty( $args['payment_method'] ) ) {
				update_post_meta( $order->id, '_payment_method', $args['payment_method'] );
			}
			if ( ! empty( $args['payment_method_title'] ) ) {
				update_post_meta( $order->id, '_payment_method_title', $args['payment_method_title'] );
			}
			if ( true === $args['set_paid'] ) {
				$order->payment_complete( $args['transaction_id'] );
			}

			// Set meta data.
			if ( ! empty( $args['meta_data'] ) && is_array( $args['meta_data'] ) ) {
				foreach ( $args['meta_data'] as $meta_key => $meta_value ) {
					if ( is_string( $meta_key ) && ! is_protected_meta( $meta_key ) && is_scalar( $meta_value ) ) {
						update_post_meta( $order->id, $meta_key, $meta_value );
					}
				}
			}

			wc_transaction_query( 'commit' );
			do_action( 'woocommerce_order_status_' . $this->atts['status'], $order->id );
			
			return $return_value;
		} catch ( Exception $e ) {
			wc_transaction_query( 'rollback' );

			return $return_value . ' Exception' . $e->getMessage();
		} 
	}
	
	private function get_item_from_itemid($item_id){
		
		$items = $this->order->get_items();
		
		foreach($items as $key=> $item){
			
			if($key == $item_id)
				return $item;
		}
		return ;
	}
}

class aw2_woo_cart {
	public $action=null;
	public $atts=null;
	public $content=null;
	public $cart=null;
	
	function __construct($action,$atts,$content=null){
	
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
		$this->cart = WC()->cart ;	
	}
	
	public function run(){
		
		$return_value='';
		if (method_exists($this, $this->action))
			return call_user_func(array($this, $this->action));
		else {
			$args=aw2_library::get_clean_args($this->content,$this->atts);
			if(isset($this->cart,$this->action)){
				$return_value=$this->cart->{$this->action};
			}
			
			if(method_exists($this->cart,$this->action) && is_callable(array($this->cart,$this->action))){
				$parameters = aw2_library::get_parameters($this->atts);
				$return_value= call_user_func_array(array($this->cart, $this->action), $parameters);
			}
		}
		return $return_value;	
	}
	
	private function billing_checkout_form (){
		$return_value ='';
		$checkout = WC()->checkout;
		wp_enqueue_script( 'checkout', plugins_url() . '/woocommerce/assets/js/frontend/checkout.js', array(), null, true );
		wp_enqueue_style( 'woocommerce', plugins_url() . '/woocommerce/assets/css/woocommerce.css', array(), null);
		ob_start();
		$checkout->checkout_form_billing();
		$return_value = ob_get_contents();
		ob_end_clean();
		return $return_value;
	}
	
	private function get_discount(){
		if(!$this->in_cart($this->atts['product_id'])) {
		 $this->cart->add_to_cart($this->atts['product_id'], 1);
		}	
		$cart_key = key($this->cart->cart_contents);
		$coupon = new WC_Coupon($this->atts['coupon']);
		
		$product = get_product($this->atts['product_id']);
		
		if ( $coupon->is_valid() && $coupon->is_valid_for_product( $product) ) {
			$discount_amount = $coupon->get_discount_amount( $this->atts['total_price'] , $this->cart->cart_contents[$cart_key], true );
			$msg = $coupon->get_coupon_message( 200 );
			$status =true;
		}else{
			$msg = $coupon->error_message;
			$status =false;
		}
		$discounted_price = $this->atts['total_price'] - $discount_amount;
		
		$discount = array();
		$discount['discounted_price']=$discounted_price;
		$discount['discount_amount']=$discount_amount;
		$discount['msg']=$msg;
		$discount['status']=$status;

		return $discount;
	}
	
	private function in_cart($product_id) {
		global $woocommerce;
	 
		foreach($woocommerce->cart->get_cart() as $key => $val ) {
			$_product = $val['data'];
	 
			if($product_id == $_product->id ) {
				return true;
			}
		}
	 
		return false;
	}
}
class aw2_woo {
	public $action=null;
	public $atts=null;
	public $content=null;
	
	function __construct($action,$atts,$content=null){
	
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
	}
	
	public function run(){
		
		$return_value='';
		if (method_exists($this, $this->action))
			return call_user_func(array($this, $this->action));
		else {
			/*$args=aw2_library::get_clean_args($this->content,$this->atts);
			 if(isset($this->cart,$this->action)){
				$return_value=$this->cart->{$this->action};
			}
			
			if(method_exists($this->cart,$this->action) && is_callable(array($this->cart,$this->action))){
				$parameters = aw2_library::get_parameters($this->atts);
				$return_value= call_user_func_array(array($this->cart, $this->action), $parameters);
			} */
			$parameters = aw2_library::get_parameters($this->atts);
			
			if(function_exists($this->action)){
				ob_start();
				call_user_func_array($this->action, $parameters);
				$return_value = ob_get_contents();
				ob_end_clean();
				return $return_value;
			}
		}
		return $return_value;	
	}
	
	private function billing_edit_form(){
		ob_start();
		woocommerce_account_edit_address( 'billing' );
		$return_value = ob_get_contents();
		ob_end_clean();
		return $return_value;
	}
}
