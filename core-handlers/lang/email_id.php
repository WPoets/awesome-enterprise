<?php
namespace aw2\email_id;

\aw2_library::add_service('email_id','Email Functions',['namespace'=>__NAMESPACE__]);

function validate_email($email) {
    if (!is_string($email)) {
        throw new \InvalidArgumentException('email_id: value must be a string');
    }
    
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException('email_id: invalid email format');
    }
    
    return strtolower($email);
}

\aw2_library::add_service('email_id.create','Create & validate an email address',['func'=>'email_id_create','namespace'=>__NAMESPACE__]);
function email_id_create($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null,
    'display_name'=>''
    ), $atts, 'email_id.create' ) );
    
    $email = validate_email($main);
    
    // If no display_name provided, return just email
    if(empty($display_name)) return $email;
    
    // If name has special characters or spaces, add quotes
    if(preg_match('/[^\x21-\x7E]/', $display_name) || strpos($display_name, ' ') !== false) {
        $display_name = '"' . str_replace('"', '\\"', $display_name) . '"';
    }
    
    return $display_name . ' <' . $email . '>';
}

\aw2_library::add_service('email_id.get','Get email from environment',['func'=>'email_id_get','namespace'=>__NAMESPACE__]);
function email_id_get($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.get' ) );
    
    $return_value = \aw2_library::get($main,$atts,$content);
    
    // Handle array case
    if(is_array($return_value)) {
        // Check if both required keys exist
        if(!array_key_exists('display_name', $return_value) || !array_key_exists('email_id', $return_value)) {
            throw new \InvalidArgumentException('email_id.get: array must have both display_name and email_id keys');
        }
        
        $result = array(
            'display_name' => $return_value['display_name'],
            'email_id' => validate_email($return_value['email_id'])
        );
        
        // Handle display name formatting
        if(preg_match('/[^\x21-\x7E]/', $result['display_name']) || strpos($result['display_name'], ' ') !== false) {
            $result['display_name'] = '"' . str_replace('"', '\\"', $result['display_name']) . '"';
        }
        
        return $result;
    }
    
    // Handle string case
    if(!is_string($return_value)) {
        throw new \InvalidArgumentException('email_id.get: input must be string or valid array');
    }
    
    return validate_email($return_value);
}

\aw2_library::add_service('email_id.is.valid','Check if email is valid',['func'=>'email_id_is_valid','namespace'=>__NAMESPACE__]);
function email_id_is_valid($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.is.valid' ) );
    
    if (!is_string($main)) return false;
    
    $email = trim($main);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

\aw2_library::add_service('email_id.normalize','Normalize email address',['func'=>'email_id_normalize','namespace'=>__NAMESPACE__]);
function email_id_normalize($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.normalize' ) );
    
    return validate_email($main);
}

\aw2_library::add_service('email_id.part.domain','Get domain part of email',['func'=>'email_id_part_domain','namespace'=>__NAMESPACE__]);
function email_id_part_domain($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.part.domain' ) );
    
    $email = validate_email($main);
    return substr($email, strpos($email, '@') + 1);
}

\aw2_library::add_service('email_id.part.user','Get user part of email',['func'=>'email_id_part_user','namespace'=>__NAMESPACE__]);
function email_id_part_user($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.part.user' ) );
    
    $email = validate_email($main);
    return substr($email, 0, strpos($email, '@'));
}

\aw2_library::add_service('email_id.mask','Mask email address',['func'=>'email_id_mask','namespace'=>__NAMESPACE__]);
function email_id_mask($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.mask' ) );
    
    $email = validate_email($main);
    $user = substr($email, 0, strpos($email, '@'));
    $domain = substr($email, strpos($email, '@'));
    
    if(strlen($user) <= 2)
        $masked_user = str_repeat('*', strlen($user));
    else
        $masked_user = substr($user, 0, 2) . str_repeat('*', strlen($user)-2);
    
    return $masked_user . $domain;
}

\aw2_library::add_service('email_id.comp.eq','Compare two emails',['func'=>'email_id_comp_eq','namespace'=>__NAMESPACE__]);
function email_id_comp_eq($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null,
    'with'=>null
    ), $atts, 'email_id.comp.eq' ) );
    
    $email1 = validate_email($main);
    $email2 = validate_email($with);
    
    return $email1 === $email2;
}

