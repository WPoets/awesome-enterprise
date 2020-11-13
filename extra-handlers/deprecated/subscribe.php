<?php
namespace aw2;

\aw2_library::add_service('aw2.subscribe','Subscribe to thrid part newsletter service like mailchimp.',['namespace'=>__NAMESPACE__]);
function subscribe($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
		'main' => null,
		'api_key' => null,
		'phone_no' => null,
		'senderid' => null,
	), $atts, 'aw2_sms' ) );
	
	$return_val = '';
	if($main!=null){
		switch($main){
			case 'mailchimp':
				if(empty(\aw2_library::get("site_settings.opt-mailchimp-api-key")))
					return 'Please Set Mailchimp API Key';
				/* 
				$param = array('api_key' => \aw2_library::get("site_settings.sms-api-key"),
					'phone_no' => '0'.$phone_no, 
					'message' => \aw2_library::parse_shortcode($content)
				); */
				
				$list_id=(isset($atts['list_id']) ? $atts['list_id'] :\aw2_library::get("site_settings.opt-mailchimp-lists"));
				$email=(isset($atts['email']) ? $atts['email'] :'');
				$email_type=(isset($atts['email_type']) ? $atts['email_type'] :'html');
				$double_optin=(isset($atts['double_optin']) ? $atts['double_optin'] :false);
				$update_existing=(isset($atts['update_existing']) ? $atts['update_existing'] :true);
				$replace_interests=(isset($atts['replace_interests']) ? $atts['replace_interests'] :false);
				$send_welcome=(isset($atts['send_welcome']) ? $atts['send_welcome'] :false);
				
				$args=\aw2_library::get_clean_args($content,$atts);
				$merge_vars=array_change_key_case($args, CASE_UPPER);	
				
				$api_key=(isset($atts['api_key']) ? $atts['api_key'] :\aw2_library::get("site_settings.opt-mailchimp-api-key"));
				$api = new \MC4WP_API($api_key);	
				$result = $api->subscribe( $list_id, $email, $merge_vars, $email_type,$double_optin, $update_existing, $replace_interests, $send_welcome);
				$return_val="";
				if ( $result !== true && $api->has_error() ) {
					$return_val=sprintf( 'MailChimp for WordPres : %s', date( 'Y-m-d H:i:s' ), $api->get_error_message() );
					error_log( $return_val );
				}
				
				return $return_val;
			break;
			case 'sendinblue':
				
			break;
		}
	}
}