<?php
namespace aw2\lt\api;

// Register the service
\aw2_library::add_service('lt.api.application', 'Handle LoanTap API transactions', ['func'=>'application', 'namespace'=>__NAMESPACE__]);

function application($atts, $content=null, $shortcode=null) {
    // Validate required attributes
    if(empty($atts['api_key']))
        throw new \Exception('api_key is required for lt.api.application');
    
    if(empty($atts['product_id']))
        throw new \Exception('product_id is required for lt.api.application');
        
    if(empty($atts['partner_id']))
        throw new \Exception('partner_id is required for lt.api.application');
        
    if(empty($atts['body']) || !is_array($atts['body']))
        throw new \Exception('body must be provided as an associative array for lt.api.application');

    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('application:activity must be specified (transact|enquire)');
    }
    $activity = $shortcode['tags_left'][0];
    
    // Initialize cURL session
    $curl = curl_init();
    
    // Prepare request body
    $request_body = json_encode($atts['body']);
	
	$time=time();
	$ciphertext = openssl_encrypt($time, 'AES-256-CBC', $atts['api_key'], 0);
	
    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://loantap.in/v1-application/' . $activity,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 5,  // 5 seconds to establish connection
        CURLOPT_TIMEOUT => 30,        // 30 seconds maximum for complete operation
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $request_body,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-API-AUTH: ' . $ciphertext,
            'REQ-PRODUCT-ID: ' . $atts['product_id'],
            'PARTNER-ID: ' . $atts['partner_id']
        ),
    ));
    
      
    // Execute request
    $response = curl_exec($curl);
    
    // Get all curl information and error details before closing
    $info = curl_getinfo($curl);
    $errno = curl_errno($curl);
    $error = curl_error($curl);
    
    // Close cURL session
    curl_close($curl);
    
    // If there was a curl error
    if($errno) {
        return array(
            'status' => 'error',
            'message' => $error,
            'error_type' => 'curl_error',
            'error_code' => $errno,
            'curl_info' => $info,
            'response' => null
        );
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    // Check for JSON decode errors
    if(json_last_error() !== JSON_ERROR_NONE) {
        return array(
            'status' => 'error',
            'message' => 'Invalid JSON response: ' . json_last_error_msg(),
            'error_type' => 'json_error',
            'error_code' => json_last_error(),
            'curl_info' => $info,
            'response' => $response
        );
    }
    
    // Return final response with metadata
    $is_success = ($info['http_code'] >= 200 && $info['http_code'] < 300);
    
    return array(
        'status' => $is_success ? 'success' : 'error',
        'message' => $is_success ? 'API call successful' : 'API call failed with HTTP code ' . $info['http_code'],
        'response' => $result,
        'curl_info' => $info
    );
}


// Register the service
\aw2_library::add_service('lt.api.loan', 'Handle LoanTap Loan API transactions', ['func'=>'loan', 'namespace'=>__NAMESPACE__]);

function loan($atts, $content=null, $shortcode=null) {
    // Validate required attributes
    if(empty($atts['api_key']))
        throw new \Exception('api_key is required for lt.api.loan');
        
    if(empty($atts['partner_id']))
        throw new \Exception('partner_id is required for lt.api.loan');
        
    if(empty($atts['body']) || !is_array($atts['body']))
        throw new \Exception('body must be provided as an associative array for lt.api.loan');

    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('loan:activity must be specified (transact|transact_strcl|enquire)');
    }
    $activity = $shortcode['tags_left'][0];
    
    // Validate activity
    $valid_activities = array('transact', 'transact_strcl', 'enquire');
    if(!in_array($activity, $valid_activities)) {
        throw new \InvalidArgumentException('Invalid loan:activity. Must be one of: ' . implode(', ', $valid_activities));
    }
    
    // Initialize cURL session
    $curl = curl_init();
    
    // Prepare request body
    $request_body = json_encode($atts['body']);
	
	$time=time();
	$ciphertext = openssl_encrypt($time, 'AES-256-CBC', $atts['api_key'], 0);
    
    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://loantap.in/v1-loan/' . $activity,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 5,  // 5 seconds to establish connection
        CURLOPT_TIMEOUT => 30,        // 30 seconds maximum for complete operation
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $request_body,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-API-AUTH: ' . $ciphertext,
            'PARTNER-ID: ' . $atts['partner_id']
        ),
    ));
    
    // Execute request
    $response = curl_exec($curl);
    
    // Get all curl information and error details before closing
    $info = curl_getinfo($curl);
    $errno = curl_errno($curl);
    $error = curl_error($curl);
    
    // Close cURL session
    curl_close($curl);
    
    // If there was a curl error
    if($errno) {
        return array(
            'status' => 'error',
            'message' => $error,
            'error_type' => 'curl_error',
            'error_code' => $errno,
            'curl_info' => $info,
            'response' => null
        );
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    // Check for JSON decode errors
    if(json_last_error() !== JSON_ERROR_NONE) {
        return array(
            'status' => 'error',
            'message' => 'Invalid JSON response: ' . json_last_error_msg(),
            'error_type' => 'json_error',
            'error_code' => json_last_error(),
            'curl_info' => $info,
            'response' => $response
        );
    }
    
    // Return final response with metadata
    $is_success = ($info['http_code'] >= 200 && $info['http_code'] < 300);
    
    return array(
        'status' => $is_success ? 'success' : 'error',
        'message' => $is_success ? 'API call successful' : 'API call failed with HTTP code ' . $info['http_code'],
        'response' => $result,
        'curl_info' => $info
    );
}