\aw2_library::add_service('email_id.is.disposable','Check if email is from disposable domain',['func'=>'email_id_is_disposable','namespace'=>__NAMESPACE__]);
function email_id_is_disposable($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.is.disposable' ) );
    
    $email = validate_email($main);
    $domain = substr($email, strpos($email, '@') + 1);
    
    // This is a basic list - in production, you'd want a more comprehensive list
    $disposable_domains = array(
        'tempmail.com', 'throwawaymail.com', 'temporarymail.com',
        'temp-mail.org', 'disposablemail.com'
    );
    
    return in_array($domain, $disposable_domains);
}

\aw2_library::add_service('email_id.is.role','Check if email is a role account',['func'=>'email_id_is_role','namespace'=>__NAMESPACE__]);
function email_id_is_role($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.is.role' ) );
    
    $email = validate_email($main);
    $user = substr($email, 0, strpos($email, '@'));
    
    $role_accounts = array(
        'admin', 'webmaster', 'info', 'support',
        'contact', 'sales', 'marketing', 'help'
    );
    
    return in_array(strtolower($user), $role_accounts);
}

\aw2_library::add_service('email_id.mailto_link','Create mailto link',['func'=>'email_id_mailto_link','namespace'=>__NAMESPACE__]);
function email_id_mailto_link($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null,
    'subject'=>'',
    'body'=>''
    ), $atts, 'email_id.mailto_link' ) );
    
    $email = validate_email($main);
    $return_value = 'mailto:' . $email;
    
    $params = array();
    if($subject) $params[] = 'subject=' . urlencode($subject);
    if($body) $params[] = 'body=' . urlencode($body);
    
    if(count($params) > 0)
        $return_value .= '?' . implode('&', $params);
    
    return $return_value;
}

\aw2_library::add_service('email_id.extract.email','Extract email from text',['func'=>'email_id_extract_from_text','namespace'=>__NAMESPACE__]);
function email_id_extract_from_text($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.extract_from_text' ) );
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('email_id.extract_from_text: input must be string');
    }
    
    $pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
    preg_match($pattern, $main, $matches);
    
    return isset($matches[0]) ? validate_email($matches[0]) : '';
}

\aw2_library::add_service('email_id.has_mx','Check if email domain has MX record',['func'=>'email_id_has_mx','namespace'=>__NAMESPACE__]);
function email_id_has_mx($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.has_mx' ) );
    
    $email = validate_email($main);
    $domain = substr($email, strpos($email, '@') + 1);
    
    return checkdnsrr($domain, 'MX');
}

\aw2_library::add_service('email_id.decompose','Decompose email string into components',['func'=>'email_id_decompose','namespace'=>__NAMESPACE__]);
function email_id_decompose($atts,$content=null,$shortcode){
    extract(\aw2_library::shortcode_atts( array(
    'main'=>null
    ), $atts, 'email_id.decompose' ) );
    
    if(!is_string($main)) {
        throw new \InvalidArgumentException('email_id.decompose: input must be string');
    }
    
    $result = array(
        'display_name' => '',
        'email_id' => ''
    );
    
    // First try to extract email from "Name <email>" format
    if(preg_match('/^(?:"([^"]+)"|([^<]+))\s*<([^>]+)>$/', $main, $matches)) {
        // Extract display name - either from quoted or unquoted match
        $result['display_name'] = !empty($matches[1]) ? $matches[1] : trim($matches[2]);
        // Extract email from angle brackets
        $result['email_id'] = validate_email($matches[3]);
    } else {
        // No formatted string found, treat entire input as email
        try {
            $result['email_id'] = validate_email($main);
        } catch(\Exception $e) {
            throw new \InvalidArgumentException('email_id.decompose: invalid email format');
        }
    }
    
    return $result;
}